# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2012 Mandriva, http://www.mandriva.com
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

"""
MDS samba plugin for the MMC agent.
"""

import os
import logging
import ldap
from time import strftime

from mmc.core.version import scmRevision
from mmc.plugins.base import ldapUserGroupControl, BasePluginConfig
from mmc.support.mmctools import cleanFilter, shlaunchBackground, progressBackup
from mmc.core.audit import AuditFactory as AF

from mmc.plugins.samba.audit import AT, AA, PLUGIN_NAME
from mmc.plugins.samba.config import SambaConfig
from mmc.plugins.samba.smb_conf import SambaConf
from mmc.plugins.samba.smb_ldap import SambaLDAP

logger = logging.getLogger()

VERSION = "2.5.0"
APIVERSION = "5:3:4"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

def activate():
    """
     this function define if the module "base" can be activated.
     @return: return True if this module can be activate
     @rtype: boolean
    """
    config = SambaConfig("samba")

    if config.disabled:
        logger.info("samba plugin disabled by configuration.")
        return False

    if config.defaultSharesPath:
        if config.defaultSharesPath.endswith("/"):
            logger.error("Trailing / is not allowed in defaultSharesPath")
            return False
        if not os.path.exists(config.defaultSharesPath):
            logger.error("The default shares path '%s' does not exist" % config.defaultSharesPath)
            return False

    for cpath in config.authorizedSharePaths:
        if cpath.endswith("/"):
            logger.error("Trailing / is not allowed in authorizedSharePaths")
            return False
        if not os.path.exists(cpath):
            logger.error("The authorized share path '%s' does not exist" % cpath)
            return False

    # Verify if samba conf file exist
    conf = config.samba_conf_file
    if not os.path.exists(conf):
        logger.error(conf + " does not exist")
        return False

    # validate smb.conf
    smbconf = SambaConf()
    if not smbconf.validate(conf):
        logger.error("SAMBA configuration file is not valid")
        return False

    # For each share, test if it sharePath exists
    for share in getDetailedShares():
        shareName = share[0]
        infos = shareInfo(shareName)
        if infos:
            sharePath = infos['sharePath']
            if sharePath and not '%' in sharePath and not os.path.exists(sharePath):
                # only show error
                logger.error("The samba share path '%s' does not exist." % sharePath)
        else:
            return False

    try:
        ldapObj = ldapUserGroupControl()
    except ldap.INVALID_CREDENTIALS:
        logger.error("Can't bind to LDAP: invalid credentials.")
        return False

    # Test if the Samba LDAP schema is available in the directory
    try:
         schema = ldapObj.getSchema("sambaSamAccount")
         if len(schema) <= 0:
             logger.error("Samba schema is not included in LDAP directory");
             return False
    except:
        logger.exception("invalid schema")
        return False

    # Verify if init script exist
    init = config.samba_init_script
    if not os.path.exists(init):
        logger.error(init + " does not exist")
        return False

    # If SAMBA is defined as a PDC, make extra checks
    if smbconf.isPdc():
        samba = SambaLDAP()
        # Create SAMBA computers account OU if it doesn't exist
        head, path = samba.baseComputersDN.split(",", 1)
        ouName = head.split("=")[1]
        samba.addOu(ouName, path)
        # Check that a sambaDomainName entry is in LDAP directory
        domainInfos = samba.getDomain()
        # Set domain policy
        samba.setDomainPolicy()
        if not domainInfos:
            logger.error("Can't find sambaDomainName entry in LDAP for domain %s. Please check your SAMBA LDAP configuration." % smbconf.getContent("global", "workgroup"));
            return False
        smbconfbasesuffix = smbconf.getContent("global", "ldap suffix")
        if not smbconfbasesuffix:
            logger.error("SAMBA 'ldap suffix' option is not setted.")
            return False
        if ldap.explode_dn(samba.baseDN) != ldap.explode_dn(smbconfbasesuffix):
            logger.error("SAMBA 'ldap suffix' option is not equal to MMC 'baseDN' option.")
            return False
        # Check that SAMBA and MMC given OU are in sync
        for option in [("ldap user suffix", "baseUsersDN", samba.baseUsersDN), ("ldap group suffix", "baseGroupsDN", samba.baseGroupsDN), ("ldap machine suffix", "baseComputersDN", samba.baseComputersDN)]:
            smbconfsuffix = smbconf.getContent("global", option[0])
            if not smbconfsuffix:
                logger.error("SAMBA '" + option[0] + "' option is not setted")
                return False
            # Do a case insensitive comparison of the corresponding MMC / SAMBA options
            if ldap.explode_rdn(smbconfsuffix)[0].lower() != ldap.explode_rdn(option[2])[0].lower():
                logger.error("SAMBA option '" + option[0] + "' is not equal to MMC '" + option[1] + "' option.")
                return False
        # Check that "ldap delete dn" SAMBA option is set to "No"
        smbconfdeletedn = smbconf.isValueTrue(smbconf.getContent("global", "ldap delete dn"))
        if smbconfdeletedn == 1:
            logger.error("SAMBA option 'ldap delete dn' must be disabled.")
            return False
        # Check that Domain Computers group exists
        # We need it to put a machine account in the right group when joigning it to the domain
        if not samba.getDomainComputersGroup():
            logger.error("Can't find sambaGroupMapping entry in LDAP corresponding to 'Domain Computers' group. Please check your SAMBA LDAP configuration.");
            return False
        # Check that Domain Admins group exists
        if not samba.getDomainAdminsGroup():
            logger.error("Can't find sambaGroupMapping entry in LDAP corresponding to 'Domain Admins' group. Please check your SAMBA LDAP configuration.");
            return False
        # Check that Domain Guests group exists
        if not samba.getDomainGuestsGroup():
            logger.error("Can't find sambaGroupMapping entry in LDAP corresponding to 'Domain Guests' group. Please check your SAMBA LDAP configuration.");
            return False
        # Check that Domain Users group exists
        if not samba.getDomainUsersGroup():
            logger.error("Can't find sambaGroupMapping entry in LDAP corresponding to 'Domain Users' group. Please check your SAMBA LDAP configuration.");
            return False
        # Check that add machine script option is set, and that the given script exist
        addMachineScript = smbconf.getContent("global", "add machine script")
        if not addMachineScript:
            logger.error("SAMBA 'add machine script' option is not set.")
            return False
        else:
            script = addMachineScript.split(" ")[0]
            if not os.path.exists(script):
                logger.error("SAMBA 'add machine script' option is set to a non existing file: " + script)
                return False
        # Issue a warning if NSCD is running
        if os.path.exists("/var/run/nscd.pid") or os.path.exists("/var/run/.nscd_socket") or os.path.exists("/var/run/nscd"):
            logger.warning("Looks like NSCD is installed on your system. You should not run NSCD on a SAMBA server.")
        # Check that os level is set to 255
        oslevel = smbconf.getContent("global", "os level")
        if int(oslevel) < 255:
            logger.debug("Set SAMBA os level to 255.")
            smbconf.setContent("global", "os level", "255")
            smbconf.save()
            reloadSamba()
        try:
            from mmc.plugins.dashboard.manager import DashboardManager
            from mmc.plugins.samba.panel import SambaPanel
            DM = DashboardManager()
            DM.register_panel(SambaPanel("samba"))
        except ImportError:
            pass

    return True

