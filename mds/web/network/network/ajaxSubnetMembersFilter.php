<?
require("../../../includes/config.inc.php");
require("../../../includes/i18n.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");
require ("../../../includes/PageGenerator.php");
require("../../../modules/network/includes/network.inc.php");
require("../../../modules/network/includes/network-xmlrpc.inc.php");


function print_ajax_nav($curstart, $curend, $items, $filter)
{
  $_GET["action"] = "index";
  global $conf;

  $max = $conf["global"]["maxperpage"];

  echo '<form method="post" action="' . $PHP_SELF . '">';
  echo "<ul class=\"navList\">\n";

  if ($curstart == 0)
    {
      echo "<li class=\"previousListInactive\">"._("Previous")."</li>\n";
    }
  else
    {
      $start = $curstart - $max;
      $end = $curstart - 1;
      echo "<li class=\"previousList\"><a href=\"#\" onclick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._("Previous")."</a></li>\n";
    }

  if (($curend + 1) >= count($items))
    {
      echo "<li class=\"nextListInactive\">"._("Next")."</li>\n";
    }
  else
    {
      $start = $curend + 1;
      $end = $curend + $max;
      echo "<li class=\"nextList\"><a href=\"#\" onclick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._("Next")."</a></li>\n";
    }

  echo "</ul>\n";
}

$filter = $_GET["filter"];
$subnet = $_GET["subnet"];
$subnetInfos = getSubnet($subnet);
$netmask = $subnetInfos[0][1]["dhcpNetMask"][0];
$lines = array();

foreach(getSubnetHosts($subnet, "") as $dn => $entry) {
    $hostname = $entry[1]["cn"][0];
    $ipaddress = null;
    foreach($entry[1]["dhcpStatements"] as $statements) {
        list($name, $value) = explode(" ", $statements, 2);
	if ($name == "fixed-address") {
	    /* Convert to long for easy sorting */
	    $ipaddress = ip2long($value);
	    $lines[$ipaddress]["hostname"] =  $hostname;
            break;
        }
    }
    if (!$ipaddress) {      
        unset($lines[$ipaddress]);
        continue; /* We don't support displaying DHCP host with no fixed IP address */
    }
    list($tmp, $lines[$ipaddress]["macaddress"]) = explode(" ", strtoupper($entry[1]["dhcpHWAddress"][0]));
    $lines[$ipaddress]["type"] = _T("Static", "network");
    if ($filter) {
        /* Don't display a host if filtered */
        if (
            (strpos($hostname, $filter) === False)
            && (strpos($ipaddress, $filter) === False)
            && (strpos($lines[$ipaddress]["macaddress"], $filter) === False)
            ) {
	    unset($lines[$ipaddress]);
        }
    }
}

/* Get current DHCP leases info to display dynamically assigned IP addresses */
$leases = getDhcpLeases();
foreach($leases as $ipaddress => $infos) {
    if ($infos["state"] == "active") {
        if (ipInNetwork($ipaddress, $subnet, $netmask)) {
            /* Only display lease of the current subnet */
            $address = ip2long($ipaddress);
            $lines[$address]["type"] = _T("Dynamic", "network");
            $lines[$address]["macaddress"] = strtoupper($infos["hardware"]);
            $lines[$address]["hostname"] = $infos["hostname"];
        }
    }
}

ksort($lines);
$hosts = array();
$ipaddresses = array();
$macaddresses = array();
$types = array();
$ends = array();
$params = array();
$actionsAdd = array();
$actionsEdit = array();
$actionsDel = array();
$deleteAction = new ActionPopupItem(_T("Delete host", "network"),"subnetdeletehost","supprimer","ipaddress", "network", "network");
$addAction = new ActionItem(_T("Add static host", "network"),"subnetaddhost","addhost","ipaddress", "network", "network");
$editAction = new ActionItem(_T("edit static host", "network"),"subnetedithost","edit","ipaddress", "network", "network");
$emptyAction = new EmptyActionItem();
foreach($lines as $ipaddress => $infos) {
    $hosts[] = $infos["hostname"];
    $ipaddresses[] = long2ip($ipaddress);
    $macaddresses[] = $infos["macaddress"];
    $types[] = $infos["type"];
    $params[] = array("host" => $infos["hostname"],
                      "macaddress" => $infos["macaddress"],
                      "subnet" => $subnet);
    if ($infos["type"] == _T("Static", "network")) {
        $actionsAdd[] = $emptyAction;
        $actionsDel[] = $deleteAction;
        $actionsEdit[] = $editAction;
    } else {
        $actionsAdd[] = $addAction;
        $actionsDel[] = $emptyAction;
        $actionsEdit[] = $emptyAction;
    }
}

if (isset($_GET["start"])) {
    $start = $_GET["start"];
    $end = $_GET["end"];
} else {
    $start = 0;
    if (count($lines) > 0) {
        $end = $conf["global"]["maxperpage"] - 1;
    } else {
        $end = 0;
    }
}

print_ajax_nav($start, $end, $lines, $filter);

$n = new ListInfos($ipaddresses, _T("IP address", "network"));
$n->disableFirstColumnActionLink();
$n->setTableHeaderPadding(1);
$n->addExtraInfo($hosts, _T("Host name", "network"));
$n->addExtraInfo($macaddresses, _T("MAC address", "network"));
$n->addExtraInfo($types, _T("Type", "network"));
$n->setName(_T("Host", "network"));
$n->setParamInfo($params);
$n->addActionItemArray($actionsAdd);
$n->addActionItemArray($actionsEdit);
$n->addActionItemArray($actionsDel);
$n->display(0);

print_ajax_nav($start, $end, $lines, $filter);

?>

<input type="button" value="<?= _T("Add a static host"); ?>" onclick="location.href='main.php?module=network&submod=network&action=subnetaddhost&subnet=<?= $subnet; ?>';"/>
