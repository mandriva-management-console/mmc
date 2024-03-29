# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
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
Constants for the audit framework and the network plugin.
"""

from mmc.plugins.base.audit import AT

PLUGIN_NAME = 'MMC-NETWORK'

class AuditActions:
    NETWORK_ADD_DNS_ZONE = 'NETWORK_ADD_DNS_ZONE'
    NETWORK_DEL_DNS_ZONE = 'NETWORK_DEL_DNS_ZONE'
    NETWORK_ADD_SUBNET = 'NETWORK_ADD_SUBNET'
    NETWORK_DEL_SUBNET = 'NETWORK_DEL_SUBNET'
    NETWORK_SET_HOST = 'NETWORK_SET_HOST'
    NETWORK_SET_HOST_STMT = 'NETWORK_SET_HOST_STMT'
    NETWORK_SET_HOST_HWADD = 'NETWORK_SET_HOST_HWADD'
    NETWORK_SET_SUBNET = 'NETWORK_SET_SUBNET'
    NETWORK_SET_SUBNET_STMT = 'NETWORK_SET_SUBNET_STMT'
    NETWORK_SET_SUBNET_DESC = 'NETWORK_SET_SUBNET_DESC'
    NETWORK_SET_SUBNET_AUTH = 'NETWORK_SET_SUBNET_AUTH'
    NETWORK_SET_SUBNET_NTMSK = 'NETWORK_SET_SUBNET_NTMSK'
    NETWORK_SET_SOA = 'NETWORK_SET_SOA'
    NETWORK_SET_NS = 'NETWORK_SET_NS'
    NETWORK_ADD_SOA = 'NETWORK_ADD_SOA'
    NETWORK_MODIFY_RECORD = 'NETWORK_MODIFY_RECORD'
    NETWORK_ADD_POOL = 'NETWORK_ADD_POOL'
    NETWORK_SET_POOLRANGE = 'NETWORK_SET_POOLRANGE'
    NETWORK_ADD_HOST_TO_SUB = 'NETWORK_ADD_HOST_TO_SUB'
    NETWORK_DEL_HOST = 'NETWORK_DEL_HOST'
    NETWORK_SET_HOST_ALIASES = 'NETWORK_SET_HOST_ALIASES'
    NETWORK_ADD_RECORD_CNAME = 'NETWORK_ADD_RECORD_CNAME'
    NETWORK_ADD_RECORD_A = 'NETWORK_ADD_RECORD_A'
    NETWORK_START_DHCP_SERVICE = 'NETWORK_START_DHCP_SERVICE'
    NETWORK_STOP_DHCP_SERVICE = 'NETWORK_STOP_DHCP_SERVICE'
    NETWORK_RESTART_DHCP_SERVICE = 'NETWORK_RESTART_DHCP_SERVICE'
    NETWORK_RELOAD_DHCP_SERVICE = 'NETWORK_RELOAD_DHCP_SERVICE'
    NETWORK_START_DNS_SERVICE = 'NETWORK_START_DNS_SERVICE'
    NETWORK_STOP_DNS_SERVICE = 'NETWORK_STOP_DNS_SERVICE'
    NETWORK_RESTART_DNS_SERVICE = 'NETWORK_RESTART_DNS_SERVICE'
    NETWORK_RELOAD_DNS_SERVICE = 'NETWORK_RELOAD_DNS_SERVICE'


AA = AuditActions

class AuditTypes(AT):
    # DNS objects
    ZONE = 'ZONE'
    RECORD_CNAME = 'RECORD_CNAME'
    RECORD_A = 'RECORD_A'
    # DHCP objects
    HOST = 'HOST'
    SUBNET = 'SUBNET'
    POOL = 'POOL'

AT = AuditTypes

