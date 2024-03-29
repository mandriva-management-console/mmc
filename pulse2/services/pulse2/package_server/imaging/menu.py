# -*- coding: utf-8; -*-
#
# (c) 2009-2010 Mandriva, http://www.mandriva.com/
#
# $Id$
#
# This file is part of Pulse 2, http://pulse2.mandriva.org
#
# Pulse 2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# Pulse 2 is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MMC.  If not, see <http://www.gnu.org/licenses/>.

"""
    client menu handling classes
"""

import re
import os
import logging
import time
import shutil
import stat

import pulse2.utils
import pulse2.package_server.imaging.api.functions
from pulse2.package_server.config import P2PServerCP as PackageServerConfig


def isMenuStructure(menu):
    """
    @return: True if the given object is a menu structure
    @rtype: bool
    """
    ret = True
    logger = logging.getLogger('imaging')
    if type(menu) == dict:
        for k in ['message',
                  'protocol',
                  'default_item',
                  'default_item_WOL',
                  'timeout',
                  'background_uri',
                  'bootservices',
                  'images',
                  'bootcli',
                  'disklesscli',
                  'hidden_menu',
                  'dont_check_disk_size',
                  'ethercard']:

            if not k in menu:
                logger.error("Menu is missing %s" % (k))
                ret = False
                break
    else:
        logger.error("Menu is not a dict")
        ret = False
    return ret


class ImagingDefaultMenuBuilder:

    """
    Class that builds an imaging menu according to its dict structure.
    """

    def __init__(self, config, menu):
        """
        The object builder

        @param config : the imaging server config
        @param menu : the menu dict

        @return the object
        """
        self.logger = logging.getLogger('imaging')
        if not isMenuStructure(menu):
            raise TypeError('Bad menu structure')
        self.menu = menu
        self.config = config

    def make(self, macaddress = None):
        """
        @return: an ImagingMenu object
        @rtype: ImagingMenu
        """
        m = ImagingMenu(self.config, macaddress)
        m.setSplashImage(self.menu['background_uri'])
        m.setMessage(self.menu['message'])
        m.setTimeout(self.menu['timeout'])
        m.setDefaultItem(int(self.menu['default_item']))
        m.setProtocol(self.menu['protocol'])
        m.setMTFTPTimeout(self.menu['mtftp_restore_timeout'])
        self.logger.debug('bootcli: %s' % self.menu['bootcli'])
        m.setBootCLI(self.menu['bootcli'])
        m.hide(self.menu['hidden_menu'])
        if 'update_nt_boot' in self.menu:
            m.setNTBLFix(self.menu['update_nt_boot'])
        self.logger.debug('disklesscli: %s' % self.menu['disklesscli'])
        m.setDisklessCLI(self.menu['disklesscli'])
        self.logger.debug('dont_check_disk_size: %s' % self.menu['dont_check_disk_size'])
        m.setDiskSizeCheck(self.menu['dont_check_disk_size'])
        self.logger.debug('ethercard: %s' % self.menu['ethercard'])
        m.setEtherCard(int(self.menu['ethercard']))
        if 'language' in self.menu:
            m.setLanguage(int(self.menu['language']))
        for pos, entry in list(self.menu['bootservices'].items()):
            m.addBootServiceEntry(int(pos), entry)
        for pos, entry in list(self.menu['images'].items()):
            m.addImageEntry(int(pos), entry)
        self.logger.debug('Menu structure built successfully')
        return m


class ImagingComputerMenuBuilder(ImagingDefaultMenuBuilder):

    """
    Class that builds an imaging menu for a computer according to its dict
    structure.
    """

    def __init__(self, config, macaddress, menu):
        ImagingDefaultMenuBuilder.__init__(self, config, menu)
        self.macaddress = macaddress

    def make(self):
        self.logger.debug('Building menu structure for computer mac %s' % self.macaddress)
        computerMenu = ImagingDefaultMenuBuilder.make(self, self.macaddress)
        if 'raw_mode' in self.menu['target']:
            computerMenu.setRawMode(self.menu['target']['raw_mode'])
        return computerMenu


