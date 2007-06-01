<?

/* DNS / DHCP RPCs */

function addZoneWithSubnet($zonename, $network, $netmask, $description, $nameserver, $nameserverip, $reverse) {
    xmlCall("network.addZoneWithSubnet", array($zonename, $network, $netmask, $description, $nameserver, $nameserverip, $reverse));
}

/* DNS RPCs */

function getZones($filter) {
    return xmlCall("network.getZones", array($filter));
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

function addRecordA($zone, $hostname, $ip) {
    xmlCall("network.addRecordA", array($zone, $hostname, $ip));
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

function setNSRecord($zone, $nameserver) {
    return xmlCall("network.setNSRecord", array($zone, $nameserver));
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

/* Host */

function addHostToSubnet($subnet, $hostname) {
    xmlCall("network.addHostToSubnet", array($subnet, $hostname));
}

function delHost($hostname) {
    xmlCall("network.delHost", array($hostname));
}

function setHostOption($host, $option, $value) {
    xmlCall("network.setHostOption", array($host, $option, $value));
}

function setHostStatement($host, $option, $value) {
    xmlCall("network.setHostStatement", array($host, $option, $value));
}

function getHost($host) {
    return xmlCall("network.getHost", array($host));
}

function getHostHWAddress($host) {
    return xmlCall("network.getHostHWAddress", array($host));
}

function setHostHWAddress($host, $address) {
    xmlCall("network.setHostHWAddress", array($host, $address));
}

function hostExistsInSubnet($zone, $hostname) {
    return xmlCall("network.hostExistsInSubnet", array($zone, $hostname));
}

function ipExistsInSubnet($zone, $ip) {
    return xmlCall("network.ipExistsInSubnet", array($zone, $ip));
}

function getZoneFreeIp($zone, $ipstart = null) {
    return xmlCall("network.getZoneFreeIp", array($zone, $ipstart));
}


/* DHCP leases */
function getDhcpLeases() {
    return xmlCall("network.getDhcpLeases");
}

/* Service management RPCs */

function dhcpService($command) {
    return xmlCall("network.dhcpService", array($command));
}

function dnsService($command) {
    return xmlCall("network.dnsService", array($command));
}

?>