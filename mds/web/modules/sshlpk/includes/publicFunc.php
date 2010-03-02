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

require_once("sshlpk-xmlrpc.php");

/**
 * Add form on edit user page
 *
 */
function _sshlpk_baseEdit($ldapArr, $postArr) {
    $hassshlpk = 'checked';
    if ((isset($ldapArr['uid'][0])) && (!hasSshKeyObjectClass($ldapArr['uid'][0]))) {
        $hassshlpk = '';
    }
    $f = new DivForModule(_T("Public SSH keys management plugin","sshlpk"), "#DDF");
    
    $f->push(new Table());
    $f->add(
        new TrFormElement(_T("Enable SSH keys management", "sshlpk"), new CheckboxTpl("showsshkey")),
        array("value"=>$hassshlpk, "extraArg"=>'onclick="toggleVisibility(\'sshkeydiv\');"')
        );
    $f->pop();
    
    $sshkeydiv = new Div(array("id" => "sshkeydiv"));
    $sshkeydiv->setVisibility($hassshlpk);
    $f->push($sshkeydiv);

    if (isset($ldapArr['uid'][0])) {
        $sshkeylist = getAllSshKey($ldapArr['uid'][0]);
        if(count($sshkeylist) == 0)
            $sshkeylist = array("0" => "");
    } else {
        $sshkeylist = array();
        $sshkeylist = array("0" => "");
    }

    $f->add(new TrFormElement('', new MultipleInputTpl("sshkeylist",_T("Public SSH Key", "sshlpk"))), $sshkeylist);

    $f->pop();

    $f->pop();
    $f->display();
}

/**
 * Check POST content
 * @param $postArr $_POST array of the page
 */
function _sshlpk_verifInfo($postArr) {
    global $error;
    /*  test if key already exist */
    if (isset($postArr['sshkeylist'])) {
        $doublekey = '';
        for ( $i = 0 ; $i < count($postArr['sshkeylist']) - 1 ; $i++ ) {
            for ( $j = $i+1 ; $j < count($postArr['sshkeylist']) ; $j++ ) {
                if ($postArr['sshkeylist'][$i] != '' && $postArr['sshkeylist'][$j] != '' 
                    && $postArr['sshkeylist'][$i] == $postArr['sshkeylist'][$j] ) {
                    $postArr['sshkeylist'][$j] = '';
                    $doublekey .= "  ".($i+1)." - ".($j+1)."<br />" ;
                }
            }
        }
        if ($doublekey != '') {
            $error .= _T("Some SSH public keys are duplicate", "sshlpk")."<br />";
            $error .= " (" . $doublekey . ")";
            setFormError("sshlpk");
        }
    }
}

/**
 * function called when change on a user is requested
 * @param $FH FormHandler of the page
 */
function _sshlpk_changeUser($FH) {
    if ($FH->isUpdated('sshkeylist')) {
        updateSshKeys($FH->getPostValue('nlogin'), $FH->getValue('sshkeylist'));
    } else {
        if (hasSshKeyObjectClass($FH->getPostValue('nlogin'))) {
            delSSHKeyObjectClass($FH->getPostValue('nlogin'));
        }
    }
}

?>
