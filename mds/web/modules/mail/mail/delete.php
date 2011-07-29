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

if (isset($_POST["bback"]))
{
    header("Location: main.php?module=mail&submod=mail&action=index");
    exit;
}

if (isset($_POST["bconfirm"])) {
    $domainname = $_POST["domainname"];
    delVDomain($domainname);
    if (!isXMLRPCError()) {
        $n = new NotifyWidget();
	$n->flush();
	$result = _T("The mail domain has been deleted.");
	$n->add("<div id=\"validCode\">$result</div>");
	$n->setLevel(0);
	$n->setSize(600);
    }
    header("Location: main.php?module=mail&submod=mail&action=index");
} else {
    $domainname = urldecode($_GET["mail"]);
}
?>

<p>
<?php echo  _T("You will delete the virtual mail domain "); ?> <strong><?php echo $domainname; ?></strong>.
</p>

<form action="main.php?module=mail&submod=mail&action=delete" method="post">
<input type="hidden" name="domainname" value="<?php echo $domainname; ?>" />
<input type="submit" name="bconfirm" class="btnPrimary" value="<?php echo  _T("Delete domain"); ?>" />
<input type="submit" name="bback" class="btnSecondary" value="<?php echo  _("Cancel"); ?>" onClick="new Effect.Fade('popup'); return false;" />
</form>
