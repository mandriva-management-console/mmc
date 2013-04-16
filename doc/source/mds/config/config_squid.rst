.. highlight:: none
.. _config-squid:

===================================
MMC squid plugin configuration file
===================================

This document explains the content of the MMC squid plugin configuration file.

Introduction
############

The plugin allows control of internet content filters, manipulating squid files directly and use the LDAP base to authentication of users.

The plugin configuration file is :file:`/etc/mmc/plugins/squid.ini`.

Like all MMC related configuration file, its file format is INI style.
The file is made of sections, each one starting with a « [sectionname] » header.
In each section options can be defined like this « option = value ».

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

Here are all the squid.ini available sections:

============ ================================================================================================= ========
Section name Description                                                                                       Optional
============ ================================================================================================= ========
main         global mail plugin configuration                                                                  no
squid        paths and names of LDAP access groups                                                             no
============ ================================================================================================= ========

Section « main »
################

This sections defines the global options of the squid plugin

Available options for the « main » section:

=============== ============================================================= ================================== ==========================
Option name     Description                                                   Optional                           Default value
=============== ============================================================= ================================== ==========================
disable         Is this plugin disabled ?                                     Yes                                1
=============== ============================================================= ================================== ==========================

Section « squid »
#################

Available options for the « main » section:

================= ================================================================ ================================== ==============================================
Option name       Description                                                      Optional                           Default value
================= ================================================================ ================================== ==============================================
squidBinary       path to the squid binary                                         No                                 /usr/sbin/squid3
squidInit         the path of the squid init script                                No                                 /etc/init.d/squid3
squidPid          the path of squid pid file                                       No                                 /var/run/squid3.pid
sargBinary        the path of the sarg binary                                      Yes                                /usr/bin/sarg

groupMaster       the name of the group that have full access                      No                                 InternetMaster
groupMasterDesc   the group description                                            No                                 Full Internet access
groupFiltered     the name of the group that have a filtered access                No                                 InternetFiltered
groupFilteredDesc the group description                                            No                                 Filtered Internet access

squidRules        the path where will be stored rule files                         No                                 /etc/squid/rules/
blacklist         path to the blacklist file                                       No                                 %(squidRules)s/blacklist.txt
whitelist         path to the whitelist file                                       No                                 %(squidRules)s/whitelist.txt
blacklist_ext     path to the extensions blacklist file                            No                                 %(squidRules)s/blacklist_ext.txt
timeranges        path to the timeranges file                                      No                                 %(squidRules)s/timeranges.txt
machines          path to the machines file                                        No                                 %(squidRules)s/machines.txt
================= ================================================================ ================================== ==============================================







