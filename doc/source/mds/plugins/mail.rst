===========
Mail plugin
===========

Installation
============

Install the packages ``python-mmc-mail`` and ``mmc-web-mail``.

LDAP directory configuration
============================

You need to import our mail schema into the LDAP directory.
The schema file is provided by the ``python-mmc-base`` package in
:file:`/usr/share/doc/python-mmc-base/contrib/ldap/mail.schema`.

Once this schema is imported, you will be able to manage mail delivery
attributes thanks to the MMC.

Postfix/LDAP configuration
==========================

Example Postfix configuration files are included into the mds tarball or in
our repository : https://github.com/mandriva-management-console/mmc/tree/master/mds/agent/contrib/postfix

We provide two kinds of configuration:

- no-virtual-domain: the mail domain is fixed in the « mydestination » option
  in main.cf
- with-virtual-domains: mails are delivered to all mail domains created thanks
  to the MMC

NSS LDAP configuration
======================

NSS LDAP configuration is needed to deliver mails with the right UIDs/GIDs.

See :ref:`nss-ldap`.

MMC « mail » plugin configuration
=================================

For a full description of the MMC mail plugin configuration file see
:ref:`config-mail`.

This plugin won't be activated if your LDAP directory does not include a
special mail schema.
