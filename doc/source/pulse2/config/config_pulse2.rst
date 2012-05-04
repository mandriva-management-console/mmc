

====================================
MMC pulse2 plugin configuration file
====================================

This document explains the content of the MMC pulse2 plugin
configuration file.

Introduction
============

The « pulse2 » plugin is the MMC plugin in charge of the very
generic part of pulse2 plugins.

The plugin configuration file is
:file:`/etc/mmc/plugins/pulse2.ini`.

Like all MMC related configuration file, its file format is INI
style. The file is made of sections, each one starting with a
« [sectionname] » header. In each section options can be defined like
this: « option = value ».

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

For now two sections are available in this configuration
file:

============ =============================================== ========
Section name Description                                     Optional
============ =============================================== ========
main         Mostly MMC related behaviors                    yes
database     Describe how to reach the pulse2 mysql database yes
============ =============================================== ========

« main » section
----------------

This section is used to give directives to the MMC agent.

Available options for the "main" section:

=========== ================================================================================================================== ======== =============
Option name Description                                                                                                        Optional Default value
=========== ================================================================================================================== ======== =============
disable     Whenever use this plugin (or not)                                                                                  yes      0
location    Set the name of the location manager (by default use the only component that can do that, ie the computer backend) yes
=========== ================================================================================================================== ======== =============

« database » section
--------------------

This section defines some global options.

Available options for the "database" section:

============= ================================================================================================ ======== ===============================
Option name   Description                                                                                      Optional Default value
============= ================================================================================================ ======== ===============================
dbdriver      DB driver to use                                                                                 no       mysql
dbhost        Host which hosts the DB                                                                          no       127.0.0.1
dbport        Port on which to connect to reach the DB                                                         yes      3306 (aka "default MySQL port")
dbname        DB name                                                                                          no       pulse2
dbuser        Username to give while conencting to the DB                                                      no       mmc
dbpasswd      Password to give while connecting to the DB                                                      no       mmc
dbpoolrecycle This setting causes the pool to recycle connections after the given number of seconds has passed yes      60
dbpoolsize    The number of connections to keep open inside the connection pool                                yes      5
dbsslenable   SSL connection to the database                                                                   yes      0
dbsslca       CA certificate for SSL connection                                                                yes
dbsslcert     Public key certificate for SSL connection                                                        yes
dbsslkey      Private key certificate for SSL connection                                                       yes
============= ================================================================================================ ======== ===============================
