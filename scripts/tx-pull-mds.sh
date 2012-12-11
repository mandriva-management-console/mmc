#!/bin/bash

# This script maps PO and POT files to transifex ressources
# on https://transifex.mandriva.com

if [[ ! -d core && ! -d mds && ! -d pulse2 ]]; then
	echo "Run this script at the top dir of the repository"
	exit 1
fi

which tx > /dev/null
if [ $? -ne 0 ]; then
	echo "Install the transifex client v0.4 (pip install transifex-client==0.4)"
	exit 1
fi

test -d .tx || tx init --host=https://transifex.mandriva.com

[ ! x$1 == x ] && args="-l $1"
modules="base ppolicy services dashboard bulkimport mail network proxy samba sshlpk userquota shorewall"

for mod in $modules
do
	tx pull -r mds.${mod} ${args}
done
