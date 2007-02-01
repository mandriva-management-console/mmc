<?

$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
	      array("name" => _T("Mail domain list")));
	      
require("localSidebar.php");
require("graph/navbar.inc.php");

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

?>

<form name="Form" id="Form" action="#">

    <div id="loader"><img id="loadimg" src="<?php echo $root; ?>img/common/loader.gif" alt="loader" class="loader"/></div>

    <div id="searchSpan" class="searchbox" style="float: right;">
    <img src="graph/search.gif" style="position:relative; top: 2px; float: left;" alt="search" /> <span class="searchfield"><input type="text" class="searchfieldreal" name="param" id="param" onkeyup="pushSearch(); return false;" />
    <img src="graph/croix.gif" alt="suppression" style="position:relative; top : 3px;"
    onclick="document.getElementById('param').value =''; pushSearch(); return false;" />
    </span>
    </div>

    <script type="text/javascript">
        document.getElementById('param').focus();


                /**
        * update div with user
        */
        function updateSearch() {
            launch--;

                if (launch==0) {
                    new Ajax.Updater('container','modules/mail/mail/ajaxDomainFilter.php?filter='+document.Form.param.value, { asynchronous:true, evalScripts: true});
                }
            }

        /**
        * provide navigation in ajax for user
        */

        function updateSearchParam(filter, start, end) {
            new Ajax.Updater('container','modules/mail/mail/ajaxDomainFilter.php?filter='+filter+'&amp;start='+start+'&amp;end='+end, { asynchronous:true, evalScripts: true});
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

<h2><?= _T("Mail domain list"); ?></h2>

<div class="fixheight"></div>

<div id="container">
</div>