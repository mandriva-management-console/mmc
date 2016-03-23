<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2015 Mandriva, http://www.mandriva.com/
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

require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

$ajax = new AjaxFilter(urlStrRedirect("samba/shares/ajaxFilter"));
$ajax->display();

$p = new PageGenerator(_T("Shares"));
$p->setSideMenu($sidemenu);
$p->display();

$ajax->displayDivToUpdate();

?>
