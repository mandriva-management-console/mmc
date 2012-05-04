.. highlight:: none
.. _config-agent:

============================
MMC agent configuration file
============================

This document explains the content of the MMC agent configuration file.

Introduction
############

The MMC agent is a XML-RPC server that exports to the network the API provided
by the MMC python plugins.

Its configuration file is :file:`/etc/mmc/agent/config.ini`. This file must be
readable only by root, as it contains the login and password required to connect
to the MMC agent.

Like all MMC related configuration file, its file format is INI style. The file
is made of sections, each one starting with a « [sectionname] » header. In each
section options can be defined like this « option = value ».

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

:file:`/etc/mmc/agent/config.ini` available sections:

============ ======================= ========
Section name Description             Optional
============ ======================= ========
main         MMC agent main option   no
daemon       MMC agent daemon option no
============ ======================= ========

All the other sections (loggers, handlers, ...) are related to Python language
logging framework. See `the Python documentation <http://docs.python.org/lib/logging-config-fileformat.html>`_
for more informations.

Section « main »
################

Available options for the "main" section:

============== =========================================================================================================================================================================================================================================================== ====================== =============
Option name    Description                                                                                                                                                                                                                                                 Optional               Default value
============== =========================================================================================================================================================================================================================================================== ====================== =============
host           IP where the MMC agent XML-RPC server listens to incoming connections                                                                                                                                                                                       No
port           TCP/IP port where the MMC agent XML-RPC server listens to incoming connections                                                                                                                                                                              No
login          login to connect to the MMC agent XML-RPC server                                                                                                                                                                                                            No                     mmc
password       password to connect to the MMC agent XML-RPC server                                                                                                                                                                                                         No                     s3cr3t
enablessl      Enable TLS/SSL for XMLRPC communication. If disabled, the XMLRPC traffic is not encrypted.                                                                                                                                                                  yes                    0
verifypeer     If SSL is enabled and verifypeer is enabled, the XML-RPC client that connects to the MMC agent XML-RPC server must provide a valid certificate, else the connection will be closed.                                                                         yes                    0
localcert      If verifypeer = 1, the file should contain the private key and the public certificate. This option was previously called privkey                                                                                                                            If verifypeer = 1, yes
cacert         Path to the file (PEM format) containing the public certificate of the Certificate Authority that produced the certificate defined by the localcert option. If verifypeer = 1, the certificate provided by the XML-RPC client will be validated by this CA. If verifypeer = 1, yes
sessiontimeout Session timeout in seconds. When a user authenticates to the MMC agent, a user session in created. This session is destroyed automatically when no call is done before the session timeout is reach.                                                        Yes                    900
multithreading Multi-threading support. If enabled, each incoming XML-RPC request is processed in a new thread.                                                                                                                                                            Yes                    1
maxthreads     If multi-threading is enabled, this setting defines the size of the pool of threads serving XML-RPC requests.                                                                                                                                               Yes                    20
sessiontimeout RPC Session timeout in seconds. If unset default to Twisted hardcoded 900 seconds.                                                                                                                                                                          yes                    900
============== =========================================================================================================================================================================================================================================================== ====================== =============

If ``host=127.0.0.1``, the MMC agent will only listen to local incoming
connections. You can use ``host=0.0.0.0`` to make it listen to all available
network interfaces.

To connect to the MMC agent, the client (for example the MMC web
interface) must do a HTTP Basic authentication, using the configured login
and password.

You must change the login and password in the configuration file,
because if you keep using the default configuration, anybody can connect
to your MMC agent. MMC agent issue a warning if you use the default login
and password.

Section « daemon »
##################

This section defines some MMC agent daemon settings.

Available options for the "daemon" section

=========== ======================================================================= ======== =============
Option name Description                                                             Optional Default value
=========== ======================================================================= ======== =============
user        System user under which the MMC agent service is running                yes      root
group       System group under which the MMC agent service is running               yes      root
umask       umask used by the MMC agent when creating files (log files for example) yes      0777
pidfile     Path to the file containing the PID of the MMC agent                    No
=========== ======================================================================= ======== =============

If the MMC agent is configured to run as non-root, it drops its root
privileges to the defined user and group id using the "seteuid" system
call. This is done as soon as the configuration file is read.

Sections related to the Python logging module
#############################################

See http://docs.python.org/lib/logging-config-fileformat.html.

In the default MMC agent configuration, two handlers are configured:

::

    [handler_hand01]
    class=FileHandler
    level=INFO
    formatter=form01
    args=("/var/log/mmc/mmc-agent.log",)

    [handler_hand02]
    class=StreamHandler
    level=DEBUG
    args=(sys.stderr,)

The handler ``hand01`` records all logs emitted by the MMC agent (and its
activated plugins) in the file :file:`/var/log/mmc/mmc-agent.log`.

The handler ``hand02`` is used by the MMC agent only when it starts to display
startup messages, then it is closed.

How to enable full debug in MMC agent
#####################################

Just set ``level=DEBUG`` in hand01 handler (see previous section), and
restart the MMC agent.

All the remote function calls and responses are now recorded in MMC log file.
