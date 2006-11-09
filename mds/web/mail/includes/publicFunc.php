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

include ("mail-xmlrpc.php");

/**
 * display normal edit
 * @param $postArr $_POST array of the page
 * @param $ldapArr ldap array return by getDetailedUser xmlrpc function
 */
function _mail_baseEdit($ldapArr,$postArr) {

  print "<div class=\"formblock\" style=\"background-color: #FFD;\">";
  print "<h3>"._T("Mail plugin","mail")."</h3>\n";

  if ($ldapArr['mailenable'][0]=='NONE') {
    $checkedMail = "checked";
  }

  if ($ldapArr['uid'][0]) {
    if (hasMailObjectClass($ldapArr['uid'][0])) {
        $hasMail = "checked";
    }
  } else {
        $hasMail = "checked";
  }



  print "<table>";
  $test = new TrFormElement(_T("Mail access","ox"),new CheckboxTpl("mailaccess"));
  $test->setCssError("accesMail");
  $param=array("value"=>$hasMail,
               "extraArg"=>'onclick="toggleVisibility(\'maildiv\');"');
  $test->display($param);

  print "</table>";

  //set default value
  if (!isset($ldapArr['maildrop'])) {
    $ldapArr['maildrop'] = array('');
  }

  if (!isset($ldapArr['mailalias'])) {
    $ldapArr['mailalias'] = array('');
  }

  if (!$hasMail) {
    $style = 'style =" display: none;"';
  }


  print '<div id="maildiv" '.$style.'>';

  print "<table>";
  $test = new TrFormElement(_T("Mail delivery is disabled, if checked","mail"),new CheckboxTpl("maildisable"));
  $test->setCssError("mailenable");
  $param=array("value"=>$checkedMail);
  $test->display($param);
  print "</table>";

  $m = new MultipleInputTpl("maildrop",_T("Mail drop","mail"));
  $m->setRegexp('/^([0-9a-zA-Z@.])+$/');

  $test = new FormElement(_T("Mail drop","mail"),$m);
  $test->setCssError("maildrop");
  $test->display($ldapArr['maildrop']);

  $m = new MultipleInputTpl("mailalias",_T("Mail alias","mail"));
  $m->setRegexp('/^([0-9a-zA-Z@.])+$/');

  $test = new FormElement(_T("Mail alias","mail"),$m);
  $test->setCssError("mailalias");
  $test->display($ldapArr['mailalias']);



  print '</div>';
  print '</div>';

  if ($_GET['action'] == 'add') { //suggest only on add user
  ?>
  <script type="text/javascript" language="javascript">
     function autoCreate() {
        var firstname = $('firstname').value.toLowerCase()
        firstname = firstname.replace(/( |"|')/g,'')


        $('maildrop[0]').value = $('nlogin').value.toLowerCase();
     }

     Event.observe('name', 'keyup', function(e){ autoCreate(); });
     Event.observe('nlogin', 'keyup', function(e){ autoCreate(); });
     Event.observe('firstname', 'keyup', function(e){ autoCreate(); });
  </script>
  <?
  }
}


/**
 * verification if information
 * @param $postArr $_POST array of the page
 */
function _mail_verifInfo($postArr) {

if ($postArr["mailenable"]) {

            $ereg='/^([0-9a-zA-Z@.])*$/';

        foreach ($postArr['mailalias'] as $key => $value) {
            if (!preg_match($ereg, $postArr["mailalias"][$key]))  {
                global $error;
                setFormError("mailalias[$key]");
                $error.= sprintf(_T("%s is not a valid mail alias.","mail"),$postArr["mailalias"][$key])."<br />";
            }
        }


    }
}

/**
 * function call when you submit change on a user
 * @param $postArr $_POST array of the page
 */
function _mail_changeUser($postArr) {
    if ($postArr["mailaccess"]) {
            changeMaildrop($postArr["nlogin"],$postArr['maildrop']);
            changeMailalias($postArr["nlogin"],$postArr['mailalias']);
        if (!$postArr["maildisable"]) {
            changeMailEnable($postArr["nlogin"],True);

        } else {
            changeMailEnable($postArr["nlogin"],False);
        }
    } else { //mail access not checked
        if (hasMailObjectClass($postArr["nlogin"])) { //and mail access still present
            removemail($postArr["nlogin"]);
        }
    }

}
?>
