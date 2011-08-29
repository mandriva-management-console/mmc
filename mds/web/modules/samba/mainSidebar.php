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

$pdc = xmlCall("samba.isPdc",null);

$sidemenu = new SideMenu();
$sidemenu->setClass("shares machines config");
$sidemenu->addSideMenuItem(new SideMenuItem(_T("List shares", "samba"), "samba","shares","index", "img/shares/icn_global_active.gif", "img/shares/icn_global.gif"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Add a share", "samba"), "samba","shares","add", "img/shares/icn_addShare_active.gif", "img/shares/icn_addShare.gif"));

if ($pdc) { // if SAMBA is configured as a PDC
    $s = new SideMenuItem(_T("List computers", "samba"), "samba", "machines", "index", "img/machines/icn_global_active.gif", "img/machines/icn_global.gif");
    $s->setCssId("indexmachines");
    $sidemenu->addSideMenuItem($s);
    /*$s = new SideMenuItem(_T("Add a computer", "samba"), "samba", "machines", "add", "img/machines/icn_addMachines_active.gif", "img/machines/icn_addMachines.gif");
    $s->setCssId("addmachine");
    $sidemenu->addSideMenuItem($s);*/
}

$s = new SideMenuItem(_T("General options", "samba"), "samba","config","index", "img/config/icn_global_active.gif", "img/config/icn_global.gif");
$s->setCssId("indexconfig");
$sidemenu->addSideMenuItem($s);

switch ($_GET["submod"]) {
case "shares":
    $sidemenu->setBackgroundImage("img/shares/icn_shares_large.gif");
    break;
case "machines":
    $sidemenu->setBackgroundImage("img/machines/icn_machines_large.gif");
    break;
case "config":
    $sidemenu->setBackgroundImage("img/config/icn_config_large.gif");
    break;
}



?>
