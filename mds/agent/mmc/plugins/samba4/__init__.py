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
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.samba4.audit import AA, PLUGIN_NAME
from mmc.plugins.samba4.config import Samba4Config
from mmc.plugins.samba4.helpers import shellquote
from mmc.support.mmctools import shlaunchBackground


logger = logging.getLogger()

VERSION = "1.0.0"
APIVERSION = "1.0.0"
REVISION = scmRevision("$Rev$")


def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION

def activate():
    """
    this function define if the module "base" can be activated.
    @return: true True if this module can be activated
    @rtype: boolean
    """
    config = Samba4Config("samba4")

    if config.disabled:
        logger.info("samba4 plugin disabled by configuration.")
        return False

    # Verify samba conf and init script files exist
    for filename in (config.conf_file, config.init_script):
        if not os.path.exists(filename):
            logger.error(filename + " does not exist")
            return False

    return True

def reloadSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA_RELOAD_S4)
    shlaunchBackground(Samba4Config("samba4").init_script + ' restart')
    r.commit()
    return 0

restartSamba = reloadSamba

def purgeSamba():
    r = AF().log(PLUGIN_NAME, AA.SAMBA_PURGE_S4)

    def _purgeSambaConfig():
        config = Samba4Config("samba4")
        conf_files = []
        conf_files.append(shellquote(config.conf_file))
        conf_files.append(shellquote(config.PRIVATE_DIR + '/*'))
        shlaunchBackground("rm -rf %s", ' '.join(conf_files))

    # FIXME should we use deferred instead?
    shlaunchBackground(Samba4Config("samba4").init_script + ' stop',
                       endFunc=_purgeSambaConfig)
    r.commit()
    return 0

def provisionSamba(mode, netbios_domain, realm):
    r = AF().log(PLUGIN_NAME, AA.SAMBA_PROVISION_S4)
    if mode != 'DC':
        raise NotImplemented("We can only provision samba4 as Domain Controller")

    config = Samba4Config("samba4")
    config.write_samba_config(mode, netbios_domain, realm)

    params = {'domain': netbios_domain, 'realm': realm, 'prefix': config.PREFIX,
              'role': mode}
    cmd = ("%(prefix)s/bin/samba-tool domain provision"
           " --domain='%(domain)s'"
           " --workgroup='%(domain)s'"
           " --realm='%(realm)s'"
           " --dns-backend=BIND9_DLZ"
           " --use-xattr=yes "
           " --use-rfc2307"
           " --server-role='%(role)s'"
           " --users=''"
           " --host-name=''"
           " --host-ip=''" % params)
    shlaunchBackground(cmd)

    r.commit()
    return 0
