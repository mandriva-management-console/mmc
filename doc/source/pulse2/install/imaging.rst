Imaging client installation
===========================

Imaging client can run only on i386 compliant machines. It is not run
directly on the server, but served through the network to i386 machines.
For your convenience, prebuilt binaries are available, so that you can
install it on a server which is not i386.

Once you have downloaded prebuilt binaries as
pulse2-imaging-client-<version>_i386.tar.gz, simply run the following, as root:
$ tar xfC pulse2-imaging-client-<version>_i386.tar.gz /

All files are extracted in /var/lib/pulse2/imaging/ dir.

As to serve the imaging client to the machines, you must then configure the
following network services.
