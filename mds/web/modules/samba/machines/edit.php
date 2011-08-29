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

require("modules/samba/includes/machines.inc.php");
require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

$p = new PageGenerator(_T("Edit computer", "samba"));
$sidemenu->forceActiveItem("index");
$p->setSideMenu($sidemenu);
$p->display();

if(!isset($_GET["machine"])) {
    $name = false;
}
else {
    $name = $_GET["machine"];
}

if(isset($_POST['cn'])) {
    $name = strtolower(substr($_POST['cn'], 0, -1));
    $displayName = $_POST['displayName'];
    $disable = false;
    if(isset($_POST['disable']) && $_POST['disable'] == "on")
        $disable = true;
    $params = array("displayName" => $displayName, "disable" => $disable);
    change_machine($name, $params);
    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(_T("Computer successfully modified."));
        header("Location: " . urlStrRedirect("samba/machines/index"));
    }
}

if($name) {
    $ldapArr = get_machine($name);

    $f = new ValidatingForm();
    $f->push(new Table());

    $f->add(
            new TrFormElement(_T("Name"), new HiddenTpl("cn")),
            array("value" => $ldapArr["cn"][0], "required" => true)
    );

    $f->add(
            new TrFormElement(_T("Comment"), new InputTpl("displayName")),
            array("value" => $ldapArr["displayName"][0])
    );

    if (strpos($ldapArr["sambaAcctFlags"][0], "D")) {
        $value = "checked";
    }
    else
        $value = "";
    $f->add(
            new TrFormElement(_T("Disable computer account"), new CheckBoxTpl("disable")),
            array("value" => $value)
    );

    $f->addButton("bedit", _("Confirm"));

    $f->display();
}
