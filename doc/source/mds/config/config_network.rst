.. highlight:: none
.. _config-network:

=====================================
MMC network plugin configuration file
=====================================

This document explains the content of the MMC network plugin configuration file.

Introduction
############

The « network » plugin allows the MMC Python API to manage DNS
zones and hosts, DHCP subnet and hosts, into a LDAP. Patched
version of ISC BIND (with LDAP sdb backend) and ISC DHCP (with
LDAP configuration file backend) are needed. PowerDNS support
is also available.

The plugin configuration file is :file:`/etc/mmc/plugins/network.ini`.

Like all MMC related configuration file, its file format is INI
style. The file is made of sections, each one starting with a «
[sectionname] » header. In each section options can be defined
like this « option = value ».

For example:

::

    [section1]
    option1 = 1
    option2 = 2

    [section2]
    option1 = foo
    option2 = plop

Configuration file sections
###########################

Here are all the network.ini available sections:

============ =================================== ========
Section name Description                         Optional
============ =================================== ========
main         global network plugin configuration yes
dns          DNS related configuration           no
dhcp         DHCP related configuration          no
============ =================================== ========

Section « main »
################

This sections defines the global options of the network plugin.

Available options for the "main" section:

=========== ====================== ======== =============
Option name Description            Optional Default value
=========== ====================== ======== =============
disable     Is the plugin disabled yes      no
=========== ====================== ======== =============

Section « dns »
###############

This section defines where DNS needed files, directories and LDAP entities
are located.

When the plugin starts for the first time, it creates:

- the directory :file:`bindroot/named.ldap`. This directory will contains all
  zones definitions
- the file :file:`bindroot/named.conf.ldap`. This file will include all the
  zone definitions stored into :file:`bindroot/named.ldap`/

Available options for the "dns" section:

================== =================================================================================================== ======================= =============
Option name        Description                                                                                         Optional                Default value
================== =================================================================================================== ======================= =============
type               DNS server type: "bind" or "pdns" (PowerDNS)                                                        yes                     bind
dn                 LDAP DN where the DNS zones are stored                                                              no
logfile            path to BIND log file                                                                               no
pidfile            path to BIND pid file                                                                               no
init               BIND init script                                                                                    no
bindchrootconfpath path to the named.ldap directory inside the BIND chroot. Don't set it if BIND is not into a chroot. no
bindroot           path to the BIND configuration file directory                                                       no
bindgroup          gid which BIND is running ("bind" or "named")                                                       no
dnsreader          LDAP user DN to use to read zone info                                                               yes
dnsreaderpassword  password of the user specified in dnsreader                                                         not if dnsreader is set
================== =================================================================================================== ======================= =============

Here is an example for BIND on a Mandriva Corporate Server 4:

::

    [dns]
    type = bind
    dn = ou=DNS,dc=mandriva,dc=com
    pidfile = /var/lib/named/var/run/named.pid
    init = /etc/rc.d/init.d/named
    logfile = /var/log/messages
    bindroot = /var/lib/named/etc/
    bindchrootconfpath = /etc
    bindgroup = named
    dnsreader = uid=DNS Reader,ou=System Accounts,dc=mandriva,dc=com
    dnsreaderpassword = s3cr3t

Section « dhcp »
################

This section defines where DHCP related files and LDAP entities are located.

Available options for the "backup-tools" section:

=========== ===================================================== ======== ===================================================================================================================================================================================================
Option name Description                                           Optional Comment
=========== ===================================================== ======== ===================================================================================================================================================================================================
dn          LDAP DN where the DHCP server configuration is stored no
pidfile     path to DHCP server pidfile                           no
init        path to DHCP service init script                      no
logfile     path to DHCP service log file                         no
leases      path to DHCP service leases file                      no
hostname    name of the DHCP server to user                       no       Set manually the master DHCP hostname in the LDAP. If not set, DHCP name will be the local hostname. If set, you can configure the "ldap-dhcp-server-cn" option in dhcpd.conf to match this setting
=========== ===================================================== ======== ===================================================================================================================================================================================================
