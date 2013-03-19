============
Introduction
============

The **Mandriva Directory Server** (MDS) provides different modules running on
top of the **Mandriva Management Console**.

MDS is composed of the following plugins:

- **samba**: The « samba » plugin allows the MMC to add/remove SAMBA attributes
  to users and groups, to manage SAMBA share, etc.

- **network**: The « network » plugin allows the MMC Python API to manage DNS
  zones and hosts, DHCP subnet and hosts, into a LDAP. Patched version of ISC
  BIND (with LDAP sdb backend) and ISC DHCP (with LDAP configuration file backend)
  are needed. PowerDNS support is also available.

- **mail**: The « mail » plugin allows the MMC to add/remove mail delivery
  management attributes to users and groups, and mail virtual domains, mail
  aliases, etc. Zarafa support is also available.

- **sshlpk**: The « sshlpk » plugin allows the MMC to manage lists of SSH
  public keys on users.

- **userquota**: The « userquota » plugin allows the MMC to set filesystem quotas
  to users. The plugin provides LDAP attributes for storing quota information.
  The plugin allows also to store network quotas in the LDAP directory for
  external tools.

- **shorewall**: The « shorewall » plugin provides an interface to configure
  shorewall rules and policies from the MMC. Shorewall is wrapper around
  iptables [#f1]_.

Before installing MDS plugins, you have to install the Mandriva Management
Console (see :ref:`mmc-install`).

.. [#f1] http://shorewall.net/
