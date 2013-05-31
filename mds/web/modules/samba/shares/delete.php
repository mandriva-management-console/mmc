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

require("modules/samba/includes/shares.inc.php");

if (isset($_POST["bdelshare"])) {
    $share = $_POST["share"];
    del_share($share, $_POST["delFiles"]);
    if (!isXMLRPCError()) {
        $str = sprintf(_T("Share %s deleted"), $share);
        new NotifyWidgetSuccess($str);
    } else {
        $str = sprintf(_T("An error has occured during delete process on %s", $share));
        new NotifyWidgetFailure($str);
    }
    redirectTo(urlStrRedirect('samba/shares/index'));
} else {
    $share = urldecode($_GET["share"]);
    $f = new PopupForm(_T("Delete a share"));
    $f->addText(sprintf(_T("You will delete the share <b>%s</b>"), $share));
    $cb = new CheckboxTpl("delFiles", _T("Delete all data"));
    $f->add($cb, array("value" => ""));
    $hidden = new HiddenTpl("share");
    $f->add($hidden, array("value" => $share, "hide" => True));
    $f->addValidateButton("bdelshare");
    $f->addCancelButton("bback");
    $f->display();
}
?>
