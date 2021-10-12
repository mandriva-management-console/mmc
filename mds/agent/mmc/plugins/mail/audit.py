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
Constants for the audit framework and the mail plugin.
"""

from mmc.plugins.base.audit import AT

PLUGIN_NAME='MMC-MAIL'

class AuditActions:
    MAIL_ENABLE='MAIL_ENABLE'
    MAIL_DISABLE='MAIL_DISABLE'
    MAIL_CHANGE_MAIL_DROP='MAIL_CHANGE_MAIL_DROP'
    MAIL_CHANGE_MAIL_ALIAS='MAIL_CHANGE_MAIL_ALIAS'
    MAIL_CHANGE_MAIL_BOX='MAIL_CHANGE_MAIL_BOX'
    MAIL_CHANGE_MAIL_HOST='MAIL_CHANGE_MAIL_HOST'
    MAIL_CHANGE_MAIL_PROXY='MAIL_CHANGE_MAIL_PROXY'
    MAIL_CHANGE_MAIL_HIDDEN='MAIL_CHANGE_MAIL_HIDDEN'
    MAIL_CHANGE_MAIL_QUOTA='MAIL_CHANGE_MAIL_QUOTA'
    MAIL_ADD_MAIL_CLASS='MAIL_ADD_MAIL_CLASS'
    MAIL_DEL_MAIL_CLASS='MAIL_DEL_MAIL_CLASS'
    MAIL_DEL_MAIL_GRP_ALIAS='MAIL_DEL_MAIL_GRP_ALIAS'
    MAIL_ADD_MAIL_GROUP='MAIL_ADD_MAIL_GROUP'
    MAIL_ADD_VDOMAIN='MAIL_ADD_VDOMAIN'
    MAIL_DEL_VDOMAIN='MAIL_DEL_VDOMAIN'
    MAIL_SET_DOMAIN_DESC='MAIL_SET_DOMAIN_DESC'
    MAIL_SET_DOMAIN_QUOTA='MAIL_SET_DOMAIN_QUOTA'
    MAIL_RESET_DOMAIN_QUOTA='MAIL_RESET_DOMAIN_QUOTA'
    MAIL_ADD_ZARAFA_CLASS = 'MAIL_ADD_ZARAFA_CLASS'
    MAIL_DEL_ZARAFA_CLASS = 'MAIL_DEL_ZARAFA_CLASS'
    MAIL_MOD_ZARAFA_ADMIN = 'MAIL_MOD_ZARAFA_ADMIN'
    MAIL_MOD_ZARAFA_SHAREDSTOREONLY = 'MAIL_MOD_ZARAFA_SHAREDSTOREONLY'
    MAIL_MOD_ZARAFA_ACCOUNT = 'MAIL_MOD_ZARAFA_ACCOUNT'
    MAIL_MOD_ZARAFA_HIDDEN = 'MAIL_MOD_ZARAFA_HIDDEN'
    MAIL_MOD_ZARAFA_SENDASPRIVILEGE = 'MAIL_MOD_ZARAFA_SENDASPRIVILEGE'

AA = AuditActions

class AuditTypes(AT):
    MAIL_GROUP = 'MAIL_GROUP'
    MAIL = 'MAIL'
    VMDOMAIN = 'VMDOMAIN'

AT = AuditTypes

