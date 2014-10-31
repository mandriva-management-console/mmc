<?php
/**
 * (c) 2014 Mandriva, http://www.mandriva.com/
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
?>
<?php
/*
 * Returns whether or not the user is present at the Samba LDAP
 * This is used as a check to avoid further operations
 */
function userHasSambaAccount($username) {
    return xmlCall("samba4.userHasSambaAccount", $username);
}

/*
 * Returns whether or not the user has samba enabled.
 * The user is at the active directory, but it may be disabled
 */
function userHasSambaEnabled($username) {
    return xmlCall("samba4.userHasSambaEnabled", $username);
}

/*
 * Create the given at the Samba directory
 * Return True if the operation succeded
 */
function createSambaUser($username, $password, $givenName, $sn) {
    return xmlCall("samba4.createSambaUser", array($username, $password, $givenName, $sn));
}

/*
 * Enable the given user from the Samba directory
 * Return True if the operation succeded
 */
function enableSambaUser($username) {
    return xmlCall("samba4.enableSambaUser", $username);
}

/*
 * Disable the given user from the Samba directory
 * Return True if the operation succeded
 */
function disableSambaUser($username) {
    return xmlCall("samba4.disableSambaUser", $username);
}

/*
 * Deletes the given user from the Samba directory
 * Return True if the operation succeded
 */
function deleteSambaUser($username) {
    return xmlCall("samba4.deleteSambaUser", $username);
}

/*
 * Updates the password to the given user
 * Params:
 *  $paramsArray: (array)
 *      [0] => (string) user uid (username)
 *      [1] => (Trans) the new password (encoded)
 *          ["scalar"] => the encoded password
 *          ["xmlrpc_type"] => encoding (should be "base64")
 */
function updateSambaUserPassword($paramsArray) {
    return xmlCall("samba4.updateSambaUserPassword", $paramsArray);
}
?>
