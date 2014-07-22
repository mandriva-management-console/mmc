import struct
import binascii
from struct import pack, unpack, calcsize


class Credentials(object):
    """
    Transform between this attributes:

            (supplementalCredentials, unicodePwd) <--> (krb5Key)

    The first ones are used on samba4 the latter on openldap using smbkb5
    overlay.
    """
    def __init__(self, krb5_keys=None, unicode_pwd=None, supplemental_credentials=None):
        if krb5_keys:
            self.keys = krb5_keys
            self._encode_samba_credentials()
        elif unicode_pwd and supplemental_credentials:
            self.unicode_pwd = unicode_pwd
            self.supplemental_credentials = supplemental_credentials
            self._decode_samba_credentials()
        else:
            raise ValueError("You must supply either krb5_keys or unicode_pwd "
                             "and supplemental_credentials parameters")

    def _encode_samba_credentials(self):
        keys = {key['type']: key for key in self.keys}

        if any(key_type not in keys for key_type in (1, 3, 23)):
            raise ValueError("Kerberos keys don't have the proper types, "
                             "expected 1, 3 and 23")

        self.unicode_pwd = keys[23]['value']
        self.supplemental_credentials = UserProperties([keys[3], keys[1]]).encode()

    def _decode_samba_credentials(self):
        self.keys = UserProperties(self.supplemental_credentials).keys
        self.keys.append({'type': 23, 'value': self.unicode_pwd, 'salt': self.keys[0]['salt']})


class CommonDataType(object):
    def __str__(self):
        return self.encode()

    def decode(self):
        raise NotImplemented("decode has not been implemented")

    def encode(self):
        raise NotImplemented("encode has not been implemented")


class UserProperties(CommonDataType):
    """
    http://msdn.microsoft.com/en-us/library/cc245500.aspx
    """
    def __init__(self, kerberos_keys_or_raw_data):
        if isinstance(kerberos_keys_or_raw_data, list):
            self.keys = kerberos_keys_or_raw_data
        else:
            self._raw = kerberos_keys_or_raw_data
            self.decode()

    def _encode_user_properties(self):
        return [UserProperty('Primary:Kerberos', KerberosProperty(self.keys)),
                UserProperty('Packages', 'Kerberos'.encode('utf-16-le'))]

    def _fmt(self, len_reserved4, len_user_properties):
        return "<lLhh%dshh%dsB" % (len_reserved4, len_user_properties)

    def encode(self):
        reserved4 = binascii.unhexlify("2000") * 48
        signature = 0x50
        user_properties = self._encode_user_properties()
        properties_count = len(user_properties)
        user_properties_str = ''.join([str(up) for up in user_properties])
        total_len = 4 + len(reserved4) + len(user_properties_str)
        fmt = self._fmt(len_reserved4=len(reserved4), len_user_properties=len(user_properties_str))
        return pack(fmt, 0, total_len, 0, 0, reserved4, signature, properties_count,
                    user_properties_str, 0)

    def decode(self):
        len_reserved4 = 96
        total_len = calcsize(self._fmt(len_reserved4, 0))
        len_user_properties = len(self._raw) - total_len
        data = unpack(self._fmt(len_reserved4, len_user_properties), self._raw)
        keys = []
        properties_count = data[6]
        user_properties_str = data[7]
        offset = 0
        for i in xrange(0, properties_count):
            prop = UserProperty(user_properties_str[offset:])
            if prop.name == "Primary:Kerberos".encode('utf-16-le'):
                self.keys = KerberosProperty(prop.value).keys
            else:
                print "Ignored user property %r" % prop.name.decode('utf-16-le')
            offset += prop.size


