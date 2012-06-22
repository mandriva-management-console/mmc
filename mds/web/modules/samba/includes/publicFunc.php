<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com/
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

include ("user-xmlrpc.inc.php");


function _samba_delUser($uid) {
    if (hasSmbAttr($uid)) rmSmbAttr($uid);
}

function _samba_enableUser($paramsArr) {
    if (hasSmbAttr($paramsArr)) return xmlCall("samba.enableUser", $paramsArr);
}

function _samba_disableUser($paramsArr) {
    if (hasSmbAttr($paramsArr)) return xmlCall("samba.disableUser", $paramsArr);
}

function _samba_changeUserPasswd($paramsArr) {
    if (hasSmbAttr($paramsArr[0])) return xmlCall("samba.changeUserPasswd", $paramsArr);
}

function _samba_changeUserPrimaryGroup($uid, $group) {
    if (hasSmbAttr($uid)) return xmlCall("samba.changeUserPrimaryGroup",array($uid, $group));
}

function _samba_changePasswordPolicy($FH, $mode) {
    return xmlCall("samba.setDomainPolicy");
}


/**
 * Function called for changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _samba_changeUser($FH, $mode) {

    global $error;
    global $result;

    # check users profiles setup
    $globalProfiles = xmlCall("samba.isProfiles");

    # Already existing SAMBA user
    if  (hasSmbAttr($FH->getPostValue("uid"))) {
        # Remove atrributes
        if (!$FH->getPostValue("isSamba")) {
            rmSmbAttr($FH->getPostValue("uid"));
            $result .= _T("Samba attributes deleted.","samba")."<br />";
        }
        # Update attributes
        else {
            # Remove passwords from the $_POST array coming from the add/edit user page
            # because we don't want to send them again via RPC.
            $FH->delPostValue("pass");
            $FH->delPostValue("confpass");

            // Format samba attributes
            if($FH->isUpdated("sambaPwdMustChange")) {
                if($FH->getValue("sambaPwdMustChange") == "on") {
                    // force user to change password
                    $FH->setValue("sambaPwdMustChange", "0");
                    $FH->setValue("sambaPwdLastSet", "0");
                }
                else {
                    $FH->setValue("sambaPwdMustChange", "");
                    $FH->setValue("sambaPwdLastSet", (string)time());
                }
            }
            // account expiration
            if($FH->isUpdated("sambaKickoffTime")) {
                $datetime = $FH->getValue("sambaKickoffTime");
                // 2010-09-23 18:32:00
                if (strlen($datetime) == 19) {
                    $timestamp = mktime(substr($datetime, -8, 2), substr($datetime, -5, 2), substr($datetime, -2, 2), substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4));
                    $FH->setValue("sambaKickoffTime", "$timestamp");
                }
                // not a valid value
                else {
                    $FH->setValue("sambaKickoffTime", "");
                }
            }
            // Network profile path
            if($FH->isUpdated("sambaProfilePath") && !$globalProfiles) {
                $FH->setValue("sambaProfilePath", $FH->getPostValue("sambaProfilePath"));
            }

            // change attributes
            changeSmbAttr($FH->getPostValue("uid"), $FH->getValues());

            if (isEnabledUser($FH->getPostValue("uid"))) {
                if ($FH->getPostValue('isSmbDesactive')) {
                    smbDisableUser($FH->getPostValue("uid"));
                    $result .= _T("Samba account disabled.","samba")."<br />";
                }
            } else {
                if (!$FH->getPostValue('isSmbDesactive')) {
                    smbEnableUser($FH->getPostValue("uid"));
                    $result .= _T("Samba account enabled.","samba")."<br />";
                }
            }
            if (isLockedUser($FH->getPostValue("uid"))) {
                if (!$FH->getPostValue('isSmbLocked')) {
                    smbUnlockUser($FH->getPostValue("uid"));
                }
            } else {
                if ($FH->getPostValue('isSmbLocked')) {
                    smbLockUser($FH->getPostValue("uid"));
                }
            }
        }
    }
    else { //if not have smb attributes
        if ($FH->getPostValue("isSamba")) {
            # Add SAMBA attributes
            addSmbAttr($FH->getPostValue("uid"), $FH->getPostValue("pass"));
            if(!isXMLRPCError()) {
                // Format samba attributes
                if($FH->getPostValue("sambaPwdMustChange") == "on") {
                    $FH->setPostValue("sambaPwdMustChange", "0");
                    $FH->setPostValue("sambaPwdLastSet", "0");
                }
                else {
                    $FH->setPostValue("sambaPwdMustChange", "");
                }
                // Account expiration
                if($FH->isUpdated("sambaKickoffTime")) {
                    $datetime = $FH->getValue("sambaKickoffTime");
                    // 2010-09-23 18:32:00
                    if (strlen($datetime) == 19) {
                        $timestamp = mktime(substr($datetime, -8, 2),
                            substr($datetime, -5, 2), substr($datetime, -2, 2),
                            substr($datetime, 5, 2), substr($datetime, 8, 2),
                            substr($datetime, 0, 4));
                        $FH->setPostValue("sambaKickoffTime", "$timestamp");
                    }
                    // not a valid value
                    else {
                        $FH->setPostValue("sambaKickoffTime", "");
                    }
                }
                // Network profile
                // Clear profile path if global profiles are on
                if(!$FH->getPostValue("hasProfile") == "on" || $globalProfiles) {
                    $FH->setPostValue("sambaProfilePath", "");
                }

                changeSmbAttr($FH->getPostValue("uid"), $FH->getPostValues());
                if(!isXMLRPCError())
                    $result .= _T("Samba attributes added.","samba")."<br />";
                else
                    $error .= _T("Failed to add Samba attributes.","samba")."<br />";
            }
            else {
                // rollback operation
                global $errorStatus;
                $errorStatus = 0;
                rmSmbAttr($FH->getPostValue("uid"));
                $error .= _T("Failed to add Samba attributes.","samba")."<br />";
            }
        }
    }

    return 0;
}

/**
 * Function called before changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _samba_verifInfo($FH, $mode) {

    global $error;

    $samba_errors = "";

    if ($FH->getPostValue("uid") && $FH->getPostValue("isSamba")) {
        if (!hasSmbAttr($FH->getPostValue("uid"))) {
            if (!$FH->getValue("pass")) { // if we not precise password
                $samba_errors .= _T("You must reenter your password.","samba")."<br />\n";
                setFormError("pass");
            }
        }
        $drive = $FH->getValue("sambaHomeDrive");
        if ($drive != "") {
            // Check that the SAMBA home drive is a correct drive letter
            // "X:", "U:", etc.
            $err = False;
            if (!preg_match("/^[C-Z]:$/", $drive)) {
                $samba_errors .= _T("Invalid network drive.","samba")."<br />\n";
                setFormError("sambaHomeDrive");
            }
        }
    }

    $error .= $samba_errors;

    return $samba_errors ? 1 : 0;
}

/**
 * Form on user edit page
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _samba_baseEdit($FH, $mode) {

    // default values
    $hasSmb = false;
    $show = true;

    // get smb config info
    $smbInfo = xmlCall("samba.getSmbInfo", null);

    // fetch ldap updated info if we can
    if ($mode == 'edit') {
        $uid = $FH->getArrayOrPostValue("uid");
        if (hasSmbAttr($uid))
            $hasSmb = true;
        else
            $show = false;
        // show Samba plugin in case of error
        if ($FH->getValue("isSamba") == "on")
            $show = true;
    }
    else {
        if ($FH->getValue("isSamba") == "off")
            $show = false;
    }

    $f = new DivForModule(_T("Samba properties","samba"), "#EFE");
    $f->push(new Table());

    $f->add(
        new TrFormElement(_T("SAMBA access","samba"), new CheckboxTpl("isSamba")),
        array("value"=> $show ? "checked" : "", "extraArg"=>'onclick="toggleVisibility(\'smbdiv\');"')
    );

    $f->pop();

    $smbdiv = new Div(array("id" => "smbdiv"));
    $smbdiv->setVisibility($show);

    $f->push($smbdiv);
    $f->push(new Table());

    if ($hasSmb && userPasswdHasExpired($uid)) {
        $formElt = new HiddenTpl("userPasswdHasExpired");
        $f->add(
            new TrFormElement(_("WARNING"), $formElt),
            array("value" => _T("The user password has expired", "samba"))
        );
    }

    $checked = "";
    if (($hasSmb && !isEnabledUser($uid)) || $FH->getArrayOrPostValue('isSmbDesactive') == 'on') {
        $checked = "checked";
        // Display an error message on top of the page
        $em = new ErrorMessage(_T("Samba properties", "samba") . ' : ' .
            _T("This account is disabled", "samba"));
        $em->display();
    }
    $f->add(
        new TrFormElement(_T("User is disabled, if checked","samba"), new CheckboxTpl("isSmbDesactive"),
            array("tooltip" => _T("Disable samba user account",'samba'))),
        array ("value" => $checked)
    );

    $checked = "";
    if (($hasSmb && isLockedUser($uid)) || $FH->getArrayOrPostValue('isSmbLocked') == 'on')
        $checked = "checked";
    $f->add(
        new TrFormElement(_T("User is locked, if checked","samba"), new CheckboxTpl("isSmbLocked"),
            array("tooltip" => _T("Lock samba user access<p>User can be locked after too many failed log.</p>",'samba'))),
        array ("value" => $checked)
    );

    # display this options only if we are PDC
    if($smbInfo["pdc"]) {
        # if no global profile set, we can set a roaming profile for this user
        if(!$smbInfo["logon path"]) {
            $hasProfile = false;
            $checked = "";
            $value = "";
            if($FH->getArrayOrPostValue("sambaProfilePath")) {
                $hasProfile = true;
                $checked = "checked";
                $value = $FH->getArrayOrPostValue('sambaProfilePath');
            }
            $f->add(
                new TrFormElement(_T("Use network profile, if checked","samba"), new CheckboxTpl("hasProfile")),
                array ("value" => $checked, "extraArg" => 'onclick="toggleVisibility(\'pathdiv\')"')
            );

            $f->pop();

            $pathdiv = new Div(array('id' => 'pathdiv'));
            $pathdiv->setVisibility($hasProfile);

            $f->push($pathdiv);
            $f->push(new Table());

            $f->add(
                new TrFormElement(_T("Network path for user's profile","samba"), new InputTpl("sambaProfilePath")),
                array ("value" => $value)
            );

            $f->pop();
            $f->pop();
            $f->push(new Table());

        }

        $checked = "";
        if($FH->getArrayOrPostValue('sambaPwdMustChange') == "0" || $FH->getArrayOrPostValue('sambaPwdMustChange') == "on") $checked = "checked";
        $f->add(
            new TrFormElement(_T("User must change password on next logon, <br/>if checked","samba"), new CheckboxTpl("sambaPwdMustChange")),
            array ("value" => $checked)
        );

        $value = "";
        if($FH->getArrayOrPostValue('sambaKickoffTime'))
            $value = strftime("%Y-%m-%d %H:%M:%S", $FH->getArrayOrPostValue('sambaKickoffTime'));
        $f->add(
            new TrFormElement(_T("Account expiration","samba"), new DynamicDateTpl("sambaKickoffTime"),
                array("tooltip" => _T("Specifies the date when the user will be locked down and cannot login any longer. If this attribute is omitted, then the account will never expire.",'samba'))),
            array ("value" => $value, "ask_for_never" => 1)
        );

        $f->pop();

        // Expert mode display
        $f->push(new DivExpertMode());
        $f->push(new Table());

        $d = array(_T("Opening script session","samba") => "sambaLogonScript",
                   _T("Base directory path","samba") => "sambaHomePath",
                   _T("Connect base directory on network drive","samba") => "sambaHomeDrive");

        foreach ($d as $description => $field) {
            $f->add(
                new TrFormElement($description, new InputTpl($field)),
                array("value" => $FH->getArrayOrPostValue($field))
            );
        }

        $f->pop();
    }
    $f->pop();
    $f->pop();

    return $f;

}

?>
