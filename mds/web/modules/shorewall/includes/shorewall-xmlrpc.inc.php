<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2012 Mandriva, http://www.mandriva.com
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

function addRule($action, $src, $dst, $proto = "", $dst_port = "") {
    return xmlCall("shorewall.add_rule", array($action, $src, $dst, $proto, $dst_port));
}
function delRule($action, $src, $dst, $proto = "", $dst_port = "") {
    return xmlCall("shorewall.del_rule", array($action, $src, $dst, $proto, $dst_port));
}
function getRules($action = "", $src = "", $dst = "", $filter = "") {
    return xmlCall("shorewall.get_rules", array($action, $src, $dst, $filter));
}
function getServices() {
    if (!isset($_SESSION['shorewall_services']))
        $_SESSION['shorewall_services'] = xmlCall("shorewall.get_services", array());
    return $_SESSION['shorewall_services'];
}
function getShorewallZones($type) {
    if (!isset($_SESSION['shorewall_zones_' . $type]))
        $_SESSION['shorewall_zones_' . $type] = xmlCall("shorewall.get_zones", array($type));
    return $_SESSION['shorewall_zones_' . $type];
}
function getPolicies($src = "", $dst = "", $filter = "") {
    return xmlCall("shorewall.get_policies", array($src, $dst, $filter));
}
function changePolicies($src, $dst, $policy, $log = "") {
    return xmlCall("shorewall.change_policies", array($src, $dst, $policy, $log));
}
function getZonesInterfaces($type = "") {
    if (!isset($_SESSION['shorewall_zones_interfaces_' . $type]))
        $_SESSION['shorewall_zones_interfaces_' . $type] = xmlCall("shorewall.get_zones_interfaces", array($type));
    return $_SESSION['shorewall_zones_interfaces_' . $type];
}
function getZonesTypes() {
    if (!isset($_SESSION['shorewall_zones_types'])) {
        list($internal, $external) = xmlCall("shorewall.get_zones_types", array());
        $_SESSION['shorewall_zones_types'] = array();
        $_SESSION['shorewall_zones_types']['internal'] = $internal;
        $_SESSION['shorewall_zones_types']['external'] = $external;
    }
    return $_SESSION['shorewall_zones_types'];
}
function getMasqueradeRules() {
    return xmlCall("shorewall.get_masquerade_rules", array());
}
function addMasqueradeRule($wan_if, $lan_if) {
    return xmlCall("shorewall.add_masquerade_rule", array($wan_if, $lan_if));
}
function delMasqueradeRule($wan_if, $lan_if) {
    return xmlCall("shorewall.del_masquerade_rule", array($wan_if, $lan_if));
}
function enableIpFoward() {
    return xmlCall("shorewall.enable_ip_forward", array());
}
function disableIpFoward() {
    return xmlCall("shorewall.disable_ip_forward", array());
}
function restartShorewallService() {
    return xmlCall("shorewall.restart_service", array());
}
?>
