<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2014 Mandriva, http://www.mandriva.com/
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

$submods = array('shares', 'machines', 'config');

$sidemenu = new SideMenu();
$sidemenu->setClass(join(" ", $submods));

$MMCApp =& MMCApp::getInstance();
$mod = $MMCApp->getModule('samba');
$smbConf = xmlCall("samba.getSmbInfo", null);

foreach ($submods as $submod) {
    $submod = $mod->getSubmod($submod);
    foreach ($submod->getPages() as $page) {
        if ($page->hasAccessAndVisible($mod, $submod)) {
            if ($submod->getName() == "machines" && !$smbConf['pdc'])
                break;
            $item = new SideMenuItem($page->getDescription(), $mod->getName(), $submod->getName(), $page->getAction(), $page->getImg("active"), $page->getImg("default"));
            $item->cssId = join("_", array($mod->getName(), $submod->getName(), $page->getAction()));
            $sidemenu->addSideMenuItem($item);
        }
    }
}

?>
