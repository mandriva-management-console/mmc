# -*- coding: utf-8; -*-
#
# (c) 2014 Mandriva, http://www.mandriva.com/
#
# This file is part of Management Console.
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
#   Jean-Philippe Braun <jpbraun@mandriva.com>

import binascii
import logging

from samba.ndr import ndr_unpack, ndr_pack
from samba.dcerpc import drsblobs


logger = logging.getLogger(__name__)


class Credentials(object):

    """
    Transform between this attributes:

            (supplementalCredentials, unicodePwd) <--> (krb5Key)

    The first ones are used on samba4 the latter on openldap using smbkb5
    overlay.
    """

    def __init__(self,
                 krb5_keys=None,
                 unicode_pwd=None,
                 supplemental_credentials=None):
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
        logger.debug("Encoding supplemental_credentials using keys: %s" % keys)
        self.unicode_pwd = keys.pop(23)['value']
        self.supplemental_credentials = UserProperties(keys).encode()
        logger.debug("Encoding done")

    def _decode_samba_credentials(self):
        logger.debug("Decoding supplemental_credentials")
        self.keys = UserProperties(self.supplemental_credentials).keys
        self.keys.append({'type': 23,
                          'value': self.unicode_pwd,
                          'salt': self.keys[0]['salt']})
        logger.debug("Decoded keys: %s" % self.keys)


class CommonDataType(object):

    def __str__(self):
        return self.encode()

    def decode(self):
        raise NotImplementedError("decode has not been implemented")

    def encode(self):
        raise NotImplementedError("encode has not been implemented")


class UserProperties(CommonDataType):

    """
    http://msdn.microsoft.com/en-us/library/cc245500.aspx
    """

    def __init__(self, kerberos_keys_or_raw_data):
        if isinstance(kerberos_keys_or_raw_data, dict):
            self.keys = kerberos_keys_or_raw_data
        else:
            self._raw = kerberos_keys_or_raw_data
            self.decode()

    def encode(self):
        package_names = []
        kerberos = []
        kerberos_newer_keys = []
        cred_List = []

        # Order matters
        for key_id in (3, 1):
            if key_id in self.keys:
                kerberos.append(self.keys[key_id])
        for key_id in (18, 17):
            if key_id in self.keys:
                kerberos_newer_keys.append(self.keys[key_id])

        if kerberos_newer_keys and kerberos:
            logger.debug("compute Primary:Kerberos-Newer-Keys")
            creddata_Primary_Kerberos_Newer = KerberosNewerKeysProperty(kerberos_newer_keys + kerberos).encode()
            credname_Primary_Kerberos_Newer = "Primary:Kerberos-Newer-Keys"
            cred_Primary_Kerberos_Newer = drsblobs.supplementalCredentialsPackage()
            cred_Primary_Kerberos_Newer.name = credname_Primary_Kerberos_Newer
            cred_Primary_Kerberos_Newer.name_len = len(credname_Primary_Kerberos_Newer)
            cred_Primary_Kerberos_Newer.data = creddata_Primary_Kerberos_Newer
            cred_Primary_Kerberos_Newer.data_len = len(creddata_Primary_Kerberos_Newer)
            cred_Primary_Kerberos_Newer.reserved = 1
            cred_List.append(cred_Primary_Kerberos_Newer)
            package_names.append('Kerberos-Newer-Keys')

        if kerberos:
            logger.debug("compute Primary:Kerberos")
            creddata_Primary_Kerberos = KerberosProperty(kerberos).encode()
            credname_Primary_Kerberos = "Primary:Kerberos"
            cred_Primary_Kerberos = drsblobs.supplementalCredentialsPackage()
            cred_Primary_Kerberos.name = credname_Primary_Kerberos
            cred_Primary_Kerberos.name_len = len(credname_Primary_Kerberos)
            cred_Primary_Kerberos.data = creddata_Primary_Kerberos
            cred_Primary_Kerberos.data_len = len(creddata_Primary_Kerberos)
            cred_Primary_Kerberos.reserved = 1
            cred_List.append(cred_Primary_Kerberos)
            package_names.append('Kerberos')

        if package_names:
            krb_blob_Packages = '\0'.join(package_names).encode('utf-16-le')
            cred_PackagesBlob_data = binascii.hexlify(krb_blob_Packages).upper()
            cred_PackagesBlob_name = "Packages"
            cred_PackagesBlob = drsblobs.supplementalCredentialsPackage()
            cred_PackagesBlob.name = cred_PackagesBlob_name
            cred_PackagesBlob.name_len = len(cred_PackagesBlob_name)
            cred_PackagesBlob.data = cred_PackagesBlob_data
            cred_PackagesBlob.data_len = len(cred_PackagesBlob_data)
            cred_PackagesBlob.reserved = 2
            cred_List.append(cred_PackagesBlob)
        else:
            raise Exception("Can't buid credentials, no keys provided")

        sub = drsblobs.supplementalCredentialsSubBlob()
        sub.num_packages = len(cred_List)
        sub.packages = cred_List
        sub.signature = drsblobs.SUPPLEMENTAL_CREDENTIALS_SIGNATURE
        sub.prefix = drsblobs.SUPPLEMENTAL_CREDENTIALS_PREFIX

        sc = drsblobs.supplementalCredentialsBlob()
        sc.sub = sub

        sc_blob = ndr_pack(sc)
        return sc_blob

    def decode(self):
        sc = ndr_unpack(drsblobs.supplementalCredentialsBlob, self._raw)
        kerberos_keys = []
        kerberos_newer_keys = []
        for p in sc.sub.packages:
            if p.name == "Primary:Kerberos":
                kerberos_keys = KerberosProperty(p.data).keys
            elif p.name == "Primary:Kerberos-Newer-Keys":
                kerberos_newer_keys = KerberosNewerKeysProperty(p.data).keys
            else:
                logger.debug("Ignored user property %r" % p.name)
        if kerberos_newer_keys:
            self.keys = kerberos_newer_keys
        elif kerberos_keys:
            self.keys = kerberos_keys
        else:
            raise Exception("Can't found any key!")


