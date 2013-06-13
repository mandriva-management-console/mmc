<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: infoPackage.inc.php 8 2006-11-13 11:08:22Z cedric $
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

require("modules/mail/mainSidebar.php");
require("graph/navbar.inc.php");
require_once("modules/mail/includes/mail.inc.php");

$error = "";
$result = "";

if ($_GET["action"] == "add") {
    $mode = "add";
    $title = _T("Add a mail domain", "mail");
    $domainname = "";
    $description = "";
    $domainquota = "";
}
else {
    $mode = "edit";
    $title = _T("Edit mail domain", "mail");;
    $sidemenu->forceActiveItem("index");
    $domainname = $_GET["domain"];
    $domain = getVDomain($domainname);
    $description = "";
    $domainquota = "";
    if (isset($domain[0][1]["virtualdomaindescription"][0]))
        $description = $domain[0][1]["virtualdomaindescription"][0];
    if (isset($domain[0][1]["mailuserquota"][0]))
        $domainquota = $domain[0][1]["mailuserquota"][0];
}

if ($_POST) {
    $domainname = $_POST["domainname"];
    $description = stripslashes($_POST["description"]);
    if (isset($_POST["unlimitedquota"])) $_POST["domainquota"] = "0";
    $domainquota = $_POST["domainquota"];

    if (isset($_POST["badd"])) {
        if (!preg_match("/^[a-z][0-9\-a-zA-Z\.]+$/", $domainname)) {
            $error = _T("Invalid domain name", "mail");
        }
        else {
            addVDomain($domainname);
    	    setVDomainDescription($domainname, $description);
	        if (strlen($domainquota) == 0) $domainquota = "0";
    	    setVDomainQuota($domainname, $domainquota);
            $result = _T("The mail domain has been added.", "mail");
        }
        // Display error message
        if ($error)
            new NotifyWidgetFailure($error);
        // Display result message
        if ($result && !isXMLRPCError()) {
            new NotifyWidgetSuccess($result);
    	    header("Location: " . urlStrRedirect("mail/domains/index"));
            exit;
        }
    }
    else if (isset($_POST["bedit"]) || isset($_POST["breset"])) {
        setVDomainDescription($domainname, $description);
        if (strlen($domainquota)) setVDomainQuota($domainname, $domainquota);
        $result = _T("The mail domain has been modified.", "mail") . "<br />";
        if (isset($_POST["breset"])) {
            resetUsersVDomainQuota($domainname);
            $result .= _T(" The quota of all users of this mail domain have been reset.") . "<br />";
        }
        // Display result message
        if ($result && !isXMLRPCError())
            new NotifyWidgetSuccess($result);
    }

}

$p = new PageGenerator($title);
$p->setSideMenu($sidemenu);
$p->display();

$f = new ValidatingForm();
$f->push(new Table());

if ($mode == "add")
    $domainTpl = new DomainInputTpl("domainname");
else
    $domainTpl = new HiddenTpl("domainname");

$f->add(
    new TrFormElement(_T("Mail domain"), $domainTpl),
    array("value" => $domainname, "required" => true)
);

$f->add(
    new TrFormElement(_T("Description"), new InputTpl("description")),
    array("value" => $description)
);

$f->add(
    new TrFormElement(_T("Default mail quota for users created in this domain (in kB)"),
        new QuotaTpl("domainquota", '/^[0-9]*$/')
    ),
    array("value" => $domainquota)
);

$f->pop();

if ($mode == "add") {
    $f->addButton("badd", _("Create"));
}
else {
    $f->addExpertButton("breset", _T("Reset users quota to default", "mail"));
    $f->addButton("bedit", _("Confirm"));
}

$f->pop();
$f->display();

?>
