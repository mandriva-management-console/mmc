import ConfigParser

from mmc.support.config import PluginConfig


class SambaConfig(PluginConfig):

    def readConf(self):
        PluginConfig.readConf(self)
        self.baseComputersDN = self.get("main", "baseComputersDN")
        # Handle deprecated config option and correct the NoOptionError exception to the new option
        try:
            if self.has_option("main","defaultSharesPath"):
                self.defaultSharesPath = self.get("main", "defaultSharesPath")
            else:
                self.defaultSharesPath = self.get("main", "sharespath")
        except ConfigParser.NoOptionError:
            raise ConfigParser.NoOptionError("defaultSharesPath", "main")

        try: self.samba_conf_file = self.get("main", "sambaConfFile")
        except: pass
        try: self.samba_init_script = self.get("main", "sambaInitScript")
        except: pass
        try: self.av_so = self.get("main", "sambaAvSo")
        except: pass

        try:
            listSharePaths = self.get("main", "authorizedSharePaths")
            self.authorizedSharePaths = listSharePaths.replace(' ','').split(',')
        except:
            self.authorizedSharePaths = [self.defaultSharesPath]

    def setDefault(self):
        PluginConfig.setDefault(self)
        self.samba_conf_file = '/etc/samba/smb.conf'
        self.samba_init_script = '/etc/init.d/samba'
        self.av_so = "/usr/lib/samba/vfs/vscan-clamav.so"

