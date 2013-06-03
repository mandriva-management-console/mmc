======================
SSH public keys plugin
======================

Installation
============

Install the packages ``python-mmc-sshlpk`` and ``mmc-web-sshlpk``.

LDAP directory configuration
============================

You need to import the sshlpk schema into the LDAP directory.
The schema file is provided by the ``python-mmc-sshlpk`` package in
:file:`/usr/share/doc/mmc/contrib/sshlpk/openssh-lpk.schema`.

Once this schema is imported, you will be able to manage ssh
attributes thanks to the MMC.

.. note:: On Debian, run:

          mmc-add-schema /usr/share/doc/mmc/contrib/sshlpk/openssh-lpk.schema \
          /etc/ldap/schema

MMC « sshlpk » plugin configuration
===================================

For a full description of the MMC sshlpk plugin configuration file see
:ref:`config-sshlpk`.

This plugin won't be activated if your LDAP directory does not include the
sshlpk schema.
