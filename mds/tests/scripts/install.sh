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

if [ ! -f `which lsb_release` ];
then
    echo "Please install lsb_release."
    echo "urpmi lsb-release"
    exit 1
fi

DISTRIBUTION=`lsb_release -i -s`
RELEASE=`lsb_release -r -s`

PKGS=
ARCH=
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    if [ `arch` == "x86_64" ]; then
        ARCH=64
    fi
fi

function packages_to_install () {
    # for MDS samba plugin
    if [ $DISTRIBUTION == "MandrivaLinux" ]; then
        PKGS="$PKGS samba-server smbldap-tools nss_ldap"
        if [ $RELEASE == "2010.0" ];
            then
            PKGS="$PKGS python-pylibacl"
        fi
        if [ $RELEASE == "2009.0" ];
            then
            PKGS="$PKGS pylibacl"
        fi
    fi
    if [ $DISTRIBUTION == "Debian" ]; then
	echo samba-common samba-common/dhcp string false | debconf-set-selections
	echo samba-common samba-common/workgroup string MANDRIVA | debconf-set-selections
        echo libnss-ldap libnss-ldap/confperm string false | debconf-set-selections
        echo libnss-ldap libnss-ldap/dblogin string false | debconf-set-selections
        echo libnss-ldap libnss-ldap/dbrootlogin string false | debconf-set-selections
        echo libnss-ldap libnss-ldap/override string true | debconf-set-selections
        echo libnss-ldap shared/ldapns/base-dn string dc=mandriva,dc=com | debconf-set-selections
        echo libnss-ldap shared/ldapns/ldap-server string ldap:///127.0.0.1 | debconf-set-selections
        echo libnss-ldap shared/ldapns/ldap_version string 3 | debconf-set-selections
	echo libpam-ldap libpam-ldap/dblogin string false | debconf-set-selections
	echo libpam-ldap libpam-ldap/dbrootlogin string false | debconf-set-selections
	echo libpam-ldap libpam-ldap/pam_password string crypt | debconf-set-selections
        PKGS="$PKGS samba smbldap-tools libnss-ldap"
    fi

    # for MDS network plugin DHCP
    if [ $DISTRIBUTION == "MandrivaLinux" ]; then
        PKGS="$PKGS dhcp-server"
    fi
    if [ $DISTRIBUTION == "Debian" ]; then
        PKGS="$PKGS dhcp3-server dhcp3-server-ldap"
    fi

    # for MDS network plugin BIND
    if [ $DISTRIBUTION == "MandrivaLinux" ]; then
        PKGS="$PKGS bind"
    fi
    if [ $DISTRIBUTION == "Debian" ]; then
        PKGS="$PKGS bind9"
    fi
    # for MDS proxy plugin
    if [ $DISTRIBUTION == "MandrivaLinux" ]; then
        PKGS="$PKGS squid"
        if [ $RELEASE == "2006.0" -o $RELEASE == "2009.0" ];
            then
            PKGS="$PKGS squidGuard"
        fi
    fi
    if [ $DISTRIBUTION == "Debian" ]; then
        PKGS="$PKGS bind9"
    fi

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
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    urpmi --auto --no-suggests $PKGS
    rpm -q $PKGS
fi
if [ $DISTRIBUTION == "Debian" ]; then
    export DEBIAN_FRONTEND=noninteractive
    apt-get install --yes $PKGS
    export DEBIAN_FRONTEND=newt
    dpkg -l $PKGS
fi

# for MDS mail plugin
# Nothing needed

TMPCO=`mktemp -d`

pushd $TMPCO

# Checkout MDS
svn co http://mds.mandriva.org/svn/mmc-projects/mds/trunk mds

pushd mds/agent
make install PREFIX=/usr
popd

pushd mds/web
make install PREFIX=/usr
popd

popd

if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    schema_dir=/etc/openldap/schema
fi
if [ $DISTRIBUTION == "Debian" ]; then
    schema_dir=/etc/ldap/schema
fi

# Setup Mail LDAP schema
echo "include ${schema_dir}/mail.schema" >> ${schema_dir}/local.schema
sed -i "s/vDomainSupport = 0/vDomainSupport = 1/" /etc/mmc/plugins/mail.ini

# Setup SSH-LPK LDAP schema
echo "include ${schema_dir}/openssh-lpk.schema" >> ${schema_dir}/local.schema

# Setup Quota LDAP schema
echo "include ${schema_dir}/quota.schema" >> ${schema_dir}/local.schema

#############
# Setup SAMBA
#############
cp $TMPCO/mds/agent/contrib/samba/smb.conf /etc/samba/
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    /etc/init.d/smb stop || true
    sed -i 's/cn=admin/uid=LDAP Admin,ou=System Accounts/' /etc/samba/smb.conf
fi
if [ $DISTRIBUTION == "Debian" ]; then
    # Setup samba LDAP schema
    echo "include ${schema_dir}/samba.schema" >> ${schema_dir}/local.schema
    invoke-rc.d slapd restart
    invoke-rc.d samba stop
fi

if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    # Remove old smbldap-tools confs
    rm -f /etc/smbldap-tools/smbldap.conf
    rm -f /etc/smbldap-tools/smbldap_bind.conf
    # Copy the default ones
    cp /usr/share/doc/smbldap-tools*/smbldap.conf /etc/smbldap-tools/
    cp /usr/share/doc/smbldap-tools*/smbldap_bind.conf /etc/smbldap-tools/
    ADMINCN="uid=LDAP Admin,ou=System Accounts,dc=mandriva,dc=com"
fi
if [ $DISTRIBUTION == "Debian" ]; then
    ADMINCN="cn=admin,dc=mandriva,dc=com"
    zcat /usr/share/doc/smbldap-tools/examples/smbldap.conf.gz > \
        /etc/smbldap-tools/smbldap.conf
    cp /usr/share/doc/smbldap-tools/examples/smbldap_bind.conf \
        /etc/smbldap-tools/smbldap_bind.conf
fi
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
sed -i "s/^\(sambaDomain=\).*$/\1\"${WORKGROUP}\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(sambaUnixIdPooldn=\).*$/\1\"sambaDomainName=${WORKGROUP},${BASEDN}\"/" /etc/smbldap-tools/smbldap.conf
sed -i 's!^\(defaultMaxPasswordAge=.*\)$!#\1!' /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userSmbHome=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userProfile=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userHomeDrive=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
sed -i "s/^\(userScript=\).*$/\1\"\"/" /etc/smbldap-tools/smbldap.conf
# Populate LDAP for SAMBA
echo -e "${ADMINCNPW}\n${ADMINCNPW}" | smbldap-populate -m 512 -a administrator -b guest

if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    sed -i 's!sambaInitScript = /etc/init.d/samba!sambaInitScript = /etc/init.d/smb!' /etc/mmc/plugins/samba.ini
fi

sed -i "s/^\(passwd:\).*$/\1 files ldap/" /etc/nsswitch.conf
sed -i "s/^\(group:\).*$/\1 files ldap/" /etc/nsswitch.conf
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    cp /usr/share/doc/nss_ldap*/ldap.conf /etc/ldap.conf
    sed -i "s/base dc=padl,dc=com/base dc=mandriva,dc=com/" /etc/ldap.conf
fi
echo -e "${ADMINCNPW}\n${ADMINCNPW}" | smbpasswd -s -a administrator

# Restart LDAP & APACHE
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    service ldap restart
    service httpd restart
fi
if [ $DISTRIBUTION == "Debian" ]; then
    invoke-rc.d slapd restart
    invoke-rc.d apache2 restart
fi

# Setup DHCP
# Setup DHCP LDAP schema
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    service dhcpd stop
    cp $TMPCO/mds/agent/contrib/dhcpd/dhcpd.conf /etc/dhcpd.conf
    sed -i "s!leases = /var/lib/dhcp3/dhcpd.leases!leases = /var/lib/dhcp/dhcpd.leases!" /etc/mmc/plugins/network.ini
    service dhcpd start || true
fi
if [ $DISTRIBUTION == "Debian" ]; then
    echo "include ${schema_dir}/dhcp.schema" >> ${schema_dir}/local.schema
    invoke-rc.d slapd restart
    invoke-rc.d dhcp3-server stop
    cp $TMPCO/mds/agent/contrib/dhcpd/dhcpd.conf /etc/dhcp3/dhcpd.conf
    invoke-rc.d dhcp3-server start || true
fi

# Setup BIND
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    service named stop || true
    sed -i "s!init = /etc/init.d/dhcp3-server!init = /etc/init.d/dhcpd!" /etc/mmc/plugins/network.ini
    sed -i "s!init = /etc/init.d/bind9!init = /etc/init.d/named!" /etc/mmc/plugins/network.ini
    sed -i "s!bindgroup = bind!bindgroup = named!" /etc/mmc/plugins/network.ini
    sed -i "s!bindroot = /etc/bind!bindroot= /var/lib/named/etc/!" /etc/mmc/plugins/network.ini
    echo "bindchrootconfpath = /etc" >> /etc/mmc/plugins/network.ini
    sleep 1
    service named start || true
fi
if [ $DISTRIBUTION == "Debian" ]; then
    # Setup DNS LDAP schema
    echo "include ${schema_dir}/dnszone.schema" >> ${schema_dir}/local.schema
    invoke-rc.d slapd restart
    invoke-rc.d bind9 restart
fi

# Setup SQUID / squidGuard
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    if [ $RELEASE == "2009.0" ]; then
        BLACKLIST=/usr/share/squidGuard-1.4/db/bad.destdomainlist
    elif [ $RELEASE == "2006.0" ]; then
        BLACKLIST=/usr/share/squidGuard-1.2.0/db/bad.destdomainlist
    fi
    if [ $RELEASE == "2006.0" -o $RELEASE == "2009.0" ]; then
        touch $BLACKLIST
        chown squid.squid $BLACKLIST
        sed -i "s!blacklist = /var/lib/squidguard/db/bad.destdomainlist!blacklist = $BLACKLIST!" /etc/mmc/plugins/proxy.ini
        sed -i "s/user = proxy/user = squid/" /etc/mmc/plugins/proxy.ini
        sed -i "s/group = proxy/group = squid/" /etc/mmc/plugins/proxy.ini
    fi
fi

# Restart MMC agent
if [ $DISTRIBUTION == "MandrivaLinux" ]; then
    service mmc-agent force-stop
    rm -f /var/run/mmc-agent.pid
    service mmc-agent start
fi
if [ $DISTRIBUTION == "Debian" ]; then
    invoke-rc.d mmc-agent restart
fi

rm -fr $TMPCO

echo "Installation done successfully"

exit 0
