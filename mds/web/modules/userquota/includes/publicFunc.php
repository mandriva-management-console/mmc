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

require_once("userquota-xmlrpc.php");
require_once("userquota.php");

/**
 * Form on user edit page
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _userquota_baseEdit($FH, $mode) {
	
	/*if (key_exists("objectClass", $ldapArr)) {
		print '<input type="hidden" name="currentOC" value="'.implode(',',$ldapArr["objectClass"]).'" >';
	}*/
	$components = getActiveComponents();
	if ($components) {
        $f = new DivForModule(_T("Quota settings", "userquota"), "#FDD");
	
        if (isset($components["disk"])) {
    		$f->push(new Table());
	    	$f = addDiskQuotas($f, $FH);
			$f->pop();
	    }
        if (isset($components["network"])) {
    		$f->push(new Table());
		    $f = addNetworkQuotas($f, $FH);
    		$f->pop();
	    }
	}
	
	return $f;
}

// @todo Join these two into one function.
function addDiskQuotas($f, $FH) {
    
    $quotas = $FH->getArrayOrPostValue("quota", "array");

	foreach (getDevicemap() as $device) {
		$quota = new DiskQuota($device);
		$quota->setCurrentQuotas($quotas);
		$f->add($quota->getQuotaForm(), array("value"=>$quota->getQuotaSize()));
	}
	
	return $f;
}

function addNetworkQuotas($f, $FH) {

    $quotas = $FH->getArrayOrPostValue("networkquota", "array");

	foreach (getNetworkmap() as $network) {
		$quota = new NetworkQuota($network);
		$quota->setCurrentQuotas($quotas);
		$f->add($quota->getQuotaForm(), array("value"=>$quota->getQuotaSize()));
	}
	
	return $f;
}
/**
 * verification if information
 * @param $postArr $_POST array of the page
 */
function _userquota_verifInfo($FH, $mode) {

    return 0;

}

/**
 * function call when you submit change on a user
 * @param $postArr $_POST array of the page
 */
function _userquota_changeUser($FH, $mode) {

    global $return;
    
    $uid = $FH->getPostValue("uid");

	$components = getActiveComponents();
	if ($components["disk"]) {
		foreach (getDevicemap() as $device) {
			$quota = new DiskQuota($device);
			if ($FH->isUpdated($quota->getQuotaField())) {
			    $quota_value = $FH->getValue($quota->getQuotaField());
				if ($quota_value != "") {
					setDiskQuota($uid, $device, $quota_value);
					$result .= sprintf(_T("Disk quota set to %s.", "userquota"), $quota_value) . '<br />';
				}
    			else {
	    			deleteDiskQuota($uid, $device);
					$result .= _T("Disk quota removed.", "userquota") . '<br />';
	    		}
	    	}
	    }
	}
	if ($components["network"]) {
		foreach (getNetworkmap() as $network) {
			$quota = new NetworkQuota($network);
			if ($FH->isUpdated($quota->getQuotaField())) {
			    $quota_value = $FH->getValue($quota->getQuotaField());
				if ($quota_value != "") {
					setNetworkQuota($uid, $network, $quota_value);
					$result .= sprintf(_T("Network quota set to %s on %s.", "userquota"), $quota_value, $network) . '<br />';					
				}
    			else {
	    			deleteNetworkQuota($uid, $network);
					$result .= _T("Network quota removed.", "userquota") . '<br />';
	    		}
	    	}
		}
	}
	
	return 0;

}



function _userquota_baseGroupEdit($ldapArr, $postArr) {
	$components = getActiveComponents();
	if ($components["disk"]) {
		$f = new DivForModule(_T("Quota plugin - Filesystem", "userquota"), "#FFD");
		$f->push(new Table());
		displayDiskQuotas(&$f, $ldapArr);
		$f->add(new TrCommentElement(_T("Quota's applied here affect all members of the group", "userquota")));
		$overwrite = new RadioTpl("diskoverwrite");
		$overwrite->setChoices(array(_T("Overwrite all existing quotas", "userquota"), _T("Current quota is smaller than the new quota, or does not exist", "userquota"), _T("Current quota is larger than the new quota, or does not exist", "userquota"), _T("Don't overwrite any existing quotas", "userquota")));
		$overwrite->setValues(array("all", "smaller", "larger", "none"));
		$overwrite->setSelected("none");
		$f->add(new TrFormElement((_T("Overwrite mode for existing quotas", "userquota")), $overwrite ));
		$f->pop();
		$f->display();
	}
	if ($components["network"]) {
		$f = new DivForModule(_T("Quota plugin - Network", "userquota"), "#FFD");
		$f->push(new Table());
		displayNetworkQuotas(&$f, $ldapArr);
		$f->add(new TrCommentElement(_T("Quota's applied here affect all members of the group", "userquota")));
		$overwrite = new RadioTpl("networkoverwrite");
		$overwrite->setChoices(array(_T("Overwrite all existing quotas", "userquota"), _T("Current quota is smaller than the new quota, or does not exist", "userquota"), _T("Current quota is larger than the new quota, or does not exist", "userquota"), _T("Don't overwrite any existing quotas", "userquota")));
		$overwrite->setValues(array("all", "smaller", "larger", "none"));
		$overwrite->setSelected("none");
		$f->add(new TrFormElement((_T("Overwrite mode for existing quotas", "userquota")), $overwrite ));
		$f->pop();
		$f->display();
	}
}

/**
 * function called when you submit while editing a group
 * @param $postArr $_POST array of the page
 */
function _userquota_changeGroup($postArr) {
	$components = getActiveComponents();
	if ($components["disk"]) {
		foreach (getDevicemap() as $device) {
			$quota = new DiskQuota($device);
			if (!empty($postArr[$quota->getQuotaField()])) {
				if (strlen($postArr[$quota->getQuotaField()]) > 0)	{
					setGroupDiskQuota($postArr["groupname"], $device, $postArr[$quota->getQuotaField()], $postArr['diskoverwrite']);
				}
			}
			else {
				//			xmlCall("userquota.longTest");
				if ($postArr["diskoverwrite"] == "all")
					deleteGroupDiskQuota($postArr["groupname"], $device);
			}
		}
	}
	if ($components["network"]) {
		foreach (getNetworkmap() as $network) {
			$quota = new NetworkQuota($network);
			if (!empty($postArr[$quota->getQuotaField()])) {
				if (strlen($postArr[$quota->getQuotaField()]) > 0)	{
					setGroupNetworkQuota($postArr["groupname"], $network, $postArr[$quota->getQuotaField()],$postArr['networkoverwrite']);
				}
			}
			else {
				if ($postArr["networkoverwrite"] == "all")
					deleteGroupNetworkQuota($postArr["groupname"], $network);
			}
		}
	}
}

function _userquota_changeUserPrimaryGroup($user, $newgroup, $oldgroup) {
}

function _userquota_addUserToGroup($user, $group) {
}

function _userquota_delUserFromGroup($user, $group) {
}

function _userquota_delGroup($group) {
}

?>
