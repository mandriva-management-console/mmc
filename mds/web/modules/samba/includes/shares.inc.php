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
?>
<?php
/* $Id$ */

// $name, $path, $comment, $usergroups, $permAll, $admingroups, $browseable, $av, $customParameters
function add_share($params) {
    $name = trim($params[0]);
    # FIXME !
    $reserved = array("homes", "print$", "printers");
    foreach ($reserved as $res) {
        if ($name == $res) {
	        return;
    	}
    }
    xmlCall("samba.addShare", $params);
}

// $name, $path, $comment, $usergroups, $permAll, $admingroups, $browseable, $av, $customParameters
function mod_share($params) {
    return xmlCall("samba.modShare", $params);
}

function get_shares() {
    $shares = xmlCall("samba.getDetailedShares", null);
    foreach ($shares as $key=>$value) $resArray[]=$key;
    return $resArray;
}

function hasAv() {
    return xmlCall('samba.isSmbAntiVirus',null);
}

function getACLOnShare($name) {
    return xmlCall('samba.getACLOnShare',array($name));
}

function getAdminUsersOnShare($name) {
    return xmlCall('samba.getAdminUsersOnShare', array($name));
}


function get_shares_detailed() {
    $shares = xmlCall("samba.getDetailedShares", null);
    return $shares;
}

/* Get share path */
function share_path($share, $error) {
    $param = array($share);
    $shares = xmlCall("samba.getSharePath", $param);
}

function default_shares_path() {
    return xmlCall("samba.getDefaultSharesPath");
}

function del_share($share, $files) {
    $param = array($share, $files);
    return xmlCall("samba.delShare", $param);
}

function share_infos($share) {
    return xmlCall("samba.shareInfo", array($share));
}

function share_custom_parameters($share) {
    return xmlCall("samba.shareCustomParameters", array($share));
}

function sched_backup($share, $media) {
    $param = array($share, $media, $_SESSION["login"]);
    return xmlCall("samba.backupShare", $param);
}

function getDomainAdminsGroup() {
    return xmlCall("samba.getDomainAdminsGroup", null);
}

function isAuthorizedSharePath($path) {
    return xmlCall("samba.isAuthorizedSharePath", $path);
}

?>
