<?php
/**
 * (c) 2014 Mandriva, http://www.mandriva.com/
 *
 * $Id$
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * Author(s):
 *   Miguel Julián <mjulian@zentyal.com>
 */

require("modules/samba4/includes/shares-xmlrpc.inc.php");
require("modules/samba4/mainSidebar.php");
require("graph/navbar.inc.php");

/* protected share */
$protectedShares = getProtectedSamba4Shares();

global $conf;

$shares = getSamba4Shares();
if (!isset($shares) or !$shares) {
    $shares = array();
}

$sharesName = array();
$sharesPath = array();
$sharesEnabled = array();
$sharesGuestAccess = array();
$sharesDescription = array();

$shareComponent = array (
    "name" => 0,
    "path" => 1,
    "enabled" => 2,
    "description" => 3,
    "guest_access" => 4
);

$editActions = array();
$delActions = array();
//$backupActions = array();

$test = array();
foreach($shares as $share) {
//var_dump($share);
    $sharesName[] = isset($share[$shareComponent["name"]]) ?
                                            $share[$shareComponent["name"]] : "";

    $sharesPath[] = isset($share[$shareComponent["path"]]) ?
                                            $share[$shareComponent["path"]] : "";

    if (isset($share[$shareComponent["enabled"]]) and $share[$shareComponent["enabled"]]) {
        $sharesEnabled[] = "enabledRow";
        $shareDescription = "";
    } else {
        $sharesEnabled[] = "disabledRow";
        $shareDescription = "(" . _T("Hidden", "samba4") . ") ";
    }

    $shareDescription = isset($share[$shareComponent["description"]]) ?
                                            $shareDescription . $share[$shareComponent["description"]] : "";
    $sharesDescription[] = $shareDescription;

    $sharesGuestAccess[] = isset($share[$shareComponent["guest_access"]]) ?
                                            $share[$shareComponent["guest_access"]] : "";

    if (isset($protectedShares) and !in_array($share[$shareComponent["name"]], $protectedShares)) {
        $editActions[] = new ActionItem(_T("Edit", "samba4"),"edit","edit","share");
        $delActions[] = new ActionPopupItem(_T("Delete", "samba4"),"delete","delete","share");
    } else {
        $editActions[] = new EmptyActionItem();
        $delActions[] = new EmptyActionItem();
    }
}

$page = new PageGenerator(_T("Current list of shares", "samba4"));
$page->setSideMenu($sidemenu);
$page->display();

$list = new ListInfos($sharesName, _T("Share", "samba4"));
$list->setCssClass("shareName");
$list->setCssClasses($sharesEnabled);
$list->addExtraInfo($sharesPath, _T("Path", "samba4"));
$list->addExtraInfo($sharesDescription, _T("Description", "samba4"));
$list->addActionItemArray($editActions);
$list->addActionItemArray($delActions);

$list->addActionItem(new ActionPopupItem(_T("Archive", "samba4"),"backup","backup","share"));
$list->disableFirstColumnActionLink();
$list->display();


/* Private functions */
function _shareIsEnabled($share) {
    return isset($share[2]) and $share[2];
}

?>
