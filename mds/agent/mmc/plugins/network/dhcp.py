# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2009 Mandriva, http://www.mandriva.com
#
# $Id$
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
DHCP related methods and classes for the network plugin.
"""

import ldap
from ldap.dn import str2dn
import re
from mmc.plugins.base import ldapUserGroupControl, LogView
from tools import ipNext, ipInRange
from mmc.support.mmctools import ServiceManager
import mmc.plugins.network
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.network.audit import AT, AA, PLUGIN_NAME
import logging

logger = logging.getLogger()

class Dhcp(ldapUserGroupControl):

    def __init__(self, conffile = None, conffilebase = None):
        ldapUserGroupControl.__init__(self, conffilebase)
        self.configDhcp = mmc.plugins.network.NetworkConfig("network", conffile)

    # DHCP options management (line with "options name value;")

    def getObjectOptions(self, dn):
        try:
            ret = self.l.search_s(dn, ldap.SCOPE_BASE, "(objectClass=dhcpOptions)", ["dhcpOption"])[0][1]["dhcpOption"]
        except KeyError:
            ret = []
        return ret

    def setObjectOption(self, dn, option, value):
        # If value == "", remove the option
        if not value.strip('"'): value = None
        options = self.getObjectOptions(dn)
        toremove = None
        for oldoption in options:
            optname, optvalue = oldoption.split(" ", 1)
            if option == optname:
                toremove = oldoption
        if toremove: options.remove(toremove)
        if value: options.append(option + " " + str(value))
        if not options:
            try:
                self.l.modify_s(dn, [(ldap.MOD_DELETE, "dhcpOption", None)])
            except ldap.NO_SUCH_ATTRIBUTE:
                pass
        else:
            self.l.modify_s(dn, [(ldap.MOD_REPLACE, "dhcpOption", options)])

    # DHCP statements management (line with "name value;")

    def getObjectStatements(self, dn):
        try:
            ret = self.l.search_s(dn, ldap.SCOPE_BASE, "(objectClass=*)", ["dhcpStatements"])[0][1]["dhcpStatements"]
        except KeyError:
            ret = []
        return ret

    def setObjectStatement(self, dn, option, value):
        options = self.getObjectStatements(dn)
        toremove = None
        for oldoption in options:
            if option in oldoption:
                toremove = oldoption
        if toremove: options.remove(toremove)
        if value:
            tmp = option + " " + str(value)
            options.append(tmp.strip())
        if not options:
            try:
                self.l.modify_s(dn, [(ldap.MOD_DELETE, "dhcpStatements", None)])
            except ldap.NO_SUCH_ATTRIBUTE:
                pass
        else:
            self.l.modify_s(dn, [(ldap.MOD_REPLACE, "dhcpStatements", options)])

    # DHCP service config management

    def addServiceConfig(self, serviceName):
        """
        Add a DHCP service config container entry in directory
        """
        serviceDN = "cn=" + serviceName + "," + self.configDhcp.dhcpDN
        entry = {
            "cn" : serviceName,
            "dhcpPrimaryDN" : self.configDhcp.dhcpDN,
            "objectClass" : ["top", "dhcpService"]
            }
        attributes=[ (k,v) for k,v in entry.items() ]
        self.l.add_s(serviceDN, attributes)

    def getServices(self):
        """
        Return all available DHCP service config containers
        """
        return self.l.search_s(self.configDhcp.dhcpDN, ldap.SCOPE_SUBTREE, "(objectClass=dhcpService)", None)

    def getService(self, serviceName = "DHCP config"):
        """
        Return a DHCP service entry
        """
        for service in self.getServices():
            if service[1]["cn"][0] == serviceName:
                return service
        raise DhcpError("DHCP service %s does not exists" % serviceName)

    def getServiceConfigStatements(self):
        """
        Return dhcpStatements from DHCP service config
        """
        try:
            ret = self.getService()[1]["dhcpStatements"]
        except KeyError:
            ret = []
        return ret

    def setServiceConfigStatement(self, option, value):
        """
        Set dhcpStatement option on DHCP service config
        """
        serviceDN = self.getService()[0]
        self.setObjectStatement(serviceDN, option, value)

    # DHCP server management

    def getServers(self):
        """
        Return all available DHCP server config containers
        """
        return self.l.search_s(self.configDhcp.dhcpDN, ldap.SCOPE_SUBTREE, "(objectClass=dhcpServer)", None)

    def getServer(self, serverName):
        for server in self.getServers():
            if server[1]["cn"][0] == serverName:
                return server
        raise DhcpError("DHCP server %s does not exists" % serverName)

    def addServer(self, serverName, serviceName = None):
        """
        Add a DHCP server in directory
        """
        if serviceName: raise DhcpError("Not implemented")
        else:
            serviceDN = self.getService()[0]
        serverDN = "cn=" + serverName + "," + self.configDhcp.dhcpDN
        entry = {
            "cn" : serverName,
            "dhcpServiceDN" : serviceDN,
            "objectClass" : ["top", "dhcpServer", "dhcpOptions"],
            "dhcpOption" : "local-pac-server code 252 = text"
            }
        attributes=[ (k,v) for k,v in entry.items() ]
        self.l.add_s(serverDN, attributes)

    def addSecondaryServer(self, serverName, serviceName = None):
        """
        Add a secondary DHCP server for failover on service
        """
        if serviceName: raise DhcpError("Not implemented")

        serviceData = self.getService()[1]
        # Check there isn't any secondary servers already
        if "dhcpSecondaryDN" in serviceData:
            raise DhcpError("A secondary server is already configured.")
        # Check server is not primary
        try:
            serverDN = self.getServer(serverName)[0]
        except DhcpError:
            serverDN = False
        if serverDN and serviceData["dhcpPrimaryDN"][0] == serverDN:
            raise DhcpError("Server %s already set as primary." % serverName)
        # Add the container if not exists
        try:
            self.addServer(serverName)
            logger.info("The secondary DHCP server '%s' was added." % serverName)
        except ldap.ALREADY_EXISTS:
            pass
        self.setServiceServerStatus(serverName, "secondary")

    def delSecondaryServer(self, serviceName = None):
        """
        Remove the secondary DHCP server
        """
        if serviceName: raise DhcpError("Not implemented")
        serviceDN = self.getService()[0]
        serviceData = self.getService()[1]
        if 'dhcpSecondaryDN' in serviceData:
            secondaryDN = serviceData['dhcpSecondaryDN'][0]
            self.l.delete_s(secondaryDN)
            self.l.modify_s(serviceDN, [(ldap.MOD_DELETE, "dhcpSecondaryDN", secondaryDN)])

    def updateSecondaryServer(self, serverName, serviceName = None):
        """
        Update the secondary DHCP server
        """
        if serviceName: raise DhcpError("Not implemented")
        self.delSecondaryServer()
        self.addSecondaryServer(serverName)

    def setServiceServerStatus(self, serverName, type, serviceName = None):
        """
        Set primary/secondary servers for DHCP service
        """
        if serviceName: raise DhcpError("Not implemented")
        if type not in ["primary", "secondary"]:
            raise DhcpError("%s is not a valid type (primary or secondary)" % type)

        serverDN = self.getServer(serverName)[0]
        serviceDN = self.getService()[0]
        if serverDN and serviceDN:
            self.l.modify_s(serviceDN, [(ldap.MOD_REPLACE, "dhcp%sDN" % type.capitalize(), serverDN)])

    def setFailoverConfig(self, masterIp, slaveIp, serverPort = 647, peerPort = 647,
        delay = 30, update = 10, balance = 3, mclt = 1800, split = 128):
        """
        Setup the failover configuration on servers
        """
        serviceData = self.getService()[1]
        if 'dhcpPrimaryDN' in serviceData and 'dhcpSecondaryDN' in serviceData:
            primaryDN = serviceData['dhcpPrimaryDN'][0]
            secondaryDN = serviceData['dhcpSecondaryDN'][0]
            primaryName = str2dn(primaryDN)[0][0][1]
            secondaryName = str2dn(secondaryDN)[0][0][1]
            self.setServerFailover(primaryDN, primaryName, "primary", masterIp, slaveIp,
                int(serverPort), int(peerPort), int(delay), int(update), int(balance), int(mclt), int(split))
            self.setServerFailover(secondaryDN, secondaryName, "secondary", slaveIp, masterIp,
                int(peerPort), int(serverPort), int(delay), int(update), int(balance), int(mclt), int(split))
        else:
            return False

    def setServerFailover(self, serverDN, serverName, type, serverIp, peerIp,
            serverPort, peerPort, delay, update, balance, mclt, split):
        """
        Set failover configuration on server
        """
        # failover configuration template
        failover_config = """"dhcp-failover" { %s; address %s; port %i; peer address %s; peer port %i; max-response-delay %i; max-unacked-updates %i; """ % (type, serverIp, serverPort, peerIp, peerPort, delay, update)
        if type == "primary":
            failover_config += "load balance max seconds %i; mclt %i; split %i; }" % (balance, mclt, split)
        else:
            failover_config += "}"
        # apply configuration
        self.setObjectStatement(serverDN, 'failover peer', failover_config)
        self.setObjectStatement(serverDN, 'server-identifier', serverName)
        self.setPoolFailover()

    def getFailoverDefaultValues(self):
        """
        Return usual settings for DHCP failover
        """
        return { 'primaryPort': ['647'], 'secondaryPort': ['647'],
                 'delay': ['30'], 'update': ['10'], "balance": ['3'],
                 'mclt': ['1800'], "split": ['128'] }

    def getFailoverConfig(self):
        """
        Return failover configuration of server
        """
        serviceData = self.getService()[1]
        primaryDN = False
        if 'dhcpPrimaryDN' in serviceData:
            primaryDN = serviceData['dhcpPrimaryDN'][0]
            primaryName = str2dn(primaryDN)[0][0][1]
        secondaryDN = False
        if 'dhcpSecondaryDN' in serviceData:
            secondaryDN = serviceData['dhcpSecondaryDN'][0]
            secondaryName = str2dn(secondaryDN)[0][0][1]
        if primaryDN and secondaryDN:
            pattern = 'failover.*address (?P<primaryIp>[0-9.]+); port (?P<primaryPort>[0-9]+); peer address (?P<secondaryIp>[0-9.]+); peer port (?P<secondaryPort>[0-9]+); max-response-delay (?P<delay>[0-9]+); max-unacked-updates (?P<update>[0-9]+); load balance max seconds (?P<balance>[0-9]+); mclt (?P<mclt>[0-9]+); split (?P<split>[0-9]+);'
            for statement in self.getObjectStatements(primaryDN):
                m = re.match(pattern, statement)
                if m:
                    return { 'primary': [primaryName], 'secondary': [secondaryName],
                             'primaryIp': [m.group("primaryIp")], 'secondaryIp': [m.group("secondaryIp")],
                             'primaryPort': [m.group("primaryPort")], 'secondaryPort': [m.group("secondaryPort")],
                             'delay': [m.group("delay")], 'update': [m.group("update")], "balance": [m.group("balance")],
                             'mclt': [m.group("mclt")], "split": [m.group("split")] }
            return dict({ 'primary': [primaryName], 'secondary': [secondaryName] }.items() + self.getFailoverDefaultValues().items())
        elif primaryDN:
            return dict({ 'primary': [primaryName] }.items() + self.getFailoverDefaultValues().items())
        else:
            return self.getFailoverDefaultValues()

    def delFailoverConfig(self):
        """
        Remove failover configuration
        """
        serviceData = self.getService()[1]
        if 'dhcpPrimaryDN' in serviceData and 'dhcpSecondaryDN' in serviceData:
            primaryDN = serviceData['dhcpPrimaryDN'][0]
            secondaryDN = serviceData['dhcpSecondaryDN'][0]
        for serverDN in [primaryDN, secondaryDN]:
            self.setObjectStatement(serverDN, 'failover peer', '')
            self.setObjectStatement(serverDN, 'server-identifier', '')
        self.delPoolFailover()

    def setPoolFailover(self, pool = None):
        """
        Activate failover on pool(s)
        """
        pools = self.getPool(pool)
        for poolDN, poolData in pools:
            self.setObjectStatement(poolDN, 'failover peer', '"dhcp-failover"')
            # BOOTP not compatible with failover
            self.setObjectStatement(poolDN, 'deny', 'dynamic bootp clients')

    def delPoolFailover(self, pool = None):
        """
        Deactivate failover on pool(s)
        """
        pools = self.getPool(pool)
        for poolDN, poolData in pools:
            self.setObjectStatement(poolDN, 'failover peer', '')
            self.setObjectStatement(poolDN, 'deny', '')

    # DHCP subnet management

    def getSubnets(self, filt = None):
        filt = filt.strip()
        if not filt: filt = "*"
        else: filt = "*" + filt + "*"
        return self.l.search_s(self.configDhcp.dhcpDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpSubnet)(cn=%s))" % filt, None)

    def getSubnet(self, subnet = None):
        if not subnet: subnet = "*"
        return self.l.search_s(self.configDhcp.dhcpDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpSubnet)(cn=%s))" % subnet, None)

    def getSubnetOptions(self, subnet):
        try:
            ret = self.getSubnet(subnet)[0][1]["dhcpOption"]
        except KeyError:
            ret = []
        return ret

    def setSubnetOption(self, subnet, option, value = None):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_SUBNET, [(subnet, AT.SUBNET), (option, "OPTION")], option)
        subnets = self.getSubnet(subnet)
        if subnets:
            subnetDN = subnets[0][0]
            self.setObjectOption(subnetDN, option, value)
        r.commit()

    def setSubnetStatement(self, subnet, option, value = None):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_SUBNET_STMT, [(subnet, AT.SUBNET), (option, "OPTION")], value)
        subnets = self.getSubnet(subnet)
        if subnets:
            subnetDN = subnets[0][0]
            self.setObjectStatement(subnetDN, option, value)
        r.commit()

    def setSubnetDescription(self, subnet, description):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_SUBNET_DESC, [(subnet, AT.SUBNET)], description)
        subnets = self.getSubnet(subnet)
        if subnets:
            subnetDN = subnets[0][0]
            if description:
                self.l.modify_s(subnetDN, [(ldap.MOD_REPLACE, "dhcpComments", description)])
            else:
                self.l.modify_s(subnetDN, [(ldap.MOD_DELETE, "dhcpComments", None)])
        r.commit()

    def setSubnetNetmask(self, subnet, netmask):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_SUBNET_NTMSK, [(subnet, AT.SUBNET)], netmask)
        subnets = self.getSubnet(subnet)
        if subnets:
            subnetDN = subnets[0][0]
            self.l.modify_s(subnetDN, [(ldap.MOD_REPLACE, "dhcpNetMask", netmask)])
        r.commit()

    def setSubnetAuthoritative(self, subnet, flag = True):
        """
        Set the subnet as authoritative or 'not authoritative'

        @param subnet: the network address of the subnet
        @type subnet: str

        @param flag: whether the subnet is authoritative or not
        @type flag: bool
        """
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_SUBNET_AUTH, [(subnet, AT.SUBNET)], flag)
        subnets = self.getSubnet(subnet)
        if subnets:
            subnetDN = subnets[0][0]
            options = self.getObjectStatements(subnetDN)
            newoptions = []
            for option in options:
                if not option in ["authoritative", "not authoritative"]:
                    newoptions.append(option)
            if flag:
                newoptions.append("authoritative")
            else:
                newoptions.append("not authoritative")
            self.l.modify_s(subnetDN, [(ldap.MOD_REPLACE, "dhcpStatements", newoptions)])
        r.commit()

    def addSubnet(self, network, netmask, name = None):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_ADD_SUBNET, [(network, AT.SUBNET)], name)
        serviceDN = self.getService()[0]
        if not name: name = network + "/" + str(netmask)
        dn = "cn=" + network + "," + serviceDN
        entry = {
            "cn" : network,
            "dhcpNetMask" : str(netmask),
            "dhcpComments" : name,
            "objectClass" : ["top", "dhcpSubnet", "dhcpOptions"]
            }
        attributes=[ (k,v) for k,v in entry.items() ]
        self.l.add_s(dn, attributes)
        r.commit()

    def delSubnet(self, network):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_DEL_SUBNET, [(network, AT.SUBNET)])
        subnets = self.getSubnet()
        for subnet in subnets:
            if subnet[1]["cn"][0] == network:
                self.delRecursiveEntry(subnet[0])
                break
        r.commit()

    def getSubnetHosts(self, network, filt = None):
        if filt:
            filt = "*" + filt.strip() + "*"
        else:
            filt = "*"
        subnets = self.getSubnet(network)
        ret = []
        if subnets:
            subnetDN = subnets[0][0]
            ret = self.l.search_s(subnetDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpHost)(|(cn=%(filt)s)(dhcpHWAddress=ethernet%(filt)s)(dhcpStatements=fixed-address%(filt)s)))" % {"filt" : filt}, None)
        return ret

    def getSubnetHostsCount(self, network):
        subnets = self.getSubnet(network)
        ret = []
        if subnets:
            subnetDN = subnets[0][0]
            ret = self.l.search_s(subnetDN, ldap.SCOPE_SUBTREE, "(objectClass=dhcpHost)", ["cn"])
        return len(ret)

    # DHCP pool management

    def getPool(self, pool = None):
        if not pool: pool = "*"
        return self.l.search_s(self.configDhcp.dhcpDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpPool)(cn=%s))" % pool, None)

    def getPoolOptions(self, pool):
        try:
            ret = self.getPool(pool)[0][1]["dhcpOption"]
        except KeyError:
            ret = []
        return ret

    def setPoolOption(self, pool, option, value = None):
        pools = self.getPool(pool)
        if pools:
            poolDN = pools[0][0]
            self.setObjectOption(poolDN, option, value)

    def setPoolStatement(self, pool, option, value = None):
        pools = self.getPool(pool)
        if pools:
            poolDN = pools[0][0]
            self.setObjectStatement(poolDN, option, value)

    def getPoolsRanges(self, subnet):
        pools = self.l.search_s("cn=%s,cn=DHCP Config,%s" % (subnet, self.configDhcp.dhcpDN), ldap.SCOPE_SUBTREE, "(objectClass=dhcpPool)", None)
        ret = []
        for p in pools:
    	    ret.append(p[1]["dhcpRange"][0])
    	return ret

    def setPoolsRanges(self, subnet, ranges):
        pools = self.l.search_s("cn=%s,cn=DHCP Config,%s" % (subnet, self.configDhcp.dhcpDN), ldap.SCOPE_SUBTREE, "(objectClass=dhcpPool)", None)
        for p in pools:
            self.l.delete_s(p[0])
        id = 1
        for r in ranges:
            start, end = r.split(" ")
            self.addPool(subnet, "pool%s" % str(id), start, end)
            id = id + 1
        # If on failover mode, set the failover configuration
        # on all pools
        if self.getFailoverConfig():
            self.setPoolFailover()

    def addPool(self, subnet, poolname, start, end):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_ADD_POOL, [(subnet, AT.SUBNET),(poolname, AT.POOL)])
        dhcprange = start + " " + end
        subnets = self.getSubnet(subnet)
        dn = "cn=" + poolname + "," + subnets[0][0]
        entry = {
            "cn" : poolname,
            "dhcpRange" : dhcprange,
            "objectClass" : ["top", "dhcpPool", "dhcpOptions"]
        }
        attributes=[ (k,v) for k,v in entry.items() ]
        self.l.add_s(dn, attributes)
        r.commit()

    def delPool(self, poolname):
        pools = self.getPool(poolname)
        for pool in pools:
            if pool[1]["cn"][0] == poolname:
                self.delRecursiveEntry(pool[0])
                break

    def setPoolRange(self, pool, start, end):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_POOLRANGE, [(pool, AT.POOL)])
        pools = self.getPool(pool)
        if pools:
            poolDN = pools[0][0]
            self.l.modify_s(poolDN, [(ldap.MOD_REPLACE, "dhcpRange", start + " " + end)])
        r.commit()

    def getPoolRange(self, pool):
        """
        Return the IP range of a pool
        """
        ret = None
        pools = self.getPool(pool)
        if pools:
            fields = pools[0][1]
            try:
                dhcpRange = fields["dhcpRange"][0]
            except KeyError:
                pass
            else:
                ret = dhcpRange.split()
        return ret

    # DHCP group management

    def getGroup(self, group = None):
        if not group: group = "*"
        return self.l.search_s(self.configDhcp.dhcpDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpGroup)(cn=%s))" % group, None)

    def getGroupOptions(self, group):
        try:
            ret = self.getGroup(group)[0][1]["dhcpOption"]
        except KeyError:
            ret = []
        return ret

    def setGroupOption(self, group, option, value = None):
        groups = self.getGroup(group)
        if groups:
            groupDN = groups[0][0]
            self.setObjectOption(groupDN, option, value)

    def addGroup(self, subnet, groupname):
        subnets = self.getSubnet(subnet)
        dn = "cn=" + groupname + "," + subnets[0][0]
        entry = {
            "cn" : groupname,
            "objectClass" : ["top", "dhcpGroup", "dhcpOptions"]
            }
        attributes=[ (k,v) for k,v in entry.items() ]
        self.l.add_s(dn, attributes)

    def delGroup(self, groupname):
        groups = self.getGroup(groupname)
        for group in groups:
            if group[1]["cn"][0] == groupname:
                self.delRecursiveEntry(group[0])
                break

    # DHCP host management

    def getHost(self, subnet, host = None):
        if not host: host = "*"
        subnetDN = self.getSubnet(subnet)[0][0]
        return self.l.search_s(subnetDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpHost)(cn=%s))" % host, None)

    def getHostOptions(self, subnet, host):
        try:
            ret = self.getHost(subnet, host)[0][1]["dhcpOption"]
        except KeyError:
            ret = []
        return ret

    def setHostOption(self, subnet, host, option, value = None):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_HOST, [(subnet, AT.SUBNET),(host, AT.HOST), (option,"OPTION")], value)
        hosts = self.getHost(subnet, host)
        if hosts:
            hostDN = hosts[0][0]
            self.setObjectOption(hostDN, option, value)
        r.commit()

    def setHostStatement(self, subnet, host, option, value):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_HOST_STMT, [(subnet, AT.SUBNET),(host, AT.HOST), (option,"OPTION")], value)
        hosts = self.getHost(subnet, host)
        if hosts:
            hostDN = hosts[0][0]
            self.setObjectStatement(hostDN, option, value)
        r.commit()

    def setHostHWAddress(self, subnet, host, address):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_SET_HOST_HWADD, [(subnet, AT.SUBNET),(host, AT.HOST)], address)
        hosts = self.getHost(subnet, host)
        if hosts:
            hostDN = hosts[0][0]
            self.l.modify_s(hostDN, [(ldap.MOD_REPLACE, "dhcpHWAddress", ["ethernet " + address])])
        r.commit()

    def getHostHWAddress(self, subnet, host, address):
        try:
            ret = self.getHost(subnet, host)[0][1]["dhcpHWAddress"][0]
            ret = ret.split()[1]
        except KeyError:
            ret = None
        return ret

    def addHostToSubnet(self, subnet, hostname):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_ADD_HOST_TO_SUB, [(subnet, AT.SUBNET)], hostname)
        subnets = self.getSubnet(subnet)
        dn = "cn=" + hostname + "," + subnets[0][0]
        entry = {
            "cn" : hostname,
            "objectClass" : ["top", "dhcpHost", "dhcpOptions"]
            }
        attributes=[ (k,v) for k,v in entry.items() ]
        self.l.add_s(dn, attributes)
        r.commit()

    def addHostToGroup(self, groupname, hostname):
        groups = self.getGroup(groupname)
        dn = "cn=" + hostname + "," + groups[0][0]
        entry = {
            "cn" : hostname,
            "objectClass" : ["top", "dhcpHost", "dhcpOptions"]
            }
        attributes=[ (k,v) for k,v in entry.items() ]
        self.l.add_s(dn, attributes)

    def delHost(self, subnet, hostname):
        r = AF().log(PLUGIN_NAME, AA.NETWORK_DEL_HOST, [(subnet, AT.SUBNET)], hostname)
        hosts = self.getHost(subnet, hostname)
        for host in hosts:
            if host[1]["cn"][0] == hostname:
                self.delRecursiveEntry(host[0])
                break
        r.commit()

    def hostExistsInSubnet(self, subnet, hostname):
        subnets = self.getSubnet(subnet)
        ret = False
        if subnets:
            subnetDN = subnets[0][0]
            result = self.l.search_s(subnetDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpHost)(cn=%s))" % hostname, None)
            ret = len(result) > 0
        return ret

    def ipExistsInSubnet(self, subnet, ip):
        subnets = self.getSubnet(subnet)
        ret = False
        if subnets:
            subnetDN = subnets[0][0]
            result = self.l.search_s(subnetDN, ldap.SCOPE_SUBTREE, "(&(objectClass=dhcpHost)(dhcpStatements=fixed-address %s))" % ip, None)
            ret = len(result) > 0
        return ret

    def getSubnetFreeIp(self, subnet, startAt = None):
        """
        Return the first available IP address of a subnet.
        If startAt is given, start the search from this IP.

        IPs inside subnet dynamic pool range are never returned.

        If none available, return an empty string.

        @param subnet: subnet name in LDAP
        @type subnet: str

        @param startAt: IP to start search
        @type startAt: str
        """
        ret = ""
        subnetDN = self.getSubnet(subnet)
        network = subnetDN[0][1]["cn"][0]
        netmask = int(subnetDN[0][1]["dhcpNetMask"][0])
        poolRange = self.getPoolRange(subnet)
        if poolRange:
            rangeStart, rangeEnd = poolRange
        if startAt: ip = startAt
        else: ip = network
        ip = ipNext(network, netmask, ip)
        while ip:
            if not self.ipExistsInSubnet(subnet, ip):
                if poolRange:
                    if not ipInRange(ip, rangeStart, rangeEnd):
                        ret = ip
                        break
                else:
                    ret = ip
                    break
            ip = ipNext(network, netmask, ip)
        return ret

class DhcpLeases:

    def __init__(self, conffile = None, conffilebase = None):
        self.config = mmc.plugins.network.NetworkConfig("network", conffile)
        self.leases = self.__parse()

    def __parse(self):
        COMMENT = "#"
        BEGIN = "lease"
        STATE = "binding state"
        HARDWARE = "hardware ethernet"
        HOSTNAME = "client-hostname"
        leases = {}
        leasesFile = file(self.config.dhcpLeases)
        current = None
        for line in leasesFile:
            line = line.strip().strip(";")
            if line and not line.startswith(COMMENT):
                if line.startswith(BEGIN):
                    current = line.split()[1]
                    leases[current] = {}
                elif current:
                    if line.startswith(STATE):
                        leases[current]["state"] = line.split()[2]
                    elif line.startswith(HARDWARE):
                        leases[current]["hardware"] = line.split()[2]
                    elif line.startswith(HOSTNAME):
                        leases[current]["hostname"] = line.split()[1].strip('"')
                    else:
                        pass
        leasesFile.close()
        return leases

    def get(self):
        return self.leases

class DhcpService(ServiceManager):

    def __init__(self, conffile = None):
        self.config = mmc.plugins.network.NetworkConfig("network", conffile)
        ServiceManager.__init__(self, self.config.dhcpPidFile, self.config.dhcpInit)


class DhcpLogView(LogView):
    """
    Get DHCP service log content.
    """

    def __init__(self):
        config = mmc.plugins.network.NetworkConfig("network")
        self.logfile = config.dhcpLogFile
        self.maxElt= 200
        self.file = open(self.logfile, 'r')
        self.pattern = {
            "dhcpd-syslog1" : "^(?P<b>[A-z]{3}) *(?P<d>[0-9]+) (?P<H>[0-9]{2}):(?P<M>[0-9]{2}):(?P<S>[0-9]{2}) .* dhcpd: (?P<op>DHCP[A-Z]*) (?P<extra>.*)$",
            "dhcpd-syslog2" : "^(?P<b>[A-z]{3}) *(?P<d>[0-9]+) (?P<H>[0-9]{2}):(?P<M>[0-9]{2}):(?P<S>[0-9]{2}) .* dhcpd: (?P<extra>.*)$",
            }

class DhcpError(BaseException):

    def __init__(self, err):
        self.err = err

    def __str__(self):
        return self.err
