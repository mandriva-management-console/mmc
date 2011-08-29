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

require("modules/network/includes/network-xmlrpc.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

$p = new PageGenerator(_T("Network services management"));
$p->setSideMenu($sidemenu);
$p->display();

/* Set the available action items according to the service status */
$params = array();
$actionsStart = array();
$actionsStop = array();
$actionsReload = array();
$actionsRestart = array();
$actionsLog = array();

$startAction = new ActionItem(_T("Start service"),"servicestart","start", "");
$stopAction = new ActionItem(_T("Stop service"),"servicestop","stop","");
$reloadAction = new ActionItem(_T("Reload service"),"servicereload","reload","");
$restartAction = new ActionItem(_T("Restart service"),"servicerestart","restart","");
$logAction = new ActionItem(_T("View log"),"servicelog","display","");
$emptyAction = new EmptyActionItem();

$status = array();

$actionsReload[] = $emptyAction;
$actionsLog[] = $logAction;
if (dhcpService("status")) {
    $status[] = _T("Started");
    $actionsStart[] = $emptyAction;
    $actionsStop[] = $stopAction;
    $actionsRestart[] = $restartAction;
} else {
    $status[] = _T("Stopped");
    $actionsStart[] = $startAction;
    $actionsStop[] = $emptyAction;
    $actionsRestart[] = $emptyAction;
}

$actionsLog[] = $logAction;
$actionsReload[] = $reloadAction;
if (dnsService("status")) {
    $status[] = _T("Started");
    $actionsStart[] = $emptyAction;
    $actionsStop[] = $stopAction;
    $actionsRestart[] = $restartAction;
} else {
    $status[] = _T("Stopped");
    $actionsStart[] = $startAction;
    $actionsStop[] = $emptyAction;
    $actionsRestart[] = $emptyAction;
}

$l = new ListInfos(array("DHCP", "DNS"), _T("Services"));
$l->setName(_T("Network services status"));
$l->addExtraInfo($status, _T("Status"));
$l->setParamInfo(array(array("service" => "DHCP"), array("service" => "DNS")));
$l->setTableHeaderPadding(1);
$l->disableFirstColumnActionLink();

$l->addActionItemArray($actionsStart);
$l->addActionItemArray($actionsStop);
$l->addActionItemArray($actionsRestart);
$l->addActionItemArray($actionsReload);
$l->addActionItemArray($actionsLog);

$l->display(0);

include("servicedhcpfailover.php");

?>
