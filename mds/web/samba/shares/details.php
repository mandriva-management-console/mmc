<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007 Mandriva, http://www.mandriva.com/
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

require("modules/samba/includes/shares.inc.php");
require("modules/base/includes/groups.inc.php");
require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

if (isset($_POST["bcreate"]))
{
    $shareName = $_POST["shareName"];
    $shareDesc = $_POST["shareDesc"];
    $shareGroup = $_POST["usergroupsselected"];
    $adminGroups = $_POST["admingroupsselected"];
    $permAll = $_POST["permAll"];
    if ($_POST["hasClamAv"]) $av = 1;
    else $av = 0;
    if ($_POST["browseable"]) $browseable = 1;
    else $browseable = 0;
    
    if (!(preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/", $shareName))) {
        $error = _T("Invalid share name");
	$n = new NotifyWidget();
	$n->flush();
	$n->add("<div id=\"errorCode\">$error</div>");
	$n->setLevel(4);
	$n->setSize(600);    
    } else {
        add_share($shareName, $shareDesc, $shareGroup, $permAll, $adminGroups, $browseable, $av);
	if (!isXMLRPCError()) {
	    $n = new NotifyWidget();
	    $n->add(sprintf(_T("Share %s successfully added"), $shareName));
	    header("Location: main.php?module=samba&submod=shares&action=index");
	}
    }
}

if (isset($_POST["bmodify"]))
{
    $share = $_POST["share"];
    $shareDesc = $_POST["shareDesc"];
    $shareGroup = $_POST["usergroupsselected"];
    $adminGroups = $_POST["admingroupsselected"];
    $permAll = $_POST["permAll"];
    if ($_POST["hasClamAv"]) $av = 1;
    else $av = 0;
    if ($_POST["browseable"]) $browseable = 1;
    else $browseable = 0;
    mod_share($share, $shareDesc, $shareGroup, $permAll, $adminGroups, $browseable, $av);
    if (!isXMLRPCError()) {
        $n = new NotifyWidget();
        $n->add(sprintf(_T("Share %s successfully modified"), $share));
    }
}

if ($_GET["action"] == "add") {
    $title = _T("Add a share");
    $activeItem = "add";
} else {
    $share = urldecode($_GET["share"]);
    $title = _T("Properties of share $share");
    $activeItem = "index";
    $permAll = False;
    $av = False;
    $browseable = True;
}

if ($share != "homes")
{
    $shareInfos = share_infos($error, $share);
    if (!isset($error)) {
        $shareDesc = $shareInfos["desc"];
        $shareGroup = $shareInfos["group"];
        $permAll = $shareInfos["permAll"];
	$av = $shareInfos["antivirus"];
	$browseable = $shareInfos["browseable"];
    }
}

$p = new PageGenerator($title);
$sidemenu->forceActiveItem($activeItem);
$p->setSideMenu($sidemenu);
$p->display();

?>

<? if ($_GET["action"] == "add")  { ?>
<p>
<?= _T("The share name can only contains letters (lowercase and uppercase) and numbers, and must begin with a letter."); ?>
</p>

<?
}
?>

<form method="post" action="<? echo $PHP_SELF; ?>" onSubmit="autouserObj.selectAll(); autoadminObj.selectAll();">

<table cellspacing="0">
<tr><td style="text-align: right;width :40%"><?= _T("Name"); ?></td>
    <td>
<? if ($_GET["action"] == "add")  { ?>
    <input name="shareName" type="text" class="textfield" size="23" value="<?php if (isset($error)) {echo $shareName;} ?>" />
<? } else {
    echo $share;
} ?>
    </td>
</tr>
<tr><td style="text-align: right;width :40%"><?= _T("Comment"); ?></td>
    <td><input name="shareDesc" type="text" class="textfield" size="23" value="<?php
if ($_GET["action"] == "add") {    
    if (isset($error)) echo $shareDesc;
} else {
    echo $shareDesc;
}
?>" /></td></tr>

<?php
    if (hasClamAv()) {
        $checked = "";
	if ($shareInfos["antivirus"]) {
	    $checked = "checked";
	}
        $param = array ("value" => $checked);
        $test = new TrFormElement(_T("AntiVirus on this share"), new CheckboxTpl("hasClamAv"));
        $test->setCssError("hasClamAv");
        $test->display($param);
    }

?>
</table>

<div id="expertMode" class="expertMode" <?displayExpertCss();?>>
<table cellspacing="0">

<?php
if ($browseable) $param = array("value" => "CHECKED");
else $param = array("value" => "");

$test = new TrFormElement(_T("This share is visible on the domain"), new CheckboxTpl("browseable"));
$test->setCssError("browseable");
$test->display($param);
?>

</table>
</div>

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
if ($_GET["action"] == "add") $tpl_groups = array();
else {
    $tpl_groups = getACLOnShare($share);
    if ($shareGroup != 'root') {
        $tpl_groups[] = $shareGroup;
    }    
}
setVar("tpl_groups", $tpl_groups);
global $__TPLref;
$__TPLref["autocomplete"] = "user";
renderTPL("groups");
?>
</table>
</div>

<div id="expertMode" class="expertMode" <?displayExpertCss();?>>
<table cellspacing="0">
    <tr>
    <td>
    </td>
    <td>
        <?= _T("Administrator groups for this share"); ?>
    </td>
   </tr>

<?php
    if ($_GET["action"] == "add") {
      $domadmin = getDomainAdminsGroup();
      setVar("tpl_groups", array($domadmin["cn"][0]));
    } else setVar("tpl_groups", getAdminUsersOnShare($share));

    global $__TPLref;
    $__TPLref["autocomplete"] = "admin";
    renderTPL("groups");
?>

</table>
</div>

<? if ($_GET["action"] == "add")  { ?>
<input name="bcreate" type="submit" class="btnPrimary" value="<?= _T("Create"); ?>" />
<? } else { ?>
<input name="share" type="hidden" value="<?php echo $share; ?>" />
<input name="bmodify" type="submit" class="btnPrimary" value="<?= _T("Confirm");?>" /> 
<? }

?>

</form>
