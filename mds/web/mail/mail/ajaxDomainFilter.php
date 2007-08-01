<?
require("../../../includes/config.inc.php");
require("../../../includes/i18n.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");
require("../../../modules/mail/includes/mail-xmlrpc.php");
require ("../../../includes/PageGenerator.php");

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

$n = new ListInfos(array_keys($domains), _T("Mail domain", "mail"));
$n->setNavBar(new AjaxNavBar(count($domains), $filter));
$n->setAdditionalInfo($count);
$n->first_elt_padding = 1;
$n->setCssClass("domainName");
$n->addExtraInfo(array_values($domains), _T("Description", "mail"));
$n->setName(_T("Mail domain", "mail"));

$n->addActionItem(new ActionItem(_T("View domain members", "mail"),"members","afficher","mail", "mail", "mail"));
$n->addActionItem(new ActionItem(_T("Edit domain", "mail"),"edit","edit","mail", "mail", "mail"));
$n->addActionItem(new ActionPopupItem(_T("Delete domain", "mail"),"delete","supprimer","mail", "mail", "mail"));

$n->display();

?>