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
        $result = array(
                        "start" => _T("The service has been asked to start"),
                        "stop" => _T("The service has been asked to stop"),
                        "reload" => _T("The service has been asked to reload")                        
                        );
        new NotifyWidgetSuccess($result[$command]);
    }
}

/* Set the available action items according to the service status */
$params = array();
$actionsStart = array();
$actionsStop = array();
$actionsReload = array();
$actionsLog = array();

$startAction = new ActionItem(_T("Start service"),"services","start","command=start&amp;service");
$stopAction = new ActionItem(_T("Stop service"),"services","stop","command=stop&amp;service");
$reloadAction = new ActionItem(_T("Reload service"),"services","reload","command=reload&amp;service");
$logAction = new ActionItem(_T("View log"),"services","afficher","command=logview&amp;service");
$emptyAction = new EmptyActionItem();

$status = array();

$actionsReload[] = $emptyAction;
$actionsLog[] = $logAction;
if (dhcpService("status")) {
    $status[] = _T("Started");
    $actionsStart[] = $emptyAction;
    $actionsStop[] = $stopAction;
} else {
    $status[] = _T("Stopped");
    $actionsStart[] = $startAction;
    $actionsStop[] = $emptyAction;
}

$actionsLog[] = $logAction;
$actionsReload[] = $reloadAction;
if (dnsService("status")) {
    $status[] = _T("Started");
    $actionsStart[] = $emptyAction;
    $actionsStop[] = $stopAction;
} else {
    $status[] = _T("Stopped");
    $actionsStart[] = $startAction;
    $actionsStop[] = $emptyAction;
}

$l = new ListInfos(array("DHCP", "DNS"), _T("Services"));
$l->setName(_T("Network services status"));
$l->addExtraInfo($status, _T("Status"));
$l->setParamInfo(array());
$l->setTableHeaderPadding(1);
$l->disableFirstColumnActionLink();

$l->addActionItemArray($actionsStart);
$l->addActionItemArray($actionsStop);
$l->addActionItemArray($actionsReload);
$l->addActionItemArray($actionsLog);

$l->display(0);

?>
