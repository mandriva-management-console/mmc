<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com
 *
 * $Id$
 *
 * This file is part of Management Console.
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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

if (isset($_POST["bconfirm"])) {
    $alias = $_POST["alias"];
    delVAlias($alias);
    if (!isXMLRPCError()) {
        $result = _T("The virtual mail alias has been deleted.", "mail");
        new NotifyWidgetSuccess($result);
    }
    header("Location: " . urlStrRedirect("mail/aliases/index"));
    exit;
}
else {
    $alias = urldecode($_GET["alias"]);
}

?>

<p><?php echo  _T("You will delete the virtual mail alias ", "mail"); ?> <strong><?php echo $alias; ?></strong>.</p>

<form action="<?php echo urlStrRedirect('mail/aliases/delete'); ?>" method="post">
    <input type="hidden" name="alias" value="<?php echo $alias; ?>" />
    <input type="submit" name="bconfirm" class="btnPrimary" value="<?php echo _('Delete'); ?>" />
    <input type="submit" name="bback" class="btnSecondary" value="<?php echo _('Cancel'); ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>
