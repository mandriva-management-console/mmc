# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2009 Mandriva, http://www.mandriva.com
#
# $Id: Makefile 4443 2009-09-17 13:21:55Z cdelfosse $
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


# General Makefile variables
DESTDIR =
PREFIX = /usr/local
SBINDIR = $(PREFIX)/sbin
LIBDIR = $(PREFIX)/lib/mmc
ETCDIR = /etc/mmc
INITDIR = /etc/init.d
INSTALL = $(shell which install)
SED = $(shell which sed)

# Python specific variables
PYTHON = $(shell which python)
PYTHON_PREFIX = $(shell $(PYTHON) -c "import sys; print sys.prefix")

# web part
DATADIR = $(PREFIX)/share/mmc
CP = $(shell which cp)
CHOWN = $(shell which chown)
CHGRP = $(shell which chgrp)
HTTPDUSER = www-data
RM = $(shell which rm)

FILESTOINSTALL = modules

all:

generate-doc:
	gen-doc/create.sh

clean_mo:
	sh scripts/clean_mo.sh

build_mo:
	sh scripts/build_mo.sh

build_pot:
	sh scripts/build_pot.sh

# Cleaning target
clean: clean_mo
	@echo ""
	@echo "Cleaning sources..."
	@echo "Nothing to do"

# Install everything
install: build_mo 
	$(INSTALL) -d -m 755 -o root -g root $(DESTDIR)$(LIBDIR)
	$(INSTALL) -d -m 755 -o root -g root $(DESTDIR)$(ETCDIR)
	$(INSTALL) -d -m 755 -o root -g root $(DESTDIR)$(PYTHON_PREFIX)

	@echo ""
	@echo "Install python code in $(DESTDIR)$(PYTHON_PREFIX)"
	$(PYTHON) setup.py install --no-compile --prefix $(DESTDIR)$(PYTHON_PREFIX)

	@echo ""
	@echo "Install CONFILES in $(DESTDIR)$(ETCDIR)"
	$(INSTALL) -d -m 755 -o root -g root $(DESTDIR)$(ETCDIR)/plugins
	$(INSTALL) conf/plugins/* -m 600 -o root -g root $(DESTDIR)$(ETCDIR)/plugins

	@echo ""
	@echo "Installing mmc-web in $(DESTDIR)$(DATADIR)"
	$(INSTALL) -d -m 755 -o root -g root $(DESTDIR)$(DATADIR)
	$(INSTALL) -d -m 755 -o root -g root $(DESTDIR)$(ETCDIR)
	$(CP) -R $(FILESTOINSTALL) $(DESTDIR)$(DATADIR)
	$(CHOWN) -R root $(DESTDIR)$(DATADIR)
	$(CHGRP) -R root $(DESTDIR)$(DATADIR)
	find $(DESTDIR)$(DATADIR) -type f -name *.po -exec rm -f {} \;

include common.mk

$(RELEASES_DIR)/$(TARBALL_GZ):
	mkdir -p $(RELEASES_DIR)/$(TARBALL)/agent $(RELEASES_DIR)/$(TARBALL)/web
	$(CPA) agent/conf agent/COPYING agent/Changelog agent/contrib agent/mmc agent/setup.py web/scripts $(RELEASES_DIR)/$(TARBALL)
	cd web && $(CPA) $(FILESTOINSTALL) ../$(RELEASES_DIR)/$(TARBALL)
	$(CPA) agent/Changelog agent/COPYING agent/Makefile agent/common.mk $(RELEASES_DIR)/$(TARBALL)/agent
	$(CPA) Makefile common.mk $(RELEASES_DIR)/$(TARBALL)
	cd $(RELEASES_DIR) && tar -czf $(TARBALL_GZ) $(EXCLUDE_FILES) $(TARBALL); rm -rf $(TARBALL);

docs:
	epydoc mmc



