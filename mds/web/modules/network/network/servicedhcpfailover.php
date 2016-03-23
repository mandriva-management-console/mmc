<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id$
 *
 * This file is part of Management Console.
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

require("localSidebar.php");
require("graph/navbar.inc.php");
require_once("includes/FormHandler.php");

$p = new PageGenerator(_T("DHCP Failover configuration"));
$p->setSideMenu($sidemenu);
$p->display();

global $result;
global $error;

if ($_POST)
    $FH = new FormHandler("dhcpFailover", $_POST);
else
    $FH = new FormHandler("dhcpFailover", array());

function updateFailoverConfig($FH) {

    global $result;
    global $error;

    setFailoverConfig($FH->getPostValue("primaryIp"), $FH->getPostValue("secondaryIp"),
        $FH->getPostValue("primaryPort"), $FH->getPostValue("secondaryPort"), $FH->getPostValue("delay"),
        $FH->getPostValue("update"), $FH->getPostValue("balance"), $FH->getPostValue("mclt"),
        $FH->getPostValue("split"));
    if(!isXMLRPCError()) {
        $result .= _T("Failover configuration updated.") . "<br />";
        $result .= _T("You must restart DHCP services.") . "<br />";
    }
    else
        $error .= _T("Failed to update the failover configuration.") . "<br />";

}

// get current configuration
$failoverConfig = getFailoverConfig();
$FH->setArr($failoverConfig);
// default values
$show = false;
if (isset($failoverConfig['secondary'])) {
    $show = true;
}

if ($_POST) {
    $result = "";
    $error = "";

    if ($FH->isUpdated("dhcp_failover") and $FH->getValue("dhcp_failover") == "off") {
        delFailoverConfig();
        delSecondaryServer();
        if(!isXMLRPCError())
            $result .= _T("Failover configuration disabled.") . "<br />";
        else
            $error .= _T("Failed to disable the failover configuration.") . "<br />";
    }
    if ($FH->getPostValue("dhcp_failover") == "on") {
        if ($FH->isUpdated("secondary")) {
            updateSecondaryServer($FH->getValue("secondary"));
            if(!isXMLRPCError()) {
                $result .= _T(sprintf("%s set as the secondary DHCP server.", $FH->getValue("secondary"))) . "<br />";
                updateFailoverConfig($FH);
            }
            else
                $error .= _T(sprintf("Failed to set %s as the secondary DHCP server.", $FH->getValue("secondary"))) . "<br />";
        }
        else if ($FH->isUpdated("secondaryIp") or $FH->isUpdated("primaryIp") or $FH->isUpdated("primaryPort") or $FH->isUpdated("secondaryPort") or $FH->isUpdated("delay") or $FH->isUpdated("update") or $FH->isUpdated("balance") or $FH->isUpdated("mclt") or $FH->isUpdated("split")) {
            updateFailoverConfig($FH);
        }
    }

    // prepare the result popup
    $resultPopup = new NotifyWidget();
    // add error messages
    if ($error) {
        $resultPopup->add('<div class="alert alert-error">' . $error . '</div>');
        $resultPopup->setLevel(5);
    }
    // add info messages
    if ($result) {
        $services = getServicesNames();
        $resultPopup->add('<div class="alert alert-success">' . $result . '</div>');
        handleServicesModule($resultPopup, array($services[1] => "DHCP"));
    }

    if(!$error) {
        header("Location: " . urlStrRedirect("network/network/services"));
        exit;
    }
}



$f = new ValidatingForm();
$f->addValidateButton("bdhcpfailover");
$f->addCancelButton("breset");

$f->push(new Table());
$f->add(
    new TrFormElement(_T("Enable DHCP failover"), new CheckboxTpl("dhcp_failover")),
        array("value"=> $show ? "checked": "", "extraArg" => 'onclick="toggleVisibility(\'dhcpfailoverdiv\');"')
);
$f->pop();

$dhcpfailoverdiv = new Div(array("id" => "dhcpfailoverdiv"));
$dhcpfailoverdiv->setVisibility($show);

$f->push($dhcpfailoverdiv);
$f->push(new Table());
$f->add(
    new TrFormElement(_T("Primary DHCP server name"), new HiddenTpl("primary")),
    array("value" => $FH->getArrayOrPostValue("primary"))
);

$f->add(
    new TrFormElement(_T("Primary DHCP IP address"), new IPInputTpl("primaryIp")),
    array("value" => $FH->getArrayOrPostValue("primaryIp"), "required" => true)
);
$f->pop();

$f->push(new DivExpertMode());
$f->push(new Table());
$f->add(
    new TrFormElement(_T("Primary DHCP failover port"), new InputTpl("primaryPort"),
    array("tooltip" => _T("TCP port where the server listen to failover messages", "network"))),
    array("value" => $FH->getArrayOrPostValue("primaryPort"), "required" => true)
);
$f->pop();
$f->pop();

$f->push(new Table());
$f->add(
    new TrFormElement(_T("Secondary DHCP server name"), new InputTpl("secondary")),
    array("value" => $FH->getArrayOrPostValue("secondary"), "required" => true)
);

$f->add(
    new TrFormElement(_T("Secondary DHCP IP address"), new IPInputTpl("secondaryIp")),
    array("value" => $FH->getArrayOrPostValue("secondaryIp"), "required" => true)
);
$f->pop();

$f->push(new DivExpertMode());
$f->push(new Table());
$f->add(
    new TrFormElement(_T("Secondary DHCP failover port"), new InputTpl("secondaryPort"),
    array("tooltip" => _T("TCP port where the server listen to failover messages", "network"))),
    array("value" => $FH->getArrayOrPostValue("secondaryPort"), "required" => true)
);
$f->add(
    new TrFormElement(_T("Max response delay"), new InputTpl("delay"),
    array("tooltip" => _T("How many seconds may pass without receiving a message from its failover peer before it assumes that connection has failed"))),
    array("value" => $FH->getArrayOrPostValue("delay"), "required" => true)
);
$f->add(
    new TrFormElement(_T("Max unacked updates"), new InputTpl("update")),
    array("value" => $FH->getArrayOrPostValue("update"), "required" => true)
);
$f->add(
    new TrFormElement(_T("Max load balance time"), new InputTpl("balance")),
    array("value" => $FH->getArrayOrPostValue("balance"), "required" => true)
);
$f->add(
    new TrFormElement(_T("Maximum client lead time"), new InputTpl("mclt"),
    array("tooltip" => _T("Length of time for which a lease may be renewed by either failover peer without contacting the other"))),
    array("value" => $FH->getArrayOrPostValue("mclt"), "required" => true)
);
$f->add(
    new TrFormElement(_T("Split between the primary and secondary"), new InputTpl("split")),
    array("value" => $FH->getArrayOrPostValue("split"), "required" => true)
);
$f->pop();
$f->pop();

$f->display();

?>
