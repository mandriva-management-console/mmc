<?

/* DNS / DHCP RPCs */

function addZoneWithSubnet($zonename, $network, $netmask, $description, $nameserver, $nameserverip, $reverse) {
    xmlCall("network.addZoneWithSubnet", array($zonename, $network, $netmask, $description, $nameserver, $nameserverip, $reverse));
}

function getSubnetAndZoneFreeIp($subnet, $zone, $current = null) {
    return xmlCall("network.getSubnetAndZoneFreeIp", array($subnet, $zone, $current));
}

/* DNS RPCs */

function getZones($filter) {
    return xmlCall("network.getZones", array($filter));
}

function getReverseZone($zone) {
    return xmlCall("network.getReverseZone", array($zone));
}


function addZone($zonename, $network, $netmask, $reverse, $description, $nameserver, $nameserverip) {
    xmlCall("network.addZone", array($zonename, $network, $netmask, $reverse, $description, $nameserver, $nameserverip));
}

function delZone($zonename) {
    xmlCall("network.delZone", array($zonename));
}

function zoneExists($zonename) {
    return xmlCall("network.zoneExists", array($zonename));
}

function getAllZonesNetworkAddresses() {
    return xmlCall("network.getAllZonesNetworkAddresses");
}

function getZoneNetworkAddress($zone) {
    return xmlCall("network.getZoneNetworkAddress", array($zone));
}

function getZoneRecords($zone, $filter) {
    return xmlCall("network.getZoneRecords", array($zone, $filter));
}

function getZoneRecordById($zone, $id) {
    return xmlCall("network.getZoneRecordById", array($zone, $id));
}

function hasRecord($zone, $recordType, $hostname) {
    return xmlCall("network.hasRecord", array($zone, $recordType, $hostname));
}

function addRecord($zone, $recordType, $hostname, $recordValue) {
    return xmlCall("network.addRecord", array($zone, $recordType, $hostname, $recordValue));
}

function modifyRecordById($zone, $recordId, $hostname, $recordValue) {
    return xmlCall("network.modifyRecordById", array($zone, $recordId, $hostname, $recordValue));
}

function delRecordById($zone, $id) {
    xmlCall("network.delRecordById", array($zone, $id));
}

function addRecordA($zone, $hostname, $ip) {
    return xmlCall("network.addRecordA", array($zone, $hostname, $ip));
}

function delRecord($zone, $hostname) {
    xmlCall("network.delRecord", array($zone, $hostname));
}

function modifyRecord($zone, $hostname, $ip) {
    xmlCall("network.modifyRecord", array($zone, $hostname, $ip));
}

function getZoneObjectsCount($zone) {
    return xmlCall("network.getZoneObjectsCount", array($zone));
}

function getZoneObjects($zone, $filter) {
    return xmlCall("network.getZoneObjects", array($zone, $filter));
}

function getSOARecord($zone) {
    return xmlCall("network.getSOARecord", array($zone));
}

function getSOAARecord($zone) {
    return xmlCall("network.getSOAARecord", array($zone));
}

function setSOANSRecord($zone, $nameserver) {
    return xmlCall("network.setSOANSRecord", array($zone, $nameserver));
}

function setSOAARecord($zone, $ip) {
    return xmlCall("network.setSOAARecord", array($zone, $ip));
}

function setNSRecords($zone, $nameservers) {
    xmlCall("network.setNSRecords", array($zone, $nameservers));
}

function setMXRecords($zone, $mxservers) {
    xmlCall("network.setMXRecords", array($zone, $mxservers));
}

function getNSRecords($zone) {
    return xmlCall("network.getNSRecords", array($zone));
}

function getMXRecords($zone) {
    return xmlCall("network.getMXRecords", array($zone));
}

function setZoneDescription($zone, $description) {
    xmlCall("network.setZoneDescription", array($zone, $description));
}

function hostExists($zone, $hostname) {
    return xmlCall("network.hostExists", array($zone, $hostname));
}

function ipExists($zone, $ip) {
    return xmlCall("network.ipExists", array($zone, $ip));
}

function resolve($zone, $hostname) {
    return xmlCall("network.resolve", array($zone, $hostname));
}

function getZoneFreeIp($zone, $ipstart = null) {
    return xmlCall("network.getZoneFreeIp", array($zone, $ipstart));
}

function getResourceRecord($zone, $rr) {
    return xmlCall("network.getResourceRecord", array($zone, $rr));
}

function getCNAMEs($zone, $host) {
    return xmlCall("network.getCNAMEs", array($zone, $host));
}

function delCNAMEs($zone, $host) {
    xmlCall("network.delCNAMEs", array($zone, $host));
}

