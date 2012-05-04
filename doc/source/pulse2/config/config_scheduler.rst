

====================================
Pulse 2 Scheduler configuration file
====================================

This document explains the content of the configuration file of the scheduler
service from Pulse 2.

Introduction
============

The « Scheduler » service is the Pulse 2 daemon in charge of reading the MSC
database, dispatching commands over available launchers and writing results
in the MSC database.

The main service configuration file is :file:`/etc/mmc/pulse2/scheduler/scheduler.ini`.

Optionnaly, the database configuration may also be defined into
:file:`/etc/mmc/plugins/msc.ini`

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

The configuration file is splitted into several sections: Some sections
describing the different available launchers may appear, their name must
begin with ``launcher_``.

- scheduler, daemon, database and logging,
- launchers declaration (describing the different available launchers may
  appear, their name must begin with ``launcher_``).

``scheduler.ini`` available sections:

============ =================================== ===============
Section name Description                         Optional
============ =================================== ===============
scheduler    Mostly scheduler related behaviors  *no*
daemon       Scheduler service related behaviors yes
database     Scheduler database access           yes (see below)
launcher_XXX A way to talk to launcher_XXX       *no*
============ =================================== ===============

All the other sections (loggers, handlers, ...) are related to Python language
logging framework. See http://docs.python.org/lib/logging-config-fileformat.html.

« scheduler » section
---------------------

This section is used to give directives to the scheduler service.

Available options for the "main" section:

