.. highlight:: none

============
SAMBA plugin
============

This document explains how to install the SAMBA plugin for MMC and its
related configuration.

Installation
============

Install the packages ``python-mmc-samba, mmc-web-samba`` and ``samba``.

SAMBA configuration for MMC
===========================

This section explains how to configure SAMBA with a LDAP directory so that it
works with the MMC. Basically, you need to do a classic SAMBA/LDAP setup,
SAMBA running as a PDC.

.. note:: Configuration files

   A :file:`slapd.conf` for OpenLDAP and a :file:`smb.conf` for SAMBA can
   be found in `/usr/share/doc/mmc/contrib/samba`.

   Please use these files as templates for your own configuration.

If you aren't familiar with SAMBA/LDAP installation, read the
`SAMBA LDAP HOWTO <http://download.gna.org/smbldap-tools/docs/samba-ldap-howto/index.html>`_.
SAMBA LDAP setup is not easy.

LDAP directory configuration
----------------------------

You need to import the SAMBA schema into the LDAP directory.
The schema file is provided by the ``python-mmc-samba`` package in
:file:`/usr/share/doc/mmc/contrib/samba/samba.schema`. But you can
also use the schema provided by the SAMBA project.

SAMBA configuration
-------------------

Stop samba before modifying its configuration:

::

    # /etc/init.d/samba stop
    Or according to your distribution:
    # /etc/init.d/smb stop

In :file:`/etc/samba/smb.conf`, you need to modify the « workgroup »,
« ldap admin dn » and « ldap suffix » to suit your configuration.

SAMBA also needs the credentials of the LDAP manager to write into the LDAP:

::

    # smbpasswd -w secret
    Setting stored password for "cn=admin,dc=mandriva,dc=com" in secrets.tdb

Now, SAMBA needs to create the SID for your workgroup:

::

    # net getlocalsid MANDRIVA
    SID for domain MANDRIVA is: S-1-5-21-128599351-419866736-2079179792

Use slapcat to check that the SID has really been recorded into the LDAP. You should find an entry like this:

::

    # slapcat | grep sambaDomainName
    dn: sambaDomainName=MANDRIVA,dc=mandriva,dc=com
    ...

Now you can start SAMBA:

::

    # /etc/init.d/samba start

Populating the LDAP directory for SAMBA
---------------------------------------

The LDAP directory needs to be populated so that SAMBA can use it. We use the
:command:`smbldap-populate` command from the `smbldap-tools` package. This
command populates the LDAP with the OUs (Organizational Unit), users and groups
needed by SAMBA.

.. note:: On Debian do first:

    `cp /usr/share/doc/smbldap-tools/examples/smbldap_bind.conf
    /etc/smbldap-tools/`
    `cp /usr/share/doc/smbldap-tools/examples/smbldap.conf.gz
    /etc/smbldap-tools/`
    `gunzip /etc/smbldap-tools/smbldap.conf.gz`

Now the smbldap-tools conf file need to be edited. Put this in
:file:`/etc/smbldap-tools/smbldap_bind.conf`:

::

    slaveDN="cn=admin,dc=mandriva,dc=com"
    slavePw="secret"
    masterDN="cn=admin,dc=mandriva,dc=com"
    masterPw="secret"

:file:`smbldap_bind.conf` defines how to connect to and write to the LDAP server.

Then edit :file:`smbldap.conf` and set those fields:

::

    SID="S-1-5-21-128599351-419866736-2079179792"
    sambaDomain="MANDRIVA"
    ldapTLS="0"
    suffix="dc=mandriva,dc=com"
    sambaUnixIdPooldn="sambaDomainName=MANDRIVA,${suffix}"
    #defaultMaxPasswordAge="45"
    userSmbHome=""
    userProfile=""
    userHomeDrive=""

Now the directory can be populated. Type:

::

    # smbldap-populate -m 512 -a administrator

A user called « administrator » will be created, and a prompt will ask you to give its password.
Thanks to the « -m 512 » option, this user will belong to the « Domain Admins » group.

User password expiration
------------------------

By default, the maximum password age of a SAMBA user is 42 days. Then the user will need to change his/her password.

If you don't want password to expire, type:

::

    # pdbedit -P "maximum password age" -C 0

If you want to check your current password expiration policy:

::

    # pdbedit -P "maximum password age"

Giving privileges to SAMBA users and groups
-------------------------------------------

If « enable privileges = yes » is set on your :file:`smb.conf`, you can give privileges to SAMBA users and groups.

For example, to give to "Domain Admins" users the right to join a machine to the domain:

::

    # net -U administrator rpc rights grant 'DOMAIN\Domain Admins' SeMachineAccountPrivilege
    Password:
    Successfully granted rights.

Notice that you must replace « DOMAIN » by your SAMBA domain name in the command line.

.. note:: Users that can give privileges

   Only users that belong to the "Domain Admins" group can use the :command:`net rpc rights grant` command to assign privileges.

About SE Linux
==============

The default SE Linux configuration may not allow SAMBA to launch the script
defined in "add machine script", and so you won't be able to join a machine
to the SAMBA domain.

MMC « base » plugin configuration
=================================

By default, you want your new user to belong to the « Domain Users » group.

You just need to set the « defaultUserGroup » option to « Domain Users » in
:file:`/etc/mmc/plugins/base.ini`.

MMC « SAMBA » plugin configuration
==================================

For a full description of the MMC SAMBA plugin configuration file see
:ref:`config-samba`.

You shouldn't need to edit the configuration file (:file:`/etc/mmc/plugins/samba.ini`).
This plugin won't be activated if your LDAP directory does not include the
SAMBA schema, and well-known RIDs.

ACLs must be enabled on your filesystem. The SAMBA plugin needs them to set the
ACLs when creating shares, and SAMBA will be able to map NTFS ACLs to the POSIX
ACLs.

If you use XFS, ACLs are enabled by default. For ext3, you need to enable ACLs
in :file:`/etc/fstab`.
