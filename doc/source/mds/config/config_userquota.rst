.. config-userquota:

=======================================
MMC userquota plugin configuration file
=======================================

This document explains the content of the MMC userquota plugin configuration file.

Introduction
############

The « userquota » plugin allows the MMC to set filesystem quotas to users.
The plugin provides LDAP attributes for storing quota information. The plugin
allows also to store network quotas in the LDAP directory for external tools.
It uses the « base » plugin for all its related LDAP operations.

The plugin configuration file is :file:`/etc/mmc/plugins/userquota.ini`.

Like all MMC related configuration file, its file format is INI style. The file
is made of sections, each one starting with a « [sectionname] » header. In each
section options can be defined like this « option = value ».

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

Here are all the userquota.ini available sections:

============ ===================================== ========
Section name Description                           Optional
============ ===================================== ========
main         global userquota plugin configuration no
diskquota    filesystem quota configuration        yes
networkquota network quota configuration           yes
============ ===================================== ========

Section « main »
################

This sections defines the global options of the mail plugin

Available options for the « main » section:

=========== ========================= ======== =============
Option name Description               Optional Default value
=========== ========================= ======== =============
disable     Is this plugin disabled ? no       yes
=========== ========================= ======== =============

Section « diskquota »
#####################

Available options for the « diskquota » section:

=============== ============================================================= ======== ===========================================================================
Option name     Description                                                   Optional Default value
=============== ============================================================= ======== ===========================================================================
enable          Is this plugin enabled ?                                      No       0
devicemap       The definition of the filesystems using quotas                No       /dev/sda1:1024:Root
softquotablocks Coef used to calculate the soft blocks limit                  No       0.95
softquotainodes Coef used to calculate the soft inodes limit                  No       0.95
inodesperblock  Coef used to calculate the inodes limit from the blocks limit No       1.60
setquotascript  Command template for applying quotas on filesystem            No       /usr/sbin/setquota $uid $softblocks $blocks $softinodes $inodes $devicepath
delquotascript  Command template for removing quotas on filesystem            No       /usr/sbin/setquota $uid 0 0 0 0 $devicepath
runquotascript  Script for setting quotas                                     No       /bin/sh
=============== ============================================================= ======== ===========================================================================

This section define the mountpoints on which you want to use filesystem quotas.
If you are using an ext3 or xfs filesystem you should add the "usrquota" option
to the mountpoint(s) in /etc/fstab.

If you are using an XFS filesystem, you must remount manually the partition
after adding the "usrquota" option to the mountpoint in /etc/fstab. On ext3
filesystems, you can remount the filesystem dynamicaly with the usrquota option
using the following command:

::

    mount -o remount,usrquota /path/to/mount/point

On ext filesystems you have to create quota files on your mountpoints :

::

    quotacheck -cum /path/to/mount/point

The devicemap option use the following format :

::

    device1:blocksize:displayname,device2:blocksize:displayname,...

The device is the unix name of the partition (eg: "/dev/sda1"). The displayname
is a string representing the device (eg: "Root"). The iquota blocksize value is
1024 on Linux x86.

The soft limits of the quotas are calculated using the softquotablocks and
softquotainodes coefs. The inode limit is calculated using the inodesperblock
coef.

The inode limits protects the filesystem if some user create to much hardlinks
as a hardlink use one inode but no block on the filesystem.

The setquotascript and delquotascript options define the commands templates
used to apply or remove quotas on the filesystem. The runquotascript is the
name of a shell script which contain the quota commands to be run on the system.
If it is set to /bin/sh, then quotas will be applied on the local system.
Check the applyquotas.sh example script to see how you can apply quotas on a
different server. This is useful if your mmc-agent does not run on your file
server.

Section « networkquota »
########################

Available options for the « networkquota » section:

=========== ======================================= ======== ======================
Option name Description                             Optional Default value
=========== ======================================= ======== ======================
enable      Is this plugin enabled ?                No       0
networkmap  The definition of networks using quotas No       Internet:0.0.0.0/0:any
=========== ======================================= ======== ======================

This section define the networks on which you want to use quotas. This allows
you to store differents quotas values for differents network/protocol pair.
This plugin will update the ldap records for network quotas for each user,
but does not attempt to apply these quotas to a firewall, as this will be
different for most people.

The networkmap option must be formatted with the following format :

::

    displayName:network:protocol,...
    ----------------------
    Internet:0.0.0.0/0:any,Local:192.168.0.0/24:any
