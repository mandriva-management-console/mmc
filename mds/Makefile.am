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
ACLOCAL_AMFLAGS = -I m4
DISTCHECK_CONFIGURE_FLAGS=--disable-conf-backup

SUBDIRS  = agent web tests

dist-hook:
	rm -rf `find $(distdir) -name .svn`

sysconfdirwarning:
	@if test "${sysconfdir}" != "/etc"; then \
	  echo '###'; \
	  echo '### $${sysconfdir} evaluates to '${sysconfdir}': this is probably not what you want !'; \
	  echo '### You may want to launch ./configure --sysconfdir=/etc.'; \
	  echo '###'; \
	fi

prereqs:
	if [ -x /usr/bin/apt-get ]; then \
	  $(MAKE) prereqs-deb; \
	elif [ -x /usr/sbin/urpmi ]; then \
	  $(MAKE) prereqs-urpmi; \
	fi

prereqs-urpmi:

prereqs-deb:

.PHONY = sysconfdirwarning prereqs prereqs-deb prereqs-urpmi
