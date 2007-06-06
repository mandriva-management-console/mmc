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

require("modules/network/includes/network-xmlrpc.inc.php");

if (isset($_POST["bconfirm"])) {
    $host = $_POST["host"];
    $zone = $_POST["zone"];
    delRecord($zone, $host);
    if (!isXMLRPCError()) new NotifyWidgetSuccess(_T("The host has been deleted."));
    header("Location: main.php?module=network&submod=network&action=zonemembers&zone=$zone");
} else {
    $host = urldecode($_GET["host"]);
    $zone = urldecode($_GET["zone"]);
}
?>

<p>
<?= sprintf(_T("You will delete the host %s."), "<strong>$host</strong>"); ?>
</p>

<form action="main.php?module=network&submod=network&action=deletehost" method="post">
<input type="hidden" name="host" value="<?php echo $host; ?>" />
<input type="hidden" name="zone" value="<?php echo $zone; ?>" />
<input type="submit" name="bconfirm" class="btnPrimary" value="<?= _T("Delete host"); ?>" />
<input type="submit" name="bback" class="btnSecondary" value="<?= _("Cancel"); ?>" onClick="new Effect.Fade('popup'); return false;" />
</form>
