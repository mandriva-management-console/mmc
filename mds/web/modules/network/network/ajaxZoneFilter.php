<?
require("modules/network/includes/network-xmlrpc.inc.php");

$filter = $_GET["filter"];
$zones = array();
$count = array();

foreach(getZones($filter) as $dn => $entry) {
    if (in_array("associatedDomain",array_keys($entry[1]))) {
        $zonename = $entry[1]["associatedDomain"][0];
    } else {
        $zonename = $entry[1]["zoneName"][0];
    }
    $zones[$zonename] = array();
    $zones[$zonename]["description"] = "";    
    if (isset($entry[1]["tXTRecord"])) {
        foreach($entry[1]["tXTRecord"] as $value) {
            $zones[$zonename]["description"] .= $value . " ";
        }
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
    else $reverses[] = $reverse[0] . ".";
}

$n = new ListInfos(array_keys($zones), _T("DNS zones", "network"));
$n->setNavBar(new AjaxNavBar(count($zones), $filter));
$n->setAdditionalInfo($count);
$n->first_elt_padding = 1;
$n->addExtraInfo($reverses, _T("Network prefix", "network"));
$n->addExtraInfo($descriptions, _T("Description", "network"));
$n->setName(_T("DNS zones", "network"));

$n->addActionItem(new ActionItem(_T("View zone records", "network"),"zonemembers","display","zone", "network", "network"));
$n->addActionItem(new ActionItem(_T("Edit zone", "network"),"edit","edit","zone", "network", "network"));
$n->addActionItem(new ActionItem(_T("Add host", "network"),"addhost","addhost","zone", "network", "network"));
$n->addActionItem(new ActionPopupItem(_T("Delete zone", "network"),"delete","delete","zone", "network", "network"));

$n->display();

?>
