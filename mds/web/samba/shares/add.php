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

require("modules/samba/includes/shares.inc.php");
require("modules/base/includes/groups.inc.php");

require("graph/header.inc.php");
?>

<style type="text/css">
<!--

<?php
require("modules/samba/graph/shares/add.css");
?>

-->
</style>

<?php
$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
              array("name" => _T("Shares"),
                    "link" => "main.php?module=samba&submod=shares&action=index"),
              array("name" => _T("Add a share")));

require("modules/samba/mainSidebar.php");

require("graph/navbar.inc.php");

if (isset($_POST["bcreate"]))
{
  $shareName = $_POST["shareName"];
  $shareDesc = $_POST["shareDesc"];
  $shareGroup = $_POST['groupsselected'];
  $permAll = $_POST["permAll"];
    if ($_POST["hasClamAv"]) {
    $av = 1;
  } else {
    $av = 0;
  }

  if (!(preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/", $shareName)))
  {
    $error = _T("invalid share name");
  }
  else
  {
    $result = add_share($error, $shareName, $shareDesc, $shareGroup, $permAll, $av);
    if (!isXMLRPCError()) {
        $n = new NotifyWidget();
        $n->add(sprintf("share %s sucessfully added",$shareName));
    }
  }
}

?>

<h2><?= _T("Add a share")?></h2>

<div class="fixheight"></div>

<p>
<?= _T("The share name can only contains letters (lowercase and uppercase) and numbers, and must begin with a letter."); ?>
</p>

<form method="post" action="<? echo $PHP_SELF; ?>" onSubmit="selectAll();">

<table cellspacing="0">
<tr><td style="text-align: right;"><?= _T("Name"); ?></td>
    <td><input name="shareName" type="text" class="textfield" size="23" value="<?php if (isset($error)) {echo $shareName;} ?>" /></td></tr>
<tr><td style="text-align: right;"><?= _T("Comment"); ?></td>
    <td><input name="shareDesc" type="text" class="textfield" size="23" value="<?php if (isset($error)) {echo $shareDesc;} ?>" /></td></tr>
<?php
    if (hasClamAv()) {

        /*if ($av) {
            $checked = "checked";
        } else {

        }*/
        $checked = "";
        $param = array ("value" => $checked);

        $test = new TrFormElement(_T("AntiVirus on this share"), new CheckboxTpl("hasClamAv"));
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
        <?= _T("Permissions"); ?>
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
    if ($share) {
        setVar('tpl_groups',getACLOnShare($share));
    }
    renderTPL("groups");
?>
</table>
</div>



<input name="bcreate" type="submit" class="btnPrimary" value="<?= _T("Create"); ?>" />
<?php
if (isset($_POST["bcreate"]) && (!isset($error)))
{
  //echo $result;
}
if (isset($error))
{
  echo $error;
}
?>
</form>
