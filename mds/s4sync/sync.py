# -*- coding: utf-8; -*-
#
# (c) 2014 Mandriva, http://www.mandriva.com/
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
# Author(s):
#   Jesús García Sáez <jgarcia@zentyal.com>
#
from credentials import Credentials
from k5key_asn1 import encode_keys, decode_keys
from mmc.plugins.base.config import BasePluginConfig
from mmc.plugins.base import ldapUserGroupControl
from mmc.plugins.samba4 import getSamba4GlobalInfo
from mmc.plugins.samba4.samba4 import SambaAD
from mmc.support.config import PluginConfigFactory
import ldap
from ldap.controls import RequestControl
from datetime import datetime, timedelta
import pytz
import time
import os
import logging


class LdapUserMixin(object):

    """Mixin with common CRUD operations for users in LDAP: get user,
    list users, create user from another, delete user, sync fields from another
    user.

    This requires to define the following instance attributes:

      * self.user_base_dn
      * self.user_list_filter
      * self.user_pk_field
      * self.timestamp_field
      * self.user_ignore_list

    And also the following methods:

      * _create_user(self, username, password, name, surname)
        * Create the user in ldap.
      * _format_timestamp(self, timestamp_str)
        * Format a value of a self.timestamp_field attribute returning
          a datetime object.
    """
    FIELDS_TO_SYNC = ['displayName', 'givenName', 'sn']

    def _get_user(self, username, attrs=['*']):
        entries = self.l.search_s(self.user_base_dn, ldap.SCOPE_SUBTREE,
                                  filterstr='(%s=%s)' % (
                                      self.user_pk_field, username),
                                  attrlist=attrs)
        if len(entries) == 0:
            return (None, None)
        if len(entries) > 1:
            raise Exception("More than 1 entry found, wtf?")
        return entries[0]

    def get_user_attributes(self, username):
        dn, attrs = self._get_user(username)
        if not dn:
            raise UserNotFound(username, "get attributes")
        return attrs

    def list_users(self):
        entries = self.l.search_s(self.user_base_dn, ldap.SCOPE_SUBTREE,
                                  filterstr=self.user_list_filter,
                                  attrlist=[self.user_pk_field, self.timestamp_field])
        users = {}
        for e in entries:
            user = e[1][self.user_pk_field][0]
            if user not in self.user_ignore_list:
                timestamp_str = e[1][self.timestamp_field][0]
                users[user] = self._format_timestamp(timestamp_str)
#         logger.debug('users %s' % users)
        return users

    def sync_user_with(self, username, other_ldap):
        dn, attrs = self._get_user(username, self.FIELDS_TO_SYNC)
        if not dn:
            raise UserNotFound(username, "sync user attributes")
        other_attrs = other_ldap.get_user_attributes(username)
        modlist = [(ldap.MOD_REPLACE, field, other_attrs[field][0])
                   for field in self.FIELDS_TO_SYNC]
        self.l.modify_s(dn, modlist)

    def create_user(self, username, other_ldap):
        name, surname = username, username
        attrs = other_ldap.get_user_attributes(username)
        if attrs and 'givenName' in attrs and 'sn' in attrs:
            name = attrs["givenName"][0]
            surname = attrs["sn"][0]
        self._create_user(username, "thisWillChange", name, surname)

    def delete_user(self, username):
        dn, _ = self._get_user(username)
        if not dn:
            raise UserNotFound(username, "delete it")
        self.l.delete_s(dn)

    def list_groups(self):
        entries = self.l.search_s(self.group_base_dn,
                                  self.group_scope,
                                  filterstr=self.group_list_filter,
                                  attrlist=[self.group_pk_field, self.timestamp_field])
        groups = {}
        for e in entries:
            # FIXME: samba send a (None, ['ldap://mon.dom/CN=Configuration,DC=mon,DC=dom']) record
            # the algo
            if e[0] is None:
                continue
#             logger.debug('%s groups:\n    %s', self.__class__.__name__, e)
            group = e[1][self.group_pk_field][0]
            if group not in self.group_ignore_list:
                timestamp_str = e[1][self.timestamp_field][0]
                groups[group] = self._format_timestamp(timestamp_str)