class KerberosKeyData(CommonDataType):
    """
    http://msdn.microsoft.com/en-us/library/cc245504.aspx
    """
    def __init__(self, key_or_raw_data, offset=None):
        self.size = calcsize(self._fmt())
        if offset is None:
            self._raw = key_or_raw_data
            self.decode()
        else:
            self.key_type = key_or_raw_data['type']
            self.key_length = len(key_or_raw_data['value'])
            self.offset = offset

    def _fmt(self):
        return '<hhlLLl'

    def encode(self):
        return pack(self._fmt(), 0, 0, 0, self.key_type, self.key_length, self.offset)

    def decode(self):
        (_, _, _, self.key_type, self.key_length, self.offset) = unpack(self._fmt(), self._raw[:self.size])


class KerberosProperty(CommonDataType):
    """
    http://msdn.microsoft.com/en-us/library/cc245503.aspx
    """
    def __init__(self, keys_or_raw_data):
        if isinstance(keys_or_raw_data, list):
            self.keys = keys_or_raw_data
        else:
            self._raw = binascii.unhexlify(str(keys_or_raw_data))
            self.decode()

    def _fmt(self, len_credentials=None):
        if len_credentials is None:
            return "<hhhhhhL"
        else:
            return "<hhhhhhL%ds20x" % len_credentials

    def encode(self):
        revision = 3
        flags = 0
        salt = self.keys[0]['salt'].encode('utf-16-le')
        len_default_salt = len(salt)
        len_default_salt_max = len_default_salt

        credentials = []
        old_credentials = []
        key_values = []
        key_value_offset = 16 + 20 + len(self.keys) * 20 + len_default_salt
        for key in self.keys:
            credentials.append(KerberosKeyData(key, key_value_offset))
            key_values.append(key['value'])
            key_value_offset += 8

        credentials_str = ''.join([str(cred) for cred in credentials])
        old_credentials_str = ''.join(old_credentials)
        values_str = ''.join(key_values)
        default_salt_offset = 16 + 20 + len(credentials_str) + len(old_credentials_str)
        fmt = self._fmt(len(credentials_str))
        ret = pack(fmt, revision, flags, len(credentials), len(old_credentials), len_default_salt,
                   len_default_salt_max, default_salt_offset, credentials_str)
        return ret + old_credentials_str + salt + values_str

    def decode(self):
        keys = []
        fmt = self._fmt()
        (revision, flags, n_creds, n_old_creds,
         len_salt, len_max_salt, salt_offset) = unpack(fmt, self._raw[:calcsize(fmt)])
        if revision != 3:
            raise ValueError("Revision must be 3 (%x)" % revision)
        salt = self._raw[salt_offset:salt_offset+len_max_salt].decode('utf-16-le')
        offset = calcsize(fmt)
        for i in xrange(0, n_creds):
            key_data = KerberosKeyData(self._raw[offset:])
            key_type = key_data.key_type
            key_value = self._raw[key_data.offset:key_data.offset+key_data.key_length]
            keys.append({'type': key_type, 'value': key_value, 'salt': salt})
            offset += key_data.size
        self.keys = keys


class UserProperty(CommonDataType):
    """
    http://msdn.microsoft.com/en-us/library/cc245501.aspx
    """
    def __init__(self, name_or_raw_data, value=None):
        if value is None:
            self._raw = name_or_raw_data
            self.decode()
        else:
            self.name = name_or_raw_data.encode('utf-16-le')
            self.value = binascii.hexlify(str(value)).upper()
        self.size = 0

    def _fmt(self, len_name=None, len_value=None):
        if len_name is not None and len_value is not None:
            if len_name is None or len_value is None:
                raise ValueError("Parameter required: len_name and len_value")
            fmt = "<hhh%ds%ds" % (len_name, len_value)
            self.size = calcsize(fmt)
            return fmt
        else:
            return "<hhh"

    def encode(self):
        len_name = len(self.name)
        len_value = len(self.value)
        return pack(self._fmt(len_name, len_value), len_name, len_value,
                    0, self.name, self.value)

    def decode(self):
        fmt = self._fmt()
        (len_name, len_value, _) = unpack(fmt, self._raw[0:calcsize(fmt)])
        fmt = self._fmt(len_name, len_value)
        (_, _, _, self.name, self.value) = unpack(fmt, self._raw[0:calcsize(fmt)])
