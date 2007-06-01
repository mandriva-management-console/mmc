<?
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id$
 *
 * This file is part of LMC.
 *
 * LMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * LMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "subnetadd") $title =  _T("Add a DHCP subnet");
else $title =  _T("Edit DHCP subnet");;

$p = new PageGenerator($title);
if ($_GET["action"] == "subnetedit") $sidemenu->forceActiveItem("subnetindex");
$p->setSideMenu($sidemenu);
$p->display();

function checkSubnet() {
    /* Check that the given subnet is not contained into an existing subnet */
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    foreach(getSubnets("") as $dn => $entry) {
        $sub = $entry[1]["cn"][0];
        $mask = $entry[1]["dhcpNetMask"][0];
        if (ipInNetwork($subnet, $sub, $mask, True)) {
            $error = sprintf(_T("The given network address belongs to the already existing DHCP subnet %s / %s."), $sub, $mask);
            setFormError("subnet");
            setFormError("netmask");
            break;
        }
    }
    if (isset($error)) {
        $_POST["subnet"] = "";
        $_POST["netmask"] = "";
    }
    return array(isset($error), $error);
}

function checkPool() {
    /* Check that the given pool range is valid */
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];

    if (isset($_POST["badd"])) $pool = array();
    else $pool = getPool($subnet);
    if (isset($_POST["subnetpool"])) {
        if (isset($_POST["ipstart"]) && isset($_POST["ipend"])) {
            $ipstart = $_POST["ipstart"];
            $ipend = $_POST["ipend"];
            if (!(ipLowerThan($ipstart, $ipend) && ipInNetwork($ipstart, $subnet, $netmask) && ipInNetwork($ipend, $subnet, $netmask)))
                $error .= _T("The specified dynamic pool IP range is not valid.");
        } else $error.= _T("No dynamic pool IP range specified.");
    }
    return array(isset($error), $error);
}

if (isset($_POST["badd"])) $checks = array("checkSubnet", "checkPool");
if (isset($_POST["bedit"])) $checks = array("checkPool");


if (isset($_POST["badd"]) | isset($_POST["bedit"])) {
    foreach($checks as $check) {
        list($result, $error) = call_user_func($check);
        if ($result) break;
    }
}
    
if (!isset($error)
     & (isset($_POST["badd"]) || (isset($_POST["bedit"])))) {
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    $description = stripslashes($_POST["description"]);
    
    /* edit the subnet */
    if (isset($_POST["badd"])) {
        addSubnet($subnet, $netmask, $description);        
    } else {
        setSubnetNetmask($subnet, $netmask);
        setSubnetDescription($subnet, $description);
    }

    /* Update the DHCP options */
    $names = array("broadcast-address", "routers", "domain-name", "domain-name-servers", "ntp-servers", "root-path", "tftp-server-name");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
	if (in_array($name, array("domain-name", "root-path", "tftp-server-name")))
            $value = '"' . $value . '"';
	if (in_array($name, array("domain-name-servers", "ntp-servers")))
            $value = str_replace(" ", ",", $value);
        setSubnetOption($subnet, $name, $value);
    }

    /* Update the DHCP statements */
    $names = array("filename", "min-lease-time", "default-lease-time", "max-lease-time");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
        if (strlen($value)) {
            if (in_array($name, array("filename")))
                $value = '"' . $value . '"';
        }
        setSubnetStatement($subnet, $name, $value);
    }
    
    /* Create or update the DHCP pool */
    if (isset($_POST["badd"])) $pool = array();
    else $pool = getPool($subnet);
    if (isset($_POST["subnetpool"])) {
        if (isset($_POST["ipstart"]) && isset($_POST["ipend"])) {
            $ipstart = $_POST["ipstart"];
            $ipend = $_POST["ipend"];
            if (count($pool)) setPoolRange($subnet, $ipstart, $ipend);
            else {
                /* The pool needs to be created */
                addPool($subnet, $subnet, $ipstart, $ipend);
            }
        }
    } else {
        /* Dynamic pool management is not checked */
        if (count($pool)) delPool($subnet);
    }
    
    if (!isXMLRPCError()) {
        if (isset($_POST["badd"])) {
            new NotifyWidgetSuccess(_T("Subnet successfully added. You must restart the DHCP service."));
            header("Location: " . urlStrRedirect("network/network/subnetindex"));
        } else if (isset($_POST["bedit"])) {
            new NotifyWidgetSuccess(_T("Subnet successfully modified. You must restart the DHCP service."));
        }
    }    
}

if (isset($error)) {
    new NotifyWidgetFailure($error);
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    if (isset($_POST["subnetpool"])) $hasSubnetPool = "checked";
    else $hasSubnetPool = "";    
}


if ($_GET["action"] == "subnetedit") {
    $subnetInfos = getSubnet($_GET["subnet"]);
    $subnet = $subnetInfos[0][1]["cn"][0];
    $netmask = $subnetInfos[0][1]["dhcpNetMask"][0];
    $description = $subnetInfos[0][1]["dhcpComments"][0];
    $options = getSubnetOptions($subnetInfos);
    $statements = getSubnetStatements($subnetInfos);
    $pool = getPool($_GET["subnet"]);
    if (count($pool)) {
        $hasSubnetPool = "checked";
        $range = $pool[0][1]["dhcpRange"][0];
        list($ipstart, $ipend) = explode(" ", $range);
    } else $hasSubnetPool = "";
}

