# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
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

RESULTDIR=/tmp/selenium-mds.$(shell date +%Y-%m-%d+%H:%M:%S)
selenium:
	tests/scripts/prepare-for-selenium-tests.sh
	$(MMCCORE)/tests/scripts/build-selenium-suite.sh selenium-suite.html tests/selenium/suite/
	mkdir $(RESULTDIR)
	$(MMCCORE)/tests/scripts/run-selenium.sh selenium-suite.html $(RESULTDIR)