class ImagingMenu:

    """
    hold an imaging menu
    """

    DEFAULT_MENU_FILE = 'default'
    LANG_CODE = {
        1 : 'C',
        2 : 'fr_FR',
        3 : 'pt_BR',
        4 : 'de_DE',
                }
    KEYB_CODE = {
        1 : None,
        2 : 'fr',
        3 : 'pt',
        4 : 'de',
    }

    def __init__(self, config, macaddress = None):
        """
        Initialize this object.

        @param config: a ImagingConfig object
        @param macaddress: the client MAC Address
        """
        self.logger = logging.getLogger('imaging')
        self.config = config  # the server configuration
        if macaddress:
            assert pulse2.utils.isMACAddress(macaddress)
        self.mac = macaddress  # the client MAC Address

        # menu items
        self.menuitems = {}
        self.timeout = 0  # the menu timeout
        self.default_item = 0  # the menu default entry
        self.default_item_wol = 0  # the menu default entry on WOL
        self.splashimage = None  # the menu splashimage
        self.message = None
        self.colors = {  # menu colors
            'normal': {'fg': 7, 'bg': 1},
            'highlight': {'fg': 15, 'bg': 3}}
        self.keyboard = None  # the menu keymap, None is C
        self.hidden = False  # do we hide the menu ?
        self.language = 'C'  # Menu language
        self.bootcli = False  # command line access at boot time ?
        self.disklesscli = False  # command line access at diskless time ?
        self.ntblfix = False  # NT Bootloader fix
        self.ethercard = 0  # use this ethernet iface to backup / restore stuff
        self.dont_check_disk_size = False  # check that the target disk is large enough

        self.diskless_opts = list()  # revo* options put on diskless command line

        # kernel options put on diskless command line
        # Check if Davos or revo
        if self.config.imaging_api['diskless_folder'] == "davos":
            self.kernel_opts = ['boot=live',
                'config',
                'noswap',
                'edd=on',
                'nomodeset',
                'nosplash',
                'noprompt',
                'vga=788',
                'fetch=tftp://%s/%s/fs.squashfs' % (PackageServerConfig().public_ip, self.config.imaging_api['diskless_folder'])]

            # If we have a mac, we put it in kernel params
            if macaddress is not None:
                self.kernel_opts.append('mac='+ macaddress)
        else:
            self.kernel_opts = list(['quiet'])  # kernel options put on diskless command line

        self.protocol = 'nfs'  # by default
        self.rawmode = ''  # raw mode backup
        self.mtftp_timeout = '10' # MTFTP wait timeout

    def _applyReplacement(self, string, condition = 'global'):
        """
        Private func, to apply a replacement into a given string

        Some examples :
        ##MAC:fmt## replaced by the client MAC address; ATM fmt can be:
          - short (pure MAC addr)
          - cisco (cisco-fmt MAC addr)
          - linux (linux-fmt MAC addr)
          - win (win-fmt MAC addr)
        """
        # list of replacements to perform
        # a replacement is using the following structure :
        # key 'from' : the PCRE to look for
        # key 'to' : the replacement to perform
        # key 'when' : when to perform the replacement (only 'global' for now)
        replacements = [
            ('##PULSE2_LANG##', PackageServerConfig().pxe_keymap, 'global'),
            ('##PULSE2_BOOTLOADER_DIR##', self.config.imaging_api['bootloader_folder'], 'global'),
            ('##PULSE2_BOOTSPLASH_FILE##', self.config.imaging_api['bootsplash_file'], 'global'),
            ('##PULSE2_DISKLESS_DIR##', self.config.imaging_api['diskless_folder'], 'global'),
            ('##PULSE2_DISKLESS_KERNEL##', self.config.imaging_api['diskless_kernel'], 'global'),
            ('##PULSE2_DISKLESS_INITRD##', self.config.imaging_api['diskless_initrd'], 'global'),
            ('##PULSE2_DISKLESS_MEMTEST##', self.config.imaging_api['diskless_memtest'], 'global'),
            ('##PULSE2_DISKLESS_DBAN##', self.config.imaging_api['diskless_dban'], 'global'),
            ('##PULSE2_DISKLESS_OPTS##', ' '.join(self.diskless_opts), 'global'),
            ('##PULSE2_KERNEL_OPTS##', ' '.join(self.kernel_opts), 'global'),
            ('##PULSE2_MASTERS_DIR##', self.config.imaging_api['masters_folder'], 'global'),
            ('##PULSE2_POSTINST_DIR##', self.config.imaging_api['postinst_folder'], 'global'),
            ('##PULSE2_COMPUTERS_DIR##', self.config.imaging_api['computers_folder'], 'global'),
            ('##PULSE2_BASE_DIR##', self.config.imaging_api['base_folder'], 'global'),
            ('##PULSE2_REVO_RAW##', self.rawmode, 'global'),
            ('##REVOWAIT##', self.mtftp_timeout, 'global')
            ]
        if self.mac:
            replacements.append(
                ('##MAC##',
                 pulse2.utils.reduceMACAddress(self.mac),
                 'global'))

        output = string
        for replacement in replacements:
            f, t, w = replacement
            if w == condition:
                output = re.sub(f, t, output)
        return output

    def _fill_reptable(self):
        """
         this function create array to remove accent who are not
         compatible with MS-DOS encoding
        """
        _reptable = {}
        _corresp = [
            ("a",  [0x00E3]),
            ]
        for repchar,codes in _corresp :
            for code in codes :
                _reptable[code] = repchar

        return _reptable

    def delete_diacritics(self, s) :
        """
        Delete accent marks.

        @param s: string to clean
        @type s: unicode
        @return: cleaned string
        @rtype: unicode
        """
        _reptable = self._fill_reptable()
        if isinstance(s, str):
            s = str(s, "utf8", "replace")
        ret = []
        for c in s:
            ret.append(_reptable.get(ord(c) ,c))
        return "".join(ret)

    def buildMenu(self):
        """
        @return: the GRUB boot menu as a string encoded using CP-437
        @rtype: str
        """

        # takes global items, one by one
        buf = '# Auto-generated by Pulse 2 Imaging Server on %s \n\n' % time.asctime()

        # the key mapping
        if PackageServerConfig().pxe_keymap == 'fr_FR':
            buf += 'kbdfr\n'

        # PXE Password entry if pxe_password is set
        if PackageServerConfig().pxe_password != '':
            buf += 'menupwd L=%s\n' % PackageServerConfig().pxe_keymap

        # the menu timeout : warning, 0 means "do not wait !"
        if self.timeout:
            buf += 'timeout %s\n' % self.timeout

        # the menu default item
        buf += 'default %s\n' % self.default_item

        # the menu splash image
        if self.splashimage:
            buf += self._applyReplacement('splashimage %s\n' % self.splashimage)

        # the menu colors
        buf += 'color %d/%d %d/%d\n' % (
            self.colors['normal']['fg'],
            self.colors['normal']['bg'],
            self.colors['highlight']['fg'],
            self.colors['highlight']['bg'])

        # do we hide the menu ? Splash screen will still be displayed
        if self.hidden:
            buf += 'hiddenmenu\n'

        # can the user access to the grub command line ?
        if self.bootcli:
            buf += 'nosecurity\n'

        # are we dropped to a shell after having booted ?
        if self.disklesscli:
            self.diskless_opts.append('revodebug')

        # Shall be fix NT Boot Loaders ?
        if self.ntblfix:
            self.diskless_opts.append('revontblfix')

        # Shall be fix check the disk size ?
        if self.dont_check_disk_size:
            self.diskless_opts.append('revonospc')

        # Use a specifix ethernet card
        if self.ethercard:
            self.diskless_opts.append('revoeth%d' % self.ethercard)

        # then write items
        indices = list(self.menuitems.keys())
        indices.sort()
        for i in indices:
            output = self._applyReplacement(self.menuitems[i].getEntry(self.protocol))
            buf += '\n'
            buf += output

        assert(type(buf) == str)

        # Clean brazilian characters who are not compatible
        # with MS-DOS encoding by delete accent marks
        buf = self.delete_diacritics(buf)

        # Encode menu using code page 437 (MS-DOS) encoding
        buf = buf.encode('cp437')
        return buf

    def write(self):
        """
        Write the boot menu to disk
        """
        if self.mac:
            filename = os.path.join(self.config.imaging_api['base_folder'], self.config.imaging_api['bootmenus_folder'], pulse2.utils.reduceMACAddress(self.mac))
            self.logger.debug('Preparing to write boot menu for computer MAC %s into file %s' % (self.mac, filename))
        else:
            filename = os.path.join(self.config.imaging_api['base_folder'], self.config.imaging_api['bootmenus_folder'], self.DEFAULT_MENU_FILE)
            self.logger.debug('Preparing to write the default boot menu for unregistered computers into file %s' % filename)


        def _write(self):
            try:
                buf = self.buildMenu()
            except Exception as e:
                logging.getLogger().error(str(e))

            backupname = "%s.backup" % filename
            if os.path.exists(filename):
                try:
                    os.rename(filename, backupname)
                except Exception as e:  # can make a backup : give up !
                    self.logger.error("While backuping boot menu %s as %s : %s" % (filename, backupname, e))
                    return False

            try:
                fid = open(filename, 'w+b')
                fid.write(buf)
                fid.close()
                for item in list(self.menuitems.values()):
                    try:
                        item.write(self.config)
                    except Exception as e:
                        self.logger.error('An error occurred while writing boot menu entry "%s": %s' % (str(item), str(e)))
                if self.mac:
                    self.logger.debug('Successfully wrote boot menu for computer MAC %s into file %s' % (self.mac, filename))
                else:
                    self.logger.debug('Successfully wrote boot menu for unregistered computers into file %s' % filename)
            except Exception as e:
                if self.mac:
                    self.logger.error("While writing boot menu for %s : %s" % (self.mac, e))
                else:
                    self.logger.error("While writing default boot menu : %s" % e)
                return False

            if os.path.exists(backupname):
                try:
                    os.unlink(backupname)
                except Exception as e:
                    self.logger.warn("While removing backup %s of %s : %s" % (backupname, filename, e))
        # Retreive PXE Params
        pulse2.package_server.imaging.api.functions.Imaging().refreshPXEParams(_write, self)

        return True

    def setTimeout(self, value):
        """
            set the default timeout
        """
        self.timeout = value

    def setDefaultItem(self, value):
        """
            set the default item number
        """
        assert(type(value) == int)
        self.default_item = value

    def addImageEntry(self, position, entry):
        """
        Add the ImagingEntry entry to our menu
        """
        assert(type(position) == int and position >= 0)
        if position in self.menuitems:
            raise ValueError('Position %d in menu already taken' % position)
        item = ImagingImageItem(entry)
        self.menuitems[position] = item

    def addBootServiceEntry(self, position, entry):
        """
        Add the ImagingEntry entry to our menu
        """
        assert(type(position) == int and position >= 0)
        if position in self.menuitems:
            raise ValueError('Position %d in menu already taken' % position)
        self.menuitems[position] = ImagingBootServiceItem(entry)

    def setKeyboard(self, mapping = None):
        """
        set keyboard map
        if mapping is none, do not set keymap
        """
        if mapping in ['fr', 'pt', 'de']:
            self.keyboard = mapping

    def hideMenu(self):
        """
        Hide the menu
        """
        self.hidden = True

    def showMenu(self):
        """
        Show the menu
        """
        self.hidden = False

    def setProtocol(self, value):
        """
        Set the restoration protocol.
        ATM protocol can be 'nfs', 'tftp' or 'tftp'
        """
        assert(value in ['nfs', 'tftp', 'mtftp'])
        self.protocol = value

    def setBootCLI(self, value):
        """
        set CLI access on boot (key "E")
        """
        value = bool(value)
        self.bootcli = value

    def setNTBLFix(self, value):
        """
        set NT Boot Loader fix
        """
        value = bool(value)
        self.ntblfix = value

    def setDisklessCLI(self, value):
        """
        do not drop to a shell when mastering
        """
        value = bool(value)
        self.disklesscli = value

    def setDiskSizeCheck(self, value):
        """
        do check disk size when restoring
        """
        value = bool(value)
        self.dont_check_disk_size = value

    def setEtherCard(self, value):
        """
        Set the ethernet restoration interface
        @param value : the iface number, between 0 (default) and 9
        """
        assert(type(value) == int)
        assert(value >= 0)
        assert(value <= 9)
        self.ethercard = value

    def setSplashImage(self, value):
        """
        Set the background image
        """
        if type(value) == str:
            value = value.decode('utf-8')
        assert(type(value) == str)
        self.splashimage = value

    def setMessage(self, value):
        """
        Set the warn message
        """
        if type(value) == str:
            value = value.decode('utf-8')
        assert(type(value) == str)
        self.message = value

    def setLanguage(self, value):
        """
        Set menu language, and keyboard map to use
        """
        try:
            self.language = self.LANG_CODE[value]
            if not self.keyboard:
                self.keyboard = self.KEYB_CODE[value]
        except KeyError:
            self.language = self.LANG_CODE[1]
            self.keyboard = self.KEYB_CODE[1]

    def setRawMode(self, value):
        """
        Set raw backup mode
        """
        value = bool(value)
        if value:
            self.rawmode = 'revoraw'

    def setMTFTPTimeout(self, value):
        """
        Set MTFTP wait timeout
        """
        self.mtftp_timeout = str(value)

    def hide(self, flag):
        """
        Hide or display menu
        """
        flag = bool(flag)
        self.hidden = flag


