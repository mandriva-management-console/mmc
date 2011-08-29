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
	graph/img/mail_select.png \
	graph/img/mailadd_inactive.png \
	graph/img/mail_inactive.png \
	graph/img/mail_hl.png \
	graph/img/mailadd_active.png \
	graph/img/mail_active.png \
	graph/img/mail.png \
	graph/mail/index.css \
	infoPackage.inc.php \
	mainSidebar.php \
	includes/publicFunc.php \
	includes/mail-xmlrpc.php \
	includes/mail.inc.php \
	includes/auditCodes.php \
	domains/ajaxMailDomainFilter.php \
	domains/members.php \
	domains/ajaxDomainFilter.php \
	domains/index.php \
	domains/delete.php \
	domains/ajaxFilter.php \
	domains/add.php \
	domains/edit.php \
	aliases/index.php \
	aliases/add.php \
	aliases/edit.php \
	aliases/delete.php \
	aliases/ajaxAliasesFilter.php
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
