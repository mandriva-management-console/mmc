<?
require("../../../includes/config.inc.php");
require("../../../includes/i18n.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");
require("../../../modules/mail/includes/mail-xmlrpc.php");

$filter = $_POST["value"];

$domains = array();

foreach(getVDomains($filter) as $dn => $entry) {
    $domains[$entry[1]["virtualdomain"][0]] = $entry[1]["virtualdomaindescription"][0];
}
ksort($domains);

print "<ul>";
foreach($domains as $domain => $desc) {
  print "<li>$domain<br/><span class=\"informal\">$desc</span></li>";
}

print "</ul>";

?>