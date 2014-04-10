# (c) 2014 Zentyal, http://www.zentyal.com
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
#   Miguel Julián <mjulian@zentyal.com>
#
datafiles = \
	domaincontroller/index.css \
	domaincontroller/provision.php \
	domaincontroller/purge.php \
	graph/domaincontroller/index.css \
	graph/img/provision/icn_provision_active.gif \
	graph/img/provision/icn_provision.gif \
	graph/img/provision/icn_provision_ro.gif \
	graph/img/shares/icn_addShare_active.gif \
	graph/img/shares/icn_addShare.gif \
	graph/img/shares/icn_addShare_ro.gif \
	graph/img/shares/icn_global_active.gif \
	graph/img/shares/icn_global.gif \
	graph/img/shares/icn_global_ro.gif \
	graph/img/shares/icn_shareList.gif \
	graph/img/shares/icn_shares_large.gif \
	graph/navbar/share.png \
	graph/navbar/share_hl.png \
	graph/navbar/share_select.png \
	graph/navbar/pref.png \
	graph/navbar/pref_hl.png \
	graph/navbar/pref_select.png \
	includes/common-xmlrpc.inc.php \
	includes/domaincontroller-xmlrpc.inc.php \
	includes/shares-xmlrpc.inc.php \
	infoPackage.inc.php \
	mainSidebar.php \
	shares/add.php \
	shares/edit.php \
	shares/index.php \
	views/groups.tpl.php \
	views/users.tpl.php

module = $(notdir $(realpath $(srcdir)))
moduledir = $(datadir)/mmc/modules/$(module)
pofiles = $(foreach lang,$(langs),$(srcdir)/locale/$(lang)/LC_MESSAGES/$(module).po)
mofiles = $(patsubst %.po,%.mo,$(pofiles))

nobase_module_DATA = $(patsubst %,$(srcdir)/%,$(datafiles)) $(patsubst %,$(srcdir)/%,$(mofiles))

EXTRA_DIST = $(datafiles) $(pofiles)
CLEANFILES = $(mofiles)

%.mo: %.po
	$(MKDIR_P) $(@D)
	$(MSGFMT) -c -o $@ $<
