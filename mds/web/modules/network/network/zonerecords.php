<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: zonemembers.php 151 2008-03-03 15:18:18Z cedric $
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

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network2.inc.php");

require("localSidebar.php");
require("graph/navbar.inc.php");

$reverse = (isset($_GET["reverse"]) && ($_GET["reverse"]==1));

$zone = $_GET["zone"];
$sortby = $_GET["sortby"];
$asc = $_GET["asc"];

$suffix = ($sortby) ? "&sortby=$sortby&asc=$asc": "";

//echo "suf is $suffix";

$curzone = $zone;

//$zone = $_GET["zone"];
$ajax = new AjaxFilter("modules/network/network/ajaxZoneRecordsFilter.php?zone=$curzone&reverse=$reverse" . $suffix);
$ajax->display();

$title = $reverse ? 
	    sprintf(_T("Records of reverse zone for zone %s"), $zone):
	    sprintf(_T("Records of zone %s"), $zone);
$p = new PageGenerator($title);
$sidemenu->forceActiveItem("index");
$p->setSideMenu($sidemenu);
$p->display();

$ajax->displayDivToUpdate();

$f = new Form();
if ($reverse || count(getReverseZone($zone))){
$linktext = $reverse ? _T("Manage zone records") : _T("Manage reverse zone records");
$f->addSummary("<a href='" . 
		urlStr("network/network/zonerecords", array("zone" => $zone, "reverse" => !$reverse)) . 
		"'>" . $linktext . "</a>");
}
$f->addOnClickButton(_T("Add a record"), urlStr("network/network/addrecord", array("zone" => $curzone, "reverse"=>$reverse)));
$f->display();
?>
