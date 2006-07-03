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
?>
<?php
/* $Id$ */

function
add_share($error, $name, $comment, $group, $permAll, $av =0)
{
  global $conf;
  $smbconf = $conf["global"]["smbconf"];

  $name = trim($name);

  $reserved = array("homes", "print$", "printers");

  foreach ($reserved as $res)
    {
      if ($name == $res)
	{
	  $error = "$name est un nom réservé";
	  return;
	}
    }

 $param = array($name, $comment, $group, $permAll,$av);
  return xmlCall("samba.addShare", $param);
}

function
get_shares()
{
  $shares = xmlCall("samba.getDetailedShares",null);
  foreach ($shares as $key=>$value)
      $resArray[]=$key;
  return $resArray;
}

function hasClamAv() {
    return xmlCall('samba.isSmbAntiVirus',null);
}

function getACLOnShare($name) {
    return xmlCall('samba.getACLOnShare',array($name));
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

function del_share($error, $share, $files)
{
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

function
mod_share($error, $share, $comment, $group, $permAll,$av=0)
{
  del_share($errdel, $share, false);

  if (isset($errdel))
    {
      $error = $errdel;
      return;
    }

  add_share($erradd, $share, $comment, $group, $permAll, $av);

  if (isset($erradd))
    {
      $error = $erradd;
      return;
    }

  return "Partage $share modifié";
}

?>
