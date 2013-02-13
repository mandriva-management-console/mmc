# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
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
Constants for the audit framework and the squid plugin.
"""

from mmc.plugins.base.audit import AT

PLUGIN_NAME=u'MMC-PROXY'

class AuditActions:
    PROXY_ADD_NORMAL_WHITELIST=u'PROXY_ADD_NORMAL_WHITELIST'
    PROXY_DEL_NORMAL_WHITELIST=u'PROXY_DEL_NORMAL_WHITELIST'

    PROXY_ADD_NORMAL_BLACKLIST=u'PROXY_ADD_NORMAL_BLACKLIST'
    PROXY_DEL_NORMAL_BLACKLIST=u'PROXY_DEL_NORMAL_BLACKLIST'

    PROXY_ADD_NORMAL_BLACKEXT=u'PROXY_ADD_NORMAL_BLACKEXT'
    PROXY_DEL_NORMAL_BLACKEXT=u'PROXY_DEL_NORMAL_BLACKEXT'

    PROXY_ADD_TIME_WHITELIST=u'PROXY_ADD_TIME_WHITELIST'
    PROXY_DEL_TIME_WHITELIST=u'PROXY_DEL_TIME_WHITELIST'

    PROXY_ADD_TIME_DAY=u'PROXY_ADD_TIME_DAY'
    PROXY_DEL_TIME_DAY=u'PROXY_DEL_TIME_DAY'

    PROXY_ADD_TIME_NIGHT=u'PROXY_ADD_TIME_NIGHT'
    PROXY_DEL_TIME_NIGHT=u'PROXY_DEL_TIME_NIGHT'

    PROXY_RESTART_SQUID=u'PROXY_RESTART_SQUID'

AA = AuditActions

class AuditTypes(AT):
    AUDITLIST=u'AUDITLIST'

AT = AuditTypes
