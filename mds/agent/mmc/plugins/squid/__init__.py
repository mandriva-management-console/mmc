# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
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
MDS squid plugin for the MMC agent.
"""

import os
import logging
from ConfigParser import NoSectionError, NoOptionError

from mmc.core.version import scmRevision
from mmc.support.config import PluginConfig, ConfigException
from mmc.plugins.base import createGroup, changeGroupDescription, getGroupEntry
#from mmc.core.audit import AuditFactory as AF
#from mmc.plugins.squid.audit import AT, AA, PLUGIN_NAME

logger = logging.getLogger()

VERSION = "0.0.1"
APIVERSION = "1:1:0"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

def activate():
    """
    this function define if the module "squid" can be activated.
    @return: return True if this module can be activate
    @rtype: boolean
    """
    config = ProxyConfig("squid")
    if config.disabled:
        logger.warning("Plugin squid: disabled by configuration.")
        return False

    config.check()

    return True

# Exported XML-RPC methods
def reload_squid():
    return ManageList().reload_squid()

def add_list_item(list, item):
    return ManageList().add_list_item(list, item)

def del_list_item(list, item):
    return ManageList().del_list_item(list, item)

def get_list(list):
    return ManageList().get_list(list)

def get_service_name():
    config = ProxyConfig("squid")
    return config.squidInit.split("/").pop()


class ProxyConfig(PluginConfig):

    def readConf(self):

        PluginConfig.readConf(self)

        try: self.squidBinary = self.get("squid", "squidBinary")
        except (NoSectionError, NoOptionError): self.squidBinary = "/usr/sbin/squid"

        try: self.squidRules = self.get("squid", "squidRules")
        except (NoSectionError, NoOptionError): self.squidRules = "/etc/squid/rules"

        try: self.blacklist = self.get("squid", "blacklist")
        except (NoSectionError, NoOptionError): self.blacklist = os.path.join(self.squidRules, "blacklist.txt")

        try: self.whitelist = self.get("squid", "whitelist")
        except (NoSectionError, NoOptionError): self.whitelist = os.path.join(self.squidRules, "whitelist.txt")

        try: self.blacklist_ext = self.get("squid", "blacklist_ext")
        except (NoSectionError, NoOptionError): self.blacklist_ext = os.path.join(self.squidRules, "blacklist_ext.txt")

        try: self.timeranges = self.get("squid", "timeranges")
        except (NoSectionError, NoOptionError): self.timeranges = os.path.join(self.squidRules, "timeranges.txt")

        try: self.machines = self.get("squid", "machines")
        except (NoSectionError, NoOptionError): self.machines = os.path.join(self.squidRules, "machines.txt")

        try: self.sargBinary = self.get("squid", "sargBinary")
        except (NoSectionError, NoOptionError): self.sargBinary = "/usr/sbin/sarg"

        try: self.squidInit = self.get("squid", "squidInit")
        except (NoSectionError, NoOptionError): self.squidInit = "/etc/init.d/squid"

        try: self.squidPid = self.get("squid", "squidPid")
        except (NoSectionError, NoOptionError): self.squidPid = "/var/run/squid.pid"

        try: self.groupMaster = self.get("squid", "groupMaster")
        except (NoSectionError, NoOptionError): self.groupMaster = "InternetMaster"
        self.groupMasterDesc = "Full Internet access"

        try: self.groupFiltered = self.get("squid", "groupFiltered")
        except (NoSectionError, NoOptionError): self.groupFiltered = "InternetFiltered"
        self.groupFilteredDesc = "Filtered Internet access"

    def check(self):
        if not os.path.exists(self.squidBinary):
            raise ConfigException("Can't find squid binary: %s" % self.squidBinary)

        if not os.path.exists(self.sargBinary):
            raise ConfigException("Can't find sarg binary: %s" % self.sargBinary)

        if not os.path.exists(self.squidRules):
            logger.info("Creating %s" % self.squidRules)
            os.makedirs(self.squidRules)

        for list in (self.blacklist, self.whitelist, self.blacklist_ext,
                     self.timeranges, self.machines):
            if not os.path.exists(list):
                logger.info("Creating %s" % list)
                open(list, "w+").close()

        for group, desc in ((self.groupMaster, self.groupMasterDesc),
                        (self.groupFiltered, self.groupFilteredDesc)):
            if len(getGroupEntry(group)) == 0:
                createGroup(group)
                changeGroupDescription(group, desc)
                logger.info("Group %s created." % group)


class ManageList(object):
    """ Manage squid lists """

    def __init__(self):
        self.config = ProxyConfig("squid")
        self.lists = {'blacklist': List(self.config.blacklist),
                      'whitelist': List(self.config.whitelist),
                      'machines': List(self.config.machines),
                      'blacklist_ext': List(self.config.blacklist_ext),
                      'timeranges': List(self.config.timeranges)}
        self.squid = self.config.squidBinary
        self.squidInit = self.config.squidInit
        self.squidPid = self.config.squidPid
        self.sargBinary = self.config.sargBinary

    def add_list_item(self,list, item):
        """
        Add item in a list
        """
        self.lists[list].add_item(item)

    def del_list_item(self,list, item):
        """
        Delete item in a list
        """
        self.lists[list].del_item(item)

    def get_list(self, list):
        return self.lists[list].get()

    def reload_squid(self):
        """
        Reload squid service

        Uses the old API
        """
        from mmc.support.mmctools import ServiceManager
        SM = ServiceManager(self.squidInit, self.squidPid)
        SM.reload()


class List(object):

    def __init__(self, list_path):
        self.path = list_path
        self.list = []
        self.read()

    def read(self):
        f = open(self.path)
        for line in f:
            line = line.strip()
            if line and line not in self.list:
                self.list.append(line)
        f.close()

    def save(self, path):
        f = open(path, 'w')
        for i in self.list:
            f.write(i + '\n')
        f.close()

    def add_item(self, item):
        if not item in self.list:
            self.list.append(item)
        self.save(self.path)

    def del_item(self, item):
        if item in self.list:
            self.list.remove(item)
        self.save(self.path)

    def get(self):
        arr = []
        f = open(self.path)
        for row in f:
            row = row.strip()
            arr.append(row)
        f.close()
        return arr
