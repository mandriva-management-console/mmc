.. highlight:: none
.. _config-base:

==================================
MMC base plugin configuration file
==================================

This document explains the content of the MMC base plugin configuration file.

Introduction
############

The « base » plugin is the main plugin of the MMC Python API. It
features base operations like LDAP management (users, groups, etc), user
authentication and provisioning.

The plugin configuration file is
:file:`/etc/mmc/plugins/base.ini`.

Like all MMC related configuration file, its file format is INI
style. The file is made of sections, each one starting with a « [sectionname] »
header. In each section options can be defined like this «
option = value ».

For example:
::

    [section1]
    option1 = 1
    option2 = 2

    [section2]
    option1 = foo
    option2 = plop

Obfuscated password support in configuration files
##################################################

All the passwords contained in MMC-related configuration files can
be obfuscated using a base64 encoding. This is not a security feature, but
at least somebody won't be able to read accidentally a password.

To obfuscate a password, for example the word "secret", you can use
the Python interpreter:

::

    $ python -c 'print "secret".encode("base64")'
    c2VjcmV0

The base64-encoded password is the word "c2VjcmV0". Now to use it in
a configuration file:

::

    [section]
    password = {base64}c2VjcmV0

The {base64} prefix warns the configuration parser that the
following word is a base64-encoded word, and so needs to be decoded before
being used.

Configuration file sections
###########################

Here are all the base.ini available sections:

============== ======================================================================================================================================================= ========
Section name   Description                                                                                                                                             Optional
============== ======================================================================================================================================================= ========
ldap           LDAP access definition                                                                                                                                  no
backup-tools   Backup tools configuration                                                                                                                              no
audit          MMC audit framework configuration                                                                                                                       yes
hooks          Hooks for scripts that interacts with the MMC                                                                                                           yes
userdefault    Attributes and Objectclass values that are added or deleted when adding a new user into the LDAP                                                        yes
authentication Defines how a user is authenticated when logging into the MMC web interface. For example, another LDAP server can be use to perform the authentication. yes
provisioning   User accounts can be created or updated automatically when logging in to the MMC web interface.                                                         yes
subscription   This section is used to declare what has been subscribed, and to give some important information to the end user.                                       yes
============== ======================================================================================================================================================= ========

Section « ldap »
################

This section defines how the main LDAP is accessed, where are
located the users organization units, etc.

Available options for the "ldap" section

============================ =========================================================================================================================================================================================================================================================================================================================================================================== ======== ================
Option name                  Description                                                                                                                                                                                                                                                                                                                                                                 Optional Default value
============================ =========================================================================================================================================================================================================================================================================================================================================================================== ======== ================
host (deprecated by ldapurl) IP address or hostname of the LDAP server                                                                                                                                                                                                                                                                                                                                   no
ldapurl                      LDAP URL to connect to the LDAP server, for example: ldap://127.0.0.1:389. If ldapurl starts with "ldaps://", use LDAP over SSL on the LDAPS port. LDAPS is deprecated, and you should use StartTLS. If ldapverifypeer = demand, always use the server hostname instead of its IP address in the LDAP URL. This hostname must match the CN field of the server certificate. no
network_timeout              Network timeout in seconds for LDAP operations. No default timeout set.                                                                                                                                                                                                                                                                                                     yes
start_tls                    TLS connection parameters when LDAPS is not used. "off": never use TLS. "start_tls": use the LDAPv3 StartTLS extended operation (recommended).                                                                                                                                                                                                                              yes      off
ldapverifypeer               If start_tls != off or LDAPS, specify check to perform on server certificate. "never": don't ask certificate. "demand": request certificate. If none or bad certificate provided, stop the connection (recommended).                                                                                                                                                        yes      demand
cacertdir                    Client certicates to use (default are empty) for LDAPS or TLS connections. For example: /etc/ssl/certs                                                                                                                                                                                                                                                                      yes
cacert                       Certificate Authority file. For example: /etc/mmc/certs/demoCA/cacert.pem                                                                                                                                                                                                                                                                                                   yes
localcert                    Local SSL certificate file. For example: /etc/mmc/certs/client.cert                                                                                                                                                                                                                                                                                                         yes
localkey                     Local SSL public key. For example: /etc/mmc/certs/client.key                                                                                                                                                                                                                                                                                                                yes
ciphersuites                 Accepted ciphers from the LDAP server.                                                                                                                                                                                                                                                                                                                                      yes      TLSv1:!NULL
ldapdebuglevel               set this to 255 to debug LDAP connection problems. Details of all LDAP operations will be written to stdout                                                                                                                                                                                                                                                                 yes      0
baseDN                       LDAP base Distinguished Name (DN)                                                                                                                                                                                                                                                                                                                                           no
rootName                     LDAP administrator DN                                                                                                                                                                                                                                                                                                                                                       no
password                     LDAP administrator password                                                                                                                                                                                                                                                                                                                                                 no
baseUsersDN                  LDAP organisational unit DN where the users are located                                                                                                                                                                                                                                                                                                                     no
baseGroupsDN                 LDAP organisational unit DN where the groups are located                                                                                                                                                                                                                                                                                                                    no
gpoDN                        LDAP organisational unit DN where the GPO are located                                                                                                                                                                                                                                                                                                                       yes      ou=System,baseDN
userHomeAction               If set to 1, create and delete user directory when creating/deleting one                                                                                                                                                                                                                                                                                                    no
defaultUserGroup             When creating an user, set this group as the primary user group                                                                                                                                                                                                                                                                                                             yes
skelDir                      Use the specified directory when creating a user home directory                                                                                                                                                                                                                                                                                                             yes      /etc/skel
defaultHomeDir               Use this directory as a base directory when creating a user without specifying a home directory. If the creater user is called "foo", his/her homeDirectory will be "defaultHomeDir/foo"                                                                                                                                                                                    yes      /home
defaultShellEnable           the default shell for enabled users                                                                                                                                                                                                                                                                                                                                         no       /bin/bash
defaultShellDisable          the default shell for disabled users                                                                                                                                                                                                                                                                                                                                        no       /bin/false
authorizedHomeDir            a list of directory where user home directory can be put                                                                                                                                                                                                                                                                                                                    yes      defaultHomeDir
uidStart                     starting uid number for user accounts                                                                                                                                                                                                                                                                                                                                       yes      10000
gidStart                     starting gid number for groups                                                                                                                                                                                                                                                                                                                                              yes      10000
logfile                      LDAP log file path                                                                                                                                                                                                                                                                                                                                                          no
passwordscheme               LDAP user password scheme. Possible values are "ssha", "crypt" and "passmod". "passmod" uses the LDAP Password Modify Extended Operations to change password. The password encryption is done by the LDAP server.                                                                                                                                                           no       passmod
============================ =========================================================================================================================================================================================================================================================================================================================================================================== ======== ================

