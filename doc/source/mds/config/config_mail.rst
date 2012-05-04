.. highlight:: none
.. _config-mail:

==================================
MMC mail plugin configuration file
==================================

This document explains the content of the MMC mail plugin configuration file.

Introduction
############

The « mail » plugin allows the MMC to add/remove mail delivery
management attributes to users and groups, and mail virtual
domains, etc. It uses the « base » plugin for all its related
LDAP operations.

The plugin configuration file is :file:`/etc/mmc/plugins/mail.ini`.

Like all MMC related configuration file, its file format is INI style.
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

Here are all the mail.ini available sections:

============ ================================================================================================= ========
Section name Description                                                                                       Optional
============ ================================================================================================= ========
main         global mail plugin configuration                                                                  no
userdefault  Attributes and Objectclass values that are added or deleted when adding mail attributes to a user yes
mapping      Map mail.schema attributes to other existing LDAP attributes                                      yes
============ ================================================================================================= ========

Section « main »
################

This sections defines the global options of the mail plugin

Available options for the « main » section:

=============== ============================================================= ================================== ==========================
Option name     Description                                                   Optional                           Default value
=============== ============================================================= ================================== ==========================
disable         Is this plugin disabled ?                                     Yes                                1
vDomainSupport  Is virtual domain management enabled ?                        Yes                                0
vDomainDN       Organizational Unit where virtual mail domains will be stored Yes if vDomainSupport is disabled  ou=mailDomains, %(baseDN)s
vAliasesSupport Is virtual aliases management enabled ?                       Yes                                0
vAliasesDN      Organizational Unit where virtual aliases will be stored      Yes if vAliasesSupport is disabled ou=mailAliases, %(baseDN)s
zarafa          Is Zarafa LDAP fields support enables ?                       Yes                                0
=============== ============================================================= ================================== ==========================

Section « userdefault »
#######################

When adding the mail attributes to a user, you may want to change the value of
the attributes that are added. Please look at the :ref:`config-base` for a look
at how this section works.

The mailbox field of this section is very important to set because it
determines the paths where the mails are delivered to users.

If the mails are delivered by Postfix, use this:

::

    [userdefault]
    mailbox = %homeDirectory%/Maildir/

If you use Dovecot as the delivery agent:

::

    [userdefault]
    mailbox = maildir:%homeDirectory%/Maildir/

Zarafa support
##############

The zarafa.schema file must be included into the LDAP directory.

If Zarafa support is enabled, the "zarafa-user" object class
will be automatically added to users if the administrator gives
them mail access thanks to the MMC web interface.

The following fields are also available:

- Administrator of Zarafa (zarafaAdmin LDAP field)
- Shared store (zarafaSharedStoreOnly LDAP field)
- Zarafa account (zarafaAccount)
- Zarafa send as user list (zarafaSendAsPrivilege)

When you edit a group, you will also be able to set the "zarafa-group" object
class to it.

Section « mapping »
###################

When using an existing LDAP your mail attributes may not have the same name
than the attributes of our mail.schema. The MDS mail plugin support attribute
mapping so that you can use your LDAP without modification.

The following attributes can be mapped to other values: mailalias, maildrop,
mailenable, mailbox, mailuserquota, mailhost

If your are using the zarafaAliases to store users aliases write:

::

    [mapping]
    mailalias = zarafaAliases
