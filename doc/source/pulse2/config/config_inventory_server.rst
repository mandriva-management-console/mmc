

===========================================
Pulse 2 Inventory server configuration file
===========================================

This document explains the content of the configuration file of the inventory
server service from Pulse 2.

Introduction
============

The « inventory server » service is the Pulse 2 daemon in charge importing
inventory sent from ocs inventory agents.

The service configuration file is
:file:`/etc/mmc/pulse2/inventory-server/inventory-server.ini`.

Like all Pulse 2 related configuration file, its file format is INI style.
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

For now four sections are available in this configuration file. The section
describing the option can be duplicated if you need to pass more than one kind
of option to the OCS inventory agent.

============ ================================================ ========
Section name Description                                      Optional
============ ================================================ ========
main         Common inventory server configuration directives *no*
database     Database connection parameters                   *no*
daemon       Inventory server daemon related behaviors        *no*
option_XXX   Inventory agent option XXX                       yes
============ ================================================ ========

All the other sections (loggers, handlers, ...) are related to Python language
logging framework. See http://docs.python.org/lib/logging-config-fileformat.html.

« main » section
----------------

This section is used to configure the inventory server service.

Available options for the "main" section:

=================== =============================================================================================================================================== ======== ======= =================================================
Option name         Description                                                                                                                                     Optional Type    Default value
=================== =============================================================================================================================================== ======== ======= =================================================
host                The hostname or ip address where the inventory.                                                                                                 yes      string  localhost
port                The port on which the inventory listen.                                                                                                         yes      int     9999
ocsmapping          The mapping file betwen ocs inventory agent xml output and the database schema                                                                  yes      path    /etc/mmc/pulse2/inventory-server/OcsNGMap.xml
enablessl           SSL mode support                                                                                                                                yes      boolean False
verifypeer          use SSL certificates                                                                                                                            yes      boolean False
cacert              path to the certificate file describing the certificate authority of the SSL server                                                             yes      path    /etc/mmc/pulse2/inventory-server/keys/cacert.pem
localcert           path to the SSL server private certificate                                                                                                      yes      path    /etc/mmc/pulse2/inventory-server/keys/privkey.pem
hostname            allow hostname in incoming inventory to be overridden by an other information from the inventory, for exemple Registry/Value|Path:DisplayName . yes      string  Hardware/Name
default_entity      Default entity where computers are stored                                                                                                       yes      string  "." (root entity)
entities_rules_file Rules file defining computer to entity mappings. See specific section to learn how it works.                                                    yes      path    "" (no mapping)
=================== =============================================================================================================================================== ======== ======= =================================================

The hostname option is a representation of the path in the inventory XML.

« database » section
--------------------

This section is documented into the MSC inventory plugin configuration
documentation (see section :ref:`inventory-section`).

« daemon » section
------------------

This section sets the inventory service run-time options and privileges.

Available options for the "daemon" section:

=========== ================================================================================================== ======== ============================== ===================================
Option name Description                                                                                        Optional Type                           Default value
=========== ================================================================================================== ======== ============================== ===================================
pidfile     The inventory service store its PID in the given file.                                             yes      path                           /var/run/pulse2-inventoryserver.pid
user        The inventory service runs as this specified user.                                                 yes      string                         root
group       The inventory service runs as this specified group.                                                yes      string (can be base64 encoded) root
umask       The inventory service umask defines the right of the new files it creates (log files for example). yes      octal                          0077
=========== ================================================================================================== ======== ============================== ===================================

« option_XXX » section
----------------------

This section define options that has to be given to the ocs inventory agent.

At the moment the only option which return will be inserted in the database
is REGISTRY.

Each PARAM_YYY is for an XML tag PARAM in the inventory request. It is made
of two values separated by ##. The first value is PARAM XML attributes, the
second one is the content of the PARAM XML tag. The attributes are a list of
couple attribute name, attribute value, the name and the value are separated
by ::, each couple is separated by \||.

Available options for the ``option_XXX`` section:

=========== ================== ======== ====== =============
Option name Description        Optional Type   Default value
=========== ================== ======== ====== =============
NAME        The option name.   *no*     string
PARAM_YYY   The option params. yes      string
=========== ================== ======== ====== =============

For example :

::

    [option_01]
    NAME = REGISTRY
    PARAM_01 = NAME::srvcomment||REGKEY::SYSTEM\\CurrentControlSet\\Services\\lanmanserver\\parameters||REGTREE::2##srvcomment
    PARAM_02 = NAME::DisplayName||REGKEY::SYSTEM\\CurrentControlSet\\Services\\lanmanserver||REGTREE::2##DisplayName

Rules file for computer to entity mapping
#########################################

This file defines a set of rules to assign a computer to an entity according
to its inventory content.

Each line of the rules file is processing starting from the top of the file,
until one rule is valid. When a rule matches, the processing stop, and the
computer is linked to the entity. If no rule match, the user is linked to no
entity.

If no rule matches, the computer is assigned to the default entity. If the
entity does not exist, it is created automatically in the database, as a child
of the root entity (the root entity always exists).

This file is made of four or more columns. Each column is separated by space or
tab characters.

- The first column is the entity that will be assigned to the computer if the
  rule is valid. The root entity is specified by the dot character.

- The second column is the inventory component value that will be tested by the
  rule. This component is made of the name of an inventory table, the "/"
  character, and a column of this table. For example: Network/IP,
  Bios/ChipVendor, ... The :file:`OcsNGMap.xml` file can also be used to get
  the available inventory component value.

- The third column is the operator of the rules. For the moment, only the
  "match" operator is available. The "match" operator allows to test the
  inventory component value with a regexp.

- The fourth column is a value that will be used by the operator. For the
  "match" operator, the value must be a regular expression.

For example:

::

    .               Network/IP      match   ^192\\.168\\.0\\..*$
    "entity A"      Network/IP      match   ^172\\.16\\..*$
    entityB         Network/IP      match   ^10\\..*$          and        Hardware/OperatingSystem     match       ^Linux$

The first line links all computers with an IP address starting with 192.168.0.
(network 192.168.0.0/24) to the inventory root entity.

The second line links all computers with an IP address starting with 172.16.
(network 172.16.0.0/24) to the entity called "entity A". Entity name can be
written between double-quotes if they contains space characters in their name.

The third line links all computers with an IP address starting with "10."
(network 10.0.0.0/8) and with the "Linux" OS to the entity called entityB.