if ($_GET["action"]=="subnetadd") {
    $formElt = new IPInputTpl("subnet");
} else {
    $formElt = new HiddenTpl("subnet");
}

$f = new ValidatingForm();

$f->push(new Table());
$f->add(
        new TrFormElement(_T("DHCP subnet address"), $formElt),
        array("value" => $subnet, "required" => True)
        );
$f->add(
        new TrFormElement(_T("Netmask"),new NetmaskInputTpl("netmask")),
        array("value" => $netmask, "required" => True, "extra" => "(e.g. 24 for a /24 network)")
        );
$f->add(
        new TrFormElement(_T("Description"),new IA5InputTpl("description")),
        array("value" => $description)
        );
$f->pop();

$f->push(new Table());
$f->add(new TrFormElement(_T("DHCP options related to clients network parameters"), new HiddenTpl("")));
$f->add(
        new TrFormElement(_T("Broadcast address"), new IPInputTpl("broadcast-address")),
        array("value"=>$options["broadcast-address"])
        );
$f->add(
        new TrFormElement(_T("Domain name"), new DomainInputTpl("domain-name"),
                          array(
                                "tooltip" => _T("Domain name that will be appended to the client's hostname to form a fully-qualified domain-name (FQDN).<br/>
                                                If the domain name is a registered DNS domain, the subnet will be associated to the DNS domain.")
                                )
                          ),
        array("value"=>$options["domain-name"])
        );
$f->add(
        new TrFormElement(_T("Routers"), new HostIpListInputTpl("routers"),
                          array(
                                "tooltip" => _T("List of routers (gateways) on client's subnet.")
                                )
                          ),
        array("value"=>$options["routers"])
        );
$f->add(
        new TrFormElement(_T("Domain name servers"),new HostIpListInputTpl("domain-name-servers"),
                          array(
                                "tooltip" => _T("DNS name servers available to the client.")
                                )
                          ),
        array("value"=>$options["domain-name-servers"])
        );
$f->add(
        new TrFormElement(_T("NTP servers"),new HostIpListInputTpl("ntp-servers"),
                          array(
                                "tooltip" => _T("Network Time Protocol servers available to the client.")
                                )
                          ),
        array("value"=>$options["ntp-servers"])
        );
$f->pop();

$f->push(new Table());
$f->add(new TrFormElement(_T("Other DHCP options"), new HiddenTpl("")));
$f->add(
        new TrFormElement(_T("Initial boot file name"),new IA5InputTpl("filename"),
                          array(
                                "tooltip" => _T("Specify the name of the initial boot file which is to be loaded by a client.<br/>
                                                 The filename should be a filename recognizable to whatever file transfer protocol the client can be expected to use to load the file.<br/>
                                                 (DHCP option number 67)")
                                )
                          ),
        array("value"=>$statements["filename"])
        );
$f->add(
        new TrFormElement(_T("Path to the root filesystem"), new IA5InputTpl("root-path"),
                          array(
                                "tooltip" => _T("Path-name that contains the client's root disk.<br/>
                                                 (DHCP option number 17)")
                                )
                          ),
        array("value" => $options["root-path"])
        );        
$f->add(
        new TrFormElement(_T("TFTP server name"),new IA5InputTpl("tftp-server-name"),
                          array(
                                "tooltip" => _T("Trivial File Transfer Protocol server name from which the client is booting.<br/>
                                                (DHCP option number 66)")
                                )
                          ),
        array("value"=>$options["tftp-server-name"])   
        );
$f->pop();

$f->push(new Table());
$f->add(new TrFormElement(_T("DHCP client lease time (in seconds)"), new HiddenTpl("")));
$f->add(
        new TrFormElement(_T("Minimum lease time"), new NumericInputTpl("min-lease-time"),
                          array(
                                "tooltip" => _T("Minimum length in seconds that will be assigned to a lease.")
                                )                          
                          ),
        array("value"=>$statements["min-lease-time"])
        );
$f->add(
        new TrFormElement(_T("Default lease time"), new NumericInputTpl("default-lease-time"),
                          array(
                                "tooltip" => _T("Lengh in seconds that will be assigned to a lease if the client requesting the lease does not ask for a specific expiration time.")
                                )
                          ),
        array("value"=>$statements["default-lease-time"])
        );
$f->add(
        new TrFormElement(_T("Maximum lease time"),new NumericInputTpl("max-lease-time"),
                          array(
                                "tooltip" => _T("Maximum length in seconds that will be assigned to a lease.")
                                )
                          ),
        array("value"=>$statements["max-lease-time"])
        );
$f->pop();

$f->push(new Table());
$f->add(
        new TrFormElement(_T("Dynamic pool for non-registered DHCP clients", "network"),new CheckboxTpl("subnetpool")),
        array("value"=>$hasSubnetPool, "extraArg"=>'onclick="toggleVisibility(\'pooldiv\');"')
        );

$f->pop();

$pooldiv = new Div(array("id" => "pooldiv"));
$pooldiv->setVisibility($hasSubnetPool);
$f->push($pooldiv);
$f->push(new Table());
$f->add(
        new TrFormElement(_T("IP range start"), new IPInputTpl("ipstart")),
        array("value" => $ipstart)
        );
$f->add(
        new TrFormElement(_T("IP range end"), new IPInputTpl("ipend")),
        array("value" => $ipend)
        );
$f->pop();
$f->pop();

$f->pop(); // pop the form

if ($_GET["action"] == "subnetadd") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addButton("bedit", _("Confirm"));
}
$f->display();

?>
