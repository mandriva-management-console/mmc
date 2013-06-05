# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
#
# $Id$
#
# This file is part of Mandriva Management Console (MMC).
#
# MMC is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# MMC is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MMC.  If not, see <http://www.gnu.org/licenses/>.
#

"""
MDS mail plugin for the MMC agent.
"""

import ldap.modlist
from ldap.dn import str2dn
import copy
import logging
from ConfigParser import NoOptionError, NoSectionError

from mmc.core.version import scmRevision
from mmc.plugins.base import ldapUserGroupControl
from mmc.plugins.base import delete_diacritics
from mmc.support.config import PluginConfig
import mmc

from mmc.core.audit import AuditFactory as AF
from mmc.plugins.mail.audit import AT, AA, PLUGIN_NAME


VERSION = "2.5.0"
APIVERSION = "6:2:4"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

def activate():
    ldapObj = ldapUserGroupControl()
    logger = logging.getLogger()

    config = MailConfig("mail")
    if config.disabled:
        logger.warning("Plugin mail: disabled by configuration.")
        return False

    mailSchema = {
        "mailAccount" : ["mail", "mailalias", "maildrop", "mailenable", "mailbox", "mailuserquota", "mailhost"],
        "mailGroup" : ["mail"],
        "mailDomain" : ["virtualdomain", "virtualdomaindescription", "mailuserquota"],
        }

    # Additional LDAP classes/attributes to check for ZARAFA support
    if config.zarafa:
        mailSchema['zarafa-user'] = ['zarafaAdmin', 'zarafaSharedStoreOnly',
                                     'zarafaAccount', 'zarafaSendAsPrivilege',
                                     'zarafaHidden']
        mailSchema['zarafa-group'] = []

    # Additional LDAP classes for virtual aliases
    if config.vAliasesSupport:
        mailSchema['mailAlias'] = ['mailaliasmember']

    for objectClass in mailSchema:
        schema = ldapObj.getSchema(objectClass)
        if not len(schema):
            logger.error("LDAP mail schema is not up to date: %s objectClass is not included in LDAP directory" % objectClass)
            return False
        for attribute in mailSchema[objectClass]:
            if not attribute in schema:
                logger.error("LDAP mail schema is not up to date: %s attribute is not included in LDAP directory" % attribute)
                return False

    if config.vAliasesSupport:
        # Create required OU
        head, path = config.vAliasesDN.split(",", 1)
        ouName = head.split("=")[1]
        ldapObj.addOu(ouName, path)

    if config.vDomainSupport:
        # Create required OU
        head, path = config.vDomainDN.split(",", 1)
        ouName = head.split("=")[1]
        ldapObj.addOu(ouName, path)

    return True

def changeMail(uid,mail):
    MailControl().changeMail(uid,mail)

def changeMailEnable(uid, enabled):
    MailControl().changeMailEnable(uid, enabled)

def changeMaildrop(uid, maildroplist):
    MailControl().changeMaildrop(uid, maildroplist)

def changeMailalias(uid, mailaliaslist):
    MailControl().changeMailalias(uid, mailaliaslist)

def changeMailbox(uid, mailbox):
    MailControl().changeMailbox(uid, mailbox)

def changeMailhost(uid, mailhost):
    MailControl().changeMailhost(uid, mailhost)

def changeQuota(uid, mailuserquota):
    MailControl().changeQuota(uid, mailuserquota)

def removeMail(uid):
    MailControl().removeMail(uid)

def removeMailGroup(group):
    MailControl().removeMailGroup(group)

def addMailGroup(group, mail):
    MailControl().addMailGroup(group, mail)

def addMailObjectClass(uid):
    MailControl().addMailObjectClass(uid)

def hasMailObjectClass(uid):
    return MailControl().hasMailObjectClass(uid)

def hasMailGroupObjectClass(uid):
    return MailControl().hasMailGroupObjectClass(uid)