Section « backup-tools »
########################

This section defines where are located the backup tools. The backup
tools are used when backuping a home directory or a SAMBA share from the
MMC.

Available options for the "backup-tools" section:

=========== =========================================================== ========
Option name Description                                                 Optional
=========== =========================================================== ========
path        Where are located the executable needed by the backup tools no
destpath    Where the backup are located once done                      no
=========== =========================================================== ========

Section « audit »
#################

This section defines the audit framework behaviour. By default the
audit framework is disabled.

Available options for the "audit" section:

=========== ============================================================================== ========
Option name Description                                                                    Optional
=========== ============================================================================== ========
method      Method used to record all audit data. Only the "database" method is supported. no
dbhost      Host to connect to the SGBD that stores the audit database                     no
dbdriver    Database driver to use. "mysql" and "postgres" drivers are supported.          no
dbport      Port to connect to the SGBD that stores the audit database.                    no
dbuser      User login to connect to the SGBD that stores the audit database.              no
dbpassword  User password to connect to the SGBD that stores the audit database.           no
dbname      Name of the audit database.                                                    no
=========== ============================================================================== ========

Section « hooks »
#################

The hooks system allow you to run external script when doing some
operations with the MMC.

The script will be run as root user, with as only argument the path
to a temporary file containing the full LDIF export of the LDAP user. For
the « adduser » and « changeuserpassword » hooks, the LDIF file will
contain the userPassword attribute in cleartext.

The executable bit must be set on the script to run. The temporary
LDIF file is removed once the script has been executed.

Available options for the "hooks" section:

================== ====================================================================================== ========
Option name        Description                                                                            Optional
================== ====================================================================================== ========
adduser            path to the script launched when a user has been added into the LDAP directory         yes
changeuserpassword path to the script launched when a user has been changed into the LDAP directory       yes
deluser            path to the script launched when a user is going to be removed from the LDAP directory yes
================== ====================================================================================== ========

Here is a hook example written in BASH for « adduser »:

::

    #!/bin/sh
    # Get the uid of the new user
    VALUE=`cat $1 | grep ^uid: | sed "s/uid: //"`
    # Log new user event
    echo "New user $VALUE created" >> /var/log/newuser.log
    exit 0

The same hook, written in Python:

.. code-block:: python

    #!/usr/bin/env python
    import sys
    # ldif is a Python package of the python-ldap extension
    import ldif
    LOGFILE = "/var/log/newuser.log"

    class MyParser(ldif.LDIFParser):

        def handle(self, dn, entry):
            uid = entry["uid"][0]
            f = file(LOGFILE, "a")
            f.write("New user %s created\\n" % uid)
            f.close()

    parser = MyParser(file(sys.argv[1]))
    parser.parse()

