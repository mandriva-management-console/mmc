<?php
/**
 * (c) 2014 Zentyal, http://www.zentyal.com
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
 *
 * Author(s):
 *   Julien Kerihuel <jkerihuel@zentyal.com>
 */

/**
 * module declaration
 */
$mod = new Module("samba4");
$mod->setVersion("1.0.0");
$mod->setRevision('$Rev$');
$mod->setDescription(_T("SAMBA4 management"), "samba4");
$mod->setAPIVersion("1:0:0");
$mod->setPriority(20);

/**
 * domain controller submod definition
 */
$submod = new SubModule("domaincontroller");
$submod->setDescription(_T("Domain", "samba4"));
$submod->setImg('modules/samba4/graph/navbar/share');
$submod->setDefaultPage("samba4/domaincontroller/index");
$submod->setPriority(20);

$page = new Page("index",_T("Povisioning", "samba4"));
$page->setImg("modules/samba4/graph/img/provision/icn_provision_active.gif",
	      "modules/samba4/graph/img/provision/icn_provision.gif");
$submod->addPage($page);

$page = new Page("purge",_T("purge SAMBA configuration", "samba4"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$mod->addSubmod($submod);

$MMCApp =& MMCApp::getInstance();
$MMCApp->addModule($mod);

?>
