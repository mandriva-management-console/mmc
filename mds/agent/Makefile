# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2009 Mandriva, http://www.mandriva.com
#
# $Id: Makefile 4426 2009-09-10 09:12:38Z cdelfosse $
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


# General Makefile variables should be passed via environment
DESTDIR ?=
PREFIX ?= /usr/local
SBINDIR ?= $(PREFIX)/sbin
LIBDIR ?= $(PREFIX)/lib/mmc
ETCDIR ?= /etc/mmc

# Shell tools
INSTALL = $(shell which install)
SED = $(shell which sed)

# Files owner
OWNER ?= root
OGROUP ?= root
# Python specific variables
PYTHON = $(shell which python)
PYTHON_PREFIX = $(shell $(PYTHON) -c "import sys; print sys.prefix")

# List of files to install
LIBFILES = bin/add_change_share_script bin/delete_share_script bin/add_printer_script bin/delete_printer_script bin/add_machine_script

all:

# Cleaning target
clean:
	@echo ""
	@echo "Cleaning sources..."
	@echo "Nothing to do"

# Install everything
install:
	@# Install directories
	@echo "Creating directories..."
	$(INSTALL) -d -m 755 -o ${OWNER} -g ${OGROUP} $(DESTDIR)$(SBINDIR)
	$(INSTALL) -d -m 755 -o ${OWNER} -g ${OGROUP} $(DESTDIR)$(LIBDIR)
	$(INSTALL) -d -m 755 -o ${OWNER} -g ${OGROUP} $(DESTDIR)$(ETCDIR)
	$(INSTALL) -d -m 755 -o ${OWNER} -g ${OGROUP} $(DESTDIR)$(PYTHON_PREFIX)

	@echo ""
	@echo "Install python code in $(DESTDIR)$(PYTHON_PREFIX)"
	$(PYTHON) setup.py install --no-compile --prefix $(DESTDIR)$(PYTHON_PREFIX)

	@echo ""
	@echo "Install LIBFILES in $(DESTDIR)$(LIBDIR)"
	$(INSTALL) -m 755 -o ${OWNER} -g ${OGROUP} $(LIBFILES) $(DESTDIR)$(LIBDIR)
	@echo ""
	@echo "Install CONFILES in $(DESTDIR)$(ETCDIR)"
	$(INSTALL) -d -m 755 -o ${OWNER} -g ${OGROUP} $(DESTDIR)$(ETCDIR)/plugins
	-[ ! -f $(DESTDIR)$(ETCDIR)/plugins/samba.ini ] && $(INSTALL) -m 600 -o ${OWNER} -g ${OGROUP} conf/plugins/samba.ini $(DESTDIR)$(ETCDIR)/plugins/samba.ini
	-[ ! -f $(DESTDIR)$(ETCDIR)/plugins/mail.ini ] && $(INSTALL) -m 600 -o ${OWNER} -g ${OGROUP} conf/plugins/mail.ini $(DESTDIR)$(ETCDIR)/plugins/mail.ini
	-[ ! -f $(DESTDIR)$(ETCDIR)/plugins/network.ini ] && $(INSTALL) -m 600 -o ${OWNER} -g ${OGROUP} conf/plugins/network.ini $(DESTDIR)$(ETCDIR)/plugins/network.ini
	-[ ! -f $(DESTDIR)$(ETCDIR)/plugins/proxy.ini ] && $(INSTALL) -m 600 -o ${OWNER} -g ${OGROUP} conf/plugins/proxy.ini $(DESTDIR)$(ETCDIR)/plugins/proxy.ini
	-[ ! -f $(DESTDIR)$(ETCDIR)/plugins/sshlpk.ini ] && $(INSTALL) -m 600 -o ${OWNER} -g ${OGROUP} conf/plugins/sshlpk.ini $(DESTDIR)$(ETCDIR)/plugins/sshlpk.ini
	-[ ! -f $(DESTDIR)$(ETCDIR)/plugins/userquota.ini ] && $(INSTALL) -m 600 -o ${OWNER} -g ${OGROUP} conf/plugins/userquota.ini $(DESTDIR)$(ETCDIR)/plugins/userquota.ini
	-[ ! -f $(DESTDIR)$(ETCDIR)/plugins/bulkimport.ini ] && $(INSTALL) -m 600 -o ${OWNER} -g ${OGROUP} conf/plugins/bulkimport.ini $(DESTDIR)$(ETCDIR)/plugins/bulkimport.ini

include common.mk

$(RELEASES_DIR)/$(TARBALL_GZ):
	mkdir -p $(RELEASES_DIR)/$(TARBALL)
	$(CPA) bin Changelog common.mk conf contrib COPYING init.d Makefile mmc setup.py $(RELEASES_DIR)/$(TARBALL)
	cd $(RELEASES_DIR) && tar -czf $(TARBALL_GZ) $(EXCLUDE_FILES) $(TARBALL); rm -rf $(TARBALL);
