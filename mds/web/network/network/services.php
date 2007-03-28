<?
require("modules/network/includes/network-xmlrpc.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

?>

<h2><?= _T("Network services management"); ?></h2>

<div class="fixheight"></div>

<?

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

if (isset($_GET["command"]) && isset($_GET["service"])) {
    $command = $_GET["command"];
    $service = $_GET["service"];
    if (in_array($command, array("start", "stop", "reload"))) {
        if ($service == "DNS") dnsService($command);
        else if ($service == "DHCP") dhcpService($command);
    } else if ($command == "logview") {
        /* Redirect to corresponding log page */
        if ($service == "DHCP") header("Location: " . urlStrRedirect("base/logview/dhcpindex"));
        else if ($service == "DNS") header("Location: " . urlStrRedirect("base/logview/dnsindex"));
        exit;
    }
    if (!isXMLRPCError()) {
        $result = _T("The service has been asked to") . "&nbsp;" . $command . ".";
        $n = new NotifyWidget();
	$n->flush();
	$n->add("<div id=\"validCode\">$result</div>");
	$n->setLevel(0);
	$n->setSize(600);
    }
}

$status = array();
if (dhcpService("status")) $status[] = _T("Started");
else $status[] = _T("Stopped");
if (dnsService("status")) $status[] = _T("Started");
else $status[] = _T("Stopped");

$l = new ListInfos(array("DHCP", "DNS"), _T("Services"));
$l->setName(_T("Network services status"));
$l->addExtraInfo($status, _T("Status"));
$l->setParamInfo(array());
$l->setTableHeaderPadding(1);
$l->disableFirstColumnActionLink();
$l->addActionItem(new ActionItem(_T("Start", "network"),"services&amp;command=start","start","service"));
$l->addActionItem(new ActionItem(_T("Stop", "network"),"services&amp;command=stop","stop","service"));
$l->addActionItem(new ActionItem(_T("Reload", "network"),"services&amp;command=reload","reload","service"));
$l->addActionItem(new ActionItem(_T("View log", "network"),"services&amp;command=logview","afficher","service"));

$l->display(0);

?>
