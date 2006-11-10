<?php
/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id$
 *
 * This file is part of LMC.
 *
 * LMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * LMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>
<?

require("modules/samba/includes/shares.inc.php");

if (isset($_POST["brestart"]))
{
    header("Location: ".$root."modules/base/config/restart.php?goto=".$_SERVER["SCRIPT_NAME"]);
    exit;
}

function
get_smbconf()
{
    $smbInfo = xmlCall("samba.getSmbInfo", null);
    return $smbInfo;
}

function
save_smbconf()
{
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

if (isset($_POST["bsave"]))
{
    $result = save_smbconf();

    // re-read the config, now that it's been changed
    $pdc = xmlCall("samba.isPdc",null);
}


?>



<style type="text/css">
<!--

<?php
require("graph/config/index.css");
?>

-->
</style>

<?php
$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
              array("name" => _T("Shares"),
                    "link" => "main.php?module=samba&submod=shares&action=index"),
              array("name" => _T("General options")));

require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");
?>

<h2><?= _T("General options"); ?></h2>

<div class="fixheight"></div>

<?php

if (!isset($_POST["bsave"]))
{
    $smb = get_smbconf();

    if ($smb["error"])
    {
	echo $smb["error"];
    }
}
else foreach (array("pdc", "homes", "workgroup", "netbios name", "logon script", "logon path", "logon drive", "logon home") as $value)
     $smb[$value] = $_POST[$value];

?>

<form action="<?php echo $PHP_SELF ?>" method="post" name="configList" target="_self">
<table cellspacing="0">
<tr><td width="40%" style="text-align : right;"><?= _T("This server is a PDC"); ?></td>
<td><input name="pdc" type="checkbox" <?php if ($smb["pdc"]) { echo "checked"; } ?> /></td></tr>
<tr><td style="text-align : right;"><?= _T("Share user's homes"); ?></td>
<td><input name="homes" type="checkbox" <?php if ($smb["homes"]) { echo "checked"; } ?> /></td></tr>
<tr><td style="text-align : right;"><?= _T("Domain name"); ?></td>
<td><input name="workgroup" type="text" class="textfield" id="newPrinterName" size="23" value="<?php echo $smb["workgroup"]; ?>" /></td></tr>
<tr><td style="text-align : right;"><?= _T("Server name"); ?></td>
<td><input name="netbios name" type="text" class="textfield" id="newPrinterName" size="23" value="<?php echo $smb["netbios name"]; ?>" /></td></tr>

</table>

<div id="expertMode" <?displayExpertCss();?>>
<table cellspacing="0">
<?php
$d = array(_T("User profile path") => "logon path",
           _T("Opening script session") => "logon script",
           _T("Base directory path") => "logon home",
           _T("Connect base directory on network drive") => "logon drive");

foreach ($d as $description => $field) {
    $test = new TrFormElement($description,
                              new InputTpl($field));
    $test->setCssError($field);
    $test->display(array("value"=>$smb[$field]));
}
?>
</table>
</div>

<input name="bsave" type="submit" class="btnPrimary" value="<?= _T("Confirm"); ?>" />
<input name="bcancel" type="submit" class="btnSecondary" value="<?= _T("Back"); ?>" />

<?php
if (isset($_POST["bsave"]))
{
    echo $result;
}
?>

</form>

<form method="post" action="main.php?module=samba&submod=config&action=restart">
<input name="goto" type="hidden" value="<?php echo $root; ?>main.php" />
<input name="brestart" type="submit" class="btnPrimary" value="<?= _T("Restart SAMBA"); ?>" />
</form>
