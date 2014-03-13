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
#
# Author(s):
#   Julien Kerihuel <jkerihuel@zentyal.com>
#
#

import ConfigParser

from mmc.support.config import PluginConfig

class Samba4Config(PluginConfig):

    def readConf(self):
        PluginConfig.readConf(self)

        try: self.samba4_conf_file = self.get("main", "sambaConfFile")
        except: pass

        try: self.samba4_init_script = self.get("main", "sambaInitScript")
        except: pass

    def setDefault(self):
        self.samba4_conf_file = '/etc/samba/smb.conf'
        self.samba4_init_script = '/etc/init.d/samba'
