#!/bin/bash -e

#
# (c) 2010 Mandriva, http://www.mandriva.com
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

# Prepare MDS for Selenium tests

sed -i "s/fr_FR =/fr_FR = Titre de test/" /etc/mmc/mmc.ini
# Remove previously created home directory
rm -fr /home/*
# Re-create archives directory
mkdir /home/archives

exit 0