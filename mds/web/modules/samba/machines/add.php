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
require("modules/samba/includes/samba.inc.php");
require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

if (isset($_POST["baddmach"])) {
    $machine = $_POST["machine"];
    $comment = stripslashes($_POST["comment"]);

    add_machine($machine, $comment);
    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_T("Computer %s successfully added"), $machine));
        header("Location: " . urlStrRedirect("samba/machines/index"));
    }
}

$p = new PageGenerator(_T("Add a computer"));
$p->setSideMenu($sidemenu);
$p->display();

$f = new ValidatingForm();
$f->addSummary(_T("The computer name can only contains letters lowercase and numbers, and must begin with a letter."));
$f->push(new Table());
$f->add(
        new TrFormElement(_T("Computer name"), new NetbiosInputTpl("machine")),
        array("value" => $machine, "required" => True)
        );
$f->add(
        new TrFormElement(_T("Comment"), new InputTpl("comment")),
        array("value" => $comment)
        );
$f->pop();
$f->pop();
$f->addValidateButton("baddmach");
$f->display();

?>
