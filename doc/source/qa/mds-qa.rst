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

A private network with one MES 5.2 server (i568 or x86_64) 
and one Windows XP or 7 client.

The network and the machines can of course be virtualized (it's
easy to setup with VirtualBox).

- The MES 5.2 server is a base installation from the DVD + all updates
- The ``mmc-wizard`` package is installed
- The private network will be ``192.168.220.0/24`` in this document
- The MES 5.2 server has a static IP of ``192.168.220.10``

Add the repo http://mes5devel.mandriva.com/iurt/jpbraun/iurt/mes5/i586/
to be able to get the MDS test packages :

  ::

    urpmi.addmedia jpbraun http://mes5devel.mandriva.com/iurt/jpbraun/iurt/mes5/i586/

Installation & configuration
============================

From the mmc-wizard (http://192.168.220.10/mmc-wizard/) select and install 
all MDS components.

- MDS domain: ``test.local``
- MDS password: ``test$!``
- SAMBA password: ``smbTest!``
- Mail hostname: ``smtp.test.local``
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

   - Last name, phone, add secondary groups...

4. Add a second user:

   - Login: ``user2``
   - Password: ``test2``
   - Mail: ``user2@example.com``
   - Alias: ``contact@example.com``

5. Add the mail domain ``example.com``

6. Login in roundcube (http://192.168.220.10/roundcubemail)   
   with ``user1@example.com``.

   - send a mail to ``user2@example.com``
   - send a mail to ``contact@example.com``

7. Login in rouncube with ``user2@example.com`` and check the mails

8. Edit the MMC ACLs of user1 and check the "Change password" page

   - Login the MMC with ``user1`` and change his password

9. Create a DNS zone

   - FQDN: ``example.com``
   - Server IP: ``192.168.220.10``
   - Network address: ``192.168.220.0``
   - Network mask: ``24``
   - Create DHCP subnet and reverse zone

10. Edit the DHCP subnet and add a dynamic pool from
    ``192.168.220.50`` to ``192.168.220.60``

11. Restart both services in ``Network Services Management``

12. Boot the Windows client and check if it gets an IP

13. Convert the dynamic lease of the client to a static lease. Set the
    DNS name to ``win.example.com``.

14. Renew the lease on the windows client (``ipconfig /renew``) then 
    ping ``win.example.com``.

15. Join the computer to the ``MES5DOMAIN`` domain (``admin/smbTest!``)

16. Login with ``user1`` on the Windows client

17. Change ``user1`` password on the Windows client

18. Login the MMC with ``user1`` new password

19. Change ``user1`` password on the MMC interface

20. Logout/Login with ``user1`` on the Windows client
