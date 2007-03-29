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
$zones = array();
$count = array();

foreach(getZones($filter) as $dn => $entry) {
    $zonename = $entry[1]["zoneName"][0];
    $zones[$zonename] = array();
    $zones[$zonename]["description"] = "";
    foreach($entry[1]["tXTRecord"] as $value) {
        $zones[$zonename]["description"] .= $value . " ";
    }
}

ksort($zones);
$descriptions = array();
$reverses = array();
$count = array();
foreach($zones as $zone => $infos) {
    $count[] = '<span style="font-weight: normal;">(' . getZoneObjectsCount($zone) . ')</span>';
    $descriptions[] = $infos["description"];
    $reverse = getZoneNetworkAddress($zone);
    if (!$reverse) $reverses[] = "None";
    else $reverses[] = $reverse[0];
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

$n = new ListInfos(array_keys($zones), _T("DNS zones", "network"));
$n->setAdditionalInfo($count);
$n->first_elt_padding = 1;
$n->addExtraInfo($reverses, _T("Network address", "network"));
$n->addExtraInfo($descriptions, _T("Description", "network"));
$n->setName(_T("DNS zones", "network"));

$n->addActionItem(new ActionItem(_T("View zone records", "network"),"zonemembers","afficher","zone", "network", "network"));
$n->addActionItem(new ActionItem(_T("Edit zone", "network"),"edit","edit","zone", "network", "network"));
$n->addActionItem(new ActionItem(_T("Add host", "network"),"addhost","addhost","zone", "network", "network"));
$n->addActionItem(new ActionPopupItem(_T("Delete zone", "network"),"delete","supprimer","zone", "network", "network"));

$n->display(0);

print_ajax_nav($start, $end, $domains, $filter);

?>
