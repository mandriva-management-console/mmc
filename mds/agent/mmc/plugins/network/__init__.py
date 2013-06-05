# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2008 Mandriva, http://www.mandriva.com/
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
# along with MMC; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

"""
MDS network plugin for the MMC agent.
"""

import socket
import ldap
import logging
import os.path
import grp
from ConfigParser import NoOptionError
from mmc.core.version import scmRevision
from mmc.site import mmcconfdir
from mmc.plugins.base import ldapUserGroupControl
from mmc.plugins.network.dhcp import Dhcp, DhcpService, DhcpLogView, DhcpLeases
from mmc.plugins.network.dns import Dns, DnsService, DnsLogView
from mmc.support.config import PluginConfig

from mmc.core.audit import AuditFactory as AF
from mmc.plugins.network.audit import AA, PLUGIN_NAME


INI = mmcconfdir + "/plugins/network.ini"

VERSION = "2.5.0"
APIVERSION = "2:1:0"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION


def activate():
    config = NetworkConfig("network")
    logger = logging.getLogger()

    if config.disabled:
        logger.warning("Plugin network: disabled by configuration.")
        return False

    if not config.dhcpEnable and not config.dnsEnable:
        logger.warning("Plugin network: disabled by configuration.")
        return False

    try:
        ldapObj = ldapUserGroupControl()
    except ldap.INVALID_CREDENTIALS:
        logger.error("Can't bind to LDAP: invalid credentials.")
        return False


    if config.dhcpEnable:
        # Test if the DHCP/LDAP schema is available in the directory
        try:
            schema = ldapObj.getSchema("dhcpServer")
            if len(schema) <= 0:
                logger.error("DHCP schema is not included in LDAP directory");
                return False
            # Test if DHCP/LDAP schema contains the dhcpComments attribute
            if "dhcpComments" not in schema:
                logger.error("DHCP/LDAP schema does not support the dhcpComments attribute. Please use the latest version of DCHP/LDAP schema.")
                return False
        except:
            logger.exception("invalid schema")
            return False
    else:
        logger.info("DHCP submodule is disabled")

    if config.dnsEnable:
        # Test if the DNS/LDAP schema is available in the directory
        serverType = config.dnsType
        if serverType == "pdns":
            try:
                schema = ldapObj.getSchema("dNSDomain2")
                if len(schema) <= 0:
                    logger.error("DNS zone schema (dnsdomain2.schema) is not included in LDAP directory");
                    return False
            except:
                logger.exception("invalid DNS schema")
                return False
        elif serverType == "bind":
            try:
                schema = ldapObj.getSchema("dNSZone")
                if len(schema) <= 0:
                    logger.error("DNS zone schema (dnszone.schema) is not included in LDAP directory");
                    return False
            except:
                logger.exception("invalid DNS schema")
                return False
        else:
            logger.error("%s : Unknown DNS server."%serverType);
            return False
    else:
        logger.info("DNS submodule is disabled")

    if config.dhcpEnable:
        # Create DHCP ou
        head, path = config.dhcpDN.split(",", 1)
        ouName = head.split("=")[1]
        ldapObj.addOu(ouName, path)
        # Create DHCP config base structure
        d = Dhcp()
        try:
            d.addServiceConfig("DHCP config")
            logger.info("Created DHCP config object")
        except ldap.ALREADY_EXISTS:
            pass
        hostname = d.configDhcp.dhcpHostname
        try:
            d.addServer(hostname)
            d.setServiceConfigStatement("not", "authoritative")
            logging.info("The DHCP server '%s' was added." % hostname)
        except ldap.ALREADY_EXISTS:
            pass
        d.setServiceServerStatus(hostname, "primary")
        logging.info("The server '%s' has been set as the primary DHCP server" % hostname)

    # Create DNS config base structure
    if config.dnsEnable:
        # Create DNS ou
        head, path = config.dnsDN.split(",", 1)
        ouName = head.split("=")[1]
        ldapObj.addOu(ouName, path)
        if serverType == "bind":
            try:
                gidNumber = grp.getgrnam(config.bindGroup)
            except KeyError:
                logger.error('The group "%s" does not exist.' % config.bindGroup)
                return False
            gidNumber = gidNumber[2]

            try:
                os.mkdir(config.bindLdapDir)
                os.chmod(config.bindLdapDir, 02750)
                os.chown(config.bindLdapDir, -1, gidNumber)
            except OSError, e:
                # errno = 17 is "File exists"
                if e.errno != 17: raise

            if not os.path.exists(config.bindLdap):
                f = open(config.bindLdap, "w")
                f.close()
                os.chmod(config.bindLdap, 0640)
                os.chown(config.bindLdap, -1, gidNumber)

    return True


