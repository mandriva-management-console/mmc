<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007 Mandriva, http://www.mandriva.com/
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

function add_share($name, $comment, $usergroups, $permAll, $admingroups, $browseable, $av = 0)
{
    $name = trim($name);
    # FIXME !
    $reserved = array("homes", "print$", "printers");
    foreach ($reserved as $res) {
        if ($name == $res) {
	    $error = "$name est un nom réservé";
	    return;
	}
    }
    $param = array($name, $comment, $usergroups, $permAll, $admingroups, $browseable, $av);
    return xmlCall("samba.addShare", $param);
}

function get_shares()
{
    $shares = xmlCall("samba.getDetailedShares", null);
    foreach ($shares as $key=>$value) $resArray[]=$key;
    return $resArray;
}

function hasClamAv() {
    return xmlCall('samba.isSmbAntiVirus',null);
}

function getACLOnShare($name) {
    return xmlCall('samba.getACLOnShare',array($name));
}

function getAdminUsersOnShare($name) {
    return xmlCall('samba.getAdminUsersOnShare', array($name));
}


function
get_shares_detailed()
{
  $shares = xmlCall("samba.getDetailedShares",null);
  return $shares;
}

// recuperation chemin d'un partage
function share_path($share, $error)
{

  $param = array($share);
  $shares = xmlCall("samba.getSharePath",$param);
}

function del_share($share, $files) {
  $param = array($share, $files);
  return xmlCall("samba.delShare", $param);
}

function share_infos($error, $share)
{
  $param = array($share);
  $result=xmlCall("samba.shareInfo",$param);
  if ($result==-1) { $error="erreur dans la récupération des données"; return; }
  return $result;
}

function mod_share($name, $comment, $usergroups, $permAll, $admingroups, $browseable, $av = 0)
{
    # FIXME
    del_share($errdel, $name, false);
    if (isset($errdel)) {
        $error = $errdel;
	return;
    }
    add_share($name, $comment, $usergroups, $permAll, $admingroups, $browseable, $av);
    if (isset($erradd))
    {
        $error = $erradd;
        return;
    }
    return "Partage $share modifié";
}

function sched_backup($share, $media) {
    $param = array($share, $media, $_SESSION["login"]);
    xmlCall("samba.backupShare", $param);
}

function getDomainAdminsGroup() {
    return xmlCall("samba.getDomainAdminsGroup", null);
}

?>
