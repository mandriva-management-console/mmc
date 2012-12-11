<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id$
 *
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

require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

global $error;
global $result;

$p = new PageGenerator();
if ($_GET["action"] == "add") $title =  _T("Add a DNS zone");
else {
    $title =  _T("Edit DNS zone");;
    $sidemenu->forceActiveItem("index");
}
$p->setTitle($title);
$p->setSideMenu($sidemenu);
$p->display();

function hasARecord($zone, $name) {

    global $error;

    $msg = sprintf(_T("%s is not a A record of this zone."), $name) . '<br/>';
    # remove current zone name from the host name
    $name = str_replace("." . $zone, "", $name);
    if (!hasRecord($zone, "a", $name)) {
        $error .= $msg;
        $ret = False;
    }
    else
        $ret = $name;

    return $ret;
}

if (isset($_POST["badd"])) {
    $zonename = $_POST["zonename"];
    $netaddress = $_POST["netaddress"];
    $netmask = $_POST["netmask"];
    $description = $_POST["description"];
    $nameserver = $_POST["nameserver"];
    $nameserverip = $_POST["nameserverip"];

    $hasnetaddress = strlen($_POST["netaddress"]) > 0;
    $hasnetmask = strlen($_POST["netmask"]) > 0;

    /* Check that the zone name does not already exists */
    if (zoneexists($zonename)) {
        $error .= " " . _T("This zone already exists.");
        setFormError("zonename");
    }

    /* Check that network address and mask are filled if reverse of DHCP subnet are wanted */
    $reverse = False;
    if (isset($_POST["reverse"])) {
        if ($hasnetaddress & $hasnetmask) $reverse = True;
        else {
            $error .= " " . _T("The network address and the network mask fields must be filled in if you also want to create a reverse zone.");
            if (!$hasnetaddress) setFormError("netaddress");
            if (!$hasnetmask) setFormError("netmask");
        }
    }
    $dhcpsubnet = False;
    if (isset($_POST["dhcpsubnet"])) {
        if ($hasnetaddress & $hasnetmask) $dhcpsubnet = True;
        else {
            $error .= " " . _T("The network address and the network mask fields must be filled in if you also want to create a DHCP subnet.");
            if (!$hasnetaddress) setFormError("netaddress");
            if (!$hasnetmask) setFormError("netmask");
        }
    }

    /* Check that the given subnet is not contained into an existing subnet */
    if ($dhcpsubnet) {
        foreach(getSubnets("") as $dn => $entry) {
            $subnet = $entry[1]["cn"][0];
            $mask = $entry[1]["dhcpNetMask"][0];
            if (ipInNetwork($netaddress, $subnet, $mask, True)) {
                $error .= " " . sprintf(_T("The given network address belongs to the already existing DHCP subnet %s / %s."), $subnet, $mask);
                break;
            }
        }
    }

    if (!isset($error)) {
        $result = "";
        if ($dhcpsubnet) {
            addZoneWithSubnet($zonename, $netaddress, $netmask, $reverse, $description, $nameserver, $nameserverip);
            $result .= _T("DHCP subnet and DNS zone successfully added.");
            $result .= "<br />" . _T("The DHCP and DNS services must be restarted.");
            $services = array("bind9" => "DNS", "isc-dhcp-server" => "DHCP");
        } else {
            addZone($zonename, $netaddress, $netmask, $reverse, $description, $nameserver, $nameserverip);
            $result .= _T("DNS zone successfully added.");
            $result .= "<br />" . _T("The DNS service must be restarted.");
            $services = array("bind9" => "DNS");
        }
        if (!isXMLRPCError()) {
            $n = new NotifyWidgetSuccess($result);
            handleServicesModule($n, $services);
            header("Location: " . urlStrRedirect("network/network/index"));
            exit;
        }
    } else
        new NotifyWidgetFailure($error);
} else if (isset($_POST["bedit"])) {
    $zonename = $_POST["zonename"];
    $nameserver = $_POST["nameserver"];
    $description = $_POST["description"];
    $nameserverstmp = array_unique($_POST["nameservers"]);
    $mxserverstmp = array_unique($_POST["mxservers"]);
    $zoneaddress = $_POST["zoneaddress"];

    $nameserver = hasARecord($zonename, $nameserver);
    if (!$nameserver)
        setFormError('nameserver');

    $nameservers = array();
    foreach($nameserverstmp as $ns) {
        if (!empty($ns) && $ns != $nameserver) {
            $nsName = hasARecord($zonename, $ns);
            if (!$nsName)
                setFormError('nameservers0');
            else
                $nameservers[] = $ns;
        }
    }
    $mxservers = array();
    foreach($mxserverstmp as $mx) {
        if (!empty($mx)) {
            $mxInfo = explode(' ', $mx);
            $mxPriority = $mxInfo[0];
            $mxName = $mxInfo[1];
            $mxName = hasARecord($zonename, $mxName);
            if (!$mxName)
                setFormError('mxservers0');
            else
                $mxservers[] = $mxPriority . ' ' . $mxName;
        }
    }
    if (!isset($error)) {
        setSOANSRecord($zonename, $nameserver);
        setNSRecords($zonename, $nameservers);
        setMXRecords($zonename, $mxservers);
        setSOAARecord($zonename, $zoneaddress);
        setZoneDescription($zonename, $description);
        if (!isXMLRPCError()) {
            new NotifyWidgetSuccess(_T("DNS zone successfully modified."));
            header("Location: " . urlStrRedirect("network/network/index"));
            exit;
        }
    } else {
        new NotifyWidgetFailure($error);
        $mxservers = $mxserverstmp;
        $nameservers = $nameserverstmp;
    }
}

