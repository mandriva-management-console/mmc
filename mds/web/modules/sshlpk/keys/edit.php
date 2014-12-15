<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2014 Mandriva, http://www.mandriva.com
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

require("modules/base/users/localSidebar.php");
require("graph/header.inc.php");
require("graph/navbar.inc.php");

$uid = $_SESSION["login"];

if (isset($_POST['bssh'])) {
    $sshkeys = getAllSshKey($uid);
    $newSshkeys = $_POST['sshuserkeys'];
    $showSshkeys = $_POST['showusersshkey'];

    if ($showSshkeys && $newSshkeys != $sshkeys) {
        updateSshKeys($uid, $newSshkeys);
        if (!isXMLRPCError())
            new NotifyWidgetSuccess(_T("SSH public keys updated.", "sshlpk"));
    }
    else if (!$showSshkeys && hasSshKeyObjectClass($uid)) {
        delSSHKeyObjectClass($uid);
        if (!isXMLRPCError())
            new NotifyWidgetSuccess(_T("SSH public keys attributes deleted.", "sshlpk"));
    }

    redirectTo(urlStrRedirect('base/users/sshkeys'));
}

$p = new PageGenerator(_T("Change your SSH keys", "sshlpk"));
$p->setSideMenu($sidemenu);
$p->display();

$show = hasSshKeyObjectClass($uid);

$f = new ValidatingForm();

$f->push(new Table());
$f->add(
    new TrFormElement(_T("Enable SSH keys management", "sshlpk"),
        new CheckboxTpl("showusersshkey")),
        array("value" => $show ? "checked" : "",
            "extraArg"=>'onclick="toggleVisibility(\'sshkeydiv\');"')
    );
$f->pop();

$sshkeydiv = new Div(array("id" => "sshkeydiv"));
$sshkeydiv->setVisibility($show);
$f->push($sshkeydiv);

if ($show) $sshkeys = getAllSshKey($uid);
else $sshkeys = array("0" => "");

$f->add(new TrFormElement('',
    new MultipleInputTpl("sshuserkeys", _T("Public SSH Key", "sshlpk"))),
    $sshkeys
);

$f->addValidateButton("bssh");
$f->display();

?>
