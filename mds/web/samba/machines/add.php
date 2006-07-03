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

require("modules/samba/includes/machines.inc.php");

require("graph/header.inc.php");
?>

<style type="text/css">
<!--

<?php
require("modules/samba/graph/machines/add.css");
?>

-->
</style>

<?php
$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
              array("name" => _T("Computers"),
                    "link" => "main.php?module=samba&submod=machines&action=index"),
              array("name" => _T("Add a computer")));

require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

if (isset($_POST["baddmach"]))
{
  $machine = $_POST["machine"];
  $comment = $_POST["comment"];

  if (!preg_match("/^[a-z][a-z-0-9]*$/", $machine))
    {
      $error = "Nom de machine invalide !";
    }
}

?>

<h2><?= _T("Add a computer"); ?></h2>

<div class="fixheight"></div>

<p><?= _T("The computer name can only contains letters lowercase and numbers, and must begin with a letter."); ?></p>

<form method="post" action="<? echo "main.php?module=samba&submod=machines&action=add"; ?>">
<table cellspacing="0">
<tr><td><?= _T("Computer name"); ?></td>
    <td><input name="machine" type="text" class="textfield" size="23" value="<?php if (isset($error)){echo $machine;} ?>" /></td></tr>
<tr><td><?= _T("Comment"); ?></td>
    <td><input name="comment" type="text" class="textfield" size="23" value="<?php if (isset($error)){echo $comment;} ?>" /></td></tr>
</table>

<input name="baddmach" type="submit" class="btnPrimary" value="<?= _T("Add"); ?>" />
<input name="breset" type="reset" class="btnSecondary" value="<?= _T("Clear"); ?>" />
<?php
if (isset($_POST["baddmach"]) && (!$error))
{
  $result = add_machine($machine, $comment);
  if ($result==0) {
  echo _("Computer added");
  }
}
if (isset($error))
{
  echo $error;
}
?>
</form>
