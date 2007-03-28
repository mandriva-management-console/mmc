<?
require("../../../includes/config.inc.php");
require("../../../includes/i18n.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");
require("../../../modules/network/includes/network-xmlrpc.inc.php");
require ("../../../includes/PageGenerator.php");


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
$hosts = array();

foreach(getSubnetHosts($subnet, "") as $dn => $entry) {
    $hostname = $entry[1]["cn"][0];
    foreach($entry[1]["dhcpStatements"] as $statements) {
        list($name, $value) = explode(" ", $statements, 2);
	if ($name == "fixed-address") {
            $hosts[$hostname]["ipaddress"] = $value;
            break;
        }
    }
    list($tmp, $hosts[$hostname]["macaddress"]) = explode(" ", $entry[1]["dhcpHWAddress"][0]);
    if ($filter) {
        /* Don't display a host if filtered */
        if (
            (strpos($hostname, $filter) === False)
            && (strpos($hosts[$hostname]["ipaddress"], $filter) === False)
            && (strpos($hosts[$hostname]["macaddress"], $filter) === False)
            ) {
            unset($hosts[$hostname]);
        }
    }
}

ksort($hosts);
$ipaddresses = array();
$macaddresses = array();
foreach($hosts as $host => $infos) {
    $ipaddresses[] = $infos["ipaddress"];
    $macaddresses[] = $infos["macaddress"];
}

if (isset($_GET["start"])) {
    $start = $_GET["start"];
    $end = $_GET["end"];
} else {
    $start = 0;
    if (count($domains) > 0) {
        $end = $conf["global"]["maxperpage"] - 1;
    } else {
        $end = 0;
    }
}

print_ajax_nav($start, $end, $zones, $filter);

$n = new ListInfos(array_keys($hosts), _T("Host", "network"));
$n->setTableHeaderPadding(1);
$n->addExtraInfo($ipaddresses, _T("IP address", "network"));
$n->addExtraInfo($macaddresses, _T("MAC address", "network"));
$n->setName(_T("Host", "network"));

$n->addActionItem(new ActionItem(_T("Edit host", "network"),"subnetedithost", "edit", "subnet=$subnet&amp;host", "network", "network"));
$n->addActionItem(new ActionPopupItem(_T("Delete host", "network"),"subnetdeletehost","supprimer","subnet=$subnet&amp;host", "network", "network"));

$n->display(0);

print_ajax_nav($start, $end, $domains, $filter);

?>
