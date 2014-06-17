from credentials import Credentials
from k5key_asn1 import encode_keys, decode_keys
import ldap
from ldap.controls import RequestControl
from datetime import datetime, timedelta
import pytz
import time
import logging


class SambaLdap(object):
    LDAP_URI = "ldapi://%2fopt%2fsamba4%2fprivate%2fldap_priv%2fldapi"
    NO_USERS = ['Guest']

    def __init__(self, base_dn):
        self.base_dn = base_dn
        self.l = ldap.initialize(self.LDAP_URI)

    def _get_user(self, username, attrs=['*']):
        entries = self.l.search_s("CN=Users,%s" % self.base_dn, ldap.SCOPE_SUBTREE,
                                   filterstr='(cn=%s)' % username, attrlist=attrs)
        if len(entries) == 0:
            return None
        if len(entries) > 1:
            raise Exception("More than 1 entry found, wtf?")
        return entries[0]

    def realm(self):
        return ".".join(self.base_dn.upper().split(',')).replace('DC=', '')

    def list_users(self):
        entries = self.l.search_s("CN=Users,%s" % self.base_dn, ldap.SCOPE_SUBTREE,
                                  filterstr='(&(&(&(objectclass=user)(!(objectclass=computer)))(!(isDeleted=*))(!(adminCount=*))))',
                                  attrlist=['*'])
        users = [e[1]['sAMAccountName'][0] for e in entries
                 if e[1]['sAMAccountName'][0] not in self.NO_USERS]
        return sorted(users)

    def get_credentials(self, username):
        _, attrs = self._get_user(username, ['unicodePwd', 'supplementalCredentials'])
        if attrs:
            return (attrs['supplementalCredentials'][0], attrs['unicodePwd'][0])

    def update_credentials_for(self, username, supplemental_credentials, unicode_pwd, timestamp):
        dn, _ = self._get_user(username)
        modlist = [(ldap.MOD_REPLACE, 'supplementalCredentials', supplemental_credentials),
                   (ldap.MOD_REPLACE, 'unicodePwd', unicode_pwd)]
        # We also need to update pwdLastSet attribute
        # http://msdn.microsoft.com/en-us/library/ms679430%28v=vs.85%29.aspx
        delta = timestamp - datetime(1601, 1, 1, tzinfo=pytz.UTC)
        seconds = int(delta.total_seconds())
        modlist.append((ldap.MOD_REPLACE, 'pwdLastSet', str(int(seconds * 1e7))))
        control = RequestControl('1.3.6.1.4.1.7165.4.3.12', True)
        self.l.modify_ext_s(dn, modlist, serverctrls=[control])

    def password_timestamp_for(self, username):
        _, attrs = self._get_user(username, ['pwdLastSet'])
        if 'pwdLastSet' not in attrs:
            raise Exception('Samba User doesn\'t have pwdLastSet attribute')
        # pwdLastSet is 64-bit value representing the number of 100-nanosecond
        # intervals since January 1, 1601 (UTC).
        # http://msdn.microsoft.com/en-us/library/ms679430%28v=vs.85%29.aspx
        delta = timedelta(microseconds=int(attrs['pwdLastSet'][0]) / 10)
        return datetime(1601, 1, 1, tzinfo=pytz.UTC) + delta

    def update_password_timestamp_for(self, username, timestamp):
        dn, _ = self._get_user(username)
        delta = timestamp - datetime(1601, 1, 1, tzinfo=pytz.UTC)
        seconds = int(delta.total_seconds())
        modlist = [(ldap.MOD_REPLACE, 'pwdLastSet', str(int(seconds * 1e7)))]
        self.l.modify_ext_s(dn, modlist)


