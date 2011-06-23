<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: deletehost.php 2 2007-03-28 15:09:50Z cedric $
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

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");

if (isset($_POST["bconfirm"])) {
    $subnet = $_POST["subnet"];
    $host = $_POST["host"];
    delHost($subnet, $host);
    $result = _T("The host has been deleted.");
    if (isset($_POST["updatedns"]) & isset($_POST["zone"])) {
        delRecord($_POST["zone"], $host);
        $result .= " " . _T("The DNS record has been deleted.");
    }
    if (!isXMLRPCError()) new NotifyWidgetSuccess($result);
    header("Location: main.php?module=network&submod=network&action=subnetmembers&subnet=$subnet");
} else {
    $subnet = urldecode($_GET["subnet"]);
    $host = urldecode($_GET["host"]);
    $askupdatedns = False;
    $domain = "";
    $options = getSubnetOptions(getSubnet($subnet));
    if (isset($options["primarydomainname"])) {
        /*
           If the DHCP domain name option is set, and corresponds to an existing DNS zone
           we ask the user if she/he wants to remove the A record in the DNS zone too.
        */
        $domain = $options["primarydomainname"];
        if (zoneExists($domain)) {
            if (hostExists($domain, $host)) $askupdatedns = True;
        }
    }
}
?>

<p>
<?= sprintf(_T("You will delete the host %s from the DHCP subnet."), "<strong>$host</strong>"); ?>
</p>


<form action="main.php?module=network&submod=network&action=subnetdeletehost" method="post">

<?php if ($askupdatedns) { ?>
    <p><input type="checkbox" name="updatedns" CHECKED /> <?= sprintf(_T("Also unregister this host from DNS zone %s"), $domain); ?></p>
    <input type="hidden" name="zone" value="<?php echo $domain; ?>" />
<?php } ?>

<input type="hidden" name="host" value="<?php echo $host; ?>" />
<input type="hidden" name="subnet" value="<?php echo $subnet; ?>" />
<input type="submit" name="bconfirm" class="btnPrimary" value="<?= _T("Delete host"); ?>" />
<input type="submit" name="bback" class="btnSecondary" value="<?= _("Cancel"); ?>" onClick="new Effect.Fade('popup'); return false;" />
</form>
