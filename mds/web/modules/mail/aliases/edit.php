<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com
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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

require("modules/mail/mainSidebar.php");
require("graph/navbar.inc.php");
require_once("modules/mail/includes/mail.inc.php");
require_once("includes/FormHandler.php");

global $errorStatus;
$error = "";
$result = "";

// Class managing $_POST array
if($_POST) {
    $FH = new FormHandler("editAliasFH", $_POST);
}
else {
    $FH = new FormHandler("editAliasFH", array());
}

if ($_GET["action"] == "add") {
    $mode = "add";
    $title = _T("Add a virtual alias", "mail");
}
else {
    $mode = "edit";
    $title = _T("Edit virtual alias", "mail");;
    $sidemenu->forceActiveItem("index");
    $alias = $_GET['alias'];
    $FH->setArr(getVAlias($alias));
}

if ($_POST) {
    if($mode == "add") {
        if ($FH->isUpdated("mailalias")) {
            addVAlias($FH->getValue("mailalias"));
            $alias = $FH->getValue("mailalias");
            $result .= _T("The virtual alias has been created.", "mail") . "<br />";
        }
        else {
            $error .= _T("The alias name is required.", "mail");
            setFormError("mailalias");
        }
    }
    else {
        if ($FH->isUpdated("mailalias")) {
            changeVAliasName($alias, $FH->getValue("mailalias"));
            $alias = $FH->getValue("mailalias");
            $result .= _T("Virtual alias name updated.", "mail") . "<br />";
        }
    }
    if(!$error && !isXMLRPCError()) {
        if($FH->isUpdated("users")) {
            if ($mode == "edit")
                $old = getVAliasUsers($alias);
            else
                $old = array();
            $new = $FH->getValue('users');
            foreach (array_diff($old, $new) as $uid) {
                delVAliasUser($alias, $uid);
            }
            foreach (array_diff($new, $old) as $uid) {
                addVAliasUser($alias, $uid);
            }
            $result .= _T("Virtual alias users updated.", "mail") . "<br />";
        }
        if($FH->isUpdated("mail")) {
            updateVAliasExternalUsers($alias, $FH->getValue("mail"));
            $result .= _T("Virtual alias external users updated.", "mail") . "<br />";
        }
        if($FH->isUpdated("mailenable")) {
            if($FH->getValue("mailenable") == "on") {
                changeVAliasEnable($alias, true);
                $result .= _T("Virtual alias enabled.", "mail") . "<br />";
            }
            else {
                changeVAliasEnable($alias, false);
                $result .= _T("Virtual alias disabled.", "mail") . "<br />";
            }
        }
        if ($result && !isXMLRPCError())
            new NotifyWidgetSuccess($result);
        $FH->isError(false);
        header("Location: ". urlStrRedirect("mail/aliases/edit", array("alias" => $alias)));
    }
    else {
        if ($error)
            new NotifyWidgetFailure($error);
        $FH->isError(true);
        // make next XML-RPC calls
        $errorStatus = 0;
    }
}

$p = new PageGenerator($title);
$p->setSideMenu($sidemenu);
$p->display();

$f = new ValidatingForm();
$f->push(new Table());

$f->add(
    new TrFormElement(_T("Alias name"), new MailInputTpl("mailalias")),
    array("value" => $FH->getArrayOrPostValue("mailalias"), "required" => true)
);

$checked = "checked";
if ($FH->getArrayOrPostValue("mailenable") != 'on' && $FH->getArrayOrPostValue("mailenable") != 'OK')
    $checked = "";
$f->add(
    new TrFormElement(_T("Enabled", "mail"), new CheckboxTpl("mailenable")),
    array("value" => $checked)
);

/* LDAP Users */
$users = get_users();
$usersTpl = new MembersTpl("users");
$usersTpl->setTitle(_T("Alias users", "mail"), _T("All users", "mail"));
// get the alias users
if ($FH->getPostValue("users"))
    $alias_users = $FH->getPostValue("users");
else {
    if ($mode == 'edit') {
        $alias_users = getVAliasUsers($alias);
    }
    else
        $alias_users = array();
}
$member = array();
foreach($alias_users as $user) {
    $member[$user] = $user;
}
// get all users
$available = array();
foreach($users as $user) {
    if (!in_array($user, $member))
        $available[$user] = $user;
}

$f->add(
    new TrFormElement(_("Users"), $usersTpl),
    array("member" => $member, "available" => $available)
);

$f->pop();

$f->add(
    new FormElement(_T("External mail addresses", "mail"),
        new MultipleMailInputTpl("mail", _T("External mail addresses", "mail"))),
    $FH->getArrayOrPostValue("mail", "array")
);

if ($mode == "add")
    $f->addButton("badd", _("Create"));
else
    $f->addButton("bedit", _("Confirm"));

$f->display();

?>
