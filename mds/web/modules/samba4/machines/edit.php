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

require("modules/samba4/includes/machines-xmlrpc.inc.php");
require("modules/samba4/machines/machinesSidebar.php");
require("graph/navbar.inc.php");

require_once("includes/FormHandler.php");

/* Displaying the page title */
$page = new PageGenerator(_T("Edit computer", "samba4"));
$sidemenu->forceActiveItem("index");
$page->setSideMenu($sidemenu);
$page->display();

/* Getting the computer name from the GET request */
if(!isset($_GET["machine"])) {
    $name = False;
}
else {
    $name = $_GET["machine"];
}

/* If the edit form has been submited */
if(isset($_POST["bmachineedit"])) {
    list($name, $description, $enabled) = _parseEditMachineForm();

    $editSuccess = editMachine($name, array($name, $description, $enabled));

    if (!isXMLRPCError() and $editSuccess) {
        new NotifyWidgetSuccess(_T("Computer successfully modified."));
        header("Location: " . urlStrRedirect("samba4/machines/index"));
        exit;
    }
}

/* Showing the edit machine details form */
if($name) {
    $machineDetails = getMachine($name);

    $form = new ValidatingForm();
    $form->push(new Table());

    $tr = new TrFormElement(_T("Computer name", "samba4"), new HiddenTpl("name"));
    $form->add($tr, array("value" => $machineDetails["name"], "required" => True));

    $tr = new TrFormElement(_T("Computer description", "samba4"), new InputTpl("description"));
    $form->add($tr, array("value" => $machineDetails["description"], "required" => True));

    $tr = new TrFormElement(_T("Computer account is enabled", "samba4"), new CheckboxTpl("enabled"),
                                array("tooltip" => _T("If checked, the computer account is enabled", "samba4")));
    $form->add($tr, array("value" => $machineDetails["enabled"] ? "checked" : ""));

    $form->addButton("bmachineedit", _("Confirm"));

    $form->display();
}

/* Private functions */
function _parseEditMachineForm() {
    $FH = new FormHandler("editMachineFH", $_POST);

    $name = $FH->getPostValue("name");
    $description = $FH->getPostValue("description");
    $enabled = ($FH->getPostValue("shareEnabled") == "on") ? True : "";

    return array($name, $description, $enabled);
}
