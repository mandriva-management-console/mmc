<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: deletehost.php 1224 2008-03-03 15:18:18Z cedric $
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

$zone = urldecode($_GET["zone"]);
$id = urldecode($_GET["id"]);
$serial = urldecode($_GET["serial"]);
$reverse = urldecode($_GET["reverse"]);

$curzone = $zone;
if ($reverse){
    $rzone = getReverseZone($zone);
    if (count($rzone))
            $curzone = $rzone[0];
}
                

$soa = getSOARecord($curzone);
if ($soa["serial"] != $serial){
    new NotifyWidgetFailure(_T("Zone data was updated. Selected record may be incorrect."));
    header("Location: main.php?module=network&submod=network&action=zonerecords&zone=$zone&reverse=$reverse");
}

if (isset($_POST["bconfirm"])) {
    delRecordById($curzone, $id);
    if (!isXMLRPCError()) 
	new NotifyWidgetSuccess(_T("The record has been deleted."));
    header("Location: main.php?module=network&submod=network&action=zonerecords&zone=$zone&reverse=$reverse");
} 


$record = getZoneRecordById($curzone, $id);
$hostname = $record["hostname"];
$type = $record["type"];
$f = new PopupForm(_T("Delete a DNS record"));
$f->addText(sprintf(_T("You will delete the %s record for %s host"), "<strong>$type</strong>" ,"<strong>$hostname</strong>"));
$f->addValidateButton("bconfirm");
$f->addCancelButton("bback");
$f->display();

?>
