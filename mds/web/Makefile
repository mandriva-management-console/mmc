# Copyright (C) 2006, Adam Cecile for Linbox FAS
# Copyright (C) 2006, Jerome Wax for Linbox FAS
# Copyright (C) 2006, Cedric Delfosse for Linbox FAS
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA



# General Makefile variables
DESTDIR ?=
PREFIX ?= /usr/local
DATADIR ?= $(PREFIX)/share/mmc

# Shell tools
INSTALL = $(shell which install)
CP = $(shell which cp)
CHOWN = $(shell which chown)
CHGRP = $(shell which chgrp)
SED = $(shell which sed)
RM = $(shell which rm)

# Files owner
OWNER ?= root
OGROUP ?= root

FILESTOINSTALL = modules

# Default target
all:

clean_mo:
	sh scripts/clean_mo.sh

build_mo:
	sh scripts/build_mo.sh .

build_pot:
	sh scripts/build_pot.sh

clean: clean_mo

install: build_mo
	@echo ""
	@echo "Installing mmc-web in $(DESTDIR)$(DATADIR)"
	$(INSTALL) -d -m 755 -o $(OWNER) -g $(OGROUP) $(DESTDIR)$(DATADIR)
	$(CP) -R $(FILESTOINSTALL) $(DESTDIR)$(DATADIR)
	$(CHOWN) -R $(OWNER) $(DESTDIR)$(DATADIR)
	$(CHGRP) -R $(OGROUP) $(DESTDIR)$(DATADIR)
	find $(DESTDIR)$(DATADIR) -type f -name "*.po[t]" -exec rm -f {} \;

$(RELEASES_DIR)/$(TARBALL_GZ):
	mkdir -p $(RELEASES_DIR)/$(TARBALL)
	$(CPA) $(FILESTOINSTALL) $(RELEASES_DIR)/$(TARBALL)
	cd $(RELEASES_DIR) && tar -czf $(TARBALL_GZ) $(EXCLUDE_FILES) $(TARBALL); rm -rf $(TARBALL);
