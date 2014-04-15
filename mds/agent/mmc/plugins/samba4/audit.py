# -*- coding: utf-8; -*-g
#
# (c) 2014 Zentyal S.L., http://www.zentyal.com
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
#
# Author(s):
#   Julien Kerihuel <jkerihuel@zentyal.com>
#

"""
Constants for the audit framework and the SAMBA4 plugin.
"""

from mmc.plugins.base.audit import AT

PLUGIN_NAME=u'MMC-SAMBA4'

class AuditActions:
    SAMBA4_RESTART=u'SAMBA4_RESTART'
    SAMBA4_RELOAD=u'SAMBA4_RELOAD'
    SAMBA4_PURGE=u'SAMBA4_PURGE'
    SAMBA4_PROVISION=u'SAMBA4_PROVISION'
    SAMBA4_ADD_SHARE=u'SAMBA4_ADD_SHARE'
    SAMBA4_MOD_SHARE=u'SAMBA4_MOD_SHARE'
    SAMBA4_DEL_SHARE=u'SAMBA4_DEL_SHARE'

AA = AuditActions

class AuditTypes(AT):
    DOMAIN=u'DOMAIN'
    SHARE=u'SHARE'

AT = AuditTypes
