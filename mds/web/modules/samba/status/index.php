<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2014 Mandriva, http://www.mandriva.com/
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

require("graph/navbar.inc.php");
require_once('modules/samba/includes/user-xmlrpc.inc.php');

if (in_array("dashboard", $_SESSION["supportModList"])) {
    require("modules/samba/mainSidebar.php");
    $p = new PageGenerator();
    $p->setSideMenu($sidemenu); //$sidemenu inclus dans localSideBar.php
    $p->displaySideMenu();
}
else
    require("includes/statusSidebar.inc.php");

?>
<h2><?php echo  _T("Samba status","samba"); ?></h2>

<div class="fixheight"></div>

<?php
$pid = array();
$user = array();
$machine = array();
$ip =array();

$connections = array();
foreach (getConnected() as $item) {
    $connections[$item['useruid']][] = array($item['pid'], $item['machine'], $item['ip']);
}
ksort($connections);
foreach($connections as $uid => $infos) {
    foreach($infos as $info) {
        $user[] = $uid;
        $pid[] = $info[0];
        $machine[] = $info[1];
        $ip[] = $info[2];
    }
}
?>
    <h2><?php echo  _T("Opened sessions","samba"); ?>(<?= count($user); ?>):</h2>
<?php

if (count($user)!=0) {
    $n = new ListInfos($user,_T("Users","samba"));
    $n->addExtraInfo($machine,_T("Computers","samba"),"33%");
    $n->addExtraInfo($ip,_T("IP","samba"),"33%");
    $n->end = 1000; /* FIXME ! */
    $n->display(0,0);
} else {
    print '<p>'._T('No opened sessions','samba').'</p>';
}

$status = getSmbStatus();
?>
    <br/>
    <h2><?php echo  _T("Connections on shares","samba"); ?>(<?= count($status); ?>):</h2>
<?php

if (count($status)!=0) {
    foreach ($status as $sharename => $connects) {
        if (!in_array($sharename, array("homes", "IPC$", "netlogon", "archives")))
            $link = '<a href="'.urlStr('samba/shares/details',array('share'=>$sharename)).'">'.$sharename.'</a>';
        else
            $link = $sharename;
        print "<h3>"._T("Share","samba")." $link(".count($connects)."):</h3>";
        $connections = array();
        foreach ($connects as $connect) {
            if ($connect['ip']) {
                $cip = $connect['ip'];
            } else {
                $cip = "n/a";
            }
            $tstamp = strftime("%a, %d %b %Y %H:%M:%S",$connect['lastConnect']);
            $connections[$connect['useruid']][] = array($connect['machine'], $cip, $tstamp);
        }
        ksort($connections);
        $user = array();
        $machine = array();
        $ip = array();
        $timestamp = array();
        foreach($connections as $uid => $infos) {
            foreach($infos as $info) {
                $user[] = $uid;
                $machine[] = $info[0];
                $ip[] = $info[1];
                $timestamp[] = $info[2];
            }
        }

        /**
         * Creation de la liste
         */
        $n = new ListInfos($user,_T("User","samba"));
        $n->addExtraInfo($machine,_T("Computers","samba"),"25%");
        $n->addExtraInfo($ip,_T("IP","samba"),"25%");
        $n->addExtraInfo($timestamp,_T("Connected at","samba"),"25%");
        $n->end = 1000;
        $n->display(0,0);
    }
} else {
    print '<p>'._T('No connections','samba').'</p>';
}

?>


