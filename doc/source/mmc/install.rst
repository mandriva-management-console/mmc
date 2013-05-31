.. highlight:: none
.. _mmc-install:

============
Installation
============

How to install the MMC (Mandriva Management Console) on a Linux distribution

Repositories configuration and packages installation
####################################################

Mandriva users are lucky
========================

... because Mandriva RPM packages for the MDS and the MMC are available.

Packages for Mandriva 2010.0, 2010.2, 2011.0 and Cooker are available on Mandriva
official repositories. You will find an official mirror using `the Mandriva
mirror finder module <http://api.mandriva.com/mirrors/list.php>`_.

You can also add the repositories with the following command:

::

    urpmi.addmedia --distrib --mirrorlist '$MIRRORLIST'

To install the MMC base packages, just type:

::

    # urpmi mmc-agent mmc-web-base python-mmc-base

.. _debian-packages:

Debian packages
===============

For Debian Squeeze, add this in your sources.list:

::

    deb http://mds.mandriva.org/pub/mds/debian squeeze main

For Debian Wheezy:

::

    deb http://mds.mandriva.org/pub/mds/debian wheezy main

To install MMC base packages, just type:

::

    # apt-get update
    # apt-get install mmc-agent mmc-web-base python-mmc-base


.. _other-packages:

Other packages
==============

We also provide packages for other distribution trough OpenBuildSystem here :

- `MMC core <http://software.opensuse.org/download.html?project=home:eonpatapon:mds&package=mmc-core>`_
- `MDS plugins <http://software.opensuse.org/download.html?project=home:eonpatapon:mds&package=mds>`_

.. note:: CentOS DAG repository

   For some packages, you will need to add the DAG repository to yum. Create
   a file named :file:`/etc/yum.repos.d/DAG.repo` containing:

   ::

       # DAG Repository for RedHat Enterprise 4 / CentOS 4
       [dag]
       name=DAG Repository
       baseurl = http://apt.sw.be/redhat/el$releasever/en/$basearch/dag
       gpgkey=http://dag.wieers.com/packages/RPM-GPG-KEY.dag.txt
       gpgcheck=1
       enabled=0

Packages naming conventions
===========================

Here are the packages naming conventions:

- mmc-agent: the MMC agent package
- python-mmc-PLUGIN: MMC agent plugin
- mmc-web-PLUGIN: web interface plugin

.. note:: **Sample configuration files**

   All MMC related sample configuration files are available in the
   python-mmc-base package, in the directory
   :file:`/usr/share/doc/python-mmc-base/contrib/` or on our
   `repository <http://github.com/mandriva-management-console/mmc/tree/master/core/agent/contrib>`_.

   You will find there OpenLDAP, SAMBA and Postfix configuration files and also
   OpenLDAP schemas.

Installation from source tarball
################################

.. note:: **If you are using packages you can skip this part**

Pre-requisites
==============

This python modules are needed for MMC to run :

- python-twisted-web
- python-ldap
- pylibacl
- pyopenssl
- python-gobject

The MMC web interface is written in PHP4. Basically, you just need to install
an Apache 2 server with PHP5 support.

The XML-RPC module of PHP is needed too.

Installation
============

Get the current tarball at this URL: ftp://mds.mandriva.org/pub/mmc-core/sources/current/

::

    # tar xzvf mmc-core-x.y.z.tar.gz
    # cd mmc-core-x.y.z
    # ./configure --sysconfdir=/etc --localstatedir=/var
    # make
    # make install
    # tar xzvf mds-x.y.z.tar.gz

    If you want also MDS modules:

    # cd mds-x.y.z
    # ./configure --sysconfdir=/etc --localstatedir=/var
    # make
    # make install

The default $PREFIX for installation is :file:`/usr/local`. You can change it
on the ``./configure`` line by adding the option ``--prefix=/usr`` for example.

Here are how the files are installed:

- :file:`$PREFIX/sbin/mmc-agent`: the MMC agent
- :file:`$PREFIX/lib/mmc/`: helpers for some MMC plugins
- :file:`/etc/mmc/`: all MMC configuration files. There files are sample files
  you will need to edit.
