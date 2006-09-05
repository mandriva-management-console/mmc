<?php
/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
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

require("graph/navbar.inc.php");

require_once('modules/samba/includes/user-xmlrpc.inc.php');
?>

<h2><?= _T("Samba status","samba"); ?></h2>

<div class="fixheight"></div>

<?
    require("includes/statusSidebar.inc.php");

    $pid = array();
    $user = array();
    $machine = array();
    $ip =array();

    foreach (getConnected() as $item) {
        $pid[] = $item['pid'];
        $user[] = $item['useruid'];
        $machine[] = $item['machine'];
        $ip[] = $item['ip'];
    }
    ?>
    <h3><?= _T("Opened sessions","samba"); ?>(<?= count($user); ?>):</h3>
    <?

    if (count($user)!=0) {

        print '<div style="background-color: #EEE; -moz-border-radius: 0.5em; margin: 0.5em;
            padding:0.5em;">';

        $n = new ListInfos($user,_T("Users","samba"));
        $n->addExtraInfo($machine,_T("Computers","samba"),"33%");
        $n->addExtraInfo($ip,_T("IP","samba"),"33%");
        $n->display(0,0);

        print '</div>';
    } else {
        print '<p>'._T('No opened sessions','samba').'</p>';
    }

    $status = getSmbStatus();
    ?>
    <h3><?= _T("Connections on shares","samba"); ?>(<?= count($status); ?>):</h3>
    <?

    if (count($status)!=0) {
        foreach ($status as $sharename => $connects) {
                $link = '<a href="'.urlStr('samba/shares/details',array('share'=>$sharename)).'">'.$sharename.'</a>';
                print '<div style="background-color: #EEE; -moz-border-radius: 0.5em; margin: 0.5em; padding:0.5em;">';            print "<h3>"._T("Share","samba")." $link(".count($connects)."):</h3>";
                print '<div style="background-color: #E5E5E5; -moz-border-radius: 0.5em; margin: 0.5em; padding:0.5em;">';
                $user = array();
                $machine = array();
                $ip =array();

                $timestamp = array();
                foreach ($connects as $connect) {
                    $user[] = $connect['useruid'];
                    $machine[] = $connect['machine'];
                    if ($connect['ip']) {
                        $ip[] = $connect['ip'];
                    } else {
                        $ip[] = "n/a";
                    }

                    $timestamp[] = strftime("%a, %d %b %Y %H:%M:%S",$connect['lastConnect']);

                }
                /**
                    * Creation de la liste
                    */
                    $n = new ListInfos($user,_T("User","samba"));
                    $n->addExtraInfo($machine,_T("Computers","samba"),"25%");
                    $n->addExtraInfo($ip,_T("IP","samba"),"25%");
                    $n->addExtraInfo($timestamp,_T("Connected at","samba"),"25%");
                    $n->display(0,0);
                print "</div>";
                print '</div>';
        }
    } else {
        print '<p>'._T('No connections','samba').'</p>';
    }

?>


