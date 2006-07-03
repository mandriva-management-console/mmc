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
<?php
/* $Id$ */
require("modules/proxy/includes/config.inc.php");
require("modules/proxy/includes/proxy.inc.php");
?>

<?php
$path = array(array("name" => _("Home"),
                    "link" => "main.php"),
              array("name" => _T("Proxy management")));

require("localSidebar.php");

require("graph/navbar.inc.php");
?>

<h2><?= _T('Proxy status');?></h2>

<div class="fixheight"></div>

<?
  global $conf;
  $file = $conf["proxy"]["squidguard"];

$arrayTMP = getStatutProxy();

foreach($arrayTMP as $key => $value)
    {
        $info[]=$key;
        if ($value) {
            $extraInfo[]=_T("enable");
        }
        else {
            $extraInfo[]=_T("disable");
        }
    }

$p = new PageGenerator();

/**
 * Affichage du menu
 */
$p->setSideMenu($sidemenu); //$sidemenu inclus dans localSideBar.php
$p->displaySideMenu();

/**
 * Creation de la liste
 */
$n = new ListInfos($info,_T("Services"));
$n->setName(_T("Proxy status"));
$n->addExtraInfo($extraInfo,_T("Status"));
$n->display(0);

?>
</form>

<form method="post" action="main.php?module=proxy&submod=blacklist&action=restart">
<input name="goto" type="hidden" value="<?php echo $root; ?>main.php" />
<input name="brestart" type="submit" class="btnPrimary" value="<?= _T('Restart service');?>" />
</form>

