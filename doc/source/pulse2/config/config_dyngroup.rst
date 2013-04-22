.. highlight:: none

======================================
MMC dyngroup plugin configuration file
======================================

This document explains the content of the MMC dyngroup plugin configuration file.

Introduction
============

The « dyngroup » plugin is the MMC plugin in charge of creating, modifying and
deleting groups of machines.

The plugin configuration file is :file:`/etc/mmc/plugins/dyngroup.ini`.

Like all MMC related configuration file, its file format is INI style.
The file is made of sections, each one starting with a « [sectionname] » header.
In each section options can be defined like this: « option = value ».

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

For now 3 sections are available in this configuration file:

============ ===================================================== ========
Section name Description                                           Optional
============ ===================================================== ========
main         Mostly MMC related behaviors                          no
database     Needed options to connect to the database             no
querymanager Describe how it react as a potential queriable plugin yes
============ ===================================================== ========

« main » section
----------------

This section is used to give directives to the MMC agent.

Available options for the "main" section:

============================ ===================================================================================================== ======== =============
Option name                  Description                                                                                           Optional Default value
============================ ===================================================================================================== ======== =============
disable                      Whenever use this plugin (or not)                                                                     yes      0
dyngroup_activate            Tell if the dynamic group part is activated or if there is only the static group part                 yes      1
profiles_enable              Allow imaging fonctionnality on a profile, that is not available on static, and dynamic group         yes      0
default_module               Set the module that is going to be automatically selected is more than one dyngroup module is defined yes
max_elements_for_static_list The maximum number of elements that have to be display in the static group creation list              yes      2000
============================ ===================================================================================================== ======== =============

« database » section
--------------------

This section defines the database options.

Available options for the "database" section:

============= ================================================================= ======== ===============================
Option name   Description                                                       Optional Default value
============= ================================================================= ======== ===============================
dbdriver      DB driver to use                                                  no       mysql
dbhost        Host which hosts the DB                                           no       127.0.0.1
dbport        Port on which to connect to reach the DB                          no       3306 (aka "default MySQL port")
dbname        DB name                                                           no       dyngroup
dbuser        Username to give while conencting to the DB                       no       mmc
dbpasswd      Password to give while connecting to the DB                       no       mmc
dbdebug       Whenever log DB related exchanges                                 yes      ERROR
dbpoolrecycle DB connection time-to-live                                        yes      60 (seconds)
dbpoolsize    The number of connections to keep open inside the connection pool yes      5
dbsslenable   SSL connection to the database                                    yes      0
dbsslca       CA certificate for SSL connection                                 yes
dbsslcert     Public key certificate for SSL connection                         yes
dbsslkey      Private key certificate for SSL connection                        yes
============= ================================================================= ======== ===============================

« querymanager » section
------------------------

This section define how this plugin react as a potential queriable plugin.

Available options for the "querymanager" section:

=========== ========================================== ======== =============
Option name Description                                Optional Default value
=========== ========================================== ======== =============
activate    If queries on the group name are possible. yes      1
=========== ========================================== ======== =============