#         logger.debug('groups %s' % groups)
        return groups

    def create_group(self, name, other_ldap):
        self._create_group(name)
        self.sync_group_with(name, other_ldap)

    def sync_group_with(self, name, other_ldap):
        """ other_ldap is the reference we sync to"""
        members = set(self.get_group_members(name))
        members_to_sync = set(other_ldap.get_group_members(name))
        members_to_del = members - members_to_sync
        members_to_add = members_to_sync - members
        self.del_group_members(name, members_to_del)
        self.add_group_members(name, members_to_add)

    def _get_group(self, name):
        raise NotImplementedError()

    def delete_group(self, name):
        dn = self._get_group(name)
        if dn is None:
            raise GroupNotFound(name)
        self.l.delete_s(dn)

    def get_group_members(self, group):
        raise NotImplementedError()

    def del_group_members(self, group, members=None):
        self._mod_group_members(group, ldap.MOD_DELETE, members)

    def add_group_members(self, group, members=None):
        self._mod_group_members(group, ldap.MOD_ADD, members)


class SambaLdap(LdapUserMixin):
    LDAP_URI = "ldapi://%2fvar%2flib%2fsamba%2fprivate%2fldap_priv%2fldapi"

    def __init__(self, base_dn):
        self.base_dn = base_dn
        self.l = ldap.initialize(self.LDAP_URI, trace_level=0)
        self.user_base_dn = "CN=Users,%s" % self.base_dn
        self.user_pk_field = "sAMAccountName"
        self.timestamp_field = "whenChanged"
        self.user_list_filter = '(&(&(&(objectclass=user)(!(objectclass=computer)))(!(isDeleted=*))))'
        self.user_ignore_list = ['Guest', 'krbtgt']
        self.group_base_dn = self.base_dn
        self.group_list_filter = '(&(objectClass=group)(sAMAccountType=268435456)(groupType=-2147483646))'
        self.group_ignore_list = ['Users']
        self.group_pk_field = self.user_pk_field
        self.group_scope = ldap.SCOPE_SUBTREE

    def _create_user(self, username, password, name, surname):
        SambaAD().createUser(username, password, name, surname)

    def _create_group(self, name, description=None):
        SambaAD().createGroup(name, description)

    def _format_timestamp(self, timestamp_str):
        date = datetime.strptime(timestamp_str, "%Y%m%d%H%M%S.0Z")
        return date.replace(tzinfo=pytz.UTC)

    def realm(self):
        return ".".join(self.base_dn.upper().split(',')).replace('DC=', '')

    def get_credentials(self, username):
        dn, attrs = self._get_user(
            username, [
                'unicodePwd', 'supplementalCredentials'])
        if not dn:
            raise UserNotFound(username, "get credentials")
        return (attrs['supplementalCredentials'][0], attrs['unicodePwd'][0])

    def update_credentials_for(
            self, username, supplemental_credentials, unicode_pwd, timestamp):
        dn, _ = self._get_user(username)
        if not dn:
            raise UserNotFound(username, "update credentials")
        modlist = [(ldap.MOD_REPLACE, 'supplementalCredentials', supplemental_credentials),
                   (ldap.MOD_REPLACE, 'unicodePwd', unicode_pwd)]
        # We also need to update pwdLastSet attribute
        # http://msdn.microsoft.com/en-us/library/ms679430%28v=vs.85%29.aspx
        delta = timestamp - datetime(1601, 1, 1, tzinfo=pytz.UTC)
        seconds = int(delta.total_seconds())
        modlist.append(
            (ldap.MOD_REPLACE, 'pwdLastSet', str(int(seconds * 1e7))))
        control = RequestControl('1.3.6.1.4.1.7165.4.3.12', True)
        self.l.modify_ext_s(dn, modlist, serverctrls=[control])

    def password_timestamp_for(self, username):
        dn, attrs = self._get_user(username, ['pwdLastSet'])
        if not dn:
            raise UserNotFound(username, "get password timestamp")
        if 'pwdLastSet' not in attrs:
            raise Exception("Samba User doesn't have pwdLastSet attribute")
        # pwdLastSet is 64-bit value representing the number of 100-nanosecond
        # intervals since January 1, 1601 (UTC).
        # http://msdn.microsoft.com/en-us/library/ms679430%28v=vs.85%29.aspx
        delta = timedelta(microseconds=int(attrs['pwdLastSet'][0]) / 10)
        return datetime(1601, 1, 1, tzinfo=pytz.UTC) + delta

    def update_password_timestamp_for(self, username, timestamp):
        dn, _ = self._get_user(username)
        if not dn:
            raise UserNotFound(username, "update password timestamp")
        delta = timestamp - datetime(1601, 1, 1, tzinfo=pytz.UTC)
        seconds = int(delta.total_seconds())
        modlist = [(ldap.MOD_REPLACE, 'pwdLastSet', str(int(seconds * 1e7)))]
        self.l.modify_ext_s(dn, modlist)

    def get_group_members(self, group):
        entries = self.l.search_s(self.group_base_dn,
                                  self.group_scope,
                                  filterstr='(&(objectClass=group)(sAMAccountName=%s))' % group,
                                  attrlist=['member'])
        # len is 2 cause Samba alwaus adds an entry with None
        if len(entries) > 2:
            raise Exception(
                'Many groups with the name ' + group + ' something\'s going wrong')
        members_dn = entries[0][1].get('member', [])
        members_cn = []
        # Checks that members_dn are simple users (not groups, cause of a
        # limitation in our OpenLdap) and get only the cn aka sAMAccountName
        for member in members_dn:
            current_cn = self.l.search_s(member,
                                         ldap.SCOPE_BASE,
                                         filterstr='(objectClass=user)',
                                         attrlist=['sAMAccountName'])
            logger.debug(current_cn)
            members_cn.append(current_cn[0][1]['sAMAccountName'][0])
        logger.debug(
            'Members of group %s in Samba: %s', group, members_cn)
        return members_cn

    def _get_group(self, name):
        entries = self.l.search_s(self.group_base_dn,
                                  self.group_scope,
                                  filterstr='(&(objectClass=group)(sAMAccountName=%s))' % name,
                                  attrlist=[self.group_pk_field])
        return entries[0][0]

    def _mod_group_members(self, group, mod_type, members=None,):
        groupdn = self._get_group(group)
        modlist = [(mod_type, 'member', 'CN=%s,%s' % (member, self.user_base_dn))
                   for member in members]
        if modlist:
            logger.debug('Samba self.l.modify_s(%s, %s)', groupdn, modlist)
            try:
                self.l.modify_s(groupdn, modlist)
            except ldap.ALREADY_EXISTS:
                # Cause Of the sync we may want to add a user to the group
                # which is its primary group, we just ignore the exception
                pass


