<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
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

$mod = new Module("squid");
$mod->setVersion("0.0.1");
$mod->setRevision('');
$mod->setDescription(_T("Web Proxy Content filter"),"squid");
$mod->setAPIVersion('1:1:0');

$submod = new SubModule("internet");
$submod->setDescription(_T("Proxy","squid"));
$submod->setImg('modules/squid/graph/navbar/proxy');
$submod->setDefaultPage("squid/internet/blackmanager");
$submod->setPriority(300);

$page = new Page("blackmanager", _T("Blacklist","squid"));
$submod->addPage($page);

$page = new Page("whitemanager", _T("Whitelist","squid"));
$submod->addPage($page);

$page = new Page("timemanager", _T("Time range whitelist","squid"));
$submod->addPage($page);

$page = new Page("extmanager", _T("Extension blacklist","squid"));
$submod->addPage($page);

$page = new Page("machmanager", _T("IP whitelist","squid"));
$submod->addPage($page);

$page = new Page("deleteb", _T("Delete from blacklist","squid"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("deletew", _T("Delete from whitelist","squid"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("deletex", _T("Delete from extension","squid"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("deletet", _T("Delete time range","squid"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("deletem", _T("Delete IP","squid"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

//$page = new Page("accesslog", _T("Logs in real time","squid"));
//$submod->addPage($page);

$mod->addSubmod($submod);

$MMCApp =&MMCApp::getInstance();
$MMCApp->addModule($mod);

?>
