<?php
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

if ($_GET["action"] == "add") $title =  _T("Add a DNS zone");
else {
    $title =  _T("Edit DNS zone");;
    $sidemenu->forceActiveItem("index");
}
?>

<h2><?= $title; ?></h2>

<div class="fixheight"></div>

<?

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

global $error;
if (isset($_POST["badd"])) {
    $zonename = $_POST["zonename"];
    $netaddress = $_POST["netaddress"];
    $netmask = $_POST["netmask"];
    $description = $_POST["description"];
    $nameserver = $_POST["nameserver"];
    $nameserverip = $_POST["nameserverip"];

    $hasnetaddress = strlen($_POST["netaddress"]) > 0;
    $hasnetmask = strlen($_POST["netmask"]) > 0;

    /* Check that network address and mask are filled if reverse of DHCP subnet are wanted */
    $reverse = False;
    if (isset($_POST["reverse"])) {
        if ($hasnetaddress & $hasnetmask) $reverse = True;
        else {
            $error = _T("The network address and the network mask field must be filled in if you also want to create a reverse zone.");
            if (!$hasnetaddress) setFormError("netaddress");
            if (!$hasnetmask) setFormError("netmask");
        }
    }
    $dhcpsubnet = False;
    if (isset($_POST["dhcpsubnet"])) {
        if ($hasnetaddress & $hasnetmask) $dhcpsubnet = True;
        else {
            $error .= " " . _T("The network address and the network mask field must be filled in if you also want to create a DHCP subnet.");
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
            $result .= _T("DHCP subnet successfully added.");
        } else {
            addZone($zonename, $netaddress, $netmask, $reverse, $description, $nameserver, $nameserverip);
        }
        
        if (!isXMLRPCError()) {
            $result .= " " . _T("DNS zone successfully added.")
            new NotifyWidgetSuccess($result);
            header("Location: " . urlStrRedirect("network/network/index"));
        }
    } else
        new NotifyWidgetFailure($error);
} else if (isset($_POST["bedit"])) {
    $zonename = $_POST["zonename"];
    $nameserver = $_POST["nameserver"];
    $description = $_POST["description"];
    setNSRecord($zonename, $nameserver . ".");
    setZoneDescription($zonename, $description);
    if (!isXMLRPCError()) new NotifyWidgetSuccess(_T("DNS zone successfully modified."));
}

if ($_GET["action"] == "edit") {
    $zonename = $_GET["zone"];
    $soa = getSOARecord($zonename);
    $nameserver = trim($soa["nameserver"], ".");
    $zones = getZones($zonename);
    $description = $zones[0][1]["tXTRecord"][0];
}

?>

<form name="zoneform" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validateForm();">
<table cellspacing="0">

<?
$arrNetwork = array("value" => $network, "required" => True);
if ($_GET["action"]=="add") {
    $formElt1 = new DomainInputTpl("zonename");
    $formElt2 = new HostnameInputTpl("nameserver");
    $nameserver = "ns";
} else {
    $formElt1 = new HiddenTpl("zonename");
    $formElt2 = new DomainInputTpl("nameserver");
}

$tr = new TrFormElement(_T("DNS zone FQDN"), $formElt1);
$tr->display(array("value" => $zonename, "required" => True));

$tr = new TrFormElement(_T("Description"),new IA5InputTpl("description"));
$tr->display(array("value" => $description));

$tr = new TrFormElement(_T("Name server host name"), $formElt2);
$tr->display(array("value" => $nameserver, "required" => True));

if ($_GET["action"] == "add") {    
    $tr = new TrFormElement(_T("Name server IP"),new IPInputTpl("nameserverip"));
    $tr->display(array("value" => ""));

    print "</table";
    print '<table cellspacing="0">';
    
    $tr = new TrFormElement(_T("The network address and mask fields must be filled in if you also want to create a reverse zone or a DHCP subnet linked to this DNS zone."), new HiddenTpl(""));
    $tr->display(array());

    $tr = new TrFormElement(_T("Network address"), new IPInputTpl("netaddress"));
    $tr->setCssError("netaddress");
    $tr->display(array("value" => $netaddress));

    $tr = new TrFormElement(_T("Network mask"), new SimpleNetmaskInputTpl("netmask"));
    $tr->setCssError("netmask");
    $tr->display(array("value" => $netmask, "extra" => _T("Only 8, 16 or 24 is allowed")));

    $tr = new TrFormElement(_T("Also manage a reverse zone"), new CheckboxTpl("reverse"));
    $tr->display(array("value" => "CHECKED"));

    $tr = new TrFormElement(_T("Also create a related DHCP subnet"), new CheckboxTpl("dhcpsubnet"));
    $tr->display(array("value" => "CHECKED"));
}
?>

</table>
<? if ($_GET["action"] == "add") { ?>
    <input name="badd" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<? } else { ?>
    <input name="bedit" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<? } ?>

</form>

<script>
document.body.onLoad = document.zoneform.zonename.focus();
</script>
