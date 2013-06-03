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
:file:`/usr/share/doc/mmc/contrib/mail/mail.schema`.

Once this schema is imported, you will be able to manage mail delivery
attributes thanks to the MMC.

.. note:: To include the schema on Debian:

          ``mmc-add-schema /usr/share/doc/mmc/contrib/mail/mail.schema
          /etc/ldap/schema/``

Postfix/LDAP configuration
==========================

Example Postfix configuration files are included into the mds tarball and
packages in `/usr/share/doc/mmc/contrib/mail/postfix/`.

We provide two kinds of configuration:

- no-virtual-domain: the mail domain is fixed in the « mydestination » option
  in `main.cf` (you can't manage mail domains in the MMC - default mode)
- with-virtual-domains: mails are delivered to all mail domains created thanks
  to the MMC (you can add/remove mail domains from the MMC)

Copy all configuration files in `/etc/postfix` and replace LDAP configuration
values and domain name with your settings. In all `ldap-*.cf` files fix the
search_base option. In `main.cf` fix the domain name in `myhostname` and
`mydestination`.

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

To enable virtual domains set `vDomainSupport` to 1.
To enable virtual aliases set `vAliasesSupport` to 1.
To enable Zarafa support set `zarafa` to 1.