def hasDHCP():
    return NetworkConfig("network").dhcpEnable

def hasDNS():
    return NetworkConfig("network").dnsEnable

# Mixed DNS/DHCP methods
def addZoneWithSubnet(zonename, network, netmask, reverse = False, description = None, nameserver = None, nameserverip = None):
    Dns().addZone(zonename, network, netmask, reverse, description, nameserver, nameserverip)
    d = Dhcp()
    d.addSubnet(network, netmask, zonename)
    d.setSubnetOption(network, "domain-name", '"' + zonename +'"')
    if nameserverip: d.setSubnetOption(network, "domain-name-servers", nameserverip)

def getSubnetAndZoneFreeIp(subnet, zone, current = None):
    ret = ""
    dhcp = Dhcp()
    dns = Dns()
    ip = dhcp.getSubnetFreeIp(subnet, current)
    while ip:
        if not dns.ipExists(zone, ip):
            ret = ip
            break
        ip = dhcp.getSubnetFreeIp(subnet, ip)
    return ret

# DNS exported call
def getZones(f):
    return Dns().getZones(f)

def addZone(zonename, network = None, netmask = None, reverse = False, description = None, nameserver = None, nameserverip = None):
    Dns().addZone(zonename, network, netmask, reverse, description, nameserver, nameserverip)

def delZone(zone):
    Dns().delZone(zone)

def zoneExists(zone):
    return Dns().zoneExists(zone)

def getAllZonesNetworkAddresses():
    return Dns().getAllZonesNetworkAddresses()

def getZoneNetworkAddress(zone):
    return Dns().getZoneNetworkAddress(zone)

def getZoneObjectsCount(zone):
    return Dns().getZoneObjectsCount(zone)

def getZoneObjects(zone, filt):
    return Dns().getZoneObjects(zone, filt)

def getReverseZone(zone):
    return Dns().getReverseZone(zone)

def getZoneData(zone, filt):
    return Dns().getZoneData(zone, filt)

def getZoneRecords(zone, filt):
    return Dns().getZoneRecords(zone, filt)

def getZoneRecordById(zone, id):
    return Dns().getZoneRecordById(zone, id)

def modifyRecordById(zone, id, hostname, value):
    return Dns().modifyRecordById(zone, id, hostname, value)

def hasRecord(zone, type, hostname):
    return Dns().hasRecord(zone, type, hostname)

def addRecord(zone, type, hostname, value):
    return Dns().addRecord(zone, type, hostname, value)

def delRecordById(zone, id):
    return Dns().delRecordById(zone, id)

def addRecordA(zone, hostname, ip):
    return Dns().addRecordA(zone, hostname, ip)

def delRecord(zone, hostname):
    Dns().delRecord(zone, hostname)

def modifyRecord(zone, hostname, ip):
    Dns().modifyRecord(zone, hostname, ip)

def getSOARecord(zone):
    return Dns().getSOARecord(zone)

def setSOANSRecord(zone, nameserver):
    Dns().setSOANSRecord(zone, nameserver)

def setSOAARecord(zone, ip):
    Dns().setSOAARecord(zone, ip)

def setNSRecords(zone, nameservers):
    Dns().setNSRecords(zone, nameservers)

def setMXRecords(zone, mxservers):
    Dns().setMXRecords(zone, mxservers)

def getNSRecords(zone):
    return Dns().getNSRecords(zone)

def getMXRecords(zone):
    return Dns().getMXRecords(zone)

def getSOAARecord(zone):
    return Dns().getSOAARecord(zone)

def setZoneDescription(zone, description):
    Dns().setZoneDescription(zone, description)

