#!/bin/bash -e

#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2009 Mandriva, http://www.mandriva.com
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

echo "MDS basic auto-installation script"
echo

if [ ! -f "/bin/lsb_release" ];
then
    echo "Please install lsb_release."
    echo "urpmi lsb-release"
    exit 1
fi	

if [ ! -f /etc/mandriva-release ];
then
    echo "This Operating System is not supported."
    exit 1
fi

OSRELEASE=`lsb_release -ir -s`
if [ "$OSRELEASE" != "MandrivaLinux 2010.0" ];
then
    echo "This version of Operating System ($OSRELEASE) is not supported"
    exit 1
fi

echo "WARNING: this script will erase some parts of your configuration !"
echo "         type Ctrl-C now to exit if you are not sure"
echo "         type Enter to continue"
read

# LDAP stuff
urpmi openldap-servers openldap-mandriva-dit

# Python stuff
urpmi lib64python2.6-devel libpython2.6-devel
urpmi python-twisted-web python-ldap python-sqlalchemy 

# Apache/PHP
urpmi apache-mpm-prefork apache-mod_php php-gd php-iconv php-xmlrpc

# Development & install
urpmi subversion make

pushd /tmp

rm -fr mmc-core
svn co https://mds.mandriva.org/svn/mmc-projects/mmc-core/trunk mmc-core

pushd mmc-core/agent
make install PREFIX=/usr
cp contrib/ldap/mmc.schema /etc/openldap/schema/
popd

pushd mmc-core/web
make install PREFIX=/usr HTTPDUSER=apache
cp confs/apache/mmc.conf /etc/httpd/conf/webapps.d/
popd

rm -fr mds
svn co https://mds.mandriva.org/svn/mmc-projects/mds/trunk mds

pushd mds/agent
make install PREFIX=/usr
popd

pushd mds/web
make install PREFIX=/usr
popd

popd

# Setup LDAP
/usr/share/openldap/scripts/mandriva-dit-setup.sh -d mandriva.com -p secret -y
sed -i 's/cn=admin/uid=LDAP Admin,ou=System Accounts/' /etc/mmc/plugins/base.ini

sed -i 's!#include.*/etc/openldap/schema/local.schema!include /etc/openldap/schema/local.schema!g' /etc/openldap/slapd.conf
sed -i '/.*kolab.schema/d' /etc/openldap/slapd.conf
rm -f /etc/openldap/schema/local.schema
echo "include /etc/openldap/schema/mmc.schema" >> /etc/openldap/schema/local.schema

# Restart LDAP & APACHE
service ldap restart
service httpd restart

# Recreate log directory
rm -fr /var/log/mmc
mkdir /var/log/mmc

mkdir -p /home/archives

# Start MMC agent
service mmc-agent start

echo "Installation done successfully"
exit 0