if (($_GET["action"] == "edit") && !isset($error)) {
    $zonename = $_GET["zone"];
    $soa = getSOARecord($zonename);
    $nameserver = trim($soa["nameserver"], ".");
    $nameservers = array();
    foreach(getNSRecords($zonename) as $ns) {
        if ($ns != $soa["nameserver"]) {
            $nameservers[] = trim($ns, '.');
        }
    }
    if (empty($nameservers)) {
        $nameservers = array('');
    }

    $mxservers = array();
    foreach(getMXRecords($zonename) as $mx) {
        $mxservers[] = trim($mx, '.');
    }
    if (empty($mxservers)) {
        $mxservers = array('');
    }
    $zoneaddress = getSOAARecord($zonename);
    $zones = getZones($zonename);
    $description = $zones[0][1]["tXTRecord"][0];
}

$f = new ValidatingForm();
$f->push(new Table());

if ($_GET["action"]=="add") {
    $formElt1 = new DomainInputTpl("zonename");
    $formElt2 = new HostnameInputTpl("nameserver");
    $nameserver = "ns";
} else {
    $formElt1 = new HiddenTpl("zonename");
    $formElt2 = new DomainInputTpl("nameserver");
    $formElt3 = new MultipleInputTpl("nameservers", _T("Secondary name servers"));
    $formElt3->setRegexp($formElt2->regexp);
    $formElt4 = new MultipleInputTpl("mxservers", _T("MX records (SMTP servers)"));
    $tmp = new MXRecordInputTpl("mx");
    $formElt4->setRegexp($tmp->regexp);
}
$f->add(
        new TrFormElement(_T("DNS zone FQDN"), $formElt1),
        array("value" => $zonename, "required" => True)
        );
$f->add(
        new TrFormElement(_T("Description"),new IA5InputTpl("description")),
        array("value" => $description)
        );
$f->add(
        new TrFormElement(_T("Primary name server host name"), $formElt2, array("tooltip" => _T("The primary name server for this zone. The host must exists in zone (A record)."))),
        array("value" => $nameserver, "required" => True)
        );

if ($_GET["action"] == "add") {
    $f->add(
            new TrFormElement(_T("Name server IP"),new IPInputTpl("nameserverip")),
            array("value" => "")
            );
    $f->pop();

    $f->push(new Table());

    if (hasDHCP()) {
        $f->add(new TrFormElement(_T("The network address and mask fields must be filled in if you also want to create a reverse zone or a DHCP subnet linked to this DNS zone."), new HiddenTpl("")));
    }
    else {
        $f->add(new TrFormElement(_T("The network address and mask fields must be filled in if you also want to create a reverse zone."), new HiddenTpl("")));
    }

    $f->add(
            new TrFormElement(_T("Network address"), new IPInputTpl("netaddress")),
            array("value" => $netaddress)
            );
    $f->add(
            new TrFormElement(_T("Network mask"), new NetmaskInputTpl("netmask")),
            array("value" => $netmask, "extra" => _T("(e.g. 24 for a /24 network)"))
            );
    $f->add(
            new TrFormElement(_T("Also manage a reverse DNS zone"), new CheckboxTpl("reverse")),
            array("value" => "CHECKED")
        );

    if (hasDHCP()) {
        $f->add(
                new TrFormElement(_T("Also create a related DHCP subnet"), new CheckboxTpl("dhcpsubnet")),
                array("value" => "CHECKED")
            );
    }
    $f->pop();
} else {
    $f->add(
            new TrFormElement(_T("IP address of the zone"), new IPInputTpl("zoneaddress"), array("tooltip" => _T("Your zone name will be resolved to this IP address."))),
            array("value" => $zoneaddress)
            );
    $f->pop();
    $f->add(
            new FormElement(_T("Secondary name servers"), $formElt3, array("tooltip" => _T("Name of other name servers in the zone. The hosts must exist in the zone (A record)."))),
            $nameservers
            );
    $f->add(
            new FormElement(_T("MX records (SMTP servers)"), $formElt4, array("tooltip" => _T("Example : <strong>10 smtp</strong><br />Where '10' is the priority and 'smtp' the name of your mail server. The host 'smtp' must exists in the zone (A record)."))),
            $mxservers
            );
}


if ($_GET["action"] == "add") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addButton("bedit", _("Confirm"));
}
$f->pop();
$f->display();

?>
