<?
require("modules/network/includes/network-xmlrpc.inc.php");

$filter = $_GET["filter"];
$subnets = array();
$count = array();

foreach(getSubnets($filter) as $dn => $entry) {
    $subnet = $entry[1]["cn"][0];
    $subnets[$subnet] = array();
    $subnets[$subnet]["name"] = $entry[1]["dhcpComments"][0];
    $subnets[$subnet]["netmask"] = $entry[1]["dhcpNetMask"][0];
}

ksort($subnets);
$names = array();
$netmasks = array();
$count = array();
$ranges = array();
foreach($subnets as $subnet => $infos) {
    $count[] = '<span style="font-weight: normal;">(' . getSubnetHostsCount($subnet) . ')</span>';
    $names[] = $infos["name"];
    $netmasks[] = $infos["netmask"];
    $pool = getPool($subnet);
    if (count($pool)) {
        $hasSubnetPool = "checked";
        $range = $pool[0][1]["dhcpRange"][0];
        list($ipstart, $ipend) = explode(" ", $range);
	$ranges[] = "$ipstart -> $ipend";
    } else $ranges[] = _T("No dynamic address pool", "network");

}

$n = new ListInfos(array_keys($subnets), _T("DHCP subnets", "network"));
$n->setNavBar(new AjaxNavBar(count($subnets), $filter));
$n->setAdditionalInfo($count);
$n->first_elt_padding = 1;
$n->addExtraInfo($netmasks, _T("Netmask", "network"));
$n->addExtraInfo($names, _T("Description", "network"));
$n->addExtraInfo($ranges, _T("Dynamic pool range", "network"));
$n->setName(_T("DHCP subnets", "network"));

$n->addActionItem(new ActionItem(_T("View DHCP static host", "network"),"subnetmembers","display", "subnet", "network", "network"));
$n->addActionItem(new ActionItem(_T("Edit subnet", "network"),"subnetedit","edit","subnet", "network", "network"));
$n->addActionItem(new ActionItem(_T("Add static host to subnet", "network"),"subnetaddhost","addhost","subnet", "network", "network"));
$n->addActionItem(new ActionPopupItem(_T("Delete zone", "network"),"subnetdelete","delete","subnet", "network", "network"));

$n->display();

?>
