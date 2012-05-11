OpenLDAP Schema
===============

Old versions
------------

The mmc schema is needed to set ACLs on users in the MMC web interface::

  # cp /usr/share/doc/python-mmc-base/contrib/ldap/mmc.schema /etc/ldap/schema/

Then in /etc/ldap/slapd.conf include the schema::

  include /etc/ldap/schema/mmc.schema

New versions
------------

#TODO: Talk more about openldap changes in config and schema new storage.

Copy mmc schema to your current directory::

    $ cp /usr/share/doc/python-mmc-base/contrib/ldap/mmc.schema .

Create a file mmc.conf with::

    include    mmc.schema

Create a folder schemas::

    $ mkdir schemas

Convert mcc.schema to ldif::

   $ slaptest -f mmc.conf -F schemas/

Edit mmc schema, remove {0} from dn:, cn: and add cn=schema,cn=config to dn ::

    dn: cn=mmc,cn=schema,cn=config
    objectClass: olcSchemaConfig
    cn: mmc

Remove the following lines at the bottom of that file::

    structuralObjectClass: olcSchemaConfig
    entryUUID: 0ec2fe60-1381-1031-8f21-f92982aeda45
    creatorsName: cn=config
    createTimestamp: 20120405153755Z
    entryCSN: 20120405153755.316520Z#000000#000#000000
    modifiersName: cn=config
    modifyTimestamp: 20120405153755Z

Add schema to ldap::

   # ldapadd -Y EXTERNAL -H ldapi:/// -f schemas/cn\=config/cn\=schema/cn\=\{0\}mmc.ldif

Restart the slapd daemon.
