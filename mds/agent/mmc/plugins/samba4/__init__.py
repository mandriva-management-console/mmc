# -*- coding: utf-8; -*-
#
# (c) 2014 Mandriva, http://www.mandriva.com/
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
#   Jesús García Sáez <jgarcia@zentyal.com>
#

"""
MDS samba4 plugin for the MMC agent.
"""

import os
import os.path
import logging
import xmlrpclib
from time import strftime
from mmc.core.version import scmRevision
from mmc.plugins.base import BasePluginConfig
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.samba4.audit import AA, AT, PLUGIN_NAME
from mmc.plugins.samba4.config import Samba4Config
from mmc.plugins.samba4.smb_conf import SambaConf
from mmc.plugins.samba4.samba4 import SambaAD
from mmc.support.mmctools import shlaunchBackground, progressBackup

logger = logging.getLogger()

VERSION = "2.5.87"
APIVERSION = "1.0.0"
REVISION = scmRevision("$Rev$")


def getVersion():
    return VERSION


def getApiVersion():
    return APIVERSION


def getRevision():
    return REVISION


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

#     if not os.path.exists(config.init_script):
#         logger.error(config.init_script + " does not exist")
#         return False

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


def createSambaUser(username, password, name, surname):
    return SambaAD().createUser(username, password, name, surname)


def enableSambaUser(username):
    return SambaAD().enableUser(username)


def disableSambaUser(username):
    return SambaAD().disableUser(username)


def deleteSambaUser(username):
    return SambaAD().deleteUser(username)


def userHasSambaEnabled(username):
    return username and SambaAD().isUserEnabled(username)
