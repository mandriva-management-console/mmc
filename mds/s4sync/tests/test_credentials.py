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

import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))
from credentials import Credentials


def test_encode_keys_normal():
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


def test_decode_keys_normal():
    expected_uni = '\xba\xac9)\xfa\xbc\x9em\xcd2B\x1b\xa9J\x84\xd4'
    expected_sup = '\x00\x00\x00\x00\xa8\x01\x00\x00\x00\x00\x00\x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00 \x00P\x00\x02\x00 \x00\xe8\x00\x00\x00P\x00r\x00i\x00m\x00a\x00r\x00y\x00:\x00K\x00e\x00r\x00b\x00e\x00r\x00o\x00s\x000300000002000000180018004C0000000000000000000000030000000800000064000000000000000000000001000000080000006C000000000000000000000000000000000000000000000046004F004F002E00420041005200750073006500720031003ECE100B8F37FBDA3ECE100B8F37FBDA\x10\x00 \x00\x00\x00P\x00a\x00c\x00k\x00a\x00g\x00e\x00s\x004B00650072006200650072006F007300\x00'

    keys = [{'salt': 'FOO.BARuser1', 'type': 3, 'value': '>\xce\x10\x0b\x8f7\xfb\xda'},
            {'salt': 'FOO.BARuser1', 'type': 1, 'value': '>\xce\x10\x0b\x8f7\xfb\xda'},
            {'salt': 'FOO.BARuser1', 'type': 23, 'value': '\xba\xac9)\xfa\xbc\x9em\xcd2B\x1b\xa9J\x84\xd4'}]

    c = Credentials(krb5_keys=keys)

    assert len(c.supplemental_credentials) == len(expected_sup)
    assert c.supplemental_credentials == expected_sup
    assert len(c.unicode_pwd) == len(expected_uni)
    assert c.unicode_pwd == expected_uni
