<?

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

$subnet = $_GET["subnet"];
$subnetInfos = getSubnet($_GET["subnet"]);
if (count($subnetInfos) == 0) {
    header("Location: " . urlStrRedirect("network/network/subnetindex"));
    exit;
}
$netmask = $subnetInfos[0][1]["dhcpNetMask"][0];
if ($_GET["action"] == "subnetaddhost") $title =  sprintf(_T("Add a DHCP host to subnet %s / %s"), $subnet, $netmask);
else $title = sprintf(_T("Edit DHCP host of subnet %s"), $subnet);

$p = new PageGenerator($title);
$sidemenu->forceActiveItem("subnetindex");
$p->setSideMenu($sidemenu);
$p->display();

global $error;

if (isset($_POST["badd"]) || (isset($_POST["bedit"]))) {
    $updatednsrecord = False;
    $zone = "";
    $hostname = $_POST["hostname"];
    $macaddress = $_POST["macaddress"];
    $ipaddress = $_POST["ipaddress"];
    $tftpservername = $_POST["tftp-server-name"];
    $oldip = $_POST["oldip"];
    $filename = trim($_POST["filename"]);
    if (strlen($filename)) $filename = '"' . $filename . '"';
    $rootpath = trim($_POST["rootpath"]);
    if (strlen($rootpath)) $rootpath = '"' . $rootpath . '"';

    /* Check that the given IP address is in the subnet */
    if (!ipInNetwork($ipaddress, $subnet, $netmask)) {
        $error = _T("The specified IP address does not belong to the subnet.") . " ";
        setFormError("ipaddress");
    }
    /* Check that the given address is not in the dynamic pool range */
    $pool = getPool($subnet);
    if (count($pool)) {
        $range = $pool[0][1]["dhcpRange"][0];
        list($ipstart, $ipend) = explode(" ", $range);
        if (ipInRange($ipaddress, $ipstart, $ipend)) {
            $error .= _T("The specified IP address belongs to the dynamic pool range of the subnet.") . " ";
            setFormError("ipaddress");
        }
    }


    if (isset($_POST["badd"])) {
        /* Check that this hostname or IP address has been already registered in the DHCP subnet */
        if (hostExistsInSubnet($subnet, $hostname)) {
            $error .= _T("The specified hostname has been already registered in this DHCP subnet.") . " ";
            setFormError("hostname");
            $hostname = "";        
        }
        if (ipExistsInSubnet($subnet, $ipaddress)) {
            $error .= _T("The specified IP address has been already registered in this DHCP subnet.") . " ";
            setFormError("ipaddress");
            $ipaddress = "";       
        }
        if (isset($_POST["dnsrecord"]) && !isset($error)) {
            /* Check that no hostname and reverse for this IP address exist in the DNS zone */
            $options = getSubnetOptions(getSubnet($subnet));
            if (isset($options["domain-name"])) {
                $zone = $options["domain-name"];
                if (hostExists($zone, $hostname)) {
                    $error .= sprintf(_T("The specified hostname has been already registered in DNS zone %s."), $zone) . " ";
                    setFormError("hostname");
                    $hostname = "";
                }
                if (ipExists($zone, $ipaddress)) {
                    $error .= sprintf(_T("The specified IP address has been already registered in DNS zone %s."), $zone) . " ";
                    setFormError("ipaddress");
                    $ipaddress = "";
                }
            }
        }
    } else if (isset($_POST["bedit"])) {
        if ($_POST["oldip"] != $ipaddress) {
            /* The static IP must be changed */
            if (ipExistsInSubnet($subnet, $ipaddress)) {
                $error .= _T("The specified IP address has been already registered in this DHCP subnet.") . " ";
                setFormError("ipaddress");
            }
            $options = getSubnetOptions(getSubnet($subnet));
            if (isset($options["domain-name"])) {
                /* If a DNS record exists for this machine, we need to update it too */
                $zone = $options["domain-name"];
                if (hostExists($zone, $hostname) && ipExists($zone, $oldip)) {
                    /* a record exists, can we update it ? */
                    if (ipExists($zone, $ipaddress)) {
                        /* The new IP already exists */
                        /* If the current hostname doesn't resolve to this already existing IP, we can't register it */
                        if (resolve($zone, $hostname) != $ipaddress) {
                            $error .= sprintf(_T("The IP address %s is already registered in DNS zone %s"), $ipaddress, $zone);
                            setFormError("ipaddress");
                        } /* else there is no need to update the DNS record, it is already set to the good value */
                    } else $updatednsrecord = True;
                } 
            }
        }
    }
    if (!isset($error)) {
        if (isset($_POST["badd"])) {
            addHostToSubnet($subnet, $hostname);
            setHostOption($hostname, "host-name", $hostname);
            if (isset($_POST["dnsrecord"])) {
                $options = getSubnetOptions(getSubnet($subnet));
                if (isset($options["domain-name"])) addRecordA($options["domain-name"], $hostname, $ipaddress);
            }
        }
        setHostOption($hostname, "root-path", $rootpath);
        setHostOption($hostname, "tftp-server-name", $tftpservername);
        setHostStatement($hostname, "filename", $filename);
        setHostHWAddress($hostname, $macaddress);
        setHostStatement($hostname, "fixed-address", $ipaddress);
        if ($updatednsrecord) modifyRecord($zone, $hostname, $ipaddress);
        // Display result message
        if (!isXMLRPCError()) {
            if (isset($_POST["badd"])) $result .= _T("Host successfully added.");
            else $result .= _T("Host successfully modified.");
            if ($updatednsrecord) $result .= "<br>" . _T("DNS record successfully modified");
            new NotifyWidgetSuccess($result);
            header("Location: " . urlStrRedirect("network/network/subnetmembers", array("subnet" => $subnet)));
        }
    } else new NotifyWidgetFailure($error);
}

