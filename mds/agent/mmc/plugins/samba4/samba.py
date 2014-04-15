# -*- coding: utf-8; -*-g
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

import base64
import grp
import ldap
import logging
import os
import pwd
import shutil
import stat
import tempfile
from configobj import ConfigObj, ParseError
from jinja2 import Environment, PackageLoader
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.base import ldapUserGroupControl
from mmc.plugins.samba4.audit import AT, AA, PLUGIN_NAME
from mmc.plugins.samba4.config import Samba4Config
from mmc.plugins.samba4.helpers import get_internal_interfaces, shellquote
from mmc.plugins.samba4.smb_conf import SambaConf
from mmc.support.mmctools import shLaunch, shlaunch


logger = logging.getLogger()

class SambaToolException(Exception):
    pass


class SambaAD:
    """
    Handle sam.ldb: users and computers
    """
    def __init__(self):
        smb_conf = SambaConf()
        self.sam_ldb_path = os.path.join(smb_conf.PRIVATE_DIR, 'sam.ldb')
        #FIXME self.ldb = LDB(self.sam_ldb_path)

    def existsUser(self, username):
        return username in self._samba_tool("user list")

    def changeUserPassword(self,username, password, password_type):
        if password_type != 'base64':
            raise Exception('Unknown password type')
        password = base64.b64decode(password)
        self._samba_tool("user setpassword %s --newpassword='%s'" %
                         (username, password))

    def createUser(self, username, password):
        self._samba_tool("user create %s '%s'" % (username, password))

    def enableUser(self, username):
        self._samba_tool("user enable %s" % username)

    def disableUser(self, username):
        self._samba_tool("user disable %s" % username)

    def deleteUser(self, username):
        self._samba_tool("user delete %s" % username)

    def _samba_tool(self, cmd):
        exit_code, std_out, std_err = shlaunch(cmd)
        if exit_code != 0:
            error_msg = "Error processing `%s`:\n" % cmd
            if std_err:
                error_msg += "\n".join(std_err)
            if std_out:
                error_msg += "\n".join(std_out)
            raise SambaToolException(error_msg)
        return std_out