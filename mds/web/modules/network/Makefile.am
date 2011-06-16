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
	graph/network/index.css \
	graph/img/network_hl.png \
	graph/img/network_inactive.png \
	graph/img/network.png \
	graph/img/networkadd_active.png \
	graph/img/networkadd_inactive.png \
	graph/img/network_select.png \
	graph/img/network_active.png \
	infoPackage.inc.php \
	includes/network-xmlrpc.inc.php \
	includes/auditCodes.php \
	includes/network.inc.php \
	dhcplogview/ajax_setSearch.php \
	dhcplogview/ajax_showlog.php \
	dhcplogview/index.php \
	dnslogview/ajax_setSearch.php \
	dnslogview/ajax_showlog.php \
	dnslogview/index.php \
	network/subnetedit.php \
	network/edithost.php \
	network/servicelog.php \
	network/subnetdeletehost.php \
	network/subnetmembers.php \
	network/subnetaddhost.php \
	network/zonemembers.php \
	network/subnetdelete.php \
	network/addhost.php \
	network/index.php \
	network/subnetindex.php \
	network/delete.php \
	network/subnetadd.php \
	network/ajaxSubnetFilter.php \
	network/ajaxDnsGetZoneFreeIp.php \
	network/ajaxDhcpGetSubnetFreeIp.php \
	network/ajaxZoneMembersFilter.php \
	network/servicerestart.php \
	network/localSidebar.php \
	network/servicereload.php \
	network/add.php \
	network/servicestart.php \
	network/ajaxZoneFilter.php \
	network/ajaxSubnetMembersFilter.php \
	network/edit.php \
	network/services.php \
	network/subnetedithost.php \
	network/servicestop.php \
	network/deletehost.php

#langs = nb_NO es_ES ru_RU pt_BR da_DK de_DE pl_PL fr_FR
# Removed langs were .po file is known not to compile (with msgfmt)
# TODO: fix these .po files
langs = nb_NO es_ES ru_RU pt_BR de_DE pl_PL fr_FR

mmcdatadir = $(datadir)/mmc
module = $(notdir $(realpath $(srcdir)))
pofiles = $(foreach lang,$(langs),$(srcdir)/locale/$(lang)/LC_MESSAGES/$(module).po)
mofiles = $(patsubst %.po,%.mo,$(pofiles))

nobase_pkgdata_DATA = $(patsubst %,$(srcdir)/%,$(datafiles)) $(mofiles)

EXTRA_DIST = $(datafiles) $(pofiles)
CLEANFILES = $(mofiles)

%.mo: %.po
	$(MKDIR_P) $(@D)
	$(MSGFMT) -c -o $@ $<
