=====================================
MMC imaging plugin configuration file
=====================================

This document explains the content of the MMC imaging
plugin configuration file.

Introduction
============

The « imaging » plugin is the MMC agent plugin that allows
to manage all imaging related data.

The plugin configuration file is
:file:`/etc/mmc/plugins/imaging.ini`.

Like all MMC related configuration file, its file format is INI
style. The file is made of sections, each one starting with a
« [sectionname] » header. In each section options can be defined like
this: « option = value ».

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

For now three sections are available in this configuration
file:

============ ============================================= ========
Section name Description                                   Optional
============ ============================================= ========
main         Mostly MMC related behaviors                  no
database     Imaging database related options              no
imaging      Default values for the MMC web imaging plugin no
============ ============================================= ========

« main » section
----------------

This section is used to give directives to the MMC agent.

Available options for the "main" section:

=========== ================================= ======== =============
Option name Description                       Optional Default value
=========== ================================= ======== =============
disable     Whenever use this plugin (or not) no       0
=========== ================================= ======== =============

« database » section
--------------------

This section defines how to connect to the imaging database.

Available options for the "database" section:

============= ================================================================================================ ======== ===============================
Option name   Description                                                                                      Optional Default value
============= ================================================================================================ ======== ===============================
dbdriver      DB driver to use                                                                                 no       mysql
dbhost        Host which hosts the DB                                                                          no       127.0.0.1
dbport        Port on which to connect to reach the DB                                                         yes      3306 (aka "default MySQL port")
dbname        DB name                                                                                          no       imaging
dbuser        Username to use to connect to the DB                                                             no       mmc
dbpasswd      Password to use to connect to the DB                                                             no       mmc
dbpoolrecycle This setting causes the pool to recycle connections after the given number of seconds has passed yes      60
dbpoolsize    The number of connections to keep open inside the connection pool                                yes      5
dbsslenable   SSL connection to the database                                                                   yes      0
dbsslca       CA certificate for SSL connection                                                                yes
dbsslcert     Public key certificate for SSL connection                                                        yes
dbsslkey      Private key certificate for SSL connection                                                       yes
============= ================================================================================================ ======== ===============================

« imaging » section
-------------------

This section defines default values to use in the MMC web
interface in imaging related page.

Available options for the "imaging" section:

============================== ====================================================================== ======== =================================================================
Option name                    Description                                                            Optional Default value
============================== ====================================================================== ======== =================================================================
web_def_date_fmt               Date format to use (see http://www.php.net/date for more informations) yes      "%Y-%m-%d %H:%M:%S"
web_def_default_protocol       Network protocol to use for image restoration                          yes      nfs
web_def_default_menu_name      Boot menu name                                                         yes      Menu
web_def_default_timeout        Boot menu timeout in seconds                                           yes      60
web_def_default_background_uri Boot menu background                                                   yes
web_def_default_message        Boot menu message                                                      yes      Warning ! Your PC is being backed up or restored. Do not reboot !
web_def_kernel_parameters      Kernel parameters                                                      yes      quiet
web_def_image_parameters       Image parameters                                                       yes
============================== ====================================================================== ======== =================================================================
