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

$domain = $_GET["mail"];
$filter = $_GET["filter"];

$uids = array();
foreach(getVDomainUsers($domain, $filter) as $dn => $entries) {
    $mail = htmlentities($entries[1]["mail"][0]);
    $uids[$entries[1]["uid"][0]] = array($entries[1]["givenName"][0] . " " . $entries[1]["sn"][0],
                                         '<a href="' . "mailto:" . $mail . '">' . $mail . "</a>");
}
ksort($uids);

$names = array();
$mails = array();
foreach($uids as $uid) {
    $names[] = $uid[0];
    $mails[] = $uid[1];
}

if (isset($_GET["start"])) {
    $start = $_GET["start"];
    $end = $_GET["end"];
} else {
    $start = 0;
    if (count($uids) > 0) {
        $end = $conf["global"]["maxperpage"] - 1;
    } else {
        $end = 0;
    }
}

print_ajax_nav($start, $end, $uids, $filter);

$n = new ListInfos(array_keys($uids), _("Login"), "&mail=$domain");
$n->setCssClass("userName");
$n->addExtraInfo($names, _("Name"));
$n->addExtraInfo($mails, _("Mail address"));
$n->addActionItem(new ActionItem(_("Edit"),"edit","edit","user", "base", "users"));
$n->display(0);

print_ajax_nav($start, $end, $uids, $filter);
?>