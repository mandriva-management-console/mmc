<?php
/**
 * (c) 2014 Zentyal, http://www.zentyal.com
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * Author(s):
 *   Miguel Julián <mjulian@zentyal.com>
 */

require("modules/samba4/includes/machines-xmlrpc.inc.php");

$filter = $_GET['filter'];
$computers = searchMachines($filter);
$names = array();
$descriptions = array();
foreach($computers as $computer) {
    $names[] = $computer[0];
    if (! $computer[2]) {
        $computersEnabled[] = "disabledRow";
        $computer[1] = "(" . _T("Disabled") . ") " . $computer[1];
    } else {
        $computersEnabled[] = "enabledRow";
    }
    $descriptions[] = $computer[1];
}

$list = new ListInfos($names, _T("Computer name", "samba4"));
$list->disableFirstColumnActionLink();
$list->setCssClass("machineName");
$list->setCssClasses($computersEnabled);
$list->setNavBar(new AjaxNavBar(count($computers), $filter));

$list->addExtraInfo($descriptions, _T("Description", "samba4"));
$list->addActionItem(new ActionItem(_T("Edit"),"edit","edit","machine"));
$list->addActionItem(new ActionPopupItem(_T("Delete"),"delete","delete","machine"));

$list->setName(_("Computers"));
$list->display();
?>
