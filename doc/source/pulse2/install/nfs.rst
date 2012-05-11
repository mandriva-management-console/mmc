NFS setup
=========

In `/etc/exports` file, add the following lines::

    /var/lib/pulse2/imaging/computers *(async,rw,no_root_squash,subtree_check)
    /var/lib/pulse2/imaging/masters *(async,rw,no_root_squash,subtree_check)
    /var/lib/pulse2/imaging/postinst *(async,ro,no_root_squash,subtree_check)

Then reload the new NFS configuration, as root.

Check the export list::

    # showmount -e
    Export list for imaging:
    /var/lib/pulse2/imaging/masters *
    /var/lib/pulse2/imaging/postinst *
    /var/lib/pulse2/imaging/computers *



