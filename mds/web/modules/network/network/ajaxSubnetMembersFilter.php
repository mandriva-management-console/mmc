<?php

require("modules/network/includes/network.inc.php");

$filter = $_GET["filter"];
$subnet = $_GET["subnet"];
$subnetInfos = getSubnet($subnet);
$netmask = $subnetInfos[0][1]["dhcpNetMask"][0];
$lines = array();

foreach(getSubnetHosts($subnet, "") as $dn => $entry) {
    $hostname = $entry[1]["cn"][0];
    $ipaddress = null;
    foreach($entry[1]["dhcpStatements"] as $statements) {
        list($name, $value) = explode(" ", $statements, 2);
	if ($name == "fixed-address") {
	    /* Convert to long for easy sorting */
	    $ipaddress = ip2long($value);
	    $lines[$ipaddress]["hostname"] =  $hostname;
            break;
        }
    }
    if (!$ipaddress) {
        unset($lines[$ipaddress]);
        continue; /* We don't support displaying DHCP host with no fixed IP address */
    }
    list($tmp, $lines[$ipaddress]["macaddress"]) = explode(" ", strtoupper($entry[1]["dhcpHWAddress"][0]));
    $lines[$ipaddress]["type"] = _T("Static", "network");
    if ($filter) {
        /* Don't display a host if filtered */
        if (
            (stripos($hostname, $filter) === False)
            && (strpos(long2ip($ipaddress), $filter) === False)
            && (stripos($lines[$ipaddress]["macaddress"], $filter) === False)
            && (stripos($lines[$ipaddress]["type"], $filter) === False)
            ) {
	    unset($lines[$ipaddress]);
        }
    }
}

/* Get current DHCP leases info to display dynamically assigned IP addresses */
$leases = getDhcpLeases();
if ($leases)
foreach($leases as $ipaddress => $infos) {
    if ($infos["state"] == "active") {
        if (ipInNetwork($ipaddress, $subnet, $netmask)) {
            /* Only display lease of the current subnet */
            $address = ip2long($ipaddress);
            $lines[$address]["type"] = _T("Dynamic", "network");
            $lines[$address]["macaddress"] = strtoupper($infos["hardware"]);
            $lines[$address]["hostname"] = $infos["hostname"];
            if ($filter) {
                /* Don't display a host if filtered */
                if (
                    (stripos($lines[$address]["hostname"], $filter) === False)
                    && (strpos(long2ip($address), $filter) === False)
                    && (stripos($lines[$address]["macaddress"], $filter) === False)
            	    && (stripos($lines[$address]["type"], $filter) === False)
                    ) {
                    unset($lines[$address]);
                }
            }
        }
    }
}

ksort($lines);
$hosts = array();
$ipaddresses = array();
$macaddresses = array();
$types = array();
$ends = array();
$params = array();
$actionsAdd = array();
$actionsEdit = array();
$actionsDel = array();
$deleteAction = new ActionPopupItem(_T("Delete host", "network"),"subnetdeletehost","delete","ipaddress", "network", "network");
$addAction = new ActionItem(_T("Add static host", "network"),"subnetaddhost","addhost","ipaddress", "network", "network");
$editAction = new ActionItem(_T("edit static host", "network"),"subnetedithost","edit","ipaddress", "network", "network");
$emptyAction = new EmptyActionItem();
foreach($lines as $ipaddress => $infos) {
    $hosts[] = $infos["hostname"];
    $ipaddresses[] = long2ip($ipaddress);
    $macaddresses[] = $infos["macaddress"];
    $types[] = $infos["type"];
    $params[] = array("host" => $infos["hostname"],
                      "macaddress" => $infos["macaddress"],
                      "subnet" => $subnet);
    if ($infos["type"] == _T("Static", "network")) {
        $actionsAdd[] = $emptyAction;
        $actionsDel[] = $deleteAction;
        $actionsEdit[] = $editAction;
    } else {
        $actionsAdd[] = $addAction;
        $actionsDel[] = $emptyAction;
        $actionsEdit[] = $emptyAction;
    }
}

$n = new ListInfos($ipaddresses, _T("IP address", "network"));
$n->setNavBar(new AjaxNavBar(count($ipaddresses), $filter));
$n->disableFirstColumnActionLink();
$n->setTableHeaderPadding(1);
$n->addExtraInfo($hosts, _T("Host name", "network"));
$n->addExtraInfo($macaddresses, _T("MAC address", "network"));
$n->addExtraInfo($types, _T("Type", "network"));
$n->setName(_T("Host", "network"));
$n->setParamInfo($params);
$n->addActionItemArray($actionsAdd);
$n->addActionItemArray($actionsEdit);
$n->addActionItemArray($actionsDel);
$n->display();

?>
