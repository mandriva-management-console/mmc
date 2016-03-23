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

require("modules/samba4/includes/machines-xmlrpc.inc.php");
require("modules/samba4/mainSidebar.php");
require("graph/navbar.inc.php");

$ajax = new AjaxFilter(urlStrRedirect("samba4/machines/ajaxFilter"));
$ajax->display();

$page = new PageGenerator(_T("Computer management", "samba4"));
$page->setSideMenu($sidemenu);
$page->display();

$ajax->displayDivToUpdate();
?>
