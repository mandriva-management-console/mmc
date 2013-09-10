# (c) 2011 Mandriva, http://www.mandriva.com
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
#   Jean Parpaillon <jparpaillon@mandriva.com>
#
datafiles = \
	views/groups.tpl.php \
	views/users.tpl.php \
	infoPackage.inc.php \
	status/index.php \
	includes/publicFunc.php \
	includes/machines.inc.php \
	includes/machines-xmlrpc.inc.php \
	includes/errorHandling.php \
	includes/samba.inc.php \
	includes/shares.inc.php \
	includes/user-xmlrpc.inc.php \
	includes/auditCodes.php \
	includes/panels/samba.inc.php \
	mainSidebar.php \
	machines/index.php \
	machines/delete.php \
	machines/ajaxFilter.php \
	machines/localSidebar.php \
	machines/add.php \
	machines/edit.php \
	shares/backup.php \
	shares/index.php \
	shares/delete.php \
	shares/localSidebar.php \
	shares/add.php \
	shares/details.php \
	config/index.php \
	config/reload.php \
	config/restart.php \
	graph/navbar/share.png \
	graph/navbar/share_hl.png \
	graph/navbar/share_select.png \
	graph/navbar/pref.png \
	graph/navbar/pref_hl.png \
	graph/navbar/pref_select.png \
	graph/img/machines/icn_addMachines.gif \
	graph/img/machines/icn_addMachines_active.gif \
	graph/img/machines/icn_addMachines_ro.gif \
	graph/img/machines/icn_global.gif \
	graph/img/machines/icn_global_active.gif \
	graph/img/machines/icn_global_ro.gif \
	graph/img/machines/icn_groupsList.gif \
	graph/img/machines/icn_machinesList.gif \
	graph/img/machines/icn_machines_large.gif \
	graph/img/shares/icn_addShare.gif \
	graph/img/shares/icn_addShare_active.gif \
	graph/img/shares/icn_addShare_ro.gif \
	graph/img/shares/icn_global.gif \
	graph/img/shares/icn_global_active.gif \
	graph/img/shares/icn_global_ro.gif \
	graph/img/shares/icn_shareList.gif \
	graph/img/shares/icn_shares_large.gif \
	graph/img/config/icn_config_large.gif \
	graph/img/config/icn_global.gif \
	graph/img/config/icn_global_active.gif \
	graph/img/config/icn_global_ro.gif


langs = nb_NO es_ES ru_RU pt_BR da_DK de_DE pl_PL fr_FR

module = $(notdir $(realpath $(srcdir)))
moduledir = $(datadir)/mmc/modules/$(module)
pofiles = $(foreach lang,$(langs),$(srcdir)/locale/$(lang)/LC_MESSAGES/$(module).po)
mofiles = $(patsubst %.po,%.mo,$(pofiles))

nobase_module_DATA = $(patsubst %,$(srcdir)/%,$(datafiles)) $(mofiles)

EXTRA_DIST = $(datafiles) $(pofiles)
CLEANFILES = $(mofiles)

%.mo: %.po
	$(MKDIR_P) $(@D)
	$(MSGFMT) -c -o $@ $<
