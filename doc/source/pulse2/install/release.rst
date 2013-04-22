================================
Installation from source tarball
================================

In order to install Pulse2 and it's plugins you first need to install and
configure :doc:`MMC </mmc/intro>`.

Get the current tarball at `download page`_:: 

    # tar xzvf pulse2-.x.y.x.tar.gz
    # cd pulse2-x.y.z
    # ./configure --prefix=/usr --sysconfdir=/etc --localstatedir=/var
    # make
    # make install

The `pulse2-setup` tool can then be used to provision databases,
setup and check configuration files, etc. If you plan to use imaging 
service, please read the following section, as pulse2-setup does not 
handle with its configuration.

The default $PREFIX for installation is :file:`/usr/local`. You can change it
on the ``./configure`` line by adding the option ``--prefix=/usr`` for example.

configure options
=================

The `configure` recognizes the following options to control how it operate:

* --help, -h: Print a summary of all of the options to configure, and exit.

* --help=short --help=recursive: Print a summary of the options unique to this
                                 package's configure, and exit. The short
                                 variant lists options used only in the top 
                                 level, while the recursive variant lists
                                 options  also present in any nested packages.

* --version, -V: Print the version of Autoconf used to generate the configure
                 script, and exit.

* --cache-file=FILE: Enable the cache: use and save the results of the tests
                     in FILE, traditionally config.cache. FILE defaults to
                     /dev/null to disable caching.

* --config-cache, -C: Alias for --cache-file=config.cache.

* --quiet, --silent, -q: Do not print messages saying which checks are 
                         being made.  To  suppress all normal output, 
                         redirect it to /dev/null (any error messages
                         will still be shown).

* --srcdir=DIR: Look for the package's source code in directory DIR. Usually
                configure can determine that directory automatically.

* --prefix=DIR: Use DIR as the installation prefix. note Installation
                Names for more details, including other options available
                for fine-tuning the installation locations.

* --no-create, -n: Run the configure checks, but stop before creating any
                   output files.

* --disable-conf: Do not install conf files. On a first install, you may not
                  use this  option as configuration files are required.

* --disable-conf-backup: Do not backup configuration files if they are
                         already installed. Default is to create backup
                         files like `*.~N~`.

* --disable-wol:  Do not build and install wake-on-lan tool.

.. _`download page`: http://projects.mandriva.org/projects/mmc/files

.. include:: /pulse2/install/schema.rst

.. include:: /pulse2/install/pulse-setup.rst

.. include:: /pulse2/install/imaging.rst

.. include:: /pulse2/install/dhcp.rst

.. include:: /pulse2/install/nfs.rst

.. include:: /pulse2/install/tftp.rst
