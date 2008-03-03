<?

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
      
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "add") $title =  _T("Add a mail domain");
else {
    $title =  _T("Edit mail domain");;
    $sidemenu->forceActiveItem("index"); 
}

$p = new PageGenerator($title);
$p->setSideMenu($sidemenu);
$p->display();

if (isset($_POST["badd"])) {
    $domainname = $_POST["domainname"];
    $description = $_POST["description"];
    $mailuserquota = $_POST["mailuserquota"];
    if (!preg_match("/^[a-z][0-9\-a-zA-Z\.]+$/", $domainname)) {
        $error = _T("Invalid domain name");
    } else {
        addVDomain($domainname);
	setVDomainDescription($domainname, $description);
	if (strlen($mailuserquota) == 0) $mailuserquota = "0";
	setVDomainQuota($domainname, $mailuserquota);
        $result = _T("The mail domain has been added.");
    }
    // Display error message
    if (isset($error)) new NotifyWidgetFailure($error);

    // Display result message
    if (isset($result)&&!isXMLRPCError()) {
        new NotifyWidgetSuccess($result);
	header("Location: " . urlStrRedirect("mail/mail/index"));
    }
} else if (isset($_POST["bedit"]) || isset($_POST["breset"])) {
    $domainname = $_POST["domainname"];
    $description = $_POST["description"];
    $mailuserquota = $_POST["mailuserquota"];
    setVDomainDescription($domainname, $description);
    if (strlen($mailuserquota)) setVDomainQuota($domainname, $mailuserquota);
    $result = _T("The mail domain has been modified.");
    if (isset($_POST["breset"])) {
        resetUsersVDomainQuota($domainname);
        $result .= " " . _T(" The quota of all users of this mail domain have been reset.");
    }
    // Display result message
    if (isset($result)&&!isXMLRPCError()) new NotifyWidgetSuccess($result);
}

if ($_GET["action"] == "edit") {
    $domainname = $_GET["mail"];
    $domain = getVDomain($domainname);
    $description = $domain[0][1]["virtualdomaindescription"][0];
    $mailuserquota = $domain[0][1]["mailuserquota"][0];
}

$f = new ValidatingForm();
$f->push(new Table());

$arrDomain = array("value" => $domainname);
if ($_GET["action"] == "add") {
    $elt1 = new DomainInputTpl("domainname");
    $arrDomain["required"] = true;
} else {
    $elt1 = new HiddenTpl("domainname");
}

$f->add(
        new TrFormElement(_T("Mail domain"), $elt1),
        $arrDomain
        );

$f->add(
        new TrFormElement(_T("Description"), new InputTpl("description")),
        array("value" => $description)
        );

$f->add(
        new TrFormElement(_T("Default mail quota for users created in this domain (in kB)"), new InputTpl("mailuserquota", '/^[0-9]*$/')),
        array("value" => $mailuserquota)
        );

$f->pop();
if ($_GET["action"] == "add") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addExpertButton("breset", _T("Reset users quota to default"));
    $f->addButton("bedit", _("Confirm"));    
}
$f->pop();
$f->display();
?>