class OpenLdap(LdapUserMixin):

    def __init__(self, base_dn, bind_dn, bind_pw, host='localhost'):
        self.l = ldap.open(host)
        self.l.bind_s(bind_dn, bind_pw)
        # FIXME: get base_dn from mmc base.ini conf
        self.base_dn = base_dn
        # FIXME: get uses_base_dn from mmc base.ini conf
        self.user_base_dn = "ou=People,%s" % self.base_dn
        self.user_pk_field = "uid"
        self.timestamp_field = "modifyTimestamp"
        self.user_list_filter = '(&(objectClass=inetOrgPerson)(objectClass=krb5KDCEntry))'
        self.user_ignore_list = []
        # FIXME: get group_base_dn from mmc base.ini conf
        self.group_base_dn = "ou=Group,%s" % self.base_dn
        self.group_list_filter = "(objectClass=*)"
        self.group_ignore_list = ['users']
        self.group_pk_field = "cn"
        self.group_scope = ldap.SCOPE_ONELEVEL

    def _create_user(self, username, password, name, surname):
        ldapUserGroupControl().addUser(username, password, name, surname)

    def _create_group(self, name, description=None):
        ldapUserGroupControl().addGroup(name)

    def _format_timestamp(self, timestamp_str):
        date = datetime.strptime(timestamp_str, "%Y%m%d%H%M%SZ")
        return date.replace(tzinfo=pytz.UTC)

    def enable_krb5_for(self, username, realm):
        dn, user = self._get_user(username)
        if not dn:
            return False
        principal_name = '%s@%s' % (username, realm.upper())
        modlist = [(ldap.MOD_ADD, 'objectclass', 'krb5KDCEntry'),
                   (ldap.MOD_ADD, 'krb5KeyVersionNumber', '0'),
                   (ldap.MOD_ADD, 'krb5PrincipalName', principal_name)]
        self.l.modify_s(dn, modlist)
        return True

    def get_keys(self, username):
        dn, attrs = self._get_user(username, ['krb5Key'])
        if not dn:
            raise UserNotFound(username, "get kerberos keys")
        if attrs:
            return attrs['krb5Key']

    def set_kerberos_keys_for(self, username, keys, timestamp):
        dn, _ = self._get_user(username)
        if not dn:
            raise UserNotFound(username, "set kerberos keys")
        modlist = [(ldap.MOD_REPLACE, 'krb5Key', keys),
                   (ldap.MOD_REPLACE, 'userPassword', '{K5KEY}')]
        self.l.modify_s(dn, modlist)
        # FIXME change ldap schema so pwdChangeTime can be modified?
        #changed_time = timestamp.strftime("%Y%m%d%H%M%SZ")
        #self.l.modify_s(dn, [(ldap.MOD_REPLACE, 'pwdChangedTime', changed_time)])

    def password_timestamp_for(self, username):
        dn, attrs = self._get_user(username, ['pwdChangedTime'])
        if not dn:
            raise UserNotFound(username, "get password timestamp")
        if 'pwdChangedTime' not in attrs:
            raise Exception("OpenLdap User doesn't have  attribute")
        return self._format_timestamp(attrs['pwdChangedTime'][0])

    def get_group_members(self, group):
        entries = self.l.search_s(self.group_base_dn,
                                  self.group_scope,
                                  filterstr='(&(objectClass=posixGroup)(cn=%s))' % group,
                                  attrlist=['memberUid'])
        if len(entries) > 1:
            raise Exception(
                'Many groups with the name ' + group + ' something going wrong')
        members = entries[0][1].get('memberUid', [])
        logger.debug('Members of group %s in OpenLdap: %s', group, members)
        return members

    def _get_group(self, name):
        entries = self.l.search_s(self.group_base_dn,
                                  self.group_scope,
                                  filterstr='(&(objectClass=posixGroup)(cn=%s))' % name,
                                  attrlist=[self.group_pk_field])
        return entries[0][0]

    def _mod_group_members(self, group, mod_type, members=None,):
        """@members: a list of cn"""
        groupdn = self._get_group(group)
        modlist = [(mod_type, 'memberUid', member)
                   for member in members]
        if modlist:
            logger.debug('OpenLdap self.l.modify_s(%s, %s)', groupdn, modlist)
            self.l.modify_s(groupdn, modlist)


