

=================================
MMC MSC plugin configuration file
=================================

This document explains the content of the MMC MSC plugin configuration file.

Introduction
============

The « MSC » plugin is the MMC plugin in charge of recording commands in the
MSC database, and gathering results from the database.

The plugin configuration file is :file:`/etc/mmc/plugins/msc.ini`.

Like all MMC related configuration file, its file format is INI style. The
file is made of sections, each one starting with a « [sectionname] » header.
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

For now five sections are available in this configuration file:

============ ================================================== ========
Section name Description                                        Optional
============ ================================================== ========
main         Mostly MMC related behaviors                       yes
msc          MSC related options                                yes
web          Web interface default options                      yes
package_api  Describe how to reach the API package service      yes
schedulers   Describe how to reach the different MSC Schedulers yes
============ ================================================== ========

« main » section
----------------

This section is used to give directives to the MMC agent.

Available options for the "main" section:

=========== ================================= ======== =============
Option name Description                       Optional Default value
=========== ================================= ======== =============
disable     Whenever use this plugin (or not) yes      1
=========== ================================= ======== =============

« msc » section
---------------

This section defines some global options.

Available options for the "msc" section:

======================= ========================================================================================================================================================================================================================================================================================================================== ======== ===============================
Option name             Description                                                                                                                                                                                                                                                                                                                Optional Default value
======================= ========================================================================================================================================================================================================================================================================================================================== ======== ===============================
qactionpath             Folder from where Quick Action scripts are tacken                                                                                                                                                                                                                                                                          yes      /var/lib/pulse2/qactions
repopath                Folder from where packages will be copied (push mode)                                                                                                                                                                                                                                                                      yes      /var/lib/pulse2/packages
dbdriver                DB driver to use                                                                                                                                                                                                                                                                                                           yes      mysql
dbhost                  Host which hosts the DB                                                                                                                                                                                                                                                                                                    yes      127.0.0.1
dbport                  Port on which to connect to reach the DB                                                                                                                                                                                                                                                                                   yes      3306 (aka "default MySQL port")
dbname                  DB name                                                                                                                                                                                                                                                                                                                    yes      msc
dbuser                  Username to give while conencting to the DB                                                                                                                                                                                                                                                                                yes      msc
dbpasswd                Password to give while connecting to the DB                                                                                                                                                                                                                                                                                yes      msc
dbdebug                 Whenever log DB related exchanges                                                                                                                                                                                                                                                                                          yes      ERROR
dbpoolrecycle           DB connection time-to-live                                                                                                                                                                                                                                                                                                 yes      60 (seconds)
default scheduler       default scheduler to use                                                                                                                                                                                                                                                                                                   yes
ignore_non_rfc2780      Enable filter for non unicast IP addresses when inserting computers IP address in MSC database                                                                                                                                                                                                                             yes      1
ignore_non_rfc1918      Enable filter for non private IP addresses when inserting computers IP address in MSC database                                                                                                                                                                                                                             yes      0
exclude_ipaddr          Enable filter made of comma separated values with filtered ip addresses or network ranges, used when inserting computers IP address in MSC database. For example: exclude_ipaddr = 192.168.0.1,10.0.0.0/10.255.255.255                                                                                                     yes
include_ipaddr          Disable filter made of comma separated values with accepted ip addresses or network ranges, used when inserting computers IP address in MSC database. The IP addresses matching this filter are always accepted and never take out by the other filters. For example: include_ipaddr = 192.168.0.1,10.0.0.0/10.255.255.255 yes
ignore_non_fqdn         Enable filter for host name that are not FQDN. If filtered, the host name won't be used by the scheduler to find the target IP address                                                                                                                                                                                     yes      0
ignore_invalid_hostname Enable filter for host name that are invalid (that contains forbidden characters). If filtered, the host name won't be used by the scheduler to find the target IP address.                                                                                                                                                yes      0
exclude_hostname        Enable filter for host name that are invalid if they match a regexp from this list of regexp. If filtered, the host name won't be used by the scheduler to find the target IP address. For example: exclude_hostname = computer[0-9]* server[0-9]*                                                                         yes
include_hostname        The host names matching at least one regexp from this list of regexp will never be filtered. For example: For example: include_hostname = computer[0-9]* server[0-9]*                                                                                                                                                      yes
wol_macaddr_blacklist   Space separated regexps to match MAC address to filter when inserting a target for a command into the database. For example: wol_macaddr_blacklist = 12:.* 00:.*                                                                                                                                                           yes
======================= ========================================================================================================================================================================================================================================================================================================================== ======== ===============================

