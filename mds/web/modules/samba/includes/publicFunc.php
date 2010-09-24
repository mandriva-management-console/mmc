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
<?

include ("user-xmlrpc.inc.php");

function _samba_infoUser($userObjClass) {
    // FIXME: test should be case insensitive
    if (array_search("sambaSamAccount",$userObjClass) || array_search("sambaSAMAccount",$userObjClass))
        print "sambaAccount ";
}

function _samba_delUser($uid) {
    if (hasSmbAttr($uid)) rmSmbAttr($uid);
}

function _samba_enableUser($uid) {
    if (hasSmbAttr($uid)) xmlCall("samba.enableUser", array($uid));
}

function _samba_disableUser($uid) {
    if (hasSmbAttr($uid)) xmlCall("samba.disableUser", array($uid));
}

function _samba_changeUserPasswd($paramsArr) {
    if (hasSmbAttr($paramsArr[0])) return xmlCall("samba.changeUserPasswd", $paramsArr);
}

function _samba_changeUserPrimaryGroup($uid, $group) {
    if (hasSmbAttr($uid)) xmlCall("samba.changeUserPrimaryGroup",array($uid, $group));
}
 

function _samba_changeUser($FH) {

    global $error;
    if ($error) return -1;

    # check users profiles setup
    $hasProfiles = xmlCall("samba.isProfiles");

    if  (hasSmbAttr($FH->getPostValue("nlogin"))) { //if it is an smbUser
        if (!$FH->getPostValue("isSamba")) {
            // Removing all SAMBA attributes
            rmSmbAttr($FH->getPostValue("nlogin"));
            global $result;
            $result .= _T("Samba attributes deleted.","samba")."<br />";
        } else {
            // Change SAMBA attributes
            /*
             * Remove passwords from the $_POST array coming from the add/edit user page
             * because we don't want to send them again via RPC.
             */
            $FH->delPostValue("pass");
            $FH->delPostValue("confpass");

            // format samba attributes
            if($FH->isUpdated("sambaPwdLastSet")) {
                if($FH->getValue("sambaPwdLastSet") == "on") {
                    // force user to change password
                    $FH->setValue("sambaPwdLastSet", "0");
                }
                else {
                    $FH->setValue("sambaPwdLastSet", "9999999999");
                }
            }
            if($FH->isUpdated("sambaPwdCanChange")) {
                if($FH->getValue("sambaPwdCanChange") == "on") {
                    // del this attribute
                    $FH->setValue("sambaPwdCanChange", "");
                }
                else {
                    // user can't change password before this timestamp
                    $FH->setValue("sambaPwdCanChange", "9999999999");
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
            if($FH->isUpdated("hasProfile")) {
                if($FH->getValue("hasProfile") == "on" && !$hasProfiles) {
                    $FH->setValue("sambaProfilePath", $FH->getPostValue("sambaProfilePath"));
                }
                else {
                    $FH->setValue("sambaProfilePath", "");
                }
            }
            # FIXME !
            # need to find a way to disable roaming profile for user when logon path is set
            /*
                else if(($FH->getValue("sambaProfilePath") == "on" && $globalProfiles) || !$globalProfiles) {
                    # sambaProfilePath is useless
                    $FH->setValue("sambaProfilePath", "");                
                }
                else {
                    # desactivate profile for this user                    
                    $FH->setValue("sambaProfilePath", '%USERPROFILE%');
                }
            }*/
            
            // change attributes
            changeSmbAttr($FH->getPostValue("nlogin"), $FH->getValues());
            
            if (isEnabledUser($FH->getPostValue("nlogin"))) {
                if ($FH->getPostValue('isSmbDesactive')) {
                    smbDisableUser($FH->getPostValue("nlogin"));
                }
            } else {
                if (!$FH->getPostValue('isSmbDesactive')) {
                    smbEnableUser($FH->getPostValue("nlogin"));
                }
            }
            if (isLockedUser($FH->getPostValue("nlogin"))) {
                if (!$FH->getPostValue('isSmbLocked')) {
                    smbUnlockUser($FH->getPostValue("nlogin"));
                }
            } else {
                if ($FH->getPostValue('isSmbLocked')) {
                    smbLockUser($FH->getPostValue("nlogin"));
                }
            }
        }
    }
    else { //if not have smb attributes
        if ($FH->getPostValue("isSamba")) {
            global $result;
            $result.=_T("Samba attributes added.","samba")."<br />";
            # if the user password doesn't match the pwd policies
            # we set a random password for the samba user
            if($FH->getValue("randomSmbPwd") == 1) {
                $FH->setPostValue("pass", uniqid(rand(), true));
            }
            addSmbAttr($FH->getPostValue("nlogin"),$FH->getPostValue("pass"));

            // format samba attributes
            // FIXME
            // duplicate with edit phase
            if($FH->getPostValue("sambaPwdLastSet") == "on") {
                $FH->setPostValue("sambaPwdLastSet", "0");
            }
            if($FH->getPostValue("sambaPwdCanChange") == "on") {
                $FH->setPostValue("sambaPwdCanChange", "");
            }
            else {
                $FH->setPostValue("sambaPwdCanChange", "9999999999");
            }
            // account expiration
            if($FH->isUpdated("sambaKickoffTime")) {
                $datetime = $FH->getValue("sambaKickoffTime"); 
                // 2010-09-23 18:32:00
                if (strlen($datetime) == 19) {
                    $timestamp = mktime(substr($datetime, -8, 2), substr($datetime, -5, 2), substr($datetime, -2, 2), substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4));
                    $FH->setPostValue("sambaKickoffTime", "$timestamp");
                }
                // not a valid value
                else {
                    $FH->setPostValue("sambaKickoffTime", "");
                }
            }           
            if($FH->isUpdated("sambaProfilePath")) {
                if($FH->getValue("sambaProfilePath") == "on" && !$globalProfiles) {
                    $smbconf = xmlCall("samba.getSmbInfo");
                    $FH->setPostValue("sambaProfilePath", "\\\\".$smbconf["netbios name"]."\\".$FH->getPostValue("nlogin")."\\profile");
                }
                else {
                    $FH->setPostValue("sambaProfilePath", "");
                }
            }
            changeSmbAttr($FH->getPostValue("nlogin"), $FH->getPostValues());
        }
    }
}

function _samba_verifInfo($postArr) {

    if (isset($postArr["isSamba"])) {
        if (!hasSmbAttr($postArr["nlogin"])) {
            if ($postArr["pass"]=="") { //if we not precise password
                global $error;
                $error.= _T("You must reenter your password.","samba")."<br />\n";
                setFormError("pass");
                return -1;
            }
        }
        $drive = $postArr["sambaHomeDrive"];
        if ($drive != "") {

            // Check that the SAMBA home drive is a correct drive letter
            // "X:", "U:", etc.
            $err = False;
            //if (strlen($drive) != 2) $err = True;
            //elseif ($drive{1} != ":") $err = True;
            if (!preg_match("/^[C-Z]:$/", $drive)) {
                global $error;
                $error .= _T("Invalid network drive.","samba")."<br />\n";
                setFormError("sambaHomeDrive");
                return -1;
            }
        }
    }
}

function _samba_baseEdit($ldapArr,$postArr) {

    // default values
    $checked = "checked";
    $hasSmb = true;
    
    // get smb config info
    $smbInfo = xmlCall("samba.getSmbInfo", null);

    // fetch ldap updated info if we can
    if (isset($ldapArr["uid"][0])) {
        $uid = $ldapArr["uid"][0];
        if (!hasSmbAttr($ldapArr["uid"][0])) {
            $checked = "";
            $hasSmb = false;
        }
    }

    // if we update a user but error when updating
    if (isset($postArr["buser"])) {
        if (isset($postArr["isSamba"])) {
            $checked = "checked";
            $hasSmb = true;
        } else {
            $checked = "";
            $hasSmb = false;
        }
    }
    
    $f = new DivForModule(_T("Samba user properties","samba"), "#EFE");    
    $f->push(new Table());
    
    $f->add(
        new TrFormElement(_T("SAMBA access","samba"), new CheckboxTpl("isSamba")),
        array("value"=>$checked, "extraArg"=>'onclick="toggleVisibility(\'smbdiv\');"')
    );

    $f->pop();
    
    $smbdiv = new Div(array("id" => "smbdiv"));
    $smbdiv->setVisibility($hasSmb);

    $f->push($smbdiv);
    $f->push(new Table());    

    $checked = "";
    $smbuser = False;
    if (isset($ldapArr["uid"][0])) {
        $smbuser = hasSmbAttr($ldapArr["uid"][0]);
        if ($smbuser) {
            if (!isEnabledUser($ldapArr["uid"][0])) {
                $checked = "checked";
            }
        }
    }

    if ($smbuser) {
        if (userPasswdHasExpired($ldapArr["uid"][0])) {
            $formElt = new HiddenTpl("userPasswdHasExpired");
            $f->add(
                new TrFormElement(_("WARNING"), $formElt),
                array("value" => _T("The user password has expired", "samba"))
            );
        }
    }

    $f->add(
        new TrFormElement(_T("User is disabled, if checked","samba"), new CheckboxTpl("isSmbDesactive"),
            array("tooltip" => _T("Disable samba user account",'samba'))),
        array ("value" => $checked)
    );

    if ($smbuser) {
        $checked = "";    
        if (isLockedUser($ldapArr["uid"][0])) $checked = "checked";
    }
    $f->add(
        new TrFormElement(_T("User is locked, if checked","samba"), new CheckboxTpl("isSmbLocked"),
            array("tooltip" => _T("Lock samba user access<p>User can be locked after too many failed log.</p>",'samba'))),
        array ("value" => $checked)
    );
    
    # if no global profile set, we can set a roaming profile for this user
    if(!$smbInfo["logon path"]) {
        $hasProfile = false;
        $checked = "";    
        if(isset($ldapArr["sambaProfilePath"])) {
            $hasProfile = true;
            $checked = "checked";
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

        $value = "\\\\".$smbInfo["netbios name"]."\\profiles\\".$ldapArr["uid"][0];
        if(isset($ldapArr["sambaProfilePath"][0])) $value = $ldapArr["sambaProfilePath"][0];
        $f->add(
            new TrFormElement(_T("Network path for user's profile","samba"), new InputTpl("sambaProfilePath")),
            array ("value" => $value)
        );
        
        $f->pop();
        $f->pop();
        $f->push(new Table());
        
    }

    $checked = "";
    if(!isset($ldapArr["sambaPwdCanChange"]) or $ldapArr["sambaPwdCanChange"][0] < mktime()) $checked = "checked";
    $f->add(
        new TrFormElement(_T("User can change password, if checked","samba"), new CheckboxTpl("sambaPwdCanChange")),
        array ("value" => $checked)
    );
    
    $checked = "";
    if($ldapArr["sambaPwdLastSet"][0] == "0") $checked = "checked";
    $f->add(
        new TrFormElement(_T("User must change password on next logon, <br/>if checked","samba"), new CheckboxTpl("sambaPwdLastSet")),
        array ("value" => $checked)
    );

    $value = "";
    if(isset($ldapArr["sambaKickoffTime"][0])) $value = strftime("%Y-%m-%d %H:%M:%S", $ldapArr["sambaKickoffTime"][0]);
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
        $value = "";
        if (isset($ldapArr[$field][0])) $value = $ldapArr[$field][0];
        $f->add(
            new TrFormElement($description, new InputTpl($field)),
            array("value" => $value)
        );
    }
    
    $f->pop();
    $f->pop();
    $f->pop();
    $f->display();

}

?>
