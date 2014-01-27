======
MDS QA
======

This page describes how to test the MDS interface before a release.

Selenium tests
##############

Install the Selenium IDE plugin for Firefox at http://seleniumhq.org.

Running the test suite
======================

- Open Selenium IDE (Ctrl+Alt+S)
- Open the test suite from the repository
  (``mmc/mds/tests/selenium/suite/all_test.html``)
- Browse to the MMC login page on your test server. The MDS installation must be
  clean with samba, mail and network modules. Root password must be ``secret``.
- Play the full test suite

Manual tests
############

Manual tests validate basic usage of MSS (Mandriva Server Setup)
and MDS on MES 5.2. We basically check that the setup of SAMBA, bind9,
dhcpd, postfix, dovecot, OpenLDAP with MDS done by MSS is OK.

Environment setup
=================

A private network with one MBS 1.0 server and one Windows XP or 7 client.

The network and the machines can of course be virtualized (it's
easy to setup with VirtualBox).

- The MBS server is a base installation from the DVD + all updates
- The private network will be ``192.168.220.0/24`` in this document
- The MBS server has a static IP of ``192.168.220.10``

Enable the testing repo to be able to get the MDS test packages.

Installation & configuration
============================

From mss (https://192.168.220.10:8000) select and install the following
modules: Samba, DNS & DHCP, Mail, Webmail.

- MDS domain: ``test.local``
- MDS password: ``test$!``
- SAMBA password: ``smbTest!``
- Mail networks: ``192.168.220.0/255.255.255.0``
- DNS networks: ``192.168.220.0/255.255.255.0``

The configuration must be successfull.

MDS tests
=========

1. Login in MDS at http://192.168.220.10/mmc/ with ``root/test$``!

2. Add a user:

   - Login: ``user1``
   - Password: ``test1``
   - Mail: ``user1@example.com``

3. Edit the user and set some other fields:

   - Last name, phone...

4. Put the user in a secondary group then remove if from the group.

5. Add a second user:

   - Login: ``user2``
   - Password: ``test2``
   - Mail: ``user2@example.com``
   - Alias: ``contact@example.com``

6. Add the mail domain ``example.com``

7. Login in roundcube (http://192.168.220.10/roundcubemail)
   with ``user1@example.com``.

   - send a mail to ``user2@example.com``
   - send a mail to ``contact@example.com``

8. Login in roundcube with ``user2@example.com`` and check the mails

9. Edit the MMC ACLs of user1 and check the "Change password" page

   - Login the MMC with ``user1`` and change his password

10. Create a DNS zone

   - FQDN: ``example.com``
   - Server IP: ``192.168.220.10``
   - Network address: ``192.168.220.0``
   - Network mask: ``24``
   - Create DHCP subnet and reverse zone

11. Edit the DHCP subnet and add a dynamic pool from
    ``192.168.220.50`` to ``192.168.220.60``

12. Restart both services in ``Network Services Management``

13. Boot the Windows client and check if it gets an IP

14. Convert the dynamic lease of the client to a static lease. Set the
    DNS name to ``win.example.com``.

15. Renew the lease on the windows client (``ipconfig /renew``) then
    ping ``win.example.com``.

16. Join the computer to the ``MES5DOMAIN`` domain (``admin/smbTest!``)

17. Login with ``user1`` on the Windows client

18. Change ``user1`` password on the Windows client

19. Login the MMC with ``user1`` new password

20. Change ``user1`` password on the MMC interface

21. Logout/Login with ``user1`` on the Windows client
