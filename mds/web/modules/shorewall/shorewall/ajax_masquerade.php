<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2012 Mandriva, http://www.mandriva.com
 *
 * This file is part of Management Console.
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

$zones = getZonesInterfaces();
$rules = getMasqueradeRules();
$deleteAction = new ActionPopupItem(_T("Delete NAT rule"), "delete_masquerade_rule", "delete", "");

$ids = array();
$label = array();
$actionsDelete = array();

foreach($rules as $index => $rule) {
    foreach($zones as $zone_info) {
        if ($zone_info[1] == $rule[0])
            $src_zone = $zone_info[0];
        if ($zone_info[1] == $rule[1])
            $dst_zone = $zone_info[0];
    }
    $label[] = sprintf("%s (%s) ←→ %s (%s)", $src_zone, $rule[0], $dst_zone, $rule[1]);
    $ids[] = array("id" => $index);
    $actionsDelete[] = $deleteAction;
}

$n = new ListInfos($label, _T("NAT rule"));
$n->first_elt_padding = 1;
$n->disableFirstColumnActionLink();
$n->setParamInfo($ids);
$n->addActionItemArray($actionsDelete);
$n->display();

?>
