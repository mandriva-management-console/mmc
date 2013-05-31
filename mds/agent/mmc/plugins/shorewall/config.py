# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2012 Mandriva, http://www.mandriva.com
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

"""
MMC services plugin configuration
"""

from ConfigParser import NoOptionError
from mmc.support.config import PluginConfig

class ShorewallPluginConfig(PluginConfig):

    def readConf(self):
        PluginConfig.readConf(self)
        self.external_zones_names = self.get('main', 'external_zones_names')
        self.internal_zones_names = self.get('main', 'internal_zones_names')
        try:
            self.path = self.get('main', 'path')
        except NoOptionError:
            self.path = '/etc/shorewall'
        try:
            self.macros_path = self.get('main', 'macros_path')
        except NoOptionError:
            self.macros_path = '/usr/share/shorewall'
        try:
            self.macros_list = self.get('main', 'macros_list').replace(' ', '').split(',')
        except NoOptionError:
            self.macros_list = []
