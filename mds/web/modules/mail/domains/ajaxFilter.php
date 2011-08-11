<?php

$domain = $_GET["domain"];
if (isset($_GET["filter"]))
    $filter = $_GET["filter"];
else
    $filter = "";

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

$n = new ListInfos(array_keys($uids), _("Login"), "&mail=$domain");
$n->setNavBar(new AjaxNavBar(count($uids), $filter));
$n->setCssClass("userName");
$n->addExtraInfo($names, _("Name"));
$n->addExtraInfo($mails, _("Mail address"));
$n->addActionItem(new ActionItem(_("Edit"),"edit","edit","user", "base", "users"));
$n->display();

?>