def hostExists(zone, hostname):
    return Dns().hostExists(zone, hostname)

def ipExists(zone, ip):
    return Dns().ipExists(zone, ip)

def resolve(zone, hostname):
    return Dns().resolve(zone, hostname)

def getZoneFreeIp(zone, startAt = None):
    return Dns().getZoneFreeIp(zone, startAt)

def getResourceRecord(zone, rr):
    return Dns().getResourceRecord(zone, rr)

def getCNAMEs(zone, hostname):
    return Dns().getCNAMEs(zone, hostname)

def delCNAMEs(zone, hostname):
    Dns().delCNAMEs(zone, hostname)

def addRecordCNAME(zone, alias, cname, dnsClass = "IN"):
    Dns().addRecordCNAME(zone, alias, cname, dnsClass)

def setHostAliases(zone, host, aliases):
    return Dns().setHostAliases(zone, host, aliases)

# DHCP exported call

def getService():
    return Dhcp().getService()

def updateSecondaryServer(serverName):
    return Dhcp().updateSecondaryServer(serverName)

def addSecondaryServer(serverName):
    return Dhcp().addSecondaryServer(serverName)

def delSecondaryServer():
    return Dhcp().delSecondaryServer()

def setFailoverConfig(primaryIp, secondaryIp, serverPort = 647, peerPort = 647,
        delay = 30, update = 10, balance = 3, mclt = 1800, split = 128):
    return Dhcp().setFailoverConfig(primaryIp, secondaryIp, serverPort, peerPort,
        delay, update, balance, mclt, split)

def delFailoverConfig():
    return Dhcp().delFailoverConfig()

def getFailoverConfig():
    return Dhcp().getFailoverConfig()

def setPoolFailover(pool = None):
    return Dhcp().setPoolFailover(pool)

def delPoolFailover(pool = None):
    return Dhcp().delPoolFailover(pool)

def addSubnet(network, netmask, name):
    Dhcp().addSubnet(network, netmask, name)

def delSubnet(network):
    Dhcp().delSubnet(network)

def getSubnet(subnet):
    return Dhcp().getSubnet(subnet)

def getSubnets(f):
    return Dhcp().getSubnets(f)

def setSubnetOption(subnet, option, value = None):
    Dhcp().setSubnetOption(subnet, option, value)

def setSubnetStatement(subnet, option, value = None):
    Dhcp().setSubnetStatement(subnet, option, value)

def setSubnetDescription(subnet, description):
    Dhcp().setSubnetDescription(subnet, description)

def setSubnetNetmask(subnet, netmask):
    Dhcp().setSubnetNetmask(subnet, netmask)

def setSubnetAuthoritative(subnet, flag = True):
    Dhcp().setSubnetAuthoritative(subnet, flag)

def getSubnetHosts(network, filter):
    return Dhcp().getSubnetHosts(network, filter)

def getSubnetHostsCount(zone):
    return Dhcp().getSubnetHostsCount(zone)

def getPoolsRanges(subnet):
    return Dhcp().getPoolsRanges(subnet)

def setPoolsRanges(subnet, ranges):
    return Dhcp().setPoolsRanges(subnet, ranges)

def addPool(subnet, poolname, start, end):
    Dhcp().addPool(subnet, poolname, start, end)

def delPool(poolname):
    Dhcp().delPool(poolname)

def getPool(poolname):
    return Dhcp().getPool(poolname)

def setPoolRange(poolname, start, end):
    Dhcp().setPoolRange(poolname, start, end)

def addHostToSubnet(subnet, hostname):
    Dhcp().addHostToSubnet(subnet, hostname)

def delHost(subnet, hostname):
    Dhcp().delHost(subnet, hostname)

def setHostOption(subnet, host, option, value = None):
    Dhcp().setHostOption(subnet, host, option, value)

def setHostStatement(subnet, host, option, value = None):
    Dhcp().setHostStatement(subnet, host, option, value)

def setHostHWAddress(subnet, host, address):
    Dhcp().setHostHWAddress(subnet, host, address)

def getHostHWAddress(subnet, host, address):
    Dhcp().getHostHWAddress(subnet, host, address)

