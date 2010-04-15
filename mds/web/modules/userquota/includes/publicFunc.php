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
 * display normal edit
 * @param $postArr $_POST array of the page
 * @param $ldapArr ldap array return by getDetailedUser xmlrpc function
 */
function _userquota_baseEdit($ldapArr, $postArr) {
	if (key_exists("objectClass", $ldapArr)) {
		print '<input type="hidden" name="currentOC" value="'.implode(',',$ldapArr["objectClass"]).'" >';
	}
	$components = getActiveComponents();
	if ($components["disk"]) {
		$f = new DivForModule(_T("Quota plugin", "quota"), "#FDD");
		//	$hasQuota = 1;
		//	$f->push(new Table());
		//	$f->add(new TrFormElement(_T("Has Quota", "hasquota"), new CheckboxTpl("hasquota")), array (
		//		"value" => $hasQuota,
		//		"extraArg" => 'onclick="toggleVisibility(\'quotadiv\');"'
		//		));
		//
		//		$f->pop();
		//
		//		$quotadiv = new Div(array (
		//		"id" => "quotadiv"
		//		));
		//		$quotadiv->setVisibility($hasQuota);
		//		$f->push($quotadiv);
		//		$f->add(new UserQuotaTitleElement(_T("Disk quotas (in kB)","userquota")));

		$f->push(new Table());
		displayDiskQuotas(&$f, $ldapArr);

		//$f->add(new TrFormElement(_T("Disk / mount", "userquota"), new InputTpl("quota", '/^[0-9=\/:a-zA-Z]*$/')), array (
		//				"value" => $ldapArr["quota"][0]
		//			));

		$f->pop();

		$f->display();
	}
	if ($components["network"]) {
		$f = new DivForModule(_T("Quota plugin - Network", "quotanetwork"), "#FDD");
		$f->push(new Table());
		displayNetworkQuotas(&$f, $ldapArr);
		$f->pop();
		$f->display();
	}
}

// @todo Join these two into one function.
function displayDiskQuotas(&$f, &$ldapArr) {
    
    $quotas = isset($ldapArr["quota"]) ? $ldapArr["quota"] : array();

	foreach (getDevicemap() as $device) {
		$quota = new DiskQuota($device);
		$quota->setCurrentQuotas($quotas);
		$f->add($quota->getQuotaForm(), array("value"=>$quota->getQuotaSize()));
	}
}

function displayNetworkQuotas(&$f, &$ldapArr) {
	foreach (getNetworkmap() as $network) {
		$quota = new NetworkQuota($network);
		if(isset($ldapArr["networkquota"]))
    		$quota->setCurrentQuotas($ldapArr["networkquota"]);
		$f->add($quota->getQuotaForm(), array("value"=>$quota->getQuotaSize()));
	}
}
/**
 * verification if information
 * @param $postArr $_POST array of the page
 */
function _userquota_verifInfo($postArr) {
	//if ($postArr["quota"]) {

		//        $mailreg='/^([A-Za-z0-9._-]+@[A-Za-z0-9.-]+)$/';
		//        if (!preg_match($mailreg, $postArr["mail"])) {
		//            global $error;
		//            setFormError("mail");
		//            $error.= _T("You must specify a valid mail address to enable mail delivery.","mail")."<br />";
		//        }
	//}
}

/**
 * function call when you submit change on a user
 * @param $postArr $_POST array of the page
 */
function _userquota_changeUser($FH) {
//	$defaults = array();
//	if (!key_exists("currentOC",$postArr)) {
		//		$currentOC = explode(",", $postArr["currentOC"]);
		//		if (!in_array("systemQuotas", $currentOC) {
//		$defaults =	setUserQuotaDefaults($postArr["nlogin"], $postArr["primary_autocomplete"]);
		//		}

//	}
	$components = getActiveComponents();
	if ($components["disk"]) {
		foreach (getDevicemap() as $device) {
			$quota = new DiskQuota($device);
			if ($FH->isUpdated($quota->getQuotaField())) {
			    $quota_value = $FH->getValue($quota->getQuotaField());
				if ($quota_value != "") {
					setDiskQuota($FH->getPostValue("nlogin"), $device, $quota_value);
				}
    			else {
	    			deleteDiskQuota($FH->getPostValue("nlogin"), $device);
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
					setNetworkQuota($FH->getPostValue("nlogin"), $network, $quota_value);
				}
    			else {
	    			deleteNetworkQuota($postArr["nlogin"], $network);
	    		}
	    	}
		}
	}

}



function _userquota_baseGroupEdit($ldapArr, $postArr) {
	$components = getActiveComponents();
	if ($components["disk"]) {
		$f = new DivForModule(_T("Quota plugin group actions", "quota"), "#FFD");
		$f->push(new Table());
		displayDiskQuotas(&$f, $ldapArr);
		$f->add(new TrCommentElement(_T("Quota's applied here affect all members of the group")));
		$overwrite = new RadioTpl("diskoverwrite");
		$overwrite->setChoices(array("Overwrite all existing quotas", "Current quota is smaller than the new quota, or does not exist", "Current quota is larger than the new quota, or does not exist", "Don't overwrite any existing quotas"));
		$overwrite->setValues(array("all", "smaller", "larger", "none"));
		$overwrite->setSelected("none");
		$f->add(new TrFormElement((_T("Overwrite mode for existing quotas")), $overwrite ));
		$f->pop();
		$f->display();
	}
	if ($components["network"]) {
		$f = new DivForModule(_T("Quota plugin - network", "networkquota"), "#FFD");
		$f->push(new Table());
		displayNetworkQuotas(&$f, $ldapArr);
		$f->add(new TrCommentElement(_T("Quota's applied here affect all members of the group")));
		$overwrite = new RadioTpl("networkoverwrite");
		$overwrite->setChoices(array("Overwrite all existing quotas", "Current quota is smaller than the new quota, or does not exist", "Current quota is larger than the new quota, or does not exist", "Don't overwrite any existing quotas"));
		$overwrite->setValues(array("all", "smaller", "larger", "none"));
		$overwrite->setSelected("none");
		$f->add(new TrFormElement((_T("Overwrite mode for existing quotas")), $overwrite ));
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
	//	setUserQuotaDefaults($user, $newgroup);
}

function _userquota_addUserToGroup($user, $group) {
}

function _userquota_delUserFromGroup($user, $group) {
}

function _userquota_delGroup($group) {
}

?>
