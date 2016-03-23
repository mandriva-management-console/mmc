<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com/
 *
 * $Id$
 *
 * This file is part of Management Console.
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
        if (isset($ldapArr["mail"]))
            $mail = $ldapArr["mail"][0];
	    if (hasVDomainSupport()) {
	        $tmparr = explode("@", $mail);
            $mail = $tmparr[0];
    	    $maildomain = $tmparr[1];
	    }
    }
    else {
        $mail = computeMailGroupAlias($ldapArr['cn'][0]);
        $hasMail = "";
        $maildomain = "";
    	if (hasVDomainSupport()) {
            $vdomains = getVDomains("");
            if (count($vdomains) > 0)
                $maildomain = $vdomains[0][1]["virtualdomain"][0];
	    }
    }

    if (($hasMail == "") && ($mail == "")) {
        print _T("No mail alias can be set for this group", "mail");
    }
    else {
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
      }
      else {
          print '<tr><td width="40%" style="text-align: right;">' . _T("Mail alias", "mail") . '</td><td>' . $mail . '<input  type="hidden" value="' . $mail . '" name="mailgroupalias">&nbsp;@&nbsp;';
          print '<input type="text" id="autocomplete" name="maildomain" value="' . $maildomain . '" /><div id="autocomplete_choices" class="autocomplete"></div>';
          print '<script type="text/javascript">new Ajax.Autocompleter("autocomplete", "autocomplete_choices", "'.urlStrRedirect('mail/domains/ajaxMailDomainFilter').'", {paramName: "value"});</script>';
          print '</td></tr>';
      }

      if (isExpertMode()) {
          $mailhidden = "";
          if (isset($ldapArr['mailhidden']) && $ldapArr['mailhidden'][0] == 'YES')
              $mailhidden = 'checked="checked"';
          print '<tr><td width="40%" style="text-align: right;">' . _T("Mail hidden", "mail") . '</td>';
          print '<td><input type="checkbox" name="mailhidden" ' . $mailhidden . ' /></td></tr>';
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
            if (!$vdomain)
                $mail = false;
            else
                $mail .= "@" . $vdomain;
        }

        if ($mail) {
            addMailGroup($group, $mail);
            syncMailGroupAliases($group);
            $mailhidden = false;
            if ($postArr["mailhidden"] == "on")
                $mailhidden = true;
            changeMailGroupHidden($group, $mailhidden);
            return true;
        }
        else {
            new NotifyWidgetFailure(_T("Mail domain is empty. Group mail alias wasn't set."));
            return false;
        }
    }
    else { // mail group access is not checked
        if (hasGroupMailObjectClass($group)) {
            deleteMailGroupAliases($group);
            removeMailGroup($group);
        }
    }
    return true;
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

function _mail_delUser($uid, $delfiles) {
    delVAliasesUser($uid);
    if ($delfiles) {
        delUserMails($uid);
    }
}

function _mail_delGroup($group) {
    /**
     * When deleting a user group, also delete all mail aliases associated to this group
     */
    deleteMailGroupAliases($group);
}

function _mail_enableUser($uid) {
    return changeMailEnable($uid, True);
}

