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

require("modules/samba/includes/shares.inc.php");

if (isset($_POST["bgo"])) {
    $share = $_POST["share"];
    $backuppath = sched_backup($share, $_POST["media"]);
    if (!isXMLRPCError()) {
        $str = "<h2>"._T("Share backup")."</h2>";
        $str .= '<p>';
        $str .= sprintf(_T("Backup of share <b>%s</b> has been launched in background."), $share);
        $str .= "</p><p>";
        $str .= sprintf(_("The files will be stored in the directory %s of the server at the end of the backup."), $backuppath);
        $str .= "</p><p>";
        $str .= _T("Please go to the status page to check the backup status.");
        $str .= "</p><p>";
        $str .= _T("This operation will last according to the amount of data to backup.");
        $str .= "</p>";
        new NotifyWidgetSuccess($str);
    } else {
        new NotifyWidgetFailure(_T("Can't launch backup"));
    }
    header("Location: ".urlStrRedirect("samba/shares/index"));
    exit;
} else {
    $share = urldecode($_GET["share"]);
    $f = new PopupForm(_T("Share backup"));
    $f->addText(sprintf(_T("The share %s will be archived."), $share));
    $f->addText(_T("Please select media size. If your data exceed volume size, several files with your media size will be created."));
    $select = new SelectItem("media");
    $select->setElements(array("CD (650 Mo)", "DVD (4.7 Go)"));
    $select->setElementsVal(array(600, 4200));
    $f->add($select);
    $hidden = new HiddenTpl("share");
    $f->add($hidden, array("value" => $share, "hide" => True));
    $f->addValidateButton("bgo");
    $f->addCancelButton("bback");
    $f->display();
}
?>
