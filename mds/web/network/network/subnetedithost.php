<?

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

$subnet = $_GET["subnet"];
$subnetInfos = getSubnet($_GET["subnet"]);
if (count($subnetInfos) == 0) {
    header("Location: " . urlStrRedirect("network/network/subnetindex"));
    exit;
}
$netmask = $subnetInfos[0][1]["dhcpNetMask"][0];
if ($_GET["action"] == "subnetaddhost") $title =  sprintf(_T("Add a DHCP host to subnet %s / %s"), $subnet, $netmask);
else $title = sprintf(_T("Edit DHCP host of subnet %s"), $subnet);
?>

<h2><?= $title; ?></h2>

<div class="fixheight"></div>

<?

global $error;

if (isset($_POST["badd"]) || (isset($_POST["bedit"]))) {
    $hostname = $_POST["hostname"];
    $macaddress = $_POST["macaddress"];
    $ipaddress = $_POST["ipaddress"];
    $filename = trim($_POST["filename"]);
    if (strlen($filename)) $filename = '"' . $filename . '"';
    $rootpath = trim($_POST["rootpath"]);
    if (strlen($rootpath)) $rootpath = '"' . $rootpath . '"';

    /* Check that the given IP address is in the subnet */
    if (!ipInNetwork($ipaddress, $subnet, $netmask)) {
        $error = _T("The specified IP address does not belong to the subnet.") . " ";
        setFormError("ipaddress");
    }
    /* Check that the given address is not in the dynamic pool range */
    $pool = getPool($subnet);
    if (count($pool)) {
        $range = $pool[0][1]["dhcpRange"][0];
        list($ipstart, $ipend) = explode(" ", $range);
        if (ipInRange($ipaddress, $ipstart, $ipend)) {
            $error .= _T("The specified IP address belongs to the dynamic pool range of the subnet.") . " ";
            setFormError("ipaddress");
        }
    }
    if (isset($_POST["dnsrecord"])) {
        /* Check that no hostname and reverse for this IP address exist in the DNS zone */
        $options = getSubnetOptions(getSubnet($subnet));
        if (isset($options["domain-name"])) {
            $zone = $options["domain-name"];
            if (hostExists($zone, $hostname)) {
                $error .= sprintf(_T("The specified hostname has been already registered in DNS zone %s."), $zone) . " ";
                setFormError("hostname");
                $hostname = "";
            }
            if (ipExists($zone, $ipaddress)) {
                $error .= sprintf(_T("The specified IP address has been already registered in DNS zone %s."), $zone) . " ";
                setFormError("ipaddress");
                $ipaddress = "";
            }
        }
    }
    if (!isset($error)) {
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
        // Display result message
        if (!isXMLRPCError() && isset($_POST["badd"])) {
            $result .= _T("Host successfully added.");
            new NotifyWidgetSuccess($result);
            header("Location: " . urlStrRedirect("network/network/subnetmembers", array("subnet" => $subnet)));
        }
    }
    if (isset($error)) new NotifyWidgetFailure($error);
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
} else if ($_GET["action"] == "subnetaddhost") {
    if (!isset($error)) {
        /* Reset the field only if no error where found */
        $hostname = $_GET["host"];
        $macaddress = $_GET["macaddress"];
    }
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
$tr->setCssError("hostname");
$tr->display(array("value" => $hostname, "required" => True));

$tr = new TrFormElement(_T("MAC address"),new MACInputTpl("macaddress"));
$tr->display(array("value" => $macaddress, "required" => True));

$tr = new TrFormElement(_T("IP address"),new IPInputTpl("ipaddress"));
$tr->setCssError("ipaddress");
$tr->display(array("value" => $ipaddress, "required" => True));

?>
</table>
<table cellspacing="0">
<?
$tr = new TrFormElement(_T("Other DHCP options"), new HiddenTpl(""));
$tr->display(array());

$tr = new TrFormElement(_T("Initial boot file name"),new IA5InputTpl("filename"));
$tr->display(array("value"=>$filename));

$tr = new TrFormElement(_T("Path to the root filesystem"),new IA5InputTpl("rootpath"));
$tr->display(array("value"=>$rootpath));
?>
</table>

<? if ($_GET["action"] == "subnetaddhost") { ?>

<table>
<?
$options = getSubnetOptions(getSubnet($subnet));
if (isset($options["domain-name"])) {
    /*
       If the DHCP domain name option is set, and corresponds to an existing DNS zone
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
