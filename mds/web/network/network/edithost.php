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

if ($_GET["action"] == "addhost") $title =  _T("Add a host");
else $title =  _T("Edit host");;

$p = new PageGenerator($title);
$sidemenu->forceActiveItem("index");
$p->setSideMenu($sidemenu);
$p->display();

$zone = $_GET["zone"];
global $error;
if (isset($_POST["badd"])) {
    $hostname = $_POST["hostname"];
    $ipaddress = $_POST["ipaddress"];
    
    /* Basic checks */
    if (hostExists($zone, $hostname)) {
        $error = _T("The specified hostname has been already recorded in this zone.") . " ";
        setFormError("hostname");
        $hostname = "";
    }
    if (ipExists($zone, $ipaddress)) {
        $error .= _T("The specified IP address has been already recorded in this zone.");
        setFormError("address");
    } else $keepaddress = True;
    
    if (!isset($error)) {
        $ret = addRecordA($zone, $hostname, $ipaddress);
        if (!isXMLRPCError()) {
            if ($ret === 1) $msg = _T("Host successfully added to DNS zone.");
            else $msg = _T("Host successfully added to DNS zone and corresponding reverse zone.");
            new NotifyWidgetSuccess($msg);
            if (isset($_GET["gobackto"]))
                header("Location: " . $_SERVER["PHP_SELF"] . "?" . rawurldecode($_GET["gobackto"]));
            else
                header("Location: " . urlStrRedirect("network/network/zonemembers", array("zone" => $zone)));
        }
    } else new NotifyWidgetFailure($error);

}

if ($_GET["action"] == "edit") {
    $hostname = $_GET["host"];
}

$f = new ValidatingForm();
$f->push(new Table());

$zoneaddress = getZoneNetworkAddress($zone);
if (count($zoneaddress)) $f->add(new TrFormElement(_T("A reverse DNS record will be automatically created for this host."), new HiddenTpl("")));

$a = array("value" => $hostname, "extra" => "." . $zone);
if ($_GET["action"] == "addhost") {
    $formElt = new HostnameInputTpl("hostname");
    $a["required"] = True;    
    if (isset($_GET["host"])) $a["value"] = $_GET["host"]; /* pre-fill hostname field when adding a host */
} else {
    $formElt = new HiddenTpl("hostname");
}

$f->add(new TrFormElement(_T("Host name"), $formElt), $a);

if ($_GET["action"] == "addhost") {
    if (isset($_GET["ipaddress"])) $network = $_GET["ipaddress"]; /* pre-fill IP address field when adding a host */
    else {
        if (isset($error) && isset($keepaddress))
            $network = $ipaddress;
        else {            
            if (!count($zoneaddress)) $network = "";
            else $network = getZoneFreeIp($zone);
            
        }
    }
    $a = array("value"=>$network, "required" => True);
} else {
    $a = array("value"=>$ipaddress, "required" => True);
}
$a["zone"] = $zone;
$a["ajaxurl"] = "ajaxDnsGetZoneFreeIp";
$f->add(new TrFormElement(_T("Network address"), new GetFreeIPInputTpl()), $a);
$f->pop();

if ($_GET["action"] == "addhost") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addButton("badd", _("Confirm"));
}
$f->display();

?>
