.. _config-sshlpk:

====================================
MMC sshlpk plugin configuration file
====================================

This document explains the content of the MMC sshlpk plugin configuration file.

Introduction
############

The « sshlpk » plugin allows the MMC to manage lists of SSH public keys on
users. It uses the « base » plugin for all its related LDAP operations.

The plugin configuration file is :file:`/etc/mmc/plugins/sshlpk.ini`.

Like all MMC related configuration file, its file format is INI style. The
file is made of sections, each one starting with a « [sectionname] » header.
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

Here are all the sshlpk.ini available sections:

============ ============================================= ========
Section name Description                                   Optional
============ ============================================= ========
main         global sshlpk plugin configuration            no
hooks        hooks for scripts that interacts with the MMC yes
============ ============================================= ========

Section « main »
################

This sections defines the global options of the sshlpk plugin

Available options for the "main" section:

=========== ======================================= ======== =============
Option name Description                             Optional Default value
=========== ======================================= ======== =============
disable     Define if the plugin is disabled or not no       no
=========== ======================================= ======== =============

Section « hooks »
#################

The hooks system allow you to run external script when doing some operations
with the MMC.

The script will be run as root user, with as only argument the full LDIF
of the LDAP user.

Available options for the "hooks" section:

============= ======================================================================= ========
Option name   Description                                                             Optional
============= ======================================================================= ========
updatesshkeys path to the script launched when the user's SSH public keys are updated yes
============= ======================================================================= ========