function addRecordCNAME($zone, $alias, $cname) {
    xmlCall("network.addRecordCNAME", array($zone, $alias, $cname));
}

function setHostAliases($zone, $host, $aliases) {
    if ($aliases == array("")) {
        $tmp = array();
    } else {
        /* Cleanup alias list */
        foreach($aliases as $alias) {
            $alias = trim($alias);
            if (strlen($alias)) {
                $tmp[] = $alias;
            }
        }
    }
    xmlCall("network.setHostAliases", array($zone, $host, $tmp));
}

/* DHCP RPCs */

/* Subnet */

function addSubnet($network, $netmask, $name) {
    xmlCall("network.addSubnet", array($network, $netmask, $name));
}

function delSubnet($subnet) {
    xmlCall("network.delSubnet", array($subnet));
}

function getSubnets($filter) {
    return xmlCall("network.getSubnets", array($filter));
}

function getSubnet($subnet) {
    return xmlCall("network.getSubnet", array($subnet));
}

function setSubnetAuthoritative($subnet, $flag) {
    xmlCall("network.setSubnetAuthoritative", array($subnet, $flag));
}

function setSubnetOption($subnet, $option, $value) {
    xmlCall("network.setSubnetOption", array($subnet, $option, $value));
}

function setSubnetStatement($subnet, $option, $value) {
    xmlCall("network.setSubnetStatement", array($subnet, $option, $value));
}

function setSubnetNetmask($subnet, $netmask) {
    xmlCall("network.setSubnetNetmask", array($subnet, $netmask));
}

function setSubnetDescription($subnet, $description) {
    xmlCall("network.setSubnetDescription", array($subnet, $description));
}

function getSubnetHosts($subnet, $filter) {
    return xmlCall("network.getSubnetHosts", array($subnet, $filter));
}

function getSubnetHostsCount($subnet) {
    return xmlCall("network.getSubnetHostsCount", array($subnet));
}

/* Pool */

function addPool($subnet, $poolname, $start, $end) {
    xmlCall("network.addPool", array($subnet, $poolname, $start, $end));
}

function delPool($poolname) {
    xmlCall("network.delPool", array($poolname));
}

function getPool($poolname) {
    return xmlCall("network.getPool", array($poolname));
}

function setPoolRange($poolname, $start, $end) {
    xmlCall("network.setPoolRange", array($poolname, $start, $end));
}

function getPoolsRanges($subnet){
    return xmlCall("network.getPoolsRanges", array($subnet));
}

function setPoolsRanges($subnet, $ranges){
    xmlCall("network.setPoolsRanges", array($subnet, $ranges));
}

/* Host */

function addHostToSubnet($subnet, $hostname) {
    xmlCall("network.addHostToSubnet", array($subnet, $hostname));
}

function delHost($subnet, $hostname) {
    xmlCall("network.delHost", array($subnet, $hostname));
}

function setHostOption($subnet, $host, $option, $value) {
    xmlCall("network.setHostOption", array($subnet, $host, $option, $value));
}

function setHostStatement($subnet, $host, $option, $value) {
    xmlCall("network.setHostStatement", array($subnet, $host, $option, $value));
}

function getHost($subnet, $host) {
    return xmlCall("network.getHost", array($subnet, $host));
}

function getHostHWAddress($subnet, $host) {
    return xmlCall("network.getHostHWAddress", array($subnet, $host));
}

function setHostHWAddress($subnet, $host, $address) {
    xmlCall("network.setHostHWAddress", array($subnet, $host, $address));
}

function hostExistsInSubnet($subnet, $hostname) {
    return xmlCall("network.hostExistsInSubnet", array($subnet, $hostname));
}

function ipExistsInSubnet($subnet, $ip) {
    return xmlCall("network.ipExistsInSubnet", array($subnet, $ip));
}

function getSubnetFreeIp($subnet, $ipstart = null) {
    return xmlCall("network.getSubnetFreeIp", array($subnet, $ipstart));
}


/* DHCP leases */
function getDhcpLeases() {
    return xmlCall("network.getDhcpLeases");
}

/* DHCP launch config */

function getDhcpLaunchConfig() {
    return xmlCall("network.getDhcpLaunchConfig");
}

function setDhcpInterfaces($interfaces){
    return xmlCall("network.setDhcpInterfaces", array($interfaces));
}

function getInterfacesInfo(){
    return xmlCall("network.getInterfacesInfo");
}

/* Service management RPCs */

function dhcpService($command) {
    return xmlCall("network.dhcpService", array($command));
}

function dnsService($command) {
    return xmlCall("network.dnsService", array($command));
}

?>
