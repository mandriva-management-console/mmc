<?php

/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id$
 *
 * This file is part of LMC.
 *
 * LMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * LMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * module declaration
 */

require_once("modules/mail/includes/mail-xmlrpc.php");

$mod = new Module("mail");
$mod->setVersion("1.1.1");
$mod->setRevision("$Rev$");
$mod->setDescription(_T("Mail service","mail"));
$mod->setAPIVersion('2:0:0');
$mod->setPriority(600);

$mod->addACL("mailaccess", _T("Mail access","mail"));
$mod->addACL("maildisable", _T("Disable mail delivery","mail"));
$mod->addACL("mailalias", _T("Mail aliases","mail"));
$mod->addACL("mailbox", _T("Mail delivery path","mail"));

if (hasVDomainSupport()) {
    $mod->addACL("maildrop", _T("Forward to","mail"));

    $submod = new SubModule("mail");
    $submod->setDescription(_T("Mail", "mail"));
    $submod->setImg('img/navbar/pref');
    $submod->setDefaultPage("mail/mail/index");

    $page = new Page("index",_T("Mail domain list","mail"));
    $submod->addPage($page);

    $page = new Page("add",_T("Add a domain","mail"));
    $submod->addPage($page);

    $page = new Page("edit",_T("Edit a domain","mail"));
    $page->setOptions(array("visible"=>False));
    $submod->addPage($page);

    $page = new Page("delete",_("Delete a mail domain"));
    $page->setFile("modules/mail/mail/delete.php",
		   array("noHeader"=>True, "visible"=>False)
		   );
    $submod->addPage($page);

    $mod->addSubmod($submod);
} else {
    $mod->addACL("maildrop", _T("Mail drop","mail"));
}

$LMCApp =& LMCApp::getInstance();
$LMCApp->addModule($mod);

?>
