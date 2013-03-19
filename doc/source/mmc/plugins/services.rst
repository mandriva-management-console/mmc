===============
Services plugin
===============

.. note:: The configuration of the services plugin is optionnal

Installation
============

Install the packages ``python-mmc-services`` and ``mmc-web-services``.

.. warning:: This plugin requires systemd.

             If systemd is not available the plugin won't be loaded.

MMC « services » plugin
========================

This plugin allows the administrator to interact with the system services
installed on the server. The plugin uses systemd DBUS interface to interact
with services.

Currently you can start, stop, restart and reload services. You can also
check any service log from the MMC interface.

MMC « services » plugin configuration
=====================================

Like every MMC plugin the configuration can be found in
``/etc/mmc/plugins/services.ini``

The plugin is disabled by default so you need to set ``disable`` to 0.

The plugin uses ``journalctl`` to display services logs in the interface.
Check that the path to journalctl is correct for your system.

The ``blacklist`` option is used to hide any services in the interface. We
don't display the OpenLDAP service because restarting it from the MMC is not
reliable since the MMC depends on it.
