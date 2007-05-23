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
global $error;
if (isset($_POST["badd"])) {
    $hostname = $_POST["hostname"];
    $address = $_POST["address"];
    
    /* Basic checks */
    if (hostExists($zone, $hostname)) {
        $error = _T("The specified hostname has been already recorded in this zone.") . " ";
        setFormError("hostname");
        $hostname = "";
    }
    if (ipExists($zone, $address)) {
        $error .= _T("The specified IP address has been already recorded in this zone");
        setFormError("address");
    } else $keepaddress = True;
    
    if (!isset($error)) {
        addRecordA($hostname, $address, $zone);        
        if (!isXMLRPCError()) {
            new NotifyWidgetSuccess(_T("Host successfully added."));
            header("Location: " . urlStrRedirect("network/network/zonemembers", array("zone" => $zone)));
        }
    } else new NotifyWidgetFailure($error);

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
    if (isset($_GET["host"])) $a["value"] = $_GET["host"]; /* pre-fill hostname field when adding a host */
} else {
    $formElt = new HiddenTpl("hostname");
}

$tr = new TrFormElement(_T("Host name"), $formElt);
$tr->setCssError("hostname");
$tr->display($a);

$tr = new TrFormElement(_T("Network address"), new IPInputTpl("address"));
$tr->setCssError("address");
if ($_GET["action"] == "addhost") {
    if (isset($_GET["ipaddress"])) $network = $_GET["ipaddress"]; /* pre-fill IP address field when adding a host */
    else {
        if (isset($error) && isset($keepaddress))
            $network = $address;
        else {
            $zoneaddress = getZoneNetworkAddress($zone);
            if (!count($zoneaddress)) $network = "";
            else $network = $zoneaddress[0] . ".";
        }
    }
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
