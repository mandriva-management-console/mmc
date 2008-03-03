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

if (isset($_GET["machine"])) {
  $machine = urldecode($_GET["machine"]);
}
if (isset($_POST["machine"])) {
  $machine = $_POST["machine"];
}

if (isset($_POST["bdelmach"])) {
    del_machine($machine);
    $str = sprintf(_T("Computer <strong>%s</strong> deleted."),$machine);
    new NotifyWidgetSuccess($str);
    header("location: " . urlStrRedirect('samba/machines/index'));
} else {
    $f = new PopupForm(_T("Delete a computer"));
    $f->addText(sprintf(_T("You will delete the %s computer"), "<strong>$machine</strong>"));
    $f->addValidateButton("bdelmach");
    $f->addCancelButton("bback");
    $f->display();    
}