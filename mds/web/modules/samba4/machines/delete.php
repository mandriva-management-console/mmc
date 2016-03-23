<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id$
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

require("modules/samba4/includes/machines-xmlrpc.inc.php");

if (isset($_GET["machine"])) {
  $machine = urldecode($_GET["machine"]);
}
if (isset($_POST["machine"])) {
  $machine = $_POST["machine"];
}

if (isset($_POST["bdeleletemachine"])) {
    $deleteActionSuccess = deleteMachine($machine);

    if (!isXMLRPCError() and $deleteActionSuccess) {
        $computerDeletedMessage = sprintf(_T("Computer <strong>%s</strong> deleted.", "samba4"), $machine);
        new NotifyWidgetSuccess($computerDeletedMessage);
    } else {
        $computerDeletedMessage = sprintf(_T("There has been a problem while deleting <strong>%s</strong> computer.", "samba4"), $machine);
        new NotifyWidgetFailure($computerDeletedMessage);
    }

    header("location: " . urlStrRedirect('samba4/machines/index'));
    exit;
} else {
    $form = new PopupForm(_T("Delete a computer", "samba4"));
    $form->addText(sprintf(_T("You will delete the %s computer", "samba4"), "<strong>$machine</strong>"));
    $form->addValidateButton("bdeleletemachine");
    $form->addCancelButton("bback");
    $form->display();
}
