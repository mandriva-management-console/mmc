<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com/
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
      if (hasZarafaSupport()) {
          $trz = new TrFormElement(_T("Zarafa group","mail"), new CheckboxTpl("zarafaGroup"));
          $trz->display(array("value" => isZarafaGroup($ldapArr['cn'][0]) ? "checked" : ""));
      }
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
    if (hasZarafaSupport($group)) {
        setZarafaGroup($postArr["groupname"], isset($postArr["zarafaGroup"]));
    }
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
 * Form on user edit page
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _mail_baseEdit($FH, $mode) {

    $attrs = getMailAttributes();

    $f = new DivForModule(_T("Mail properties","mail"), "#FFD");

    // Show plugin details by default
    $show = true;
    // User has not mail attributes by default
    $hasMail = false;
    // User is not disabled by default
    $disabledMail = false;

    if ($mode == "edit") {
        // check user actual values
        $uid =  $FH->getArrayOrPostValue('uid');
        if (hasMailObjectClass($uid)) {
            $hasMail = true;
        }
        else {
            $show = false;
        }
        if ($FH->getArrayOrPostValue($attrs['mailenable']) == "NONE") {
            $disabledMail = true;
        }
    }
    
    if ($mode == "add" && $FH->getValue('mailaccess') == 'off') {
        $show = false;
    }

    $f->push(new Table());
    $f->add(
        new TrFormElement(_T("Mail access","mail"),new CheckboxTpl("mailaccess")),
            array("value"=> $show ? "checked": "", "extraArg"=>'onclick="toggleVisibility(\'maildiv\');"')
    );
    $f->pop();

    $maildiv = new Div(array("id" => "maildiv"));
    $maildiv->setVisibility($show);
    $f->push($maildiv);
    $f->push(new Table());

    $f->add(
        new TrFormElement(_T("Mail delivery is disabled, if checked","mail"),new CheckboxTpl("maildisable")),
        array("value"=> $disabledMail ? "checked": "")
    );
    $f->add(
        new TrFormElement(_T("Mail quota (in kB)", "mail"), new QuotaTpl($attrs['mailuserquota'], '/^[0-9]*$/')),
        array("value" => $FH->getArrayOrPostValue($attrs['mailuserquota']))
    );

    $f->pop();

    if (hasVDomainSupport()) {
        $m = new MultipleInputTpl("maildrop",_T("Forward to","mail"));
        /* In virtual domain mode, maildrop must be an email address */
        $m->setRegexp('/^[0-9a-zA-Z_.+\-]+@[0-9a-zA-Z.\-]+$/');
    } else {
        $m = new MultipleInputTpl($attrs['maildrop'], _T("Mail drop","mail"));
        $m->setRegexp('/^([0-9a-zA-Z_.+@\-])+$/');
    }

    $f->add(
        new FormElement(_T("Mail drop","mail"), $m),
            $FH->getArrayOrPostValue($attrs['maildrop'], 'array')
    );

    $m = new MultipleInputTpl($attrs['mailalias'], _T("Mail alias","mail"));
    $m->setRegexp('/^([0-9a-zA-Z@_.+\-])+$/');

    $f->add(
        new FormElement(_T("Mail alias","mail"), $m),
        $FH->getArrayOrPostValue($attrs['mailalias'], 'array')
    );

    if (hasVDomainSupport()) {
        $f->push(new DivExpertMode());
        $f->push(new Table());
        $f->add(
            new TrFormElement(_T("Mail delivery path", "mail"), new InputTpl($attrs['mailbox'])),
            array("value" => $FH->getArrayOrPostValue($attrs['mailbox']))
        );
        $f->add(
            new TrFormElement(_T("Mail server host", "mail"),new IA5InputTpl($attrs['mailhost'])),
            array("value" => $FH->getArrayOrPostValue($attrs['mailhost']))
        );
        $f->pop();
        $f->pop();
    }

    if (hasZarafaSupport()) {
        $f->push(new DivForModule(_T("Zarafa properties", "mail"), "#FFD"));
        $f->push(new Table());
        $checked = false;
        if($FH->getArrayOrPostValue('zarafaAdmin') == "on" ||
           $FH->getArrayOrPostValue('zarafaAdmin') == "1")
            $checked = true;
        $f->add(
            new TrFormElement(_T("Administrator of Zarafa", "mail"),
                new CheckboxTpl("zarafaAdmin")),
                array("value"=>  $checked ? "checked" : "")
        );
        $checked = false;
        if($FH->getArrayOrPostValue('zarafaSharedStoreOnly') == "on" ||
           $FH->getArrayOrPostValue('zarafaSharedStoreOnly') == "1")
            $checked = true;
        $f->add(
            new TrFormElement(_T("Shared store", "mail"),
                new CheckboxTpl("zarafaSharedStoreOnly")),
                array("value"=> $checked ? "checked" : "")
        );
        $checked = false;
        if($FH->getArrayOrPostValue('zarafaAccount') == "on" ||
           $FH->getArrayOrPostValue('zarafaAccount') == "1")
            $checked = true;
        $f->add(
            new TrFormElement(_T("Zarafa account", "mail"),
                new CheckboxTpl("zarafaAccount")),
                array("value"=> $checked == "on" ? "checked" : "")
        );
        $f->pop();

        $sendas = new MultipleInputTpl("zarafaSendAsPrivilege", _T("Zarafa send as user list", "mail"));
        $sendas->setRegexp('/^([0-9a-zA-Z@_.\-])+$/');
        $f->add(
            new FormElement("", $sendas), $FH->getArrayOrPostValue("zarafaSendAsPrivilege", "array")
        );
        $f->pop();
    }

    $f->pop();

    if ($mode == 'add' && !hasVDomainSupport()) {
        //suggest only on add user
        ?>
        <script type="text/javascript" language="javascript">
        var autoCreate = function(e) {
            $('maildrop[0]').value = $F('uid').toLowerCase();
        };
        Event.observe(window, 'load', function() {
            $('uid').observe('keyup', autoCreate);
        });
        </script>
        <?php
    }
    
    return $f;
}


