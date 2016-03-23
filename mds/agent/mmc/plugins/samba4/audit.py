# -*- coding: utf-8; -*-
#
# (c) 2014 Mandriva, http://www.mandriva.com/
#
# This file is part of Management Console.
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
#   Jesús García Sáez <jgarcia@zentyal.com>
#

"""
Constants for the audit framework and the SAMBA4 plugin.
"""

from mmc.plugins.base.audit import AT

PLUGIN_NAME = u'MMC-SAMBA4'


class AuditActions:
    SAMBA4_RESTART = u'SAMBA4_RESTART'
    SAMBA4_RELOAD = u'SAMBA4_RELOAD'
    SAMBA4_PURGE = u'SAMBA4_PURGE'
    SAMBA4_PROVISION = u'SAMBA4_PROVISION'
    SAMBA4_ADD_SHARE = u'SAMBA4_ADD_SHARE'
    SAMBA4_MOD_SHARE = u'SAMBA4_MOD_SHARE'
    SAMBA4_DEL_SHARE = u'SAMBA4_DEL_SHARE'
    SAMBA4_BACKUP_SHARE = u'SAMBA4_BACKUP_SHARE'
    SAMBA4_RESTART_SAMBA = u'SAMBA4_RESTART_SAMBA'
    SAMBA4_RELOAD_SAMBA = u'SAMBA4_RELOAD_SAMBA'

AA = AuditActions


class AuditTypes(AT):
    DOMAIN = u'DOMAIN'
    SHARE = u'SHARE'
    USER = u'USER'

AT = AuditTypes
