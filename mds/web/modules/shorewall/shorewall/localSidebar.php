<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2012 Mandriva, http://www.mandriva.com
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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

$sidemenu = new SideMenu();
$sidemenu->setClass("shorewall");
$sidemenu->setBackgroundImage("modules/base/graph/users/img/icn_users_large.gif");

$zones_types = getZonesTypes();
$lan_zones = getShorewallZones($zones_types['internal']);
$wan_zones = getShorewallZones($zones_types['external']);

if ($lan_zones)
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("Internal &rarr; Server", "shorewall"), "shorewall", "shorewall", "internal_fw",
                                                "modules/shorewall/graph/img/config/icn_global_active.gif",
                                                "modules/shorewall/graph/img/config/icn_global.gif"));
if ($wan_zones)
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("External &rarr; Server", "shorewall"), "shorewall", "shorewall", "external_fw",
                                                "modules/shorewall/graph/img/config/icn_global_active.gif",
                                                "modules/shorewall/graph/img/config/icn_global.gif"));
if ($lan_zones && $wan_zones)
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("Internal &rarr; External", "shorewall"), "shorewall", "shorewall", "internal_external",
                                                "modules/shorewall/graph/img/config/icn_global_active.gif",
                                                "modules/shorewall/graph/img/config/icn_global.gif"));

if (count($lan_zones) > 1)
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("Internal &rarr; Internal", "shorewall"), "shorewall", "shorewall", "internal_internal",
                                                "modules/shorewall/graph/img/config/icn_global_active.gif",
                                                "modules/shorewall/graph/img/config/icn_global.gif"));

if ($lan_zones && $wan_zones)
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("External &rarr; Internal", "shorewall"), "shorewall", "shorewall", "external_internal",
                                                "modules/shorewall/graph/img/config/icn_global_active.gif",
                                                "modules/shorewall/graph/img/config/icn_global.gif"));
if ($lan_zones && $wan_zones)
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("NAT", "shorewall"), "shorewall", "shorewall", "masquerade",
                                                "modules/shorewall/graph/img/config/icn_global_active.gif",
                                                "modules/shorewall/graph/img/config/icn_global.gif"));

?>
