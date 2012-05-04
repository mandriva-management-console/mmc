.. highlight:: none
.. _config-web:

==========================
MMC web configuration file
==========================

This document explains the content of the MMC web configuration file

Introduction
############

The MMC web interface communicates with MMC agents to manage LDAP directories,
services and ressources.

Its configuration file is :file:`/etc/mmc/mmc.ini`. This file must be readable
only by the Apache web server, as it contains the login and password required
to connect to MMC agents.

Like all MMC related configuration files, its file format is INI style.
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

:file:`/etc/mmc/mmc.ini` available sections:

============ ========================================== ========
Section name Description                                Optional
============ ========================================== ========
global       MMC web interface global options           no
debug        debug options                              no
logintitle   Login page title                           yes
server_x     MMC agent XMLRPC server connection options no
============ ========================================== ========

Section « global »
##################

Available options for the « global » section:

============= ====================================================================================== ======== =============
Option name   Description                                                                            Optional Default value
============= ====================================================================================== ======== =============
backend       Which RPC backend to use. Only xmlrpc backend is available.                            no
login         credential to authenticate with the MMC agent                                          no
password      credential to authenticate with the MMC agent                                          no
root          Root URL where the MMC web pages are installed                                         no
rootfsmodules Filesystem path where the MMC web modules are installed                                no
maxperpage    Number of items (users, groups, ...) in displayed lists on the web interface           no
community     It's a yes or no flag, it set the fact the installed version is a community one or not yes      yes
============= ====================================================================================== ======== =============

Section « debug »
#################

For debugging purpose only. The XML-RPC calls and results will be displayed on
the MMC web interface.

=========== ========================================================== ======== =============
Option name Description                                                Optional Default value
=========== ========================================================== ======== =============
level       Wanted debug level. 0 to disable debug. 1 to enable debug. No
=========== ========================================================== ======== =============

Section « logintitle »
######################

This section allows to customize the title of the login box of the MMC web
interface login page. By default, there is no title.

A title can be defined for each supported locales, like this:

::

    localename = Title_for_this_locale

The title string must be encoded in UTF-8.

For example:

::

    [logintitle]
    ; Default page title for English and non-translated languages
    C = Welcome
    ; French title
    fr_FR = Bienvenue
    ; Spanish title
    es_ES = Bienvenido

Section « server_x »
####################

You can set multiple sections called « server_01 », « server_02 » ...
to specify a list of MMC agents to connect to.

On the MMC login web page, all the specified MMC agents will be displayed,
and you will be able to select the one you want to be connected to.

Available options for the « server_x » sections:

=========== =================================================================================================================================================================================================================================== ==================== =============
Option name Description                                                                                                                                                                                                                         Optional             Default value
=========== =================================================================================================================================================================================================================================== ==================== =============
description label to display on the MMC login web page                                                                                                                                                                                          no
url         How to connect the XMLRPC server of this MMC agent                                                                                                                                                                                  no
timeout     Timeout in seconds for all socket I/O operations. Beware that timeout on a SSL socket only works with PHP >= 5.2.1.                                                                                                                 yes                  300
verifypeer  If verifypeer is enabled, the TLS protocol is used, and the XML-RPC server must provide a valid certificate.                                                                                                                        yes                  0
localcert   If verifypeer = 1, path to the file (PEM format) containing the private key and the public certificate used to authenticate with the MMC agent                                                                                      no if verifypeer = 1
cacert      Path to the file (PEM format) containing the public certificate of the Certificate Authority that produced the certificate defined by the localcert option. The certificate provided by the MMC agent will be validated by this CA. no if verifypeer = 1
=========== =================================================================================================================================================================================================================================== ==================== =============

For example, to define a local MMC agent:

::

    [server_01]
    description = Local MMC agent
    url = http://127.0.0.1:7080

To use SSL between the web interface and the MMC agent (SSL must be enabled on
the MMC agent):

::

    [server_01]
    description = Local MMC agent
    url = https://127.0.0.1:7080

To use TLS with certificate check:

::

    [server_01]
    description = MMC agent
    url = https://10.0.0.1:7080
    verifypeer = 1
    cacert = /etc/mmc/certs/demoCA/cacert.pem
    localcert = /etc/mmc/certs/client.pem
