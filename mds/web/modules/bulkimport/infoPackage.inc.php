<?php
/**
 * (c) 2009 Open Systems Specilists - Glen Ogilvie
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
$mod = new Module("bulkimport");
$mod->setVersion("2.5.0");
$mod->setRevision('$Rev$');
$mod->setDescription(_T("Bulk user manager via CSV files", "bulkimport"));
$mod->setAPIVersion('0:0:0');

/* Get the base module instance reference */
$base = &$MMCApp->getModule('base');
/* Get the computers sub-module instance reference */
$users = &$base->getSubmod('users');

/* Add the page to the module */
$page = new Page("bulkimport",_T("Bulk import (CSV)", "bulkimport"));
$page->setFile("modules/bulkimport/import/index.php");
$users->addPage($page);

$MMCApp =& MMCApp::getInstance();
$MMCApp->addModule($mod);

unset ($page);
unset ($users);

?>
