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
    $options = array();

    # sanitize POST values
    foreach ($_POST as $key => $value)
        if (!in_array($key, array('logon_home', 'logon_path')))
            $options[str_replace("_", " ", $key)] = stripslashes($value);
        else
            $options[str_replace("_", " ", $key)] = $value;

    if(!isset($_POST['hasprofiles']))
        $options['hasprofiles'] = false;
    else
        $options['hasprofiles'] = true;
    if(!isset($_POST['hashomes']))
        $options['hashomes'] = false;
    else
        $options['hashomes'] = true;
    if(!isset($_POST['pdc']))
        $options['pdc'] = false;
    else
        $options['pdc'] = true;
    # apply samba options
    return xmlCall("samba.smbInfoSave", array($options));
}

function getCheckedState($smb, $option) {
    $ret = "";
    if (strtolower($smb[$option]) == "yes")
        $ret = "checked";
    return $ret;
}

if (isset($_POST["bsave"])) {
    $ret = save_smbconf();
    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_T("SAMBA configuration saved. You may need to reload or restart the SAMBA service.")));
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

if ($smb["pdc"])
    $workgroupTpl = new HiddenTpl("workgroup");
else
    $workgroupTpl = new NetbiosUppercaseInputTpl("workgroup");

$f->add(
        new TrFormElement(_T("Domain name"), $workgroupTpl),
        array("value" => $smb["workgroup"], "required" => True)
);

$f->add(
        new TrFormElement(_T("Server name"), new NetbiosUppercaseInputTpl("netbios name")),
        array("value" => $smb["netbios name"], "required" => True)
);

$value = "";
if ($smb["pdc"]) $value = "checked";
$f->add(
        new TrFormElement(_T("This server is a PDC"),new CheckboxTpl("pdc")),
        array("value" => $value)
);

$f->add(
        new TrFormElement(_T("This server is a WINS server"),new CheckboxTpl("wins support")),
        array("value" => getCheckedState($smb, "wins support"))
);

$value = "";
if ($smb["hashomes"]) $value = "checked";
$f->add(
        new TrFormElement(_T("Share user's homes"),new CheckboxTpl("hashomes")),
        array("value" => $value)
);

$value = "";
$hasProfiles = false;
if ($smb['logon path']) {
    $value = "checked";
    $hasProfiles = true;
}
$f->add(
        new TrFormElement(_T("Use network profiles for users"), new CheckboxTpl("hasprofiles"),
            array("tooltip" => _T("Activate roaming profiles for all users.", "samba"))),
        array("value" => $value, "extraArg" => 'onclick=toggleVisibility("profilespath")')
);

$f->pop();

$pathdiv = new Div(array("id" => "profilespath"));
$pathdiv->setVisibility($hasProfiles);

$f->push($pathdiv);
$f->push(new Table());

# default value for profile path
$value = "\\\\%N\\profiles\\%U";
if($hasProfiles) $value = $smb['logon path'];
$f->add(
        new TrFormElement(_T("Network path for profiles"), new InputTpl("logon path"),
            array("tooltip" => _T("The share must exist and be world-writable.", "samba"))),
        array("value" => $value)
);
$f->pop();
$f->pop();

$f->push(new DivExpertMode());
$f->push(new Table());

$syncTpl = new SelectItem("ldap passwd sync");
$labels = array(_T('Yes'), _T('No'), _T('Only (for smbk5pwd)'));
$values = array('yes', 'no', 'only');
$syncTpl->setElements($labels);
$syncTpl->setElementsVal($values);
$f->add(
        new TrFormElement(_T("LDAP password sync"), $syncTpl),
        array("value" => $smb["ldap passwd sync"])
);

$d = array(_T("Opening script session") => "logon script",
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
