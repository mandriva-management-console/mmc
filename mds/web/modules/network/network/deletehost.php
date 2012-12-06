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

if (isset($_POST["bconfirm"])) {
    $host = $_POST["host"];
    $zone = $_POST["zone"];
    delRecord($zone, $host);
    if (!isXMLRPCError()) new NotifyWidgetSuccess(_T("The record has been deleted."));
    header("Location: main.php?module=network&submod=network&action=zonemembers&zone=$zone");
    exit;
} else {
    $host = urldecode($_GET["host"]);
    $zone = urldecode($_GET["zone"]);
    $rr = getResourceRecord($zone, $host);
    $f = new PopupForm(_T("Delete a DNS record"));
    $f->addText(sprintf(_T("You will delete the %s record"), "<strong>$host</strong>"));
    /* If the deleted record is a A record, CNAME may be linked to it */
    if (isset($rr[0][1]["aRecord"])) {
        $cnames = getCNAMEs($zone, $host);
        if (!empty($cnames)) {
            $msg = _T("The linked CNAME records will also be deleted:");
            foreach($cnames as $cname) {
                $msg .= " <strong>" . $cname[1]["relativeDomainName"][0] . "</strong>";
            }
            $f->addText($msg);
        }
    }
    $hidden1 = new HiddenTpl("host");
    $hidden2 = new HiddenTpl("zone");
    $f->add($hidden1, array("value" => $host, "hide" => True));
    $f->add($hidden2, array("value" => $zone, "hide" => True));
    $f->addValidateButton("bconfirm");
    $f->addCancelButton("bback");
    $f->display();
}
?>
