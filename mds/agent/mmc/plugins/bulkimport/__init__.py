# -*- coding: utf-8; -*-
#
# (c) 2009 Open Systems Specilists - Glen Ogilvie
#
# $Id: __init__.py 743 2008-12-15 14:20:35Z cdelfosse $
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

""" Bulkimport """

import socket
import ldap
import logging
import os
import os.path
import grp

from mmc.plugins.base import ldapUserGroupControl
from mmc.support.config import *
from mmc.support import mmctools
import mmc

INI = "/etc/mmc/plugins/bulkimport.ini"

VERSION = "0.0.2"
APIVERSION = "1:0:0"
REVISION = int("$Rev: 1 $".split(':')[1].strip(' $'))

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION


def activate():
    return True
