.. highlight:: none

=================
MMC configuration
=================

Web interface configuration
===========================

For a full documentation of the :file:`/etc/mmc/mmc.ini` file see
:ref:`config-web`.

What you may change in this file is:

- « login » and « password »: these are the credentials to connect to the MMC
  agents on your network (the same credentials as in
  :file:`/etc/mmc/agent/config.ini`)

- « url » option of the \[server_x]: the URL to connect to the MMC agent.

To connect to the MMC web interface using an URL like http://IP/mmc, we add
an alias to Apache 2:

::

    # cp /etc/mmc/apache/mmc.conf /etc/httpd/conf.d/mmc.conf
    
or on Debian:

::

    # cp /etc/mmc/apache/mmc.conf /etc/apache2/conf.d/mmc.conf

Then don't forget to reload the Apache service.

Now you should be able to see the MMC login screen at this URL: http://IP/mmc

.. note::

    **PHP configuration notes**

    The directive magic_quotes_gpc must be enabled in Apache PHP configuration,
    either in the global PHP configuration file, either in the :file:`mmc.conf`
    file with this line:

    ::

        php_flag magic_quotes_gpc on

    The MMC web interface is not compatible with php-eaccelerator. Please
    uninstall it else you won't be able to connect to the MMC.

MMC agent configuration
=======================

For a full description of the MMC agent configuration file see :ref:`config-agent`.

With the default configuration file we provide (:file:`/etc/mmc/agent/config.ini`),
the MMC agent listen locally to incoming XMLRPC over HTTPS connections on port
7080.

MMC « base » plugin configuration
=================================

For a full description of the MMC base plugin configuration file see
:ref:`config-base`.

The main part of the configuration (:file:`/etc/mmc/plugins/base.ini`) is to
set the LDAP server to connect to, and the credentials to use to write into
the LDAP. Check the following options:

- ``ldapurl`` : usually ``ldap://127.0.0.1:389``
- ``baseDN`` : the rootdn of your LDAP directory
- ``baseUsersDN`` : DN to the ``ou`` containing LDAP users (eg: ``ou=People, %(baseDN)``)
- ``baseGroupsDN`` : DN to the ``ou`` containing LDAP groups (eg: ``ou=Group, %(baseDN)``)
- ``rootName`` : DN of the LDAP administrator
- ``password`` : password of the LDAP administrator

The ``defaultUserGroup`` option must be set to an existing group in the LDAP.
You will have to create it using the MMC web interface if this group does not
exist.

You need to create the directory specified in the ``destpath`` option.

About firewalling
=================

The MMC web interface communicate with the MMC agent using the TCP port 7080
on localhost (default configuration). Please check that your firewall 
configuration doesn't block this port.

About SE Linux
==============

The MMC web interface opens a socket to communicate with the MMC agent using
XML-RPC.

On SE Linux enabled systems (e.g. Fedora Core 6), by default Apache can't open
socket per policy. So you need to fix or disable your SE linux configuration
to make it works.