class ImagingItem:

    """
    Common class to hold an imaging menu item
    """

    def __init__(self, entry):
        """
        @param entry: menu item in dict format
        @type entry: dict
        """
        self.logger = logging.getLogger('imaging')
        self._convertEntry(entry)
        self.title = entry['name']  # the item title
        self.desc = entry['desc']  # the item desc
        assert(type(self.title) == str)
        assert(type(self.desc) == str)
        self.uuid = None

    def __str__(self):
        d = self.__dict__
        return str(d)

    def _applyReplacement(self, out, network = True):
        if network:
            device = '(nd)'
        else:
            # FIXME: Is it the right device name ?
            device = '(cdrom)'
        out = re.sub('##PULSE2_NETDEVICE##', device, out)
        if self.uuid:
            out = re.sub('##PULSE2_IMAGE_UUID##', self.uuid, out)
        return out

    def _convertEntry(self, array):
        """
        Convert dictionary value of type str to unicode.
        """
        for key, value in list(array.items()):
            if type(value) == str:
                value = value.decode('utf-8')
            array[key] = value

    def write(self, config):
        """
        Actions to do for this item
        """
        pass

    def getLogger(self):
        """
        return internal logger
        """
        return self.logger


class ImagingBootServiceItem(ImagingItem):

    """
    hold an imaging menu item for a boot service
    """

    def __init__(self, entry):
        """
        @param entry : a ImagingItem
        """
        ImagingItem.__init__(self, entry)
        self.value = entry['value']  # the GRUB command line
        assert(type(self.value) == str)

    def getEntry(self, protocol, network = True):
        """
        Return the entry, in a GRUB compatible format
        """
        buf = 'title %s\n' % self.title
        if self.desc:
            buf += 'desc %s\n' % self.desc
        if self.value:
            buf += self.value + '\n'
        return self._applyReplacement(buf, network)

    def writeShFile(self, script_file):
        """
        Create a sh file who contains commands of PostInstallScript
        """
        config = PackageServerConfig()
        postinst_scripts_folder = os.path.join(config.imaging_api['base_folder'],
                                config.imaging_api['postinst_folder'],
                                'scripts')
        if not os.path.exists(postinst_scripts_folder):
            self.logger.info("Postinst scripts directory %s doesn't exists, create it", postinst_scripts_folder)
            os.mkdir(postinst_scripts_folder)

        # Create and write sh file
        try:
            f = open(os.path.join(postinst_scripts_folder, script_file[0]), 'w')
            f.write(script_file[1])
            f.close()
            self.logger.info('File %s successfully created', os.path.join(postinst_scripts_folder, script_file[0]))
        except Exception as e:
            self.logger.exception('Error while create sh file: %s', e)

        return True

    def unlinkShFile(self, bs_uuid):
        """
        Delete sh file created by post-install script
        """
        script_file = bs_uuid[0] + ".sh"

        config = PackageServerConfig()
        postinst_scripts_folder = os.path.join(config.imaging_api['base_folder'],
                                config.imaging_api['postinst_folder'],
                                'scripts')
        if not os.path.exists(postinst_scripts_folder):
            self.logger.exception("Error while delete sh file: Post-install script folder %s doesn't exists", postinst_scripts_folder)
            raise Exception ("Post-install script folder %s doesn't exists", postinst_scripts_folder)

        try:
            os.unlink(os.path.join(postinst_scripts_folder, script_file))
        except Exception as e:
            self.logger.exception("Error while delete sh file: %s", e)


