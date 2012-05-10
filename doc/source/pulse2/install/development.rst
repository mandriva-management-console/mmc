==================================
Installing the development version
==================================

In order to install Pulse2 and it's plugins you first need to install and
configure :doc:`MMC </mmc/intro>`.

This how to will guide you through the installation and configuration of Pulse2
development environment

Installation form source code
=============================

Pre-requisites
--------------

Debian::

    # apt-get install git-core build-essential autogen autoconf libtool gettext
    python-sqlalchemy python-mysqldb python-ldap python-openssl
    python-twisted-web nsis xsltproc docbook-xsl 

Centos::

    # yum install git-core

Mandriva::

    # urpmi git-core

Get the source code
-------------------

The development source code is managed in github https://github.com/mandriva-management-console/mmc.

Clone the github repository::

    $ git clone git://github.com/mandriva-management-console/mmc.git

To compile and install all modules run::

    $ $ cd pulse2/
    $ ./autogen.sh
    $ ./configure --prefix=/usr --sysconfdir=/etc --localstatedir=/var
    $ make
    # make install

You can update by running the following command::

    $ git pull origin master

To keep your configuration files intact you may change the configure line to::

    $ ./configure --prefix=/usr --sysconfdir=/etc --localstatedir=/var --disable-conf 

The option ``--disable-conf`` will disable configuration files installation.

OpenLDAP
=========

Debian::

    # apt-get install slapd ldap-utils

Debconf will ask only ldap root password by default to more granular configuration use::

    # dpkg-reconfigure slapd

Using dpkg-reconfigure debconf will ask you for:

* Omit OpenLDAP server configuration?: Choose <No>.
* DNS domain name: Enter you domain name.
* Organization name: Enter organization name.
* Admin password: Enter a password and confirm in next screen.
* Database backend to use: choose HDB.
* Do you want the database to be removed when slapd is purged: Choose <No>.
* Allow LDAPv2 protocol: Choose <NO>.

Centos::

    # yum install

Mandriva::

    # urpmi

.. include:: /pulse2/install/schema.rst


MySQL
=====

Debian::

    # apt-get install mysql-server

Debconf will ask mysql root password.

Centos::

    # yum install

Mandriva::

    # urpmi

Apache HTTP server
==================

Debian::

   # apt-get install apache2 php5 php5-gd php5-xmlrpc

Centos::

    # yum install

Mandriva::

    # urpmi

Configuring apache2 and php
---------------------------

Enable mmc web site::

    # ln -s /etc/mmc/apache/mmc.conf /etc/apache2/sites-enabled/mmc.conf

Restart apache2::

    # /etc/init.d/apache2 restart

.. include:: /pulse2/install/pulse-setup.rst

DHCP Install
============

Debian::

    # apt-get install isc-dhcp-server


.. include:: /pulse2/install/dhcp.rst

.. include:: /pulse2/install/imaging.rst

.. include:: /pulse2/install/nfs.rst

.. include:: /pulse2/install/tftp.rst

