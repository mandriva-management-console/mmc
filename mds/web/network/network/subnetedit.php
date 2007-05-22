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

function checkSubnet() {
    /* Check that the given subnet is not contained into an existing subnet */
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    foreach(getSubnets("") as $dn => $entry) {
        $sub = $entry[1]["cn"][0];
        $mask = $entry[1]["dhcpNetMask"][0];
        if (ipInNetwork($subnet, $sub, $mask, True)) {
            $error = sprintf(_T("The given network address belongs to the already existing DHCP subnet %s / %s."), $sub, $mask);
            setFormError("subnet");
            setFormError("netmask");
            break;
        }
    }
    if (isset($error)) {
        $_POST["subnet"] = "";
        $_POST["netmask"] = "";
    }
    return array(isset($error), $error);
}

function checkPool() {
    /* Check that the given pool range is valid */
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];

    if (isset($_POST["badd"])) $pool = array();
    else $pool = getPool($subnet);
    if (isset($_POST["subnetpool"])) {
        if (isset($_POST["ipstart"]) && isset($_POST["ipend"])) {
            $ipstart = $_POST["ipstart"];
            $ipend = $_POST["ipend"];
            if (!(ipLowerThan($ipstart, $ipend) && ipInNetwork($ipstart, $subnet, $netmask) && ipInNetwork($ipend, $subnet, $netmask)))
                $error .= _T("The specified dynamic pool IP range is not valid.");
        } else $error.= _T("No dynamic pool IP range specified.");
    }
    return array(isset($error), $error);
}

if (isset($_POST["badd"])) $checks = array("checkSubnet", "checkPool");
if (isset($_POST["bedit"])) $checks = array("checkPool");


if (isset($_POST["badd"]) | isset($_POST["bedit"])) {
    foreach($checks as $check) {
        list($result, $error) = call_user_func($check);
        if ($result) break;
    }
    print $subnet . " " . $netmask;
}
    
if (!isset($error)
     & (isset($_POST["badd"]) || (isset($_POST["bedit"])))) {
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    $description = stripslashes($_POST["description"]);
    
    /* edit the subnet */
    if (isset($_POST["badd"])) {
        addSubnet($subnet, $netmask, $description);        
    } else {
        setSubnetNetmask($subnet, $netmask);
        setSubnetDescription($subnet, $description);
    }

    /* Update the DHCP options */
    $names = array("broadcast-address", "routers", "domain-name", "domain-name-servers", "ntp-servers", "root-path");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
	if (in_array($name, array("domain-name", "root-path")))
            $value = '"' . $value . '"';
	if (in_array($name, array("domain-name-servers", "ntp-servers")))
            $value = str_replace(" ", ",", $value);
        setSubnetOption($subnet, $name, $value);
    }

    /* Update the DHCP statements */
    $names = array("filename");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
        if (strlen($value)) {
            if (in_array($name, array("filename")))
                $value = '"' . $value . '"';
        }
        setSubnetStatement($subnet, $name, $value);
    }
    
    /* Create or update the DHCP pool */
    if (isset($_POST["badd"])) $pool = array();
    else $pool = getPool($subnet);
    if (isset($_POST["subnetpool"])) {
        if (isset($_POST["ipstart"]) && isset($_POST["ipend"])) {
            $ipstart = $_POST["ipstart"];
            $ipend = $_POST["ipend"];
            if (count($pool)) setPoolRange($subnet, $ipstart, $ipend);
            else {
                /* The pool needs to be created */
                addPool($subnet, $subnet, $ipstart, $ipend);
            }
        }
    } else {
        /* Dynamic pool management is not checked */
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

if (isset($error)) {
    new NotifyWidgetFailure($error);
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    if (isset($_POST["subnetpool"])) $hasSubnetPool = "checked";
    else $hasSubnetPool = "";    
}


if ($_GET["action"] == "subnetedit") {
    $subnetInfos = getSubnet($_GET["subnet"]);
    $subnet = $subnetInfos[0][1]["cn"][0];
    $netmask = $subnetInfos[0][1]["dhcpNetMask"][0];
    $description = $subnetInfos[0][1]["dhcpComments"][0];
    $options = getSubnetOptions($subnetInfos);
    $statements = getSubnetStatements($subnetInfos);
    $pool = getPool($_GET["subnet"]);
    if (count($pool)) {
        $hasSubnetPool = "checked";
        $range = $pool[0][1]["dhcpRange"][0];
        list($ipstart, $ipend) = explode(" ", $range);
    } else $hasSubnetPool = "";
}

$f = new Form();
?>

<form id="edit" name="subnetform" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validateForm();">

<?
$f->beginTable();

if ($_GET["action"]=="subnetadd") {
    $formElt = new IPInputTpl("subnet");
} else {
    $formElt = new HiddenTpl("subnet");
}

$tr = new TrFormElement(_T("DHCP subnet address"), $formElt);
$tr->setCssError("subnet");
$tr->display(array("value" => $subnet, "required" => True));

$tr = new TrFormElement(_T("Netmask"),new NetmaskInputTpl("netmask"));
$tr->setCssError("netmask");
$tr->display(array("value" => $netmask, "required" => True, "extra" => "(e.g. 24 for a /24 network)"));

$tr = new TrFormElement(_T("Description"),new IA5InputTpl("description"));
$tr->display(array("value" => $description));

$f->endTable();
$f->beginTable();

$tr = new TrFormElement(_("DHCP options related to clients network parameters"), new HiddenTpl(""));
$tr->display(array());

$tr = new TrFormElement(_T("Broadcast address"), new IPInputTpl("broadcast-address"));
$tr->display(array("value"=>$options["broadcast-address"]));

$tr = new TrFormElement(_T("Domain name"),new DomainInputTpl("domain-name"));
$tr->display(array("value"=>$options["domain-name"]));

$tr = new TrFormElement(_T("Routers"),new HostIpListInputTpl("routers"));
$tr->display(array("value"=>$options["routers"]));

$tr = new TrFormElement(_T("Domain name servers"),new HostIpListInputTpl("domain-name-servers"));
$tr->display(array("value"=>$options["domain-name-servers"]));

$tr = new TrFormElement(_T("NTP servers"),new HostIpListInputTpl("ntp-servers"));
$tr->display(array("value"=>$options["ntp-servers"]));

$f->endTable();
$f->beginTable();

$tr = new TrFormElement(_T("Other DHCP options"), new HiddenTpl(""));
$tr->display(array());

$tr = new TrFormElement(_T("Initial boot file name"),new IA5InputTpl("filename"));
$tr->display(array("value"=>$statements["filename"]));

$tr = new TrFormElement(_T("Path to the root filesystem"),new IA5InputTpl("root-path"));
$tr->display(array("value"=>$options["root-path"]));

$f->endTable();
$f->beginTable();

$tr = new TrFormElement(_T("Dynamic pool for non-registered DHCP clients", "network"),new CheckboxTpl("subnetpool"));
$param=array("value"=>$hasSubnetPool,
	     "extraArg"=>'onclick="toggleVisibility(\'pooldiv\');"');
$tr->display($param);

$f->endTable();

if (!$hasSubnetPool) $style = 'style =" display: none;"';
else $style = "";
print '<div id="pooldiv" '.$style.'>';

$f->beginTable();

$tr = new TrFormElement(_T("IP range start"), new IPInputTpl("ipstart"));
$tr->display(array("value"=>$ipstart));

$tr = new TrFormElement(_T("IP range end"), new IPInputTpl("ipend"));
$tr->display(array("value"=>$ipend));

$f->endTable();

?>

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
