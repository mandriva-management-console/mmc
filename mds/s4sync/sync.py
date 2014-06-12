import ldap
from ldap.controls import RequestControl
from credentials import Credentials
from k5key_asn1 import encode_keys, decode_keys


class SambaLdap(object):
    LDAP_URI = "ldapi://%2fopt%2fsamba4%2fprivate%2fldap_priv%2fldapi"

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

    def get_credentials(self, username):
        _, attrs = self._get_user(username, ['unicodePwd', 'supplementalCredentials'])
        if attrs:
            return (attrs['supplementalCredentials'][0], attrs['unicodePwd'][0])

    def update_credentials_for(self, username, supplemental_credentials, unicode_pwd):
        dn, _ = self._get_user(username)
        modlist = [(ldap.MOD_REPLACE, 'supplementalCredentials', supplemental_credentials),
                   (ldap.MOD_REPLACE, 'unicodePwd', unicode_pwd)]
        control = RequestControl('1.3.6.1.4.1.7165.4.3.12', True)
        self.l.modify_ext_s(dn, modlist, serverctrls=[control])


class OpenLdap(object):
    def __init__(self, base_dn, bind_dn, bind_pw, host='localhost'):
        self.l = ldap.open(host)
        self.l.bind_s(bind_dn, bind_pw)
        self.base_dn = base_dn

    def _get_user(self, username, attrs=['*']):
        entries = self.l.search_s(self.base_dn, ldap.SCOPE_SUBTREE,
                                  filterstr='(uid=%s)' % username, attrlist=attrs)
        if len(entries) == 0:
            return None
        if len(entries) > 1:
            raise Exception("More than 1 entry found, wtf?")
        return entries[0]

    def get_keys(self, username):
        _, attrs = self._get_user(username, ['krb5Key'])
        if attrs:
            return attrs['krb5Key']

    def set_kerberos_keys_for(self, user, keys):
        dn, _ = self._get_user(username)
        modlist = [(ldap.MOD_REPLACE, 'krb5Key', keys),
                   (ldap.MOD_REPLACE, 'userPassword', '{K5KEY}')]
        self.l.modify_s(dn, modlist)


def copy_password_from_samba_to_ldap(username, samba_ldap, openldap):
    sup, uni = samba_ldap.get_credentials(username)
    creds = Credentials(unicode_pwd=uni, supplemental_credentials=sup)
    keys = encode_keys(creds.keys)
    openldap.set_kerberos_keys_for(username, keys)


def copy_password_from_ldap_to_samba(username, samba_ldap, openldap):
    keys = openldap.get_keys(username)
    keys = decode_keys(keys)
    creds = Credentials(krb5_keys=keys)
    samba_ldap.update_credentials_for(username, creds.supplemental_credentials,
                                      creds.unicode_pwd)

# -----------------------------------------------------------------------------

username = 'user1'
samba_base_dn = 'dc=foo,dc=bar'
ldap_creds = {'base_dn': 'dc=example,dc=com',
              'bind_dn': 'uid=LDAP Admin,ou=System Accounts,dc=example,dc=com',
              'bind_pw': 'foobar'}

samba_ldap = SambaLdap(samba_base_dn)
openldap = OpenLdap(ldap_creds['base_dn'], ldap_creds['bind_dn'], ldap_creds['bind_pw'])

for _ in xrange(2):
    copy_password_from_samba_to_ldap(username, samba_ldap, openldap)
    copy_password_from_ldap_to_samba(username, samba_ldap, openldap)
