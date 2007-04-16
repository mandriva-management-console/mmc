<?
require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "subnetadd") $title =  _T("Add a DHCP subnet");
else $title =  _T("Edit DHCP subnet");;
?>

<h2><?= $title; ?></h2>

<div class="fixheight"></div>

<?

$p = new PageGenerator();
if ($_GET["action"] == "subnetedit") $sidemenu->forceActiveItem("subnetindex");
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

if (isset($_POST["badd"]) || (isset($_POST["bedit"]))) {
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    $description = $_POST["description"];
    if (isset($_POST["badd"])) {
        addSubnet($subnet, $netmask, $description);
    } else {
        setSubnetNetmask($subnet, $netmask);
        setSubnetDescription($subnet, $description);
    }
    $names = array("broadcast-address", "routers", "domain-name", "domain-name-servers", "ntp-servers", "root-path");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
	if (in_array($name, array("domain-name", "root-path")))
            $value = '"' . $value . '"';
	if (in_array($name, array("domain-name-servers", "ntp-servers")))
            $value = str_replace(" ", ",", $value);
        setSubnetOption($subnet, $name, $value);
    }
    $names = array("filename");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
	if (in_array($name, array("filename")))
            $value = '"' . $value . '"';
        setSubnetStatement($subnet, $name, $value);
    }
    if (isset($_POST["badd"])) $pool = array();
    else $pool = getPool($subnet);

    if (isset($_POST["subnetpool"])) {
        if (isset($_POST["ipstart"]) && isset($_POST["ipend"])) {
            $ipstart = $_POST["ipstart"];
            $ipend = $_POST["ipend"];
            if (ipLowerThan($ipstart, $ipend) && ipInNetwork($ipstart, $subnet, $netmask) && ipInNetwork($ipend, $subnet, $netmask)) {
                if (count($pool)) setPoolRange($subnet, $ipstart, $ipend);
                else {
                    /* The pool needs to be created */
                    addPool($subnet, $subnet, $ipstart, $ipend);
                }
            }
        }
    } else {
        if (count($pool)) delPool($subnet);
    }
    if (!isXMLRPCError()) {
        if (isset($_POST["badd"])) {
            new NotifyWidgetSuccess(_T("Subnet successfully added. You must restart the DHCP service."));
            header("Location: " . urlStrRedirect("network/network/subnetindex"));
        } else if (isset($_POST["bedit"])) {
            new NotifyWidgetSuccess(_T("Subnet successfully modified. You must restart the DHCP service."));
        }
    }
}

if ($_GET["action"] == "subnetedit") {
    $subnet = getSubnet($_GET["subnet"]);
    $cn = $subnet[0][1]["cn"][0];
    $netmask = $subnet[0][1]["dhcpNetMask"][0];
    $description = $subnet[0][1]["dhcpComments"][0];
    $options = getSubnetOptions($subnet);
    $statements = getSubnetStatements($subnet);
    $pool = getPool($cn);
    if (count($pool)) {
        $hasSubnetPool = "checked";
        $range = $pool[0][1]["dhcpRange"][0];
        list($ipstart, $ipend) = explode(" ", $range);
    } else $hasSubnetPool = "";
}

?>

<form id="edit" name="subnetform" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validateForm();">
<table cellspacing="0">

<?
if ($_GET["action"]=="subnetadd") {
    $formElt = new IPInputTpl("subnet");
} else {
    $formElt = new HiddenTpl("subnet");
}

$tr = new TrFormElement(_T("DHCP subnet"), $formElt);
$tr->display(array("value" => $cn, "required" => True));

$tr = new TrFormElement(_T("Netmask"),new NetmaskInputTpl("netmask"));
$tr->display(array("value" => $netmask, "required" => True, "extra" => "(e.g. 24 for a /24 network)"));

$tr = new TrFormElement(_T("Description"),new IA5InputTpl("description"));
$tr->display(array("value" => $description));
?>
</table>
<table>
<?
$tr = new TrFormElement(_("DHCP options related to clients network parameters"), new HiddenTpl(""));
$tr->display(array());

$tr = new TrFormElement(_T("Broadcast address"), new IPInputTpl("broadcast-address"));
$tr->display(array("value"=>$options["broadcast-address"]));

$tr = new TrFormElement(_T("Domain name"),new DomainInputTpl("domain-name"));
$tr->display(array("value"=>$options["domain-name"]));

$tr = new TrFormElement(_T("Routers"),new InputTpl("routers"));
$tr->display(array("value"=>$options["routers"]));

$tr = new TrFormElement(_T("Domain name servers"),new InputTpl("domain-name-servers"));
$tr->display(array("value"=>$options["domain-name-servers"]));

$tr = new TrFormElement(_T("NTP servers"),new InputTpl("ntp-servers"));
$tr->display(array("value"=>$options["ntp-servers"]));
?>
</table>
<table>
<?
$tr = new TrFormElement(_T("Other DHCP options"), new HiddenTpl(""));
$tr->display(array());

$tr = new TrFormElement(_T("Initial boot file name"),new InputTpl("filename"));
$tr->display(array("value"=>$statements["filename"]));

$tr = new TrFormElement(_T("Path to the root filesystem"),new InputTpl("root-path"));
$tr->display(array("value"=>$options["root-path"]));
?>
</table>

<?
print "<table>";
$tr = new TrFormElement(_T("Dynamic pool for non-registered DHCP clients", "network"),new CheckboxTpl("subnetpool"));
$param=array("value"=>$hasSubnetPool,
	     "extraArg"=>'onclick="toggleVisibility(\'pooldiv\');"');
$tr->display($param);

print "</table>";
if (!$hasSubnetPool) $style = 'style =" display: none;"';
else $style = "";
print '<div id="pooldiv" '.$style.'>';
print "<table>";

$tr = new TrFormElement(_T("IP range start"), new IPInputTpl("ipstart"));
$tr->display(array("value"=>$ipstart));

$tr = new TrFormElement(_T("IP range end"), new IPInputTpl("ipend"));
$tr->display(array("value"=>$ipend));

?>

</table>
</div>

<? if ($_GET["action"] == "subnetadd") { ?>
    <input name="badd" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<? } else { ?>
    <input name="bedit" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<? } ?>

</form>

<script>
document.body.onLoad = document.subnetform.subnet.focus();
</script>
