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
$root = $conf["global"]["root"];

require("modules/samba/includes/shares.inc.php");

function sched_backup($share, $media)
{
  $param = array($share,$media,$_SESSION["login"]);
  return xmlCall("samba.backupShare",$param);
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
              array("name" => _T("Backup this share")));

require("modules/samba/mainSidebar.php");

?>

<h2><?= _T("Share backup"); ?></h2>

<div class="fixheight"></div>

<?php
if (isset($_GET["share"]))
{
  $share = urldecode($_GET["share"]);
}
if (isset($_POST["share"]))
{
  $share = $_POST["share"];
}

if (isset($_POST["bgo"]))
{
  $ret = sched_backup($share, $_POST["media"]);

  if ($ret)
    {
      $str =  "<p>"._T("Error during backup process")."</p>";
      $str .= "<p>".$ret."</p>";
      $n = new NotifyWidget();
      $n->add($str);
      header("Location: ".urlStrRedirect("samba/shares/index"));
    }
  else
    {


$str = "<h2>"._T("Share backup")."</h2>";
$str .=  '
<p>';
$str.=sprintf(_T("Backup of share <b>%s</b> has been launched as a background job."),$share);
$str.="</p>
<p>";
$archive = $_SESSION["login"]."-".$share."-".date("Ymd");
$str.=sprintf(_T("The ISO file that contains this backup will be stored into the directory <b>%s</b>."), $archive);
$str.="</p>
<p>";
$str.=_T("This operation will last according to the amount of data to backup.");
$str.="</p>";

$n = new NotifyWidget();
$n->setSize(400);
$n->add($str);

header("Location: ".urlStrRedirect("samba/shares/index"));

    }
}
else
{
?>

<form action="main.php?module=samba&submod=shares&action=backup" method="post">
<p>
<?php
    printf(_T("The share %s will be archived."),$share);
?>
</p>
<p>
<?php
echo _T("Please select media size. If your data exceed volume size, several files with your media size will be created");
?>
</p>

<?= _T("Media size"); ?>
<select name="media" />
<option value="600">CD (650 Mo)</option>
<option value="4200">DVD (4.7 Go)</option>
</select>
<br><br>
<input name="share" type="hidden" value="<?php echo $share; ?>" />
<input name="bgo" type="submit" class="btnPrimary" value="<?= _("Launch backup"); ?>" />
<input name="bback" type="submit" class="btnSecondary" value="<?= _("Cancel"); ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>

<?php
}
?>
