<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: infoPackage.inc.php 8 2006-11-13 11:08:22Z cedric $
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

require("modules/mail/mainSidebar.php");
require("graph/navbar.inc.php");

$domain = $_GET["domain"];
$ajax = new AjaxFilter(urlStrRedirect("mail/domains/ajaxFilter", array("domain" => $domain)));
$ajax->display();

$p = new PageGenerator(_T("Members of ") . " " . $domain);
$sidemenu->forceActiveItem("index");
$p->setSideMenu($sidemenu);
$p->display();

$ajax->displayDivToUpdate();
?>
