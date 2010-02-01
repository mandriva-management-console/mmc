# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
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
Constants for the audit framework and the proxy plugin.
"""

from mmc.plugins.base.audit import AT

PLUGIN_NAME=u'MMC-PROXY'

class AuditActions:
    PROXY_ADD_BLACKLIST=u'PROXY_ADD_BLACKLIST'
    PROXY_DEL_BLACKLIST=u'PROXY_DEL_BLACKLIST'
    PROXY_RESTART_SQUID=u'PROXY_RESTART_SQUID'

AA = AuditActions
	
class AuditTypes(AT):
    BLACKLIST=u'BLACKLIST'

AT = AuditTypes
