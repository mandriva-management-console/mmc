# (c) 2014 Mandriva, http://www.mandriva.com
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
datafiles = \
	infoPackage.inc.php \
	includes/radius-xmlrpc.php \
	includes/publicFunc.php \
	includes/auditCodes.php

langs = pt_BR de_DE fr_FR

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
