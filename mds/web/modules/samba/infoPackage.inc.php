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

/**
 * module declaration
 */
$mod = new Module("samba");
$mod->setVersion("2.5.0");
$mod->setRevision('$Rev$');
$mod->setDescription(_T("SAMBA management"),"samba");
$mod->setAPIVersion("5:3:4");
$mod->setPriority(20);

/**
 * shares submod definition
 */

$submod = new SubModule("shares");
$submod->setDescription(_T("Shares","samba"));
$submod->setImg('modules/samba/graph/navbar/share');
$submod->setDefaultPage("samba/shares/index");
$submod->setPriority(20);

$page = new Page("index",_T("List shares","samba"));
$submod->addPage($page);

$page = new Page("add",_T("Add a share","samba"));
$submod->addPage($page);

$page = new Page("backup",_T("Backup a share","samba"));
$page->setOptions( array ("noHeader" => True,"visible" => False));
$submod->addPage($page);

$page = new Page("delete",_T("Remove a share","samba"));
$page->setOptions( array ("noHeader" => True,"visible" => False));
$submod->addPage($page);

$page = new Page("details",_T("Share details","samba"));
$page->setOptions( array ("visible" => False));
$submod->addPage($page);

$mod->addSubmod($submod);


/**
 *  Machines submod
 */
$submod = new SubModule("machines");
$submod->setVisibility(False);
$submod->setImg('modules/base/graph/navbar/computer');
$submod->setDefaultPage("samba/machines/index");
$submod->setDescription(_T("Machines"),"samba");
$submod->setAlias('shares');

$page = new Page("index",_T("Computer list","samba"));
$submod->addPage($page);

$page = new Page("ajaxFilter");
$page->setOptions(array("AJAX" =>True,"visible"=>False));
$submod->addPage($page);

/*$page = new Page("add",_T("Add a computer","samba"));
$submod->addPage($page);*/

$page = new Page("edit",_T("Edit a computer","samba"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("delete",_T("Delete a computer","samba"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$mod->addSubmod($submod);


/**
 * Config submod
 */
$submod = new SubModule("config");
$submod->setDefaultPage("samba/config/index");
$submod->setImg('modules/samba/graph/navbar/pref');
$submod->setDescription(_T("Configuration"),"samba");
$submod->setVisibility(False);
$submod->setAlias('shares');


$page = new Page("index",_T("SAMBA configuration","samba"));
$submod->addPage($page);

$page = new Page("restart",_T("restart SAMBA service","samba"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("reload",_T("Reload SAMBA service","samba"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$mod->addSubmod($submod);

/**
 * Declare ACL
 */
$mod->addACL("isSamba",_T("Add/delete SAMBA's attributes","samba"));
$mod->addACL("isSmbDesactive",_T("Enable/Disable account","samba"));
$mod->addACL("isSmbLocked",_T("Lock/Unlock account","samba"));
$mod->addACL("hasProfile",_T("Network profile","samba"));
$mod->addACL("sambaPwdCanChange",_T("Can change password","samba"));
$mod->addACL("sambaPwdLastSet",_T("Must change password","samba"));
$mod->addACL("sambaKickoffTime",_T("Account expiration","samba"));
$mod->addACL("sambaLogonScript",_T("Logon script","samba"));
$mod->addACL("sambaHomePath",_T("Home path","samba"));
$mod->addACL("sambaHomeDrive",_T("Home drive","samba"));

$MMCApp =&MMCApp::getInstance();
$MMCApp->addModule($mod);


//add status page
$base = &$MMCApp->getModule('base');
$status = &$base->getSubmod('status');

$page = new Page("sambastatus",_T("SAMBA status","samba"));
$page->setFile("modules/samba/status/index.php");
$status->addPage($page);

?>
