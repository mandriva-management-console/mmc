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
    $shareGroup = $_POST["groupgroupsselected"];
    $shareUser = $_POST["userusersselected"];
    $adminGroups = $_POST["admingroupsselected"];
    $customParameters = $_POST["customparameters"];
    $permAll = $_POST["permAll"];
    if ($_POST["hasAv"]) $av = 1;
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
            $params = array($shareName, $sharePath, $shareDesc, $shareGroup, $shareUser, $permAll, $adminGroups, $browseable, $av, $customParameters);
            add_share($params);
            if (!isXMLRPCError()) {
                new NotifyWidgetSuccess(sprintf(_T("Share %s successfully added"), $shareName));
                header("Location: " . urlStrRedirect("samba/shares/index" ));
                exit;
            }
        }
    }
}

if (isset($_POST["bmodify"]))
{
    $share = $_GET["share"];
    $shareName = $_POST["shareName"];
    $sharePath = $_POST["sharePath"];
    $shareDesc = $_POST["shareDesc"];
    if (isset($_POST["groupgroupsselected"]))
        $shareGroup = $_POST["groupgroupsselected"];
    else
        $shareGroup = array();
    if (isset($_POST["userusersselected"]))
        $shareUser = $_POST["userusersselected"];
    else
        $shareUser = array();
    if (isset($_POST["admingroupsselected"]))
        $adminGroups = $_POST["admingroupsselected"];
    else
        $adminGroups = array();
    $customParameters = $_POST["customparameters"];
    if (isset($_POST["permAll"])) {
        $permAll = $_POST["permAll"];
    }
    else {
        $permAll = 0;
    }
    if (isset($_POST["hasAv"])) $av = 1;
    else $av = 0;
    if (isset($_POST["browseable"])) $browseable = 1;
    else $browseable = 0;

    $params = array($share, $sharePath, $shareDesc, $shareGroup, $shareUser, $permAll, $adminGroups, $browseable, $av, $customParameters);
    mod_share($params);

    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_T("Share %s successfully modified"), $shareName));
    }
    else {
        // Catch exception
        // but continue to show the page
        global $errorStatus;
        $errorStatus = 0;
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
    $customParameters = array("");
} else {
    $share = urldecode($_GET["share"]);
    $title = _T("Properties of share $share");
    $activeItem = "index";
    $shareInfos = share_infos($share);
    $customParameters = share_custom_parameters($share);
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

<?php if ($_GET["action"] == "add")  { ?>
<p>
<?php echo  _T("The share name can only contains letters (lowercase and uppercase) and numbers, and must begin with a letter."); ?>
</p>

<?
}
?>

<form id="Form" method="post" action="" onSubmit="autogroupObj.selectAll(); autouserObj.selectAll(); autoadminObj.selectAll(); return validateForm();">

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

if (hasAv()) {
    $checked = "";
    if ($av) {
        $checked = "checked";
    }
    $param = array ("value" => $checked);
    $t->add(
            new TrFormElement(_T("AntiVirus on this share"), new CheckboxTpl("hasAv")),
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
    $input = new IA5InputTpl("sharePath");
} else {
    $sharePath = $shareInfos["sharePath"];
    $sharePathText = "Path";
    $input = new IA5InputTpl("sharePath");
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
        <?php echo  _T("Permissions"); ?>
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
if ($_GET["action"] == "add") $acls = array(array(), array());
else {
    $acls = getACLOnShare($share);
    if ($shareGroup != 'root') {
        $acls[0][] = $shareGroup;
    }
}
setVar("tpl_groups", $acls[0]);
global $__TPLref;
$__TPLref["autocomplete"] = "group";
renderTPL("groups");

?>
</table>

<div id="expertMode" class="expertMode" <?displayExpertCss(); ?>>
<table cellspacing="0">
    <tr>
    <td>
    </td>
    <td>
        <?php echo  _T("Users for this share"); ?>
    </td>
   </tr>

<?php


setVar("tpl_users", $acls[1]);
$__TPLref["autocomplete"] = "user";
renderTPL("users");

?>
</table>
</div>
</div>

<div id="expertMode" class="expertMode" <?displayExpertCss(); ?>>
<table cellspacing="0">
    <tr>
    <td>
    </td>
    <td>
        <?php echo  _T("Administrator groups for this share"); ?>
    </td>
   </tr>

<?php
    if ($_GET["action"] == "add") {
        $domadmin = getDomainAdminsGroup();
        if ($domadmin)
            setVar("tpl_groups", array($domadmin["cn"][0]));
        else
            setVar("tpl_groups", array());
    }
    else {
        $domadmin = getAdminUsersOnShare($share);
        if ($domadmin)
            setVar("tpl_groups", $domadmin);
        else
            setVar("tpl_groups", array());
    }
    global $__TPLref;
    $__TPLref["autocomplete"] = "admin";
    renderTPL("groups");
?>

</table>

<?php

    if (!isset($customParameters) || empty($customParameters)) {
        $customParameters = array('');
    }
    $cp = new MultipleInputTpl("customparameters",_("Custom parameters"));
    $cp->setRegexp('/^[a-z: _]+[ ]*=.*$/');
    $cpf = new FormElement(_("Custom parameters"), $cp);
    $cpf->display($customParameters);

?>

</div>

<?php if ($_GET["action"] == "add")  { ?>
<input name="bcreate" type="submit" class="btnPrimary" value="<?php echo  _T("Create"); ?>" />
<?php } else { ?>
<input name="share" type="hidden" value="<?php echo $share; ?>" />
<input name="bmodify" type="submit" class="btnPrimary" value="<?php echo  _T("Confirm"); ?>" />
<?php }

?>

</form>