- :file:`/etc/init.d/mmc-agent`: MMC agent init script
- :file:`$PREFIX/lib/pythonX.Y/site-packages/mmc`: MMC Python libraries and
  plugins.
- :file:`$PREFIX/lib/pythonX.Y/site-packages/mmc/plugins/`: MMC Python plugins
- :file:`$PREFIX/share/mmc/`: all MMC web interface related files
  (PHP, images, ...l)
- :file:`$PREFIX/share/mmc/modules/`: MMC web interface plugins
- :file:`/etc/mmc/mmc.ini`: MMC web configuration file

LDAP server configuration
#########################

MMC currently supports OpenLDAP.

One LDAP schema called MMC schema is mandatory.
This schema and others are available in the
:file:`/usr/share/doc/mmc/contrib/base/` directory provided by
the python-mmc-base package.

Mandriva
========

The OpenLDAP configuration can be easily done using the ``openldap-mandriva-dit-package``.

::

    # urpmi openldap-mandriva-dit
    ...
    # /usr/share/openldap/scripts/mandriva-dit-setup.sh
    Please enter your DNS domain name [localdomain]:
    mandriva.com
    Administrator account
    The administrator account for this directory is
    uid=LDAP Admin,ou=System Accounts,dc=mandriva,dc=com
    Please choose a password for this account:
    New password: [type password]
    Re-enter new password: [type password]
    Summary
    =======
    Domain:        mandriva.com
    LDAP suffix:   dc=mandriva,dc=com
    Administrator: uid=LDAP Admin,ou=System Accounts,dc=mandriva,dc=com
    Confirm? (Y/n)
    Y
    config file testing succeeded
    Stopping ldap service
    Finished, starting ldap service
    Running /usr/bin/db_recover on /var/lib/ldap
    remove /var/lib/ldap/alock
    Starting slapd (ldap + ldaps): [  OK  ]

And you're done, the LDAP directory has been populated and the LDAP service
has been started.

Some tweaks needs to be done to the LDAP configuration so that the LDAP service
suits to the MDS.

First, copy the MMC LDAP schema you need to the LDAP schemas directory.

::

    # cp /usr/share/doc/mmc/contrib/base/mmc.schema /etc/openldap/schema/

Then, add these line to the file :file:`/etc/openldap/schema/local.schema`:

::

    include /etc/openldap/schema/mmc.schema

Then, to avoid LDAP schemas conflicts, comment or remove these lines at the
beginning of the file :file:`/etc/openldap/slapd.conf`:

::

    #include /usr/share/openldap/schema/misc.schema
    #include /usr/share/openldap/schema/kolab.schema
    #include /usr/share/openldap/schema/dnszone.schema
    #include /usr/share/openldap/schema/dhcp.schema

Last, comment or remove these lines at the end of the file
:file:`/etc/openldap/mandriva-dit-access.conf`:

::

    #access to dn.one="ou=People,dc=mandriva,dc=com"
    #        attrs=@inetLocalMailRecipient,mail
    #        by group.exact="cn=MTA Admins,ou=System Groups,dc=mandriva,dc=com" write
    #        by * read

To check that the LDAP service configuration is right, run slaptest:

::

    # slaptest
    config file testing succeeded

Now you can restart the LDAP service:

::

    # service ldap restart
    Checking config file /etc/openldap/slapd.conf: [  OK  ]
    Stopping slapd:                                [  OK  ]
    Starting slapd (ldap + ldaps):                 [  OK  ]

Debian
======

When installing the slapd package, debconf allows you to configure
the root DN of your LDAP directory, set the LDAP manager password
and populate the directory. By default debconf will not ask you to
configure the root DN, you can run ``dpkg-reconfigure`` for this.
If you choose "mandriva.com" as your domain, the LDAP DN suffix
will be "dc=mandriva,dc=com".

::

    # dpkg-reconfigure slapd

After that you only need to include the mmc.schema in slapd
configuration and you are done.