def hasVDomainSupport():
    return MailControl().hasVDomainSupport()

def addVDomain(domain):
    MailControl().addVDomain(domain)

def delVDomain(domain):
    MailControl().delVDomain(domain)

def setVDomainDescription(domain, description):
    MailControl().setVDomainDescription(domain, description)

def setVDomainQuota(domain, quota):
    MailControl().setVDomainQuota(domain, quota)

def resetUsersVDomainQuota(domain):
    MailControl().resetUsersVDomainQuota(domain)

def getVDomain(domain):
    return MailControl().getVDomain(domain)

def getVDomains(filt):
    return MailControl().getVDomains(filt)

def getVDomainUsersCount(domain):
    return MailControl().getVDomainUsersCount(domain)

def getVDomainUsers(domain, filt):
    return MailControl().getVDomainUsers(domain, filt)

def computeMailGroupAlias(group):
    return MailControl().computeMailGroupAlias(group)

def deleteMailGroupAliases(group):
    return MailControl().deleteMailGroupAliases(group)

def syncMailGroupAliases(group, foruser = "*"):
    return MailControl().syncMailGroupAliases(group, foruser)

def getMailAttributes():
    return MailConfig('mail').attrs

def hasVAliasesSupport():
    return MailControl().hasVAliasesSupport()

def getVAliases(filt):
    return MailControl().getVAliases(filt)

def getVAlias(alias):
    return MailControl().getVAlias(alias)

def addVAlias(alias):
    return MailControl().addVAlias(alias)

def changeVAliasName(alias, name):
    return MailControl().changeVAliasName(alias, name)

def changeVAliasEnable(alias, enabled):
    return MailControl().changeVAliasEnable(alias, enabled)

def delVAlias(alias):
    return MailControl().delVAlias(alias)

def getVAliasUsers(alias):
    return MailControl().getVAliasUsers(alias)

def addVAliasUser(alias, uid):
    return MailControl().addVAliasUser(alias, uid)

def delVAliasUser(alias, uid):
    return MailControl().delVAliasUser(alias, uid)

def delVAliasesUser(uid):
    return MailControl().delVAliasesUser(uid)

def addVAliasExternalUser(alias, mail):
    return MailControl().addVAliasExternalUser(alias, mail)

def delVAliasExternalUser(alias, mail):
    return MailControl().delVAliasExternalUser(alias, mail)

def updateVAliasExternalUsers(alias, mails):
    return MailControl().updateVAliasExternalUsers(alias, mails)

# Zarafa support

def hasZarafaSupport():
    return MailConfig('mail').zarafa

def modifyZarafa(uid, attribute, value):
    return MailControl().modifyZarafa(uid, attribute, value)

def isZarafaGroup(group):
    return MailControl().isZarafaGroup(group)

def setZarafaGroup(group, value):
    return MailControl().setZarafaGroup(group, value)

class MailConfig(PluginConfig):

    def readConf(self):
        logger = logging.getLogger()
        PluginConfig.readConf(self)
        try: self.vDomainSupport = self.getboolean("main", "vDomainSupport")
        except: pass
        if self.vDomainSupport:
            self.vDomainDN = self.get("main", "vDomainDN")
        try: self.vAliasesSupport = self.getboolean("main", "vAliasesSupport")
        except: pass
        if self.vAliasesSupport:
            self.vAliasesDN = self.get("main", "vAliasesDN")
        try:
            self.zarafa = self.getboolean("main", "zarafa")
        except NoOptionError:
            pass
        try:
            self.attrs = dict(self.items("mapping"))
        except NoSectionError:
            self.attrs = {}
        attrs = ["mailalias", "maildrop", "mailenable", "mailbox", "mailuserquota", "mailhost"]
        # validate attribute mapping
        for attr, val in self.attrs.copy().items():
            if not attr in attrs:
                del self.attrs[attr]
                logger.error("Can't map attribute %s. Attribute not supported." % attr)
        # add all other attributes
        for attr in attrs:
            if not attr in self.attrs:
                self.attrs[attr] = attr

    def setDefault(self):
        PluginConfig.setDefault(self)
        self.vDomainSupport = False
        self.vAliasesSupport = False
        self.userDefault = {}
        self.zarafa = False

