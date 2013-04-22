============
Installation
============

.. toctree::
    :maxdepth: 2
    :hidden:

    development
    dhcp
    distribution
    imaging
    nfs
    pulse-setup
    release
    schema
    tftp


Pulse2 quick install guide.

In order to install Pulse2 and it's plugins you first need to install and
configure :doc:`MMC </mmc/intro>`.

Pre-requisites
==============

The following tools are needed in order for Pulse2 to install and run
properly:

* mmc-core framework
* python >= 2.5, with the following modules:
  * sqlalchemy
  * mysqldb
* OpenSSH client
* iputils (ping)
* perl, with the following modules:
  * syslog
* gettext
* NFS server (for imaging)
* 7z (for win32 client generation)
* NSIS (for building win32 agent pack, can be disabled)

Furthermore, the following services must be accessible, either on the local
machine or through the network:

* MySQL
* DHCP server (for imaging purpose)

If you build Pulse2 from scm repository, you will also need the following tools
(not needed if you use the .tar.gz archive):

* autoconf
* automake
* xsltproc
* docbook xsl

The MMC web interface is written in PHP4. Basically, you just need to install
an Apache 2 server with PHP5 support. The XML-RPC module of PHP is needed too.

Packages naming conventions
===========================

Here are the packages naming conventions:

* pulse2-``SERVER``: the Pulse2 servers 
* python-mmc-``PLUGIN``: MMC agent plugin
* mmc-web-``PLUGIN``: web interface plugin

Where ``PLUGIN`` can be one of dyngroup, glpi, imaging, inventory, msc, pkgs
and pulse2.

Where ``SERVER`` can be one of common, imaging-server, inventory-server,
launcher, package-server and scheduler.

Sample configuration files
==========================

All related sample configuration files are available or on our repository for
Pulse2 plugins_ and servers_.

.. _plugins: https://github.com/wiliamsouza/mmc/tree/master/pulse2/services/conf/plugins
.. _servers: https://github.com/wiliamsouza/mmc/tree/master/pulse2/services/conf/pulse2

Installation options
====================

There are three easy options to install:

* Install an :doc:`official release from source tarball<release>`.

* Install a version provided by your :doc:`operating system distribution 
  <distribution>`.

* Install the latest :doc:`development <development>` version.
