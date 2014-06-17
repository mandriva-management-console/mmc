# -*- coding: utf-8; -*-g
#
# (c) 2014 Zentyal S.L., http://www.zentyal.com
#
# This file is part of Mandriva Management Console (MMC).
#
# MMC is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# MMC is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MMC.  If not, see <http://www.gnu.org/licenses/>.
#
# Author(s):
#   Julien Kerihuel <jkerihuel@zentyal.com>
#
#

"""
MDS samba4 plugin for the MMC agent.
"""

import os
import os.path
import logging
import socket
import xmlrpclib
from time import strftime
from twisted.internet import defer, reactor
from mmc.core.version import scmRevision
from mmc.plugins.base import BasePluginConfig
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.samba4.audit import AA, AT, PLUGIN_NAME
from mmc.plugins.samba4.config import Samba4Config
from mmc.plugins.samba4.smb_conf import SambaConf
from mmc.plugins.samba4.samba4 import SambaAD
from mmc.plugins.samba4.helpers import shellquote
from mmc.support.mmctools import (cleanFilter, shlaunchBackground,
                                  shLaunchDeferred, progressBackup,
                                  shlaunch)
from mmc.plugins.services import ServiceManager


logger = logging.getLogger()

VERSION = "1.0.0"
APIVERSION = "1.0.0"
REVISION = scmRevision("$Rev$")


def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

def activate():
    """
    This function define if the module "base" can be activated.
    @return: True if this module can be activated
    @rtype: boolean
    """
    config = Samba4Config("samba4")

    if config.disabled:
        logger.info("samba4 plugin disabled by configuration.")
        return False

    if not os.path.exists(config.init_script):
        logger.error(config.init_script + " does not exist")
        return False

    return True

def reloadSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA4_RELOAD)
    shlaunchBackground(Samba4Config("samba4").init_script + ' restart')
    r.commit()
    return 0

restartSamba = reloadSamba

def purgeSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA4_PURGE)

    def _purgeSambaConfig():
        samba = SambaConf()
        conf_files = []
        conf_files.append(shellquote(samba.smb_conf_path))
        conf_files.append(shellquote(samba.private_dir() + '/*'))
        shlaunchBackground("rm -rf %s" % ' '.join(conf_files))

    # FIXME should we use deferred instead?
    shlaunchBackground(Samba4Config("samba4").init_script + ' stop',
                       endFunc=_purgeSambaConfig)
    r.commit()
    return True

def isSamba4Provisioned():
    """
    @return: check if Samba4 has been provisioned already
    @rtype: boolean
    """
    global_info = SambaConf().getGlobalInfo()
    if global_info["realm"] and global_info["server role"]:
        return True
    return False

def getSamba4GlobalInfo():
    """
    @return: values from [global] section in smb.conf
    @rtype: dict
    """
    return SambaConf().getGlobalInfo()

def netbiosDomainName():
    """
    @return default netbios domain name which is the hostname
    @rtype str
    """
    (exitCode, stdout, stderr) = shlaunch("hostname")
    if exitCode != 0:
        error_msg = "Couldn't get hostname (`%d`): %s\n%s" % (exitCode, stdout, stderr)
        logger.error(error_msg)
        raise Exception(error_msg)
    return stdout[0]