class UserNotFound(Exception):

    def __init__(self, user, action):
        message = "Not found user %s when trying to %s" % (user, action)
        super(UserNotFound, self).__init__(message)


class GroupNotFound(Exception):

    def __init__(self, group, action):
        msg = "Not found group %s when trying to %s" % (group, action)
        super(GroupNotFound, self).__init__(msg)


def copy_password_from_samba_to_ldap(username, samba_ldap, openldap, timestamp):
    sup, uni = samba_ldap.get_credentials(username)
    creds = Credentials(unicode_pwd=uni, supplemental_credentials=sup)
    keys = encode_keys(creds.keys)
    openldap.set_kerberos_keys_for(username, keys, timestamp)
    # FIXME change openldap schema to mark pwdChangedTime as NO readonly
    openldap_timestamp = openldap.password_timestamp_for(username)
    samba_ldap.update_password_timestamp_for(username, openldap_timestamp)


def copy_password_from_ldap_to_samba(username, samba_ldap, openldap, timestamp):
    keys = openldap.get_keys(username)
    keys = decode_keys(keys)
    creds = Credentials(krb5_keys=keys)
    samba_ldap.update_credentials_for(username, creds.supplemental_credentials,
                                      creds.unicode_pwd, timestamp)

# -----------------------------------------------------------------------------


