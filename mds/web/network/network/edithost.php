<?

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "addhost") $title =  _T("Add a host");
else $title =  _T("Edit host");;
?>

<h2><?= $title; ?></h2>

<div class="fixheight"></div>

<?

$p = new PageGenerator();
$sidemenu->forceActiveItem("index");
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

$zone = $_GET["zone"];

if (isset($_POST["badd"])) {
    $hostname = $_POST["hostname"];
    $address = $_POST["address"];
    addRecordA($hostname, $address, $zone);        
    // Display result message
    if (!isXMLRPCError()) {
        $result = _T("Host successfully added.");
        $n = new NotifyWidget();
	$n->flush();
	$n->add("<div id=\"validCode\">$result</div>");
	$n->setLevel(0);
	$n->setSize(600);
	header("Location: " . urlStrRedirect("network/network/index"));
    }
}

if ($_GET["action"] == "edit") {
    $hostname = $_GET["host"];
}

?>

<form name="hostform" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validateForm();">
<table cellspacing="0">

<?

$a = array("value" => $hostname, "extra" => "." . $zone);
if ($_GET["action"] == "addhost") {
    $formElt = new HostnameInputTpl("hostname");
    $a["required"] = True;
} else {
    $formElt = new HiddenTpl("hostname");
}

$tr = new TrFormElement(_T("Host name"), $formElt);
$tr->display($a);

$tr = new TrFormElement(_T("Network address"), new IPInputTpl("address"));
if ($_GET["action"] == "addhost") {
    $zoneaddress = getZoneNetworkAddress($zone);
    if ($zoneaddress === 0) $network = "";
    else $network = $zoneaddress . ".";
    $tr->display(array("value"=>$network, "required" => True));
} else {
    $tr->display(array("value"=>$address, "required" => True));
}

?>

</table>

<? if ($_GET["action"] == "addhost") { ?>
    <input name="badd" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<? } else { ?>
    <input name="bedit" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<? } ?>

</form>

<script>
document.body.onLoad = document.hostform.hostname.focus();
</script>
