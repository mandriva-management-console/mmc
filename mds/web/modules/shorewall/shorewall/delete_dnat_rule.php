<?php
/**
 * (c) 2012 Mandriva, http://www.mandriva.com/
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

if (isset($_POST["bdelete"])) {
    $list = getRules("", $src, $dst, $filter);
    $rule = $list[$_POST['id']];
    foreach(getShorewallZones($src) as $zone)
        delRule($rule[0], $rule[1], $rule[2], $rule[3], $rule[4]);
    if (!isXMLRPCError())
        new NotifyWidgetSuccess(_T("The rule has been deleted."));
    header("Location: " . urlStrRedirect("shorewall/shorewall/" . $_POST['page']));
    exit;
}

?>

<p><?= _T("Delete this rule ?") ?></p>
<form action="<?= urlStrRedirect('shorewall/shorewall/delete_' . $_GET['page'] . '_rule') ?>" method="post">
    <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
    <input type="hidden" name="page" value="<?= $page ?>" />
    <input type="submit" name="bdelete" class="btnPrimary" value="<?= _('Delete') ?>" />
    <input type="submit" name="bback" class="btnSecondary" value="<?= _('Cancel') ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>
