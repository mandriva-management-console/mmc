=========
Using MMC
=========

Controlling mmc-agent
=====================

To start and stop the MMC agent, use the :file:`/etc/init.d/mmc-agent` script:

::

    # /etc/init.d/mmc-agent stop
    # /etc/init.d/mmc-agent start

The MMC agent must be started to use the MMC web interface.

When the MMC agent is started, all startup log messages are written to stderr
and :file:`/var/log/mmc/mmc-agent.log`.

Here is what is written (for example) if there is no error:

::

    # /etc/init.d/mmc-agent start
    Starting Mandriva Management Console XML-RPC Agent: mmc-agent starting...
    Plugin base loaded, API version: 4:0:0 build(82)
    Plugin mail loaded, API version: 3:0:1 build(78)
    Plugin samba loaded, API version: 3:0:2 build(78)
    Plugin proxy loaded, API version: 1:0:0 build(78)
    Daemon PID 13943
    done.

If there is an error:

::

    # /etc/init.d/mmc-agent start
    Starting Mandriva Management Console XML-RPC Agent: mmc-agent starting...
    Can't bind to LDAP: invalid credentials.
    Plugin base not loaded.
    MMC agent can't run without the base plugin. Exiting.
    failed.

The base plugin can't bind to LDAP, because the credentials we used to connect
to the LDAP server are wrong. As the base plugin must be activated to use the
MMC agent, the MMC agent exits.

::

    # /etc/init.d/mmc-agent start
    Starting Mandriva Management Console XML-RPC Agent: mmc-agent starting...
    Plugin base loaded, API version: 4:0:0 build(82)
    Plugin mail loaded, API version: 3:0:1 build(78)
    Samba schema are not included in LDAP directory
    Plugin samba not loaded.
    Plugin proxy loaded, API version: 1:0:0 build(78)
    Daemon PID 14010
    done.

In this example, the SAMBA schema has not been detected in the LDAP directory,
so the SAMBA plugin is not started. But this plugin is not mandatory,
so the MMC agent doesn't exit.


Administrator login to the MMC web interface
============================================

You can always login to the MMC web interface using the login « root » with the
LDAP administrator password.

After you installed the MMC, this is the only user you can use to log in,
because the LDAP directory entry is empty.

MMC agent and Python plugins inter-dependencies
===============================================

When the MMC agent starts, it looks for all the installed plugins, and tries to activate them.
Each plugin has a self-test function to check if it can be activated or not. For example, if the « base » plugin can't contact the LDAP, it won't be activated. It the SAMBA schema is not available in the LDAP, the « samba » plugin won't start.

The MMC agent always tries to enable the plugin « base » first. The MMC agent won't start if the plugin « base » can't be activated.

A MMC web module won't show in the web interface if the corresponding Python plugin is not loaded by the contacted MMC agent.

For example, you installed the SAMBA web module, but the SAMBA Python plugin of the MMC agent the web interface is connected to has not been activated.
This will be detected and automatically the SAMBA management module of the web interface won't be displayed.

How to disable a plugin
=======================

In the .ini file corresponding to the plugin (in :file:`/etc/mmc/plugins/`) ,
set « disable = 1 » in the main section.
