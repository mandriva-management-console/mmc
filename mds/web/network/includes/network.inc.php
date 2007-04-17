<?

/**
 * Input with a check for host name validity in a DNS zone. No dot allowed !
 */
class HostnameInputTpl extends InputTpl {

    function HostnameInputTpl($name) {
        $this->name = $name;
        $this->regexp = '/^[a-z][a-z0-9-]*[a-z0-9]$/';
    }

}

/**
 * Input with a basic check for a colon separated list of host name and/or IP
 */
class HostIpListInputTpl extends InputTpl {
    
    function HostIpListInputTpl($name) {
        $this->name = $name;
        $this->regexp = '/^[0-9a-z,.-]*$/';
    }

}

/**
 * Input with a check for a valid net mask (range from 1 to 32)
 */
class NetmaskInputTpl extends InputTpl {

    function NetmaskInputTpl($name) {
        $this->name = $name;
        $this->regexp = '/^([1-9]|[1-2][0-9]|3[0-2])$/';
    }

}

/**
 * Input with a check for a valid simple net mask (8, 16, or 24)
 */
class SimpleNetmaskInputTpl extends InputTpl {

    function SimpleNetmaskInputTpl($name) {
        $this->name = $name;
        $this->regexp = '/^(8|16|24)$/';
    }

}

/* IP functions */

/**
 * Return True if an IP is in a network
 */
function ipInNetwork($ip, $network, $mask) {
    $ip = ip2long($ip);
    $network = ip2long($network);
    $mask = intval($mask);
    $ret = True;
    for ($i = 0 ; $i < $mask ; $i++) {
        $n = pow(2, 31 - $i) ;
        if (($n & $ip) != ($n & $network)) {
            $ret = False;
            break;
        }
    }    
    return $ret;
}

/**
 * Return True if IP A is lower than IP B
 */
function ipLowerThan($ipa, $ipb) {
    return ip2long($ipa) < ip2long($ipb);
}

/* Some common used utility functions */

function getSubnetOptions($subnet) {
    foreach($subnet[0][1]["dhcpOption"] as $option) {
        list($name, $value) = explode(" ", $option, 2);
        $options[$name] = trim($value, '"');
    }
    return $options;
}

function getSubnetStatements($subnet) {
    foreach($subnet[0][1]["dhcpStatements"] as $statement) {
        list($name, $value) = explode(" ", $statement, 2);
        $statements[$name] = trim($value, '"');
    }
    return $statements;
}

?>