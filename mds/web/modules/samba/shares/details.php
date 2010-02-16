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


require("modules/samba/includes/shares.inc.php");
require("modules/base/includes/groups.inc.php");
require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

if (isset($_POST["bcreate"])) {
    $shareName = $_POST["shareName"];
    $sharePath = $_POST["sharePath"];
    $shareDesc = $_POST["shareDesc"];
    $shareGroup = $_POST["usergroupsselected"];
    $adminGroups = $_POST["admingroupsselected"];
    $permAll = $_POST["permAll"];
    if ($_POST["hasClamAv"]) $av = 1;
    else $av = 0;
    if ($_POST["browseable"]) $browseable = 1;
    else $browseable = 0;
    
    if (!(preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/", $shareName))) {
	new NotifyWidgetFailure(_T("Invalid share name")); 
    } else {
        $add = True;
        if (strlen($sharePath)) {
            if (!isAuthorizedSharePath($sharePath)) {
                new NotifyWidgetFailure(_T("The share path is not authorized by configuration"));
                $add = False;
            }
        }
        if ($add) {
            add_share($shareName, $sharePath, $shareDesc, $shareGroup, $permAll, $adminGroups, $browseable, $av);
            if (!isXMLRPCError()) {
                new NotifyWidgetSuccess(sprintf(_T("Share %s successfully added"), $shareName));
                header("Location: " . urlStrRedirect("samba/shares/index" ));
            }
        }
    }
}

if (isset($_POST["bmodify"]))
{
    $share = $_POST["share"];
    $sharePath = $_POST["sharePath"];
    $shareDesc = $_POST["shareDesc"];
    $shareGroup = $_POST["usergroupsselected"];
    $adminGroups = $_POST["admingroupsselected"];
    $permAll = $_POST["permAll"];
    if ($_POST["hasClamAv"]) $av = 1;
    else $av = 0;
    if ($_POST["browseable"]) $browseable = 1;
    else $browseable = 0;
    mod_share($share, $sharePath, $shareDesc, $shareGroup, $permAll, $adminGroups, $browseable, $av);
    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_T("Share %s successfully modified"), $shareName));
    }
}

if ($_GET["action"] == "add") {
    $title = _T("Add a share");
    $activeItem = "add";
    $share = "";
    $shareDesc = "";
    $permAll = False;
    $av = False;
    $browseable = True;
} else {
    $share = urldecode($_GET["share"]);
    $title = _T("Properties of share $share");
    $activeItem = "index";
    $shareInfos = share_infos($share);
    $shareDesc = $shareInfos["desc"];
    $sharePath = $shareInfos["sharePath"];
    $shareGroup = $shareInfos["group"];
    $permAll = $shareInfos["permAll"];
    $av = $shareInfos["antivirus"];
    $browseable = $shareInfos["browseable"];
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

<form method="post" action="" onSubmit="autouserObj.selectAll(); autoadminObj.selectAll();">

<?

$t = new Table();
if ($_GET["action"] == "add")  {
    $input = new InputTpl("shareName");
} else {
    $input = new HiddenTpl("shareName");
}
$t->add(
        new TrFormElement(_T("Name"), $input),
        array("value" => $share)
        );

$t->add(
        new TrFormElement(_T("Comment"), new InputTpl("shareDesc")),
        array("value" => $shareDesc)
        );

if (hasClamAv()) {
    $checked = "";
    if ($av) {
        $checked = "checked";
    }
    $param = array ("value" => $checked);
    $t->add(
            new TrFormElement(_T("AntiVirus on this share"), new CheckboxTpl("hasClamAv")),
            $param
            );
}
$t->display();

$d = new DivExpertMode();
$d->push(new Table());

/* As long as we have no own modShare() (Ticket #96), the sharePath is readonly in edit mode */
if ($_GET["action"] == "add")  {
    $sharePath = "";
    $sharePathText = sprintf(_T("Share path (leave empty for a default path in %s)"), default_shares_path());
    $input = new InputTpl("sharePath");
} else {
    $sharePath = $shareInfos["sharePath"];
    $sharePathText = "Path";
    $input = new HiddenTpl("sharePath");
}

$d->add(
        new TrFormElement(_T($sharePathText), $input),
        array("value" => $sharePath)
        );

if ($browseable) $param = array("value" => "CHECKED");
else $param = array("value" => "");

$d->add(
        new TrFormElement(_T("This share is visible on the domain"), new CheckboxTpl("browseable")),
        $param
        );
$d->pop();
$d->display();
        
?>

<table cellspacing="0">
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
