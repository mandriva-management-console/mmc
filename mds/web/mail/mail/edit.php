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
    if (!preg_match("/^[a-z][0-9\-a-zA-Z\.]+$/", $domainname)) {
        $error = _("Invalid domain name");
    } else {
        addVDomain($domainname);
	setVDomainDescription($domainname, $description);
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
    }
}

if (isset($_POST["bedit"])) {
    $domainname = $_GET["mail"];
    $description = $_POST["description"];
    setVDomainDescription($domainname, $description);    
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
}


?>
<form name="domainform" method="post" action="<? echo $PHP_SELF; ?>">
<table cellspacing="0">
<tr>
<td width="40%" style="text-align:right;"><?= _("Mail domain")?></td>

<? if ($_GET["action"] == "add") { ?>
    <td><input id="domainname" name="domainname" type="text" class="textfield" size="30" value="<?php if (isset($error)){echo $domainname;} ?>" /></td>
<? } else { ?>
    <td><?php echo $domainname; ?></td>
<? } ?>

</tr>
<tr>
<td style="text-align:right;"><?= _("Description")?></td>
<? if ($_GET["action"] == "add") { ?>
    <td><input id="description" name="description" type="text" class="textfield" size="30" value="<?php if (isset($error)){echo $description;} ?>" /></td>
<? } else { ?>
    <td><input id="description" name="description" type="text" class="textfield" size="30" value="<?php echo $description; ?>" /></td>
<? } ?>
</tr>
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
