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

require("graph/header.inc.php");

?>

<!-- D�inition de styles locaux �cette page -->
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
$path = array(array("name" => _T("Restart server")));
$topLeft = 1;

/* Inclusion de la bar de navigation */
require("graph/navbar.inc.php");

if (isset($_GET["goto"]))
{
  $goto = $_GET["goto"];
}
else if (isset($_POST["goto"]))
{
  $goto = $_POST["goto"];
}
else
{
  $goto = $root."main.php";
}

?>


<h2><?= _T("Restart server"); ?></h2>

<?php
  echo "<pre>";
  print_r (xmlCall("samba.restartSamba",null));
  echo "</pre>";

  $n = new NotifyWidget();
  $n->add(_T("Your Samba server is being restarted... You can see progress on the home page."));
  redirectTo(urlstr("samba/config/index"));
?>

<form method="post" action="main.php?module=samba&submod=shares&action=index">
<input name="bback" type="submit" class="btnPrimary" value="<?= _T("Back"); ?>" />
</form>
