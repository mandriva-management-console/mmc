<?

$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
	      array("name" => _T("Mail domain list")));
	      
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "add") $title =  _T("Add a mail domain");
else $title =  _T("Edit mail domain");;
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
        $error = _("Invalid domain name");
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
}

if (isset($_POST["bedit"])) {
    $domainname = $_GET["mail"];
    $description = $_POST["description"];
    $mailuserquota = $_POST["mailuserquota"];
    setVDomainDescription($domainname, $description);
    if (strlen($mailuserquota)) setVDomainQuota($domainname, $mailuserquota);
    $result = _T("The mail domain has been modified.");
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

if ($_GET["action"] == "add") {
    $elt1 = new InputTpl("domainname");
} else {
    $elt1 = new HiddenTpl("domainname");
}

$tr = new TrFormElement(_T("Mail domain"), $elt1);
$tr->display(array("value" => $domainname));

$tr = new TrFormElement(_T("Description"), new InputTpl("description"));
$tr->display(array("value" => $description));

$tr = new TrFormElement(_T("Default mail quota for users created in this domain (in KB)"), new InputTpl("mailuserquota", '/^[0-9]*$/'));
$tr->display(array("value" => $mailuserquota));

?>

</table>
<? if ($_GET["action"] == "add") { ?>
    <input name="badd" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<? } else { ?>
    <input name="bedit" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<? } ?>

</form>

<script>
document.body.onLoad = document.domainform.domainname.focus();
</script>
