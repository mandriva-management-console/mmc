<?

/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id: infoPackage.inc.php 8 2006-11-13 11:08:22Z cedric $
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
      
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "add") $title =  _T("Add a mail domain");
else {
    $title =  _T("Edit mail domain");;
    $sidemenu->forceActiveItem("index"); 
}
?>

<h2><?= $title; ?></h2>

<div class="fixheight"></div>

<?

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

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
    if (isset($error)) {
        $n = new NotifyWidget();
        $n->flush();
        $n->add("<div id=\"errorCode\">$error</div>");
        $n->setLevel(4);
        $n->setSize(600);
    }
    // Display result message
    if (isset($result)&&!isXMLRPCError()) {
        $n = new NotifyWidget();
	$n->flush();
	$n->add("<div id=\"validCode\">$result</div>");
	$n->setLevel(0);
	$n->setSize(600);
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
    if (isset($result)&&!isXMLRPCError()) {
        $n = new NotifyWidget();
	$n->flush();
	$n->add("<div id=\"validCode\">$result</div>");
	$n->setLevel(0);
	$n->setSize(600);
    }
}

if ($_GET["action"] == "edit") {
    $domainname = $_GET["mail"];
    $domain = getVDomain($domainname);
    $description = $domain[0][1]["virtualdomaindescription"][0];
    $mailuserquota = $domain[0][1]["mailuserquota"][0];
}


?>
<form name="domainform" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validateForm();">
<table cellspacing="0">

<?

$arrDomain = array("value" => $domainname);
if ($_GET["action"] == "add") {
    $elt1 = new DomainInputTpl("domainname");
    $arrDomain["required"] = true;
} else {
    $elt1 = new HiddenTpl("domainname");
}

$tr = new TrFormElement(_T("Mail domain"), $elt1);
$tr->display($arrDomain);

$tr = new TrFormElement(_T("Description"), new InputTpl("description"));
$tr->display(array("value" => $description));

$tr = new TrFormElement(_T("Default mail quota for users created in this domain (in kB)"), new InputTpl("mailuserquota", '/^[0-9]*$/'));
$tr->display(array("value" => $mailuserquota));

?>

</table>
<? if ($_GET["action"] == "add") { ?>
    <input name="badd" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<? } else { ?>
    <div id="expertMode" <?displayExpertCss();?>><input name="breset" type="submit" class="btnPrimary" value="<?= _T("Reset users quota to default"); ?>" /></div>
    <input name="bedit" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<? } ?>

</form>

<script>
document.body.onLoad = document.domainform.domainname.focus();
</script>
