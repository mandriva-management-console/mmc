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

/* Function triggered when a user has been updated or created*/
function _samba4_changeUser($FH, $mode) {
    return 1;
    if ($mode == "add") {
        $username = $FH->getPostValue("uid");
        $password = $FH->getPostValue("pass");
        createSambaUser($username, $password);
    }
}

function _samba4_delUser($username) {
    return 1;
    if (userHasSambaAccount($username)) {
        deleteSambaUser($username);
    }
}

/* Function triggered when a user has been enabled*/
function _samba4_enableUser($username) {
    return 1;

    if (! is_string($username)) {
        $username = $username[0];
    }

    if (userHasSambaAccount($username)) {
        enableSambaUser($username);
    }
}

/* Function triggered when a user has been disabled*/
function _samba4_disableUser($paramsArr) {
    return 1;

    if (! is_string($username)) {
        $username = $username[0];
    }

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
    return 1;
    if (userHasSambaAccount($paramsArray[0])) {
        updateSambaUserPassword($paramsArray);
    }
}
?>
