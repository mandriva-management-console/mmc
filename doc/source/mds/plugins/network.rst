.. highlight:: none

==============
Network plugin
==============

Introduction
============

This plugin allows to store in a LDAP directory:

- DNS zones declarations and related DNS records as needed for a standard LAN;
- DHCP server configuration with DHCP subnet, dynamic pool and static host
  declarations.

The MMC web interface allows to easily manage the DNS and DHCP services.

The network plugin relies on patched version of ISC DHCP 3 and ISC BIND 9:

- ISC BIND: a patch featuring a LDAP sdb backend must be applied to your BIND
  installation. With this patch BIND will be able to read DNS zone declarations
  from a LDAP directory. This patch is available `there <http://www.venaas.no/ldap/bind-sdb/>`_.
  The stable release of this patch (version 1.0) works fine.

- ISC DHCP: the patch `on this page <http://home.ntelos.net/~masneyb/>`_ allows
  to store into a LDAP the DHCP service configuration (instead of :file:`/etc/dhcp3/dhcpd.conf`).

Installation
============

Install the packages ``python-mmc-network`` and ``mmc-web-network``.

Debian packages for patched versions of BIND
============================================

We provide Debian Lenny packages for the LDAP patched version of BIND.
This packages work on Squeeze too.

Configure your APT repository as in the :ref:`debian-packages` section.
And add in /etc/apt/preferences.d/pining :

::

    Package: *
    Pin: origin mds.mandriva.org
    Pin-Priority: 1001

Then install the packages :

::

    # apt-get update
    # apt-get install bind9 isc-dhcp-server-ldap


DNS service configuration (ISC BIND)
====================================

When managing the DNS zones, the MMC agent will create files into the BIND
configuration directory (located in :file:`/etc/bind/`). These files must be
included in the main BIND configuration file so that the corresponding zones
are loaded from the LDAP directory.

All the DNS zones are defined in the file :file:`named.conf.ldap`. This file
must be included in the main BIND configuration file :file:`named.conf`.
Adding this line at the end of BIND :file:`named.conf` should be sufficient:

::

    include "/etc/bind/named.conf.ldap";

An example of :file:`named.conf` filename for Debian based system is available
at :file:`/usr/share/doc/mmc/contrib/network/named.conf`.

.. note:: BIND and OpenLDAP services startup order

   On most distributions, BIND is started before OpenLDAP during the boot
   sequence. If BIND/LDAP is used, BIND won't be able to connect to the LDAP
   directory, and won't start. So you may need to tweak your system boot scripts
   to fix this. The following command line should work on Debian based systems:

   ::

       # update-rc.d -f slapd remove && update-rc.d slapd start 14 2 3 4 5 . stop 86 0 1 6 .

DHCP service configuration (ISC DHCP)
=====================================

The DHCP server needs to know how to load its configuration from LDAP.
Here is a typical :file:`/etc/dhcp/dhcpd.conf`:

::

    ldap-server "localhost";
    ldap-port 389;
    ldap-username "cn=admin, dc=mandriva, dc=com";
    ldap-password "secret";
    ldap-base-dn "dc=mandriva, dc=com";
    ldap-method dynamic;
    ldap-debug-file "/var/log/dhcp-ldap-startup.log";

The dhcpd service will try to find an LDAP entry for the machine hostname. If the entry name is different, you can set in :file:`dhcpd.conf`:

::

    ldap-dhcp-server-cn "DHCP_SERVER_NAME";

An example of :file:`dhcpd.conf` filename is available in the directory :file:`/usr/share/doc/mmc/contrib/network/`.

LDAP Schemas
============

Two new LDAP schemas must be imported into your LDAP directory: dnszone.schema and dhcp.schema.

Both are available in the directory :file:`/usr/share/doc/mmc/contrib/network/`.

To speed up LDAP search, you can index these attributes: zoneName, relativeDomainName, dhcpHWAddress, dhcpClassData.

For OpenLDAP :file:`slapd.conf` configuration file, you will add:

::

    index zoneName,relativeDomainName eq
    index dhcpHWAddress,dhcpClassData eq


MMC « network » plugin configuration
====================================

For a full description of the MMC network plugin configuration file see
:ref:`config-network`.

You should verify that the paths to directories and init scripts are right.

MMC « network » plugin initialization
=====================================

For the DHCP service only, the MMC network plugin needs to create into the LDAP directory two objects:

- the container called "DHCP config" (objectClass dhcpService), where all the DHCP service configuration will be stored
- the primary server (objectClass dhcpServer) that links to the DHCP service configuration.
  The hostname of the machine running the MMC network plugin will be use to name this entry.

The first start of the MMC network plugin should look like:

::

    ...
    Created OU ou=DHCP,dc=mandriva,dc=com
    Created DHCP config object
    The server 'your_server_hostname' has been set as the primary DHCP server
    Plugin network loaded ...
    ...

DHCP failover configuration
===========================

The DHCP failover can be done directly from the MMC interface on the page
"Network -> Network services management".

The primary DHCP server name is by default the hostname of the server where
the mmc-agent is running. You can override this by setting the "hostname" option in
:file:`/etc/mmc/plugins/network.ini`

To configure DHCP failover you need at least the name of your secondary DHCP server
and the IP addresses of the two DHCP servers. In expert mode you can set any parameter of
the failover configuration.

The secondary ISC dhcpd configuration is almost the same as the primary DHCP:

::

    ldap-server "LDAP_SERVER_IP";
    ldap-port 389;
    ldap-username "cn=admin, dc=mandriva, dc=com";
    ldap-password "secret";
    ldap-base-dn "dc=mandriva, dc=com";
    ldap-dhcp-server-cn "SECONDARY_DHCP_SERVER_NAME";
    ldap-method dynamic;
    ldap-debug-file "/var/log/dhcp-ldap-startup.log";