def provisionSamba(mode, realm, description):
    r = AF().log(PLUGIN_NAME, AA.SAMBA4_PROVISION)
    if mode != 'dc':
        raise NotImplemented("We can only provision samba4 as Domain Controller")

    samba = SambaConf()
    netbios_domain_name = netbiosDomainName()
    params = {'realm': realm, 'prefix': samba.prefix,
              'role': mode, 'adminpass': samba.admin_password,
              'workgroup': samba.workgroupFromRealm(realm)}
    cmd = ("%(prefix)s/bin/samba-tool domain provision"
           " --adminpass='%(adminpass)s'"
           " --domain='%(workgroup)s'"
           " --workgroup='%(workgroup)s'"
           " --realm='%(realm)s'"
           " --use-xattr=yes"
           " --use-rfc2307"
           " --server-role='%(role)s'" % params)

    def domain_provision_cb(_, sambatool):
        logger.info("provision: domain_provision_cb")
        if sambatool.exitCode != 0:
            logger.debug("Fail executing %s, ret code %d",
                         cmd, sambatool.exitCode)
            logger.debug(sambatool.out)
            logger.debug(sambatool.err)
        samba.writeSambaConfig(mode, netbios_domain_name, realm, description)
        samba.writeKrb5Config(realm)
        return sambatool.exitCode == 0

    def disable_password_complexity(sambatool):
        logger.info("provision: disable_password_complexity")
        cmd = ("%s/bin/samba-tool domain passwordsettings set"
               " --complexity=off"
               " --min-pwd-length=0"
               " --min-pwd-age=0"
               " --max-pwd-age=999" % samba.prefix)
        d = shLaunchDeferred(cmd)
        d.addCallback(domain_provision_cb, sambatool)
        d.addCallback(reconfig_ldap_service)
        d.addCallback(stop_iptables_services)
        d.addCallback(start_samba4_service)
        return d

    # Number of times we will check whether ldap is running on 389 port
    max_checkings_ldap_running = 10
    # Sleep time between each check
    sleep_time = 1

    def check_ldap_is_running(result, tries=1):
        if tries > max_checkings_ldap_running:
            logger.info("Ldap is not running after waiting long time")
            return False
        logger.info("Checking ldap is running")
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        result = sock.connect_ex(('127.0.0.1', 389))
        if result == 0:
            return True
        d = defer.Deferred()
        reactor.callLater(sleep_time, d.callback, None)
        d.addCallback(check_ldap_is_running, tries + 1)
        return d

    def reconfig_ldap_service(result):
        if not result:
            return result
        logger.info("provision: reconfig_ldap_service")
        should_reconfing = True
        f = None
        try:
            f = open('/etc/sysconfig/ldap', 'r')
            for line in f:
                if line.lstrip().startswith('SLAPDURLLIST='):
                    should_reconfing = False
            if should_reconfing:
                f.close()
                f = open('/etc/sysconfig/ldap', 'a')
                import os
                f.write(os.linesep)
                f.write('SLAPDURLLIST="ldap://127.0.0.1"')
                f.write(os.linesep)
                # restart slapd
                ServiceManager().restart("ldap")
        except Exception as e:
            logger.error(e.message)
            return False
        finally:
            if f:
                f.close()
        d = defer.Deferred()
        reactor.callLater(sleep_time, d.callback, None)
        d.addCallback(check_ldap_is_running)
        return d

    def stop_iptables_services(result):
        if not result:
            return result
        if ServiceManager().get_unit_info("iptables")['active_state'] == 'active':
            logger.info("provision: stop iptables.service")
            return ServiceManager().stop("iptables")
        return True

    def start_samba4_service(result):
        if not result:
            return result
        logger.info("provision: Starting samba4.service")
        (exitCode, stdout, stderr) = shlaunch("service samba4 start")
        # return ServiceManager().start("samba4")
        return exitCode == 0

    d = shLaunchDeferred(cmd)
    d.addCallback(disable_password_complexity)

    r.commit()
    return d

# v Shares --------------------------------------------------------------------

def getACLOnShare(name):
    if name:
        return SambaConf().getACLOnShare(name)
    else:
        return []

def getProtectedSamba4Shares():
    return ["", "homes", "netlogon", "archive", "sysvol"]

def getShares():
    return SambaConf().getDetailedShares()

def getShare(name):
    return SambaConf().getDetailedShare(name)

def addShare(name, path, comment, browseable, permAll, usergroups, users):
    samba = SambaConf()
    samba.addShare(name, path, comment, browseable, permAll, usergroups, users)
    samba.save()
    return name

def editShare(name, path, comment, browseable, permAll, usergroups, users):
    samba = SambaConf()
    samba.addShare(name, path, comment, browseable, permAll, usergroups, users,
                   mod=True)
    return samba.save()

def deleteShare(name, file):
    samba = SambaConf()
    samba.delShare(name, file)
    return samba.save()

def isAuthorizedSharePath(path):
    return not path or SambaConf().isAuthorizedSharePath(path)

def backupShare(share, media, login):
    """
    Launch as a background process the backup of a share
    """
    r = AF().log(PLUGIN_NAME, AA.SAMBA4_BACKUP_SHARE,
                 [(share, AT.SHARE), (login, AT.USER)], media)
    config = BasePluginConfig("base")
    cmd = os.path.join(config.backuptools, "backup.sh")
    if share == "homes":
        # FIXME: Maybe we should have a configuration directive to tell that
        # all users home are stored into /home
        savedir = "/home/"
    else:
        savedir = SambaConf().getContent(share, "path")
    # Run backup process in background
    shlaunchBackground(cmd + " " + share + " " + savedir + " " +
                       config.backupdir + " " + login + " " + media + " " +
                       config.backuptools, "backup share " + share,
                       progressBackup)
    r.commit()
    return os.path.join(config.backupdir, "%s-%s-%s" %
                        (login, share, strftime("%Y%m%d")))

# v Machines ------------------------------------------------------------------

def listDomainMembers():
    return SambaAD().listDomainMembers()

def deleteMachine(name):
    return SambaAD().deleteMachine(name)

def getMachine(name):
    return SambaAD().getMachine(name)

def editMachine(name, description, enabled):
    return SambaAD().editMachine(name, description, enabled)

# v Users ---------------------------------------------------------------------

def userHasSambaAccount(username):
    return username and SambaAD().existsUser(username)

def updateSambaUserPassword(username, password):
    if isinstance(password, xmlrpclib.Binary):
        password = str(password)
    return SambaAD().updateUserPassword(username, password)

def createSambaUser(username, password):
    return SambaAD().createUser(username, password)

def enableSambaUser(username):
    return SambaAD().enableUser(username)

def disableSambaUser(username):
    return SambaAD().disableUser(username)

def deleteSambaUser(username):
    return SambaAD().deleteUser(username)

def userHasSambaEnabled(username):
    return username and SambaAD().isUserEnabled(username)
