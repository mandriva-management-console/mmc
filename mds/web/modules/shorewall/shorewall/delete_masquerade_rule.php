<?php
/**
 * (c) 2012 Mandriva, http://www.mandriva.com/
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if (isset($_POST["bdelete"])) {
    $rules = getMasqueradeRules();
    $rule = $rules[$_POST['id']];
    delMasqueradeRule($rule[0], $rule[1]);
    if (!isXMLRPCError()) {
        $n = new NotifyWidgetSuccess(_T("The rule has been deleted."));
        handleServicesModule($n, array("shorewall" => _T("Firewall")));
    }
    header("Location: " . urlStrRedirect("shorewall/shorewall/masquerade"));
    exit;
}

?>

<p><?= _T("Delete this rule ?") ?></p>
<form action="<?= urlStrRedirect('shorewall/shorewall/delete_masquerade_rule') ?>" method="post">
    <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
    <input type="submit" name="bdelete" class="btnPrimary" value="<?= _('Delete') ?>" />
    <input type="submit" name="bback" class="btnSecondary" value="<?= _('Cancel') ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>
