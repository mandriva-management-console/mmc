

=========================================
Pulse 2 Package server configuration file
=========================================

This document explains the content of the configuration file of the package
server service from Pulse 2.

Introduction
============

The « package server » service is the Pulse 2 daemon that implement all the
package apis, it permit the creation, edition, suppression, share,
mirroring... of packages.

The service configuration file is
:file:`/etc/mmc/pulse2/package-server/package-server.ini`.

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

The section describing the ``mirror``, ``package_api_get`` or
``package_api_put`` can be duplicated if you need to have more than one api of
this kind.

================== ============================================== ========
Section name       Description                                    Optional
================== ============================================== ========
main               Common package server configuration directives no
daemon             Package server daemon related behaviors        yes
ssl                Package server ssl related dehaviors           yes
mirror_api                                                        yes
user_package_api                                                  yes
scheduler_api                                                     yes
imaging_api                                                       yes
mirror:XX                                                         yes
package_api_get:XX                                                yes
package_api_put:XX                                                yes
================== ============================================== ========

All the other sections (loggers, handlers, ...) are related to Python language
logging framework. See http://docs.python.org/lib/logging-config-fileformat.html.

« main » section
----------------

This section is used to configure the inventory server services.

Available options for the "main" section:

========================================== ============================================================================================================================================================================== ======== ====================
Option name                                Description                                                                                                                                                                    Optional Default value
========================================== ============================================================================================================================================================================== ======== ====================
host                                       The hostname or ip address where the inventory.                                                                                                                                yes      localhost
port                                       The port on which the inventory listen.                                                                                                                                        yes      9999
use_iocp_reactor                           *Windows XP, Windows 2003 and Windows 2008 only.* This option sets the Twisted event loop to use the IOCP reactor for better performance. Please read :ref:`win32-perf`        yes      0
package_detect_activate                    Is package autodetection activated                                                                                                                                             yes      0
package_detect_loop                        Time between two loops of detection                                                                                                                                            yes      60
package_detect_smart_method                methods in none, last_time_modification, check_size; for more than 1 method, separate with ","                                                                                 yes      none
package_detect_smart_time                                                                                                                                                                                                 yes      60
package_mirror_loop                                                                                                                                                                                                       yes      5
package_mirror_target                      Package api can synhronise package data to others servers; package synchronisation targets                                                                                     yes
package_mirror_status_file                 package synchronisation state file. used only if package_mirror_target is defined. File where pending sync are written so that they can be finished on package server restart. yes      /var/data/mmc/status
package_mirror_command                     package synchronisation command to use                                                                                                                                         yes      /usr/bin/rsync
package_mirror_command_options             package synchronisation command options                                                                                                                                        yes      -ar --delete
package_mirror_level0_command_options      package synchronisation command on only one level options                                                                                                                      yes      -d --delete
package_mirror_command_options_ssh_options options passed to SSH via "-o" if specified --rsh is automatically added to package_mirror_command_options                                                                     yes      ""
package_global_mirror_activate             loop for the sync of the whole package directory; can only be activated when package_mirror_target is given                                                                    yes      1
package_global_mirror_loop                                                                                                                                                                                                yes      3600
package_global_mirror_command_options                                                                                                                                                                                     yes      -ar --delete
real_package_deletion                      real package deletion                                                                                                                                                          yes      0
mm_assign_algo                             machine/mirror assign algo                                                                                                                                                     yes      default
up_assign_algo                             user/packageput assign algo                                                                                                                                                    yes      default
========================================== ============================================================================================================================================================================== ======== ====================

``package_mirror_command_options_ssh_options`` can be for exemple :

::

    IdentityFile=/root/.ssh/id_rsa StrictHostKeyChecking=no Batchmode=yes PasswordAuthentication=no ServerAliveInterval=10 CheckHostIP=no ConnectTimeout=10

« daemon » section
------------------

This section sets the package server service run-time options and privileges.

Available options for the "daemon" section:

=========== ================================================================================================== ======== ==================================
Option name Description                                                                                        Optional Default value
=========== ================================================================================================== ======== ==================================
pidfile     The package server service store its PID in the given file.                                        yes      /var/run/pulse2-package-server.pid
user        The inventory service runs as this specified user.                                                 yes      root
group       The inventory service runs as this specified group.                                                yes      root
umask       The inventory service umask defines the right of the new files it creates (log files for example). yes      0077
=========== ================================================================================================== ======== ==================================

« ssl » section
---------------

Available options for the "ssl" section:

=========== =================================================================================== ======== ===============================================
Option name Description                                                                         Optional Default value
=========== =================================================================================== ======== ===============================================
username                                                                                        yes      ""
password                                                                                        yes      ""
enablessl   SSL mode support                                                                    yes      1
verifypeer  use SSL certificates                                                                yes      0
cacert      path to the certificate file describing the certificate authority of the SSL server yes      /etc/mmc/pulse2/package-server/keys/cacert.pem
localcert   path to the SSL server private certificate                                          yes      /etc/mmc/pulse2/package-server/keys/privkey.pem
=========== =================================================================================== ======== ===============================================

« mirror_api » section
----------------------

This section define options for the mirror_api api implementation
(it assign mirrors and package_api to machines).

Available options for the "mirror_api" section:

=========== =================== ======== =============
Option name Description         Optional Default value
=========== =================== ======== =============
mount_point The api mount point no       /rpc
=========== =================== ======== =============

« user_package_api » section
----------------------------

