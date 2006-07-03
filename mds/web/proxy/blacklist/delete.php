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

if (isset($_POST["bback"]))
{
  header("Location: main.php?module=proxy&submod=blacklist&action=index");
  exit;
}
else if (isset($_POST["brestart"]))
{
  header("Location: main.php?module=proxy&submod=blacklist&action=restart");
  exit;
}
else if (isset($_POST["bdelblacklist"]))
{
  $arrB=get_blacklist();
  delElementInBlackList($_POST["blacklist"],$arrB);
  save_blacklist($arrB);
}


?>


<style type="text/css">
<!--

<?php
require("modules/proxy/graph/blacklist/index.css");
?>

-->
</style>

<?php
$path = array(array("name" => _("Home"),
                    "link" => "main.php"),
              array("name" => _T("Proxy management"),
                    "link" => "main.php?module=proxy&submod=blacklist&action=index"),
              array("name" => _T("Remove an entry in blacklist")));

if (isset($_GET["blacklist"]))
{

}

?>

<h2><?= _T("Remove a squidGuard domain"); ?></h2>

<div class="fixheight"></div>

<?php
if (isset($_GET["blacklist"]))
{
  $blacklist = urldecode($_GET["blacklist"]);
?>

<form action="<? echo "main.php?module=proxy&submod=blacklist&action=delete";?>" method="post">
<p>
<?php printf(_T("You will remove <b>%s</b> entry"),$blacklist); ?>
</p>

<br>
<p>
<?= _T("Are you sure ?"); ?>
</p>

<input name="blacklist" type="hidden" value="<?php echo $blacklist; ?>" />
<input name="bdelblacklist" type="submit" class="btnPrimary" value="<?= _("Delete"); ?> <?php echo $blacklist; ?>" />
<input name="bback" type="submit" class="btnSecondary" value="<?= _("Back"); ?>" />
</form>

<?php
}
else if (isset($_POST["bdelblacklist"]))
{
  $n = new NotifyWidget();
  $blacklist = $_POST["blacklist"];

  if (isset($error))
    {
$str = sprintf(_T("An error has occured during removal of domain %s"),$blacklist);
$str.="<br/>";
$str.= $error;
    }
  else
    {
        $str = sprintf(_T("Domain %s has been removed"),$blacklist);
    }
$n->add($str);

redirectTo(urlstr('proxy/blacklist/index'));

}
else
{
?>

<p>
<?= _T("You must select a domain in blacklist entries"); ?>
</p>

<form action="<? echo "main.php?module=proxy&submod=blacklist&action=index"; ?> " method="post">
<input name="bback" type="submit" class="btnPrimary" value="<?= _("Back"); ?>" />
</form>

<?php
}

?>
