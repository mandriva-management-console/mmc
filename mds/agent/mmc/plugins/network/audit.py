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

PLUGIN_NAME = u'MMC-NETWORK'

class AuditActions:
    NETWORK_ADD_DNS_ZONE = u'NETWORK_ADD_DNS_ZONE'
    NETWORK_DEL_DNS_ZONE = u'NETWORK_DEL_DNS_ZONE'
    NETWORK_ADD_SUBNET = u'NETWORK_ADD_SUBNET'
    NETWORK_DEL_SUBNET = u'NETWORK_DEL_SUBNET'
    NETWORK_SET_HOST = u'NETWORK_SET_HOST'
    NETWORK_SET_HOST_STMT = u'NETWORK_SET_HOST_STMT'
    NETWORK_SET_HOST_HWADD = u'NETWORK_SET_HOST_HWADD'
    NETWORK_SET_SUBNET = u'NETWORK_SET_SUBNET'
    NETWORK_SET_SUBNET_STMT = u'NETWORK_SET_SUBNET_STMT'
    NETWORK_SET_SUBNET_DESC = u'NETWORK_SET_SUBNET_DESC'
    NETWORK_SET_SUBNET_AUTH = u'NETWORK_SET_SUBNET_AUTH'
    NETWORK_SET_SUBNET_NTMSK = u'NETWORK_SET_SUBNET_NTMSK'
    NETWORK_SET_SOA = u'NETWORK_SET_SOA'
    NETWORK_SET_NS = u'NETWORK_SET_NS'
    NETWORK_ADD_SOA = u'NETWORK_ADD_SOA'
    NETWORK_MODIFY_RECORD = u'NETWORK_MODIFY_RECORD'
    NETWORK_ADD_POOL = u'NETWORK_ADD_POOL'
    NETWORK_SET_POOLRANGE = u'NETWORK_SET_POOLRANGE'
    NETWORK_ADD_HOST_TO_SUB = u'NETWORK_ADD_HOST_TO_SUB'
    NETWORK_DEL_HOST = u'NETWORK_DEL_HOST'
    NETWORK_SET_HOST_ALIASES = u'NETWORK_SET_HOST_ALIASES'
    NETWORK_ADD_RECORD_CNAME = u'NETWORK_ADD_RECORD_CNAME'
    NETWORK_ADD_RECORD_A = u'NETWORK_ADD_RECORD_A'
    NETWORK_START_DHCP_SERVICE = u'NETWORK_START_DHCP_SERVICE'
    NETWORK_STOP_DHCP_SERVICE = u'NETWORK_STOP_DHCP_SERVICE'
    NETWORK_RESTART_DHCP_SERVICE = u'NETWORK_RESTART_DHCP_SERVICE'
    NETWORK_RELOAD_DHCP_SERVICE = u'NETWORK_RELOAD_DHCP_SERVICE'
    NETWORK_START_DNS_SERVICE = u'NETWORK_START_DNS_SERVICE'
    NETWORK_STOP_DNS_SERVICE = u'NETWORK_STOP_DNS_SERVICE'
    NETWORK_RESTART_DNS_SERVICE = u'NETWORK_RESTART_DNS_SERVICE'
    NETWORK_RELOAD_DNS_SERVICE = u'NETWORK_RELOAD_DNS_SERVICE'


AA = AuditActions

class AuditTypes(AT):
    ZONE = u'ZONE'
    HOST = u'HOST'
    NET = u'NET'
    SUBNET = u'SUBNET'
    ALIAS = u'ALIAS'
    POOL = u'POOL'

AT = AuditTypes

