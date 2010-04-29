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

require_once("mail-xmlrpc.php");
require_once("mail.inc.php");

function _mail_baseGroupEdit($ldapArr, $postArr) {
    if (!isset($ldapArr["cn"][0])) return;

    print "<div class=\"formblock\" style=\"background-color: #FFD;\">";
    print "<h3>"._T("Mail plugin","mail")."</h3>\n";
    $mail = "";
    $maildomain = "";
    if (hasGroupMailObjectClass($ldapArr['cn'][0])) {
        $hasMail = "checked";
        if (isset($ldapArr["mail"])) $mail = $ldapArr["mail"][0];
	if (hasVDomainSupport()) {
	    $tmparr = explode("@", $mail);
            $mail = $tmparr[0];
	    $maildomain = $tmparr[1];
	}
    } else {
        $mail = computeMailGroupAlias($ldapArr['cn'][0]);
        $hasMail = "";
	if (hasVDomainSupport()) {
            $vdomains = getVDomains("");
            if (count($vdomains) == 1) $maildomain = $vdomains[0][1]["virtualdomain"][0];
	}
    }

    if (($hasMail == "") && ($mail == "")) {
      print _T("No mail alias can be set for this group", "mail");
    } else {
      print '<table cellspacing="0">';
      $test = new TrFormElement(_T("Enable mail alias for users of this group ","mail"), new CheckboxTpl("mailgroupaccess"));
      $param = array("value" => $hasMail,
		     "extraArg" => 'onclick="toggleVisibility(\'maildiv\');"');
      $test->display($param);
      print "</table>";
      if (!$hasMail) {
        $style = 'style =" display: none;"';
      }

      print '<div id="maildiv" '.$style.'>';
      print '<table cellspacing="0">';
      if (!hasVDomainSupport()) {
          $m = new TrFormElement(_T("Mail alias", "mail"), new InputTpl("mailgroupalias"));
	  $m->displayRo(array("value" => $mail));
      } else {
          print '<tr><td width="40%" style="text-align: right;">' . _T("Mail alias", "mail") . '</td><td>' . $mail . '<input  type="hidden" value="' . $mail . '" name="mailgroupalias">&nbsp;@&nbsp;';
	  print '<input type="text" id="autocomplete" name="maildomain" value="' . $maildomain . '" /><div id="autocomplete_choices" class="autocomplete"></div>';
	  print '<script type="text/javascript">new Ajax.Autocompleter("autocomplete", "autocomplete_choices", "modules/mail/mail/ajaxMailDomainFilter.php", {paramName: "value"});</script>';
	  print '</td></tr>';
      }
      print "</table>";
      print "</div>";
    }

    print "</div>";
}

/**
 * function called when you submit while editing a group
 * @param $postArr $_POST array of the page
 */
function _mail_changeGroup($postArr) {
    $group = $postArr["groupname"];
    if (!empty($postArr["mailgroupaccess"])) {
        $mail = $postArr["mailgroupalias"];
        if (hasVDomainSupport()) {
            $vdomain = $postArr["maildomain"];
            $mail .= "@" . $vdomain;
	}
        addMailGroup($group, $mail);
        syncMailGroupAliases($group);
    } else { // mail group access is not checked
        if (hasGroupMailObjectClass($group)) {
            deleteMailGroupAliases($group);
            removeMailGroup($group);
        }
    }
}

function _mail_changeUserPrimaryGroup($user, $newgroup, $oldgroup) {
    syncMailGroupAliases($oldgroup, $user);
    syncMailGroupAliases($newgroup, $user);
}

function _mail_addUserToGroup($user, $group) {
    syncMailGroupAliases($group, $user);
}

function _mail_delUserFromGroup($user, $group) {
    syncMailGroupAliases($group, $user);
}

function _mail_delGroup($group) {
    /** 
     * When deleting a user group, also delete all mail aliases associated to this group
     */
    deleteMailGroupAliases($group);
}


/**
 * display normal edit
 * @param $postArr $_POST array of the page
 * @param $ldapArr ldap array return by getDetailedUser xmlrpc function
 */
