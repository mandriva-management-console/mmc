<?
require("../../../includes/config.inc.php");
require("../../../includes/i18n.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");
require("../../../modules/mail/includes/mail-xmlrpc.php");
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
      echo "<li class=\"previousList\"><a href=\"#\" onClick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._("Previous")."</a></li>\n";
    }

  if (($curend + 1) >= count($items))
    {
      echo "<li class=\"nextListInactive\">"._("Next")."</li>\n";
    }
  else
    {
      $start = $curend + 1;
      $end = $curend + $max;
      echo "<li class=\"nextList\"><a href=\"#\" onClick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._("Next")."</a></li>\n";
    }

  echo "</ul>\n";
}

$filter = $_GET["filter"];

$domains = array();
$count = array();

foreach(getVDomains($filter) as $dn => $entry) {
    $domains[$entry[1]["virtualdomain"][0]] = $entry[1]["virtualdomaindescription"][0];
}
ksort($domains);
foreach($domains as $domain => $info) {
    $count[] = '<span style="font-weight: normal;">(' . getVDomainUsersCount($domain) . ')</span>';
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

print_ajax_nav($start, $end, $domains, $filter);

$n = new ListInfos(array_keys($domains), _T("Mail domain", "mail"));
$n->setAdditionalInfo($count);
$n->first_elt_padding = 1;
$n->setCssClass("domainName");
$n->addExtraInfo(array_values($domains), _T("Description", "mail"));
$n->setName(_T("Mail domain", "mail"));

$n->addActionItem(new ActionItem(_T("View domain members", "mail"),"members","afficher","mail", "mail", "mail"));
$n->addActionItem(new ActionItem(_T("Edit domain", "mail"),"edit","edit","mail", "mail", "mail"));
$n->addActionItem(new ActionPopupItem(_T("Delete domain", "mail"),"delete","supprimer","mail", "mail", "mail"));

$n->display(0);

print_ajax_nav($start, $end, $domains, $filter);

?>