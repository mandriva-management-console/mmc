DHCP Setup
==========

The imaging module of Pulse 2 needs PXE functionalities, NFS and TFTP services.
For PXE configure the DHCP server on the network to serve the Pulse2 PXE
bootmenu.

For example with dhcp3-server in /etc/dhcp3/dhcpd.conf::

     subnet 192.168.0.0 netmask 255.255.255.0 {
         option broadcast-address 192.168.0.255; # broadcast address
         option domain-name "pulse2.test"; # domain name
         option domain-name-servers 192.168.0.2; # dns servers
         option routers 192.168.0.2; # default gateway
         pool {
             range 192.168.0.170 192.168.0.180;
             filename "/bootloader/pxe_boot";
            next-server 192.168.0.237;
         }
    }

* filename and next-server are the relevant options to set.

You can find an example file for dhcp3 server in or repository_.

.. _repository: https://github.com/mandriva-management-console/mmc/blob/master/pulse2/services/contrib/dhcp/dhcpd.conf 
