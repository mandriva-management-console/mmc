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
#

import sys
import logging
import os

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))

from credentials import Credentials


def test_decode_keys_normal():
    sup = '\x00\x00\x00\x00\xa8\x01\x00\x00\x00\x00\x00\x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00P\x00\x02\x00 \x00\xe8\x00\x00\x00P\x00r\x00i\x00m\x00a\x00r\x00y\x00:\x00K\x00e\x00r\x00b\x00e\x00r\x00o\x00s\x000300000002000000180018004C0000000000000000000000030000000800000064000000000000000000000001000000080000006C000000000000000000000000000000000000000000000046004F004F002E0042004100520075007300650072003100C258DF8304297F76C258DF8304297F76\x10\x00 \x00\x00\x00P\x00a\x00c\x00k\x00a\x00g\x00e\x00s\x004B00650072006200650072006F007300\x00'
    uni = "\x9c\x90F'\xaeKE\xdf\xfe\x06L\xdf\xeb\xe72["

    c = Credentials(supplemental_credentials=sup, unicode_pwd=uni)
    keys = {k['type']: k for k in c.keys}

    assert len(keys) == 3
    assert 1 in keys and 3 in keys and 23 in keys

    assert keys[1]['salt'] == 'FOO.BARuser1'
    assert keys[1]['salt'] == keys[3]['salt']

    assert keys[1]['value'] == '\xc2X\xdf\x83\x04)\x7fv'
    assert keys[3]['value'] == '\xc2X\xdf\x83\x04)\x7fv'

    assert keys[23]['value'] == uni


def test_encode_keys_normal():
    expected_uni = '\xba\xac9)\xfa\xbc\x9em\xcd2B\x1b\xa9J\x84\xd4'
    expected_sup = '\x00\x00\x00\x00\xa8\x01\x00\x00\x00\x00\x00\x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00P\x00\x02\x00 \x00\xe8\x00\x00\x00P\x00r\x00i\x00m\x00a\x00r\x00y\x00:\x00K\x00e\x00r\x00b\x00e\x00r\x00o\x00s\x000300000002000000180018004C0000000000000000000000030000000800000064000000000000000000000001000000080000006C000000000000000000000000000000000000000000000046004F004F002E00420041005200750073006500720031003ECE100B8F37FBDA3ECE100B8F37FBDA\x10\x00 \x00\x00\x00P\x00a\x00c\x00k\x00a\x00g\x00e\x00s\x004B00650072006200650072006F007300\x00'

    keys = [{'salt': 'FOO.BARuser1', 'type': 3, 'value': '>\xce\x10\x0b\x8f7\xfb\xda'},
            {'salt': 'FOO.BARuser1', 'type': 1, 'value': '>\xce\x10\x0b\x8f7\xfb\xda'},
            {'salt': 'FOO.BARuser1', 'type': 23, 'value': '\xba\xac9)\xfa\xbc\x9em\xcd2B\x1b\xa9J\x84\xd4'}]

    c = Credentials(krb5_keys=keys)

    assert len(c.unicode_pwd) == len(expected_uni)
    assert c.unicode_pwd == expected_uni
    assert len(c.supplemental_credentials) == len(expected_sup)
    assert c.supplemental_credentials == expected_sup


def test_decode_keys_new():
    sup = '\x00\x00\x00\x00\x04\x04\x00\x00\x00\x00\x00\x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00P\x00\x03\x006\x00\xb8\x01\x01\x00P\x00r\x00i\x00m\x00a\x00r\x00y\x00:\x00K\x00e\x00r\x00b\x00e\x00r\x00o\x00s\x00-\x00N\x00e\x00w\x00e\x00r\x00-\x00K\x00e\x00y\x00s\x0004000000040000000000000024002400780000000000000000000000000000000000000012000000200000009C0000000000000000000000000000001100000010000000BC0000000000000000000000000000000300000008000000CC0000000000000000000000000000000100000008000000D400000054004F0054004F002E0043004F00520050002E0043004F004D00750073006500720032005D57C0F5CFD3B47EF030EC339C098D129FBD9F371813A4089EFA9C2DEF30086BBFE71BF62A5458309894929CA1007DE16797F78AC76738BF6797F78AC76738BF \x00\x00\x01\x01\x00P\x00r\x00i\x00m\x00a\x00r\x00y\x00:\x00K\x00e\x00r\x00b\x00e\x00r\x00o\x00s\x000300000002000000240024004C00000000000000000000000300000008000000700000000000000000000000010000000800000078000000000000000000000000000000000000000000000054004F0054004F002E0043004F00520050002E0043004F004D00750073006500720032006797F78AC76738BF6797F78AC76738BF\x10\x00p\x00\x02\x00P\x00a\x00c\x00k\x00a\x00g\x00e\x00s\x004B00650072006200650072006F0073002D004E0065007700650072002D004B0065007900730000004B00650072006200650072006F007300\x00'
    uni = 'qB\x8fs\n\x82\xb6$ \xb6\xd3m\xc9 \xe3\x89'

    c = Credentials(supplemental_credentials=sup, unicode_pwd=uni)
    keys = {k['type']: k for k in c.keys}

    assert len(keys) == 5
    assert all(k in (1, 3, 17, 18, 23) for k in keys.keys())

    assert keys[3]['salt'] == 'TOTO.CORP.COMuser2'
    assert keys[17]['salt'] == keys[3]['salt']
    assert keys[18]['salt'] == keys[3]['salt']

    assert keys[3]['value'] == 'g\x97\xf7\x8a\xc7g8\xbf'
    assert keys[23]['value'] == uni


