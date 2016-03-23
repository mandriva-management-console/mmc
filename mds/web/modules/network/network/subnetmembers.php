<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
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
 */

require("localSidebar.php");
require("graph/navbar.inc.php");

$subnet = $_GET["subnet"];

$ajax = new AjaxFilter(urlStrRedirect("network/network/ajaxSubnetMembersFilter", array("subnet" => $subnet)));
$ajax->display();

$p = new PageGenerator(_T("Members of DHCP subnet") . "&nbsp;" . $subnet);
$sidemenu->forceActiveItem("subnetindex");
$p->setSideMenu($sidemenu);
$p->display();

$ajax->displayDivToUpdate();

$f = new Form();
$f->addOnClickButton(_T("Add a static host"), urlStr("network/network/subnetaddhost", array("subnet" => $subnet)));
$f->display();

?>