class KerberosProp(CommonDataType):

    def __init__(self, keys_or_raw_data):
        if isinstance(keys_or_raw_data, list):
            self.keys = keys_or_raw_data
        else:
            self._raw = binascii.unhexlify(str(keys_or_raw_data))
            self.decode()

    def check_key_value(self, key):
        lengths = {
            18: 32,  # aes256
            17: 16,  # aes12
            3: 8,    # des_md5
            1: 8     # des_crc
        }
        assert len(key['value']) == lengths[key['type']]

    def keys_to_blob(self, keys_list):
        keys = []
        for key in keys_list:
            # type = -140 can be safely iignored
            if key['type'] == 4294967156:
                continue
            self.check_key_value(key)
            keys.append(self.key_to_blob(key))
        return keys

    def key_to_blob(self, key):
        raise NotImplementedError()


class KerberosProperty(KerberosProp):

    def key_to_blob(self, key):
        k = drsblobs.package_PrimaryKerberosKey3()
        k.keytype = key['type']
        k.value = key['value']
        k.value_len = len(key['value'])
        return k

    def encode(self):
        keys = self.keys_to_blob(self.keys)

        salt3 = drsblobs.package_PrimaryKerberosString()
        salt3.string = self.keys[0]['salt']

        ctr3 = drsblobs.package_PrimaryKerberosCtr3()
        ctr3.salt = salt3
        ctr3.num_keys = len(keys)
        ctr3.keys = keys

        krb_Primary_Kerberos = drsblobs.package_PrimaryKerberosBlob()
        krb_Primary_Kerberos.version = 3
        krb_Primary_Kerberos.ctr = ctr3

        return binascii.hexlify(ndr_pack(krb_Primary_Kerberos)).upper()

    def decode(self):
        keys = []
        krb = ndr_unpack(drsblobs.package_PrimaryKerberosBlob, self._raw)
        assert krb.version == 3
        for key in krb.ctr.keys:
            keys.append({'type': key.keytype, 'value': key.value, 'salt': krb.ctr.salt.string})
        self.keys = keys


class KerberosNewerKeysProperty(KerberosProp):

    def key_to_blob(self, key):
        k = drsblobs.package_PrimaryKerberosKey4()
        k.keytype = key['type']
        k.value = key['value']
        k.value_len = len(key['value'])
        return k

    def encode(self):
        keys = self.keys_to_blob(self.keys)

        salt4 = drsblobs.package_PrimaryKerberosString()
        salt4.string = self.keys[0]['salt']

        ctr4 = drsblobs.package_PrimaryKerberosCtr4()
        ctr4.salt = salt4
        ctr4.num_keys = len(keys)
        ctr4.keys = keys

        krb_Primary_Kerberos_Newer = drsblobs.package_PrimaryKerberosBlob()
        krb_Primary_Kerberos_Newer.version = 4
        krb_Primary_Kerberos_Newer.ctr = ctr4

        return binascii.hexlify(ndr_pack(krb_Primary_Kerberos_Newer)).upper()

    def decode(self):
        keys = []
        krb = ndr_unpack(drsblobs.package_PrimaryKerberosBlob, self._raw)
        assert krb.version == 4
        for key in krb.ctr.keys:
            keys.append({'type': key.keytype, 'value': key.value, 'salt': krb.ctr.salt.string})
        self.keys = keys
