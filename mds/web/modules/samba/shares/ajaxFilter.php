<?php

require("modules/samba/includes/shares.inc.php");

$filter = fromGET('filter');
if ($filter && strpos($filter, "*") === false) {
    $filter = "*" . $filter . "*";
}

/* protected shares */
$protectedShare = array ("","homes","netlogon","archives");

$shares = get_shares_detailed($filter);
$sharesName = array();
$sharesComment = array();

$editActions = array();
$delActions = array();

foreach($shares as $share) {
    $sharesName[] = $share[0];
    if (isset($share[1]))
        $sharesComment[] = $share[1];
    else
        $sharesComment[] = "";
    if (!in_array($share[0], $protectedShare)) {
        $editActions[] = new ActionItem(_T("Edit"),"details","edit","share");
        $delActions[] = new ActionPopupItem(_T("Delete"),"delete","delete","share");
    } else {
        $editActions[] = new EmptyActionItem();
        $delActions[] = new EmptyActionItem();
    }
}

$l = new ListInfos($sharesName, _T("Shares"));
$l->first_elt_padding = 1;
$l->addExtraInfo($sharesComment, _T("Description"));
$l->addActionItemArray($editActions);
$l->addActionItemArray($delActions);

$l->addActionItem(new ActionPopupItem(_T("Archive"),"backup","backup","share"));
$l->display();


?>
