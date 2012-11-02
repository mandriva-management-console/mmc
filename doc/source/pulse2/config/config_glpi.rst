

==================================
MMC glpi plugin configuration file
==================================

This document explains the content of the MMC glpi plugin configuration file.

Introduction
============

The « glpi » plugin is the MMC plugin in charge of the glpi machine backend,
it should only be used when invnetory is not used.

The plugin configuration file is :file:`/etc/mmc/plugins/glpi.ini`.

Like all MMC related configuration file, its file format is INI style. The
file is made of sections, each one starting with a « [sectionname] » header.
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

For now four sections are available in this configuration file:

=================== ============================================================================================== ========
Section name        Description                                                                                    Optional
=================== ============================================================================================== ========
main                Mostly MMC related behaviors                                                                   no
querymanager        Describe how it react as a potential queriable plugin                                          yes
authentication_glpi Give the way to authenticate on glpi                                                           yes
provisioning_glpi   Give the permissions that are going to be associated with users (based on permissions in glpi) yes
=================== ============================================================================================== ========

« main » section
----------------

This section is used to give directives to the MMC agent.

Available options for the "main" section:

=============== ================================================================= ======== ===============================
Option name     Description                                                       Optional Default value
=============== ================================================================= ======== ===============================
disable         Whenever use this plugin (or not)                                 yes      0
dbdriver        DB driver to use                                                  no       mysql
dbhost          Host which hosts the DB                                           no       127.0.0.1
dbport          Port on which to connect to reach the DB                          no       3306 (aka "default MySQL port")
dbname          DB name                                                           no       glpi
dbuser          Username to give while conencting to the DB                       no       mmc
dbpasswd        Password to give while connecting to the DB                       no       mmc
dbdebug         Whenever log DB related exchanges                                 yes      ERROR
dbpoolrecycle   DB connection time-to-live                                        yes      60 (seconds)
dbpoolsize      The number of connections to keep open inside the connection pool yes      5
dbsslenable     SSL connection to the database                                    yes      0
dbsslca         CA certificate for SSL connection                                 yes
dbsslcert       Public key certificate for SSL connection                         yes
dbsslkey        Private key certificate for SSL connection                        yes
localisation    Tells if the glpi entities are going to be used in pulse2         yes
active_profiles Tells which profiles are going to be used                         yes
filter_on       add a filter on the glpi_computers table when retrieving machines yes      state==3
=============== ================================================================= ======== ===============================

« querymanager » section
------------------------

This section define how this plugin react as a potential queriable plugin.

Available options for the "querymanager" section:

=========== ===================================================== ======== =============
Option name Description                                           Optional Default value
=========== ===================================================== ======== =============
activate    If queries on glpi inventory criterions are possible. yes      True
=========== ===================================================== ======== =============

« authentication_glpi » section
-------------------------------

This section define a way to authenticate thru glpi.

Available options for the "authentication_glpi" section:

=========== =============================================================================================================================== ======== =============
Option name Description                                                                                                                     Optional Default value
=========== =============================================================================================================================== ======== =============
baseurl     glpi login page url yes      http://glpi-server/glpi/
doauth      Before provisioning, should we perform a GLPI authentication to create or update the user's informations in the GLPI database ? yes      True
=========== =============================================================================================================================== ======== =============

« provisioning_glpi » section
-----------------------------

This section define a way to do the user provisioning from glpi.

Available options for the "provisioning_glpi" section:

==================== =============================================================================================================================== ======== ==========================
Option name          Description                                                                                                                     Optional Default value
==================== =============================================================================================================================== ======== ==========================
exclude              users that are never going to be provisioned                                                                                    yes      root
profile_acl_profileX MMC web interface ACLs definition according to the user GLPI profile                                                            yes      :##:base#main#default
profile_order        If the user belong to more than one profile, the first profile of this list will be used                                        yes      profile1 profile2 profile3
==================== =============================================================================================================================== ======== ==========================