def test_encode_keys_new():
    sup = '\x00\x00\x00\x00\x04\x04\x00\x00\x00\x00\x00\x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00P\x00\x03\x006\x00\xb8\x01\x01\x00P\x00r\x00i\x00m\x00a\x00r\x00y\x00:\x00K\x00e\x00r\x00b\x00e\x00r\x00o\x00s\x00-\x00N\x00e\x00w\x00e\x00r\x00-\x00K\x00e\x00y\x00s\x0004000000040000000000000024002400780000000000000000000000000000000000000012000000200000009C0000000000000000000000000000001100000010000000BC0000000000000000000000000000000300000008000000CC0000000000000000000000000000000100000008000000D400000054004F0054004F002E0043004F00520050002E0043004F004D00750073006500720032005D57C0F5CFD3B47EF030EC339C098D129FBD9F371813A4089EFA9C2DEF30086BBFE71BF62A5458309894929CA1007DE16797F78AC76738BF6797F78AC76738BF \x00\x00\x01\x01\x00P\x00r\x00i\x00m\x00a\x00r\x00y\x00:\x00K\x00e\x00r\x00b\x00e\x00r\x00o\x00s\x000300000002000000240024004C00000000000000000000000300000008000000700000000000000000000000010000000800000078000000000000000000000000000000000000000000000054004F0054004F002E0043004F00520050002E0043004F004D00750073006500720032006797F78AC76738BF6797F78AC76738BF\x10\x00p\x00\x02\x00P\x00a\x00c\x00k\x00a\x00g\x00e\x00s\x004B00650072006200650072006F0073002D004E0065007700650072002D004B0065007900730000004B00650072006200650072006F007300\x00'
    uni = 'qB\x8fs\n\x82\xb6$ \xb6\xd3m\xc9 \xe3\x89'

    keys = [{'salt': 'TOTO.CORP.COMuser2', 'type': 23, 'value': 'qB\x8fs\n\x82\xb6$ \xb6\xd3m\xc9 \xe3\x89'},
            {'salt': 'TOTO.CORP.COMuser2', 'type': 17, 'value': '\xbf\xe7\x1b\xf6*TX0\x98\x94\x92\x9c\xa1\x00}\xe1'},
            {'salt': 'TOTO.CORP.COMuser2', 'type': 18, 'value': ']W\xc0\xf5\xcf\xd3\xb4~\xf00\xec3\x9c\t\x8d\x12\x9f\xbd\x9f7\x18\x13\xa4\x08\x9e\xfa\x9c-\xef0\x08k'},
            {'salt': 'TOTO.CORP.COMuser2', 'type': 3, 'value': 'g\x97\xf7\x8a\xc7g8\xbf'},
            {'salt': 'TOTO.CORP.COMuser2', 'type': 1, 'value': 'g\x97\xf7\x8a\xc7g8\xbf'}]
    c = Credentials(krb5_keys=keys)

    assert len(c.supplemental_credentials) == len(sup)
    assert c.supplemental_credentials == sup
    assert len(c.unicode_pwd) == len(uni)
    assert c.unicode_pwd == uni


if __name__ == "__main__":
    logging.basicConfig(level=logging.DEBUG)
    test_decode_keys_normal()
    #test_encode_keys_normal()
    test_decode_keys_new()
    test_encode_keys_new()
