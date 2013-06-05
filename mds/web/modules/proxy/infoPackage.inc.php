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
$mod = new Module("proxy");
$mod->setVersion("2.5.0");
$mod->setRevision('$Rev$');
$mod->setDescription(_T("Web proxy"),"proxy");
$mod->setAPIVersion('1:1:0');

/**
 * user submod definition
 */
$submod = new SubModule("blacklist");
$submod->setDescription(_T("Proxy","proxy"));
$submod->setImg('modules/proxy/graph/navbar/proxy');
$submod->setDefaultPage("proxy/blacklist/statut");
$submod->setPriority(300);


$page = new Page("index",_T("Blacklist","proxy"));
$submod->addPage($page);

$page = new Page("delete",_T("Remove a domain in the blacklist","proxy"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("add",_T("Add a domain in the blacklist","proxy"));
$submod->addPage($page);

$page = new Page("restart",_T("Restart proxy web service","proxy"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("statut",_T("Proxy status page","proxy"));
$submod->addPage($page);

$mod->addSubmod($submod);

$MMCApp =&MMCApp::getInstance();
$MMCApp->addModule($mod);

?>
