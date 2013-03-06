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
datafiles = graph/navbar/proxy_hl.png \
			graph/navbar/proxy.png \
			graph/navbar/proxy_select.png \
			graph/internet/add.css \
			graph/internet/index.css \
			graph/internet/navbar.png \
			includes/auditCodes.php \
			includes/squid-xmlrpc.inc.php \
			includes/squid.inc.php \
			includes/delete.php \
			includes/manager.php \
			includes/restart.php \
			localCss.php \
			internet/accesslog.php \
			internet/accesslog/images/blank.gif \
			internet/accesslog/images/fillet.gif \
			internet/accesslog/images/full.gif \
			internet/accesslog/images/icon_wait.gif \
			internet/accesslog/images/mark.gif \
			internet/accesslog/images/on.gif \
			internet/accesslog/images/pause.gif \
			internet/accesslog/includes/detect_browser.js \
			internet/accesslog/includes/drag.js \
			internet/accesslog/includes/effects.js \
			internet/accesslog/includes/prototype.js \
			internet/accesslog/includes/squidnow.js \
			internet/accesslog/includes/squidnow_poshtml.js \
			internet/accesslog/includes/wait_msg.js \
			internet/accesslog/rsys.css \
			internet/accesslog/squidnow.pl \
			internet/accesslog/index.php \
			internet/blackmanager.php \
			internet/deleteb.php \
			internet/deletem.php \
			internet/deletet.php \
			internet/deletew.php \
			internet/deletex.php \
			internet/extmanager.php \
			internet/localSidebar.php \
			internet/machmanager.php \
			internet/timemanager.php \
			internet/whitemanager.php \
			infoPackage.inc.php

langs =

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