Section « userdefault »
#######################

This section allows to set default attributes to a user, or remove
them, only at user creation.

Each option of this section is corresponding to the attribute you
want to act on.

If you want to remove the « displayName » attribute of each newly
added user:

::

    [userdefault]
    displayName = DELETE

Substitution is done on the value of an option if a string between
'%' is found. For example, if you want that all new user have a default
email address containing their uid:

::

    [userdefault]
    mail = %uid%@mandriva.com

If you want to add a value to a multi-valuated LDAP attribute, do
this:

::

    [userdefault]
    objectClass = +corporateUser

Since version 1.1.0, you can add modifiers that interact with the
substitution. This modifiers are put between square bracket at the
beginning os the string to substitute.

Available modifiers for substitution

================== =============================================================
modifier character Description
================== =============================================================
/                  Remove diacritics (accents mark) from the substitution string
_                  Set substitution string to lower case
\|                 Set substitution string to upper case
================== =============================================================

For example, you want that all new created users have a default mail
address made this way: « firstname.lastname@mandriva.com ». But your user
firstname/lastname have accent marks, that are forbidden for email
address. You do it like this:

::

    [userdefault]
    mail = [_/]%givenName%.%sn%@mandriva.com

User authentication
###################

The default configuration authenticates users using the LDAP
directory specified in the [ldap] section.

But it is also possible to set up authentication using an external
LDAP server.

Section « authentication »
==========================

This optional section tells the MMC agent authentication manager
how to authenticate a user. Each Python plugin can register
"authenticator" objects to the authentication manager, that then can be
used to authenticate users.

The authentication manager tries each authenticator with the
supplied login and password until one successfully authenticate the
user.

Please note that the user won't be able to log in to the MMC web
interface if she/he doesn't have an account in the LDAP directory
configured in the [ldap] section of the base plugin. The provisioning
system will allow you to automatically create this account.

The base plugin registers two authenticators:

- baseldap: this authenticator uses the LDAP directory
  configured in the [ldap] section of the base plugin to authenticate
  the user,

- externalldap: this authenticator uses an external LDAP
  directory to authenticate the user.

Available options for the "authentication" section

=========== ==================================================================== ======== =============
Option name Description                                                          Optional Default value
=========== ==================================================================== ======== =============
method      space-separated list of authenticators to try to authenticate a user yes      baseldap
=========== ==================================================================== ======== =============

The default configuration is:

::

    [authentication]
    method = baseldap

authentication_baseldap
=======================

This section defines some configuration directives for the
baseldap authenticator.

Available options for the "authentication_baseldap" section:

=========== ========================================================================================================= ======== =============
Option name Description                                                                                               Optional Default value
=========== ========================================================================================================= ======== =============
authonly    space-separated list of login that will be authentified using this authenticator. Others will be skipped. yes
=========== ========================================================================================================= ======== =============

For example, to make the "baseldap" authenticator only
authenticate the virtual MMC "root" user:

::

    [authentication_baseldap]
    authonly = root

authentication_externalldap
===========================

This section defines some configuration directives for the
baseldap authenticator.

Available options for the "authentication_externalldap" section:

=============== ================================================================================================================================================================================================== ======== =============
Option name     Description                                                                                                                                                                                        Optional Default value
=============== ================================================================================================================================================================================================== ======== =============
exclude         space-separated list of login that won't be authenticated using this authenticator.                                                                                                                yes
authonly        If set, only the logins from the specified space-separated list of login will be authenticated using this authenticator, other login will be skipped.                                              yes
mandatory       Set whether this authenticator is mandatory. If it is mandatory and can't be validated during the mmc-agent activation phase, the mmc-agent exits with an error.                                   yes      True
network_timeout LDAP connection timeout in seconds. If the LDAP connection failed after this timeout, we try the next LDAP server in the list or give up if it the last.                                           yes
ldapurl         LDAP URL of the LDAP directory to connect to to authenticate user. You can specify multiple LDAP URLs, separated by spaces. Each LDAP server is tried until one successfully accepts a connection. no
suffix          DN of the LDAP directory where to search users                                                                                                                                                     no
bindname        DN of the LDAP directory account that must be used to bind to the LDAP directory and to perform the user search. If empty, an anonymous bind is done.                                              no
bindpasswd      Password of the LDAP directory account given by the bindname option. Not needed if bindname is empty.                                                                                              no
filter          LDAP filter to use to search the user in the LDAP directory                                                                                                                                        yes      objectClass=*
attr            Name of the LDAP attribute that will allow to match a user entry with a LDAP search                                                                                                                no
=============== ================================================================================================================================================================================================== ======== =============