class ImagingImageItem(ImagingItem):

    """
    Hold an imaging menu item for a image to restore
    """

    # Important: This is a cmdline for a restore process
    # For "Create an image" it's a bootservice and its command line is in the DB
    # TODO: for clonezilla backend it will be useful to clean some of unused params

    # Grub cmdlines
    CMDLINE = "kernel ##PULSE2_NETDEVICE##/##PULSE2_DISKLESS_DIR##/##PULSE2_DISKLESS_KERNEL## ##PULSE2_KERNEL_OPTS## ##PULSE2_DISKLESS_OPTS## revosavedir=##PULSE2_MASTERS_DIR## revoinfodir=##PULSE2_COMPUTERS_DIR## revooptdir=##PULSE2_POSTINST_DIR## revobase=##PULSE2_BASE_DIR## ##PROTOCOL## revopost revomac=##MAC## revoimage=##PULSE2_IMAGE_UUID## \ninitrd ##PULSE2_NETDEVICE##/##PULSE2_DISKLESS_DIR##/##PULSE2_DISKLESS_INITRD##\n"

    PROTOCOL = {
        'nfs'   : 'revorestorenfs',
        'tftp'  : '',
        'mtftp' : 'revorestoremtftp'}

    POSTINST = '%02d_postinst'
    POSTINSTDIR = 'postinst.d'

    def __init__(self, entry):
        """
        Initialize this object.
        """
        ImagingItem.__init__(self, entry)
        self.uuid = entry['uuid']
        assert(type(self.uuid) == str)
        if 'post_install_script' in entry:
            assert(type(entry['post_install_script']) == list)
            self.post_install_script = entry['post_install_script']
        else:
            self.post_install_script = None

        # Davos imaging client case
        if PackageServerConfig().imaging_api['diskless_folder'] == "davos":
            self.CMDLINE = "kernel ##PULSE2_NETDEVICE##/##PULSE2_DISKLESS_DIR##/##PULSE2_DISKLESS_KERNEL## ##PULSE2_KERNEL_OPTS## ##PULSE2_DISKLESS_OPTS## ##PROTOCOL## image_uuid=##PULSE2_IMAGE_UUID## davos_action=RESTORE_IMAGE \ninitrd ##PULSE2_NETDEVICE##/##PULSE2_DISKLESS_DIR##/##PULSE2_DISKLESS_INITRD##\n"


    def getEntry(self, protocol, network = True):
        """
        @param network: if True, build a menu for a network restoration , else
                        build a menu for a CD-ROM restoration

        @return: the entry, in a GRUB compatible format.
        """
        buf = 'title %s\n' % self.title
        if self.desc:
            buf += 'desc %s\n' % self.desc
        if protocol not in self.PROTOCOL:
            protocol = 'nfs'
        replacement = self.PROTOCOL[protocol]
        # Add REVOWAIT on kernel command line for MTFTP
        if protocol == 'mtftp':
            replacement +=  ' ' + 'revowait=##REVOWAIT##'
        buf += self.CMDLINE.replace('##PROTOCOL##', replacement)
        return self._applyReplacement(buf, network)

    def write(self, config):
        """
        Write post-imaging scripts if any.
        """
        # First: remove old post-imaging directory if there is one
        postinstdir = os.path.join(config.imaging_api['base_folder'], config.imaging_api['masters_folder'], self.uuid, self.POSTINSTDIR)
        if os.path.exists(postinstdir):
            self.logger.debug('Deleting previous post-imaging directory: %s' % postinstdir)
            try:
                shutil.rmtree(postinstdir)
            except OSError as e:
                self.logger.error("Can't delete post-imaging directory %s: %s" % (postinstdir, e))
                raise
        # Then populate the post-imaging script directory if needed
        if self.post_install_script:
            try:
                os.mkdir(postinstdir)
                self.logger.debug('Directory successfully created: %s' % postinstdir)
            except OSError as e:
                self.logger.error("Can't create post-imaging script folder %s: %s" % (postinstdir, e))
                raise

            order = 1  # keep 0 for later use
            for script in self.post_install_script:
                postinst = os.path.join(postinstdir, self.POSTINST % order)
                try:
                    # write header
                    f = open(postinst, 'w+')
                    f.write('#!/bin/sh\n')
                    f.write('\n')
                    f.write('. /opt/lib/libpostinst.sh')
                    f.write('\n')
                    f.write('echo "==> postinstall script #%d : %s"\n' % (order, script['name'].encode('utf-8')))
                    f.write('set -v\n')
                    f.write('\n')
                    f.write(script['value'].encode('utf-8'))
                    f.close()
                    os.chmod(postinst, stat.S_IRUSR | stat.S_IXUSR)
                    self.logger.debug('Successfully wrote script: %s' % postinst)
                except OSError as e:
                    self.logger.error("Can't update post-imaging script %s: %s" % (postinst, e))
                    raise
                except Exception as e:
                    self.logger.error("Something wrong happened while writing post-imaging script %s: %s" % (postinst, e))
                order += 1
        else:
            self.logger.debug('No post-imaging script to write')


