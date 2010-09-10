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

require("modules/samba/includes/shares.inc.php");
require("modules/samba/includes/samba.inc.php");

if (isset($_POST["brestart"])) {
    header("Location: " . urlStrRedirect("samba/config/restart"));
    exit;
} else if (isset($_POST["breload"])) {
    header("Location: " . urlStrRedirect("samba/config/reload"));
    exit;    
}

function get_smbconf() {
    $smbInfo = xmlCall("samba.getSmbInfo", null);
    return $smbInfo;
}

function save_smbconf() {
    $ispdc = $_POST["pdc"];
    $homes = $_POST["homes"];
    $wg = $_POST["workgroup"];
    $netname = $_POST["netbios name"];

    $param=array($ispdc,$homes,$wg,$netname);

    foreach ($_POST as $key => $value)
        $_POST[str_replace("_", " ", $key)] = stripslashes($value);

    xmlCall("samba.smbInfoSave", array($ispdc, $homes, $_POST));
    return _T("Configuration saved");
}

function getCheckedState($smb, $option) {
    $ret = "";
    if (isset($smb["ldap passwd sync"])) {
        if (strtolower($smb[$option]) == "yes") 
            $ret = "CHECKED";
        }
    return $ret;
}

if (isset($_POST["bsave"])) {
    $result = save_smbconf();
    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_T("SAMBA configuration saved. You may need to reload or restart the SAMBA service."), $shareName));
    }
}

require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

$p = new PageGenerator(_T("General options"));
$p->setSideMenu($sidemenu);
$p->display();

$smb = get_smbconf();

$f = new ValidatingForm();
$f->push(new Table());
if ($smb["pdc"]) $value = "CHECKED";
else $value = "";
$f->add(
        new TrFormElement(_T("This server is a PDC"),new CheckboxTpl("pdc")),
        array("value" => $value)
        );
$f->add(
        new TrFormElement(_T("This server is a WINS server"),new CheckboxTpl("wins support")),
        array("value" => getCheckedState($smb, "wins support"))
        );
if ($smb["homes"]) $value = "CHECKED";
else $value = "";
$f->add(
        new TrFormElement(_T("Share user's homes"),new CheckboxTpl("homes")),
        array("value" => $value)
        );
$f->add(
        new TrFormElement(_T("Domain name"), new NetbiosUppercaseInputTpl("workgroup")),
        array("value" => $smb["workgroup"], "required" => True)
        );
$f->add(
        new TrFormElement(_T("Server name"), new NetbiosUppercaseInputTpl("netbios name")),
        array("value" => $smb["netbios name"], "required" => True)
        );
$f->pop();


$f->push(new DivExpertMode());
$f->push(new Table());
$f->add(
        new TrFormElement(_T("LDAP password sync"), new CheckboxTpl("ldap passwd sync")),
        array("value" => getCheckedState($smb, "ldap passwd sync"))
        );
$d = array(_T("User profile path") => "logon path",
           _T("Opening script session") => "logon script",
           _T("Base directory path") => "logon home",
           _T("Connect base directory on network drive") => "logon drive");

foreach ($d as $description => $field) {
    $f->add(
            new TrFormElement($description, new IA5InputTpl($field)),
            array("value"=>$smb[$field])
            );
}
$f->pop();
$f->pop();

$f->addValidateButton("bsave");
$f->addExpertButton("brestart", _T("Restart SAMBA"));
$f->addButton("breload", _T("Reload SAMBA configuration"));

$f->display();

?>
