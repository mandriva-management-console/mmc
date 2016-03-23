# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
#
# $Id$
#
# This file is part of Management Console.
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
MDS proxy plugin for the MMC agent.
"""

import os
import logging
from mmc.core.version import scmRevision
from mmc.support import mmctools
from mmc.support.config import PluginConfig, ConfigException
from ConfigParser import NoSectionError, NoOptionError

from mmc.core.audit import AuditFactory as AF
from mmc.plugins.proxy.audit import AT, AA, PLUGIN_NAME


VERSION = "2.5.95"
APIVERSION = "1:1:0"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

def activate():
    """
     this function define if the module "proxy" can be activated.
     @return: return True if this module can be activate
     @rtype: boolean
    """
    config = ProxyConfig("proxy")
    logger = logging.getLogger()
    if config.disabled:
        msg = "disabled by configuration"        
        logger.warning("Plugin proxy: " + msg + ".")
        return False
    result = True
    msg = ""
    try:
        config.check()
    except ConfigException, ce:
        msg = str(ce)
        result = False
    except Exception, e:
        msg = str(e)
        result = False
    if not result:
        logger.warning("Plugin proxy: " + msg + ".")
    return result

def getBlackList():
    return Blacklist().getBlacklist()

def addBlackList(elt):
    return Blacklist().addBlacklist(elt)

def delBlackList(elt):
    return Blacklist().delBlacklist(elt)

def restartSquid():
    return Blacklist().restartSquid()

def getStatutProxy():
    return Blacklist().getStatutProxy()

class ProxyConfig(PluginConfig):

    def readConf(self):
        PluginConfig.readConf(self)
        try: self.sgBinary = self.get("squidguard", "path")
        except (NoSectionError, NoOptionError): pass
        try: self.sgBlacklist = self.get("squidguard", "blacklist")
        except (NoSectionError, NoOptionError): pass
        try: self.squidReload = self.get("squidguard", "scriptReload")
        except (NoSectionError, NoOptionError): pass
        try: self.squidUser = self.get("squidguard", "user")
        except (NoSectionError, NoOptionError): pass
        try: self.squidGroup = self.get("squidguard", "group")
        except (NoSectionError, NoOptionError): pass

    def setDefault(self):
        PluginConfig.setDefault(self)
        self.squidReload = "/etc/init.d/squid reload"
        self.squidUser = "proxy"
        self.squidGroup = "proxy"
        self.sgBinary = "/usr/bin/squidGuard"
        self.sgBlacklist = "/var/lib/squidguard/db/bad.destdomainlist"

    def check(self):
        if not os.path.exists(self.sgBinary):
            raise ConfigException("Can't find squidguard binary: %s" % self.sgBinary)
        # Try to get squidguard version string
        code, out, err = mmctools.shlaunch("%s -v" % self.sgBinary)
        if code != 0:
            raise ConfigException("Can't start %s -v: %s (%s)'" % (self.sgBinary, "\n".join(err), str(code)))
        self.sgVersion = err.strip()
        if not os.path.exists(self.sgBlacklist):
            raise ConfigException("Can't find squidguard blacklist: %s" % self.sgBlacklist)


####################################################################
#           Blacklist class for squid
####################################################################

class Blacklist:

    PLACEHOLDER = "placeholder"

    def __init__(self):
        """
        Load bad.destdomainlist in self.contentArr.
        For easier modification bad.destdomainlist is always loaded
        in self.contentArr.
        """
        self.config = ProxyConfig("proxy")
        self.blacklist = self.config.sgBlacklist
        self.squidguard = self.config.sgBinary
        self.contentArr = []
        f = open(self.blacklist)
        for line in f:
            line = line.strip()
            if line and line not in self.contentArr:
                self.contentArr.append(line)
        f.close()
        if not self.PLACEHOLDER in self.contentArr:
            self.contentArr.append(self.PLACEHOLDER)

    def restartSquid(self):
        """
        Compile the blacklist database and reload squid.
        """
        r = AF().log(PLUGIN_NAME, AA.PROXY_RESTART_SQUID)
        mmctools.shlaunch(self.config.sgBinary + ' -C ' + self.config.sgBlacklist)
        mmctools.shlaunch("chown " + self.config.squidGroup + "." + self.config.squidUser + " " + self.config.sgBlacklist + "*")

        psout = os.popen(self.config.squidReload, 'r')
        read = psout.read()

        if psout.close(): read = "error reloading squid"
        r.commit()

        return read

    def getStatutProxy(self):
        """
        Return proxy status
        Return if process squid and squidGuard are launched
        """
        # FIXME: works well but ugly
        res={}
        res['squid']=0
        res['squidGuard']=0

        psout = os.popen('ps ax | grep squid | grep -v grep','r')
        try:
            tmp=psout.read()
        except:
            return res

        for a in tmp.split("\n"):
            if 'squid' in a : res['squid'] = 1
            if 'squidGuard' in a : res['squidGuard'] = 1
        psout.close()
        return res

    def getBlacklist(self):
        """
        Return blacklist content, without the placeholder element which must
        not be seen.
        """
        ret = self.contentArr[:]
        ret.remove(self.PLACEHOLDER)
        return ret

    def saveBlacklist(self):
        """save self.contentArr in bad.destdomainlist"""
        f = open(self.config.sgBlacklist, 'w')
        for elt in self.contentArr:
            f.write(elt + '\n')
        f.close()
        return 0

    def addBlacklist(self, elt):
        """Add an element to the blacklist"""
        r = AF().log(PLUGIN_NAME, AA.PROXY_ADD_BLACKLIST, [(elt, AT.BLACKLIST)])
        if not elt in self.contentArr:
            self.contentArr.append(elt)
        self.saveBlacklist()
        r.commit()

    def delBlacklist(self, elt):
        """Remove an element from the blacklist"""
        r = AF().log(PLUGIN_NAME, AA.PROXY_DEL_BLACKLIST, [(elt, AT.BLACKLIST)])
        if elt in self.contentArr:
            self.contentArr.remove(elt)
        self.saveBlacklist()
        r.commit()
