

==============================================
MMC inventory plugin configuration file
==============================================

This document explains the content of the MMC inventory
plugin configuration file.

Introduction
============

The « inventory » plugin is the MMC plugin in charge displaying the content
of the inventory database, and providing facilities for dynamic group creation.

The plugin configuration file is :file:`/etc/mmc/plugins/inventory.ini`.

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

Available sections in this configuration file:

====================== ===================================================================================== ========
Section name           Description                                                                           Optional
====================== ===================================================================================== ========
main                   Mostly MMC related behaviors                                                          no
inventory              Inventory related options                                                             no
computer               Computers list's display content                                                      no
expert_mode            Select which columns are only shown in expert mode                                    no
graph                  Select which columns can be graphed                                                   no
querymanager           Describe which part of the inventory is going to be queryable for the dyngroup plugin yes
provisioning_inventory Define the rules of provisioning of users from the inventory                          yes
====================== ===================================================================================== ========

« main » section
----------------

This section is used to give directives to the MMC agent.

Available options for the "main" section:

=============== ===================================================================================================================================================================================================================================== ======== =============
Option name     Description                                                                                                                                                                                                                           Optional Default value
=============== ===================================================================================================================================================================================================================================== ======== =============
disable         Whenever use this plugin (or not)                                                                                                                                                                                                     no       0
software_filter Allows to exclude softwares in the inventory software views according to their names, using a SQL expression. For example: %KB% allows to filter all softwares containing KB in their name. Multiple filters can be set using commas. yes
=============== ===================================================================================================================================================================================================================================== ======== =============

.. _inventory-section:

« inventory » section
---------------------

This section defines some global options.

Available options for the "inventory" section:

============= ================================================================================================ ======== ===============================
Option name   Description                                                                                      Optional Default value
============= ================================================================================================ ======== ===============================
dbdriver      DB driver to use                                                                                 no       mysql
dbhost        Host which hosts the DB                                                                          no       127.0.0.1
dbport        Port on which to connect to reach the DB                                                         yes      3306 (aka "default MySQL port")
dbname        DB name                                                                                          no       inventory
dbuser        Username to give while conencting to the DB                                                      no       mmc
dbpasswd      Password to give while connecting to the DB                                                      no       mmc
dbpoolrecycle This setting causes the pool to recycle connections after the given number of seconds has passed yes      60
dbpoolsize    The number of connections to keep open inside the connection pool                                yes      5
dbsslenable   SSL connection to the database                                                                   yes      0
dbsslca       CA certificate for SSL connection                                                                yes
dbsslcert     Public key certificate for SSL connection                                                        yes
dbsslkey      Private key certificate for SSL connection                                                       yes
============= ================================================================================================ ======== ===============================

« computer » section
--------------------

This section define what kind of informations will be displayed in
computers list.

Available options for the "computer" section:

=========== =========================================================== ======== ===========================================
Option name Description                                                 Optional Default value
=========== =========================================================== ======== ===========================================
content     List of additional parameters for the Computer object       yes      cn::Computer Name||displayName||Description
display     List of parameters that will be displayed in computers list yes      ""
=========== =========================================================== ======== ===========================================

For exemple :

::

    [computers]
    content = Registry::Value::regdn::Path==DisplayName||Registry::Value::srvcomment::Path==srvcomment
    display = cn::Computer Name||displayName::Description||srvcomment::Name||regdn::Display Name

« expert_mode » section
-----------------------

This section defined columns that will be only displayed when in
expert mode.

Available options for the "expert_mode" section:

============ ======================================================================= ======== =============
Option name  Description                                                             Optional Default value
============ ======================================================================= ======== =============
<Table name> List of column in this Sql table that won't be displayed in normal mode yes      ""
============ ======================================================================= ======== =============

« graph » section
-----------------

This section defined columns on which we will be able to draw graphs.

Available options for the "graph" section:

============ ======================================================== ======== =============
Option name  Description                                              Optional Default value
============ ======================================================== ======== =============
<Table name> List of column in this Sql table we will be able to draw yes      ""
============ ======================================================== ======== =============

« querymanager » section
------------------------

This section defined columns that are going to be queryable to
create groups from the dyngroup plugin.

Available options for the "querymanager" section:

=========== ============================================================================================================= ======== ======================================================================================================
Option name Description                                                                                                   Optional Default value
=========== ============================================================================================================= ======== ======================================================================================================
list        List of simple columns to query                                                                               yes      Entity/Label||Software/ProductName||Hardware/ProcessorType||Hardware/OperatingSystem||Drive/TotalSpace
double      List of double columns to query (for exemple a software AND it's version)                                     yes      Software/Products::Software/ProductName##Software/ProductVersion
halfstatic  List of columns to query with an hidden setted double columns (for exemple software KNOWING THAT version = 3) yes      Registry/Value/display name::Path##DisplayName
=========== ============================================================================================================= ======== ======================================================================================================

The separator to use between two entries is ||

List is a list of Table/Column that can be queryed as it.

Double is composed like that : NAME::Table1/Column1##Table2/Column2,
knowing that name MUST start by the mysql table name plus the char '/'.
It's generaly used for having a AND on the same entry in a table (all
machines having the software X and the version Y is not the same as all
machines having the software X at the version Y)

Halfstatic is a list of Table/Column1/name
complement::Column2##Value2, where the choices are on the column Column1
in the table Table where Column2 == Value2. The name complement is just to
display purpose.

« provisioning_inventory » section
----------------------------------

This section defines some configuration directives for user
provisioning with the inventory database. It allows to set access rights
for users to the entities of the inventory database.

To enable the inventory provisioning system, you have to set this in
:file:`/etc/mmc/plugins/base.ini`:

::

    [provisioning]
    method = inventory
    # Multiple provisining method can be used, for example:
    # method = externalldap inventory

Available options for the "provisioning_inventory" section:

================ ============================================================================================================= ======== =============
Option name      Description                                                                                                   Optional Default value
================ ============================================================================================================= ======== =============
exclude          space-separated list of login that won't be provisioned by this provisioner.                                  yes
profile_attr     LDAP user attribute that is used to get the user profile                                                      yes
profile_entity_x Space-separated list of entities assigned to the user profile "x". See the example below for more information yes
================ ============================================================================================================= ======== =============

If the entity does not exist, it is created automatically in the
database, as a child of the root entity (the root entity always
exists).

For example:

::

    [provisioning_inventory]
    exclude = root
    profile_attr = pulse2profile
    profile_entity_admin = .
    profile_entity_agent = entityA entityB
    profile_entity_tech = %pulse2entity%
    profile_entity_manager = plugin:network_to_entity
    profile_entity_none =
    profile_entity_default = entityA

In this example, the root user is never provisioned. The LDAP
attribute used to get the user profile is called "pulse2profile".

The users with the "admin" profile are linked to the root entity,
which is represented by the dot character. These users have access the
root entity and all its sub-entities.

The users with the "agent" profile are linked to both entities
"entityA" and "entityB" character. These users have access to entities
"entityA" and "entityB", and all their sub-entities.

The users with the "tech" profile are linked to entities defined in
the "pulse2entity" LDAP attribute values of these users.

The users with the "manager" profile are linked to entities computed
by the "network_to_entity" provisioning plugin. See the next sub-section
for more informations.

The users with the "none" profile are linked to no entity.

The users with no profile (the pulse2profile field is empty or don't
exist) or with none of the profiles described in the configuration
file are set to the "default" profile (be carefull, default is now a
keyword).

« network_to_entity » plugin
----------------------------

This plugin for the inventory provisioning system allows to link
users to entities according to their IP when connecting to the MMC web
interface.

The IP address of the user is determined by the Apache server
running the MMC web interface thanks to the remote address of the HTTP
connection. Then this IP address is forwarded to the MMC agent when
authenticating and provisioning the user.

The IP address to entities mapping is done thanks to a rules file,
similar to the one used by the inventory server to affect a computer
inventory to an entity.

The rules file must be called
:file:`/etc/mmc/plugins/provisioning-inventory`. There is
now to specify an alternate rules file.

Here is an example of rules file:

::

    entityA              ip      match   ^192\\.168\\.1\\..*$
    entityB,entityC      ip      match   ^192\\.168\\.0\\.19$
    .                    ip      match   ^.*$

Each line of the rules file is processing starting from the top of
the file, until one rule is valid. The user IP address is matched
against a regular expression. If no rule match, the user is linked to no
entity.

The first line links users connecting from the 192.168.1.0/24 to
the entity called "entityA".

The second line links users connecting from the IP address
192.168.0.19 to the entities called "entityB" and "entityC".

The third line is a kind of catch-all rule.
