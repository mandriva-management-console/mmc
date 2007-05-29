<?

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
    $address = $_POST["address"];
    
    /* Basic checks */
    if (hostExists($zone, $hostname)) {
        $error = _T("The specified hostname has been already recorded in this zone.") . " ";
        setFormError("hostname");
        $hostname = "";
    }
    if (ipExists($zone, $address)) {
        $error .= _T("The specified IP address has been already recorded in this zone.");
        setFormError("address");
    } else $keepaddress = True;
    
    if (!isset($error)) {
        addRecordA($zone, $hostname, $address);
        if (!isXMLRPCError()) {
            new NotifyWidgetSuccess(_T("Host successfully added."));
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
            $network = $address;
        else {
            $zoneaddress = getZoneNetworkAddress($zone);
            if (!count($zoneaddress)) $network = "";
            else $network = $zoneaddress[0] . ".";
        }
    }
    $a = array("value"=>$network, "required" => True);
} else {
    $a = array("value"=>$address, "required" => True);
}
$f->add(new TrFormElement(_T("Network address"), new IPInputTpl("address")), $a);
$f->pop();

if ($_GET["action"] == "addhost") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addButton("badd", _("Confirm"));
}
$f->display();

?>