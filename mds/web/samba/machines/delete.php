<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id$
 *
 * This file is part of LMC.
 *
 * LMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * LMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>

<style type="text/css">
<!--

<?php
require("modules/samba/graph/machines/index.css");
?>

-->
</style>

<?php

require("modules/samba/includes/machines.inc.php");
require("modules/samba/mainSidebar.php");

?>

<h2><?= _T("Delete a computer"); ?></h2>

<?php
if (isset($_GET["machine"])) {
  $machine = urldecode($_GET["machine"]);
}
if (isset($_POST["machine"])) {
  $machine = $_POST["machine"];
}

if (isset($_POST["bdelmach"])) {
    del_machine($machine);
    $str = sprintf(_T("Computer <strong>%s</strong> deleted."),$machine);
    $n = new NotifyWidget();
    $n->add($str);
    header("location: " . urlStrRedirect('samba/machines/index'));
}

?>

<form action="<? echo "main.php?module=samba&submod=machines&action=delete"; ?>" method="post">
<p>
<?
printf(_T("You will delete <strong>%s</strong>."),$machine);
?>
</p>

<input name="machine" type="hidden" value="<?php echo $machine; ?>" />
<input name="bdelmach" type="submit" class="btnPrimary" value="<?= _T("Delete"); ?>" />
<input name="bback" type="submit" class="btnSecondary" value="<?= _("Cancel"); ?>" onclick="new Effect.Fade('popup'); return false;"/>
</form>