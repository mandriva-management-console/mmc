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
?>
<?php

function hasSmbAttr($uid) {
    return xmlCall("samba.isSmbUser", array($uid));
}

function addSmbAttr($uid, $passwd) {
    return xmlCall("samba.addSmbAttr", array($uid, prepare_string($passwd)));
}

function rmSmbAttr($uid) {
    return xmlCall("samba.delSmbAttr", array($uid));
}

function changeSmbAttr($uid, $array) {
    if (!empty($array)) {
        xmlCall("samba.changeSambaAttributes", array($uid, $array));
    }
}

function isEnabledUser($uid) {
    return xmlCall("samba.isEnabledUser",array($uid));
}

function isLockedUser($uid) {
    return xmlCall("samba.isLockedUser",array($uid));
}

function userPasswdHasExpired($uid) {
    return xmlCall("samba.userPasswdHasExpired", array($uid));
}

function smbEnableUser($uid) {
    return xmlCall("samba.enableUser",array($uid));
}

function smbDisableUser($uid) {
    return xmlCall("samba.disableUser",array($uid));
}

function smbLockUser($uid) {
    return xmlCall("samba.lockUser",array($uid));
}

function smbUnlockUser($uid) {
    return xmlCall("samba.unlockUser",array($uid));
}

function getSmbStatus() {
    return xmlCall("samba.getSmbStatus",array());
}

function getConnected() {
    return xmlCall("samba.getConnected",array());
}


?>