function _mail_baseEdit($ldapArr, $postArr) {

  $f = new DivForModule(_T("Mail plugin","mail"), "#FFD");

  if (isset($ldapArr['mailenable'][0]) && $ldapArr['mailenable'][0] == 'NONE') {
    $checkedMail = "checked";
  }
  else {
    $checkedMail = "";
  }

  if (isset($ldapArr['uid'][0]) && $ldapArr['uid'][0]) {
    if (hasMailObjectClass($ldapArr['uid'][0])) {
        $hasMail = "checked";
    }
    else {
        $hasMail = "";
    }
  } else {
        $hasMail = "checked";
  }
  
  if (isset($ldapArr["mailuserquota"][0])) {
    $mailuserquota = $ldapArr["mailuserquota"][0];
  }
  else {
    $mailuserquota = "";
  }

  $f->push(new Table());
  $f->add(
          new TrFormElement(_T("Mail access","mail"),new CheckboxTpl("mailaccess")),
          array("value"=>$hasMail, "extraArg"=>'onclick="toggleVisibility(\'maildiv\');"')
          );
  
  $f->pop();

  // Set default value
  if (!isset($ldapArr['maildrop'])) {
    $ldapArr['maildrop'] = array('');
  }

  if (!isset($ldapArr['mailalias'])) {
    $ldapArr['mailalias'] = array('');
  }

  $maildiv = new Div(array("id" => "maildiv"));
  $maildiv->setVisibility($hasMail);  
  $f->push($maildiv);
  $f->push(new Table());  
  $f->add(
          new TrFormElement(_T("Mail delivery is disabled, if checked","mail"),new CheckboxTpl("maildisable")),
          array("value"=> $checkedMail)
          );
  $f->add(          
          new TrFormElement(_T("Mail quota (in kB)", "mail"), new QuotaTpl("mailuserquota", '/^[0-9]*$/')),
          array("value" => $mailuserquota)
          );
  $f->pop();

  if (hasVDomainSupport()) {
      $m = new MultipleInputTpl("maildrop",_T("Forward to","mail"));
      /* In virtual domain mode, maildrop must be an email address */
      $m->setRegexp('/^[0-9a-zA-Z_.-]+@[0-9a-zA-Z.-]+$/');
  } else {
      $m = new MultipleInputTpl("maildrop",_T("Mail drop","mail"));
      $m->setRegexp('/^([0-9a-zA-Z_.-@.])+$/');
  }
  $f->add(
          new FormElement(_T("Mail drop","mail"), $m),
          $ldapArr['maildrop']
          );  
  $m = new MultipleInputTpl("mailalias",_T("Mail alias","mail"));
  $m->setRegexp('/^([0-9a-zA-Z@_.-])+$/');
  $f->add(
          new FormElement(_T("Mail alias","mail"), $m),
          $ldapArr['mailalias']
          );

  if (hasVDomainSupport()) {
      $f->push(new DivExpertMode());
      $f->push(new Table());
      $f->add(
              new TrFormElement(_T("Mail delivery path", "mail"),new InputTpl("mailbox")),
              array("value" => $ldapArr["mailbox"][0])
              );
      $f->add(
              new TrFormElement(_T("Mail server host", "mail"),new IA5InputTpl("mailhost")),
              array("value" => $ldapArr["mailhost"][0])
              );
      $f->pop();
      $f->pop();
  }

  $f->pop();

  $f->display();

  if (($_GET['action'] == 'add') && (!hasVDomainSupport())) { //suggest only on add user
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
    if (isset($postArr["mailaccess"])) {
        $ereg='/^([0-9a-zA-Z@._-])*$/';
        foreach ($postArr['mailalias'] as $key => $value) {
            if (!preg_match($ereg, $postArr["mailalias"][$key]))  {
                global $error;
                setFormError("mailalias[$key]");
                $error.= sprintf(_T("%s is not a valid mail alias.","mail"),$postArr["mailalias"][$key])."<br />";
            }
        }
        $mailreg='/^([A-Za-z0-9._-]+@[A-Za-z0-9.-]+)$/';
        if (!preg_match($mailreg, $postArr["mail"])) {
            global $error;
            setFormError("mail");
            $error.= _T("You must specify a valid mail address to enable mail delivery.","mail")."<br />";
        }
    }
}


/**
 * function call when you submit change on a user
 * @param $FH FormHandler of the page
 */
function _mail_changeUser($FH) {

    if ($FH->getPostValue("mailaccess")) {
        
        if (hasMailObjectClass($FH->getPostValue("nlogin"))) {
            $syncmailgroupalias = False;
            if ($FH->getValue("unlimitedquota") == "on") 
                $FH->setValue("mailuserquota", "0");
        } 
        else $syncmailgroupalias = True;
        
        if($FH->isUpdated("maildrop"))
            changeMaildrop($FH->getPostValue("nlogin"), $FH->getValue('maildrop'));
        if($FH->isUpdated("mailalias"))
            changeMailalias($FH->getPostValue("nlogin"), $FH->getValue('mailalias'));
        /*
          If we are adding the user and the mailbox/mailhost attributes are
          not filled in, we don't empty them as this may clear default values
          set by the MMC agent.
        */
        if ($FH->isUpdated("mailbox") && $FH->getPostValue("mailbox")) {
            if ($_GET["action"] != "add") {
                changeMailbox($FH->getPostValue("nlogin"), $FH->getPostValue('mailbox'));
            }
        }
        if ($FH->isUpdated("mailhost")) {
            if ($_GET["action"] != "add") {
                changeMailhost($FH->getPostValue("nlogin"), $FH->getPostValue("mailhost"));
            }
        }
        
        if($FH->isUpdated('maildisable')) {
            // disable mail
            if ($FH->getValue('maildisable') == "on")
                changeMailEnable($FH->getPostValue("nlogin"), False);
            else
                changeMailEnable($FH->getPostValue("nlogin"), True);
        }        
        
        /*
          Only change quota if it is POSTed. When adding a user, the default
          domain mail quota is used.
        */
        if ($FH->isUpdated("mailuserquota")) {
            changeQuota($FH->getPostValue("nlogin"), $FH->getValue("mailuserquota"));
        }
            
        if ($syncmailgroupalias) {
            addMailObjectClass($FH->getPostValue("nlogin"));
            /* When mail service is activated for an user, add mail group aliases */
            syncMailGroupAliases($FH->getPostValue("primary_autocomplete"));
            foreach($FH->getPostValue("groupsselected") as $group) 
                syncMailGroupAliases($group);
        }
    } else { //mail access not checked
        if (hasMailObjectClass($FH->getPostValue("nlogin"))) { //and mail access still present
            removemail($FH->getPostValue("nlogin"));
        }
    }

}



?>
