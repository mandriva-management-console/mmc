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

/**
 * Function called for changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */

//require_once("ErrorHandling.php");
require("modules/samba4/includes/users-xmlrpc.inc.php");
require_once("modules/samba4/includes/common-xmlrpc.inc.php");

/* Function triggered when a user has been updated or created*/
function _samba4_changeUser($FH, $mode) {
    if ($mode == "add") {
        $username = $FH->getPostValue("uid");
        $password = $FH->getPostValue("pass");
        createSambaUser($username, $password);

        if (_enablingSamba4ToUser($FH, $mode))
            enableSambaUser($username);
        else
            disableSambaUser($username);
    } else if($mode == "edit") {
        $username = $FH->getPostValue("uid");
        if (_enablingSamba4ToUser($FH, $mode)) {
            _samba4_enableUser($username);
        } else if (_disablingSamba4ToAExistingUser($FH, $mode)) {
            _samba4_disableUser($username);
        }
    }
}

function _samba4_delUser($username) {
    if (userHasSambaAccount($username)) {
        deleteSambaUser($username);
    }
}

/* Function triggered when a user has been enabled*/
function _samba4_enableUser($paramsArray) {
    if (! is_string($paramsArray))
        $username = $paramsArray[0];
    else
        $username = $paramsArray;

    if (userHasSambaAccount($username)) {
        enableSambaUser($username);
    }
}

/* Function triggered when a user has been disabled*/
function _samba4_disableUser($paramsArray) {
    if (! is_string($paramsArray))
        $username = $paramsArray[0];
    else
        $username = $paramsArray;

    if (userHasSambaAccount($username)) {
        disableSambaUser($username);
    }
}

/*
 * Triggered when a user's password is changed
 * Params:
 *  $paramsArray: array
 *      [0] => (string) user uid
 *      [1] => (Trans) the new password (encoded)
 *          ["scalar"] => the encoded password
 *          ["xmlrpc_type"] => encoding (should be "base64")
 */
function _samba4_changeUserPasswd($paramsArray) {
    if (userHasSambaAccount($paramsArray[0])) {
        updateSambaUserPassword($paramsArray);
    }
}

/**
 * Form on user edit page
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */

/*
 * Return the form that will be appended to the user details
 * Anything "->display()" will be shown at the begining of the page (warnings?...)
 */
function _samba4_baseEdit($FH, $mode) {
    $form = new DivForModule(_T("Samba4 properties","samba4"), "#F3E2F2");

    if (! isSamba4Provisioned())
        $form->setVisibility(False);

    $username = $FH->getArrayOrPostValue("uid");
    if (! $username)
        $form->setVisibility(False);

    $form->push(new Table());

    $tr = new TrFormElement(_T("Samba access","samba"), new CheckboxTpl("isSamba4"));
    $form->add($tr, array("value" => userHasSambaEnabled($username) ? "checked" : ""));

    $form->pop();

    return $form;
}

/**
 * Function called before changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 *
 *  @return (bool) whether or not there are any errors
 */

/*
 * The contents of globar variable error will be shown to the user
 * Append custom information to it (there may be previous errors)
 */
function _samba4_verifInfo($FH, $mode) {
    global $error;

    $samba4Errors = "";

    $samba4Errors .= _checkSambaProvisionError();
    $samba4Errors .= _checkUsername($FH);
    $samba4Errors .= _checkPassword($FH, $mode);

    $error .= $samba4Errors;

    return $samba4Errors ? 1 : 0;
}

/* Private functions */

/* Form helper functions */
function _enablingSamba4ToUser($FH, $mode) {
    return $FH->isUpdated("isSamba4") and $FH->getPostValue("isSamba4") == "on";
}

function _disablingSamba4ToAExistingUser($FH, $mode) {
    return $FH->isUpdated("isSamba4") and ! $FH->getPostValue("isSamba4");
}

/* Checking functions */
function _checkSambaProvisionError() {
    if (! isSamba4Provisioned()) {
        return  _T("You have to provision samba4 module before enabling it to the user","samba4")."<br />\n";
    }

    return "";
}

function _checkUsername($FH) {
    if (! $FH->getPostValue("uid")) {
        return _T("UID field has not been submited.","samba4")."<br />\n";
    }

    return "";
}

function _checkPassword($FH, $mode) {
    if (_enablingSamba4ToUser($FH, $mode)) {
        if (! $FH->isUpdated("pass")) {
            setFormError("pass"); /* Mark the field password to be filled */
            setFormError("confpass");
            return _T("You must reenter your password.","samba4")."<br />\n";
        }
    }

    return "";
}
?>
