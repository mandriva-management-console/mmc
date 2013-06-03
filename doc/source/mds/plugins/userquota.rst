================
Userquota plugin
================

Installation
============

Install the packages ``python-mmc-userquota`` and ``mmc-web-userquota``.

LDAP directory configuration
============================

You need to import the quota schema into the LDAP directory.
The schema file is provided by the ``python-mmc-userquota`` package in
:file:`/usr/share/doc/mmc/contrib/userquota/quota.schema`.

Once this schema is imported, you will be able to manage quota
attributes thanks to the MMC.

.. note:: On Debian, run:

          mmc-add-schema /usr/share/doc/mmc/contrib/userquota/quota.schema \
          /etc/ldap/schema

Enabling filesystem quotas on your server
=========================================

If you are using an ext3 or XFS filesystem you should add the "usrquota" option
on the mountpoint(s) where you want to manage quotas in /etc/fstab.

If you want to manage quota on / with an XFS filesystem you need also to pass
the kernel option ``rootflags=usrquota``. You'll need to modify your GRUB
configuration for this.

If you are using an XFS filesystem, you must remount manually the partition
after adding the "usrquota" option on the mountpoints in /etc/fstab. On ext3
filesystems, you can remount the filesystem dynamicaly with the usrquota option
using the following command:

::

    mount -o remount,usrquota /path/to/mount/point

On ext filesystems you have to create quota files on your mountpoints :

::

    quotacheck -cum /path/to/mount/point

This is not needed on XFS.

Enable the quotas on all mountpoints with:

::

    quotaon -au

Check that the quotas are enabled with:

::

    quotaon -aup

MMC « userquota » plugin configuration
======================================

In the diskquota section of ``/etc/mmc/plugins/usrquota.ini`` you need to
specify the list of devices where you want to apply user quotas in the option
``devicemap``.

The devicemap option use the following format :

::

    device1:blocksize:displayname,device2:blocksize:displayname,...

The device is the unix name of the partition (eg: "/dev/sda1").

.. note:: Use the device name reported by the ``quotaon -aup`` command

The displayname is a string representing the device (eg: "Homes"). The quota
blocksize value is 1024 on Linux x86.

For a full description of the MMC userquota plugin configuration file see
:ref:`config-userquota`.

This plugin won't be activated if your LDAP directory does not include the
quota schema or the quotas are not enabled on any mountpoints.
