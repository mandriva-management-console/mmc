# -*- coding: utf-8; -*-
#
# (c) 2014 Zentyal S.L., http://www.zentyal.com
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

from pyasn1.type import univ, char, namedtype, namedval, tag, constraint, useful
from pyasn1.codec.ber import encoder, decoder

def _OID(*components):
    output = []
    for x in tuple(components):
        if isinstance(x, univ.ObjectIdentifier):
            output.extend(list(x))
        else:
            output.append(int(x))

    return univ.ObjectIdentifier(output)


class Salt(univ.Sequence):
    componentType = namedtype.NamedTypes(
        namedtype.NamedType('type', univ.Integer().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatSimple, 0))),
        namedtype.NamedType('salt', univ.OctetString().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatSimple, 1))),
        namedtype.OptionalNamedType('opaque', univ.OctetString().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatSimple, 2)))
    )


class EncryptionKey(univ.Sequence):
    componentType = namedtype.NamedTypes(
        namedtype.NamedType('keytype', univ.Integer().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatSimple, 0))),
        namedtype.NamedType('keyvalue', univ.OctetString().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatSimple, 1)))
    )


class Key(univ.Sequence):
    componentType = namedtype.NamedTypes(
        namedtype.OptionalNamedType('mkvno', univ.Integer().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatSimple, 0))),
        namedtype.NamedType('key', EncryptionKey().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatConstructed, 1))),
        namedtype.OptionalNamedType('salt', Salt().subtype(explicitTag=tag.Tag(tag.tagClassContext, tag.tagFormatConstructed, 2)))
    )


def encode_keys(keys):
    """
    Encode into asn.1 format given kerberos keys which must be an array of
    dictionaries with the following keys: type, value, salt
    """
    ret = []
    if not isinstance(keys, list):
        raise ValueError("Keys parameter must be a list of dict")
    for key in keys:
        if not isinstance(key, dict):
            raise ValueError("Each key must be a dict")
        if 'type' not in key or 'value' not in key:
            raise ValueError("A key must have 'type' and 'value' entries")
        k = Key()
        # mkvno
        k.setComponentByPosition(0, 0)
        # key
        ek = k.setComponentByPosition(1).getComponentByPosition(1)
        ek.setComponentByPosition(0, key['type'])
        ek.setComponentByPosition(1, key['value'])
        if 'salt' in key:
            # salt
            salt = k.setComponentByPosition(2).getComponentByPosition(2)
            salt.setComponentByPosition(0, 3)
            salt.setComponentByPosition(1, key['salt'])
            salt.setComponentByPosition(2, '')
        # Encode and add it to ret values
        ret.append(encoder.encode(k))
    return ret


def decode_keys(keys):
    """
    Decode asn.1 representation of kerberos keys. Returns an array of
    dictionaries with the following keys: type, value, salt
    """
    ret = []
    if not isinstance(keys, list):
        raise ValueError("Keys parameter must be a list of dict")
    for key in keys:
        if not isinstance(key, str):
            raise ValueError("Each key must be a str")
        k = decoder.decode(key, asn1Spec=Key())[0]
        key = {}
        key['type'] = int(k.getComponentByName('key').getComponentByName('keytype'))
        key['value'] = str(k.getComponentByName('key').getComponentByName('keyvalue'))
        if k.getComponentByName('salt'):
            key['salt'] = str(k.getComponentByName('salt').getComponentByName('salt'))
        ret.append(key)
    return ret
