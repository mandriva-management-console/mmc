# -*- coding: utf-8; -*-
#
# (c) 2014 Mandriva, http://www.mandriva.com
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
MMC agent Radius plugin.
"""

import copy
import ldap
import logging

import ldap.modlist
from mmc.core.version import scmRevision
from mmc.plugins.base import ldapUserGroupControl
from mmc.support.config import PluginConfig

VERSION = "2.5.87"
APIVERSION = "0:0:0"
REVISION = scmRevision("$Rev$")


def getVersion():
    return VERSION


def getApiVersion():
    return APIVERSION


def getRevision():
    return REVISION


class RadiusConfig(PluginConfig):
    pass


def activate():
    ldapObj = ldapUserGroupControl()
    logger = logging.getLogger()

    config = RadiusConfig("radius")
    if config.disabled:
        logger.warning("Plugin radius: disabled by configuration.")
        return False

    radiusSchema = ['posixAccount', 'radiusprofile']

    for objectClass in radiusSchema:
        schema = ldapObj.getSchema(objectClass)
        if not len(schema):
            logger.error("Radius schema is not available: %s objectClass is \
                          not included in LDAP directory" % objectClass)
            return False

    return True


class UserRadius(ldapUserGroupControl):

    """
    Class to manage the radius attributes of a user
    """

    def __init__(self, uid, conffile=None):
        """
        Class constructor.

        @param uid: User id
        @type uid: str
        """
        ldapUserGroupControl.__init__(self, conffile)
        self.configRadius = RadiusConfig("radius", conffile)
        self.userUid = uid
        self.dn = 'uid=' + uid + ',' + self.baseUsersDN
        self.hooks.update(self.configRadius.hooks)

    def hasRadiusObjectClass(self):
        """
        Return true if the user owns the radiusprofile objectClass.

        @return: return True if the user owns the radiusprofile objectClass.
        @rtype: boolean
        """
        return "radiusprofile" in self.getDetailedUser(self.userUid)["objectClass"]

    def addRadiusObjectClass(self):
        """
        Add the radiusprofile object class to the current user.
        """
        # Get current user entry
        s = self.l.search_s(self.dn, ldap.SCOPE_BASE)
        c, old = s[0]

        new = copy.deepcopy(old)

        if "radiusprofile" not in new["objectClass"]:
            new["objectClass"].append("radiusprofile")
        if "posixAccount" not in new["objectClass"]:
            new["objectClass"].append("posixAccount")

        # Update LDAP
        modlist = ldap.modlist.modifyModlist(old, new)
        self.l.modify_s(self.dn, modlist)

    def delRadiusObjectClass(self):
        """
        Remove the radiusprofile object class from the current user.
        """
        self.removeUserObjectClass(self.userUid, 'radiusprofile')


def hasRadiusObjectClass(uid):
    return UserRadius(uid).hasRadiusObjectClass()


def addRadiusObjectClass(uid):
    UserRadius(uid).addRadiusObjectClass()


def delRadiusObjectClass(uid):
    UserRadius(uid).delRadiusObjectClass()
