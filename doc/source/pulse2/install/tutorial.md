Pulse² server install Debian GNU/Linux 7.0
===================================

**1 - Introduction**
----------------

This document describes the steps to install a full featured version of Pulse 2 on a Debian based system.

Before that you must have a fresh up-to-date [Debian Wheezy 7.0](https://www.debian.org/releases/wheezy/) installation on your server.

As most of the data will lives in the /var directory it is recommended to create a dedicated partition for /var.


**2 - Conventions**
---------------

Command launched by the root user :

    # command

File modification :

> Text in file

Filename, path, option or command :

    /etc/init.d/ssh

**3 - Sources**
-----------

A Pulse² source is available at github.

    https://github.com/mandriva-management-console/mmc

**4 - Installation**
----------------
### **4.1 - MMC Core**
####4.1.1 Required packages

    # apt-get install autoconf automake make libtool gettext python python-openssl python-ldap python-twisted-web python-mysqldb python-pylibacl python-gobject python-psutil python-pyquery python-configobj python-xlwt python-dev python-pip libxml2-dev python-libxml2 libxslt1-dev python-libxslt1 python-lxml python-ipaddr python-nmap python-requests python-netifaces python-netaddr python-sqlalchemy python-mysqldb python-dbus php5-xmlrpc php5-gd mysql-server nsis libcairo2 libpango1.0-0 libgdk-pixbuf2.0-0 libffi-dev shared-mime-info openssh-client iputils-ping nfs-kernel-server p7zip p7zip-full perl libsys-syslog-perl apache2 php5 php5-mysql mysql-server phpmyadmin libtool samba inetutils-syslogd nfs-kernel-server nfs-common rpm rng-tools expect reprepro createrepo curl nmap

Enter MySQL password (write it down) and configure it for apache2.

Enter also phpMyAdmin config :

 - use dbconfig-common : **yes**
 - password

Don't worry about the NFS Kernel warning, we will configure it later (imaging section)

#### 4.1.2 Install Python dependencies

Weazy - Web to PDF

    # pip install WeasyPrint

Pygal - SVG Charts Creator

    # pip install pygal

Python PSutil update

    # pip install psutil --upgrade

####4.1.3 OpenLDAP (slapd) setup

Set `debconf` priority to medium :

    # dpkg-reconfigure debconf

Choose Dialog -> medium

Install ldap and tool's

    # apt-get install slapd ldap-utils

Choose :

 - Omit : `No`
 - Set your domain name, default : `localdomain` (good for LAN)
 - Set your organization name, default : `localdomain` (set your company name if you want)
 - LDAP password (write it down, it's mandatory for Pulse²) : `pulse`
 - LDAP v2 use : `No`
 - Man : `No`

Configure syslog for LDAP log file

    # echo "local4.* /var/log/ldap.log" >> /etc/syslog.conf
    # touch /var/log/ldap.log
    # service rsyslog restart

####4.1.4 Install the core

    # git clone https://github.com/mandriva-management-console/mmc.git
    # cd mmc/core
    # ./autogen.sh
    # ./configure --prefix=/usr --sysconfdir=/etc --localstatedir=/var
    # make
    # make install

#### 4.1.5 LDAP schema installation

Install mmc LDAP schema

    # mmc-add-schema /usr/share/doc/mmc/contrib/base/mmc.schema /etc/ldap/schema/

Restart slapd daemon

    # service slapd restart

#### 4.1.6 Apache configuration

Enable mmc in your web server.
There is a sample conf file for Apache2, so you can do:

    # ln -s /etc/mmc/apache/mmc.conf /etc/apache2/conf.d/
    # service apache2 restart

#### 4.1.7 MMC configuration
Modify MMC base configuration according to LDAP configuration above

    # nano /etc/mmc/plugins/base.ini

Change `baseDN` (line 35) according to the LDAP domain name set above, here :
> baseDN = dc=localdomain

For example : if your domain name is mydomain.com you should set `dc=mydomain,dc=com`

 Change also the LDAP password (line 42) according to the one you set above, here :
 > password = pulse

Create the archives directory and start the mmc-agent service

    # mkdir /home/archives
    # service mmc-agent start

Connect to mmc console through your navigator to http://pulse.localdomain/mmc with the root ldap credential.

If the mmc-agent does not start, look at the `/var/log/mmc/mmc-agent.log` and check reported error.

Common errors are :

 - `ERROR Can't bind to LDAP: invalid credentials` : check that the ldap password and baseDN are correct
 - `ERROR Backup directory /home/archives does not exist or is not a directory` : you must create this directory

### **4.2 - BackupPC install**

If you want to make backup of pulse client, you must install backupPC.

    # apt-get install backuppc
Write down admin panel password during install or you can change it afterwards by launching

    # htpasswd /etc/backuppc/htpasswd backuppc

Test the backuppc default admin panel (*required only for test, because later we will use the pulse backup admin panel*)
http://pulse.localdomain/backuppc/

If you want to change configuration to restrict default backuppc admin panel, edit `/etc/apache2/conf.d/backuppc.conf` and add before the `</Directory>`
>   Order Deny, Allow
    Deny from all
    Allow from 127.0.0.1

### **4.3 - GLPI**
####4.3.1 Installation
**GLPI** is a free asset and IT management software package, it also offers functionalities like servicedesk ITIL or license tracking and software auditing.

Installation is optional but recommended because **GLPI** is more powerful than the embedded Pulse² inventory server.
If you do not want to have an advanced inventory of your computers, you can skip this step.

You can install **GLPI** by using debian package (0.83.31 in Wheezy) :

    # apt-get install glpi

**Or** 

Download a more updated copy from **GLPI** website and use [this guide](http://www.glpi-project.org/spip.php?article61) to install **or** the following tutorial (url below could be obsolete).

Download [latest version](http://www.glpi-project.org/spip.php?article41) and extract archive

    # wget http://forge.glpi-project.org/attachments/download/2093/glpi-0.85.5.tar.gz --no-check-certificate
    # tar xvzf glpi-0.85.5.tar.gz

Copy `glpi` dir to apache root and change user right

    # mv glpi /var/www/
    # chown www-data:www-data /var/www/glpi -R

Access from browser and follow instructions to install GLPI
http://pulse.localdomain/glpi

Main parameters :

 - Database host : localhost
 - Database user/pass : root/yourpass
 - Database name : glpi
 
After installation, delete installation file, set config file permissions and **GLPI** change default passwords (from admin panel) :

    # rm /var/www/glpi/install/install.php
    # chmod 400 /var/www/glpi/config/config_db.php

Add glpi directory to apache defaut site, edit `/etc/apache2/sites-available/default`, and add :
>        <Directory /var/www/glpi/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>

  after

>        <Directory /var/www/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride None
                Order allow,deny
                allow from all
        </Directory>

Restart Apache 

    service apache2 restart

And last, restrict permissions to sensible directories

    # echo "Deny from all" > /var/www/glpi/files/.htaccess
    # echo "Deny from all" > /var/www/glpi/config/.htaccess

#### 4.3.2 Fusion Inventory for GLPI

This plugin is mandatory to use GLPI with Pulse².

To install it follow [the documentation on the official website](http://fusioninventory.org/documentation/documentation/fi4g/installation.html) **or** use this tutorial (url could be obsolete)

Download plugin archive, extract, move to **GLPI** plugins folder and change right

    # wget http://forge.fusioninventory.org/attachments/download/1875/fusioninventory-for-glpi_0.85+1.2.tar.gz
    # tar xvzf fusioninventory-for-glpi_0.85+1.2.tar.gz
    # mv fusioninventory /var/www/glpi/plugins/
    # chown www-data:www-data /var/www/glpi/plugins/fusioninventory -R

 - Connect to **GLPI**
 - Go in the menu ***Setup*** > ***Plugins***
 - Install the plugin **FusionInventory**
 - Activate **FusionInventory** 
 - Go in the menu ***Administration*** > ***Entities*** > ***Root entity*** > ***tab FusionInventory*** to set the **Service URL**, here http://pulse.localdomain/glpi.

#### 4.3.3 Web services for GLPI

This plugin is mandatory to use wth Pulse²

[Download](https://forge.glpi-project.org/projects/webservices/files) & extract the latest copy (url could be obsolete)

    # wget https://forge.glpi-project.org/attachments/download/2033/glpi-webservices-1.5.0.tar.gz --no-check-certificate
    # tar xvzf glpi-webservices-1.5.0.tar.gz
    # mv webservices /var/www/glpi/plugins/
    # chown www-data:www-data /var/www/glpi/plugins/webservices -R

- Connect to **GLPI**
- Go in the menu ***Setup*** > ***Plugins***
- Install the plugin **Web Services**
- Activate **Web Services** 

Test **Web Services** access

    # cp /var/www/glpi/plugins/webservices/scripts/testxmlrpc.php .   

**GLPI** is now ready to work with Pulse²

###**4.4 - Pulse² install**

Install required packages

    # apt-get install docbook-xsl xsltproc

Install Pulse²

    # cd mmc/pulse2
    # ./autogen.sh
    # ./configure --prefix=/usr --sysconfdir=/etc --localstatedir=/var
    # make
    # make install

Launch Pulse² Setup

    # pulse2-setup

Follow instructions on screen to complete setup.
If you use **GLPI**, say **yes** to **Enable GLPI plugin**.

**Installation Log**

    INFO Load values from config
    INPUT - Enable inventory server (Y/n): y
    INPUT - Enable GLPI plugin (y/N): y
    INPUT - Enable package server (proxy) (Y/n): y
    INPUT - Server external IP address (default: 127.0.0.2): 172.16.0.6
    INPUT - BackupPC IP (default: 127.0.0.1):
    INPUT - BackupPC Entity (default: UUID1):
    INFO Run setup
    INPUT - Database host (default: localhost):
    INPUT - Database admin user (default: root):
    INPUT - Database admin password (default: ):
    INFO Update database schema for module dyngroup
    INFO ‘dyngroup’ database updated from version 0 to 6
    INFO Update database schema for module imaging
    INFO ‘imaging’ database updated from version 0 to 9
    INFO Update database schema for module inventory
    INFO ‘inventory’ database updated from version 0 to 15
    INFO Update database schema for module msc
    INFO ‘msc’ database updated from version 0 to 28
    INFO Update database schema for module pulse2
    INFO ‘pulse2’ database updated from version 0 to 1
    INFO Update database schema for module backuppc
    INFO ‘backuppc’ database updated from version 0 to 2
    INFO Update database schema for module report
    INFO ‘report’ database updated from version 0 to 3
    INFO Update database schema for module update
    INFO ‘update’ database updated from version 0 to 6
    INFO Setup db credentials
    INFO Creates user mmc@localhost
    INFO Updating user password: ‘mmc’@’localhost’
    INFO Grant rights on db dyngroup
    INFO Grant rights on db imaging
    INFO Grant rights on db inventory
    INFO Grant rights on db msc
    INFO Grant rights on db pulse2
    INFO Grant rights on db backuppc
    INFO Grant rights on db report
    INFO Grant rights on db update
    INFO Creates user mmc@127.0.0.1
    INFO Updating user password: ‘mmc’@’127.0.0.1’
    INFO Grant rights on db dyngroup
    INFO Grant rights on db imaging
    INFO Grant rights on db inventory
    INFO Grant rights on db msc
    INFO Grant rights on db pulse2
    INFO Grant rights on db backuppc
    INFO Grant rights on db report
    INFO Grant rights on db update
    INPUT - Glpi URL (default: http://127.0.0.1): http://pulse.localdomain/glpi
    INPUT - GLPI database user: root
    INPUT - GLPI database password (default: ):
    INPUT - GLPI database host (default: 127.0.0.1):
    INPUT - GLPI database name (default: glpi):
    INPUT - Purge machines from GLPI dustbin (default: False):
    INPUT - GLPI Webservices Username: pulse
    INPUT - GLPI Webservices password: pulse
    INPUT - LDAP uri (default: ldap://127.0.0.1:389):
    INPUT - LDAP base DN (default: dc=localdomain):
    INPUT - LDAP admin DN (default: cn=admin, dc=localdomain):
    INPUT - LDAP admin password (default: pulse):
    INFO Connection to LDAP succesfull.
    INFO Check for MMC schema
    INPUT - Default user group (default: Domain Users):
    INFO Creating directory: /var/lib/pulse2/packages
    INFO Creating directory: /var/lib/pulse2/downloads
    INFO Creating directory: /var/lib/pulse2/package-server-tmpdir
    INPUT - Wake-on-lan tool path (default: /usr/sbin/pulse2-wol):
    INFO Enabling service: mmc-agent
    INFO Enabling service: pulse2-inventory-server
    INFO Enabling service: pulse2-launchers
    INFO Enabling service: pulse2-package-server
    INFO Enabling service: pulse2-scheduler
    INFO Enabling service: pulse2-cm
    INFO Check for root user RSA key pair
    INFO Creating a RSA key pair for user root
    Generating public/private rsa key pair.
    Created directory ‘/root/.ssh’.
    Your identification has been saved in /root/.ssh/id_rsa.
    Your public key has been saved in /root/.ssh/id_rsa.pub.
    The key fingerprint is:
    2c:cb:63:b5:8f:54:3f:d3:47:00:ee:fb:8a:02:4a:7a root@pulse
    The key’s randomart image is:
    +–[ RSA 2048]—-+
    | . |
    | . . |
    | . . |
    | . . . |
    | . S . . .|
    | …+ o . o . |
    | o .=.o = . .|
    | . E. o.o . + . |
    | . ..o … |
    +—————–+
    INFO Generated uuid (603f1d19-c2a5-478e-9309-a6b5868b9020) for package server
    INFO Reloading service: apache2
    [ ok ] Reloading web server config: apache2.
    INFO Wrote configuration file: /etc/mmc/plugins/inventory.ini.local
    INFO Wrote configuration file: /etc/mmc/pulse2/package-server/package-server.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/report.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/imaging.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/dyngroup.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/update.ini.local
    INFO Wrote configuration file: /etc/mmc/pulse2/cm/cm.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/pulse2.ini.local
    INFO Wrote configuration file: /etc/mmc/pulse2/uuid-resolver/uuid-resolver.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/backuppc.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/msc.ini.local
    INFO Wrote configuration file: /etc/mmc/agent/config.ini.local
    INFO Wrote configuration file: /etc/mmc/pulse2/inventory-server/inventory-server.ini.local
    INFO Wrote configuration file: /etc/mmc/pulse2/launchers/launchers.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/base.ini.local
    INFO Wrote configuration file: /etc/mmc/pulse2/scheduler/scheduler.ini.local
    INFO Wrote configuration file: /etc/mmc/plugins/glpi.ini.local
    INFO Stopping service: mmc-agent
    Stopping Mandriva Management Console : mmc-agent : done.
    INFO Starting service: mmc-agent
    Starting Mandriva Management Console : mmc-agent : done.
    INFO Stopping service: pulse2-inventory-server
    Stopping Pulse2 inventoryserver : no pid
    INFO Starting service: pulse2-inventory-server
    Starting Pulse2 inventoryserver : done.
    INFO Stopping service: pulse2-launchers
    Stopping Pulse2 launchers : done.
    INFO Starting service: pulse2-launchers
    Starting Pulse2 launchers : done.
    INFO Stopping service: pulse2-package-server
    Stopping Pulse2 Package Server : pulse2-package-server : done.
    INFO Starting service: pulse2-package-server
    Starting Pulse2 Package Server : pulse2-package-server : done.
    INFO Stopping service: pulse2-scheduler
    Stopping Pulse2 Scheduler : done.
    INFO Starting service: pulse2-scheduler
    Starting Pulse2 Scheduler : done.
    INFO Stopping service: pulse2-cm
    Stopping Pulse2 Connection Manager : done.
    INFO Starting service: pulse2-cm
    Starting Pulse2 Connection Manager : done.
    INFO - Imaging server not registered
    INFO Registering imaging server
    INFO - Registration succeeded
    INFO BackupPC server http://127.0.0.1/backuppc/index.cgi associated to entity UUID1
    Group ‘Domain Users’ successfully created: {‘objectClass’: [‘posixGroup’, ‘top’], ‘gidNumber’: [‘10001’], ‘cn’: [‘Domain Users’]}
    INFO Group Domain Users successfully created
    INFO Wrote configuration file: /etc/mmc/agent/config.ini.local
    INFO Stopping service: mmc-agent
    Stopping Mandriva Management Console : mmc-agent : done.
    INFO Starting service: mmc-agent
    Starting Mandriva Management Console : mmc-agent : done.


### 4.5 - Agents for computers
#### 4.5.1 Generate agents
    # cd /var/lib/pulse2/clients/
    # ./generate-agents    
Install the generated agent on the computers to manage.

 - For **Windows**: agents are in the **win32** folder
  - pulse2-win32-agents-installer.exe
  - pulse2-win32-agents-pack.exe
  - pulse2-win32-agents-pack-noprompt.exe
  - pulse2-win32-agents-pack-silent.exe
 - For **Mac**: agents are in the **mac** folder
  - Pulse2AgentsInstaller.tar
 - For **Debian**/**Ubuntu**/**Mint**: agents are in the **deb** folder
  - pulse2-agents-installer.deb
  - pulse2-agents-installer-nordp.deb
 - For **Suse**/**Mandriva**/**Fedora**: agents are in the **rpm** folder
  - pulse2-agents-installer-0.1-7.noarch.rpm
  - pulse2-agents-installer-nordp-0.1-7.noarch.rpm
  - pulse2-agents-installer-suse-0.1-7.noarch.rpm
  - pulse2-agents-installer-suse-nordp-0.1-7.noarch.rpm
 
#### 4.5.2 Create debian repository (optional)

For debian-based client, you can generate a repository on the pulse server, that you can add to the client */etc/apt/sources.list*.

Use the entire absolute path command below or it will not work (eg: don't use ./create-repos.sh).

    # /var/lib/pulse2/clients/create-repos.sh

If you should create entropy use `ls -R /` command in another console.

#### 4.5.3 Generate pulse-update-client (to manage Windows Update for Windows computers)
**FROM A WINDOWS DESKTOP CLIENT**

Install 

 - **Python 2.7** : https://www.python.org/ftp/python/2.7.10/python-2.7.10.amd64.msi
 - **cx_Freeze pour python 2.7** : https://pypi.python.org/packages/2.7/c/cx_Freeze/cx_Freeze-4.3.4.win-amd64-py2.7.exe#md5=5a11ce9c92572af5a4efa612a8fa9d5d
 - **pywin32 pour python 2.7** : http://heanet.dl.sourceforge.net/project/pywin32/pywin32/Build%20219/pywin32-219.win-amd64-py2.7.exe
    
Download in a folder the last GIT version of MMC
https://github.com/mandriva-management-console/mmc/archive/master.zip

Copy ***mmc-master\pulse2\services\pulse2\pulse_update_manager*** folder in a temp folder to make pre-built for Windows

Copy all the **src** folder in the **temp folder**, and also **build.bat** and **setup.py** which is in the **win32** folder.
Temp folder should be like this
![enter image description here](http://pix.toile-libre.org/upload/original/1442891705.png)

Lauch **build.bat** from command line and check for errors, if the path to Python is not correct change it.

Copy the **build/exe.win-amd64-2.7** folder to the Pulse Server in the GIT folder ***mmc\pulse2\services\pulse2\pulse_update_manager\win32\bin***

**Or**

Download the pre-generated package ([temporary url](https://github.com/psyray/mmc/blob/docs/pulse2/services/pulse2/pulse_update_manager/win32/bin/exe.win-amd64-2.7.tar.gz?raw=true))

    # wget https://github.com/psyray/mmc/blob/docs/pulse2/services/pulse2/pulse_update_manager/win32/bin/exe.win-amd64-2.7.tar.gz?raw=true

**FROM PULSE SERVER**

    # cd mmc\pulse2\services\pulse2\pulse_update_manager\win32
    # makensis installer.nsi

Install generated file (**pulse2-secure-agent-windows-update-plugin-1.0.0.exe**) on Windows desktop and wait for client to appear in the Pulse² admin

#### 4.5.4 Give access from computers to agent/doc from browser
Create `/etc/apache2/conf.d/pulse2.conf` file

Add this configuration
> Alias /downloads /var/lib/pulse2/clients/
Alias /doc /usr/share/doc/pulse2/

>     <Directory /var/lib/pulse2/clients/>
        Options +Indexes
        AllowOverride None
        # Apache < 2.4
        Order allow,deny
        allow from all
        # Apache >= 2.4
        #Require all granted
      </Directory>

>     <Directory /usr/share/doc/pulse2/>
        Options +Indexes
        AllowOverride None
        # Apache < 2.4
        Order allow,deny
        allow from all
        # Apache >= 2.4
        #Require all granted
      </Directory>

    # service apache2 reload

Now your client could have access to agent download from http://pulse.localdomain/downloads/ and the doc will be available from here http://pulse.localdomain/doc/

### 4.6 - Imaging server
Last step is to configure the imaging server to permit, with PXE boot, to boot on network and perform imaging, computer registering...

PXE boot will make possible to boot a computer, register to the Pulse² server and deploy agent silently to target.
#### 4.6.1 Extract PXE skeleton
Download PXE boot skeleton ([temporary URL](https://github.com/psyray/mmc/blob/docs/pulse2/services/contrib/imaging-server/pulse2-imaging-client-binary-1.3.1_i386.tar.gz?raw=true))

    # cd ~
    # wget https://github.com/psyray/mmc/blob/docs/pulse2/services/contrib/imaging-server/pulse2-imaging-client-binary-1.3.1_i386.tar.gz?raw=true

Extract to /

    # tar xvzf pulse2-imaging-client-binary-1.3.1_i386.tar.gz?raw=true -C /

#### 4.6.2 Configure NFS
Edit `/etc/exports`

Add

> /var/lib/pulse2/imaging/computers *(async,rw,no_root_squash,subtree_check)
/var/lib/pulse2/imaging/masters *(async,rw,no_root_squash,subtree_check)
/var/lib/pulse2/imaging/postinst *(async,ro,no_root_squash,subtree_check)

    # service nfs-kernel-server reload
    # showmount -e
    Export list for imaging:
    /var/lib/pulse2/imaging/masters *
    /var/lib/pulse2/imaging/postinst *
    /var/lib/pulse2/imaging/computers *

    
If error : `clnt_create: RPC: Program not registered`

    # service rpcbind restart
    # service nfs-kernel-server restart

> <i class="icon-info"></i>  If you are running Pulse² in an OpenVZ container, you must activate the NFS support by typing this command in the host system 
> `vzctl set $CTID --feature nfsd:on --save`

#### 4.6.3 Configure DHCP
If you are already using a DHCP server, you have to configure your server to send PXE boot request to Pulse² server.

For example, with IPFire/IPCop you could use this configuration in the fixed lease section of the DCHP settings.
![enter image description here](http://pix.toile-libre.org/upload/original/1442897131.png)

Important thing is to set the **next-server** directive, with IP of the Pulse² server and the **filename** directive, relative to /var/lib/pulse2/imaging

If you want to use Pulse² server as the DHCP server, install the required DHCP package and set the configuration.
For example with ISC-DHCP server (not tested):

    # apt-get install isc-dhcp-server

Edit `/etc/default/isc-dhcp-server`
And put (replace IP range with yours)

    ###########################################
    # This is a dhcpd sample file for Pulse 2 # 
    ########################################### 
    ddns-update-style ad-hoc; # mandatory since 3.0b2pl11 
    
    # When using a NAS, uses DHCP option 177 
    option pulse2-nfs code 177 = text; 
    
    # PXE definitions 
        option space PXE; 
        option PXE.mtftp-ip code 1 = ip-address; 
        option PXE.mtftp-cport code 2 = unsigned integer 16; 
        option PXE.mtftp-sport code 3 = unsigned integer 16; 
        option PXE.mtftp-tmout code 4 = unsigned integer 8; 
        option PXE.mtftp-delay code 5 = unsigned integer 8; 
        option PXE.discovery-control code 6 = unsigned integer 8; 
        option PXE.discovery-mcast-addr code 7 = ip-address;
        
    # PXE boot following the PXE specs 
    class "PXE" { 
        match if substring(option vendor-class-identifier, 0, 9) = "PXEClient"; 
         vendor-option-space PXE; 
         option PXE.mtftp-ip 0.0.0.0; 
            }   
            
    # Etherboot boot 
    class "Etherboot" { 
        match if substring (option vendor-class-identifier, 0, 11) = "Etherboot-5"; 
        option vendor-encapsulated-options 3c:09:45:74:68:65:72:62:6f:6f:74:ff; 
        option vendor-class-identifier "Etherboot-5.0"; 
        vendor-option-space PXE; 
        option PXE.mtftp-ip 0.0.0.0; 
                  } 
                  
    subnet 192.168.30.0 netmask 255.255.255.0 { 
          option broadcast-address 192.168.30.255;        # broadcast address 
          option domain-name "pulse2.test";               # domain name 
          option domain-name-servers 192.168.30.1;        # dns servers 
          option routers 192.168.30.1;                    # default gateway 
    pool { # Only defined pool 
                        # uncomment the two following lines for PXE-only boot 
                        # allow members of "PXE"; # PXE-only 
                        # allow members of "Etherboot"; # PXE-only 
                        range 192.168.30.170 192.168.30.180; 
                        filename "/bootloader/pxe_boot"; 
                        next-server 192.168.30.1; 
        } 
     } 

Restart DHCP service

    # service isc-dhcp-server restart

#### 4.6.4 TFTP Installation
Pulse 2 use the atftpd server as it supports multicast.

    # apt-get install atftpd atftp
Configuration should be set like this :

 - don't use inetd
 - tftp root: **/var/lib/pulse2/imaging**

Check configuration

    # atftp 127.0.0.1
    tftp> get /bootloader/pxe_boot
    tftp> quit
    rm pxe_boot
    
#### 4.6.5 Activate default imaging server
Now that DHCP, TFTP and NFS is up and running, **you must set a default imaging server** to permit network boot.

Go to http://pulse.localdomain/mmc/main.php?module=imaging&submod=manage&action=index and click on the **Add** button at the right of the imaging server

When imaging server is correctly set you should see this screen
![enter image description here](http://pix.toile-libre.org/upload/original/1442934510.png)

Now try to boot from a workstation with PXE boot actived in the BIOS and you should see this screen
![enter image description here](http://pix.toile-libre.org/upload/original/1442935185.png)

Select **Register Pulse client**, enter the **hostname** (follow instructions displayed) and you should normally see the client in the Pulse² MMC admin panel
![enter image description here](http://pix.toile-libre.org/upload/original/1442941251.png)

----------


When finished you should have an up and running Pulse² Server ;)

![enter image description here](http://pix.toile-libre.org/upload/original/1442888207.png)

If you have any problem you could post your problem in the [Pulse² forum](http://forum.pulse2.fr)

If you found any issue, thanks to report it to [GitHub Repository](https://github.com/mandriva-management-console/mmc/issues)

Have fun with Pulse²
