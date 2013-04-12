===========
Squid plugin
===========

Installation
============

Install the packages ``python-mmc-squid`` and ``mmc-web-squid``.

LDAP directory configuration
============================
Two groups will be created automatically on LDAP base:

::

    InternetMaster is the group with total previlegies to access any site and download any extension in any time.
    Internet is the group with Internet and extensions filtred by a list of the key-words and domains,


Squid configuration
==========================
The configuration of the squid.conf file was customize to provide a LDAP authentication of the users and follow configurations 

::

    hierarchy_stoplist cgi-bin ?
    acl QUERY urlpath_regex cgi-bin \?
    no_cache deny QUERY
    dns_nameservers localhost
    maximum_object_size_in_memory 64 KB
    cache_store_log none
    hosts_file /etc/hosts

Authentication configuration
======================

::

    auth_param basic realm Atention: Autentication Required!
    auth_param basic program /usr/lib64/squid/squid_ldap_auth -b "@DN@" -f uid=%s localhost
    auth_param basic children 3
    auth_param basic casesensitive off
    auth_param basic credentialsttl 1 hours
    external_acl_type ldap_auth %LOGIN /usr/lib64/squid/squid_ldap_group -d -b "@DN@" -f "(&(memberuid=%u)(cn=%g))" -h localhost

Python Pugin
=====================
The python plugin manipulate directly squid files, read and write in rules files in.

::
    /etc/squid/rules/


