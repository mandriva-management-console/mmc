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


//partage protégé
$protectedShare= array ("","hotbackup","homes","netlogon","public","archive");

require("modules/samba/includes/shares.inc.php");
?>


<style type="text/css">
<!--

<?php
require("modules/samba/graph/shares/index.css");
?>

-->
</style>

<?php
$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
              array("name" => _T("Shares"),
                    "link" => "main.php?module=samba&submod=shares&action=index"),
              array("name" => _T("Management")));

require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

global $conf;

if (!isset($_GET["items"]))
{
  if (!isset($_POST["start"]))
  {
    $shares = get_shares_detailed();
    $start = 0;

    if (count($shares) > 0)
      {
	$end = $conf["global"]["maxperpage"] - 1;
      }
    else
      {
	$end = 0;
      }
  }
}
else
{
  $shares = unserialize(base64_decode(urldecode($_GET["items"])));
}
if (isset($_GET["start"]))
{
$start = $_GET["start"];
$end = $_GET["end"];
}

?>

<h2><?= _T("Shares"); ?></h2>

<div class="fixheight"></div>

<?php
print_nav($start, $end, $shares);
global $maxperpage; //definition globale
?>

<p class="listInfos">

<?php

global $maxperpage;
printf(_T("Shares <strong>%s</strong>
to <strong>%s</strong>
on a total of <strong>%s</strong>
(page %s on "),min(($start + 1), count($shares)),min(($end + 1), count($shares)),count($shares),sprintf("%.0f", ($end + 1) / $maxperpage));

?>
<?php
 $pages = count($shares) / $maxperpage;
 if ((count($shares) % $maxperpage > 0) && (count($shares) > $maxperpage))
   {
     $pages++;
   }
 if ((count($shares) > 0) && ($pages < 1))
   {
     $pages = 1;
   }
 printf("%.0f", $pages);
?>
)
</p>

<table border="1" cellspacing="0" cellpadding="5">


<?php

for ($idx = $start;
     ($idx < count($shares)) && ($idx <= $end);
     $idx++)
{
  if (($start - $idx) % 2)
    {
      echo "<tr>";
    }
  else
    {
      echo "<tr class=\"alternate\">";
    }

  echo "<td class=\"shareName\">".$shares[$idx][0]."</td>";
  echo "<td>".$shares[$idx][1]."</td>";
  echo "<td class=\"shareAction\">";
  echo "<ul class=\"action\">";
  echo "<li class=\"edit\">";
  if(array_search($shares[$idx][0],$protectedShare)==null) {
    echo "<a title=\"Propriétés\" href=\"main.php?module=samba&submod=shares&action=details&share=".urlencode($shares[$idx][0])."\">.</a></li>";
    //echo "<li class=\"supprimer\"><a title=\"Supprimer\" href=\"main.php?module=samba&submod=shares&action=delete&share=".urlencode($shares[$idx][0])."\">.</a></li>";
      $a = new ActionPopupItem(_T("Delete"),"delete","supprimer","share");
      $a->display(urlencode($shares[$idx][0]));

    }
  $a = new ActionPopupItem(_T("Archive"),"backup","archiver","share");
  $a->display(urlencode($shares[$idx][0]));
  echo "</ul>";
  echo "</td>";
  echo "</tr>\n";
}
?>

</table>

<?php
print_nav($start, $end, $shares);
?>
