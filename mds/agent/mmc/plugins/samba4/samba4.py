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
#   Kamen Mazdrashki <kmazdrashki@zentyal.com>
#

import logging
import os
import sys

from mmc.support.mmctools import shlaunch, to_str

from mmc.plugins.samba4.config import Samba4Config
from mmc.plugins.samba4.smb_conf import SambaConf


logger = logging.getLogger()

try:
    samba4_site_packages = os.path.join(Samba4Config("samba4").samba_prefix,
                                        'lib64/python2.7/site-packages')
    sys.path.insert(0, samba4_site_packages)

    from samba.samdb import SamDB
    from samba.param import LoadParm
    from samba.auth import system_session
    from samba import ldb
    from samba import dsdb

except ImportError:
    logger.error("Python module ldb.so not found...\n"
                 "Samba4 package must be installed\n")
    raise


class SambaToolException(Exception):
    pass


class SambaAD:

    """
    Handle sam.ldb: users and computers
    """

    def __init__(self):
        self.smb_conf = SambaConf()
        self.samdb_url = os.path.join(self.smb_conf.private_dir(), 'sam.ldb')
        self.samdb = SamDB(url=self.samdb_url, session_info=system_session(),
                           lp=LoadParm())

# v Users ---------------------------------------------------------------------

    def isUserEnabled(self, username):
        search_filter = "(&(objectClass=user)(sAMAccountName=%s))" % ldb.binary_encode(
            to_str(username))
        userlist = self.samdb.search(base=self.samdb.domain_dn(),
                                     scope=ldb.SCOPE_SUBTREE,
                                     expression=search_filter,
                                     attrs=["userAccountControl"])
        if not userlist:
            return False

        uac_flags = int(userlist[0]["userAccountControl"][0])
        return 0 == (uac_flags & dsdb.UF_ACCOUNTDISABLE)

    def existsUser(self, username):
        return to_str(username) in self._samba_tool("user list")

    def updateUserPassword(self, username, password):
        self._samba_tool("user setpassword %s --newpassword='%s'" %
                         (username, password))
        return True

    def createUser(self, username, password, given_name=None, surname=None):
        cmd = "user create %s '%s'" % (username, password)
        if given_name and surname:
            cmd += " --given-name='%s' --surname='%s'" % (to_str(given_name),
                                                          to_str(surname))
        self._samba_tool(cmd)
        return True

    def createGroup(self, name, description):
        cmd = 'group add ' + name
        if description:
            cmd += ' --description=' + description
        self._samba_tool(cmd)
        return True

    def enableUser(self, username):
        self._samba_tool("user enable %s" % username)
        return True

    def disableUser(self, username):
        self._samba_tool("user disable %s" % username)
        return True

    def deleteUser(self, username):
        self._samba_tool("user delete %s" % username)
        return True

    def _samba_tool(self, cmd):
        samba_tool = os.path.join(self.smb_conf.prefix, "bin/samba-tool")
        cmd = samba_tool + " " + cmd
        exit_code, std_out, std_err = shlaunch(cmd)
        if exit_code != 0:
            error_msg = "Error processing `%s`:\n" % cmd
            if std_err:
                error_msg += "\n".join(std_err)
            if std_out:
                error_msg += "\n".join(std_out)
            logger.error(error_msg)
            raise SambaToolException(error_msg)
        return std_out

# v Machines ------------------------------------------------------------------

    def _listComputersInContainer(self, container_dn, name_suffix=''):
        computers = self.samdb.search(base=container_dn,
                                      scope=ldb.SCOPE_ONELEVEL,
                                      expression="(objectClass=computer)",
                                      attrs=["name", "description", "operatingSystem"])
        res = []
        if computers:
            for computer in computers:
                description = computer.get("description", computer.get("operatingSystem", ""))
                res.append({
                    "name": str(computer["name"]) + name_suffix,
                    "description": str(description),
                    "enabled": 1  # TODO: get what the state actually is
                })
        return res

    def listDomainMembers(self):
        """
        Returns list of Computer objects description

        @return: list of dicts with Computer name and description
        @rtype: list
        """
        dcs = self._listComputersInContainer(
            "OU=Domain Controllers,%s" % self.samdb.domain_dn(), ' (dc)')
        computers = self._listComputersInContainer(
            "CN=Computers,%s" % self.samdb.domain_dn())
        return dcs + computers

    def deleteMachine(self, name):  # TODO
        return True

    def getMachine(self, name):
        container_dn = "CN=Computers,%s" % self.samdb.domain_dn()
        computers = self.samdb.search(base=container_dn,
                                      scope=ldb.SCOPE_ONELEVEL,
                                      expression="(&(objectClass=computer)(name=%s))" % name,
                                      attrs=["description", "operatingSystem"])
        if not computers or len(computers) < 1:
            return {'name': name, 'description': 'Unknown', 'enabled': False}

        c = computers[0]
        description = str(c.get('description', c.get('operatingSystem')))
        return {'name': name, 'description': description, 'enabled': True}

    def editMachine(self, name, description, enabled):  # TODO
        return True
