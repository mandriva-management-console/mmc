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

$p = new PageGenerator($title);
$sidemenu->forceActiveItem("subnetindex");
$p->setSideMenu($sidemenu);
$p->display();

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

    if (isset($_POST["badd"])) {
        /* Check that this hostname or IP address has been already registered in the DHCP subnet */
        if (hostExistsInSubnet($subnet, $hostname)) {
            $error .= _T("The specified hostname has been already registered in this DHCP subnet.") . " ";
            setFormError("hostname");
            $hostname = "";        
        }
        if (ipExistsInSubnet($subnet, $ipaddress)) {
            $error .= _T("The specified IP address has been already registered in this DHCP subnet.") . " ";
            setFormError("ipaddress");
            $ipaddress = "";       
        }
        if (isset($_POST["dnsrecord"]) && !isset($error)) {
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

$f = new Form();
?>

<form id="edit" name="subnetedithostform" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validateForm();" >

<?
$f->beginTable();
if ($_GET["action"]=="subnetaddhost") {
    $formElt = new HostnameInputTpl("hostname");
    $formEltIp = new IPInputTpl("ipaddress");
} else {
    $formElt = new HiddenTpl("hostname");
    $formEltIp = new HiddenTpl("ipaddress");
}

$tr = new TrFormElement(_T("Host name"), $formElt);
$tr->setCssError("hostname");
$tr->display(array("value" => $hostname, "required" => True));

$tr = new TrFormElement(_T("IP address"), $formEltIp);
$tr->setCssError("ipaddress");
$tr->display(array("value" => $ipaddress, "required" => True));

$tr = new TrFormElement(_T("MAC address"),new MACInputTpl("macaddress"));
$tr->display(array("value" => $macaddress, "required" => True));

$f->endTable();

$options = getSubnetOptions(getSubnet($subnet));
if (isset($options["domain-name"])) {
    $domain = $options["domain-name"];
    if (zoneExists($domain)) {
        $f->beginTable();
        if ($_GET["action"] == "subnetaddhost") {
            /*
                If the DHCP domain name option is set, and corresponds to an existing DNS zone
                we ask the user if she/he wants to record the machine in the DNS zone too.
            */
            $tr = new TrFormElement(_T("Also records this machine into DNS zone") . "&nbsp;" . $domain, new CheckboxTpl("dnsrecord"));
            $tr->display(array("value" => "CHECKED"));
        } else {
            $domainurl = urlStr("network/network/zonemembers", array("zone" => "localnet"));
            $domainlink = '<a href="' . $domainurl . "\">$domain</a>";            
            if (hostExists($domain, $hostname)) {
                $tr = new TrFormElement(sprintf(_T("This host name is also registered in DNS zone %s"), $domainlink), new HiddenTpl(""));
                $tr->display(array());
                $resolvedip = resolve($domain, $hostname);
                if ((strlen($resolvedip) > 0) && ($ipaddress != $resolvedip)) {
                    $warn = '<div class="error">' . sprintf(_T("but with another IP address (%s)"), $resolvedip) . '</div>';
                    $tr = new TrFormElement($warn, new HiddenTpl(""));
                    $tr->display(array());                    
                }
            } else {
                $warn = '<div class="error">' . sprintf(_T("This host is not registered in DNS zone %s"), $domainlink) . '</div>';
                $tr = new TrFormElement($warn, new HiddenTpl(""));
                $tr->display(array());
            }            
        }
        $f->endTable();
    }
}

$f->beginTable();

$tr = new TrFormElement(_T("Other DHCP options"), new HiddenTpl(""));
$tr->display(array());

$tr = new TrFormElement(_T("Initial boot file name"),new IA5InputTpl("filename"));
$tr->display(array("value"=>$filename));

$tr = new TrFormElement(_T("Path to the root filesystem"),new IA5InputTpl("rootpath"));
$tr->display(array("value"=>$rootpath));

$f->endTable();

if ($_GET["action"] == "subnetaddhost") { ?>
    <input name="badd" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<? } else { ?>
    <input name="bedit" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<? }

$f->end();
?>

<script>
document.body.onLoad = document.subnetedithostform.hostname.focus();
</script>