def get_samba_base_dn():
    """Return samba4 base dn using mmc samba4 plugin"""
    info = getSamba4GlobalInfo()
    if not info['realm']:
        return None
    return str('DC=%s' % ',DC='.join(info['realm'].split('.')))


def get_openldap_config():
    """Return OpenLdap credentials used by mmc base plugin"""
    mmc_base_config = PluginConfigFactory.new(BasePluginConfig, "base")
    return {'base_dn': mmc_base_config.baseDN,
            'bind_dn': mmc_base_config.username,
            'bind_pw': mmc_base_config.password}


class Samba4NotProvisioned(Exception):
    pass


class S4SyncTimestampError(Exception):
    pass


class S4Sync(object):
    TIMESTAMP_FORMAT = "%Y-%m-%d %H:%M:%S"
    TIMESTAMP_PATH = "/etc/s4sync.timestamp"

    def __init__(self, logger):
        self.logger = logger
        self.reset()

    def reset(self):
        samba_base_dn = get_samba_base_dn()
        if samba_base_dn is None:
            raise Samba4NotProvisioned()
#         self.logger.debug('Samba base dn: %s' % samba_base_dn)
        self.samba_ldap = SambaLdap(samba_base_dn)
        ldap_creds = get_openldap_config()
#         self.logger.debug('ldap config: %s' % ldap_creds)
        self.openldap = OpenLdap(ldap_creds['base_dn'], ldap_creds['bind_dn'],
                                 ldap_creds['bind_pw'])

    def _sync_password(self, user):
        """Sync password for an user that exists in both OpenLdap and Samba"""
        samba_timestamp = self.samba_ldap.password_timestamp_for(user)
        openldap_timestamp = self.openldap.password_timestamp_for(user)
        if samba_timestamp > openldap_timestamp:
            self.logger.info("Updating %s password on OpenLdap" % user)
            copy_password_from_samba_to_ldap(user, self.samba_ldap,
                                             self.openldap, samba_timestamp)
        elif openldap_timestamp > samba_timestamp:
            self.logger.info("Updating %s password on Samba" % user)
            copy_password_from_ldap_to_samba(user, self.samba_ldap,
                                             self.openldap, openldap_timestamp)

    def _sync_fields(self, user, samba_timestamp, openldap_timestamp):
        """Sync some fields of an user that exists in both OpenLdap and Samba"""
        if samba_timestamp > openldap_timestamp:
            self.logger.info("Updating %s info on OpenLdap" % user)
            self.openldap.sync_user_with(user, self.samba_ldap)
        elif openldap_timestamp > samba_timestamp:
            self.logger.info("Updating %s info on Samba" % user)
            self.samba_ldap.sync_user_with(user, self.openldap)

    def sync(self):
        def groups_mix():
            openldap_listed_groups = self.openldap.list_groups()
            samba_listed_groups = self.samba_ldap.list_groups()
            openldap_groups = set(openldap_listed_groups.keys())
            samba_groups = set(samba_listed_groups.keys())
            common_groups = samba_groups.intersection(openldap_groups)

            for group in common_groups:
                samba_timestamp = samba_listed_groups[group]
                openldap_timestamp = openldap_listed_groups[group]
                if samba_timestamp > last_sync_timestamp:
                    self.logger.debug(
                        '\tCommon group:\'%s\' changed in Samba, sync to OpenLdap', group)
                    self.openldap.sync_group_with(group, self.samba_ldap)
                elif openldap_timestamp > last_sync_timestamp:
                    self.logger.debug(
                        '\tCommon group:\'%s\' changed in OpenLdap, sync to Samba', group)
                    self.samba_ldap.sync_group_with(group, self.openldap)

            for group in openldap_groups - samba_groups:
                self.logger.debug(
                    '\tOpenLdap group %s is not in Samba', group)
                timestamp = openldap_listed_groups[group]
                if timestamp > last_sync_timestamp:
                    # New group, create it on the other side
                    self.logger.debug("\tCreating group %s on Samba" % group)
                    self.samba_ldap.create_group(group, self.openldap)
                else:
                    # Old group, remove it
                    self.logger.debug("\tDeleting group %s on OpenLdap because its "
                                      "timestamp `%s` is previous to the last sync `%s`"
                                      % (group, timestamp, last_sync_timestamp))
                    self.openldap.delete_group(group)

            for group in samba_groups - openldap_groups:
                self.logger.debug(
                    '\t Samba group \'%s\' is not in OpenLdap', group)
                timestamp = samba_listed_groups[group]
                if timestamp > last_sync_timestamp:
                    # New group, create it on the other side
                    self.logger.debug(
                        "\tCreating group %s on OpenLdap" % group)
                    self.openldap.create_group(group, self.samba_ldap)
                else:
                    # Old group, remove it
                    self.logger.debug("\tDeleting group %s on Samba because its "
                                      "timestamp `%s` is previous to the last sync `%s`"
                                      % (group, timestamp, last_sync_timestamp))
                    self.samba_ldap.delete_group(group)

        def users_mix():
            samba_listed_users = self.samba_ldap.list_users()
            samba_users = set(samba_listed_users.keys())
            openldap_listed_users = self.openldap.list_users()
            openldap_users = set(openldap_listed_users.keys())
            common_users = samba_users.intersection(openldap_users)
            for user in common_users:
                # Synchronize passwords
                self._sync_password(user)
                # Synchronize some fields
                samba_user_timestamp = samba_listed_users[user]
                openldap_user_timestamp = openldap_listed_users[user]
                user_changed_since_last_sync = (samba_user_timestamp > last_sync_timestamp or
                                                openldap_user_timestamp > last_sync_timestamp)
                if user_changed_since_last_sync:
                    self._sync_fields(
                        user,
                        samba_user_timestamp,
                        openldap_user_timestamp)

            # Users existing in OpenLdap but not in Samba.
            # We must either create it on Samba or delete it on OpenLdap.
            # Depending whether timestamp is newer than last execution or not.
            for user in openldap_users - samba_users:
                self.logger.debug("OpenLdap User %s is not in Samba" % user)
                user_timestamp = openldap_listed_users[user]
                if user_timestamp > last_sync_timestamp:
                    # Create it on Samba
                    self.logger.debug("\tCreating user %s on samba" % user)
                    self.samba_ldap.create_user(user, self.openldap)
                    # Set password from OpenLdap
                    openldap_timestamp = self.openldap.password_timestamp_for(
                        user)
                    copy_password_from_ldap_to_samba(user, self.samba_ldap,
                                                     self.openldap, openldap_timestamp)
                else:
                    # Delete it on OpenLdap
                    self.logger.debug("\tDeleting user %s on openldap because its "
                                      "timestamp `%s` is previous to the last sync `%s`"
                                      % (user, user_timestamp, last_sync_timestamp))
                    self.openldap.delete_user(user)

            # Users existing in Samba but not in OpenLdap.
            # We must either create it on Samba or delete it on OpenLdap.
            # Depending whether timestamp is newer than last execution or not.
            for user in samba_users - openldap_users:
                # Maybe the user exists but does not have krb5 enabled
                # Try to enable smbk5 overlay for this user
                if self.openldap.enable_krb5_for(user, self.samba_ldap.realm()):
                    self.logger.info("Enabled krb5 on OpenLdap user %s" % user)
                    # Set password from Samba
                    samba_timestamp = self.samba_ldap.password_timestamp_for(
                        user)
                    copy_password_from_samba_to_ldap(user, self.samba_ldap,
                                                     self.openldap, samba_timestamp)
                    continue

                self.logger.debug("Samba User %s is not in OpenLdap" % user)
                # User does not exist on OpenLdap
                user_timestamp = samba_listed_users[user]
                if user_timestamp > last_sync_timestamp:
                    # Create it on OpenLdap
                    self.logger.debug("\tCreating user %s on samba" % user)
                    self.openldap.create_user(user, self.samba_ldap)
                    # Enable krb5 overlay
                    if self.openldap.enable_krb5_for(
                            user, self.samba_ldap.realm()):
                        self.logger.info(
                            "\tEnabled krb5 on OpenLdap user %s" %
                            user)
                    else:
                        raise Exception("Failed to enabled krb5 on %s" % user)
                    # Set password from Samba
                    samba_timestamp = self.samba_ldap.password_timestamp_for(
                        user)
                    copy_password_from_samba_to_ldap(user, self.samba_ldap,
                                                     self.openldap, samba_timestamp)
                else:
                    # Delete it on Samba
                    self.logger.debug("\tDeleting user %s on samba because its "
                                      "timestamp (%s) is previous or equal to the "
                                      "last sync (%s)" %
                                      (user, user_timestamp, last_sync_timestamp))
                    self.samba_ldap.delete_user(user)

        now_timestamp = datetime.now(pytz.UTC)
        last_sync_timestamp = self.timestamp()

        groups_mix()
        users_mix()

        self.update_timestamp(now_timestamp)

    def timestamp(self):
        """
        Return last successful syncing time.

        This is used to determine either to delete or create users between
        OpenLdap and Samba.
        When a user is found in only one place:
          * It will be created if its timestamp (last modification timestamp
            attribute of the user) is after this timestamp.
          * It will be deleted if its timestamp (whenChanged on samba,
            modifyTimestamp on openldap) is before this timestamp.
        """
        if os.path.isfile(self.TIMESTAMP_PATH):
            # Read timestamp file
            try:
                with open(self.TIMESTAMP_PATH, 'r') as timestamp_file:
                    timestamp_content = timestamp_file.read()
                    try:
                        d = datetime.strptime(
                            timestamp_content,
                            self.TIMESTAMP_FORMAT)
                        return d.replace(tzinfo=pytz.UTC)
                    except ValueError:
                        raise S4SyncTimestampError("Badformed timestamp file")
            except IOError:
                raise S4SyncTimestampError("Error reading timestamp")
        elif os.path.exists(self.TIMESTAMP_PATH):
            # Exists but is not a file
            raise S4SyncTimestampError(
                "Timestamp file exists but is not a file")
        else:
            # Doesn't exist, write default value and return it
            beginning_of_time = datetime(1900, 1, 1, tzinfo=pytz.UTC)
            self.update_timestamp(beginning_of_time)
            return beginning_of_time

    def update_timestamp(self, timestamp):
        """Update syncing time."""
        timestamp_str = timestamp.strftime(self.TIMESTAMP_FORMAT)
        try:
            with open(self.TIMESTAMP_PATH, 'w') as timestamp_file:
                timestamp_file.write(timestamp_str)
        except IOError:
            raise S4SyncTimestampError("Error updating timestamp")


def sync_loop(logger, wait_time):
    try:
        s4sync = S4Sync(logger)
    except Samba4NotProvisioned:
        logger.error("Samba4 not provisioned? exiting...")
        return

    logger.info("S4Sync daemon started")
    while True:
        try:
            logger.debug('loop')
            s4sync.sync()
        except Samba4NotProvisioned:
            logger.error("Samba4 not provisioned? exiting...")
            return
        except S4SyncTimestampError:
            logger.exception("Error with timestamp")
            s4sync.reset()
        except:
            logger.exception("Error syncing")
            s4sync.reset()

        time.sleep(wait_time)


logger = None

if __name__ == "__main__":

    WAIT_TIME = 10  # sleep time between each iteration, in seconds

    def initialize_logging():
        global logger
        logger = logging.getLogger("s4sync")
        handler = logging.StreamHandler()
        formatter = logging.Formatter(
            '%(asctime)s - %(name)s - %(levelname)s - %(message)s')
        handler.setFormatter(formatter)
        logger.addHandler(handler)
        logger.setLevel(logging.DEBUG)
        return logger

    sync_loop(initialize_logging(), WAIT_TIME)
