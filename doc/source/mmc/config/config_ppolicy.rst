.. highlight:: none
.. _config-ppolicy:

=======================================================
MMC ppolicy (Password Policy) plugin configuration file
=======================================================

This document explains the content of the MMC ppolicy
(Password Policy) plugin configuration file

Introduction
############

The « ppolicy » plugin allows to set the default password
policy to apply to all users contained into the LDAP directory,
and to set a specific password policy to a user.

This plugin is disabled by default. Please be sure to understand
how works password policy for LDAP before enabling it. Here are
some related documentations:

- `Internet-Draft:
  Password Policy for LDAP Directories <http://tools.ietf.org/html/draft-behera-ldap-password-policy>`_
- `Managing
  Password Policies in the Directory <http://www.symas.com/blog/?page_id=66>`_

The plugin configuration file is :file:`/etc/mmc/plugins/ppolicy.ini`.

Like all MMC related configuration file, its file format is INI
style. The file is made of sections, each one starting with a «
[sectionname] » header. In each section options can be defined
like this « option = value ».

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

Here are all the ppolicy.ini available sections:

================= =================================== ========
Section name      Description                         Optional
================= =================================== ========
main              global ppolicy plugin configuration no
ppolicy                                               yes
ppolicyattributes                                     yes
================= =================================== ========

Section « main »
################

This sections defines the global options of the mail plugin

=========== ========================= ======== =============
Option name Description               Optional Default value
=========== ========================= ======== =============
disable     Is this plugin disabled ? Yes      1
=========== ========================= ======== =============

Section « ppolicy »
###################

This section defines the LDAP location of the password policies.

============== ================================================================== ======== =============
Option name    Description                                                        Optional Default value
============== ================================================================== ======== =============
ppolicyDN      DN of the LDAP OU where the default password policy will be stored No
ppolicyDefault Name of the default password policy                                No
============== ================================================================== ======== =============

Section « ppolicyattributes »
#############################

This section defines the attributes and the values of the
default LDAP password policy. The default policy will be initialized
when the MMC agent starts if the default policy doesn't exist in
the LDAP directory.

Of course the attribute name must match the LDAP password policy
schema. Here is the default configuration we ship for this section:

::

    # This options are used only once to create the default password
    policy entry
    # into the LDAP
    [ppolicyattributes]
    pwdAttribute = userPassword
    pwdLockout = True
    pwdMaxFailure = 5
    pwdLockoutDuration = 900
    # Password can be change if it not 7 days old
    pwdMinAge = 25200
    # Password expiration is 42 days
    pwdMaxAge = 3628800
    pwdMinLength = 8
    pwdInHistory = 5
    pwdMustChange = True
    # To check password quality
    pwdCheckModule = mmc-check-password.so
    pwdCheckQuality = 2
