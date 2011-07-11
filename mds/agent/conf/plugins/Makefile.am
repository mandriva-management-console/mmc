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
pluginconfdir = $(sysconfdir)/mmc/plugins
dist_pluginconf_DATA = mail.ini network.ini proxy.ini sshlpk.ini \
	userquota.ini bulkimport.ini
nodist_pluginconf_DATA = samba.ini

EXTRA_DIST = samba.ini.in
CLEANFILES = samba.ini

if ENABLE_CONF_BACKUP
INSTALL += --backup=numbered
endif

edit = sed \
        -e 's|@libdir[@]|$(libdir)|g'

samba.ini: Makefile
	rm -f $@ $@.tmp
	$(MKDIR_P) $(@D)
	srcdir=''; \
	  test -f ./$@.in || srcdir=$(srcdir)/; \
	  $(edit) $${srcdir}$@.in >$@.tmp
	mv $@.tmp $@

samba.ini: $(srcdir)/samba.ini.in
