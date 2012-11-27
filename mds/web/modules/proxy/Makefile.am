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
	graph/blacklist/add.css \
	graph/blacklist/navbar.png \
	graph/blacklist/index.css \
	blacklist/statut.php \
	blacklist/index.php \
	blacklist/restart.php \
	blacklist/delete.php \
	blacklist/localSidebar.php \
	blacklist/add.php \
	infoPackage.inc.php \
	includes/proxy.inc.php \
	includes/config.inc.php \
	includes/proxy-xmlrpc.inc.php \
	includes/proxy-unix.inc.php \
	includes/auditCodes.php \
	localCss.php \
	graph/navbar/proxy.png \
	graph/navbar/proxy_hl.png \
	graph/navbar/proxy_select.png

langs = nb_NO es_ES ru_RU pt_BR da_DK de_DE pl_PL fr_FR

module = $(notdir $(realpath $(srcdir)))
moduledir = $(datadir)/mmc/modules/$(module)
pofiles = $(foreach lang,$(langs),$(srcdir)/locale/$(lang)/LC_MESSAGES/$(module).po)
mofiles = $(patsubst %.po,%.mo,$(pofiles))

nobase_module_DATA = $(patsubst %,$(srcdir)/%,$(datafiles)) $(mofiles)

EXTRA_DIST = $(datafiles) $(pofiles) README
CLEANFILES = $(mofiles)

%.mo: %.po
	$(MKDIR_P) $(@D)
	$(MSGFMT) -c -o $@ $<
