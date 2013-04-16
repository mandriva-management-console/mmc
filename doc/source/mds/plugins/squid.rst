============
Squid plugin
============

Installation
============

Install the packages ``python-mmc-squid`` and ``mmc-web-squid``.

LDAP directory configuration
============================

Two groups will be created automatically in the LDAP tree when the mmc-agent
starts with the squid plugin enabled:

* InternetMaster: the group with total privilegies to access any site and downloads at any time
* InternetFiltered: is the group with Internet and extensions filtred by a list of keywords and domains

The group names and their description can be changed in the configuration file of the plugin: :ref:`config-squid`.

Squid configuration
===================

Please use the provided squid configuration available in ``/usr/share/doc/mmc/contrib/squid/``.

The configuration of the ``squid.conf`` file was customized to provide LDAP authentication for the users.
Copy the configuration file to ``/etc/squid`` or ``/etc/squid3/`` (on Debian).
