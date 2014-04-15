<?php
/**
 * (c) 2014 Zentyal, http://www.zentyal.com
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
 *
 * Author(s):
 *   Miguel Julián <mjulian@zentyal.com>
 */


require("modules/samba4/includes/shares-xmlrpc.inc.php");
require("modules/samba4/mainSidebar.php");
require("graph/navbar.inc.php");

require("modules/base/includes/groups.inc.php");
require_once("includes/FormHandler.php");

/*
   Add/Edit buttons redirect to this edit.php, so we should handle here
   their form actions
*/
/* When edit share form has been submited */
if (isset($_POST["bshareedit"]) or isset($_POST["bshareadd"]))
{
    $actionCanBeCalled = True;

    $params = _parseForm($_POST);
    list($shareName, $sharePath, $shareDescription, $shareEnabled, $shareGuest, $shareGroup, $shareUser) = $params;

    $actionCanBeCalled = _shareNameAndPathCheckings($shareName, $sharePath);

    if (isset($_POST["bshareedit"])) {
        $action = "edit";
        $successMessage = sprintf(_T("Share %s successfully modified"), $shareName);
    } else if (isset($_POST["bshareadd"])) {
        $action = "add";
        $successMessage = sprintf(_T("Share %s successfully added"), $shareName);
    }

    $actionResult = False;
    if ($actionCanBeCalled) {
        $actionResult = _callAddEditShareAction($action, $params);
    }

    _displaySuccessMessage($actionResult, $successMessage);
    _redirectToSharesList($actionResult, $share);
}

/* This will show the form (empty if adding) with the share details
   After adding or editing the share (above) then just show the share again (below) */
if ($_GET["action"] == "add") {
    $title = _T("Add a share");
    $activeItem = "add";
    $shareName = "";
    $sharePath= "";
    $shareDescription = "";
    $shareEnabled = "checked";
    $shareGuest = "";
    $shareGroup = "";
    $shareUser = "";
} else {
    $share = urldecode($_GET["share"]);
    $title = _T("Properties of share $share");
    $activeItem = "index";
    $shareDetails = getShare($share);
    $shareName = $shareDetails[0];
    $sharePath= $shareDetails[1];
    $shareEnabled = $shareDetails[2] ? "checked" : "";
    $shareDescription = $shareDetails[3];
    $shareGuest = $shareDetails[4] ? "checked" : "";
    $shareGroup = array();
    $shareUser = array();
}

$page = new PageGenerator($title);
$sidemenu->forceActiveItem($activeItem);
$page->setSideMenu($sidemenu);
$page->display();
?>

<form id="Form" method="post" action="" onSubmit="autogroupObj.selectAll(); autouserObj.selectAll(); autoadminObj.selectAll(); return validateForm();">

<?php

$table = new Table();
if ($_GET["action"] == "add")  {
    $input = new InputTpl("shareName");
} else {
    $input = new HiddenTpl("shareName");
}

$table->add(new TrFormElement(_T("Name"), $input), array("value" => $shareName));
$table->add(new TrFormElement(_T("Path"), new InputTpl("sharePath")),array("value" => $sharePath));
$table->add(new TrFormElement(_T("Description"), new InputTpl("shareDescription")),array("value" => $shareDescription));
$table->display();
?>

<table cellspacing="0">
<?php
$param = array("value" => $shareGuest, "extraArg"=>'onclick="toggleVisibility(\'grouptable\');"');
$test = new TrFormElement(_T("Guest access"), new CheckboxTpl("shareGuest"));
$test->setCssError("shareGuest");
$test->display($param);

$param = array("value" => $shareEnabled);
$test = new TrFormElement(_T("Share enabled"), new CheckboxTpl("shareEnabled"));
$test->display($param);
?>
</table>

<?php
if ($shareGuest) {
    echo '<div id="grouptable" style="display:none">';
} else {
    echo '<div id="grouptable">';
}
?>
<table>
<?php
$acls = array(array(), array());

if ($_GET["action"] != "add")
    $acls = getACLOnShare($share);

setVar("tpl_groups", $acls[0]);
global $__TPLref;
$__TPLref["autocomplete"] = "group";
renderTPL("groups");

?>
</table>
</div>

<div id="expertMode" class="expertMode" <?php displayExpertCss(); ?>>
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

<?php if ($_GET["action"] == "add")  { ?>
<input name="bshareadd" type="submit" class="btnPrimary" value="<?php echo  _T("Create"); ?>" />
<?php } else { ?>
<input name="share" type="hidden" value="<?php echo $share; ?>" />
<input name="bshareedit" type="submit" class="btnPrimary" value="<?php echo  _T("Confirm"); ?>" />
<?php }

?>

</form>

<?php
/* Private functions */
function _parseForm($_POST) {
    $FH = new FormHandler("editSambaShareFH", $_POST);

    $shareName = $FH->getPostValue("shareName");
    $sharePath = $FH->getPostValue("sharePath");
    $shareDescription = $FH->getPostValue("shareDescription");
    $shareEnabled = ($FH->getPostValue("shareEnabled") == "on") ? True : "";
    $shareGuest = ($FH->getPostValue("shareGuest") == "on") ? True : "";
    $shareGroup = $FH->getPostValue($_POST["groupgroupsselected"]);
    $shareUser = $FH->getPostValue($_POST["userusersselected"]);

    if (! $shareGroup)
        $shareGroup = array();
    else if ($shareGroup and ! is_array($shareGroup))
        $shareGroup = array($shareGroup);

    if (! $shareUser)
        $shareUser = array();
    else if ($shareUser and ! is_array($shareUser))
        $shareUser = array($shareUser);

    return array($shareName, $sharePath, $shareDescription, $shareEnabled, $shareGuest, $shareGroup, $shareUser);
}

function _getShareValue($_GET) {
    return (isset($_GET["share"])) ? $_GET["share"] : "";
}

function _shareNameAndPathCheckings($name, $path) {
    if ($name and !(preg_match("/^[a-zA-Z][a-zA-Z0-9.]*$/", $name)))
        new NotifyWidgetFailure(_T("Invalid share name"));
    else if (!isAuthorizedSharePath($path))
        new NotifyWidgetFailure(_T("The share path is not authorized by configuration"));
    else
        return True;

    return False;
}

function _callAddEditShareAction($action, $params) {
    if ($action == "edit")
        return editShare($params);
    else if ($action == "add")
        return addShare($params);
    else
        return False;
}

function _displaySuccessMessage($success, $message) {
    if (!isXMLRPCError() and $success) {
        new NotifyWidgetSuccess($message);
    } else {
        global $errorStatus;
        $errorStatus = 0;
    }
}

function _redirectToSharesList($success, $share) {
    if ($success) {
        header("Location: " . urlStrRedirect("samba4/shares/index"));
        exit;
    }
}

?>
