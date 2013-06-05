# -*- coding: utf-8; -*-
#
# (c) 2009 Open Systems Specilists - Glen Ogilvie
#
# $Id$
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
MDS bulk import plugin for the MMC agent.
"""

from mmc.core.version import scmRevision
from mmc.site import mmcconfdir

INI = mmcconfdir + "/plugins/bulkimport.ini"

VERSION = "2.5.0"
APIVERSION = "0:0:0"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION


def activate():
    return True
