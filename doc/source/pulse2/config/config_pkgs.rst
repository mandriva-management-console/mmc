

==================================
MMC pkgs plugin configuration file
==================================

This document explains the content of the MMC pkgs plugin configuration file/

Introduction
============

The « pkgs » plugin is the MMC plugin in charge of the edition, removal and
creation of packages in the Pulse2 package system.

The plugin configuration file is :file:`/etc/mmc/plugins/pkgs.ini`.

Like all MMC related configuration file, its file format is INI style. The file
is made of sections, each one starting with a « [sectionname] » header. In each
section options can be defined like this: « option = value ».

For example:

::

    [section1]
    option1 = 1
    option2 = 2

    [section2]
    option1 = foo
    option2 = plop

Configuration file sections
===========================

For now two sections are available in this configuration file:

================ ================================================== ========
Section name     Description                                        Optional
================ ================================================== ========
main             Mostly MMC related behaviors                       yes
user_package_api Describe how to reach the User package API service yes
================ ================================================== ========

« main » section
----------------

This section is used to give directives to the MMC agent.

Available options for the "main" section:

=========== ================================= ======== =============
Option name Description                       Optional Default value
=========== ================================= ======== =============
disable     Whenever use this plugin (or not) yes      0
=========== ================================= ======== =============

« user_package_api » section
----------------------------

This section is used to tell to the plugin where to find its User Package API
service.

Available options for the "user_package_api" section:

=========== =================================================================================== ======== =============
Option name Description                                                                         Optional Default value
=========== =================================================================================== ======== =============
server      The service IP address                                                              yes      127.0.0.1
port        The service TCP port                                                                yes      9990
mountpoint  The service path                                                                    yes      /upaa
username    The name to use when we send XMLRPC call                                            yes      ""
password    The password to use when we send XMLRPC call                                        yes      ""
enablessl   SSL mode support                                                                    yes      1
verifypeer  use SSL certificates                                                                yes      0
cacert      path to the certificate file describing the certificate authority of the SSL server yes      ""
localcert   path to the SSL server private certificate                                          yes      ""
=========== =================================================================================== ======== =============
