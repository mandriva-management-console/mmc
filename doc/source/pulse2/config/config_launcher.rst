

===================================
Pulse 2 Launcher configuration file
===================================

This document explains the content of the configuration file of the launcher
service from Pulse 2.

Introduction
============

The « Launcher » service is the Pulse 2 daemon in charge of doing jobs on
clients on scheduler orders.

The service configuration file is :file:`/etc/mmc/pulse2/launchers.ini`
(please note the ending "s").

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

Some sections describing the different available launchers may appear, their
name must begin with ``launcher_``. The idea behind this is that the main
section controls the common behavior of launchers, the others control the
specific behaviors.

============= ========================================= ========
Section name  Description                               Optional
============= ========================================= ========
launchers     Common launchers configuration directives yes
wrapper       wrapper related options                   yes
ssh           ssh modus-operandi related section        yes
daemon        Launchers services related behaviors      yes
wol           WOL related behaviors                     yes
wget          Wget related options                      yes
tcp_sproxy    Wget related options                      yes
smart_cleaner Smart cleaning options                    yes
scheduler_XXX Referent scheduler location               no
launcher_XXX  Configuration for launcher_XXX            no
============= ========================================= ========

All the other sections (loggers, handlers, ...) are related to Python language
logging framework. See http://docs.python.org/lib/logging-config-fileformat.html.

« launchers » section
---------------------

This section is used to give directives common to every launcher service.

Available options for the "launchers" section:

================== ======================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================= ======== ============ =================================================================================================================================================================================================================================================================================================
Option name        Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             Optional Type         Default value
================== ======================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================= ======== ============ =================================================================================================================================================================================================================================================================================================
halt_command       The halt command to use on a client, after a successful deployment.                                                                                                                                                                                                                                                                                                                                                                                                                                                                     yes      string       /bin/shutdown.exe -f -s 1 \|| shutdown -h now
inventory_command  The inventory command to use on a client, after a successful deployment.                                                                                                                                                                                                                                                                                                                                                                                                                                                                yes      string       export PULSE2_SERVER=`echo $SSH_CONNECTION | cut -f1 -d\\ \`; export PULSE2_PORT=21999; /cygdrive/c/Program\\ Files/OCS\\ Inventory\\ Agent/OCSInventory.exe /server:$PULSE2_SERVER /pnum:$PULSE2_PORT /debug \|| /usr/bin/ocsinventory-agent --server=http://$PULSE2_SERVER:$PULSE2_PORT --debug
launcher_path      The Launcher main script location, used by launchers-manager to start and daemonize the services.                                                                                                                                                                                                                                                                                                                                                                                                                                       yes      path         /usr/sbin/pulse2-launcher
max_command_age    The parameter which limits a command's time lenght. A command must take less than this value (in seconds), or being killed; High values mean that the command will have more time to complete, thus may also stay blocked longer. Only works for ASYNC commands.                                                                                                                                                                                                                                                                        yes      int, seconds 86400 (one day)
max_ping_time      Timeout when attempting to ping a client: A ping is aborded if it takes more that this value (in seconds). High values will minimize false-positives (aborded probe even if the client if obviously reachable). Lower values will enhance interface reponse time (but lead to more false-positives).                                                                                                                                                                                                                                    yes      int, seconds 4 (seconds)
max_probe_time     Timeout when attempting to probe a client: A probe is aborded if it takes more that this value (in seconds). High values will minimize false-positives (aborded probe even if the client if obviously reachable). Lower values will enhance interface reponse time (but lead to more false-positives). Please note that even if the client is not far (less than 10 ms), the probe may last a very long ime as sshd perform a reverse DNS query for each incoming connection, which may be problematic with a badly configured DNS.     yes      int, seconds 20 (seconds)
ping_path          Path to Pulse 2 Ping tool                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               yes      path         /usr/sbin/pulse2-ping
reboot_command     The reboot command to use on a client, after a successful deployment.                                                                                                                                                                                                                                                                                                                                                                                                                                                                   yes      string       /bin/shutdown.exe -f -r 1 \|| shutdown -r now
source_path        Packages source path target path (used for upload purpose).                                                                                                                                                                                                                                                                                                                                                                                                                                                                             yes      path         /var/lib/pulse2/packages
target_path        Client target path (used for upload purpose).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           yes      path         /tmp
temp_folder_prefix During a deployment, if a folder has to be created, its name will begin by this string.                                                                                                                                                                                                                                                                                                                                                                                                                                                 yes      string       MDVPLS
================== ======================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================================= ======== ============ =================================================================================================================================================================================================================================================================================================


« daemon » section
------------------

This section sets the pulse2-launchers-manager and pulse2-launchers service
run-time options and privileges.

Available options for the "daemon" section:

=========== ======================================================================================================================================== ======== ===== ===============
Option name Description                                                                                                                              Optional Type  Default value
=========== ======================================================================================================================================== ======== ===== ===============
group       The pulse2-launchers-manager and pulse2-launchers services run as this specified group.                                                  yes      group root
pidfile     The launcher services PID, used by pulse2-launchers-manager to track the launchers services.                                             yes      path  /var/run/pulse2
umask       The pulse2-launchers-manager and pulse2-launchers services umask defines the right of the new files they create (log files for example). yes      octal 0077
user        The pulse2-launchers-manager and pulse2-launchers service run as this specified user.                                                    yes      user  root
=========== ======================================================================================================================================== ======== ===== ===============

« wrapper » section
-------------------

This section define the wrapper behavior.

Available options for the "wrapper" section:

============= ===================================================================================================================================================================================== ======== =============== ===============================
Option name   Description                                                                                                                                                                           Optional Type            Default value
============= ===================================================================================================================================================================================== ======== =============== ===============================
max_exec_time Default max exec time in seconds, older process are killed using SIGKILL. Different from max_command_age as beeing handled by the wrapper itself, so it also works for SYNC commandS. yes      int, in seconds 21600 (6 hours)
max_log_size  Cap generated logs to this value                                                                                                                                                      yes      int, in bytes   512000 (500 kB)
path          Pulse 2 launcher wrapper (ie "job launcher") location.                                                                                                                                yes      path            /usr/sbin/pulse2-output-wrapper
============= ===================================================================================================================================================================================== ======== =============== ===============================

« ssh » section
---------------

This section define global ssh (and scp) options.

Available options for the "ssh" section:

============== ======================================================================================================================================================================================================================================================= ======== =============================== ====================================================================================================================================================================
Option name    Description                                                                                                                                                                                                                                             Optional Type                            Default value
============== ======================================================================================================================================================================================================================================================= ======== =============================== ====================================================================================================================================================================
default_key    The default SSHv2 key to use, the config code will look for an "ssh_<default_key>" entry in the config file. ssh_* are ssh keys, * her names, f.ex. by using sshkey_default = /root/.ssh/id_rsa, /root/.ssh/id_rsa will be known as the 'default' key.  yes      string                          default
forward_key    Should we perform key-forwarding (never, always, or let = let the scheduler take its decision)                                                                                                                                                          yes      string                          let
scp_path       Path to the SCP binary                                                                                                                                                                                                                                  yes      string                          /usr/bin/scp
ssh_options    Options passed to OpenSSH binary (-o option).                                                                                                                                                                                                           yes      list of space separated strings LogLevel=ERROR UserKnownHostsFile=/dev/null StrictHostKeyChecking=no Batchmode=yes PasswordAuthentication=no ServerAliveInterval=10 CheckHostIP=no ConnectTimeout=10
ssh_agent_path Path to the SSH agent                                                                                                                                                                                                                                   yes      string                          /usr/bin/ssh-agent
ssh_path       Path to the SSH binary                                                                                                                                                                                                                                  yes      string                          /usr/bin/ssh
sshkey_default The "default" ssh key path.                                                                                                                                                                                                                             yes      path                            /root/.ssh/id_rsa
sshkey_XXXX    The "XXXX" ssh key path (when more than one key may be used).                                                                                                                                                                                           yes      string
============== ======================================================================================================================================================================================================================================================= ======== =============================== ====================================================================================================================================================================

« wget » section
----------------

This section sets the pulse2-launchers wget options
(for the pull part of the push/pull mode)

Available options for the "wget" section:

============ ================================================ ======== ======= =============
Option name  Description                                      Optional Type    Default value
============ ================================================ ======== ======= =============
check_certs  Put the check certificate flag.                  yes      boolean False
resume       Attempt to resume a partialy completed transfert yes      boolean True
wget_options Options passed to wget binary.                   yes      string  ""
wget_path    wget binary path (on client)                     yes      string  /usr/bin/wget
============ ================================================ ======== ======= =============

« rsync » section
-----------------

This section sets the pulse2-launchers rsync options (for the push mode)

Available options for the "rsync" section:

============== =================================================================================== ======== ======= ==============
Option name    Description                                                                         Optional Type    Default value
============== =================================================================================== ======== ======= ==============
resume         Attempt to resume a partial completed transfert                                     yes      boolean True
rsync_path     rsync binary path (on server)                                                       yes      string  /usr/bin/rsync
set_executable Do we force +/-X on uploaded files (yes/no/keep). See below.                        yes      string  yes
set_access     Do we enforce permissions of uploaded files (private/restricted/public). See below. yes      string  private
============== =================================================================================== ======== ======= ==============

Uploaded file permissions:

============================ ================ ============= ================
set_access \\ set_executable yes              no            keep
private                      u=rwx,g=,o=      u=rw,g=,o=    u=rwX,g=,o=
restricted                   u=rwx,g=rx,o=    u=rw,g=r,o=   u=rwX,g=rX,o=
public                       u=rwx,g=rwx,o=rx u=rw,g=rw,o=r u=rwX,g=rwX,o=rX
============================ ================ ============= ================

« wol » section
---------------

This section sets the wol feature handling.

Available options for the "wol" section:

=========== ================================================== ======== ====== ====================
Option name Description                                        Optional Type   Default value
=========== ================================================== ======== ====== ====================
wol_bcast   WOL IP BCast adress.                               yes      string 255.255.255.255
wol_path    Pulse 2 scheduler awaker (via WOL "magic packet"). yes      path   /usr/sbin/pulse2-wol
wol_port    WOL TCP port.                                      yes      string 40000
=========== ================================================== ======== ====== ====================

« tcp_sproxy » section
----------------------

This section sets the tcp_sproxy feature handling, mainly used by the VNC feature.

Available options for the "tcp_sproxy" section:

========================== ================================================================================================================================================================================================================================== ======== ========= ===========================
Option name                Description                                                                                                                                                                                                                        Optional Type      Default value
========================== ================================================================================================================================================================================================================================== ======== ========= ===========================
tcp_sproxy_path            Pulse 2 TCP Secure Proxy (woot !) path                                                                                                                                                                                             yes      path      /usr/sbin/pulse2-tcp-sproxy
tcp_sproxy_host            Fill-in the following option if you plan to use VNC, it will be the "external" IP from the VNC client point-of-view                                                                                                                yes      string    ""
tcp_sproxy_port_range      The proxy uses a port range to establish proxy to the client: 2 ports used per connection                                                                                                                                          yes      int range 8100-8200
tcp_sproxy_establish_delay The initial ssh connection to the client timeout                                                                                                                                                                                   yes      seconds   20
tcp_sproxy_connect_delay   The proxy allow the initial connection to be established within N seconds (ie. a client as N seconds to connect to the proxy after a port has bee found, then the connection is dropped and further connections will be impossible yes      seconds   60
tcp_sproxy_session_lenght  The number of seconds a connection will stay open after the initial handshake, conenction will be closed after this delay even if still in use                                                                                     yes      seconds   3600 (one hour)
========================== ================================================================================================================================================================================================================================== ======== ========= ===========================

« smart_cleaner » section
-------------------------

This section sets the wol feature handling.

Available options for the "wol" section:

===================== ========================================================= ======== ====================== ================================
Option name           Description                                               Optional Type                   Default value
===================== ========================================================= ======== ====================== ================================
smart_cleaner_path    Pulse 2 smart cleaner path (on client), not used if empty yes      path                   /usr/bin/pulse2-smart-cleaner.sh
smart_cleaner_options Pulse 2 smart cleaner option (see win32 agent doc)        yes      array, space-separated ''
===================== ========================================================= ======== ====================== ================================

« scheduler_XXX » section
-------------------------

This section define how the launchers may reach their referent scheduler.

Available options for the "scheduler" section:

======================== =========================================================================================================================================================================================================================================================================================================================================================== ======== ================ =============
Option name              Description                                                                                                                                                                                                                                                                                                                                                 Optional Type             Default value
======================== =========================================================================================================================================================================================================================================================================================================================================================== ======== ================ =============
awake_incertitude_factor As our awake_time can be the same that the scheduler awake_time, add a little randomness here. Default value is .2, ie +/- 20 %. For example we will awake every 10 minutes, more or less 2 minutes. Values lower than 0 or greater than .5 are rejected Use this if your scheduler has the same awake time and busy each time we have to send our results  yes      float            .2
awake_time               The launcher will periodicaly awake (for exemple to send results to is scheduler), with this key a specific periodicity can be given. Field unit is the "second".                                                                                                                                                                                           yes      int              600
defer_results            In async mode, whenever immedialetly send results to referent scheduler upon job completion or wait for being waked up (see above)                                                                                                                                                                                                                          yes      string           no
enablessl                Flag that tells if SSL should be used to connect to the scheduler                                                                                                                                                                                                                                                                                           yes      boolean          True
host                     The referent scheduler IP address                                                                                                                                                                                                                                                                                                                           yes      string           127.0.0.1
password                 The password to use when authenticating vs our referent scheduler                                                                                                                                                                                                                                                                                           yes      string or base64 password
port                     The referent scheduler TCP port                                                                                                                                                                                                                                                                                                                             yes      string           8000
username                 The login name to use when authenticating vs our referent scheduler                                                                                                                                                                                                                                                                                         yes      string           username
======================== =========================================================================================================================================================================================================================================================================================================================================================== ======== ================ =============

« launcher_XXX » section
------------------------

This section define specific options for all launchers on the server.

Available options for the "launcher_XXX" section:

=========== ============================================================================================================================================================================================================================================================================================================= ======================== ================ ==========================================
Option name Description                                                                                                                                                                                                                                                                                                   Optional                 Type             Default value
=========== ============================================================================================================================================================================================================================================================================================================= ======================== ================ ==========================================
bind        The launcher binding IP address.                                                                                                                                                                                                                                                                              yes                      string           127.0.0.1
cacert      path to the certificate file describing the certificate authority of the SSL server                                                                                                                                                                                                                           *no if enablessl is set* path             /etc/mmc/pulse2/scheduler/keys/cacert.pem
certfile    deprecated (see cacert)
enablessl   SSL mode support                                                                                                                                                                                                                                                                                              *no*                     boolean          1
localcert   path to the SSL serverprivate certificate                                                                                                                                                                                                                                                                     *no if enablessl is set* path             /etc/mmc/pulse2/scheduler/keys/privkey.pem
password    The password to use when authenticating vs this launcher                                                                                                                                                                                                                                                      yes                      string or base64 password
port        The launcher binding TCP port.                                                                                                                                                                                                                                                                                *no*                     int
privkey     deprecated (see localcert)
slots       The number of available slots (ie. maximum number of concurrent jobs)                                                                                                                                                                                                                                         yes                      int              300
scheduler   The referent scheduler                                                                                                                                                                                                                                                                                        yes                      string           the first defined scheduler
username    The login name to use when authenticating vs this launcher                                                                                                                                                                                                                                                    yes                      string           username
verifypeer  Check that our parent scheduler present a signed certificate                                                                                                                                                                                                                                                  *no if enablessl is set* boolean          False
logconffile path to the file containing the logging configuration of this launcher (the format of this file is described in the `Python documentation <http://docs.python.org/library/logging.html#configuration-file-format>`_. If it is not set, the default logging configuration is read from the launchers.ini file. yes                      string
=========== ============================================================================================================================================================================================================================================================================================================= ======================== ================ ==========================================