.. note:: **Debian Squeeze and later**

   Debian's OpenLDAP uses its own database for storing
   its configuration. So there is no more slapd.conf.
   You can use the mmc-add-schema script to load new schema in
   the OpenLDAP configuration database:

   ::

       # mmc-add-schema /usr/share/doc/mmc/contrib/base/mmc.schema /etc/ldap/schema/
       Adding schema for inclusion: mmc... ok

   You can also write a regular slapd.conf file like before, and issue
   the followind commands to convert the file in the new format:

   ::

       # /etc/init.d/slapd stop
       # rm -rf /etc/ldap/slapd.d/*
       # slaptest -f /path/to/slapd.conf -F /etc/ldap/slapd.d
       # chown -R openldap.openldap /etc/ldap/slapd.d
       # /etc/init.d/slapd start

Other distributions
===================

.. note:: **OpenLDAP example configuration**

   You will find an example of OpenLDAP configuration in the directory
   :file:`agent/contrib/ldap/` of the mmc-core tarball.

.. note:: **Already existing directory**

   If you already have an OpenLDAP directory, all you need to do
   is to include the mmc.schema file.

Get the file :file:`mmc.schema` from the
:file:`/usr/share/doc/mmc/contrib/base`
directory, and copy it to :file:`/etc/openldap/schema/`
(or maybe :file:`/etc/ldap/schema/`).

Include this schema in the OpenLDAP configuration, in
:file:`/etc/ldap/slapd.conf`
(or maybe :file:`/etc/openldap/slapd.conf`):

::

    include /etc/openldap/schema/mmc.schema

This schema must be included after the
:file:`inetorgperson.schema` file.

In the OpenLDAP configuration file, we also define the LDAP DN
suffix, the LDAP manager (rootdn) and its password (rootpw):

::

    suffix          "dc=mandriva,dc=com"
    rootdn          "cn=admin,dc=mandriva,dc=com"
    rootpw          {SSHA}gqNR92aL44vUg8aoQ9wcZYzvUxMqU6/8

The SSHA password is computed using the slappasswd command:

::

    # slappasswd -s secret
    {SSHA}gqNR92aL44vUg8aoQ9wcZYzvUxMqU6/8

Once the OpenLDAP server is configured, the base LDAP directory
architecture must be created. Create a file called
:file:`/tmp/ldap-init.ldif` containing:

::

    dn: dc=mandriva,dc=com
    objectClass: top
    objectClass: dcObject
    objectClass: organization
    dc: mandriva
    o: mandriva
    dn: cn=admin,dc=mandriva,dc=com
    objectClass: simpleSecurityObject
    objectClass: organizationalRole
    cn: admin
    description: LDAP Administrator
    userPassword: gqNR92aL44vUg8aoQ9wcZYzvUxMqU6/8

The userPassword field must be filled with the output of the
slappasswd command. Now we inject the LDIF file into the directory:

::

    # /etc/init.d/ldap stop
    # slapadd -l /tmp/ldap-init.ldif
    # chown -R ldap.ldap /var/lib/ldap (use the openldap user for your distribution)
    # /etc/init.d/ldap start

.. note:: LDAP suffix

   In this example, the LDAP suffix is dc=mandriva,dc=com. Of course, you can
   choose another suffix.

.. note:: Changing the OpenLDAP manager password

   You can't change this password using the MMC interface. You must use this
   command line:

   ::

       $ ldappasswd -s NewPassword -D "cn=admin,dc=mandriva,dc=com" -w OldPassword -x cn=admin,dc=mandriva,dc=com

.. _nss-ldap:

NSS LDAP configuration
######################

To use LDAP users and groups, the OS needs to know where to look in LDAP.

To do this, :file:`/etc/nsswitch.conf` and :file:`/etc/ldap.conf`
(:file:`/etc/libnss-ldap.conf` for Debian based distros) should be configured.

.. note:: On Debian install the package ``libnss-ldap``

Your :file:`/etc/nsswitch.conf` should look like this:

::

    passwd:     files ldap
    shadow:     files ldap
    group:      files ldap
    hosts:      files dns
    bootparams: files
    ethers:     files
    netmasks:   files
    networks:   files
    protocols:  files
    rpc:        files
    services:   files
    netgroup:   files
    publickey:  files
    automount:  files
    aliases:    files

Your :file:`/etc/ldap.conf`:

.. note:: On Debian wheezy the configuration is located in
`/etc/libnss-ldap.conf`

::

    host 127.0.0.1
    base dc=mandriva,dc=com
