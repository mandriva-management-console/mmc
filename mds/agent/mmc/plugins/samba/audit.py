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
Constants for the audit framework and the SAMBA plugin.
"""

from mmc.plugins.base.audit import AT

PLUGIN_NAME='MMC-SAMBA'

class AuditActions:
    SAMBA_ADD_SHARE='SAMBA_ADD_SHARE'
    SAMBA_MOD_SHARE='SAMBA_MOD_SHARE'
    SAMBA_DEL_SHARE='SAMBA_DEL_SHARE'
    SAMBA_BACKUP_SHARE='SAMBA_BACKUP_SHARE'
    SAMBA_RESTART_SAMBA='SAMBA_RESTART_SAMBA'
    SAMBA_RELOAD_SAMBA='SAMBA_RELOAD_SAMBA'
    SAMBA_ADD_SAMBA_CLASS='SAMBA_ADD_SAMBA_CLASS'
    SAMBA_DEL_SAMBA_CLASS='SAMBA_DEL_SAMBA_CLASS'
    SAMBA_ADD_ATTR='SAMBA_ADD_ATTR'
    SAMBA_DEL_ATTR='SAMBA_DEL_ATTR'
    SAMBA_CHANGE_ATTR='SAMBA_CHANGE_ATTR'
    SAMBA_CHANGE_USER_PASS='SAMBA_CHANGE_USER_PASS'
    SAMBA_CHANGE_USER_PRIMARY_GRP='SAMBA_CHANGE_USER_PRIMARY_GRP'
    SAMBA_ENABLE_USER='SAMBA_ENABLE_USER'
    SAMBA_DISABLE_USER='SAMBA_DISABLE_USER'
    SAMBA_LOCK_USER='SAMBA_LOCK_USER'
    SAMBA_UNLOCK_USER='SAMBA_UNLOCK_USER'
    SAMBA_MAKE_SAMBA_GRP='SAMBA_MAKE_SAMBA_GRP'
    SAMBA_ADD_MACHINE='SAMBA_ADD_MACHINE'
    SAMBA_DEL_MACHINE='SAMBA_DEL_MACHINE'
    SAMBA_UNEXPIRE_USER_PASSWD='SAMBA_UNEXPIRE_USER_PASSWD'
    SAMBA_EXPIRE_USER_PASSWD='SAMBA_EXPIRE_USER_PASSWD'

AA = AuditActions

class AuditTypes(AT):
    SHARE='SHARE'
    MACHINE='MACHINE'

AT = AuditTypes

