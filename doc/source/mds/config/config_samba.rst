.. highlight:: none
.. _config-samba:

===================================
MMC SAMBA plugin configuration file
===================================

This document explains the content of the MMC SAMBA plugin configuration file.

Introduction
############

The « samba » plugin allows the MMC to add/remove SAMBA attributes to users
and groups, to manage SAMBA share, etc. It uses the « base » plugin for all
its related LDAP operations.

The plugin configuration file is :file:`/etc/mmc/plugins/samba.ini`.

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

Here are all the samba.ini available sections:

============ ================================================================================================ ========
Section name Description                                                                                      Optional
============ ================================================================================================ ========
main         global SAMBA plugin configuration                                                                yes
hooks        Hooks for scripts that interacts with the MMC                                                    yes
userdefault  Attributes and Objectclass values that are added or deleted when adding a new user into the LDAP yes
============ ================================================================================================ ========

Section « main »
################

This section defines the global options of the SAMBA plugin.

Available options for the "main" section:

==================== ================================================================================================================================================================================================================================================================== ======== ==================================
Option name          Description                                                                                                                                                                                                                                                        Optional Default value
==================== ================================================================================================================================================================================================================================================================== ======== ==================================
baseComputersDN      LDAP organisational unit DN where the SAMBA computer accounts are located                                                                                                                                                                                          no
sambaConfFile        Main SAMBA configuration file path                                                                                                                                                                                                                                 yes      /etc/samba/smb.conf
sambaInitScript      System SAMBA initialization script                                                                                                                                                                                                                                 yes      /etc/init.d/samba
sambaAvSo            VFS shared library location for anti-virus check on shares (scannedonly, vscan-clamav...). If this file is present, we can enable anti-virus check when creating a SAMBA share. This results to an option on the share : vfs object = libname (without .so suffix) yes      /usr/lib/samba/vfs/vscan-clamav.so
defaultSharesPath    Directory where the SAMBA shares are created, if no path is specified                                                                                                                                                                                              no
authorizedSharePaths Comma-separated list of directories where SAMBA shares are allowed to be created.                                                                                                                                                                                  yes      The value of defaultSharesPath
==================== ================================================================================================================================================================================================================================================================== ======== ==================================

Section « hooks »
#################

The hooks system allow you to run external script when doing some operations
with the MMC.

The script will be run as root user, with as only argument the full LDIF of
the LDAP user. For the « addsmbattr » and « changeuserpasswd » hook, the LDIF
file will contains the userPassword attributes in cleartext.

Available options for the "hooks" section:

===================== ===================================================================================== ========
Option name           Description                                                                           Optional
===================== ===================================================================================== ========
addsmbattr            path to the script launched when the SAMBA LDAP attributes has been added to a user   yes
changesambaattributes path to the script launched when the SAMBA LDAP attributes has been changed on a user yes
changeuserpasswd      path to the script launched when the SAMBA password of a user is changed              yes
===================== ===================================================================================== ========

Section « userdefault »
#######################

When adding the SAMBA attributes to a user, you may want to change the value
of the attribute that are added. Please look at the :ref:`config-base` for a
look at how this section works.

For example, if you want to delete the sambaPwdMustChange attribute of a
user entry:

::

    sambaPwdMustChange = DELETE
