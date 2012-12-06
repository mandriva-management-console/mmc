<?
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

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

$zone = $_GET["zone"];

if ($_GET["action"] == "addhost") $title =  sprintf(_T("Add a host to zone %s"), $zone);
else $title =  _T("Edit host");;

$p = new PageGenerator($title);
$sidemenu->forceActiveItem("index");
$p->setSideMenu($sidemenu);
$p->display();

global $error;
/* Adding a new record */
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
            exit;
        }
    } else new NotifyWidgetFailure($error);
}

/* Editing a record */
if (isset($_POST["bedit"])) {
    $aliases = $_POST["hostalias"];

    $ret = setHostAliases($zone, $_POST["hostname"], $aliases);
    if (!isXMLRPCError()) {
        if (empty($ret)) {
            new NotifyWidgetSuccess(_T("DNS record successfully modified."));
            header("Location: " . urlStrRedirect("network/network/zonemembers", array("zone" => $zone)));
            exit;
        }else {
            $msg = _T("The following aliases have not been set because a DNS record with the same name already exists:");
            foreach($ret as $alias)
                $msg .= " $alias";
            new NotifyWidgetFailure($msg);
        }
    }
}


if ($_GET["action"] == "edithost") {
    $hostname = $_GET["host"];
    $hostname = str_replace('.' . $zone, '', $hostname);
    $data = getResourceRecord($zone, $hostname);
    if (empty($data)) die("Record $hostname does not exist.");
    else if (isset($data[0][1]["aRecord"])) {
        $ipaddress = $data[0][1]["aRecord"][0];
        /* Lookup host alias */
        $cnames = array();
        foreach(getCNAMEs($zone, $hostname) as $dn => $cname) {
            if (in_array("associatedDomain",array_keys($cname[1]))) {
                $cnames[] = str_replace('.' . $zone, '', $cname[1]["associatedDomain"][0]);
            } else {
                $cnames[] = $cname[1]["relativeDomainName"][0];
            }
        }
    } else {
        die("Only A record edition is supported.");
    }

}

$f = new ValidatingForm();
$f->push(new Table());

/* Prepare hostname input field content */
if ($_GET["action"] == "addhost") {
    $hostname = "";
    $zoneaddress = getZoneNetworkAddress($zone);
    if (count($zoneaddress)) $f->add(new TrFormElement(_T("A reverse DNS record will be automatically created for this host."), new HiddenTpl("")));
}

$a = array("value" => $hostname, "extra" => "." . $zone);
if ($_GET["action"] == "addhost") {
    $formElt = new HostnameInputTpl("hostname");
    $a["required"] = True;
    if (isset($_GET["host"])) $a["value"] = $_GET["host"]; /* pre-fill hostname field when adding a host */
} else {
    $formElt = new HiddenTpl("hostname");
}

$f->add(new TrFormElement(_T("Host name"), $formElt), $a);

/* Prepare IP address input field content */
if ($_GET["action"] == "addhost" && count($zoneaddress) > 0) {
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
    $a["zone"] = $zone;
    $a["ajaxurl"] = "ajaxDnsGetZoneFreeIp";
    $formElt = new GetFreeIPInputTpl();
}
else if ($_GET["action"] == "addhost") {
    $a = array("value" => "", "required" => True);
    $formElt = new IPInputTpl("ipaddress");
}
else {
    $a = array("value"=>$ipaddress);
    $formElt = new HiddenTpl("ipaddress");
}
$f->add(new TrFormElement(_T("Network address"), $formElt), $a);
$f->pop();

if ($_GET["action"] == "addhost") {
    $f->addButton("badd", _("Create"));
} else {
    /* On edit mode, the user can setup host aliases */
    $m = new MultipleInputTpl("hostalias",_T("Hostname alias"));
    $m->setRegexp('/^[a-z][a-z0-9-]*[a-z0-9]$/');
    if (empty($cnames)) $cnames = array("");
    $f->add(
            new FormElement(_T("Hostname alias"), $m),
            $cnames
            );
    $f->addValidateButton("bedit");
}
$f->display();

?>
