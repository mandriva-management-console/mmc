#!/bin/bash -e

#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
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

# bootstrap.sh [target]
# Main entry point to automatically perform a basic Pulse setup using code
# from the SVN.
# If target is specified, the Pulse 2 main Makefile will be called with this
# target.

# Set more permissive umask
umask 0022

# Install script won't ask user confirmation when FORCE=1
export FORCE=1

export LANG=C
export LC_ALL=C

export TMPCO=`mktemp -d`

if [ -f /etc/mandriva-release ];
    then
    urpmi --auto subversion make lsb-release
fi

pushd $TMPCO

# Uninstall MDS
svn co http://mds.mandriva.org/svn/mmc-projects/mds/trunk mds
export MDS="$TMPCO/mds"
pushd $MDS/tests/scripts
./uninstall.sh
popd

svn co http://mds.mandriva.org/svn/mmc-projects/mmc-core/trunk mmc-core
export MMCCORE="$TMPCO/mmc-core"
pushd $MMCCORE/tests/scripts
./uninstall.sh
./install.sh
popd

pushd $MDS/tests/scripts
./install.sh
popd

# Call makefile target if specified
if [ ! -z "$1" ];
    then
    pushd $MDS
    set +e
    make "$1"
    RET=$?
    set -e
    popd
else
    RET=0
fi

popd

rm -fr $TMPCO

exit $RET
