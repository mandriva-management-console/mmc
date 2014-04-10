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

import os
import shutil
import tempfile
import logging
from configobj import ConfigObj, ParseError
from jinja2 import Environment, PackageLoader
from mmc.plugins.samba4.config import Samba4Config
from mmc.plugins.samba4.helpers import get_internal_interfaces


logger = logging.getLogger()
env = Environment(loader=PackageLoader('mmc.plugins.samba4', 'templates'))


class SambaConf:
    """
    Handle smb.conf file for Samba 4
    """
    PREFIX = '/opt/samba4'

    SYSVOL_DIR = os.path.join(PREFIX, 'var/locks/sysvol');
    PRIVATE_DIR = os.path.join(PREFIX, 'private');

    def __init__(self):
        self.smb_conf_path = Samba4Config("samba4").conf_file

    def _open_smb_conf(self):
        try:
            self.config = ConfigObj(self.smb_conf_path, interpolation=False,
                                    list_values=False, write_empty_values=True,
                                    encoding='utf8')
        except ParseError, e:
            logger.error("Failed to parse %s : %s " % (self.smb_conf_path, e))

    def write_samba_config(self, mode, netbios_name, realm):
        """
        Write SAMBA configuration file (smb.conf) to disk
        """
        _, tmpfname = tempfile.mkstemp("mmc")

        workgroup = realm.split('.')[0][:15].upper()
        netbios_name = netbios_name.lower()
        realm = realm.upper()
        domain = realm.lower()
        params = {'workgroup': workgroup,
                  'realm': realm,
                  'netbios_name': netbios_name,
                  'description': 'Mandriva Directory Server - SAMBA %v',
                  'mode' : mode,
                  'sysvol_path': self.SYSVOL_DIR,
                  'openchange': False, # FIXME
                  'domain': domain,
                  'interfaces': get_internal_interfaces()}
        smb_conf_template = env.get_template("smb.conf")
        with open(tmpfname, 'a') as f:
            f.write(smb_conf_template.render(params))
        """
        if openchange:
            openchange_conf_template = env.get_template("openchange.conf")
            with open(self.PREFIX + "etc/openchange.conf", 'a') as f:
                f.write(openchange_conf_template.render())
        """
        # FIXME validation?
        shutil.copy(tmpfname, self.smb_conf_path)
        os.remove(tmpfname)
