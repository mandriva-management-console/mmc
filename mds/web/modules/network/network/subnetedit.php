<?
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id$ *
 * This file is part of Mandriva Management Console (MMC).
 *
 * MMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * MMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("modules/network/includes/network2.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

if ($_GET["action"] == "subnetadd") $title =  _T("Add a DHCP subnet");
else $title =  _T("Edit DHCP subnet");;

$p = new PageGenerator($title);
if ($_GET["action"] == "subnetedit") $sidemenu->forceActiveItem("subnetindex");
$p->setSideMenu($sidemenu);
$p->display();

function isAuthoritative($subnet) {
    $ret = False;
    $statements = array();
    if (isset($subnet[0][1]["dhcpStatements"])) {
        foreach($subnet[0][1]["dhcpStatements"] as $statement) {
            if ($statement == "authoritative") {
                $ret = True;
                break;
            }
        }
    }
    return $ret;
}


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

function checkPools() {
    /* Check that the given pool range is valid */
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    $pools = $_POST["subnetpools"];
    $hasPools = isset($_POST["hassubnetpools"]);
    if ($hasPools) {
	for ($i = 0; $i < count($pools); $i++)
	{
	    list($ipStart, $ipEnd) = split(" ", $pools[$i]);
	    if (isset($ipStart) && isset($ipEnd)) {
		if (!(ipLowerThan($ipStart, $ipEnd) && ipInNetwork($ipStart, $subnet, $netmask) && ipInNetwork($ipEnd, $subnet, $netmask))){
		    $error .= sprintf(_T("The specified dynamic pool IP range from %s to %s is not valid."), $ipStart, $ipEnd);
		    if ($i < count($pools) -1) $error .= "<br>";
		}
	    } else { 
		$error.= sprintf(_T("No IP range specified for %d dynamic pool."), $i + 1);
		if ($i < count($pools) -1) $error .= "<br>";
	    }
	}
    }
    return array(isset($error), $error);
}

if (isset($_POST["badd"])) $checks = array("checkSubnet", "checkPools");
if (isset($_POST["bedit"])) $checks = array("checkPools");


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
    $names = array("broadcast-address", "routers", "domain-name", "domain-name-servers", "ntp-servers", "netbios-name-servers", "netbios-node-type", "root-path", "tftp-server-name","local-pac-server");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
	if (in_array($name, array("domain-name", "root-path", "tftp-server-name")))
            $value = '"' . $value . '"';
	if (in_array($name, array("domain-name", "root-path", "tftp-server-name", "local-pac-server")))
            $value = str_replace(" ", ",", $value);
        setSubnetOption($subnet, $name, $value);
    }

    /* Update the DHCP statements */
    $names = array("filename", "next-server", "min-lease-time", "default-lease-time", "max-lease-time");
    foreach($names as $name) {
        $value = trim($_POST[$name]);
        if (strlen($value)) {
            if (in_array($name, array("filename")))
                $value = '"' . $value . '"';
        }
        setSubnetStatement($subnet, $name, $value);
    }
    setSubnetAuthoritative($subnet, isset($_POST["authoritative"]));
    
    /* Create or update the DHCP pools */
    $poolsRanges = (isset($_POST["hassubnetpools"])) ? $_POST["subnetpools"] : array();
    setPoolsRanges($subnet, $poolsRanges);
    
    if (!isXMLRPCError()) {
        if (isset($_POST["badd"])) {
            new NotifyWidgetSuccess(_T("Subnet successfully added. You must restart the DHCP service."));
        } else if (isset($_POST["bedit"])) {
            new NotifyWidgetSuccess(_T("Subnet successfully modified. You must restart the DHCP service."));
        }
        header("Location: " . urlStrRedirect("network/network/subnetindex"));
    }    
}

if (isset($error)) {
    new NotifyWidgetFailure($error);
    $subnet = $_POST["subnet"];
    $netmask = $_POST["netmask"];
    if (isset($_POST["hassubnetpools"])) $hasSubnetPools = "checked";
    else $hasSubnetPools = "";    
    $poolsRanges = $_POST["subnetpools"];
}


if ($_GET["action"] == "subnetedit") {
    $subnetInfos = getSubnet($_GET["subnet"]);
    $subnet = $subnetInfos[0][1]["cn"][0];
    $netmask = $subnetInfos[0][1]["dhcpNetMask"][0];
    $description = $subnetInfos[0][1]["dhcpComments"][0];
    $options = getSubnetOptions($subnetInfos);
    $statements = getSubnetStatements($subnetInfos);
    if (isAuthoritative($subnetInfos)) {
        $authoritative = "CHECKED";
    } else {
        $authoritative = "";
    }
    $poolsRanges = getPoolsRanges($subnet);
    $hasSubnetPools = (count($poolsRanges)) ? "checked": "";
}
    //var_dump($pool);

if ($_GET["action"] == "subnetadd") {    
    $formElt = new IPInputTpl("subnet");
    $authoritative = "";
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
        array("value" => $netmask, "required" => True, "extra" => _T("(e.g. 24 for a /24 network)"))
        );
$f->add(
        new TrFormElement(_T("Description"),new IA5InputTpl("description")),
        array("value" => $description)
        );
$f->add(
        new TrFormElement(_T("Authoritative"),new CheckboxTpl("authoritative")),
        array("value" => $authoritative)
        );
$f->pop();

