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


function cleanup_arr($array) {
    /* FIXME: Do we need this function ? */
    $res = array();
    foreach ($array as $item) {
        if ((preg_match("/^([0-9a-zA-Z@._-]){1,}$/",$item))&&(array_search($item,$res)===False)) {
            $res[] = $item;
        }
    }
    return $res;
}

function changeMailEnable($login,$boolean) {
    return xmlCall("mail.changeMailEnable",array($login,$boolean));
}

function changeMaildrop($login, $droplist) {
    $arr = cleanup_arr($droplist);
    if ((count($arr) == 0) && !hasVDomainSupport()) {
      return; //if no maildrop specified
    }
    return xmlCall("mail.changeMaildrop",array($login,$arr));
}

function changeMailalias($login,$aliaslist) {
    return xmlCall("mail.changeMailalias",array($login, cleanup_arr($aliaslist)));
}

function changeMailbox($login, $mailbox) {
    return xmlCall("mail.changeMailbox", array($login, $mailbox));
}

function changeMailhost($login, $mailhost) {
    return xmlCall("mail.changeMailhost", array($login, $mailhost));
}

function changeQuota($login, $mailuserquota) {
    return xmlCall("mail.changeQuota", array($login, $mailuserquota));
}

function removeMail($login) {
    return xmlCall("mail.removeMail",array($login));
}

function removeMailGroup($group) {
    return xmlCall("mail.removeMailGroup",array($group));
}

function addMailGroup($group, $mail) {
    return xmlCall("mail.addMailGroup",array($group, $mail));
}

function deleteMailGroupAliases($group) {
    xmlCall("mail.deleteMailGroupAliases", array($group));
}

function syncMailGroupAliases($group, $foruser = "*") {
    xmlCall("mail.syncMailGroupAliases", array($group, $foruser));
}

function hasMailObjectClass($login) {
    return xmlCall("mail.hasMailObjectClass",array($login));
}

function hasGroupMailObjectClass($group) {
    return xmlCall("mail.hasMailGroupObjectClass", array($group));
}

function hasVDomainSupport() {
    if (!isset($_SESSION["hasVDomainSupport"])) {
        $_SESSION["hasVDomainSupport"] = xmlCall("mail.hasVDomainSupport", null);
    }
    return $_SESSION["hasVDomainSupport"];
}

function addVDomain($domain) {
    xmlCall("mail.addVDomain", array($domain));
}

function delVDomain($domain) {
    xmlCall("mail.delVDomain", array($domain));
}

function setVDomainDescription($domain, $description) {
    xmlCall("mail.setVDomainDescription", array($domain, $description));
}

function setVDomainQuota($domain, $quota) {
    xmlCall("mail.setVDomainQuota", array($domain, $quota));
}

function resetUsersVDomainQuota($domain) {
    xmlCall("mail.resetUsersVDomainQuota", array($domain));
}

function getVDomain($domain) {
    return xmlCall("mail.getVDomain", array($domain));
}

function getVDomains($filter) {
    return xmlCall("mail.getVDomains", array($filter));
}

function getVDomainUsersCount($domain) {
    return xmlCall("mail.getVDomainUsersCount", array($domain));
}

function getVDomainUsers($domain, $filter) {
    return xmlCall("mail.getVDomainUsers", array($domain, $filter));
}

function computeMailGroupAlias($group) {
    return xmlCall("mail.computeMailGroupAlias", array($group));
}

?>