This section define options for the user_package_api api implementation
(it assign package_api to users, it's used for the package edition permissions).

Available options for the "user_package_api" section:

=========== =================== ======== =============
Option name Description         Optional Default value
=========== =================== ======== =============
mount_point The api mount point no       /upaa
=========== =================== ======== =============

« scheduler_api » section
-------------------------

This section define options for the scheduler_api api implementation
(it assign a scheduler to each machine).

Available options for the "scheduler_api" section:

=========== ================================================ ======== ==============
Option name Description                                      Optional Default value
=========== ================================================ ======== ==============
mount_point The api mount point                              no       /scheduler_api
schedulers  The possible schedulers (can be a url or an id). no
=========== ================================================ ======== ==============

« imaging_api » section
-----------------------

This section define options for the imaging API.

Available options for the "imaging_api" section:

================== ======================================================================================================================= ======== ============================
Option name        Description                                                                                                             Optional Default value
================== ======================================================================================================================= ======== ============================
mount_point        The API mount point                                                                                                     yes      /imaging_api
uuid               The package server UUID. You can use the uuidgen command to compute one.                                                no
base_folder        Base folder where Pulse 2 imaging sub directories are contained.                                                        yes      /var/lib/pulse2/imaging
bootloader_folder  Where bootloader (and bootsplash) is stored, relative to "base_folder"                                                  yes      bootloader
cdrom_bootloader   The CD-ROM boot loader file. It is used to create bootable restoration CD/DVD.                                          yes      cd_boot
bootsplash_file    The imaging menu (GRUB menu) backgroung image, in XPM format.                                                           yes      bootsplash.xpm
bootmenus_folder   Where boot menus are generated / being served, relative to "base_folder"                                                yes      bootmenus
diskless_folder    Where kernel, initrd and other official diskless tools are stored, relative to "base_folder"                            yes      diskless
diskless_kernel    Name of the diskless kernel to run, relative to "diskless_folder"                                                       yes      kernel
diskless_initrd    Name of the diskless initrd to boot (core), relative to "diskless_folder"                                               yes      initrd
diskless_initrdcd  Name of the diskless initrd to boot (add on to boot on CD), relative to "diskless_folder"                               yes      initrdcd
diskless_memtest   Diskless memtest too, relative to "diskless_folder"                                                                     yes      initrdcd
inventories_folder Where inventories are stored / retrieved, relative to "base_folder"                                                     yes      inventories
computers_folder   Where additionnal material (hdmap, exclude) are stored / retrieved, relative to "base_folder"                           yes      computers
masters_folder     Where images are stored, relative to "base_folder"                                                                      yes      masters
postinst_folder    Where postinst tools are stored, relative to "base_folder"                                                              yes      postinst
archives_folder    Will contain archived computer imaging data, relative to "base_folder"                                                  yes      archives
isos_folder        Will contain generated ISO images                                                                                       yes      /var/lib/pulse2/imaging/isos
isogen_tool        tool used to generate ISO file                                                                                          yes      /usr/bin/mkisofs
rpc_replay_file    File contained in "base_folder" where failed XML-RPC calls from the package server to the central MMC agent are stored. yes      rpc-replay.pck
rpc_loop_timer     RPC replay loop timer in seconds. The XML-RPC are sent again to the central MMC agent at each loop.                     yes      60
rpc_count          RPC to replay at each loop.                                                                                             yes      10
rpc_interval       Interval in seconds between two RPCs                                                                                    yes      2
uuid_cache_file    Our UUID cache *inside* "base_folder"                                                                                   yes      uuid-cache.txt
================== ======================================================================================================================= ======== ============================

« mirror:XX » section
---------------------

This section define options for the mirror api implementation.

Available options for the ``mirror:XX`` section:

=========== ================================== ======== =============
Option name Description                        Optional Default value
=========== ================================== ======== =============
mount_point The api mount point                no
src         The root path of the package tree. no
=========== ================================== ======== =============

« package_api_get:XX » section
------------------------------

This section define options for the ``package_api_get`` API implementation.

Available options for the ``package_api_get:XX`` section:

=========== ================================== ======== =============
Option name Description                        Optional Default value
=========== ================================== ======== =============
mount_point The api mount point                no
src         The root path of the package tree. no
=========== ================================== ======== =============

« package_api_put:XX » section
------------------------------

This section define options for the ``package_api_put`` API implementation.

Available options for the ``package_api_put:XX`` section:

============= ======================================================== ======== =============
Option name   Description                                              Optional Default value
============= ======================================================== ======== =============
mount_point   The api mount point                                      no       /rpc
src           The root path of the package tree.                       no
tmp_input_dir The directory where the data for package creation is put yes
============= ======================================================== ======== =============

.. _win32-perf:

Pulse 2 package server performance on win32 platforms
=====================================================

Using the default configuration, the service won't accept more than
64 concurrent TCP connections. The default event loop used by the Python
Twisted library use the select() system call, which is limited to waiting on
64 sockets at a time on Windows.

Fortunately Twisted allows to choose another reactor instead of the default
select() one. If sets to 1 in the package server configuration file,
the ``use_iocp_reactor`` option lets Twisted runs with the IOCP reactor.
IOCP (IO completions Ports) is a fast and scalable event loop system available
on win32 platform. More informations are available in
`the Twisted documentation <http://twistedmatrix.com/projects/core/documentation/howto/choosing-reactor.html>`_.

But there are some limitations:

- SSL is not supported (for the moment) by the IOCP reactor, so the package
  server can't be run with IOCP and SSL enabled at the same time,
- The IOCP reactor implementation from Twisted only works on win32 platform
  where the ConnectEx() API is available. So it won't works on Windows NT
  and Windows 2000 platforms.

Using the IOCP reactor, the package server can handle at least 300 parallel
TCP connections, but more benchmarks need to be done to guess its limits.