For example, to authenticate a user using an Active
Directory:

::

    [authentication_externalldap]
    exclude = root
    ldapurl = ldap://192.168.0.1:389
    suffix = cn=Users,dc=adroot,dc=com
    bindname = cn=Administrator, cn=Users, dc=adroot, dc=com
    bindpasswd = s3cr3t
    filter = objectClass=*
    attr = cn

User provisioning
#################

This feature allows to automatically create a user account if it
does not already exist in the LDAP directory configured in the [ldap]
section of the base plugin.

User provisioning is needed for example if an external LDAP is used
to authenticate users. The users won't be able to log in to the MMC web
interface even if their login and password are rights, because the local
LDAP doesn't store thir accounts.

Section « provisioning »
========================

This optional section tells the MMC agent provisioning manager how
to provision a user account. Each Python plugin can register
"provisioner" objects to the provisioning manager, that then can be used
to provision users.

When a user logs in to the MMC web interface, the authenticator
manager authenticates this user. If the authentication succeed, then the
provisioning manager runs each provisioner.

The authenticator object that successfully authenticates the user
must pass to the provisioning manager the user informations, so that the
provisioners have data to create or update the user entry.

Available options for the "provisioning" section

=========== ==================================== ======== =============
Option name Description                          Optional Default value
=========== ==================================== ======== =============
method      space-separated list of provisioners yes
=========== ==================================== ======== =============

For example, this configuration tells to use the "externalldap"
provisioner to create the user account:

::

    [provisioning]
    method = externalldap

provisioning_external
=====================

This section defines some configuration directives for the
externalldap authenticator.

Available options for the "authentication_externalldap" section:

========================= ================================================================================================================================================================ ======== =============
Option name               Description                                                                                                                                                      Optional Default value
========================= ================================================================================================================================================================ ======== =============
exclude                   space-separated list of login that won't be provisioned by this provisioner.                                                                                     yes
ldap_uid                  name of the external LDAP field that is corresponding to the local uid field. The uid LDAP attribute is the user login.                                          no
ldap_givenName            name of the external LDAP field that is corresponding to the local givenName field                                                                               no
ldap_sn                   name of the external LDAP field that is corresponding to the local sn (SurName) field                                                                            no
profile_attr              The ACLs fields of the user that logs in can be filled according to the value of an attribute from the external LDAP. This option should contain the field name. yes
profile_acl_<profilename> The ACLs field of the user that logs in with the profile <profilename>.                                                                                          yes
profile_group_mapping     If enabled, users with the same profile will be put in the same users group.                                                                                     yes      False
profile_group_prefix      If profile_group_mapping is enabled, the created groups name will be prefixed with the given string.                                                             yes
========================= ================================================================================================================================================================ ======== =============

To create a user account, the MMC agent needs the user's login,
password, given name and surname. That's why the ``ldap_uid`È,
``ldap_givenName`` and ``ldap_sn`` options are mandatory.

Here is a simple example of an authenticators and provisioners
chain that authenticates users using an Active Directory, and create
accounts:

::

    [authentication]
    method = baseldap externalldap

    [authentication_externalldap]
    exclude = root
    ldapurl = ldap://192.168.0.1:389
    suffix = cn=Users,dc=adroot,dc=com
    bindname = cn=Administrator, cn=Users, dc=adroot, dc=com
    bindpasswd = s3cr3t
    filter = objectClass=*
    attr = cn

    [provisioning]
    method = externalldap

    [provisioning_externalldap]
    exclude = root
    ldap_uid = cn
    ldap_givenName = sn
    ldap_sn = sn

Subscription informations
#########################

This section contains all the information needed when the version is
not a community one. It allow for example to send mail to the
administrator directly from the GUI when something went wrong.

Available options for the "subscription" section:

=============== ======================================================================== ======== ==================================
Option name     Description                                                              Optional Default value
=============== ======================================================================== ======== ==================================
product_name    A combination of "MDS" and "Pulse 2" to describe the product             yes      MDS
vendor_name     The vendor's name                                                        yes      Mandriva
vendor_mail     The vendor's email address                                               yes      sales@mandriva.com
customer_name   The customer's name                                                      yes
customer_mail   The customer's email address                                             yes
comment         A comment on the customer                                                yes
users           The number of users included in the subscription. 0 is for infinite.     yes      0
computers       The number of computers included in the subscription. 0 is for infinite. yes      0
support_mail    The support's email address                                              yes      customer@customercare.mandriva.com
support_phone   The support's phone number                                               yes      0810 LINBOX
support_comment A comment about the support                                              yes
=============== ======================================================================== ======== ==================================
