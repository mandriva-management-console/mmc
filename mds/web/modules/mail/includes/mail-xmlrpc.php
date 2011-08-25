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


function changeMailEnable($login,$boolean) {
    return xmlCall("mail.changeMailEnable",array($login,$boolean));
}

function changeMaildrop($login, $droplist) {
    return xmlCall("mail.changeMaildrop",array($login, $droplist));
}

function changeMailalias($login,$aliaslist) {
    return xmlCall("mail.changeMailalias",array($login, $aliaslist));
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

function addMailObjectClass($login) {
    xmlCall("mail.addMailObjectClass", $login);
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

function hasVAliasesSupport() {
    return xmlCall("mail.hasVAliasesSupport", null);
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

function getVAliases($filter = '') {
    return xmlCall("mail.getVAliases", array($filter));
}

function getVAlias($alias) {
    return xmlCall("mail.getVAlias", array($alias));
}

function addVAlias($alias) {
    return xmlCall("mail.addVAlias", array($alias));
}

function changeVAliasName($alias, $name) {
    return xmlCall("mail.changeVAliasName", array($alias, $name));
}

function changeVAliasEnable($alias, $enabled) {
    return xmlCall("mail.changeVAliasEnable", array($alias, $enabled));
}

function delVAlias($alias) {
    return xmlCall("mail.delVAlias", array($alias));
}

function updateVAliasExternalUsers($alias, $mails) {
    return xmlCall("mail.updateVAliasExternalUsers", array($alias, $mails));
}

function getVAliasUsers($alias) {
    return xmlCall("mail.getVAliasUsers", array($alias));
}

function addVAliasUser($alias, $user) {
    return xmlCall("mail.addVAliasUser", array($alias, $user));
}

function delVAliasUser($alias, $user) {
    return xmlCall("mail.delVAliasUser", array($alias, $user));
}

function delVAliasesUser($user) {
    return xmlCall("mail.delVAliasesUser", array($user));
}

function computeMailGroupAlias($group) {
    return xmlCall("mail.computeMailGroupAlias", array($group));
}

function getMailAttributes() {
    return xmlCall("mail.getMailAttributes");
}

function hasZarafaSupport() {
    return xmlCall("mail.hasZarafaSupport");
}

function modifyZarafa($login, $attr, $value) {
    return xmlCall("mail.modifyZarafa", array($login, $attr, $value));
}

function isZarafaGroup($group) {
    return xmlCall("mail.isZarafaGroup", $group);
}

function setZarafaGroup($group, $value) {
    return xmlCall("mail.setZarafaGroup", array($group, $value));
}

?>
