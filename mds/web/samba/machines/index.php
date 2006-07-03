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

require("modules/samba/includes/machines.inc.php");

require("graph/header.inc.php");
?>

<style type="text/css">
<!--

<?php
require("modules/samba/graph/machines/index.css");
?>

-->
</style>

<?php
$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
              array("name" => _T("Computers"),
                    "link" => "main.php?module=samba&submod=machines&action=index"),
              array("name" => _T("Computers management")));

require("modules/samba/mainSidebar.php");

require("graph/navbar.inc.php");

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
else
{
  $machines = unserialize(base64_decode(urldecode($_GET["items"])));
}
if (isset($_GET["start"]))
{
$start = $_GET["start"];
$end = $_GET["end"];
}

?>

<form name="Form" id="Form" action="#">

    <div id="loader"><img id="loadimg" src="<?php echo $root; ?>img/common/loader.gif" alt="loader" class="loader"/></div>

    <div id="searchSpan" class="searchbox" style="float: right;">
    <img src="graph/search.gif" style="position:relative; top: 2px; float: left;" alt="search" /> <span class="searchfield"><input type="text" class="searchfieldreal" name="param" id="param" onKeyUp="pushSearch(); return false;">
    <img src="graph/croix.gif" alt="suppression" style="position:relative; top : 3px;"
    onClick="document.getElementById('param').value =''; pushSearch(); return false;">
    </span>
    </div>

    <script>
        document.getElementById('param').focus();


                /**
        * update div with user
        */
        function updateSearch() {
            launch--;

                if (launch==0) {
                    new Ajax.Updater('container','modules/samba/machines/ajaxFilter.php?filter='+document.Form.param.value, { asynchronous:true, evalScripts: true});
                }
            }

        /**
        * provide navigation in ajax for user
        */

        function updateSearchParam(filter, start, end) {
            new Ajax.Updater('container','modules/samba/machines/ajaxFilter.php?filter='+filter+'&start='+start+'&end='+end, { asynchronous:true, evalScripts: true});
            }

        /**
        * wait 500ms and update search
        */

        function pushSearch() {
            launch++;
            setTimeout("updateSearch()",500);
        }

    </script>

</form>

<h2><?= _T("Computers management") ?></h2>

<div class="fixheight"></div>
<div id="container">
<?php
print_nav($start, $end, $machines);
?>
<p class="listInfos">
<?php

global $maxperpage;
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
  //echo "<li class=\"supprimer\"><a title=\""._T("delete")."\" href=\"main.php?module=samba&submod=machines&action=delete&machine=".urlencode($machines[$idx][0])."\">.</a></li>";
  $a = new ActionPopupItem(_T("Delete"),"delete","supprimer","machine");
  $a->display(urlencode($machines[$idx][0]));
  echo "</ul></td>";

  echo "</tr>\n";
}

?>

</table>
<?php
print_nav($start, $end, $machines);
?>
</div>
