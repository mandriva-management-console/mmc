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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

$filter = $_GET["filter"];
$list = getRules("", $src, $dst, $filter);
$zones = getZonesInterfaces($src);

$deleteAction = new ActionPopupItem(_T("Delete rule"), "delete_" . $page . "_rule", "delete", "");

$ids = array();
$decision = array();
$service = array();
$source = array();
$proto = array();
$port = array();
$actionsDelete = array();

foreach($list as $index => $rule) {
    include('rule_list.inc.php');
}

$n = new ListInfos($decision, _T("Decision"));
$n->first_elt_padding = 1;
$n->disableFirstColumnActionLink();
$n->addExtraInfo($service, _T("Service"));
if (count($zones) > 1)
    $n->addExtraInfo($source, _T("Source"));
$n->addExtraInfo($proto, _T("Protocol"));
$n->addExtraInfo($port, _T("Port(s)"));
$n->setParamInfo($ids);
$n->addActionItemArray($actionsDelete);
$n->setNavBar(new AjaxNavBar(count($ids), $filter));
$n->display();

?>