def isSmbAntiVirus():
    return os.path.exists(SambaConfig("samba").av_so)

def isAuthorizedSharePath(path):
    return SambaConf(SambaConfig("samba").samba_conf_file).isAuthorizedSharePath(path)

def getDefaultSharesPath():
    """
    @return: the default SAMBA share path
    @rtype: str
    """
    return SambaConfig("samba").defaultSharesPath

def getDetailedShares():
    """Get a complete array of information about all shares"""
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    resList=smbObj.getDetailedShares()
    return resList

def getACLOnShare(name):
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    return smbObj.getACLOnShare(name)

def getAdminUsersOnShare(name):
    return SambaConf(SambaConfig("samba").samba_conf_file).getAdminUsersOnShare(name)

def getDomainAdminsGroup():
    return SambaLDAP().getDomainAdminsGroup()

def setDomainPolicy():
    return SambaLDAP().setDomainPolicy()

def isBrowseable(name):
    return SambaConf(SambaConfig("samba").samba_conf_file).isBrowseable(name)

def addShare(name, path, comment, usergroups, users, permAll, admingroups, browseable = True, av = 0, customparameters = None):
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    smbObj.addShare(name, path, comment, usergroups, users, permAll, admingroups, browseable, av, customparameters)
    smbObj.save()