======================= ================================================================================================================================================================================================================================================================================== ======================================== ================================== ===============================================
Option name             Description                                                                                                                                                                                                                                                                        Optional                                 Type                               Default value
======================= ================================================================================================================================================================================================================================================================================== ======================================== ================================== ===============================================
id                      This scheduler name, used to take the right jobs in the database.                                                                                                                                                                                                                  *no*                                     string
active_clean_states     Declare which kind of unconsistant states should be fixed. States can be either 'run', 'stop', or both, comma-separated.                                                                                                                                                           yes                                      string
analyse_hour            Once per day, at "analyse_hour" hour (HH:MM:SS), the scheduler will analyse the database, looking to weird / broken commands; set to empty to disable analyse                                                                                                                      yes                                      HH:MM:SS                           "" (disabled)
announce_check          To announce what we are currently try to do on client, for each stage. For example TRANFERT while transfering something: announce_check = transfert=TRANFERT (comma-separated list as for previous options). currently available keywords: transfert, execute, delete, inventory   yes                                      string
awake_time              The scheduler will periodicaly awake (for exemple to poll the database), with this key a specific periodicity can be given.                                                                                                                                                        yes                                      int, seconds                       600 (ten minuts)
cacert                  path to the certificate file describing the certificate authority of the SSL server                                                                                                                                                                                                yes, and used only if *enablessl* is set path                               /etc/mmc/pulse2/scheduler/keys/cacert.pem
clean_state_time        The scheduler will periodicaly awake to hunt for unconsistant command states, with this key a specific periodicity can be given.                                                                                                                                                   yes                                      int                                3600 (one hour)
client_check            comma-separated list of <key>=<value> tokens to ask to the client; value (as part ot the 'target' table' may be name, uuid, ipaddr, mac; only the first value are used for the last two.                                                                                           yes                                      string
checkstatus_period      The period of the loop in charge of checking the scheduler health                                                                                                                                                                                                                  yes                                      int                                900 (15 minutes)
dbencoding              The encoding to use when injecting logs into the MSC database.                                                                                                                                                                                                                     yes                                      string                             utf-8
enablessl               SSL mode support                                                                                                                                                                                                                                                                   yes                                      boolean                            True
initial_wait            The amount of seconds to wait for the system to be stabilized when starting.                                                                                                                                                                                                       yes                                      int                                2 (seconds)
initial_wait            Add a little randomness to some loops. Default value is .2, ie +/- 20 %                                                                                                                                                                                                            yes                                      float                              .2
localcert               path to the SSL server private certificate                                                                                                                                                                                                                                         yes, and used only if *enablessl* is set path                               /etc/mmc/pulse2/scheduler/keys/privkey.pem
host                    This scheduler listing binding IP address                                                                                                                                                                                                                                          yes                                      string                             127.0.0.1
lock_processed_commands Locking system, use with caution ! The only reason to activate this feature is for systems under heavy load; risk of double-preemption is drastically reduced using this, but your system will be even more slow.                                                                  yes                                      boolean                            False
loghealth_period        The period of the loop in charge of logging the scheduler health                                                                                                                                                                                                                   yes                                      int                                60 (1 minute)
max_command_time        Command max authorized time, used by the launcher                                                                                                                                                                                                                                  yes                                      int                                3600 (one hour)
max_upload_time         Upload max authorized time, used by the launcher                                                                                                                                                                                                                                   yes                                      int                                21600 (six hours)
max_slots               The max number of slot to use for all launchers                                                                                                                                                                                                                                    yes                                      int                                300
max_wol_time            WOL wait time                                                                                                                                                                                                                                                                      yes                                      int                                300 (five minuts)
mg_assign_algo          The plugin the scheduler will use to assign a computer to a group. See doc.                                                                                                                                                                                                        yes                                      string                             default (ie. use scheduler/assign_algo/default)
mode                    The scheduler way-of-giving-task-to-its-launchers (see doc).                                                                                                                                                                                                                       yes                                      string                             async
password                The password to use when sending XMLRPC commands to this scheduler.                                                                                                                                                                                                                yes                                      string or base64                   password
port                    This scheduler listing TCP port.                                                                                                                                                                                                                                                   yes                                      int                                8000
preempt_amount          Starting with version 1.2.5, the scheduler will perform this amount of command at a time.                                                                                                                                                                                          yes                                      int                                50
preempt_period          Starting with version 1.2.5, the scheduler will periodicaly perform commands, using this period.                                                                                                                                                                                   yes                                      int                                1
resolv_order            The different means used to find a client on the network (see doc).                                                                                                                                                                                                                yes                                      list of string, separator is space fqdn hosts netbios ip
scheduler_path          The Scheduler main script location, used by scheduler-manager to start and daemonize the service.                                                                                                                                                                                  no                                       path                               /usr/sbin/pulse2-scheduler
server_check            see client_check for option formating, the main differente is that checks are done server-side, not client-side.                                                                                                                                                                   yes                                      string
username                The name to use when sending XMLRPC commands to this scheduler.                                                                                                                                                                                                                    yes                                      string                             username
verifypeer              SSL cert verirfication (if set to True, you will have to build and use a PKI)                                                                                                                                                                                                      yes                                      boolean                            False
======================= ================================================================================================================================================================================================================================================================================== ======================================== ================================== ===============================================

« daemon » section
------------------

This section sets the scheduler service run-time options and privileges.

Available options for the "daemon" section:

=========== ================================================================================================================================================================================================================================== ======== ====== ===============
Option name Description                                                                                                                                                                                                                        Optional Type   Default value
=========== ================================================================================================================================================================================================================================== ======== ====== ===============
group       The scheduler service runs as this specified group.                                                                                                                                                                                yes      group  root
pidfile     The scheduler service PID, used by scheduler-manager to track the scheduler service.                                                                                                                                               yes      path   /var/run/pulse2
umask       The scheduler service umask defines the right of the new files it creates (log files for example).                                                                                                                                 yes      octal  0077
user        The scheduler service runs as this specified user.                                                                                                                                                                                 yes      user   root
setrlimit   Resource usage limits to apply to the scheduler process, specified by a string of triplets (resource, soft limit, hard limit). See the `Python documentation <http://docs.python.org/library/resource.html>`_ for more information yes      string
=========== ================================================================================================================================================================================================================================== ======== ====== ===============

Example:

::

    [daemon]
    pid_path = /var/run/pulse2
    user = mmc
    group = mmc
    umask = 0007
    setrlimit = RLIMIT_NOFILE 2048 2048 RLIMIT_CORE 0 0

« database » section
--------------------

This section can either be defined in ``scheduler.ini``,
or in ``msc.ini`` (in that order).

This section is documented into the :doc:`config_msc`.

« launcher_XXX » section
------------------------

This section define available launchers (one per launcher, "XXX" must be an
integer). By default, no launcher is defined.

Available options for the "launcher_XXX" section:

=========== =================================================================== ======== ================ =============
Option name Description                                                         Optional Type             Default value
=========== =================================================================== ======== ================ =============
enablessl   Flag telling if SSL mode should be used to connect to the launcher. *no*     boolean
host        The launcher IP address.                                            *no*     string
password    The password to use when we send XMLRPC commands to this launcher.  *no*     string or base64
port        The launcher TCP port.                                              *no*     string
username    The name to use when we send XMLRPC commands to this launcher.      *no*     string
=========== =================================================================== ======== ================ =============
