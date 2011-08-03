<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com
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
 * Form on user edit page
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _sshlpk_baseEdit($FH, $mode) {

    // default value
    $hassshlpk = '';

    if ($mode == 'edit' && hasSshKeyObjectClass($FH->getArrayOrPostValue("uid")))
        $hassshlpk = 'checked';

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

    $sshkeylist = array();
    if ($FH->getArrayOrPostValue("uid")) {
        if ($hassshlpk == 'checked') {
            $sshkeylist = getAllSshKey($FH->getArrayOrPostValue("uid"));
        }
    }
    if(count($sshkeylist) == 0) {
        $sshkeylist = array("0" => "");
    }

    $f->add(new TrFormElement('', new MultipleInputTpl("sshkeylist",_T("Public SSH Key", "sshlpk"))), $sshkeylist);

    $f->pop();

    $f->pop();
    $f->display();
}

/**
 * Function called before changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _sshlpk_verifInfo($FH, $mode) {

    // Check if keys have been updated
    if ($FH->getValue('sshkeylist')) {
        // make keys unique
        $keys = $FH->getValue('sshkeylist');
        $keys = array_unique($keys);
        $FH->setValue('sshkeylist', $keys);
    }
    
    return 0;
}

/**
 * Function called for changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _sshlpk_changeUser($FH, $mode) {

    global $result;

    if ($FH->getPostValue("showsshkey")) {
        if ($FH->isUpdated('sshkeylist')) {
            updateSshKeys($FH->getPostValue('uid'), $FH->getValue('sshkeylist'));
            $result .= _T("SSH public keys updated.", "sshlpk") . "<br />";
        }
    }
    else {
        if ($mode == 'edit' && hasSshKeyObjectClass($FH->getPostValue('uid'))) {
            delSSHKeyObjectClass($FH->getPostValue('uid'));
            $result .= _T("SSH public keys attributes deleted.", "sshlpk") . "<br />";
        }
    }
    
    return 0;
}

?>
