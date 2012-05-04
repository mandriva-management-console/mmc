.. highlight:: none
.. _mmc-audit:

===============
Audit framework
===============

.. note:: The configuration of the audit framework is optionnal

The MMC audit framework allows to record all users operations
made through the MMC agent, and so the MMC web interface. These
operations are all loggued: LDAP modifications, all filesystem
related modifications, and service management (stop, start, ...)

The Python SQLAlchemy library version 0.5.x/0.6.x is required for the audit
framework. The Python / MySQL bindings are also needed. On Debian install
the following packages:

::

    apt-get install python-mysqldb python-sqlalchemy

The audit framework is configured in the :file:`base.ini` configuration file, 
and is disabled by default. To enable it, uncomment the audit
section. It should look like:

::

    [audit]
    method = database
    dbhost = 127.0.0.1
    port = 3306
    dbdriver = mysql
    dbuser = audit
    dbpassword = audit
    dbname = audit

The :command:`mmc-helper` tool will allow you to create
the dabatase and to populate it with the audit tables easily.

To create the MySQL database:

::

    # mmc-helper audit create
    -- Execute the following lines into the MySQL client
    CREATE DATABASE audit DEFAULT CHARSET utf8;
    GRANT ALL PRIVILEGES ON audit.* TO 'audit'@localhost IDENTIFIED BY
    'audit';
    FLUSH PRIVILEGES;

Just execute the printed SQL statement in a MySQL client and the
database will be created. Note that the :file:`base.ini` is read to set the 
audit database name, user and password in the SQL statements.

On most Linux distribution, the "root" user has administrative
access to the local MySQL server. So this one liner will often be enough:

::

    # mmc-helper audit create | mysql

Once created, the audit database tables must be initialized with this command:

::

    # mmc-helper audit init
    INFO:root:Creating audit tables as requested
    INFO:root:Using database schema version 2
    INFO:root:Done

At the next start, the MMC agent will connect to the audit database and record 
operations.
