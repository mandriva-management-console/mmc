<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com/
 *
 * $Id: mainSidebar.php 7484 2010-09-16 14:45:45Z jpbraun $
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

$sidemenu= new SideMenu();
$sidemenu->setClass("domains aliases");

if (hasVDomainSupport()) {

    $s = new SideMenuItem(_T("Mail domain list", "mail"), "mail", "domains", "index",
        "modules/mail/graph/img/mail_active.png", "modules/mail/graph/img/mail_inactive.png");
    $s->setCssId("index_domains");
    $sidemenu->addSideMenuItem($s);

    $s = new SideMenuItem(_T("Add a domain", "mail"), "mail", "domains", "add",
        "modules/mail/graph/img/mailadd_active.png", "modules/mail/graph/img/mailadd_inactive.png");
    $s->setCssId("add_domain");
    $sidemenu->addSideMenuItem($s);
}
if (hasVAliasesSupport()) {
    $s = new SideMenuItem(_T("Virtual aliases list", "mail"), "mail", "aliases", "index",
        "modules/mail/graph/img/mail_active.png", "modules/mail/graph/img/mail_inactive.png");
    $s->setCssId("index_aliases");
    $sidemenu->addSideMenuItem($s);

    $s = new SideMenuItem(_T("Add a virtual alias", "mail"), "mail", "aliases", "add",
        "modules/mail/graph/img/mailadd_active.png", "modules/mail/graph/img/mailadd_inactive.png");
    $s->setCssId("add_alias");
    $sidemenu->addSideMenuItem($s);
}

?>
