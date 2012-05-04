

=========================================
Pulse 2 Imaging Server configuration file
=========================================

This document explains the content of the configuration file of the imaging
server service from Pulse 2.

Introduction
============

The « imaging server » service is the Pulse 2 daemon in charge of managing
backup folder on the server, based on the clients needs.

The service configuration file is :file:`/etc/mmc/pulse2/imaging-server/imaging-server.ini`.

Like all Pulse 2 related configuration file, its file format is INI style.
The file is made of sections, each one starting with a « [sectionname] » header.
In each section options can be defined like this: « option = value ».

For example:

::

    [section1]
    option1 = 1
    option2 = 2

    [section2]
    option1 = foo
    option2 = plop

Configuration file sections
===========================

For now four sections are available in this configuration file.
The section describing the option can be duplicated if you need to pass
more than one kind of option to the OCS inventory agent.


============ ============================================== ========
Section name Description                                    Optional
============ ============================================== ========
main         Common imaging server configuration directives *no*
daemon       Imaging server daemon related behaviors        *no*
helpers      Imaging server hooks                           *no*
logger       Logging setting                                *no*
============ ============================================== ========

« main » section
----------------

This section is used to configure the imaging server services.

Available options for the "main" section:

============== =============================================================== ======== ====== ============================
Option name    Description                                                     Optional Type   Default value
============== =============================================================== ======== ====== ============================
adminpass      The password to be used when subscibing to this Pulse 2 server. *no*     string mandriva
base_folder    Where the images will be recorded                               *no*     path   /var/lib/pulse2/imaging
host           The IP address on which the server will listen                  *no*     string 0.0.0.0
netboot_folder Where the PXE elements will be taken from                       *no*     path   /var/lib/tftpboot/pulse2
port           The port on which the server will listen.                       *no*     int    1001
skel_folder    Where the original image template will be taken from            *no*     path   /usr/lib/pulse2/imaging/skel
============== =============================================================== ======== ====== ============================

« daemon » section
------------------

This section sets the imaging service run-time options and privileges.

Available options for the "daemon" section:

=========== ================================================================================================== ======== ====== ==================================
Option name Description                                                                                        Optional Type   Default value
=========== ================================================================================================== ======== ====== ==================================
group       The inventory service runs as this specified group.                                                *no*     string root
pidfile     The inventory service store its PID in the given file.                                             *no*     path   /var/run/pulse2-imaging-server.pid
umask       The inventory service umask defines the right of the new files it creates (log files for example). *no*     octal  0077
user        The inventory service runs as this specified user.                                                 *no*     string root
=========== ================================================================================================== ======== ====== ==================================

« helpers » section
-------------------

This section sets the imaging service hooks.

Available options for the "daemon" section:

===================== ===================================== ======== ==== =================================================
Option name           Description                           Optional Type Default value
===================== ===================================== ======== ==== =================================================
client_add_path       The client_add script path            *no*     path /usr/lib/pulse2/imaging/helpers/check_add_host
client_remove_path    The client_remove script path         *no*     path /usr/lib/pulse2/imaging/helpers/check_remove_host
client_inventory_path The client_inventory_path script path *no*     path /usr/lib/pulse2/imaging/helpers/info
menu_reset_path       The menu_reset_path script path       *no*     path /usr/lib/pulse2/imaging/helpers/set_default
menu_update_path      The menu_update_path script path      *no*     path /usr/lib/pulse2/imaging/helpers/update_menu
storage_create_path   The storage_create_path script path   *no*     path /usr/lib/pulse2/imaging/helpers/create_config
storage_update_path   The storage_update_path script path   *no*     path /usr/lib/pulse2/imaging/helpers/update_dir
===================== ===================================== ======== ==== =================================================

« logger » section
------------------

This section sets the logging system.

Available options for the "daemon" section:

============= ============ ======== ==== ======================================
Option name   Description  Optional Type Default value
============= ============ ======== ==== ======================================
log_file_path The log path *no*     path /var/log/mmc/pulse2-imaging-server.log
============= ============ ======== ==== ======================================