« scheduler_XXX » section
-------------------------

This section define available schedulers (one per scheduler,
"XXX" must be an integer).

Available options for the "scheduler_XXX" section:

=========== =================================================================== ======== =============
Option name Description                                                         Optional Default value
=========== =================================================================== ======== =============
host        The scheduler IP address.                                           yes      127.0.0.1
port        The scheduler TCP port.                                             yes      8000
enablessl   Flag that tells if SSL should be used to connect to the scheduler   yes      1
username    The name to use when we send XMLRPC commands to this scheduler.     yes      username
password    The password to use when we send XMLRPC commands to this scheduler. yes      password
=========== =================================================================== ======== =============

By default, a scheduler is always defined:

::

    [scheduler_01]
    host=127.0.0.1
    port=8000
    username = username
    password = password
    enablessl = 1

« web » section
---------------

This section defined some default web fields.

Available options for the "main" section:

============================= =============================================================================================================================================================================================================================================================================================================================================================================================================================== ======== =============
Option name                   Description                                                                                                                                                                                                                                                                                                                                                                                                                     Optional Default value
============================= =============================================================================================================================================================================================================================================================================================================================================================================================================================== ======== =============
web_def_awake                 Check "Do WOL on client" ?                                                                                                                                                                                                                                                                                                                                                                                                      yes      1
web_def_inventory             Check "Do inventory on client" ?                                                                                                                                                                                                                                                                                                                                                                                                yes      1
web_def_mode                  Fill default package send mode                                                                                                                                                                                                                                                                                                                                                                                                  yes      push
web_def_maxbw                 Fill default max usable bw                                                                                                                                                                                                                                                                                                                                                                                                      yes      0
web_def_delay                 Fill delay between two attempts                                                                                                                                                                                                                                                                                                                                                                                                 yes      60
web_def_attempts              Fill max number of attempts                                                                                                                                                                                                                                                                                                                                                                                                     yes      3
web_def_deployment_intervals  Fill deployment time window                                                                                                                                                                                                                                                                                                                                                                                                     yes
web_dlpath                    Directory of target computers from which a file is download when a user perform the download file action in the computers list. If empty, the download file action is not available on the web interface.                                                                                                                                                                                                                       yes
web_def_dlmaxbw               Max bandwidth to use when download a file from a computer. Set to 0 by default. If set to 0, there is no bandwidth limit applied.                                                                                                                                                                                                                                                                                               yes      0
web_def_local_proxy_mode      Default proxy mode, defaut "multiple", other possible value "single".                                                                                                                                                                                                                                                                                                                                                           yes      multiple
web_def_max_clients_per_proxy Max number of clients per proxy in proxy mode.                                                                                                                                                                                                                                                                                                                                                                                  yes      10
web_def_proxy_number          Number of auto-selected proxy in semi-auto mode.                                                                                                                                                                                                                                                                                                                                                                                yes      2
web_def_proxy_selection_mode  Default mode (semi_auto / manual).                                                                                                                                                                                                                                                                                                                                                                                              yes      semi_auto
vnc_show_icon                 May the VNC applet used ? (this setting simply (en/dis)able the display of the VNC action button)                                                                                                                                                                                                                                                                                                                               yes      True
vnc_view_only                 Allow user to interact with remote desktop                                                                                                                                                                                                                                                                                                                                                                                      yes      False
vnc_network_connectivity      Use a VNC client pre-defined rule                                                                                                                                                                                                                                                                                                                                                                                               yes      lan
vnc_allow_user_control        Display applet control to user                                                                                                                                                                                                                                                                                                                                                                                                  yes      False
vnc_port                      The port to use to connect to a VNC                                                                                                                                                                                                                                                                                                                                                                                             yes      5900
============================= =============================================================================================================================================================================================================================================================================================================================================================================================================================== ======== =============

Currently available profiles for VNC (``vnc_network_connectivity``):

- fiber: for high speed local networks (low latency, 10 Mb/s per connection)
- lan: for 100 Mb local networks (low latency, 3 Mb/s per connection)
- cable: for high-end broadband links (high latency, 400 kb/s per connection)
- dsl: for low-end broadband links (high latency, 120 kb/s per connection)
- isdn: (high latency, 75 kb/s)

« package_api » section
-----------------------

This section is used to tell to the plugin where to find its Package service.

Available options for the "main" section:

=========== ====================== ======== =============
Option name Description            Optional Default value
=========== ====================== ======== =============
mserver     The service IP address yes      127.0.0.1
mport       The service TCP port   yes      9990
mmountpoint The service path       yes      /rpc
=========== ====================== ======== =============
