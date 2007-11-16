<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007 Mandriva, http://www.mandriva.com/
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

require("../../../includes/config.inc.php");



require("../../../includes/i18n.inc.php");
//require("../../../modules/base/includes/edit.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");



//require("../../../modules/base/includes/users.inc.php");
//require("../../../modules/base/includes/groups.inc.php");
require ("../../../includes/PageGenerator.php");
require("../../../modules/samba/includes/machines.inc.php");


$root = $conf["global"]["root"];
$maxperpage = $conf["global"]["maxperpage"];

if (!isset($_GET["items"]))
{
  $machines = get_machines($error);
  $start = 0;

  if (count($machines) > 0)
    {
      $end = $conf["global"]["maxperpage"] - 1;
    }
  else
    {
      $end = 0;
    }
}

if (isset($_GET["start"]))
{
$start = $_GET["start"];
$end = $_GET["end"];
}

function
print_ajax_nav($curstart, $curend, $items, $filter)
{
  $_GET["action"] = "index";
  global $conf;

  $max = $conf["global"]["maxperpage"];
  $encitems = urlencode(base64_encode(serialize($items)));

  echo '<form method="post" action="' . $PHP_SELF . '">';
  echo "<ul class=\"navList\">\n";

  if ($curstart == 0)
    {
      echo "<li class=\"previousListInactive\">"._T("Previous")."</li>\n";
    }
  else
    {
      $start = $curstart - $max;
      $end = $curstart - 1;
      echo "<li class=\"previousList\"><a href=\"#\" onClick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._T("Previous")."</a></li>\n";
    }

  if (($curend + 1) >= count($items))
    {
      echo "<li class=\"nextListInactive\">"._("Next")."</li>\n";
    }
  else
    {
      $start = $curend + 1;
      $end = $curend + $max;


      echo "<li class=\"nextList\"><a href=\"#\" onClick=\"updateSearchParam('$filter','$start','$end'); return false\";>"._T("Next")."</a></li>\n";
    }

  echo "</ul>\n";
}

$filter = $_GET['filter'];

$machines = search_machines($filter);

//print_r($machines);

print_ajax_nav($start, $end, $machines,$filter);
global $maxperpage;
?>
<p class="listInfos">

<?php

printf(_T("Computers <strong>%s</strong>
to <strong>%s</strong>
on a total of <strong>%s</strong>
(page %s on"),min(($start + 1), count($machines)),min(($end + 1), count($machines)),count($machines),sprintf("%.0f", ($end + 1) / $maxperpage));

?>

<?php
 $pages = count($machines) / $maxperpage;
 if ((count($machines) % $maxperpage > 0) && (count($machines) > $maxperpage))
   {
     $pages++;
   }
 if ((count($machines) > 0) && ($pages < 1))
   {
     $pages = 1;
   }
 printf("%.0f", $pages);
?>
)
</p>

<?php
if ($error)
{
  echo $error;
}
?>

<table border="1" cellspacing="0" cellpadding="5">
<?php

for ($idx = $start;
     ($idx < count($machines)) && ($idx <= $end);
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

  echo "<td class=\"machineName\">".$machines[$idx][0]."</td>";
  echo "<td class=\"Name\">".$machines[$idx][1]."</td>";
  echo "<td class=\"machineAction\">";
  echo "<ul class=\"action\">";

  $_GET["module"] = 'samba';
  $_GET["submod"] = 'machines';
  $a = new ActionPopupItem(_T("Delete"),"delete","delete","machine");
  $a->display(urlencode($machines[$idx][0]));
  echo "</ul></td>";

  echo "</tr>\n";
}

?>

</table>
<?php
print_ajax_nav($start, $end, $machines,$filter);
?>