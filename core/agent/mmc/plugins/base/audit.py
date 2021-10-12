# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
#
# $Id: writers.py 4827 2009-11-27 14:54:51Z cdelfosse $
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
Contains constants for the audit system. 
"""

class AuditActions:
    BASE_ADD_USER = 'BASE_ADD_USER'
    BASE_ENABLE_USER = 'BASE_ENABLE_USER'
    BASE_DISABLE_USER = 'BASE_DISABLE_USER'
    BASE_MOD_USER_ATTR = 'BASE_MOD_USER_ATTR'
    BASE_MOD_GROUP = 'BASE_MOD_GROUP_ATTR'
    BASE_ADD_GROUP = 'BASE_ADD_GROUP'
    BASE_ADD_USER_TO_GROUP = 'BASE_ADD_USER_TO_GROUP'
    BASE_DEL_USER_FROM_GROUP = 'BASE_DEL_USER_FROM_GROUP'
    BASE_DEL_USER_FROM_ALL_GROUPS = 'BASE_DEL_USER_FROM_ALL_GROUPS'
    BASE_MOD_USER_PGROUP = 'BASE_MOD_USER_PGROUP'
    BASE_DEL_USER = 'BASE_DEL_USER'
    BASE_DEL_USER_ATTR = 'BASE_DEL_USER_ATTR'
    BASE_MOD_USER_PASSWORD = 'BASE_MOD_USER_PASSWORD'
    BASE_DEL_GROUP = 'BASE_DEL_GROUP'
    BASE_BACKUP_USER = 'BASE_BACKUP_USER'
    BASE_AUTH_USER = 'BASE_AUTH_USER'
    BASE_MOVE_USER_HOME = 'BASE_MOVE_USER_HOME'
    BASE_ADD_OU = 'BASE_ADD_OU'
AA = AuditActions

class AuditTypes:
    USER = 'USER'
    GROUP = 'GROUP'
    ATTRIBUTE = 'ATTRIBUTE'
    ORGANIZATIONAL_UNIT = 'ORGANIZATIONAL_UNIT'
AT = AuditTypes

PLUGIN_NAME = 'MMC-BASE'
