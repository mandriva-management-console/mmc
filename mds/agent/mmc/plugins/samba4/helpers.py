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

from mmc.plugins.shorewall import get_zones_interfaces
from mmc.plugins.shorewall.config import ShorewallPluginConfig


def shellquote(s):
    """
    @rtype str
    @return String quoted ready to be used in a shell command
    """
    return "'" + s.replace("'", "'\\''") + "'"


def get_internal_interfaces():
    """
    Return string with all the internal interfaces separated by a space
    Something like: "eth1 eth2"

    @rtype str
    @return Names of internal interfaces
    """
    shorewall_config = ShorewallPluginConfig("shorewall")
    interfaces = get_zones_interfaces(shorewall_config.internal_zones_names)
    if not interfaces:
        raise Exception("No internal networks detected")
    return " ".join([interface[1] for interface in interfaces])
