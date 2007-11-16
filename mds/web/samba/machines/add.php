<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007 Mandriva, http://www.mandriva.com/
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

if (isset($_POST["baddmach"])) {
    $machine = $_POST["machine"];
    $comment = $_POST["comment"];

    if (!preg_match("/^[A-Za-z][A-Za-z-0-9]*$/", $machine)) {
        $error = _T("Invalid computer name");
        $n = new NotifyWidget();
        $n->flush();
        $n->add("<div id=\"errorCode\">$error</div>");
        $n->setLevel(4);
        $n->setSize(600);    
    } else {
        add_machine($machine, $comment);
	if (!isXMLRPCError()) {
            $n = new NotifyWidget();
	    $n->add(sprintf("Computer %s successfully added", $machine));
	    header("Location: " . urlStrRedirect("samba/machines/index"));
	}
    }
}

$p = new PageGenerator(_T("Add a computer"));
$p->setSideMenu($sidemenu);
$p->display();

?>

<p><?= _T("The computer name can only contains letters lowercase and numbers, and must begin with a letter."); ?></p>

<form method="post" action="<? echo "main.php?module=samba&submod=machines&action=add"; ?>">
<table cellspacing="0">
<tr><td style="text-align: right;width :40%"><?= _T("Computer name"); ?></td>
    <td><input name="machine" type="text" class="textfield" size="23" value="<?php if (isset($error)){echo $machine;} ?>" /></td></tr>
<tr><td style="text-align: right;width :40%"><?= _T("Comment"); ?></td>
    <td><input name="comment" type="text" class="textfield" size="23" value="<?php if (isset($error)){echo $comment;} ?>" /></td></tr>
</table>

<input name="baddmach" type="submit" class="btnPrimary" value="<?= _T("Add"); ?>" />
</form>
