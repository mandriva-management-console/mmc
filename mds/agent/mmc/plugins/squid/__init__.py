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
import ldap
from ConfigParser import NoSectionError, NoOptionError

from mmc.core.version import scmRevision
from mmc.core.audit import AuditFactory as AF
from mmc.support.config import PluginConfig, ConfigException
from mmc.plugins.squid.audit import AT, AA, PLUGIN_NAME
from mmc.plugins.base import createGroup, changeGroupDescription

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

    #try:
    config.check()
    #except Exception, e:
        #logger.error("Squid configuration error: %s" % str(e))
        #return False

    return True


def reloadSquid():
    return ManageList().reloadSquid()


def getStatutProxy():
    return ManageList().getStatutProxy()


def add_list_item(list, item):
    ManageList().add_list_item(list, item)

def del_list_item(list, item):
    ManageList().del_list_item(list, item)

    
def get_list(list):
    return ManageList().get_list(list)    

class ProxyConfig(PluginConfig):

    def readConf(self):

        PluginConfig.readConf(self)

        try: self.squidBinary = self.get("squid", "squidBinary")
        except (NoSectionError, NoOptionError): self.squidBinary = "/usr/sbin/squid"

        try: self.squidRules = self.get("squid", "squidRules")
        except (NoSectionError, NoOptionError): self.squidRules = "/etc/squid/rules"

        try: self.squidMasterGroup = self.get("squid", "squidMasterGroup")
        except (NoSectionError, NoOptionError): self.squidMasterGroup = "/etc/squid/rules/group_internet"

        try: self.normalBlackList = self.get("squid", "normalBlackList")
        except (NoSectionError, NoOptionError): self.normalBlackList = "/etc/squid/rules/group_internet/normal_blacklist.txt"

        try: self.normalWhiteList = self.get("squid", "normalWhiteList")
        except (NoSectionError, NoOptionError): self.normalWhiteList = "/etc/squid/rules/group_internet/normal_whitelist.txt"

        try: self.normalBlackExt = self.get("squid", "normalBlackExt")
        except (NoSectionError, NoOptionError): self.normalBlackExt = "/etc/squid/rules/group_internet/normal_blacklist_ext.txt"

        try: self.timeDay = self.get("squid", "timeDay")
        except (NoSectionError, NoOptionError): self.timeDay = "/etc/squid/rules/group_internet/time_day.txt"

        try: self.normalMachList = self.get("squid", "normalMachList")
        except (NoSectionError, NoOptionError): self.normalMachList = "/etc/squid/rules/group_internet/allow_machines.txt"

        try: self.sargBinary = self.get("squid", "sargBinary")
        except (NoSectionError, NoOptionError): self.sargBinary = "/usr/sbin/sarg"

        try: self.squidInit = self.get("squid", "squidInit")
        except (NoSectionError, NoOptionError): self.squidInit = "/etc/init.d/squid"

        try: self.squidPid = self.get("squid", "squidPid")
        except (NoSectionError, NoOptionError): self.squidPid = "/var/run/squid.pid"

        try: self.groupMaster = self.get("squid", "groupMaster")
        except (NoSectionError, NoOptionError): self.groupMaster = "InternetMaster"
        self.groupMasterDesc = "Free access to Internet and downloads"

        try: self.groupFiltered = self.get("squid", "groupFiltered")
        except (NoSectionError, NoOptionError): self.groupFiltered = "Internet"
        self.groupFilteredDesc = "Filtered access to Internet and downloads"


    def check(self):
        if not os.path.exists(self.squidBinary):
            raise ConfigException("Can't find squid binary: %s" % self.squidBinary)

        if not os.path.exists(self.sargBinary):
            raise ConfigException("Can't find sarg binary: %s" % self.sargBinary)

        for dir in (self.squidRules, self.squidMasterGroup):
            if not os.path.exists(dir):
                logger.info("Creating %s" % dir)
                os.makedirs(dir)

        for list in (self.normalBlackList, self.normalWhiteList, self.normalBlackExt,
                     self.timeDay, self.normalMachList):
            if not os.path.exists(list):
                logger.info("Creating %s" % list)
                open(list, "w+").close()

        for group, desc in ((self.groupMaster, self.groupMasterDesc),
                        (self.groupFiltered, self.groupFilteredDesc)):
            try:
                createGroup(group)
                changeGroupDescription(group, desc)
                logger.info("Group %s created." % group)
            except ldap.ALREADY_EXISTS:
                pass



####################################################################
#           Class to persist and manipulate squid files
####################################################################

class ManageList(object):

    def __init__(self):
        """
        For easier modification Arrays are always loaded

        """
    
        self.config = ProxyConfig("squid")
        self.lists = {
                        'blacklist':List(self.config.normalBlackList),
                        'whitelist':List(self.config.normalWhiteList),
                        'machlist':List(self.config.normalMachList),
                        'extlist':List(self.config.normalBlackExt),
                        'timelist':List(self.config.timeDay),
	                'blacklist':List(self.config.normalBlackList),
        }
        self.squid = self.config.squidBinary
        self.squidInit = self.config.squidInit
        self.squidPid = self.config.squidPid
        self.sargBinary = self.config.sargBinary

        ''' add and del item in a list'''
   
    def add_list_item(self,list, item):
        self.lists[list].add_item(item)

    def del_list_item(self,list, item):
        self.lists[list].del_item(item)

    def get_list(self, list):
        return self.lists[list].get_list()
    
    def reloadSquid(self):
        try:
            from mmc.plugins.services.manager import ServiceManager
            ServiceManager().reload("squid")
        except ImportError:
            from mmc.support.mmctools import ServiceManager
            SM = ServiceManager(self.squidInit, self.squidPid)
            SM.reload()

    def getStatutProxy(self):
        res={}
        res['squid']=0

        psout = os.popen('ps ax | grep squid | grep -v grep','r')
        try:
            tmp=psout.read()
        except:
            return res

        for a in tmp.split("\n"):
            if 'squid' in a : res['squid'] = 1
        psout.close()
        return res


class List(object):

	def __init__(self, listPath):
	    self.path = listPath
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

	def get_list(self):
	    arr = []
	    f = open(self.path)
	    for row in f:
                    row = row.strip()
                    arr.append(row)
            f.close()
	    return arr
