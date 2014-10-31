<?php
/**
 * (c) 2014 Mandriva, http://www.mandriva.com/
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
 *
 * Author(s):
 *   Miguel Julián <mjulian@zentyal.com>
 */

require("modules/samba4/includes/shares-xmlrpc.inc.php");

if (isset($_POST["bdeleteshare"])) {
    $share = $_POST["share"];

    $deleteFiles = (isset($_POST["deleteFiles"])) ? True : False;
    $deletionSuccess = deleteShare($share, $deleteFiles);

    if (!isXMLRPCError() and $deletionSuccess) {
        $successMessage = sprintf(_T("Share %s deleted"), $share);
        new NotifyWidgetSuccess($successMessage);
    } else {
        $failureMessage = sprintf(_T("An error has occured during delete process on %s"), $share);
        new NotifyWidgetFailure($failureMessage);
    }

    redirectTo(urlStrRedirect('samba4/shares/index'));
    exit;
} else {
    $share = urldecode($_GET["share"]);

    $form = new PopupForm(_T("Delete a share"));
    $form->addText(sprintf(_T("You will delete the share <b>%s</b>"), $share));

    $form->push(new Table());

    $tr = new TrFormElement(_T("Delete data"), new CheckboxTpl("deleteFiles"));
    $form->add($tr, array("value" => ""));

    $form->pop();

    $hidden = new HiddenTpl("share");
    $form->add($hidden, array("value" => $share, "hide" => True));

    $form->addValidateButton("bdeleteshare");
    $form->addCancelButton("bback");

    $form->display();
}
?>
