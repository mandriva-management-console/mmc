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

?>

<!-- D�finition de styles locaux � cette page -->
<style type="text/css">
<!--

#section, #sectionTopRight, #sectionBottomLeft
{
	margin: 0 0 0 17px;
}

#sectionTopRight {
        border-left: none;
}

#sectionTopLeft {
    height: 9px;
        padding: 0;
        margin: 0;
        background: url("../img/common/sectionTopLeft.gif") no-repeat top left transparent;
}

-->
</style>

<?php
$path = array(array("name" => _T("Restart squidGuard server")));
$topLeft = 1;

/* Inclusion de la bar de navigation */
require("graph/navbar.inc.php");

  $goto = $root."main.php?module=proxy&submod=blacklist&action=index";

?>


<h2><?php echo  _T('Restart squidGuard server'); ?></h2>

<?php

$file=$conf["proxy"]["squidguard"];


echo xmlCall("proxy.restartSquid",null);
?>

<form method="post" action="main.php?module=proxy&submod=blacklist&action=statut">
<input name="bback" type="submit" class="btnPrimary" value="<?php echo  _('Back'); ?>" />
</form>
