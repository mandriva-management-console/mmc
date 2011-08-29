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
<?php
/* $Id$ */
require("modules/proxy/includes/config.inc.php");
require("modules/proxy/includes/proxy.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");
?>

<h2><?php echo  _T('Proxy status'); ?></h2>

<div class="fixheight"></div>

<?
$arrayTMP = getStatutProxy();

foreach($arrayTMP as $key => $value) {
    $info[]=$key;
    if ($value) $extraInfo[]=_T("enabled");
    else $extraInfo[]=_T("disabled");
}

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

$n = new ListInfos($info,_T("Services"));
$n->setName(_T("Proxy status"));
$n->addExtraInfo($extraInfo,_T("Status"));
$n->display(0);

?>

<form method="post" action="main.php?module=proxy&amp;submod=blacklist&amp;action=restart">
<input name="goto" type="hidden" value="<?php echo $root; ?>main.php" />
<input name="brestart" type="submit" class="btnPrimary" value="<?php echo  _T('Restart service'); ?>" />
</form>