$f->push(new Table());
$f->add(new TrFormElement(_T("DHCP options related to clients network parameters"), new HiddenTpl("")));
$f->add(
        new TrFormElement(_T("Broadcast address"), new IPInputTpl("broadcast-address")),
        array("value"=>$options["broadcast-address"])
        );
$f->add(
        new TrFormElement(_T("Domain name"), new IA5InputTpl("domain-name"),
                          array(
                                "tooltip" => _T("Domain name that will be appended to the client's hostname to form a fully-qualified domain-name (FQDN).") . "<br/>"
                                . _T("If the domain name is a registered DNS domain, the subnet will be associated to the DNS domain.") . "<br/>"
                                . _T("You can set more than one domain, separated by spaces. They will be added to the DHCP client DNS domain search path.")
                                )
                          ),
        array("value"=>$options["domain-name"], "extra" => _T("Links the subnet to a DNS zone"))
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
                                "tooltip" => _T("DNS name servers available to the client. Separate servers addresses with ','.")
                                )
                          ),
        array("value"=>$options["domain-name-servers"])
        );
$f->add(
        new TrFormElement(_T("NTP servers"),new HostIpListInputTpl("ntp-servers"),
                          array(
                                "tooltip" => _T("Network Time Protocol servers available to the client. Separate servers addresses with ','.")
                                )
                          ),
        array("value"=>$options["ntp-servers"])
        );
$f->add(
        new TrFormElement(_T("Proxy auto config URL"),new IA5InputTpl("local-pac-server"),
                          array(
                                "tooltip" => _T("Automatic proxy configuration URL (PAC).")
                                )
                          ),
        array("value"=>$options["local-pac-server"])
        );
$f->add(
        new TrFormElement(_T("WINS servers"),new HostIpListInputTpl("netbios-name-servers"),
                          array(
                                "tooltip" => _T("Netbios name servers available to Windows clients, listed in order of preference. Separate servers addresses with ','.")
                                )
                          ),
        array("value"=>$options["netbios-name-servers"])
        );

$winsclient = new SelectItem("netbios-node-type");
$types = array("" => "Auto", "1" => _T("Broadcast only"), "2" => _T("WINS only"), "4" => _T("Broadcast, then WINS"), "8" => _T("WINS, then broadcast"));
$winsclient->setElements(array_values($types));
$winsclient->setElementsVal(array_keys($types));
$f->add(
        new TrFormElement(_T("WINS resolution and registration method"),$winsclient,
                          array(
                                "tooltip" => _T("Specify how NetBIOS name resolution is performed. Auto: the client OS will automatically select a method. Broadcast only (B-node): use broadcast for name resolution and registration. Peer node (P-node): use the specified WINS servers. Mixed node (M-node): use broadcast, then the specified WINS servers. Hybrid node (H-node): use the specified WINS server, then broadcast.")
                                )
                          ),
        array("value"=>$options["netbios-node-type"])
        );

$f->pop();

$f->push(new Table());
$f->add(new TrFormElement(_T("Other DHCP options"), new HiddenTpl("")));
$tooltip = _T("Specify the name of the initial boot file which is to be loaded by a client.");
$tooltip .= "%s" ;
$tooltip .= _T("The filename should be a filename recognizable to whatever file transfer protocol the client can be expected to use to load the file.");
$tooltip .= "%s";
$tooltip .= _T("(DHCP option number 67)");
$tooltip = sprintf($tooltip, "<br/>", "<br/>");
$f->add(
        new TrFormElement(_T("Initial boot file name"),new IA5InputTpl("filename"),
                          array("tooltip" => $tooltip )
                          ),
        array("value"=>$statements["filename"])
        );
$tooltip = _T("Path-name that contains the client's root disk.");
$tooltip .= "%s";
$tooltip .= _T("(DHCP option number 17)");
$tooltip = sprintf($tooltip, "<br/>");
$f->add(
        new TrFormElement(_T("Path to the root filesystem"), new IA5InputTpl("root-path"),
                          array("tooltip" => $tooltip )
                          ),
        array("value" => $options["root-path"])
        );
$tooltip = _T("Server from which the initial boot file is to be loaded");
$f->add(
        new TrFormElement(_T("Next server"), new IA5InputTpl("next-server"),
                          array("tooltip" => $tooltip)
                          ),
        array("value" => $statements["next-server"])
        );
$tooltip = _T("Trivial File Transfer Protocol server name from which the client is booting.");
$tooltip .= "%s";
$tooltip .= _T("(DHCP option number 66)");
$tooltip = sprintf($tooltip, "<br/>");
$f->add(
        new TrFormElement(_T("TFTP server name"),new IA5InputTpl("tftp-server-name"),
                          array("tooltip" => $tooltip )
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
        new TrFormElement(_T("Dynamic pool(s) for non-registered DHCP clients", "network"),new CheckboxTpl("hassubnetpools")),
        array("value"=>$hasSubnetPools, "extraArg"=>'onclick="toggleVisibility(\'poolsdiv\');"')
        );


$f->pop();



$poolsdiv = new Div(array("id" => "poolsdiv"));
$poolsdiv->setVisibility($hasSubnetPools);
$f->push($poolsdiv);

$f->add(new FormElement(_T("Dynamic pools"),
			new MultipleRangeInputTpl("subnetpools")), 
			$poolsRanges);
$f->pop();

$f->pop(); // pop the form



if ($_GET["action"] == "subnetadd") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addButton("bedit", _("Confirm"));
}
$f->display();

?>
