# (c) 2012 Mandriva, http://www.mandriva.com
#
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
	infoPackage.inc.php \
	includes/shorewall-xmlrpc.inc.php \
	includes/functions.inc.php \
	includes/functions.js \
	shorewall/localSidebar.php \
	shorewall/internal_fw.php \
	shorewall/ajax_internal_fw.php \
	shorewall/delete_internal_fw_rule.php \
	shorewall/external_fw.php \
	shorewall/ajax_external_fw.php \
	shorewall/delete_external_fw_rule.php \
	shorewall/internal_external.php \
	shorewall/ajax_internal_external.php \
	shorewall/delete_internal_external_rule.php \
	shorewall/external_internal.php \
	shorewall/ajax_external_internal.php \
	shorewall/delete_external_internal_rule.php \
	shorewall/rules.php \
	shorewall/ajax_rules.php \
	shorewall/delete_rule.php \
	shorewall/dnat_rules.php \
	shorewall/ajax_dnat_rules.php \
	shorewall/delete_dnat_rule.php \
	shorewall/rule_list.inc.php \
	shorewall/dnat_rule_list.inc.php \
	shorewall/masquerade.php \
	shorewall/ajax_masquerade.php \
	shorewall/delete_masquerade_rule.php \
	shorewall/restart_service.php \
	graph/navbar/shorewall.png \
	graph/navbar/shorewall_select.png \
	graph/navbar/shorewall_hl.png

langs = fr_FR pt_BR

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
