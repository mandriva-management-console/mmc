<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
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

require("modules/samba/includes/shares.inc.php");

if (isset($_POST["bdelshare"])) {
    del_share($error, $_POST["share"], $_POST["delFiles"]);
}
?>

<style type="text/css">
<!--

<?php
require("modules/samba/graph/shares/index.css");
?>

-->
</style>

<?php
$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
              array("name" => _T("Shares"),
                    "link" => "main.php?module=samba&submod=shares&action=index"),
              array("name" => _T("Del a share")));

require("modules/samba/mainSidebar.php");

?>

<h2><?= _T("Delete a share"); ?></h2>

<div class="fixheight"></div>

<?php
if (isset($_GET["share"]))
{
  $share = urldecode($_GET["share"]);
?>

<form action="<? echo "main.php?module=samba&submod=shares&action=delete";?>" method="post">
<p>
<?php
   printf(_T("You will delete the share <b>%s</b>"),$share);
?>
</p>

<input type="checkbox" name="delFiles" /><?= _T("Delete all data"); ?>
<br>
<br>
<p>
<?= _T("Are you sure ?"); ?>
</p>

<input name="share" type="hidden" value="<?php echo $share; ?>" />
<input name="bdelshare" type="submit" class="btnPrimary" value="<?= _T("Delete "); ?> <?php echo $share; ?>" />
<input name="bback" type="submit" class="btnSecondary" value="<?= _T("Cancel"); ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>

<?php
}
else if (isset($_POST["bdelshare"]))
{
  $share = $_POST["share"];

  if (isset($error))
    {
?>

<p>
    <?= _T("An error has occured during delete process on %s:<br/>",$share); ?>
</p>

<?php
      echo $error;
    }
  else
    {
?>

<p>
<?php
    $str = sprintf(_T("Share %s deleted"),$share);
    $n = new NotifyWidget();
    $n->add($str);

    header( "location: " . urlStrRedirect('samba/shares/index'));
  }
}
else
{
?>

<p>
<?= _T("You must select a share into the list of shares"); ?>
</p>

<form action="<? echo "main.php?module=samba&submod=shares&action=index"; ?> " method="post">
<input name="bback" type="submit" class="btnSecondary" value="<?= _("Cancel"); ?>" onclick="new Effect.Fade('popup'); return false;"/>
</form>

<?php
}
?>
