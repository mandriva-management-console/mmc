<?

/**
 * Input with a check for host name validity in a DNS zone
 */
class HostnameInputTpl extends InputTpl {

    function HostnameInputTpl($name) {
        $this->name = $name;
        $this->regexp = '/^[a-z][a-z0-9-]*[a-z0-9]$/';
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