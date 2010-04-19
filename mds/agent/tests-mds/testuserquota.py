#!/usr/bin/python
# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2008 Mandriva, http://www.mandriva.com/
#
# $Id: testsamba.py 4870 2009-12-14 13:59:34Z cdelfosse $
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
# along with MMC; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

""" Unit tests for userquota plugin """

import unittest

import sys
import os
import os.path
import time

from mmc.plugins.base import ldapUserGroupControl
from mmc.plugins.userquota import UserQuotaConfig, UserQuotaControl


def cleanLdap():
    # Wipe out /home
    os.system("rm -fr /home/*")
    # Wipe out LDAP
    os.system("/etc/init.d/slapd stop")
    os.system("killall -9 slapd")
    os.system("rm -f /var/lib/ldap/*")
    os.system("rm -fr /var/backups/*.ldapdb")
    os.system("cp contrib/ldap/*.schema /etc/ldap/schema")
    os.system("echo slapd slapd/password1 string secret | debconf-set-selections")
    os.system("echo slapd slapd/password2 string secret | debconf-set-selections")
    os.system("dpkg-reconfigure -pcritical slapd")
    os.system("cp contrib/ldap/slapd.conf.userquota /etc/ldap/slapd.conf")
    os.system("/etc/init.d/slapd restart")
    time.sleep(5)
    # Create Base OU
    l = ldapUserGroupControl("tests-mds/basetest.ini")
    l.addOu("Groups", "dc=mandriva,dc=com")
    l.addOu("Users",  "dc=mandriva,dc=com")
    l.addOu("Computers",  "dc=mandriva,dc=com")


class testUserquota(unittest.TestCase):

    def setUp(self):
        cleanLdap()
        self.l = ldapUserGroupControl("tests-mds/basetest.ini")
        self.l.addGroup("allusers")
        self.u = UserQuotaControl(conffile = "tests-mds/userquotatest.ini", conffilebase = "tests-mds/basetest.ini")

    def test_quota(self):
        self.l.addUser("usertest", "userpass", "firstname", "sn")
        self.u.setDiskQuota("usertest", "/dev/hda1:1024:Root", "100")
        self.assertEqual(self.u.hasDiskQuotaObjectClass("usertest"), True)
        self.u.deleteDiskQuota("usertest", "/dev/hda1:1024:Root")
        self.assertEqual(self.u.hasDiskQuotaObjectClass("usertest"), False)
        self.u.setNetworkQuota("usertest", "Internet:0.0.0.0/0:any", "100")
        self.assertEqual(self.u.hasDiskQuotaObjectClass("usertest"), True)
        self.u.deleteNetworkQuota("usertest", "Internet:0.0.0.0/0:any")
        self.assertEqual(self.u.hasDiskQuotaObjectClass("usertest"), False)

if __name__ == "__main__":
    unittest.main()