/**
 * Function called before changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _mail_verifInfo($FH, $mode) {

    global $error;
    
    $mail_errors = "";
    $attrs = getMailAttributes();

    if ($FH->getPostValue("mailaccess")) {
        $ereg = '/^([A-Za-z0-9._+@-])*$/';
        if ($FH->getValue('mailalias')) {
            $mails = $FH->getValue($attrs['mailalias']);
            foreach ($mails as $key => $value) {
                if ($value && !preg_match($ereg, $mails[$key]))  {
                    $mail_errors .= sprintf(_T("%s is not a valid mail alias.","mail"), $mails[$key])."<br />";
                    setFormError($attrs['mailalias']."[".$key."]");
                }
            }
        }
        $mailreg = '/^([A-Za-z0-9._+-]+@[A-Za-z0-9.-]+)$/';
	    if (!preg_match($mailreg, $FH->getPostValue('mail'), $matches)) {
            $mail_errors .= _T("You must specify a valid mail address to enable mail delivery.","mail")."<br />";
            setFormError("mail");
        }
    }
    
    $error .= $mail_errors;
    
    return $mail_errors ? 1 : 0;
}


/**
 * Function called for changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _mail_changeUser($FH, $mode) {

    global $result;
    $attrs = getMailAttributes();

    if ($FH->getPostValue("mailaccess")) {

        if (hasMailObjectClass($FH->getPostValue("uid"))) {
            $syncmailgroupalias = False;
            if ($FH->getValue("unlimitedquota") == "on")
                $FH->setPostValue($attrs["mailuserquota"], "0");
                $FH->setValue($attrs["mailuserquota"], "0");
        }
    	else {
            addMailObjectClass($FH->getPostValue("uid"));
            $result .= _T("Mail attributes added.", "mail")."<br />";
            $syncmailgroupalias = True;
	}

        if($FH->isUpdated($attrs["maildrop"]))
            changeMaildrop($FH->getPostValue("uid"), $FH->getValue($attrs['maildrop']));
        if($FH->isUpdated($attrs["mailalias"]))
            changeMailalias($FH->getPostValue("uid"), $FH->getValue($attrs['mailalias']));
        /*
          If we are adding the user and the mailbox/mailhost attributes are
          not filled in, we don't empty them as this may clear default values
          set by the MMC agent.
        */
        if ($FH->isUpdated($attrs["mailbox"]))
            changeMailbox($FH->getPostValue("uid"), $FH->getValue($attrs['mailbox']));
        if ($FH->isUpdated($attrs["mailhost"]))
            changeMailhost($FH->getPostValue("uid"), $FH->getValue($attrs["mailhost"]));

        if ($FH->isUpdated('maildisable')) {
            // disable mail
            if ($FH->getValue('maildisable') == "on") {
                changeMailEnable($FH->getPostValue("uid"), False);
                $result .= _T("Mail delivery disabled.", "mail")."<br />";
            }
            else
                changeMailEnable($FH->getPostValue("uid"), True);
        }

        /*
          Only change quota if it is POSTed. When adding a user, the default
          domain mail quota is used.
        */
        if ($FH->isUpdated($attrs["mailuserquota"])) {
            changeQuota($FH->getPostValue("uid"), $FH->getValue($attrs["mailuserquota"]));
        }

        /* Zarafa only */
        if (hasZarafaSupport()) {
            $fields = array("zarafaAdmin", "zarafaSharedStoreOnly", "zarafaAccount");
            foreach($fields as $field) {
                if ($FH->isUpdated($field)) {
                    modifyZarafa($FH->getPostValue("uid"),
                                 $field,
                                 $FH->getValue($field) == "on" ? True : False);
                }
            }
            if ($FH->isUpdated("zarafaSendAsPrivilege")) {
                $values = $FH->getValue("zarafaSendAsPrivilege");
                $newvalues = array();
                foreach($values as $value) {
                    if (!empty($value)) $newvalues[] = $value;
                }
                modifyZarafa($FH->getPostValue("uid"),
                             "zarafaSendAsPrivilege",
                             $newvalues);
            }
        }

        if ($syncmailgroupalias) {
            /* When mail service is activated for an user, add mail group aliases */
            syncMailGroupAliases($FH->getPostValue("primary"));
            foreach($FH->getPostValue("groupsselected") as $group)
                syncMailGroupAliases($group);
        }
    } else { // mail access not checked
        if (hasMailObjectClass($FH->getPostValue("uid"))) { //and mail access still present
            removemail($FH->getPostValue("uid"));
            $result .= _T("Mail attributes deleted.", "mail")."<br />";
        }
    }
    
    return 0;

}

?>
