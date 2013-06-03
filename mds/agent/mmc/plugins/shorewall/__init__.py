#
# (c) 2012 Mandriva, http://www.mandriva.com
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

"""
MDS shorewall plugin for the MMC agent.
"""

import os
import glob
import logging

from mmc.core.version import scmRevision
from mmc.support.mmctools import ServiceManager
from mmc.plugins.shorewall.io import ShorewallConf
from mmc.plugins.shorewall.config import ShorewallPluginConfig

VERSION = "2.4.3"
APIVERSION = "6:2:4"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

logger = logging.getLogger()

def activate():
    conf = ShorewallPluginConfig('shorewall')

    if conf.disabled:
        logger.warning("Plugin shorewall: disabled by configuration.")
        return False

    files = ['zones', 'interfaces', 'rules', 'policy']
    for file in files:
        path = os.path.join(conf.path, file)
        if not os.path.exists(path):
            logger.error("%s doesn't exists" % path)
            return False

    if len(get_zones(conf.external_zones_names)) == 0 and \
       len(get_zones(conf.internal_zones_names)) == 0:
           logger.error("No external or internal zone defined.")
           return False

    return True


class ShorewallZones(ShorewallConf):

    def __init__(self):
        ShorewallConf.__init__(self, 'zones',
            r'^(?P<name>[\w\d]+)\s+(?P<type>[\w\d]+)$')
        self.read()

    def get(self, type = ""):
        zones = []
        for line in self.get_conf():
            if line[0].startswith(type):
                zones.append(line[0])
        return zones


class ShorewallRules(ShorewallConf):

    def __init__(self):
        ShorewallConf.__init__(self, 'rules',
            r'^(?P<action>[\w\d/]+)\s+(?P<src>[\w\d:.,]+)\s+(?P<dst>[\w\d:.]+)\s*(?P<proto>[\w\d]*)\s*(?P<dst_port>[:\w\d]*)$')
        self.read()

    def add(self, action, src, dst, proto = "", dst_port = ""):
        action = action.split('/')
        if len(action) == 2:
            if not os.path.exists(os.path.join('/usr', 'share', 'shorewall', 'macro.%s' % action[0])) and \
               not os.path.exists(os.path.join('/etc', 'shorewall', 'macro.%s' % action[0])):
                raise ShorewallMacroDoesNotExists("Macro %s does not exists" % action[0])
        action = "/".join(action)
        self.add_line([action, src, dst, proto, dst_port])

    def delete(self, action, src, dst, proto = "", dst_port = ""):
        self.del_line([action, src, dst, proto, dst_port])

    def get(self, action = "", src = "", dst = "", filter = ""):
        rules = []
        for line in self.get_conf():
            use = True
            if action and action not in line[0]:
                use = False
            if src and src not in line[1]:
                use = False
            if dst and dst not in line[2]:
                use = False
            if use:
                rules.append(line)
        return rules


class ShorewallPolicies(ShorewallConf):

    def __init__(self):
        ShorewallConf.__init__(self, 'policy',
            r'^(?P<src>[\w]+)\s+(?P<dst>[\w]+)\s+(?P<policy>ACCEPT|DROP|REJECT)\s*(?P<log>[\w]*)$')
        self.read()

    def get(self, src, dst, filter = ""):
        policies = []
        for line in self.get_conf():
            use = True
            if src and src not in line[0]:
                use = False
            if dst and dst not in line[1]:
                use = False
            if use:
                policies.append(line)
        return policies

    def change(self, src, dst, policy, log = ""):
        policies = self.get(src, dst)
        if policies:
            for p in policies:
                old = p[:]
                new = list(p[:])
                new[2] = policy
                new[3] = log
                self.replace_line(old, new)
            self.write()
            return True
        return False

class ShorewallMasq(ShorewallConf):

    def __init__(self):
        ShorewallConf.__init__(self, 'masq',
            r'^(?P<lan_if>[\w]+)\s+(?P<wan_if>[\w]+)$')
        self.read()

    def get(self):
        return self.get_conf()

    def add(self, wan_if, lan_if):
        return self.add_line([wan_if, lan_if])

    def delete(self, wan_if, lan_if):
        return self.del_line([wan_if, lan_if])


class ShorewallInterfaces(ShorewallConf):

    def __init__(self):
        ShorewallConf.__init__(self, 'interfaces',
            r'^(?P<zone>[\w]+)\s+(?P<if>[\w]+)\s+(?P<options>[\w,=]+)$')
        self.read()

    def get(self, type = ""):
        zones = []
        for line in self.get_conf():
            if line[0].startswith(type):
                zones.append(line)
        return zones


class ShorewallConfig(ShorewallConf):

    def __init__(self):
        ShorewallConf.__init__(self, 'shorewall.conf',
            r'^(?P<option>[^=]+)=(?P<value>.*)',
            '%s=%s')
        self.read()

    def enable_ip_forward(self):
        for old_value in ['Keep', 'No', 'Off']:
            self.replace_line(['IP_FORWARDING', old_value], ['IP_FORWARDING', 'Yes'])

    def disable_ip_forward(self):
        for old_value in ['Keep', 'Yes', 'On']:
            self.replace_line(['IP_FORWARDING', old_value], ['IP_FORWARDING', 'No'])


class ShorewallService(ServiceManager):

    def __init__(self):
        self.config = ShorewallPluginConfig("shorewall")
        ServiceManager.__init__(self, self.config.service["pid"], self.config.service["init"])


class ShorewallMacroDoesNotExists(Exception):
    pass

# XML-RPC methods
def get_zones(type = ""):
    return ShorewallZones().get(type)

def get_zones_interfaces(type = ""):
    return ShorewallInterfaces().get(type)

def get_zones_types():
    conf = ShorewallPluginConfig('shorewall')
    return (conf.internal_zones_names, conf.external_zones_names)

def add_rule(action, src, dst, proto = "", dst_port = ""):
    return ShorewallRules().add(action, src, dst, proto, dst_port)

def del_rule(action, src, dst, proto = "", dst_port = ""):
    return ShorewallRules().delete(action, src, dst, proto, dst_port)

def get_rules(action = "", src = "", dst = "", filter = ""):
    return ShorewallRules().get(action, src, dst, filter)

def get_services():
    conf = ShorewallPluginConfig('shorewall')
    services = [ os.path.basename(m)[6:] for m in glob.glob(os.path.join(conf.macros_path, 'macro.*')) ] + \
               [ os.path.basename(m)[6:] for m in glob.glob(os.path.join(conf.path, '/macro.*')) ]
    services.sort()
    # Remove not allowed macros from the list
    if len(conf.macros_list) > 0:
        for service in services[:]:
            if not service in conf.macros_list:
                services.remove(service)
    return services

def get_policies(src = "", dst = "", filter = ""):
    return ShorewallPolicies().get(src, dst, filter)

def change_policies(src, dst, policy, log = ""):
    return ShorewallPolicies().change(src, dst, policy, log)

def get_masquerade_rules():
    return ShorewallMasq().get()

def del_masquerade_rule(wan_if, lan_if):
    return ShorewallMasq().delete(wan_if, lan_if)

def add_masquerade_rule(wan_if, lan_if):
    return ShorewallMasq().add(wan_if, lan_if)

def enable_ip_forward():
    return ShorewallConfig().enable_ip_forward()

def disable_ip_forward():
    return ShorewallConfig().disable_ip_forward()

def restart_service():
    return ShorewallService().command('restart')
