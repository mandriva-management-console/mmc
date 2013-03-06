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
			graph/normalgroup/add.css \
			graph/normalgroup/index.css \
			graph/normalgroup/navbar.png \
			includes/auditCodes.php \
			includes/squid-xmlrpc.inc.php \
			includes/squid.inc.php \
			includes/delete.php \
			includes/manager.php \
			includes/restart.php \
			localCss.php \
			normalgroup/accesslog.php \
			normalgroup/accesslog/images/blank.gif \
			normalgroup/accesslog/images/fillet.gif \
			normalgroup/accesslog/images/full.gif \
			normalgroup/accesslog/images/icon_wait.gif \
			normalgroup/accesslog/images/mark.gif \
			normalgroup/accesslog/images/on.gif \
			normalgroup/accesslog/images/pause.gif \
			normalgroup/accesslog/includes/detect_browser.js \
			normalgroup/accesslog/includes/drag.js \
			normalgroup/accesslog/includes/effects.js \
			normalgroup/accesslog/includes/prototype.js \
			normalgroup/accesslog/includes/squidnow.js \
			normalgroup/accesslog/includes/squidnow_poshtml.js \
			normalgroup/accesslog/includes/wait_msg.js \
			normalgroup/accesslog/rsys.css \
			normalgroup/accesslog/squidnow.pl \
			normalgroup/accesslog/index.php \
			normalgroup/blackmanager.php \
			normalgroup/deleteb.php \
			normalgroup/deletem.php \
			normalgroup/deletet.php \
			normalgroup/deletew.php \
			normalgroup/deletex.php \
			normalgroup/extmanager.php \
			normalgroup/localSidebar.php \
			normalgroup/machmanager.php \
			normalgroup/timemanager.php \
			normalgroup/whitemanager.php \
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
