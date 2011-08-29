<?php
/**
 * (c) 2009 Open Systems Specilists - Glen Ogilvie
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

function getDevicemap() {
	return xmlCall("userquota.getDevicemap");
}

function getNetworkmap() {
	return xmlCall("userquota.getNetworkmap");
}
function setDiskQuota($login, $device, $quota) {
	return xmlCall("userquota.setDiskQuota", array($login, $device, $quota));
}

function deleteDiskQuota($login, $device) {
	return xmlCall("userquota.deleteDiskQuota", array($login, $device));
}

function setNetworkQuota($login, $network, $quota) {
	return xmlCall("userquota.setNetworkQuota", array($login, $network, $quota));
}

function deleteNetworkQuota($login, $network) {
	return xmlCall("userquota.deleteNetworkQuota", array($login, $network));
}


function setGroupDiskQuota($group, $device, $quota, $overwrite) {
	return xmlCall("userquota.setGroupDiskQuota", array($group, $device, $quota, $overwrite));
}

function deleteGroupDiskQuota($cn, $device) {
	return xmlCall("userquota.deleteGroupDiskQuota", array($cn, $device));
}

function setGroupNetworkQuota($group, $device, $quota, $overwrite) {
	return xmlCall("userquota.setGroupNetworkQuota", array($group, $device, $quota, $overwrite));
}

function deleteGroupNetworkQuota($cn, $device) {
	return xmlCall("userquota.deleteGroupNetworkQuota", array($cn, $device));
}

function getActiveComponents() {
	return xmlCall("userquota.getActiveComponents");
}

/*
 * Set the quota defaults for the primary group
 * @param user, group
 */

function setUserQuotaDefaults($user,$group) {
	return xmlCall("userquota.setUserQuotaDefaults", array($user,$group));
}

?>