<?php

/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id: infoPackage.inc.php 8 2006-11-13 11:08:22Z cedric $
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


/**
 * module declaration
 */

$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
	      array("name" => _T("Mail domain list")));
	      
require("localSidebar.php");
require("graph/navbar.inc.php");

$domain = $_GET["mail"];
?>


<?

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

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
                    new Ajax.Updater('container','modules/mail/mail/ajaxFilter.php?mail=<?=$domain?>&filter='+document.Form.param.value, { asynchronous:true, evalScripts: true});
                }
            }

        /**
        * provide navigation in ajax for user
        */

        function updateSearchParam(filter, start, end) {
            new Ajax.Updater('container','modules/mail/mail/ajaxFilter.php?mail=<?=$domain?>&filter='+filter+'&start='+start+'&end='+end, { asynchronous:true, evalScripts: true});
            }

        /**
        * wait 500ms and update search
        */

        function pushSearch() {
            launch++;
            setTimeout("updateSearch()",500);
        }
         
        pushSearch();
    </script>


</form>

<h2><?= _T("Members of ") . " " . $domain; ?></h2>

<div class="fixheight"></div>


<div id="container">
</div>