class OpenLdap(object):
    def __init__(self, base_dn, bind_dn, bind_pw, host='localhost'):
        self.l = ldap.open(host)
        self.l.bind_s(bind_dn, bind_pw)
        self.base_dn = base_dn

    def _get_user(self, username, attrs=['*']):
        entries = self.l.search_s('ou=People,%s' % self.base_dn, ldap.SCOPE_SUBTREE,
                                  filterstr='(uid=%s)' % username, attrlist=attrs)
        if len(entries) == 0:
            return None
        if len(entries) > 1:
            raise Exception("More than 1 entry found, wtf?")
        return entries[0]

    def list_users(self):
        entries = self.l.search_s('ou=People,%s' % self.base_dn, ldap.SCOPE_SUBTREE,
                                  filterstr='(&(objectClass=inetOrgPerson)(objectClass=krb5KDCEntry))',
                                  attrlist=['uid'])
        return sorted([e[1]['uid'][0] for e in entries])

    def enable_krb5_for(self, username, realm):
        dn, user = self._get_user(username)
        if not dn:
            raise Exception("User not found %s" % username)
        modlist = [(ldap.MOD_ADD, 'objectclass', 'krb5KDCEntry'),
                   (ldap.MOD_ADD, 'krb5KeyVersionNumber', '0'),
                   (ldap.MOD_ADD, 'krb5PrincipalName', '%s@%s' % (username, realm.upper()))]
        self.l.modify_s(dn, modlist)

    def get_keys(self, username):
        _, attrs = self._get_user(username, ['krb5Key'])
        if attrs:
            return attrs['krb5Key']

    def set_kerberos_keys_for(self, username, keys, timestamp):
        dn, _ = self._get_user(username)
        modlist = [(ldap.MOD_REPLACE, 'krb5Key', keys),
                   (ldap.MOD_REPLACE, 'userPassword', '{K5KEY}')]
        self.l.modify_s(dn, modlist)
        #FIXME change ldap schema so pwdChangeTime can be modified?
        #changed_time = timestamp.strftime("%Y%m%d%H%M%SZ")
        #self.l.modify_s(dn, [(ldap.MOD_REPLACE, 'pwdChangedTime', changed_time)])

    def password_timestamp_for(self, username):
        _, attrs = self._get_user(username, ['pwdChangedTime'])
        if 'pwdChangedTime' not in attrs:
            raise Exception("OpenLdap User doesn't have  attribute")
        date = datetime.strptime(attrs['pwdChangedTime'][0], "%Y%m%d%H%M%SZ")
        return date.replace(tzinfo=pytz.UTC)


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
    """
    Return samba4 base dn using mmc samba4 plugin
    """
    from mmc.plugins.samba4 import getSamba4GlobalInfo
    info = getSamba4GlobalInfo()
    if 'realm' not in info:
        return None
    return str('DC=%s' % ',DC='.join(info['realm'].split('.')))


def get_openldap_config():
    """
    Return OpenLdap credentials used by mmc base plugin
    """
    from mmc.support.config import PluginConfigFactory
    from mmc.plugins.base.config import BasePluginConfig
    mmc_base_config = PluginConfigFactory.new(BasePluginConfig, "base")
    return {'base_dn': mmc_base_config.baseDN,
            'bind_dn': mmc_base_config.username,
            'bind_pw': mmc_base_config.password}


class S4Sync(object):
    def __init__(self, logger):
        try:
            self.reset()
        except:
            pass
        self.logger = logger

    def reset(self):
        samba_base_dn = get_samba_base_dn()
        if samba_base_dn is None:
            raise Exception("Samba4 is not provisioned")
        self.samba_ldap = SambaLdap(samba_base_dn)
        ldap_creds = get_openldap_config()
        self.openldap = OpenLdap(ldap_creds['base_dn'], ldap_creds['bind_dn'], ldap_creds['bind_pw'])

    def sync(self):
        samba_users = set(self.samba_ldap.list_users())
        openldap_users = set(self.openldap.list_users())

        common_users = samba_users.intersection(openldap_users)

        for user in common_users:
            samba_timestamp = self.samba_ldap.password_timestamp_for(user)
            openldap_timestamp = self.openldap.password_timestamp_for(user)
            if samba_timestamp > openldap_timestamp:
                self.logger.info("Updating %s password on OpenLdap" % user)
                copy_password_from_samba_to_ldap(user, self.samba_ldap, self.openldap, samba_timestamp)
            elif openldap_timestamp > samba_timestamp:
                self.logger.info("Updating %s password on Samba" % user)
                copy_password_from_ldap_to_samba(user, self.samba_ldap, self.openldap, openldap_timestamp)

        for user in openldap_users - samba_users:
            # FIXME do something?
            self.logger.debug("OpenLdap User %s is not in Samba" % user)

        for user in samba_users - openldap_users:
            # Try to enable smbk5 overlay for this user
            self.openldap.enable_krb5_for(user, self.samba_ldap.realm())
            self.logger.info("Enabled krb5 on OpenLdap user %s" % user)


if __name__ == "__main__":

    WAIT_TIME = 10  # sleep time between each iteration, in seconds

    logger = logging.getLogger("s4sync")
    handler = logging.StreamHandler()
    formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    handler.setFormatter(formatter)
    logger.addHandler(handler)
    logger.setLevel(logging.DEBUG)

    s4sync = S4Sync(logger)
    logger.info("S4Sync daemon started")
    while True:
        try:
            s4sync.sync()
        except:
            logger.exception("Error syncing")
            s4sync.reset()
        time.sleep(WAIT_TIME)
