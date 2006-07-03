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


require("modules/samba/includes/shares.inc.php");
require("modules/base/includes/groups.inc.php");

if (isset($_POST["bback"]))
{
  header("Location: main.php?module=samba&submod=shares&action=index");
  exit;
}

require("graph/header.inc.php");
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
              array("name" => _T("Details")));

require("modules/samba/mainSidebar.php");

require("graph/navbar.inc.php");

if (isset($_GET["share"]))
{
  $share = urldecode($_GET["share"]);
}
if (isset($_POST["share"]))
{
  $share = $_POST["share"];
}

if (isset($_POST["bmodify"]))
{
  $shareDesc = $_POST["shareDesc"];
  $shareGroup = $_POST['groupsselected'];
  $permAll = $_POST["permAll"];
  if ($_POST["hasClamAv"]) {
    $av = 1;
  } else {
    $av = 0;
  }

  $result = mod_share($error, $share, $shareDesc, $shareGroup, $permAll,$av);
}

if ($share != "homes")
{
  $shareInfos = share_infos($error, $share);

  if (!isset($error))
    {
      $shareDesc = $shareInfos["desc"];
      $shareGroup = $shareInfos["group"];
      $permAll = $shareInfos["permAll"];
    }
}
?>

<h2><?= _T("Properties of share ");?><?php echo $share; ?></h2>

<div class="fixheight"></div>

<?php

if ($share == "homes")
{
?>

<p>
<?= _T("You cannot modify properties on share <b>homes</b>.");?>
</p>

<form action="<? echo "main.php?module=samba&submod=shares&action=index"; ?>" method="post">
<input type="submit" name="bback" class="btnPrimary" value="<?= _T("Back");?>" />
</form>

<?php
}
else
{
?>

<form method="post" action="<? echo "main.php?module=samba&submod=shares&action=details"; ?>" onSubmit="selectAll();">

<table cellspacing="0">
<tr><td><?= _T("Description");?></td>
    <td><input name="shareDesc" type="text" class="textfield" size="23" value="<?php echo $shareDesc; ?>" /></td></tr>




<?php
    if (hasClamAv()) {

        if ($shareInfos["antivirus"]) {
            $checked = "checked";
        } else {
            $checked = "";
        }
        $param = array ("value" => $checked);

        $test = new TrFormElement( _T("Antivirus is enabled"), new CheckboxTpl("hasClamAv"));
        $test->setCssError("hasClamAv");
        $test->display($param);

    }
?>


</table>

<table>
    <tr>
    <td>
    </td>
    <td>
        Permissions
    </td>
    </tr>
        <?php
            if ($permAll) {
                $checked = "checked";
            } else {
                $checked = "";
            }

        $param =array ("value" => $checked,"extraArg"=>'onclick="toggleVisibility(\'grouptable\');"');

        $test = new TrFormElement(_T("Access for all"), new CheckboxTpl("permAll"));
        $test->setCssError("permAll");
        $test->display($param);
         ?>
    </td>
</tr>



</table>

<?php
if ($permAll) {
    echo '<div id="grouptable" style="display:none">';
} else {
    echo '<div id="grouptable">';
}

?>
<table>
<?php

    $tpl_groups = getACLOnShare($share);
    if ($shareGroup!='root') {
        $tpl_groups[] = $shareGroup;
    }
    setVar('tpl_groups',$tpl_groups);
    renderTPL("groups");
?>
</table>
</div>

<input name="share" type="hidden" value="<?php echo $share; ?>" />
<input name="bmodify" type="submit" class="btnPrimary" value="<?= _T("Confirm");?>" />
<input type="submit" name="bback" class="btnSecondary" value="<?= _T("Back");?>" />
<?php
if (isset($_POST["bmodify"]) && (!isset($error)))
{
  echo $result;
}
if (isset($error))
{
  echo $error;
}
?>

</form>

<?php
}
?>
