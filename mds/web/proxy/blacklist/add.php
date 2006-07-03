<?php
/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
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
<?php
/* $Id$ */

require("modules/proxy/includes/proxy.inc.php");
require("modules/proxy/includes/config.inc.php");

$path = array(array("name" => _("Home"),
                    "link" => "main.php"),
              array("name" => _T("Blacklist"),
                    "link" => "maint.php?module=proxy&submod=blacklist&action=add"),
              array("name" => _T("Add a domain in the blacklist")));

require("localSidebar.php");

require("graph/navbar.inc.php");

if (isset($_POST["bcreate"]))
{
  $blacklistName = $_POST["blacklistName"];
  $blacklistDesc = $_POST["blacklistDesc"];
  $blacklistGroup = $_POST["group"];
  $permAll = $_POST["permAll"];

  if (!(preg_match("/^[a-zA-Z][a-zA-Z0-9\._-]$/", $blacklistName)))
  {
    $error =  _T("Invalid domain name");
  }
  else
  {
    $arrB=get_blacklist();
    addElementInBlackList($blacklistName,$arrB);
    save_blacklist($arrB);
  }
}

?>

<h2><?= _T("Add a domain in the blacklist"); ?></h2>

<div class="fixheight"></div>

<p>
<?= _T("The domain must be valid"); ?>
</p>

<form method="post" action="<? echo $PHP_SELF; ?>">

<table cellspacing="0">
<tr><td><?= _T("Name"); ?></td>
    <td><input name="blacklistName" type="text" class="textfield" size="23" value="<?php if (isset($error)) {echo $blacklistName;} ?>" /></td></tr>
</table>

<input name="bcreate" type="submit" class="btnPrimary" value="<?= _("Create"); ?>" />
<?php
if (isset($_POST["bcreate"]) && (!isset($error)))
{
  echo $result;
}
if (isset($error))
{
  $n = new NotifyWidget();
  $n->add($error);
}
?>
</form>