def changeDefaultMenuItem(macaddress, value):
    """
    Change the default menu item of a computer.

    This method directly changes the content of the boot menu file of a
    computer. It should be used only when the MMC-agent can't be reached.

    @param macaddress: computer MAC address
    @type macaddress: str

    @param value: defaut item value
    @type value: int

    @return: True if success, else False
    @rtype: bool
    """
    config = PackageServerConfig()
    logger = logging.getLogger('imaging')
    filename = os.path.join(config.imaging_api['base_folder'],
                            config.imaging_api['bootmenus_folder'],
                            pulse2.utils.reduceMACAddress(macaddress))
    logger.debug('Changing default boot menu item of computer MAC %s'
                 % macaddress)
    newlines = ''
    try:
        for line in open(filename):
            if line.startswith('default '):
                if line == 'default %d\n' % value:
                    logger.debug('Default item is already set to %d, nothing to do.' % value)
                    return True
                # Change default menu item
                newlines += 'default %d\n' % value
            else:
                newlines += line
    except OSError as e:
        logger.error('Error while reading computer bootmenu file %s: %s' %
                          (filename, str(e)))
        logger.error('This computer default menu item can\'t be changed')
        newlines = ''
    if newlines:
        backupname = "%s.backup" % filename
        try:
            os.rename(filename, backupname)
        except OSError as e:  # can make a backup : give up !
            logger.error("While backuping boot menu %s as %s : %s"
                         % (filename, backupname, e))
            return False
        # Write new boot menu
        try:
            fid = open(filename, 'w+b')
            fid.write(newlines)
            fid.close()
            logger.debug('Successfully wrote boot menu for computer MAC %s into file %s' % (macaddress, filename))
        except IOError as e:
            logger.error("While writing boot menu for %s : %s"
                         % (macaddress, e))
            return False
        # Remove boot menu backup
        try:
            os.unlink(backupname)
        except OSError as e:
            logger.warn("While removing backup %s of %s : %s"
                        % (backupname, filename, e))
        return True
    return False