if ($_GET["action"] == "subnetedithost") {
    $hostname = $_GET["host"];
    $host = getHost($hostname);
    $options = array();
    foreach($host[0][1]["dhcpOption"] as $option) {
        list($name, $value) = explode(" ", $option, 2);
        $options[$name] = trim($value, '"');
    }
    $statements = array();
    foreach($host[0][1]["dhcpStatements"] as $statement) {
        list($name, $value) = explode(" ", $statement, 2);
        $statements[$name] = trim($value, '"');
    }
    $macaddress = $host[0][1]["dhcpHWAddress"][0];
    list($tmp, $macaddress) = explode(" ", $macaddress);
    $ipaddress = $statements["fixed-address"];
    $filename = $statements["filename"];
    $rootpath = $options["root-path"];
    $tftpservername = $options["tftp-server-name"];
} else if ($_GET["action"] == "subnetaddhost") {
    if (!isset($error)) {
        /* Reset the field only if no error where found */
        $hostname = $_GET["host"];
        $macaddress = $_GET["macaddress"];
    }
}

$f = new ValidatingForm();
$f->push(new Table());

if ($_GET["action"]=="subnetaddhost") {
    $formElt = new HostnameInputTpl("hostname");
} else {
    $formElt = new HiddenTpl("hostname");
    /* Keep the old IP in the page to detect that the user want to change the machine IP */
    $oldIp = new HiddenTpl("oldip");
    $oldIp->display(array("value" => $ipaddress, "hide" => True));
}

$tr = new TrFormElement(_T("Host name"), $formElt);
$tr->setCssError("hostname");
$f->add($tr, array("value" => $hostname, "required" => True));

$tr = new TrFormElement(_T("IP address"), new IPInputTpl("ipaddress"));
$tr->setCssError("ipaddress");
$f->add($tr, array("value" => $ipaddress, "required" => True));

$f->add(
        new TrFormElement(_T("MAC address"),new MACInputTpl("macaddress")),
        array("value" => $macaddress, "required" => True)
        );
$f->pop();

$options = getSubnetOptions(getSubnet($subnet));
if (isset($options["domain-name"])) {
    $domain = $options["domain-name"];
    if (zoneExists($domain)) {
        $f->push(new Table());
        if ($_GET["action"] == "subnetaddhost") {
            /*
                If the DHCP domain name option is set, and corresponds to an existing DNS zone
                we ask the user if she/he wants to record the machine in the DNS zone too.
            */
            $f->add(
                    new TrFormElement(_T("Also records this machine into DNS zone") . "&nbsp;" . $domain, new CheckboxTpl("dnsrecord")),
                    array("value" => "CHECKED")
                    );
        } else {
            $domainurl = urlStr("network/network/zonemembers", array("zone" => "localnet"));
            $domainlink = '<a href="' . $domainurl . "\">$domain</a>";
            if (hostExists($domain, $hostname)) {
                $f->add(new TrFormElement(sprintf(_T("This host name is also registered in DNS zone %s"), $domainlink), new HiddenTpl("")));
                $resolvedip = resolve($domain, $hostname);
                if ((strlen($resolvedip) > 0) && ($ipaddress != $resolvedip)) {
                    $warn = '<div class="error">' . sprintf(_T("but with another IP address (%s)"), $resolvedip) . '</div>';
                    $f->add(new TrFormElement($warn, new HiddenTpl("")));
                }
            } else {
                $warn = '<div class="error">' . sprintf(_T("This host is not registered in DNS zone %s"), $domainlink) . '</div>';
                $f->add(new TrFormElement($warn, new HiddenTpl("")));
                $newhosturl = urlStr("network/network/addhost", array("zone" => "localnet", "host" => $hostname, "ipaddress" => $ipaddress, "gobackto" => rawurlencode($_SERVER["QUERY_STRING"])));
                $newhostlink = '<a href="' . $newhosturl . '">' . _T("Click here to add it") . "</a>";
                $f->add(new TrFormElement($newhostlink, new HiddenTpl("")));
            }            
        }
        $f->pop();
    }
}

$f->push(new Table());
$f->add(new TrFormElement(_T("Other DHCP options"), new HiddenTpl("")));
$f->add(
        new TrFormElement(_T("Initial boot file name"),new IA5InputTpl("filename")),
        array("value"=>$filename)
        );
$f->add(
        new TrFormElement(_T("Path to the root filesystem"),new IA5InputTpl("rootpath")),
        array("value"=>$rootpath)
        );
$f->add(
        new TrFormElement(_T("TFTP server name"),new IA5InputTpl("tftp-server-name")),
        array("value"=>$tftpservername)
        );
$f->pop();

$f->pop();

if ($_GET["action"] == "subnetaddhost") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addButton("badd", _("Confirm"));
}

$f->display();

?>