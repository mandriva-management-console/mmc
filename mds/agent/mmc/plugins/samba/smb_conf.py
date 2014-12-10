import os
import operator
import ldap
import stat
import grp
import pwd
import shutil
import tempfile
import logging
from time import mktime, strptime
from configobj import ConfigObj, ParseError

from mmc.plugins.base import ldapUserGroupControl
from mmc.support.mmctools import shLaunch, shlaunch
from mmc.core.audit import AuditFactory as AF

from mmc.plugins.samba.config import SambaConfig
from mmc.plugins.samba.audit import AT, AA, PLUGIN_NAME
from mmc.plugins.samba.signals import share_modified, share_created


logger = logging.getLogger()

try:
    import posix1e
except ImportError:
    logger.error("Python module pylibacl not found...\nPlease install :\n  * python-pylibacl on Debian/Ubuntu\n  * python-libacl on CentOS 4.3\n  * pylibacl on Mandriva 2006\n")
    raise


class SambaConf:
    supportedGlobalOptions = ["workgroup", "netbios name", "logon path", "logon drive", "logon home", "logon script", "ldap passwd sync", "wins support"]
    supportedOptions = ['comment', 'path', 'public', 'read only', 'guest ok', 'browseable', 'browsable', 'group', 'admin users', 'writable', 'writeable', 'write list', 'valid users']

    def __init__(self, smbconffile="/etc/samba/smb.conf", conffile=None, conffilebase=None):
        """
        Constructor for object that read/write samba conf file.

        We use the testparm command on the smb configuration file to sanitize it,
        and to replace all keyword synonyms with the preferred keywords:
         - 'write ok', 'writeable', 'writable' -> 'read only'
         - 'public' -> 'guest ok'
         ...

        In SAMBA source code, parameters are defined in param/loadparm.c
        """
        config = SambaConfig("samba", conffile)
        self.defaultSharesPath = config.defaultSharesPath
        self.authorizedSharePaths = config.authorizedSharePaths
        self.conffilebase = conffilebase
        self.smbConfFile = smbconffile
        # Parse SAMBA configuration file
        try:
            self.config = ConfigObj(self.smbConfFile, interpolation=False,
                                    list_values=False, write_empty_values=True,
                                    encoding='utf8')
        except ParseError, e:
            logger.error("Failed to parse %s : %s " % (self.smbConfFile, e))

    def validate(self, conffile="/etc/samba/smb.conf"):
        """
        Validate SAMBA configuration file with testparm.
        Try also to parse the configuration with configObj

        @return: Return True if smb.conf has been validated, else return False
        """
        cmd = shLaunch("/usr/bin/testparm -s %s" % conffile)
        if cmd.exitCode:
            ret = False
        elif "Unknown" in cmd.err or "ERROR:" in cmd.err or "Ignoring badly formed line" in cmd.err:
            ret = False
        else:
            ret = True

        try:
            ConfigObj(conffile, interpolation=False, list_values=False)
        except ParseError:
            ret = False

        return ret

    def isValueTrue(self, string):
        """
        @param string: a string
        @type string: str
        @return: Return 1 if string is yes/true/1 (case insensitive), return 0 if string is no/false/0 (case insensitive), else return -1
        $rtype: int
        """
        string = str(string).lower()
        if string in ["yes", "true", "1", "on"]:
            return 1
        elif string in ["no", "false", "0"]:
            return 0
        else:
            return -1

    def isValueAuto(self, string):
        """
        @param string: a string
        @type string: str
        @return: Return True if string is 'auto' (case insensitive), else return False
        $rtype: int
        """
        string = string.lower()
        return string == "auto"

    def mapOptionValue(self, value):
        """
        Translate option value to SAMBA value
        """
        mapping = {"on": "Yes", "off": "No"}
        try:
            ret = mapping[value]
        except KeyError:
            ret = value
        return ret

    def getSmbInfo(self):
        """
        return main information about global section
        """
        resArray = {}
        resArray['logons'] = self.isValueTrue(self.getContent('global', 'domain logons'))
        resArray['master'] = self.isValueTrue(self.getContent('global', 'domain master'))
        if resArray['master'] == -1:
            resArray["master"] = self.isValueAuto(self.getContent('global', 'domain master'))
        resArray['hashomes'] = 'homes' in self.config
        resArray['pdc'] = (resArray['logons']) and (resArray['master'])
        for option in self.supportedGlobalOptions:
            resArray[option] = self.getContent("global", option)
        return resArray

    def isPdc(self):
        ret = self.getSmbInfo()
        return ret["pdc"]

    def isProfiles(self):
        ret = self.getSmbInfo()
        if ret["logon path"]:
            return True
        else:
            return False

    def getContent(self, section, option):
        try:
            return self.config[section][option]
        except KeyError:
            return False

    def setContent(self, section, option, value):
        try:
            self.config[section][option] = value
        except KeyError:
            self.config[section] = {}
            self.setContent(section, option, value)

    def remove(self, section, option):
        """
        Remove an option from a section.
        """
        try:
            del self.config[section][option]
        except KeyError:
            pass

    def smbInfoSave(self, options):
        """
        Set information in global section:
         @param options: dict with global options
        """
        current = self.getSmbInfo()

        # Don't write an empty value
        # Use the SAMBA default
        for option in ["logon home", "logon drive"]:
            if options[option] == "":
                self.remove("global", option)
                del options[option]

        # We update only what has changed from the current configuration
        for option in self.supportedGlobalOptions:
            try:
                if option in options:
                    options[option] = self.mapOptionValue(options[option])
                    if options[option] != current[option]:
                        self.setContent("global", option, options[option])
                    # else do nothing, the option is already set
                else:
                    self.remove("global", option)
            except KeyError:
                # Just ignore the option if it was not sent
                pass

        if current["pdc"] != options['pdc']:
            if options['pdc']:
                self.setContent('global', 'domain logons', 'yes')
                self.setContent('global', 'domain master', 'yes')
                self.setContent('global', 'os level', '255')
            else:
                self.setContent('global', 'domain logons', 'no')
                self.remove('global', 'domain master')
                self.remove('global', 'os level')

        if options['hashomes']:
            self.setContent('homes', 'comment', 'User shares')
            self.setContent('homes', 'browseable', 'no')
            self.setContent('homes', 'read only', 'no')
            self.setContent('homes', 'create mask', '0700')
            self.setContent('homes', 'directory mask', '0700')
            # Set the vscan-av plugin if available
            if os.path.exists(SambaConfig("samba").av_so):
                self.setContent("homes", "vfs objects", os.path.splitext(os.path.basename(SambaConfig("samba").av_so))[0])
        elif 'homes' in self.config:
            del self.config["homes"]
            self.setContent('global', 'logon home', '')

        # Disable global profiles
        if not options['hasprofiles']:
            self.setContent('global', 'logon path', '')

        # Save file
        self.save()
        return 0

    def getDetailedShares(self):
        """return detailed list of shares"""
        resList = []
        # foreach element in smb.conf
        # so for each element in self.config
        for section in self.getSectionList():
            if section not in ["global", "printers", "print$"]:
                localArr = []
                localArr.append(section)
                comment = self.getContent(section, 'comment')
                if comment:
                    localArr.append(comment)
                resList.append(localArr)

        resList.sort()
        return resList

    def getSectionList(self):
        section_list = []
        for k, v in self.config.items():
            section_list.append(k)
        return section_list

    def save(self):
        """
        Write SAMBA configuration file (smb.conf) to disk
        """

        handle, tmpfname = tempfile.mkstemp("mmc")
        self.config.filename = tmpfname
        self.config.write()
        if not self.validate(tmpfname):
            raise Exception("smb.conf file is not valid")
        shutil.copy(tmpfname, self.smbConfFile)
        os.remove(tmpfname)

    def delShare(self, name, remove):
        """
        Delete a share from SAMBA configuration, and maybe delete the share
        directory from disk.
        The save method must be called to update smb.conf.

        @param name: Name of the share
        @param remove: If true, we physically remove the directory
        """
        r = AF().log(PLUGIN_NAME, AA.SAMBA_DEL_SHARE, [(name, AT.SHARE)], remove)
        path = self.getContent(name, 'path')
        if not path:
            raise Exception('Share "' + name + '" does not exist')
        del self.config[name]

        if remove:
            if os.path.exists(path):
                shutil.rmtree(path)
            else:
                logger.error('The "%s" share path does not exist.' % path)
        r.commit()

    def shareInfo(self, name):
        """
        Get information about a share
        """
        returnArr = {}
        returnArr['desc'] = self.getContent(name, 'comment')
        if not returnArr['desc']:
            returnArr['desc'] = ""
        returnArr['sharePath'] = self.getContent(name, 'path')
        if self.isValueTrue(self.getContent(name, 'public')) == 1:
            returnArr['permAll'] = 1
        elif self.isValueTrue(self.getContent(name, 'guest ok')) == 1:
            returnArr['permAll'] = 1
        else:
            returnArr['permAll'] = 0

        # If we cannot find it
        if not self.getContent(name, 'vfs objects'):
            returnArr['antivirus'] = 0
        else:
            returnArr['antivirus'] = 1

        if not self.getContent(name, 'browseable'):
            returnArr["browseable"] = 1
        elif self.isValueTrue(self.getContent(name, 'browseable')):
            returnArr["browseable"] = 1
        else:
            returnArr["browseable"] = 0

        # Get the directory group owner
        if os.path.exists(str(returnArr['sharePath'])):
            stat_info = os.stat(returnArr['sharePath'])
            gid = stat_info.st_gid
            try:
                returnArr['group'] = grp.getgrgid(gid)[0]
            except:
                logger.error("Can't find the primary group of %s. Check your libnss settings." % returnArr['sharePath'])
                return False

        return returnArr

    def shareCustomParameters(self, name):
        """
        Get additional parameters about a share
        """

        returnArr = []
        for key, value in self.config[name].iteritems():
            if key not in self.supportedOptions:
                returnArr.append(key + " = " + value)

        return returnArr

    def addShare(self, name, path, comment, perms, admingroups, recursive=True,
                 browseable=True, av=False, customparameters=None, mod=False):
        """
        add a share in smb.conf
        and create it physicaly

        perms = {
            'rx': ['user1', '@group1'],
            'rwx': ['user2', '@group2']
        }

        perms = {
            'rwx': ['@all']
        }
        """

        if mod:
            action = AA.SAMBA_MOD_SHARE
            oldPath = self.config[name]['path']
        else:
            action = AA.SAMBA_ADD_SHARE
        r = AF().log(PLUGIN_NAME, action, [(name, AT.SHARE)], path)

        if name in self.config and not mod:
            raise Exception('This share already exist')
        if name not in self.config and mod:
            raise Exception('This share does not exist')

        # If no path is given, create a default one
        if not path:
            path = os.path.join(self.defaultSharesPath, name)
        path = os.path.realpath(path)

        # Check that the path is authorized
        # FIXME: handle correctly archives in base plugin
        if not self.isAuthorizedSharePath(path) and "/home/archives" not in path:
            raise Exception("%s is not an authorized share path.")

        # Create or move samba share directory, if it does not exist
        try:
            if mod:
                os.renames(oldPath, path)
            else:
                os.makedirs(path)
        except OSError, (errno, strerror):
            # Raise exception if error is not "File exists"
            if errno != 17:
                raise OSError(errno, strerror + ' ' + path)
            else:
                pass

        # Directory is owned by root
        os.chown(path, 0, 0)

        if mod:
            # Delete the old share
            del self.config[name]

        # create table and fix permission
        tmpInsert = {}

        # We insert first custom parameters, so if the user has
        # entered manually any reserved key, that key is overriden
        # below, with the values of specific fields.
        if customparameters is not None:
            for line in customparameters:
                if len(line) > 0:
                    parts = line.split("=", 1)
                    if len(parts) is 2:
                        if not parts[0].strip() in self.supportedOptions:
                            tmpInsert[parts[0].strip()] = parts[1].strip()
                    else:
                        raise Exception("invalid samba parameter format")

        tmpInsert['path'] = path
        tmpInsert['comment'] = comment

        if not browseable:
            tmpInsert['browseable'] = 'No'

        # flush ACLs
        shlaunch("setfacl -b %s" % path)

        def sanitize_name(name):
            if ' ' in name:
                name = '"' + name + '"'
            return name

        if '@all' in perms['rwx']:
            tmpInsert['public'] = 'yes'
            tmpInsert['writeable'] = 'yes'
            shlaunch("setfacl -b %s" % path)
            os.chmod(path, stat.S_IRWXU | stat.S_IRWXG | stat.S_IRWXO)
        else:
            tmpInsert['public'] = 'no'
            tmpInsert['writeable'] = 'no'
            os.chmod(path, stat.S_IRWXU | stat.S_IRWXG)
            acls = posix1e.ACL(file=path)
            # Add and set default mask to rwx
            # This is needed by the ACL system, else the ACLs won't be valid
            e = acls.append()
            e.permset.add(posix1e.ACL_READ)
            e.permset.add(posix1e.ACL_WRITE)
            e.permset.add(posix1e.ACL_EXECUTE)
            e.tag_type = posix1e.ACL_MASK
            # For each specified group, we add rwx access
            ldapobj = ldapUserGroupControl(self.conffilebase)

            def add_acl_permset(acl, entity, rights):
                e = acls.append()
                if 'r' in rights:
                    e.permset.add(posix1e.ACL_READ)
                if 'w' in rights:
                    e.permset.add(posix1e.ACL_WRITE)
                if 'x' in rights:
                    e.permset.add(posix1e.ACL_EXECUTE)
                if entity.startswith('@'):
                    e.tag_type = posix1e.ACL_GROUP
                    try:
                        gidNumber = ldapobj.getDetailedGroup(entity[1:])['gidNumber'][0]
                    except ldap.NO_SUCH_OBJECT:
                        gidNumber = grp.getgrnam(entity[1:]).gr_gid
                    e.qualifier = int(gidNumber)
                else:
                    e.tag_type = posix1e.ACL_USER
                    try:
                        uidNumber = ldapobj.getDetailedUser(entity)['uidNumber'][0]
                    except KeyError:
                        uidNumber = pwd.getpwnam(entity).pw_uid
                    e.qualifier = int(uidNumber)

            for rights, entities in perms.items():
                for entity in entities:
                    add_acl_permset(acls, entity, rights)

            # Test if our ACLs are valid
            if acls.valid():
                acls.applyto(path)
                acls.applyto(path, posix1e.ACL_TYPE_DEFAULT)
                if recursive:
                    for root, dirs, files in os.walk(path):
                        acls.applyto(root)
                        acls.applyto(root, posix1e.ACL_TYPE_DEFAULT)
                        for file in map(lambda f: os.path.join(root, f), files):
                            acls.applyto(file)
            else:
                logger.error("Cannot save ACL on folder " + path)

            tmpInsert['valid users'] = ', '.join(map(sanitize_name, reduce(operator.add, [j for p, j in perms.items()])))
            tmpInsert['write list'] = ', '.join(map(sanitize_name, reduce(operator.add, [j for p, j in perms.items() if 'w' in p])))

        # Set the anti-virus plugin if available
        if av:
            tmpInsert['vfs objects'] = os.path.splitext(os.path.basename(SambaConfig("samba").av_so))[0]

        # Set the admin groups for the share
        if admingroups:
            # need to add '@'
            admingroups = map(lambda g: '@' + g, admingroups)
            tmpInsert["admin users"] = ", ".join(map(sanitize_name, admingroups))
            # include admin users in valid users list !
            tmpInsert['valid users'] = tmpInsert['valid users'] + ', ' + tmpInsert["admin users"]

        self.config[name] = tmpInsert

        info = self.shareInfo(name)
        if mod:
            share_modified.send(sender=self, share_name=name, share_info=info)
        else:
            share_created.send(sender=self, share_name=name, share_info=info)

        r.commit()

    def getACLOnShare(self, name):
        """
        Return a list with all the groups that have rwx access to the share.

        @param name: name of the share (last component of the path)
        @type name: str

        @rtype: dict
        @return: dict of permissions: [list of users/groups]
        """
        ldapobj = ldapUserGroupControl(self.conffilebase)
        path = self.getContent(name, "path")
        public = self.getContent(name, "public")
        perms = {'rx': [], 'rwx': []}
        if path is False:
            return perms
        if public == "yes":
            return {'rwx': ['@all']}
        acls = posix1e.ACL(file=path)
        for e in acls:
            permset = zip(['r', 'w', 'x'], [e.permset.read, e.permset.write, e.permset.execute])
            perm = ''.join([r for r, b in permset if b is True])
            entity = ""

            if e.tag_type == posix1e.ACL_GROUP:
                res = ldapobj.getDetailedGroupById(str(e.qualifier))
                if res:
                    entity = '@' + res['cn'][0]
                else:
                    entity = '@' + grp.getgrgid(e.qualifier).gr_name

            if e.tag_type == posix1e.ACL_USER:
                res = ldapobj.getDetailedUserById(str(e.qualifier))
                if res:
                    entity = res['uid'][0]
                else:
                    entity = pwd.getpwuid(e.qualifier).pw_name

            if perm not in perms and entity:
                perms[perm] = [entity]
            elif entity:
                perms[perm].append(entity)

        return perms

    def getAdminUsersOnShare(self, name):
        """
        Return a list of all the groups in the admin users option of the given share.

        @param name: name of the share
        @type name: str

        @rtype: list
        @return: list of administrator groups of the share
        """
        adminusers = self.getContent(name, "admin users")
        translation_table = dict.fromkeys(map(ord, '"@&+'), None)
        ret = []
        if adminusers:
            for item in adminusers.split(","):
                item = item.strip().translate(translation_table)
                # Remove the SAMBA domain part
                if "\\" in item:
                    item = item.split("\\")[1]
                ret.append(item)
        return ret

    def isBrowseable(self, name):
        """
        Return true if the share is browseable

        @param name: name of the share (last component of the path)
        @type name: str

        @rtype: bool
        @return: False if browseable = No
        """
        state = self.getContent(name, "browseable")
        if not state:
            ret = True
        else:
            ret = bool(self.isValueTrue(state))
        return ret

    def getSmbStatus(self):
        """
        Return SAMBA shares connection status
        """
        code, output, err = shlaunch('/usr/bin/net status shares parseable')
        service = {}

        for line in output:
            if line.strip():
                tab = line.strip().split('\\', 7)
                serviceitem = {}
                serviceitem['pid'] = tab[0]

                # Create unix timestamp
                serviceitem['lastConnect'] = mktime(strptime(tab[6]))

                serviceitem['machine'] = tab[4]

                if tab[2]:
                    serviceitem['useruid'] = tab[2]
                    serviceitem['ip'] = tab[5]
                else:
                    serviceitem['useruid'] = 'anonymous'

                if tab[0] == tab[2]:
                    indIndex = "homes"
                else:
                    indIndex = tab[0]

                if indIndex not in service:
                    service[indIndex] = list()

                service[indIndex].append(serviceitem)

        return service

    def getConnected(self):
        """
        Return all opened SAMBA sessions
        """
        code, output, err = shlaunch('/usr/bin/net status sessions parseable')
        result = []
        for line in output:
            if line.strip():
                # 7727\useruid\Domain Users\machine\192.168.0.17
                # 0    1       2            3       4
                tab = line.strip().split('\\', 5)
                sessionsitem = {}
                sessionsitem['pid'] = tab[0]
                sessionsitem['useruid'] = tab[1]
                sessionsitem['machine'] = tab[3]
                sessionsitem['ip'] = tab[4]
                result.append(sessionsitem)
        return result

    def isAuthorizedSharePath(self, path):
        """
        @return: True if the given path is authorized to create a SAMBA share
        @rtype: bool
        """
        ret = False
        for apath in self.authorizedSharePaths:
            ret = apath + "/" in path
            if ret:
                break
        return ret
