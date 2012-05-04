=================================
MMC audit framework specification
=================================

This document specifies the MMC audit framework.

Introduction
############

An administrator needs to know who did what and when. This feature is highly
needed for the MMC, as it manages users and critical network ressources.

This document defines the perimeter of the MMC audit system, the data structure
and the Python API that will be used to log events.

We won't talk in this document about any audit GUI design.

Perimeter
#########

First, what is not the MMC audit framework:

- a debugging log for developers. Auditing is not logging.
- a log for all LDAP directory modifications. That's the role of the LDAP
  directory to provide this kind of data. Moreover, the MMC API also works on
  non LDAP data, like SAMBA shares for example.

We will record only "atomic" events. For example, If we have a method that
remove a user from all her/his group, we will create a log record for each
removed group instead of a single log telling that the user has been removed
from all groups. This precision is needed to follow the exact life-cycle of
the objects.

Structure of an audit record
############################

An audit record is made of the following datas:

- timestamp: when does this event occured ?
- source: which host is performing the action related to the event and
  reporting the event ? This can be the host name of the MMC agent reporting
  the event, or the host name where a script using the MMC API is used.
- initiator: who triggered this event ? This can be the user id of the user
  connected to the MMC agent, or the effective user id of a script using the
  MMC API.
- initiator: which application on which host initiated this event ? The typical
  cases: the MMC web interface on computer laptop.example.net, the python
  script ``/root/populate_ldap.py`` on computer mds.example.net.
- event: what happened ? To designate an event, we will use a simple label.
  For example, the "add a user action" from the MMC "base" API is called
  "BASE_ADD_USER".
- action result: was the operation triggering the event succesfull ?
- target: which were the object affected by the event ? An object could be a
  user, a group, a user LDAP attribute, etc.
- what were the previous values of the affected targets, and what are the
  current values ? For example, if we modified the value of a LDAP attribute,
  we will save its previous and current value in the audit record.

Audit database schema
#####################

We will provide a MySQL and a PostgreSQL database backend, thanks to the
python SQLAlchemy library.

Here is the database schema:

.. image:: /_static/audit-database-schema.png

Table description:

- initiator: application (MMC web interface for example), IP address and
  hostname of the server that initiated the connection;

- source: hostname of the machine that received the action that triggered the
  event, and which is reporting the event;

- module: module name owner of the event. If the event is linked to the MMC
  SAMBA module, the name will be ``MMC-SAMBA``;

- event: name of the reported event, for example "SAMBA_LOCK_USER". Each event
  is linked to its module;

- type: name of a object type, for example "USER", "GROUP", ...

- object: URI of a object affected by the audit record. If the object is a LDAP
  object, the URI is the LDAP DN of the object. For other type of object, a URI
  system must be found. Each object has a type. For example, the object
  representing the LDAP user "foo" has the URI "uid=foo,ou=Users,dc=mds"
  and the type "USER".

- object_log: link a log entry to object entries;

- previous_value: the previous value (if applicable) of the object before the
  audit record;

- current_value: the value (if applicable) of the object after the audit record;

- parameters: additional event parameters (optional);

- log: a log is linked to an initiator, a source, an event, one or multiple
  object_log rows, and zero or multiple parameters. The first object_log row
  is always linked to the object representing the user that triggered the event.
  The "result" column is a boolean, which value is false if the operation
  linked to the event failed.

How do we address object in a log ?
===================================

There is a little problem when addressing object in a log. For example, we
want to record that the LDAP attribute called "fooattr" from the user "foo"
has been deleted. How do we implement that using this database structure ?

When storing the log data into the database, we will simply connect the object
representing the LDAP attribute "fooattr" to the object representing the user
"foo" thanks to the fk_parent field.

How do we know the current execution context ?
==============================================

All XML-RPC calls received by the MMC agent are executed in threads. Each
time a new thread is started, the current user session is attached to the
thread. From the session the MMC agent knows which user triggered an event and
the initiator (the MMC web interface in most case).

When using the MMC API without the MMC agent (no XML-RPC calls), the initiator
is the current host and the current application (sys.argv[0]), and the user is
the current effective user id number.

Python API
##########

AuditFactory singleton class
============================

We provide a Singleton class called "AuditFactory" that allows to access the
audit framework. It reads the "audit" section of
:file:`/etc/mmc/plugins/base.ini` file that defines the database connection.

For compatibility, the audit framework can be disabled.

Logging an event
================

The AuditFactory class owns this method to log an event:

::

    def log(self, module, event, objects = None, current=None, previous=None, parameters = None)

- module: module name owner of the event
- event: event name
- objects: objects affected by the event. the object is represented by a couple
  (object name, object type). For example, the user "foo" is ``("foo", "USER")``.
  If the object is a child of another object, its parent must be prepended in a
  list. For example, the attribute "fooattr" of the user "foo" is
  ``[("foo", "USER"), ("fooattr", "ATTRIBUTE")]``
- previous: previous value of the object affected by the event;
- current: current value of the object affected by the event;
- parameters: parameters used when performing the action that triggered the event;

This method creates all needed rows into the audit database. It should be called
just before an action is performed. It sets the log database result field to
False to define that the action has not been performed or has failed.

This method returns an AuditRecord object, that has only one method called
"commit", that should be use when the action is done:

Example
=======

::

    from mmc.core.audit import AuditFactory
    # Record to the audit database the action being performed
    r = AuditFactory().log("MODULE_TEST", "TEST_AUDIT")
    # Do domething
    ...
    # Flag the action has successfull
    r.commit()

Declaring module events and type
================================

Each MMC API module have a :file:`audit.py` Python file that defines all events
and types managed by the module.

Here is an extract of what contains the :file:`audit.py` file for the
"base" MMC module:

::

    class AuditActions:
        BASE_ADD_USER = u'BASE_ADD_USER'
        BASE_ENABLE_USER = u'BASE_ENABLE_USER'
        ...

    class AuditTypes:
        USER = u'USER'
        GROUP = u'GROUP'
        ...

    AA = AuditActions
    AT = AuditTypes
    PLUGIN_NAME = u'MMC-BASE'

Remarks:

- All the strings must be unicode strings, in uppercase.
- Actions (events) name starts with the name of the plugin

Defining object URI
===================

An object URI must allow us to identify and address a unique object in the
audit database, to record and track all its changes.

For LDAP objects, it is logical to use the object DN as the URI to store into
the database.

But the MMC allows to modify objects which are not into the LDAP, for example
SAMBA shares. For this kind of objects, a method to build an URI must be found.
