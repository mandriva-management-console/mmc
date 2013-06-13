<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2010 Mandriva, http://www.mandriva.com
 *
 * $Id$
 *
 * This file is part of Mandriva Management Console (MMC).
 *
 * MMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * MMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

$module_audit_codes = array(
    'NETWORK_ADD_DNS_ZONE' => _T("Add zone", "network"),
    'NETWORK_DEL_DNS_ZONE' => _T("Del zone", "network"),
    'NETWORK_ADD_SUBNET' => _T("Add subnet", "network"),
    'NETWORK_DEL_SUBNET' => _T("Del subnet", "network"),
    'NETWORK_SET_HOST' => _T("Set host", "network"),
    'NETWORK_SET_HOST_STMT' => _T("Set host statement", "network"),
    'NETWORK_SET_HOST_HWADD' => _T("Set host hardware address", "network"),
    'NETWORK_SET_SUBNET' => _T("Set subnet", "network"),
    'NETWORK_SET_SUBNET_STMT' => _T("Set subnet statement", "network"),
    'NETWORK_SET_SUBNET_DESC' => _T("Set subnet description", "network"),
    'NETWORK_SET_SUBNET_AUTH' => _T("Set subnet authoritative flag", "network"),
    'NETWORK_SET_SUBNET_NTMSK' => _T("Set subnet netmask", "network"),
    'NETWORK_SET_SOA' => _T("Set SOA record", "network"),
    'NETWORK_SET_NS' => _T("Set NS record", "network"),
    'NETWORK_ADD_SOA' => _T("Add SOA record", "network"),
    'NETWORK_MODIFY_RECORD' => _T("Modify record", "network"),
    'NETWORK_ADD_POOL' => _T("Add pool", "network"),
    'NETWORK_SET_POOLRANGE' => _T("Set pool range", "network"),
    'NETWORK_ADD_HOST_TO_SUB' => _T("Add host to subnet", "network"),
    'NETWORK_DEL_HOST' => _T("Del host", "network"),
    'NETWORK_SET_HOST_ALIASES' => _T("Set host aliases", "network"),
    'NETWORK_ADD_RECORD_CNAME' => _T("Add CNAME record", "network"),
    'NETWORK_ADD_RECORD_A' => _T("Add A record", "network"),
    "NETWORK_START_DHCP_SERVICE" => _T('Start DHCP service', "network"),
    "NETWORK_STOP_DHCP_SERVICE" => _T('Stop DHCP service', "network"),
    "NETWORK_RESTART_DHCP_SERVICE" => _T('Restart DHCP service', "network"),
    "NETWORK_RELOAD_DHCP_SERVICE" => _T('Reload DHCP service', "network"),
    "NETWORK_START_DNS_SERVICE" => _T('Start DNS service', "network"),
    "NETWORK_STOP_DNS_SERVICE" => _T('Stop DNS service', "network"),
    "NETWORK_RESTART_DNS_SERVICE" => _T('Restart DNS service', "network"),
    "NETWORK_RELOAD_DNS_SERVICE" => _T('Reload DNS service', "network"),
    'ZONE' => _T("DNS zone", "network"),
    'RECORD_CNAME' => _T("CNAME record", "network"),
    'RECORD_A' => _T("A record", "network"),
    'HOST' => _T("DHCP host", "network"),
    'SUBNET' => _T("DHCP subnet", "network"),
    'POOL' => _T("DHCP pool", "network"),
    'MMC-NETWORK' => _T("network", "network"),
);

?>