def modShare(name, path, comment, usergroups, users, permAll, admingroups, browseable = True, av = 0, customparameters = None):
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    smbObj.addShare(name, path, comment, usergroups, users, permAll, admingroups, browseable, av, customparameters, True)
    smbObj.save()

def delShare(name, file):
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    smbObj.delShare(name, file)
    smbObj.save()
    return 0

def shareInfo(name):
    """get an array of information about a share"""
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    return smbObj.shareInfo(name)

def shareCustomParameters(name):
    """get an array of additionnal params about a share"""
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    return smbObj.shareCustomParameters(name)

def getSmbInfo():
    """get main information of global section"""
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    return smbObj.getSmbInfo()

def smbInfoSave(options):
    """save information about global section"""
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    return smbObj.smbInfoSave(options)

def isPdc():
    try: smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    except: raise Exception("Can't open SAMBA configuration file")
    return smbObj.isPdc()

def isProfiles():
    """ check if global profiles are setup """
    smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
    return smbObj.isProfiles()

def backupShare(share, media, login):
    """
    Launch as a background process the backup of a share
    """
    r = AF().log(PLUGIN_NAME, AA.SAMBA_BACKUP_SHARE, [(share, AT.SHARE), (login, AT.USER)], media)
    config = BasePluginConfig("base")
    cmd = os.path.join(config.backuptools, "backup.sh")
    if share == "homes":
        # FIXME: Maybe we should have a configuration directive to tell that
        # all users home are stored into /home
        savedir = "/home/"
    else:
        smbObj = SambaConf(SambaConfig("samba").samba_conf_file)
        savedir = smbObj.getContent(share, "path")
    # Run backup process in background
    shlaunchBackground(cmd + " " + share + " " + savedir + " " + config.backupdir + " " + login + " " + media + " " + config.backuptools, "backup share " + share, progressBackup)
    r.commit()
    return os.path.join(config.backupdir, "%s-%s-%s" % (login, share, strftime("%Y%m%d")))

def restartSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA_RESTART_SAMBA)
    shlaunchBackground(SambaConfig("samba").samba_init_script+' restart')
    r.commit()
    return 0;

def reloadSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA_RELOAD_SAMBA)
    shlaunchBackground(SambaConfig("samba").samba_init_script+' reload')
    r.commit()
    return 0;

def addSmbAttr(uid, password):
    return SambaLDAP().addSmbAttr(uid, password)

def isSmbUser(uid):
    return SambaLDAP().isSmbUser(uid)

def userPasswdHasExpired(uid):
    return SambaLDAP().userPasswdHasExpired(uid)

def changeUserPasswd(uid, password, oldpasswd = None, bind = False):
    return SambaLDAP().changeUserPasswd(uid, password, oldpasswd, bind)

def changeSambaAttributes(uid, attributes):
    return SambaLDAP().changeSambaAttributes(uid, attributes)

def changeUserPrimaryGroup(uid, groupName):
    return SambaLDAP().changeUserPrimaryGroup(uid, groupName)

def delSmbAttr(uid):
    return SambaLDAP().delSmbAttr(uid)

def isEnabledUser(uid):
    return SambaLDAP().isEnabledUser(uid)

def isLockedUser(uid):
    return SambaLDAP().isLockedUser(uid)

def enableUser(uid):
    return SambaLDAP().enableUser(uid)

def disableUser(uid):
    return SambaLDAP().disableUser(uid)

def lockUser(uid):
    return SambaLDAP().lockUser(uid)

def unlockUser(uid):
    return SambaLDAP().unlockUser(uid)

def makeSambaGroup(group):
    return SambaLDAP().makeSambaGroup(group)

def getSmbStatus():
    return SambaConf().getSmbStatus()

def getConnected():
    return SambaConf().getConnected()

# create a machine account
def addMachine(name, comment, addMachineScript = False):
    return SambaLDAP().addMachine(name, comment, addMachineScript)

def delMachine(name):
    return SambaLDAP().delMachine(name)

def getMachine(name):
    return SambaLDAP().getMachine(name)

def changeMachine(name, options):
    return SambaLDAP().changeMachine(name, options)

def getMachinesLdap(searchFilter= ""):
    ldapObj = SambaLDAP()
    searchFilter = cleanFilter(searchFilter)
    return ldapObj.searchMachine(searchFilter)