class MailControl(ldapUserGroupControl):

    def __init__(self, conffile = None, conffilebase = None):
        mmc.plugins.base.ldapUserGroupControl.__init__(self, conffilebase)
        self.conf = MailConfig("mail", conffile)

    def hasVAliasesSupport(self):
        return self.conf.vAliasesSupport

    def hasVDomainSupport(self):
        return self.conf.vDomainSupport

    def hasZarafaSupport(self):
        return self.conf.zarafa

    def addVDomain(self, domain):
        """
        Add a virtual mail domain name entry in directory

        @param domain: virtual mail domain name
        @type domain: str
        """
        r = AF().log(PLUGIN_NAME, AA.MAIL_ADD_VDOMAIN, [(domain, AT.VMDOMAIN)])
        dn = "virtualdomain=" + domain + ", " + self.conf.vDomainDN
        entry = {
            "virtualdomain" : domain,
            "objectClass" :  ("mailDomain", "top")
            }
        modlist = ldap.modlist.addModlist(entry)
        self.l.add_s(dn, modlist)
        r.commit()

    def delVDomain(self, domain):
        """
        Del a virtual mail domain name entry from directory

        @param domain: virtual mail domain name
        @type domain: str
        """
        r = AF().log(PLUGIN_NAME, AA.MAIL_DEL_VDOMAIN, [(domain, AT.VMDOMAIN)])
        dn = "virtualdomain=" + domain + ", " + self.conf.vDomainDN
        self.delRecursiveEntry(dn)
        r.commit()

    def setVDomainDescription(self, domain, description):
        """
        Set the virtualdomaindescription of a virtual mail domain name

        @param domain: virtual mail domain name
        @type domain: str

        @param description: description
        @type description: unicode
        """
        r = AF().log(PLUGIN_NAME, AA.MAIL_SET_DOMAIN_DESC, [(domain, AT.VMDOMAIN)], description)
        dn = "virtualdomain=" + domain + ", " + self.conf.vDomainDN
        description = description.encode("utf-8")
        if description:
            self.l.modify_s(dn, [(ldap.MOD_REPLACE, "virtualdomaindescription", description)])
        else:
            self.l.modify_s(dn, [(ldap.MOD_REPLACE, "virtualdomaindescription", "null")])
            self.l.modify_s(dn, [(ldap.MOD_DELETE, "virtualdomaindescription", "null")])
        r.commit()

    def setVDomainQuota(self, domain, quota):
        """
        Set the quota of a virtual mail domain name

        @param domain: virtual mail domain name
        @type domain: str

        @param quota: created user quota in the virtual domain
        @type description: unicode
        """
        r = AF().log(PLUGIN_NAME, AA.MAIL_SET_DOMAIN_QUOTA, [(domain, AT.VMDOMAIN)], quota)
        dn = "virtualdomain=" + domain + ", " + self.conf.vDomainDN
        try:
            int(quota)
        except ValueError:
            quota = None
        if quota:
            self.l.modify_s(dn, [(ldap.MOD_REPLACE, self.conf.attrs['mailuserquota'], quota)])
        else:
            self.l.modify_s(dn, [(ldap.MOD_DELETE, self.conf.attrs['mailuserquota'], None)])
        r.commit()

    def resetUsersVDomainQuota(self, domain):
        """
        Reset the quota of all users in the given virtual mail domain

        @param domain: virtual mail domain name
        @type domain: str
        """
        r = AF().log(PLUGIN_NAME, AA.MAIL_RESET_DOMAIN_QUOTA, [(domain, AT.VMDOMAIN)])
        vdomain = self.getVDomain(domain)
        mailuserquota = vdomain[0][1][self.conf.attrs['mailuserquota']][0]
        for user in self.getVDomainUsers(domain):
            self.changeUserAttributes(user[1]["uid"][0], self.conf.attrs['mailuserquota'], mailuserquota, False)
        r.commit()

    def getVDomain(self, domain):
        """
        Get a virtual mail domain name entry from directory

        @param domain: virtual mail domain name
        @type domain: str

        @rtype: dict
        """
        dn = "virtualdomain=" + domain + ", " + self.conf.vDomainDN
        return self.l.search_s(dn, ldap.SCOPE_BASE)

    def getVDomains(self, filt = ""):
        """
        Get virtual mail domain name list from directory

        @rtype: list
        """
        filt = filt.strip()
        if not filt: filt = "*"
        else: filt = "*" + filt + "*"
        return self.l.search_s(self.conf.vDomainDN, ldap.SCOPE_SUBTREE, "(&(objectClass=mailDomain)(virtualdomain=%s))" % filt)

    def getVAlias(self, alias):
        """
        Get a virtual alias entry from directory

        @param alias: virtual alias name
        @type alias: str

        @rtype: dict
        """
        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        attrib = self.l.search_s(dn, ldap.SCOPE_BASE)
        c, attrs = attrib[0]
        return attrs

    def getVAliases(self, filt = ""):
        """
        Get virtual aliases list from directory

        @rtype: list
        """
        filt = filt.strip()
        if not filt: filt = "*"
        else: filt = "*" + filt + "*"
        if self.conf.vAliasesSupport:
            return self.l.search_s(self.conf.vAliasesDN, ldap.SCOPE_SUBTREE, "(&(objectClass=mailAlias)(mailalias=%s))" % filt)
        else:
            return ()

    def addVAlias(self, alias):
        """
        Add a virtual alias entry in directory

        @param alias: virtual alias name
        @type alias: str
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        entry = {
            "mailalias": alias,
            "mailenable": "OK",
            "objectClass":  ("mailAlias", "top")
            }
        modlist = ldap.modlist.addModlist(entry)
        self.l.add_s(dn, modlist)
        return 0

    def changeVAliasEnable(self, alias, enabled):
        """
        Set the virtual alias mailenable attribute.
        This tells if the alias is active or not

        @param alias: alias name
        @type alias: str
        @param enabled: Boolean to specify if alias is enabled or not
        @type enabled: bool
        """

        if enabled:
            attr_val = "OK"
        else:
            attr_val = "NONE"

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        self.l.modify_s(dn, [(ldap.MOD_REPLACE, 'mailenable', attr_val)])
        return 0

    def changeVAliasName(self, alias, name):
        """
        Change the virtual alias name

        @param alias: current alias name
        @type alias: str
        @param name: new alias name
        @type name: str
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        self.l.modrdn_s(dn, "mailalias=%s" % name, True)
        return 0


    def delVAlias(self, alias):
        """
        Del a virtual alias entry from directory

        @param alias: virtual alias name
        @type alias: str
        """
        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        self.delRecursiveEntry(dn)
        return 0

    def getVAliasUsers(self, alias):
        """
        Get the user list of a virtual alias entry

        @param alias: virtual alias name
        @type alias: str
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        s = self.l.search_s(dn, ldap.SCOPE_BASE)
        c, attrs = s[0]
        users = []
        if "mailaliasmember" in attrs:
            for user in attrs["mailaliasmember"]:
                # get the user uid
                users.append(str2dn(user)[0][0][1])
        return users

    def addVAliasUser(self, alias, uid):
        """
        Add a LDAP user into a virtual alias entry

        @param alias: virtual alias name
        @type alias: str
        @param uid: user name
        @type uid: str
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        userdn = self.searchUserDN(uid)
        try:
            self.l.modify_s(dn, [(ldap.MOD_ADD, 'mailaliasmember', userdn)])
        except ldap.TYPE_OR_VALUE_EXISTS:
            # Can be safely ignored
            pass
        return 0

    def delVAliasUser(self, alias, uid):
        """
        Remove a LDAP user from a virtual alias entry

        @param alias: virtual alias name
        @type alias: str
        @param uid: user name
        @type uid: str
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        userdn = self.searchUserDN(uid)
        self.l.modify_s(dn, [(ldap.MOD_DELETE, 'mailaliasmember', userdn)])
        return 0

    def delVAliasesUser(self, uid):
        """
        Remove a LDAP user from all virtual aliases entries

        @param uid: the user uid
        @type uid: str
        """
        if self.conf.vAliasesSupport:
            userdn = self.searchUserDN(uid)
            for aliasDN, aliasData in self.getVAliases():
                if 'mailaliasmember' in aliasData and userdn in aliasData['mailaliasmember']:
                    self.delVAliasUser(aliasData['mailalias'][0], uid)
        return 0

    def addVAliasExternalUser(self, alias, mail):
        """
        Add an external user into a virtual alias entry

        @param alias: virtual alias name
        @type alias: str
        @param mail: user mail address
        @type mail: str
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        try:
            self.l.modify_s(dn, [(ldap.MOD_ADD, 'mail', mail)])
        except ldap.TYPE_OR_VALUE_EXISTS:
            # Can be safely ignored
            pass
        return 0

    def delVAliasExternalUser(self, alias, mail):
        """
        Remove an external user from a virtual alias entry

        @param alias: virtual alias name
        @type alias: str
        @param mail: user mail address
        @type mail: str
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        self.l.modify_s(dn, [(ldap.MOD_DELETE, 'mail', mail)])
        return 0

    def updateVAliasExternalUsers(self, alias, mails):
        """
        Add a list of mails adresses into a virtual alias entry.

        @param mails:
        @type: list of mail adresses
        """

        dn = "mailalias=" + alias + ", " + self.conf.vAliasesDN
        # Get current virtual alias entry
        s = self.l.search_s(dn, ldap.SCOPE_BASE)
        c, old = s[0]

        new = copy.deepcopy(old)

        new['mail'] = mails

        # Update LDAP
        modlist = ldap.modlist.modifyModlist(old, new)
        self.l.modify_s(dn, modlist)

    def changeMailEnable(self, uid, enabled):
        """
        Set the user mailenable attribute.
        This tells if the user receive mail or not.

        @param uid: user name
        @type uid: str
        @param enabled: Boolean to specify if mail is enabled or not
        @type enabled: bool
        """

        if enabled:
            action = AA.MAIL_ENABLE
            attr_val = "OK"
        else:
            action = AA.MAIL_DISABLE
            attr_val = "NONE"

        r = AF().log(PLUGIN_NAME, action, [(uid, AT.MAIL)], enabled)
        if not self.hasMailObjectClass(uid):
            self.addMailObjectClass(uid)
        self.changeUserAttributes(uid, self.conf.attrs['mailenable'], attr_val, False)
        r.commit()

    def changeMaildrop(self, uid, maildroplist):
        """
        Change the user mail drop.

        @param uid: user name
        @type uid: str
        @param maildroplist: a list of all mail drop
        @type maildroplist: list
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.MAIL_CHANGE_MAIL_DROP, [(userdn, AT.MAIL)], maildroplist)
        if not self.hasMailObjectClass(uid): self.addMailObjectClass(uid)
        self.changeUserAttributes(uid, self.conf.attrs['maildrop'], maildroplist, False)
        r.commit()

    def changeMailalias(self, uid, mailaliaslist):
        """
        Change the user mail aliases.

        @param uid: user name
        @type uid: str
        @param mailaliaslist: a list of all mail aliases
        @type mailaliaslist: list
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.MAIL_CHANGE_MAIL_ALIAS, [(userdn, AT.MAIL)], mailaliaslist)
        if not self.hasMailObjectClass(uid): self.addMailObjectClass(uid)
        self.changeUserAttributes(uid, self.conf.attrs['mailalias'], mailaliaslist, False)
        r.commit()

    def changeMailbox(self, uid, mailbox):
        """
        Change the user mailbox attribute (mail delivery directory).

        @param uid: user name
        @type uid: str
        @param mailbox: a list of all mail aliases
        @type mailbox: mailbox value
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.MAIL_CHANGE_MAIL_BOX, [(userdn, AT.MAIL)], mailbox)
        if not self.hasMailObjectClass(uid): self.addMailObjectClass(uid)
        if mailbox: self.changeUserAttributes(uid, self.conf.attrs['mailbox'], mailbox, False)
        r.commit()

    def changeMailhost(self, uid, mailhost):
        """
        Change the user mailhost attribute (mail delivery server).

        @param uid: user name
        @type uid: str
        @param mailhost: the FQDN or IP of the mail server
        @type mailhost: str
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.MAIL_CHANGE_MAIL_HOST, [(userdn, AT.MAIL)], mailhost)
        if not self.hasMailObjectClass(uid): self.addMailObjectClass(uid)
        self.changeUserAttributes(uid, self.conf.attrs['mailhost'], mailhost, False)
        r.commit()

    def changeQuota(self, uid, mailuserquota):
        """
        Change the user quota attribute.

        @param uid: user name
        @type uid: str
        @param mailuserquota: Quota in kB for uid
        @type mailuserquota: str
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.MAIL_CHANGE_MAIL_QUOTA, [(userdn, AT.MAIL)], mailuserquota)
        if not self.hasMailObjectClass(uid): self.addMailObjectClass(uid)
        self.changeUserAttributes(uid, self.conf.attrs['mailuserquota'], mailuserquota, False)
        r.commit()

    def removeMail(self, uid):
        r = AF().log(PLUGIN_NAME, AA.MAIL_DEL_MAIL_CLASS, [(uid, AT.MAIL)])
        self.removeUserObjectClass(uid, "mailAccount")
        r.commit()
        if self.hasZarafaSupport():
            r = AF().log(PLUGIN_NAME, AA.MAIL_DEL_ZARAFA_CLASS, [(uid, AT.MAIL)])
            self.removeUserObjectClass(uid, "zarafa-user")
            r.commit()

    def removeMailGroup(self, group):
        r = AF().log(PLUGIN_NAME, AA.MAIL_DEL_MAIL_CLASS, [(group, AT.MAIL)])
        self.removeGroupObjectClass(group, "mailGroup")
        r.commit()

    def hasMailObjectClass(self, uid):
        """
        Return true if the user owns the mailAccount objectClass.

        @param uid: user name
        @type uid: str

        @return: return True if the user owns the mailAccount objectClass.
        @rtype: boolean
        """
        userClasses = self.getDetailedUser(uid)["objectClass"]
        ret = "mailAccount" in userClasses
        if ret and self.hasZarafaSupport():
            ret = 'zarafa-user' in userClasses
        return ret

    def hasMailGroupObjectClass(self, group):
        """
        Return true if the group owns the mailGroup objectClass.

        @param group: group name
        @type group: str

        @return: return True if the user owns the mailGroup objectClass.
        @rtype: boolean
        """
        return "mailGroup" in self.getDetailedGroup(group)["objectClass"]

    def addMailObjectClass(self, uid, maildrop = None):
        r = AF().log(PLUGIN_NAME, AA.MAIL_ADD_MAIL_CLASS, [(uid, AT.MAIL)])
        # Get current user entry
        dn = 'uid=' + uid + ',' + self.baseUsersDN
        s = self.l.search_s(dn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = self._applyUserDefault(old, self.conf.userDefault)

        if not "mailAccount" in new["objectClass"]:
            new["objectClass"].append("mailAccount")

        if self.hasZarafaSupport():
            rz = AF().log(PLUGIN_NAME, AA.MAIL_ADD_ZARAFA_CLASS, [(uid, AT.MAIL)])
            if not 'zarafa-user' in new['objectClass']:
                new['objectClass'].append('zarafa-user')

        # Add maildrop attribute to user if we are not in virtual domain mode
        if maildrop == None and not self.hasVDomainSupport():
            maildrop = uid
            new[self.conf.attrs['maildrop']] = maildrop

        if self.hasVDomainSupport():
            # If the user has her/his mail address in a VDomain, set quota according to domain policy
            maildomain = new["mail"][0].split("@")[1]
            try:
                vdomain = self.getVDomain(maildomain)
                new[self.conf.attrs['mailuserquota']] = vdomain[0][1][self.conf.attrs['mailuserquota']]
            except ldap.NO_SUCH_OBJECT:
                pass
            except KeyError:
                pass

        # Update LDAP
        modlist = ldap.modlist.modifyModlist(old, new)
        self.l.modify_s(dn, modlist)
        r.commit()
        if self.hasZarafaSupport():
            rz.commit()

    def getVDomainUsersCount(self, domain):
        return len(self.search("(&(objectClass=mailAccount)(mail=*@%s))" % domain, self.baseUsersDN, [""]))

    def getVDomainUsers(self, domain, filt = ""):
        filt = filt.strip()
        if not filt: filt = "*"
        else: filt = "*" + filt + "*"
        return self.l.search_s(self.baseUsersDN, ldap.SCOPE_SUBTREE, "(&(objectClass=mailAccount)(mail=*@%s)(|(uid=%s)(givenName=%s)(sn=%s)(mail=%s)))" % (domain, filt, filt, filt, filt), ["uid", "givenName", "sn", "mail"])

    def addMailGroup(self, group, mail):
        r = AF().log(PLUGIN_NAME, AA.MAIL_ADD_MAIL_GROUP, [(group, AT.MAIL_GROUP)], mail)
        group = group.encode("utf-8")
        cn = 'cn=' + group + ', ' + self.baseGroupsDN
        attrs = []
        attrib = self.l.search_s(cn, ldap.SCOPE_BASE)
        c, attrs = attrib[0]
        newattrs = copy.deepcopy(attrs)
        if not 'mailGroup' in newattrs["objectClass"]:
            newattrs["objectClass"].append('mailGroup')
        if self.hasZarafaSupport() and not 'zarafa-group' in newattrs["objectClass"]:
            newattrs["objectClass"].append('zarafa-group')
        newattrs['mail'] = mail
        mlist = ldap.modlist.modifyModlist(attrs, newattrs)
        self.l.modify_s(cn, mlist)
        r.commit()

    def searchMailGroupAlias(self, mail):
        ret = self.search("(&(cn=*)(mail=%s@*))" % mail, self.baseGroupsDN, ["cn"])
        return len(ret) > 0

    def computeMailGroupAlias(self, group):
        """
        Find a mail alias that fits for a group.

        Non ASCII characters are replaced, and spaces are replaced with hyphens

        @param group: group name
        @type group: str

        @return: return the computed mail alias, or an empty string if it already exists
        @rtype: str
        """
        group = group.lower()
        group = delete_diacritics(group)
        group = group.replace(" ", "-")
        if self.searchMailGroupAlias(group):
            # This alias already exists
            return ""
        else:
            return group

    def deleteMailGroupAliases(self, group):
        """
        Remove the alias of this group from all user entries
        """
        r = AF().log(PLUGIN_NAME, AA.MAIL_DEL_MAIL_GRP_ALIAS, [(group, AT.MAIL_GROUP)], group)
        if hasMailGroupObjectClass(group):
            mailgroup = self.getDetailedGroup(group)["mail"][0]
            users = self.search("(&(uid=*)(%s=%s))" % (self.conf.attrs['mailalias'], mailgroup),
                self.baseUsersDN, ["uid", self.conf.attrs['mailalias']])
            for user in users:
                uid = user[0][1]["uid"][0]
                mailaliases = user[0][1][self.conf.attrs['mailalias']]
                mailaliases.remove(mailgroup)
                self.changeMailalias(uid, mailaliases)
        r.commit()

    def syncMailGroupAliases(self, group, foruser = "*"):
        """
        Sync all users mail aliases for this group
        """
        if hasMailGroupObjectClass(group):
            mailgroup = self.getDetailedGroup(group)["mail"][0]
            groupusers = self.getMembers(group)
            allusers = self.search("(&(uid=%s)(objectClass=mailAccount))" % foruser, self.baseUsersDN,
                ["uid", self.conf.attrs['mailalias']])
            for user in allusers:
                uid = user[0][1]["uid"][0]
                try:
                    mailaliases = user[0][1][self.conf.attrs['mailalias']]
                except KeyError:
                    mailaliases = []
                if uid in groupusers:
                    # Add group mail alias for users of the group that don't have it
                    if not mailgroup in mailaliases:
                        mailaliases.append(mailgroup)
                        self.changeMailalias(uid, mailaliases)
                else:
                    # Remove group mail alias for users that have it but are not in the group
                    if mailgroup in mailaliases:
                        mailaliases.remove(mailgroup)
                        self.changeMailalias(uid, mailaliases)

    def modifyZarafa(self, uid, attribute, value):
        events = { 'zarafaAdmin' : AA.MAIL_MOD_ZARAFA_ADMIN,
                 'zarafaSharedStoreOnly' : AA.MAIL_MOD_ZARAFA_SHAREDSTOREONLY,
                 'zarafaAccount' : AA.MAIL_MOD_ZARAFA_ACCOUNT,
                 'zarafaSendAsPrivilege' : AA.MAIL_MOD_ZARAFA_SENDASPRIVILEGE,
                 'zarafaHidden' : AA.MAIL_MOD_ZARAFA_HIDDEN }
        if attribute not in events:
            raise ValueError('Attribute %s is not managed' % attribute)
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, events[attribute], [(userdn, AT.MAIL)], value)
        if not self.hasMailObjectClass(uid):
            self.addMailObjectClass(uid)
        if not value:
            # If value is False or empty, we set the value to None so that it
            # is removed
            value = None
        elif value == True:
            value = '1'
        self.changeUserAttributes(uid, attribute, value, False)
        r.commit()

    def isZarafaGroup(self, group):
        """
        @param group: group name
        @type group: str

        @return: return True if the user owns the zarafa-group objectClass.
        @rtype: boolean
        """
        return "zarafa-group" in self.getDetailedGroup(group)["objectClass"]

    def setZarafaGroup(self, group, value):
        """
        @param group: group name
        @type group: str

        @param value: to set or unset the zarafa-group class
        @type value: boolean

        Set/unset zarafa-group object class to a user group
        """
        if value:
            event = AA.MAIL_ADD_ZARAFA_CLASS
        else:
            event = AA.MAIL_DEL_ZARAFA_CLASS
        r = AF().log(PLUGIN_NAME, event, [(group, AT.MAIL_GROUP)], group)
        group = group.encode("utf-8")
        cn = 'cn=' + group + ', ' + self.baseGroupsDN
        attrs = []
        attrib = self.l.search_s(cn, ldap.SCOPE_BASE)
        c, attrs = attrib[0]
        newattrs = copy.deepcopy(attrs)
        if value and not 'zarafa-group' in newattrs['objectClass']:
            newattrs["objectClass"].append('zarafa-group')
        elif not value and 'zarafa-group' in newattrs['objectClass']:
            newattrs["objectClass"].remove('zarafa-group')
        mlist = ldap.modlist.modifyModlist(attrs, newattrs)
        if mlist:
            self.l.modify_s(cn, mlist)
        r.commit()
