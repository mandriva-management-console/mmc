<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2009 Mandriva, http://www.mandriva.com
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

function hasSshKeyObjectClass($uid) {
    return xmlCall("sshlpk.hasSshKeyObjectClass",array($uid));
}

function addSshKeyObjectClass($uid) {
    xmlCall("sshlpk.addSshKeyObjectClass",array($uid));
}

function getSshKey($uid, $id) {
    return xmlCall("sshlpk.getSshKey",array($uid, $id));
}

function getAllSshKey($uid) {
    return xmlCall("sshlpk.getAllSshKey",array($uid));
}

function addSshKey($uid, $value) {
    xmlCall("sshlpk.addSshKey",array($uid, $value));
}

function updateSshKeys($uid, $keylist) {
    xmlCall("sshlpk.updateSshKeys",array($uid, $keylist));
}

function delSshKey($uid, $value) {
    xmlCall("sshlpk.delSshKey",array($uid, $value));
}

function delSSHKeyObjectClass($uid) {
    xmlCall("sshlpk.delSSHKeyObjectClass", array($uid));
}

?>
