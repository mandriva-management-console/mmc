===============================
MMC projects release guidelines
===============================

This document explains how to release a new mmc project (mmc-core, mds, pulse2).

Release components
##################

What we release is a single tarball by project called:

- mmc-core-VERSION.tar.gz
- mds-VERSION.tar.gz
- pulse2-VERSION.tar.gz

This tarballs contains:

- the mmc-agent, the core MMC modules (audit framework),
  the core plugins "base" and "ppolicy" and the MMC web interface framework, 
  with the "base" and "ppolicy" web modules.
- MDS modules (samba, network, sshlpk, mail, bulkimport, userquota...) python
  and web parts
- Pulse 2 modules (inventory, msc, dyngroup, pkgs...) and services
  (inventory-server, package-server, imaging-server, scheduler, launcher...)

Preparing a new release
#######################

First a release candidate (RC) should be generated to prepare packages and do
QA tests.

1. Bump the version of the project
==================================

If the current stable version is 1.1.0 and we want to release 1.2.0 bump
the version to 1.1.90. This will be the first RC before the final 1.2.0
release.

The version number must be updated in several files:

- :file:`configure.ac` file
- :file:`agent/mmc/agent.py` file (for mmc-core only)
- :file:`agent/mmc/plugin/<PLUGIN_NAME>/__init__.py` files (VERSION attribute)
- :file:`web/modules/<MODULE_NAME>/infoPackage.inc.php` files

2. Prepare the changelog
========================

The Changelog file must be updated. If an entry in the changelog is a bugfix
of a bug reported in the bug tracking system, the ticket number must be written.

3. Documentation update
=======================

All the installation/configuration manuals must be updated and checked.

The upgrade procedure is updated:

- http://projects.mandriva.org/projects/mmc/wiki/Pulse2_Upgrade_Procedure
- http://projects.mandriva.org/projects/mmc/wiki/MDS_Upgrade_Procedure

4. Making the tarball
=====================

::

  # Clean all generated/untracked files
  $ git clean -fdx
  # Do a fresh configure
  $ ./configure --disable-python-check ...
  # Make the tarball
  $ make dist

5. Packaging and tests
======================

Packages are published on a testing repository. The installation/upgrade is
validated by the QA team and developers.

- All python unit tests of the project runs succesfully
- Selenenium tests runs succesfully
- Manual tests are succesfull

.. warning:: If bugs are found a new RC release must be issued and tests
    re-run (in our example it would be 1.1.91 for the next RC)

    Fix the bugs then go back to step 1.

6. Publishing the release
=========================

Final Bump
----------

- If all tests are successfull the version is bump to the final release number
  (1.2.0 in our example).
- A git tag is created after the version bump commit (MMC-CORE-XXX, MDS-XXX,
  Pulse2-XXX)
- The final tarball is generated.

Redmine updates
---------------

- The final tarballs are put in the public download place:
  http://projects.mandriva.org/projects/mmc/files
- Close the version we are going to release with the date of the release.
- Open a new version for the next release.
- If the release provide new plugins new Redmine components must be created
- Make a news for the new release (details of the news can be taken from the
  Changelog file)

Packages updates
----------------

- The Debian packages repository is updated, for Lenny and Squeeze.
- The RPMs packages repository for Mandriva MES5 and Mandriva Cooker are
  updated.
- A bug is open on https://qa.mandriva.com for MES5 official updates (eg:
  https://qa.mandriva.com/show_bug.cgi?id=65463)

Communication
-------------

- A mail is sent to the XXX-announce mailing list
- The freshmeat entry is updated
- A blog entry can be post on http://blog.mandriva.com
- A news can be proposed on http://www.linuxfr.org and other revelant websites.