function _mail_disableUser($uid) {
    return changeMailEnable($uid, False);
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
            // Display an error message on top of the page
            $em = new ErrorMessage(_T("Mail properties", "samba") . ' : ' .
                _T("Mail delivery is disabled", "samba"));
            $em->display();
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

    $groupmailaliases = getMailGroupAliases();
    $useraliases = $FH->getArrayOrPostValue($attrs['mailalias'], 'array');

    $mailalias = array_diff($useraliases, $groupmailaliases);
    $m = new MultipleInputTpl($attrs['mailalias'], _T("Mail alias","mail"));
    $m->setRegexp('/^([0-9a-zA-Z@_.+\-])+$/');
    $f->add(
        new FormElement(_T("Mail alias","mail"), $m),
        $mailalias ? $mailalias : array("")
    );

    $mailalias = array_intersect($useraliases, $groupmailaliases);
    if (count($mailalias) > 0) {
        $f->push(new Table());
        $f->add(
            new TrFormElement(_T("Group mail aliases", "mail"), new HiddenTpl('mailgroupalias'),
                array("tooltip" => _T("The user is also in these group mail aliases", "mail"))),
            array("value" => join(", ", $mailalias))
        );
        $f->pop();
    }

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
        $f->add(
            new TrFormElement(_T("Mail proxy", "mail"),new IA5InputTpl($attrs['mailproxy'])),
            array("value" => $FH->getArrayOrPostValue($attrs['mailproxy']))
        );
        $f->pop();
        $f->pop();
    }

    $mailhidden = false;
    if ($FH->getArrayOrPostValue($attrs['mailhidden']) == "YES")
        $mailhidden = true;
    $f->push(new DivExpertMode());
    $f->push(new Table());
    $f->add(
        new TrFormElement(_T("Mail hidden", "mail"), new CheckboxTpl('mailhidden')),
        array("value" => $mailhidden ? "checked" : "")
    );
    $f->pop();
    $f->pop();

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
        $checked = false;
        if($FH->getArrayOrPostValue('zarafaHidden') == "on" ||
           $FH->getArrayOrPostValue('zarafaHidden') == "1")
            $checked = true;
        $f->add(
            new TrFormElement(_T("Hide from Zarafa address book", "mail"),
                new CheckboxTpl("zarafaHidden")),
                array("value"=> $checked ? "checked" : "")
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

    if ($FH->isUpdated($attrs['mailalias'])) {
        $ereg = '/^([A-Za-z0-9._+@-])*$/';
        $mails = $FH->getValue($attrs['mailalias']);
        foreach ($mails as $key => $value) {
            if ($value && !preg_match($ereg, $mails[$key]))  {
                $mail_errors .= sprintf(_T("%s is not a valid mail alias.","mail"), $mails[$key])."<br />";
                setFormError($attrs['mailalias']."[".$key."]");
            }
        }
    }

    if ($FH->isUpdated($attrs['maildrop']) &&
        count($FH->getValue($attrs['maildrop'])) == 0 &&
        !hasVDomainSupport()) {
        $mail_errors .= _T("You must specify at least one mail drop. Usually it has the same name as the user.","mail")."<br />";
    }

    if ($FH->getPostValue("mailaccess") == "on") {
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

    $uid = $FH->getPostValue("uid");

    if ($FH->getPostValue("mailaccess")) {

        $attrs = getMailAttributes();

        if (hasMailObjectClass($uid)) {
            if ($FH->getValue("unlimitedquota") == "on") {
                $FH->setPostValue($attrs["mailuserquota"], "0");
                $FH->setValue($attrs["mailuserquota"], "0");
            }
        }
    	else {
            addMailObjectClass($uid);
            $result .= _T("Mail attributes added.", "mail")."<br />";
        }

        if($FH->isUpdated($attrs["maildrop"]))
            changeMaildrop($uid, $FH->getValue($attrs['maildrop']));
        if($FH->isUpdated($attrs["mailalias"])) {
            changeMailalias($uid, $FH->getValue($attrs['mailalias']));
            syncUserMailGroupAliases($uid);
        }
        if ($FH->isUpdated($attrs["mailbox"]))
            changeMailbox($uid, $FH->getValue($attrs['mailbox']));
        if ($FH->isUpdated($attrs["mailhost"]))
            changeMailhost($uid, $FH->getValue($attrs["mailhost"]));
        if ($FH->isUpdated($attrs["mailproxy"]))
            changeMailproxy($uid, $FH->getValue($attrs["mailproxy"]));
        if ($FH->isUpdated($attrs["mailuserquota"]))
            changeQuota($uid, $FH->getValue($attrs["mailuserquota"]));
        // always set mailhidden
        if ($FH->getValue($attrs["mailhidden"]) == "on") {
            changeMailhidden($uid, true);
        }
        else {
            changeMailhidden($uid, false);
        }
        if ($FH->isUpdated('maildisable')) {
            // disable mail
            if ($FH->getValue('maildisable') == "on") {
                changeMailEnable($uid, False);
                $result .= _T("Mail delivery disabled.", "mail")."<br />";
            }
            else {
                changeMailEnable($uid, True);
                $result .= _T("Mail delivery enabled.", "mail")."<br />";
            }
        }
        else {
            changeMailEnable($uid, True);
        }

        /* Zarafa only */
        if (hasZarafaSupport()) {
            $fields = array("zarafaAdmin", "zarafaSharedStoreOnly", "zarafaAccount");
            foreach($fields as $field) {
                if ($FH->isUpdated($field)) {
                    modifyZarafa($uid,
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
                modifyZarafa($uid,
                             "zarafaSendAsPrivilege",
                             $newvalues);
            }
        }

        /* When mail service is activated for an user, add mail group aliases */
        syncMailGroupAliases($FH->getPostValue("primary"));
        if ($FH->isUpdated('secondary')) {
            if ($FH->getValue("secondary")) {
                foreach($FH->getValue("secondary") as $group)
                    syncMailGroupAliases($group);
            }
        }
    } else { // mail access not checked
        if (hasMailObjectClass($uid)) { //and mail access still present
            removemail($uid);
            $result .= _T("Mail attributes deleted.", "mail")."<br />";
        }
    }

    return 0;

}

?>
