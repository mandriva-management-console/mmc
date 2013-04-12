.. highlight:: none
.. _config-squid:

==================================
MMC squid plugin configuration file
==================================

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
#######################

 Available options for the « main » section:

================= ================================================================ ================================== ==============================================
Option name       Description                                                      Optional                           Default value 
================= ================================================================ ================================== ==============================================
squidRules        the path where will be stored rule files                         No                                 /etc/squid/rules/
squidMasterGroup  the path where will be stored rule files to Master group         No                                 %(squidRules)s/group_internet 
squidBinary       path to bin of squid                                             No                                 /usr/sbin/squid
normalBlackList   the path where will be stored files content bad words            No                                 %(squidMasterGroup)s/normal_blacklist.txt
normalWhiteList   the path where will be stored files content good words           No                                 %(squidMasterGroup)s/normal_whitelist.txt
normalBlackExt    the path where will be stored files content extensions blocked   No                                 %(squidMasterGroup)s/normal_blacklist_ext.txt
timeDay           the path where will be stored files content range time to access No                                 %(squidMasterGroup)s/time_day.txt
normalMachList    the path where will be stored files content allowed IPs          No                                 %(squidMasterGroup)s/allow_machines.txt
squidInit         the path of the squid daemon                                     No                                 /etc/init.d/squid
squidPid          the path of squid pid file                                       No                                 /var/run/squid.pid
sargBinary        the path of sarg                                                 Yes                                /usr/bin/sarg
groupMaster       the name of the group that have free access                      No                                 InternetMaster
groupFiltered     the name of the group that have a filtered access                No                                 Internet
================= ================================================================ ================================== ==============================================







