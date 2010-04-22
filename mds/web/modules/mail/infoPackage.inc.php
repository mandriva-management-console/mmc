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

require_once("modules/mail/includes/mail-xmlrpc.php");

$mod = new Module("mail");
$mod->setVersion("2.4.0");
$mod->setRevision('$Rev$');
$mod->setDescription(_T("Mail service","mail"));
$mod->setAPIVersion("6:2:4");
$mod->setPriority(600);

$mod->addACL("mailaccess", _T("Mail access","mail"));
$mod->addACL("maildisable", _T("Disable mail delivery","mail"));
$mod->addACL("mailalias", _T("Mail aliases","mail"));
$mod->addACL("mailbox", _T("Mail delivery path","mail"));
$mod->addACL("mailhost", _T("Mail server host","mail"));
$mod->addACL("mailuserquota", _T("Mail user quota","mail"));

$mod->addACL("mailgroupaccess", _T("Mail group alias access", "mail"));

if (hasVDomainSupport()) {
    $mod->addACL("maildrop", _T("Forward to","mail"));

    $submod = new SubModule("mail");
    $submod->setDescription(_T("Mail", "mail"));
    $submod->setImg('modules/mail/graph/img/mail');
    $submod->setDefaultPage("mail/mail/index");

    $page = new Page("index",_T("Mail domain list","mail"));
    $submod->addPage($page);

    $page = new Page("add",_T("Add a domain","mail"));
    $submod->addPage($page);

    $page = new Page("edit",_T("Edit a domain","mail"));
    $page->setOptions(array("visible"=>False));
    $submod->addPage($page);

    $page = new Page("members",_T("View members","mail"));
    $page->setOptions(array("visible"=>False));
    $submod->addPage($page);

    $page = new Page("delete",_T("Delete a mail domain", "mail"));
    $page->setFile("modules/mail/mail/delete.php",
		   array("noHeader"=>True, "visible"=>False)
		   );
    $submod->addPage($page);

    $page = new Page("ajaxFilter");
    $page->setFile("modules/mail/mail/ajaxFilter.php",
		   array("AJAX" =>True,"visible"=>False)
		   );
    $submod->addPage($page);

    $page = new Page("ajaxDomainFilter");
    $page->setFile("modules/mail/mail/ajaxDomainFilter.php",
		   array("AJAX" =>True,"visible"=>False)
		   );
    $submod->addPage($page);

    $page = new Page("ajaxMailDomainFilter");
    $page->setFile("modules/mail/mail/ajaxMailDomainFilter.php",
		   array("AJAX" =>True,"visible"=>False)
		   );
    $submod->addPage($page);

    $mod->addSubmod($submod);
} else {
    $mod->addACL("maildrop", _T("Mail drop","mail"));
}

$MMCApp =& MMCApp::getInstance();
$MMCApp->addModule($mod);

?>
