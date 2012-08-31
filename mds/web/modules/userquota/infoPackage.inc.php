<?php
/**
 * (c) 2009 Glen Ogilvie
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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */




/**
 * module declaration
 */
$mod = new Module("userquota");
$mod->setVersion("2.4.2.93");
$mod->setRevision('$Rev$');
$mod->setDescription(_T("Manage user quotas for filesystems and networks", "userquota"));
$mod->setAPIVersion('0:0:0');
$mod->setPriority(700);

$MMCApp =& MMCApp::getInstance();
$MMCApp->addModule($mod);

?>
