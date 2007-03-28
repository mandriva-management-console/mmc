<?

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "subnetaddhost") $title =  _T("Add a DHCP host");
else $title =  _T("Edit DHCP host");;
?>

<h2><?= $title; ?></h2>

<div class="fixheight"></div>

<?

$subnet = $_GET["subnet"];

if (isset($_POST["badd"]) || (isset($_POST["bedit"]))) {
    $hostname = $_POST["hostname"];
    $macaddress = $_POST["macaddress"];
    $ipaddress = $_POST["ipaddress"];
    $filename = trim($_POST["filename"]);
    if (strlen($filename)) $filename = '"' . $filename . '"';
    $rootpath = trim($_POST["rootpath"]);
    if (strlen($rootpath)) $rootpath = '"' . $rootpath . '"';
    if (isset($_POST["badd"])) {
        addHostToSubnet($subnet, $hostname);
	setHostOption($hostname, "host-name", $hostname);
        if (isset($_POST["dnsrecord"])) {
            $options = getSubnetOptions(getSubnet($subnet));
            if (isset($options["domain-name"])) addRecordA($hostname, $ipaddress, $options["domain-name"]);
        }
    }
    setHostOption($hostname, "root-path", $rootpath);
    setHostStatement($hostname, "filename", $filename);
    setHostHWAddress($hostname, $macaddress);
    setHostStatement($hostname, "fixed-address", $ipaddress);
}

if ($_GET["action"] == "subnetedithost") {
    $hostname = $_GET["host"];
    $host = getHost($hostname);
    $options = array();
    foreach($host[0][1]["dhcpOption"] as $option) {
        list($name, $value) = explode(" ", $option, 2);
        $options[$name] = trim($value, '"');
    }
    $statements = array();
    foreach($host[0][1]["dhcpStatements"] as $statement) {
        list($name, $value) = explode(" ", $statement, 2);
        $statements[$name] = trim($value, '"');
    }
    $macaddress = $host[0][1]["dhcpHWAddress"][0];
    list($tmp, $macaddress) = explode(" ", $macaddress);
    $ipaddress = $statements["fixed-address"];
    $filename = $statements["filename"];
    $rootpath = $options["root-path"];
}

$p = new PageGenerator();
$sidemenu->forceActiveItem("subnetindex");
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

?>

<form id="edit" name="subnetedithostform" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validateForm();" >
<table cellspacing="0">

<?
if ($_GET["action"]=="subnetaddhost") {
    $formElt = new HostnameInputTpl("hostname");
} else {
    $formElt = new HiddenTpl("hostname");
}

$tr = new TrFormElement(_T("Host name"), $formElt);
$tr->display(array("value" => $hostname, "required" => True));

$tr = new TrFormElement(_T("MAC address"),new MACInputTpl("macaddress"));
$tr->display(array("value" => $macaddress, "required" => True));

$tr = new TrFormElement(_T("IP address"),new IPInputTpl("ipaddress"));
$tr->display(array("value" => $ipaddress, "required" => True));

?>
</table>
<table>
<?
$tr = new TrFormElement(_T("Other DHCP options"), new HiddenTpl(""));
$tr->display(array());

$tr = new TrFormElement(_T("Initial boot file name"),new InputTpl("filename"));
$tr->display(array("value"=>$filename));

$tr = new TrFormElement(_T("Path to the root filesystem"),new InputTpl("rootpath"));
$tr->display(array("value"=>$rootpath));
?>
</table>

<? if ($_GET["action"] == "subnetaddhost") { ?>

<table>
<?
$options = getSubnetOptions(getSubnet($subnet));
if (isset($options["domain-name"])) {
    /* if the DHCP domain name option is set, and corresponds to an existing DNS zone
       we ask the user if she/he wants to record the machine in the DNS zone too.
    */
    $domain = $options["domain-name"];
    if (zoneExists($domain)) {
        $tr = new TrFormElement(_T("Also records this machine into DNS zone") . "&nbsp;" . $domain, new CheckboxTpl("dnsrecord"));
        $tr->display(array("value" => "CHECKED"));
    }
}
?>
</table >
<? } ?>

<? if ($_GET["action"] == "subnetaddhost") { ?>
    <input name="badd" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<? } else { ?>
    <input name="bedit" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<? } ?>

</form>

<script>
document.body.onLoad = document.subnetedithostform.hostname.focus();
</script>
