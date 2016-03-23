<?php
/**
 * (c) 2014 Mandriva, http://www.mandriva.com/
 *
 * $Id$
 *
 * This file is part of Management Console.
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

function getSamba4Shares() {
    return xmlCall("samba4.getShares", array());
}

function getProtectedSamba4Shares() {
    return xmlCall("samba4.getProtectedSamba4Shares", array());
}

function getShare($share) {
    return xmlCall("samba4.getShare", array($share));
}

/*
 * $params => array($shareName, $sharePath, $shareDescription, $shareEnabled, $shareGuest)
 */
function editShare($params) {
    return xmlCall("samba4.editShare", $params);
}

/*
 * $params => array($shareName, $sharePath, $shareDescription, $shareEnabled, $shareGuest)
 */
function addShare($params) {
    return xmlCall("samba4.addShare", $params);
}

function isAuthorizedSharePath($sharePath) {
    return xmlCall("samba4.isAuthorizedSharePath", array($sharePath));
}

function getACLOnShare($share) {
    return xmlCall("samba4.getACLOnShare", array($share));
}

function deleteShare($shareName, $deleteFiles) {
    return xmlCall("samba4.deleteShare", array($shareName, $deleteFiles));
}

function sched_backup($share, $media) {
    $param = array($share, $media, $_SESSION["login"]);
    return xmlCall("samba4.backupShare", $param);
}

?>
