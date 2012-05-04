======================
Password policy plugin
======================

.. note:: The configuration of the ppolicy plugin is optionnal

Installation
============

Install the packages ``python-mmc-ppolicy`` and ``mmc-web-ppolicy``.

OpenLDAP configuration for password policies
============================================

On Mandriva, if you used the mandriva-dit setup scripts, the
password policy configuration is already done. If not, here are
some instructions:

You must add this to your OpenLDAP :file:`slapd.conf` configuration file:

::

    # Include password policy schema
    include /path/to/openldap/schema/ppolicy.schema
    ...
    # Load the ppolicy module
    moduleload  ppolicy
    ...
    # Add the overlay ppolicy to your OpenLDAP database
    database  bdb
    suffix    "dc=mandriva,dc=com"
    ...
    overlay ppolicy
    ppolicy_default "cn=default,ou=Password Policies,dc=mandriva,dc=com"

Beware that the ppolicy_default value must match the options "ppolicyDN" and
"ppolicyDefault" you set into the :file:`ppolicy.ini` file.

MMC « ppolicy » plugin configuration
====================================

For a full description of the MMC ppolicy plugin configuration file see
:ref:`config-ppolicy`.

The only thing you'll have to modify in the configuration file
is the "ppolicyDN" option if needed. The OU parent must be an existing
DN. If the OU or the default password policy object doesn't
exist, the MMC agent will create them when it starts.

Password Policy checker module
==============================

This module has only been built and tested on Mandriva and Debian. It is
installed as :file:`/usr/lib/openldap/mmc-check-password.so`.

If password quality checking is enabled on the password
policy, OpenLDAP calls this module to check password quality
when a user password is changed using the LDAP Password Modify
Extended operation. MDS will change user passwords with this
operation if you set "passwordscheme = passmod" in
the :file:`base.ini` configuration file.

To check a password, :file:`mmc-check-password.so` will launch the
command :file:`/usr/bin/mmc-password-helper`. The password will pass
the quality checks if it contains at least one number, one upper case
character, one lower case character and one special character (like #, $, etc.).
The password must not contains the same character twice. If python-cracklib
is available, a cracklib check is also done.

The mmc-password-helper tool
----------------------------

This tool allows to check a password from the command line.
For example:

::

    % echo foo | mmc-password-helper -c
    % echo $?
    1
    # Exit code is set to 1 if the password fails quality checks, else 0
    # Use -v for more
    # echo foo | mmc-password-helper -c -v
    the password must be 8 or longer
    % echo $?
    1

The tool also generates good passwords:

::

    % mmc-password-helper -n
    1NjY0MD:
    # Use -l to change the length (default is 8)
    % mmc-password-helper -n -l 12
    2ND=3OTcwMjY
    % mmc-password-helper -n | mmc-password-helper -c
    % echo $?
    0
    # Generated password will always succeed quality checks :)

Using password policies with SAMBA
==================================

If the SAMBA module is installed you can benefit of the LDAP password policies
when a user changes his password from any Windows machine in the domain or via
the MMC web interface.

Since SAMBA can't handle multiple password policies the MMC won't set any SAMBA
password policies in the SAMBA domain ldap entry. But when SAMBA will try to
change the user password in the LDAP, standard LDAP password policies applies.

The OpenLDAP password policies applies when the user password is changed with
the "passmod" LDAP operation and when the user running the "passmod" is not the
OpenLDAP rootdn.

If the MMC is binded to OpenLDAP with the rootdn as the administrator you will
be able to change passwords from the MMC interface without any password policy
checks. However, password poclicy is applied on the "change user password page"
for normal users.

.. note:: Password synchronization

   Usually the password synchronisation between the SAMBA password and
   the LDAP password is done by SAMBA itself. When a user changes his password
   SAMBA updates the sambaNTPassword attribute and run the "passmod" LDAP operation
   to change the userPassword attribute. This synchronization is done when
   :file:`ldap sync password = yes` is set in SAMBA configuration.
   The problem with this method is that if the password does not pass the password
   policy check, the SAMBA password will be updated (as it is not changed by a
   "passmod" operation) but the userPassword attribute won't.

   The second method to synchronize the password is to set :file:`ldap sync password = only`
   in SAMBA configuration. In this case, SAMBA will only run the "passmod" LDAP operation
   when the user changes his password and won't update the ``sambaNTPassword`` attribute of the user.
   To update this attribute the OpenLDAP overlay ``smbk5pwd`` must be used. This overlay will
   intercept "passmod" operations and update the SAMBA password automatically only if
   the ``userPassword`` attribute has been updated successfully.

In conclusion, in order to use LDAP password policies with SAMBA you have to
make sure that:

- SAMBA is not binded to OpenLDAP with the rootdn

- The :file:`password scheme` option is set to "passmod" in
  :file:`/etc/mmc/plugins/base.ini`

- Prefer using the :file:`ldap sync password = only` method with the ``smbk5pwd``
  overlay to make sure that passwords are always in sync (Shares ->
  General options -> Expert mode -> LDAP password sync)

The configuration of the smbk5pwd overlay is pretty forward. In your slapd.conf
just add :

::

    moduleload    smbk5pwd
    [ ... ]
    overlay smbk5pwd
    smbk5pwd-enable samba
    overlay ppolicy
    ppolicy_default "cn=default,ou=Password Policies,dc=mandriva,dc=com"
    [ ... ]

.. note:: The overlays order is important. Overlays will be called in the
   reverse order that they are defined. So ppolicy check must be done before
   smbk5pwd synchronization.
