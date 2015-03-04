<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2015 Mandriva, http://www.mandriva.com
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

require("modules/base/includes/users.inc.php");

if (isset($_GET["user"])) $user = urldecode($_GET["user"]);
if (isset($_POST["user"])) $user = $_POST["user"];

if (isset($_POST["bdeluser"])) {
    $delfiles = false;
    if (isset($_POST["delfiles"]) && $_POST["delfiles"] == "on")
        $delfiles = true;
    del_user($user, $delfiles);
    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_("User %s has been successfully deleted"), $user));
    }
    redirectTo(urlStrRedirect("base/users/index"));
}
else {
    $f = new PopupForm(_("Delete user"));
    $f->addText(sprintf(_("You will delete user <b>%s</b>."), $user));
    $f->add(
        new CheckboxTpl("delfiles", _("Delete all user's files (home directory, mails...)")),
        array("value" => "")
    );
    $f->add(
        new HiddenTpl("user"),
        array("value" => $user, "hide" => True)
    );
    $f->addValidateButton("bdeluser");
    $f->addCancelButton("bback");
    $f->display();
}

?>
