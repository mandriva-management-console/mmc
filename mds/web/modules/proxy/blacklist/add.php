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
?>
<?php
/* $Id$ */

require("modules/proxy/includes/proxy.inc.php");
require("modules/proxy/includes/config.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

if (isset($_POST["bcreate"])) {
    $blacklistName = $_POST["blacklistName"];
    $blacklistDesc = $_POST["blacklistDesc"];
    $blacklistGroup = $_POST["group"];
    $permAll = $_POST["permAll"];

    if (!(preg_match("/^(([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/", $blacklistName))) {
        $error = _T("Invalid domain name");
    } else  {
        $arrB = get_blacklist();
	addElementInBlackList($blacklistName, $arrB);
	save_blacklist($arrB);
	if (!isXMLRPCError()) {
	    $n = new NotifyWidget();
	    $n->add(sprintf(_T("Domain %s successfully added"), $blacklistName));
	    header("Location: " . urlStrRedirect("proxy/blacklist/index"));
	}
    }
}

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

?>

<h2><?= _T("Add a domain in the blacklist"); ?></h2>

<div class="fixheight"></div>

<p>
<?= _T("The domain must be valid"); ?>
</p>

<form method="post" action="">

<table cellspacing="0">
 <tr>
  <td><?= _T("Name"); ?></td>
  <td><input name="blacklistName" type="text" class="textfield" size="23" value="<?php if (isset($error)) {echo $blacklistName;} ?>" /></td>
 </tr>
</table>

<input name="bcreate" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
</form>

<?php
if (isset($error)) {
    $n = new NotifyWidget();
    $n->add($error);
}
?>