def getHost(subnet, host):
    return Dhcp().getHost(subnet, host)

def hostExistsInSubnet(subnet, hostname):
    return Dhcp().hostExistsInSubnet(subnet, hostname)

def ipExistsInSubnet(subnet, ip):
    return Dhcp().ipExistsInSubnet(subnet, ip)

def getSubnetFreeIp(subnet, startAt):
    return Dhcp().getSubnetFreeIp(subnet, startAt)

# DHCP leases
def getDhcpLeases():
    return DhcpLeases().get()

# Log
def getDhcpLog(filter = ''):
    return DhcpLogView().getLog(filter)

def getDnsLog(filter = ''):
    return DnsLogView().getLog(filter)

# Service management
def get_services_names():
    dhcp_service = os.path.basename(NetworkConfig("network").dhcpInit)
    dns_service = os.path.basename(NetworkConfig("network").dnsInit)
    return (dns_service, dhcp_service)

def dhcpService(command):
    if command != 'status':
        event = { 'start' : AA.NETWORK_START_DHCP_SERVICE,
                  'stop' : AA.NETWORK_STOP_DHCP_SERVICE,
                  'restart' : AA.NETWORK_RESTART_DHCP_SERVICE,
                  'reload' : AA.NETWORK_RELOAD_DHCP_SERVICE }
        r = AF().log(PLUGIN_NAME, event[command])
    ret = DhcpService().command(command)
    if command != 'status':
        r.commit()
    return ret

def dnsService(command):
    if command != 'status':
        event = { 'start' : AA.NETWORK_START_DNS_SERVICE,
                  'stop' : AA.NETWORK_STOP_DNS_SERVICE,
                  'restart' : AA.NETWORK_RESTART_DNS_SERVICE,
                  'reload' : AA.NETWORK_RELOAD_DNS_SERVICE }
        r = AF().log(PLUGIN_NAME, event[command])
    ret = DnsService().command(command)
    if command != 'status':
        r.commit()
    return ret


class NetworkConfig(PluginConfig):

    def readConf(self):
        PluginConfig.readConf(self)
        # DHCP conf
        try:
            self.dhcpEnable = self.getboolean("dhcp", "enable")
        except:
            self.dhcpEnable = True
        self.dhcpDN = self.getdn("dhcp", "dn")
        self.dhcpPidFile = self.get("dhcp", "pidfile")
        self.dhcpInit = self.get("dhcp", "init")
        self.dhcpLogFile = self.get("dhcp", "logfile")
        self.dhcpLeases = self.get("dhcp", "leases")
        try:
            self.dhcpHostname = self.get("dhcp", "hostname")
        except NoOptionError:
            self.dhcpHostname = socket.gethostname()
        # DNS conf
        try:
            self.dnsEnable = self.getboolean("dns", "enable")
        except:
            self.dnsEnable = True
        try:
            self.dnsType = self.get("dns", "type")
        except NoOptionError:
            self.dnsType = "bind"
        self.dnsDN = self.getdn("dns", "dn")
        self.dnsPidFile = self.get("dns", "pidfile")
        self.dnsInit = self.get("dns", "init")
        self.dnsLogFile = self.get("dns", "logfile")
        if self.dnsType == "bind":
            self.bindRootPath = self.get("dns", "bindroot")
            self.bindGroup = self.get("dns", "bindgroup")
            self.bindLdap = os.path.join(self.bindRootPath, "named.conf.ldap")
            self.bindLdapDir = os.path.join(self.bindRootPath, "named.ldap")
            try:
                self.bindLdapChrootConfPath = os.path.join(self.get("dns", "bindchrootconfpath"), "named.ldap")
            except NoOptionError:
                self.bindLdapChrootConfPath = self.bindLdapDir
        try:
            self.dnsReader = self.getdn("dns", "dnsreader")
        except NoOptionError:
            pass
        try:
            self.dnsReaderPassword = self.getpassword("dns", "dnsreaderpassword")
        except NoOptionError:
            pass

    def setDefault(self):
        PluginConfig.setDefault(self)
        self.dnsReader = None
        self.dnsReaderPassword = None
