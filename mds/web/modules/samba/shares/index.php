<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
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
 */
/* $Id$ */

/* protected share */
$protectedShare= array ("","homes","netlogon","archive");

require("modules/samba/includes/shares.inc.php");
require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

global $conf;

$shares = get_shares_detailed();
$sharesName = array();
$sharesComment = array();

$editActions = array();
$delActions = array();
//$backupActions = array();

foreach($shares as $share) {
    $sharesName[] = $share[0];
    if (isset($share[1]))
        $sharesComment[] = $share[1];
    else
        $sharesComment[] = "";
    if (!in_array($share[0], $protectedShare)) {
        $editActions[] = new ActionItem(_T("Edit"),"details","edit","share");
        $delActions[] = new ActionPopupItem(_T("Delete"),"delete","delete","share");
    } else {
        $editActions[] = new EmptyActionItem();
        $delActions[] = new EmptyActionItem();
    }
}

$p = new PageGenerator(_T("Shares"));
$p->setSideMenu($sidemenu);
$p->display();

$l = new ListInfos($sharesName, _T("Shares"));
$l->setCssClass("shareName");
$l->addExtraInfo($sharesComment, _T("Description"));
$l->addActionItemArray($editActions);
$l->addActionItemArray($delActions);

$l->addActionItem(new ActionPopupItem(_T("Archive"),"backup","backup","share"));
$l->disableFirstColumnActionLink();
$l->display();

?>
