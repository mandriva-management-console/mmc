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
#   Julien Kerihuel <jkerihuel@zentyal.com>
#   Jesús García Sáez <jgarcia@zentyal.com>
#
from ConfigParser import NoOptionError
from mmc.support.config import PluginConfig


class Samba4Config(PluginConfig):
    def __init__(self, name="samba4"):
        # Default values
        self.samba_prefix = "/usr"
        self.conf_file = "/etc/samba/smb.conf"
        self.db_dir = "/var/lib/samba"
        self.defaultSharesPath = "/home/samba"
        self.authorizedSharePaths = [self.defaultSharesPath]
        PluginConfig.__init__(self, name)

    def readConf(self):
        PluginConfig.readConf(self)

        try:
            self.samba_prefix = self.get("main", "sambaPrefix")
        except NoOptionError:
            pass

        try:
            self.conf_file = self.get("main", "sambaConfFile")
        except NoOptionError:
            pass

        try:
            self.db_dir = self.get("main", "sambaDBDir")
        except NoOptionError:
            pass

        self.defaultSharesPath = self.get("main", "defaultSharesPath")

        try:
            listSharePaths = self.get("main", "authorizedSharePaths")
            self.authorizedSharePaths = listSharePaths.replace(' ',
                                                               '').split(',')
        except NoOptionError:
            self.authorizedSharePaths = [self.defaultSharesPath]

#    def setDefault(self):
#        self.samba4_conf_file = '/etc/smb.conf'
        #self.samba4_init_script = '/etc/rc.d/init.d/samba4'
