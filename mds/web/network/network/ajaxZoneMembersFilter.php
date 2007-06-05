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
$zone = $_GET["zone"];
$addresses = array();

foreach(getZoneObjects($zone, "") as $dn => $entry) {
    $hostname = $entry[1]["relativeDomainName"][0];
    $address = ip2long($entry[1]["aRecord"][0]); 
    $addresses[$address] = $hostname;
    if ($filter) {
        /* Don't display a host if filtered */
        if (
            (strpos($hostname, $filter) === False)
            && (strpos($entry[1]["aRecord"][0], $filter) === False)
            ) {
            unset($addresses[$address]);
        }        
    }
}

ksort($addresses);
$sorted = array();
$params = array();
foreach($addresses as $ip => $host) {
    $sorted[] = long2ip($ip);
    $params[] = array("host" => $host, "zone" => $zone);
}

if (isset($_GET["start"])) {
    $start = $_GET["start"];
    $end = $_GET["end"];
} else {
    $start = 0;
    if (count($addresses) > 0) {
        $end = $conf["global"]["maxperpage"] - 1;
    } else {
        $end = 0;
    }
}

print_ajax_nav($start, $end, $addresses, $filter);

$n = new ListInfos($sorted, _T("IP address", "network"));
$n->setTableHeaderPadding(1);
$n->addExtraInfo(array_values($addresses), _T("Host name", "network"));
$n->setName(_T("Host", "network"));
$n->setParamInfo($params);
$n->disableFirstColumnActionLink();

$n->addActionItem(new ActionPopupItem(_T("Delete host", "network"),"deletehost","supprimer","", "network", "network"));

$n->display(0);

print_ajax_nav($start, $end, $addresses, $filter);

?>

<input type="button" class="btnPrimary" value="<?= _T("Add a host"); ?>" onclick="location.href='main.php?module=network&submod=network&action=addhost&zone=<?= $zone; ?>';"/>