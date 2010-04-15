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

echo "MDS basic auto-installation script"
echo

if [ ! -f "/etc/init.d/mmc-agent" ];
then
    echo "Please install MMC CORE first."
    exit 1
fi

if [ ! -f "/bin/lsb_release" ];
then
    echo "Please install lsb_release."
    echo "urpmi lsb-release"
    exit 1
fi

DISTRIBUTION=`lsb_release -i -s`
RELEASE=`lsb_release -r -s`

PKGS=

if [ `arch` == "x86_64" ];
then
    ARCH=64
else
    ARCH=
fi

function packages_to_install () {
    # for MDS samba plugin
    PKGS="$PKGS samba-server smbldap-tools nss_ldap"
    if [ $RELEASE != "2006.0" ];
        then
        PKGS="PKGS python-pylibacl"
    fi

    # for MDS network plugin DHCP
    PKGS="$PKGS dhcp-server"

    # for MDS network plugin BIND
    PKGS="$PKGS bind"
}

if [ ! -f "$DISTRIBUTION-$RELEASE" ];
then
    echo "This version of Operating System ($DISTRIBUTION-$RELEASE) is not supported"
    exit 1
fi

if [ -z $FORCE ];
    then
    echo "WARNING: this script will erase some parts of your configuration !"
    echo "         type Ctrl-C now to exit if you are not sure"
    echo "         type Enter to continue"
    read
fi

packages_to_install
urpmi --auto --no-suggests $PKGS
rpm -q $PKGS

# for MDS mail plugin
# Nothing needed

TMPCO=`mktemp -d`

pushd $TMPCO

# Checkout MDS
svn co https://mds.mandriva.org/svn/mmc-projects/mds/trunk mds

pushd mds/agent
make install PREFIX=/usr
popd

pushd mds/web
make install PREFIX=/usr
popd

popd

# Setup Mail LDAP schema
echo "include /etc/openldap/schema/mail.schema" >> /etc/openldap/schema/local.schema
sed -i "s/vDomainSupport = 0/vDomainSupport = 1/" /etc/mmc/plugins/mail.ini

# Setup SSH-LPK LDAP schema
echo "include /etc/openldap/schema/openssh-lpk.schema" >> /etc/openldap/schema/local.schema

# Setup Quota LDAP schema
echo "include /etc/openldap/schema/quota.schema" >> /etc/openldap/schema/local.schema

# Setup ppolicy
sed -i "s/disable = 1/disable = 0/" /etc/mmc/plugins/ppolicy.ini

#############
# Setup SAMBA
#############
/etc/init.d/smb stop || true
cp $TMPCO/mds/agent/contrib/samba/smb.conf /etc/samba/
sed -i 's/cn=admin/uid=LDAP Admin,ou=System Accounts/' /etc/samba/smb.conf

# Remove old smbldap-tools confs
rm -f /etc/smbldap-tools/smbldap.conf
rm -f /etc/smbldap-tools/smbldap_bind.conf
# Copy the default ones
cp /usr/share/doc/smbldap-tools*/smbldap.conf /etc/smbldap-tools/
cp /usr/share/doc/smbldap-tools*/smbldap_bind.conf /etc/smbldap-tools/

ADMINCN="uid=LDAP Admin,ou=System Accounts,dc=mandriva,dc=com"
ADMINCNPW="secret"
WORKGROUP="MANDRIVA"
BASEDN="dc=mandriva,dc=com"

smbpasswd -w ${ADMINCNPW}
SID=`net getlocalsid ${WORKGROUP} | sed 's!^.*is: \(.*\)$!\1!'`

# Configure smbldap_bind.conf
sed -i "s/^\(slaveDN=\).*$/\1\"${ADMINCN}\"/" /etc/smbldap-tools/smbldap_bind.conf
sed -i "s/^\(masterDN=\).*$/\1\"${ADMINCN}\"/" /etc/smbldap-tools/smbldap_bind.conf
sed -i "s/^\(slavePw=\).*$/\1\"${ADMINCNPW}\"/" /etc/smbldap-tools/smbldap_bind.conf
sed -i "s/^\(masterPw=\).*$/\1\"${ADMINCNPW}\"/" /etc/smbldap-tools/smbldap_bind.conf
# Configure smbldap.conf
sed -i "s/^\(slaveLDAP=\).*$/\1\"127.1\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(masterLDAP=\).*$/\1\"127.1\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(ldapTLS=\).*$/\1\"0\"/" /etc/smbldap-tools/smbldap.conf

sed -i "s/^\(usersdn=\).*$/\1\"ou=Users\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(groupsdn=\).*$/\1\"ou=Groups\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(computersdn=\).*$/\1\"ou=Computers\"/" /etc/smbldap-tools/smbldap.conf

sed -i "s/^\(SID=\).*$/\1\"${SID}\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(suffix=\).*$/\1\"${BASEDN}\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(sambaUnixIdPooldn=\).*$/\1\"sambaDomainName=${WORKGROUP},${BASEDN}\"/" /etc/smbldap-tools/smbldap.conf
sed -i 's!^\(defaultMaxPasswordAge=.*\)$!#\1!' /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userSmbHome=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userProfile=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userHomeDrive=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userScript=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
# Populate LDAP for SAMBA
echo -e "${ADMINCNPW}\n${ADMINCNPW}" | smbldap-populate -m 512 -a administrator -b guest

sed -i 's!sambaInitScript = /etc/init.d/samba!sambaInitScript = /etc/init.d/smb!' /etc/mmc/plugins/samba.ini

sed -i "s/^\(passwd:\).*$/\1 files ldap/" /etc/nsswitch.conf
sed -i "s/^\(group:\).*$/\1 files ldap/" /etc/nsswitch.conf
cp /usr/share/doc/nss_ldap*/ldap.conf /etc/ldap.conf
sed -i "s/base dc=padl,dc=com/base dc=mandriva,dc=com/" /etc/ldap.conf

echo -e "${ADMINCNPW}\n${ADMINCNPW}" | smbpasswd -s -a administrator

# Restart LDAP & APACHE
service ldap restart
service httpd restart

# Setup DHCP
service dhcpd stop
cp $TMPCO/mds/agent/contrib/dhcpd/dhcpd.conf /etc/dhcpd.conf
service dhcpd start || true

# Setup BIND
service named stop
sed -i "s!init = /etc/init.d/dhcp3-server!init = /etc/init.d/dhcpd!" /etc/mmc/plugins/network.ini
sed -i "s!init = /etc/init.d/bind9!init = /etc/init.d/named!" /etc/mmc/plugins/network.ini
sed -i "s!bindgroup = bind!bindgroup = named!" /etc/mmc/plugins/network.ini
sed -i "s!bindroot = /etc/bind!bindroot= /var/lib/named/etc/!" /etc/mmc/plugins/network.ini
echo "bindchrootconfpath = /etc" >> /etc/mmc/plugins/network.ini
sleep 1
service named start || true

# Restart MMC agent
service mmc-agent restart

rm -fr $TMPCO

echo "Installation done successfully"

exit 0
