<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: delete.php 1 2006-07-04 20:34:28Z cedric $
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
    $domain = $_POST["domain"];
    delVDomain($domain);
    if (!isXMLRPCError()) {
        $result = _T("The mail domain has been deleted.", "mail");
        new NotifyWidgetSuccess($result);
    }

    header("Location: " . urlStrRedirect("mail/domains/index"));
    exit;
}
else {
    $domain = urldecode($_GET["domain"]);
}

?>

<p><?php echo  _T("You will delete the virtual mail domain ", "mail"); ?> <strong><?php echo $domain; ?></strong>.</p>

<form action="<?php echo urlStrRedirect('mail/domains/delete'); ?>" method="post">
    <input type="hidden" name="domain" value="<?php echo $domain; ?>" />
    <input type="submit" name="bconfirm" class="btnPrimary" value="<?php echo _('Delete'); ?>" />
    <input type="submit" name="bback" class="btnSecondary" value="<?php echo _('Cancel'); ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>
