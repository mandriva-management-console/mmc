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


if (isset($_POST["bconfirm"])) {
    $subnet = $_POST["subnet"];
    delSubnet($subnet);
    if (!isXMLRPCError()) {
        $services = getServicesNames();
        $n = new NotifyWidgetSuccess(_T("The DHCP subnet has been deleted. You must restart the DHCP service."));
        handleServicesModule($n, array($services[1] => "DHCP"));
    }
    header("Location: " . urlStrRedirect("network/network/subnetindex"));
    exit;
} else {
    $subnet = urldecode($_GET["subnet"]);
}
?>

<p>
<?php echo  sprintf(_T("You will delete the DHCP subnet %s."), "<strong>$subnet</strong>"); ?>
</p>

<form action="<?= urlStrRedirect("network/network/subnetdelete") ?>" method="post">
    <input type="hidden" name="subnet" value="<?php echo $subnet; ?>" />
    <input type="submit" name="bconfirm" class="btnPrimary" value="<?php echo  _T("Delete subnet"); ?>" />
    <input type="submit" name="bback" class="btnSecondary" value="<?php echo  _("Cancel"); ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>
