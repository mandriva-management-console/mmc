<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2012 Mandriva, http://www.mandriva.com
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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

$sidemenu = new SideMenu();
$sidemenu->setClass("control");
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Core services", "services"), "services", "control", "index",
                                            "modules/services/graph/actions/icn_essential_active.png",
                                            "modules/services/graph/actions/icn_essential.png"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Other services", "services"), "services", "control", "others",
                                            "modules/services/graph/actions/icn_other_active.png",
                                            "modules/services/graph/actions/icn_other.png"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Services log", "services"), "services", "control", "log",
                                            "modules/services/graph/actions/icn_list_log_active.gif",
                                            "modules/services/graph/actions/icn_list_log.gif"));

?>
