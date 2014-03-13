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

"""
MDS samba4 plugin for the MMC agent.
"""

import os
import os.path
import logging
from mmc.core.version import scmRevision

from mmc.site import mmcconfdir
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.samba4.audit import AT, AA, PLUGIN_NAME
from mmc.plugins.samba4.config import Samba4Config


logger = logging.getLogger()

VERSION = "1.0.0"
APIVERSION = "1.0.0"
REVISION = scmRevision("$Rev$")

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

def activate():
    """
    this function degine if the module "base" can be activated.
    @return: true True if this module can be activated
    @rtype: boolean
    """
    config = Samba4Config("samba4")

    if config.disabled:
        logger.info("samba4 plugin disabled by configuration.")
        return False

    # Verify if samba conf file exists
    conf = config.samba4_conf_file
    if not os.path.exists(conf):
        logger.error(conf + " does not exist")
        return False

    # Validate smb.conf

    # Verify if init script exists
    init = config.samba4_init_script
    if not os.path.exists(init):
        logger.error(init + " does not exist")
        return False


    try:
        from mmc.plugins.dashboard.manager import DashboardManager
        from mmc.plugins.samba4.panel import Samba4Panel
        DM = DashboardManager()
        DM.register_panel(Samba4Panel("samba4"))
    except ImportError:
        pass
    
    return True

def restartSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA_RELOAD_S4)
    # mmctools.shlaunchBackground
    r.commit()
    return 0;

def reloadSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA_RELOAD_S4)
    # mmctools.shlaunchBackground
    r.commit()
    return 0;

def purgeSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA_PURGE_S4)
    r.commit()
    return 0;
