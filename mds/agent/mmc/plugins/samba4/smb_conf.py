# -*- coding: utf-8; -*-
#
# (c) 2014 Mandriva, http://www.mandriva.com/
#
# This file is part of Mandriva Management Console (MMC).
#
# MMC is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# MMC is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MMC.  If not, see <http://www.gnu.org/licenses/>.
#
# Author(s):
#   Jesús García Sáez <jgarcia@zentyal.com>
#   Kamen Mazdrashki <kmazdrashki@zentyal.com>
#

import grp
import ldap
import logging
import os
import pwd
import shutil
import stat
import tempfile
from configobj import ConfigObj, ParseError
from jinja2 import Environment, PackageLoader
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.base import ldapUserGroupControl
from mmc.plugins.samba4.audit import AT, AA, PLUGIN_NAME
from mmc.plugins.samba4.config import Samba4Config
from mmc.plugins.samba4.helpers import get_internal_interfaces, shellquote
from mmc.plugins.samba4.signals import share_modified, share_created
from mmc.support.mmctools import shLaunch, shlaunch


logger = logging.getLogger()
env = Environment(loader=PackageLoader('mmc.plugins.samba4', 'templates'))

try:
    import posix1e
except ImportError:
    logger.error("Python module pylibacl not found...\n"
                 "Please install :\n"
                 "  * python-pylibacl on Debian/Ubuntu\n"
                 "  * python-libacl on CentOS 4.3\n"
                 "  * pylibacl on Mandriva 2006\n")
    raise


class SambaConf:
    """
    Handle smb.conf file for Samba 4
    """
    KRB5_CONF_PATH = '/etc/krb5.conf'

    def __init__(self):
        config = Samba4Config("samba4")
        self.smb_conf_path = config.conf_file
        self.default_shares_path = config.defaultSharesPath
        self.authorizedSharePaths = config.authorizedSharePaths
        self.prefix = config.samba_prefix
        try:
            self.config = ConfigObj(self.smb_conf_path, interpolation=False,
                                    list_values=False, write_empty_values=True,
                                    encoding='utf8')
        except ParseError as e:
            logger.error("Failed to parse %s : %s " % (self.smb_conf_path, e))

    def private_dir(self):
        return os.path.join(self.prefix, 'private')

    def validate(self, conf_file):
        """
        Validate SAMBA configuration file with testparm.
        Try also to parse the configuration with ConfigObj.

        @return: Whether smb.conf has been validated or not
        @rtype: boolean
        """
        cmd = shLaunch("%s/bin/testparm -s %s" % (self.prefix,
                                                  shellquote(conf_file)))
        if cmd.exitCode:
            ret = False
        elif ("Unknown" in cmd.err or "ERROR:" in cmd.err or
              "Ignoring badly formed line" in cmd.err):
            ret = False
        else:
            ret = True

        try:
            ConfigObj(conf_file, interpolation=False, list_values=False)
        except ParseError:
            ret = False

        return ret

    def isValueTrue(self, string):
        """
        @param string: a string
        @type string: str
        @return: Return 1 if string is yes/true/1 (case insensitive),
        return 0 if string is no/false/0 (case insensitive), else return -1
        @rtype: int
        """
        string = str(string).lower()
        if string in ["yes", "true", "1", "on"]:
            return 1
        elif string in ["no", "false", "0"]:
            return 0
        else:
            return -1

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

    def getGlobalInfo(self):
        """
        return main information about global section
        """
        GLOBAL_OPTIONS = ['realm', 'workgroup', 'netbios name', 'server role']
        resArray = {}
        for option in GLOBAL_OPTIONS:
            resArray[option] = self.getContent('global', option)
        return resArray

    def workgroupFromRealm(self, realm):
        return realm.split('.')[0][:15].upper()

    def writeSambaConfig(self, mode, netbios_name, realm, description):
        """
        Write SAMBA configuration file (smb.conf) to disk.

        @return values used to write the smb.conf template
        @rtype: dict
        """
        openchange = False  # FIXME
        openchange_conf = self.prefix + 'etc/openchange.conf'
        workgroup = self.workgroupFromRealm(realm)
        netbios_name = netbios_name.lower()
        realm = realm.upper()
        domain = realm.lower()
        params = {'workgroup': workgroup,
                  'realm': realm,
                  'netbios_name': netbios_name,
                  'description': description,
                  'mode': mode,
                  'sysvol_path': os.path.join(self.prefix, 'var/locks/sysvol'),
                  'openchange': openchange,
                  'openchange_conf': openchange_conf,
                  'domain': domain,
                  'interfaces': get_internal_interfaces()}
        smb_conf_template = env.get_template("smb.conf")
        with open(self.smb_conf_path, 'w') as f:
            f.write(smb_conf_template.render(params))

        if openchange:
            openchange_conf_template = env.get_template("openchange.conf")
            with open(openchange_conf, 'w') as f:
                f.write(openchange_conf_template.render())
        return params

    def writeKrb5Config(self, realm):
        params = {'realm': realm}
        krb5_conf_template = env.get_template('krb5.conf')
        with open(self.KRB5_CONF_PATH, 'w') as f:
            f.write(krb5_conf_template.render(params))

    def getDetailedShares(self):
        """Return detailed list of shares"""
        return [self.getDetailedShare(section)
                for section in self._getSharesSectionList()]

    def getDetailedShare(self, section):
        guest = (self.isValueTrue(self.getContent(section, 'public')) == 1 or
                 self.isValueTrue(self.getContent(section, 'guest ok')) == 1)
        enabled = (not self.getContent(section, 'browseable') or
                   self.isValueTrue(self.getContent(section, 'browseable')) == 1)
        share_detail = {
            'shareName': section,
            'sharePath': self.getContent(section, 'path'),
            'shareEnable': enabled,
            'shareDescription': self.getContent(section, 'comment') or '',
            'shareGuest': guest
        }
        #return share_detail
        return [share_detail['shareName'], share_detail['sharePath'], share_detail['shareEnable'],
                share_detail['shareDescription'], share_detail['shareGuest']]

    def _getSharesSectionList(self):
        return [k for k, _ in self.config.items()
                if k not in ("global", "printers", "print$")]

    def save(self):
        """
        Write SAMBA configuration file (smb.conf) to disk
        """
        _, tmpfname = tempfile.mkstemp("mmc")
        self.config.filename = tmpfname
        self.config.write()
        if not self.validate(tmpfname):
            raise Exception("smb.conf file is not valid (%s)" % tmpfname)
        shutil.copy(tmpfname, self.smb_conf_path)
        os.remove(tmpfname)
        return True

    def delShare(self, name, remove):
        """
        Delete a share from SAMBA configuration, and maybe delete the share
        directory from disk.
        The save method must be called to update smb.conf.

        @param name: Name of the share
        @param remove: If true, we physically remove the directory
        """
        r = AF().log(PLUGIN_NAME, AA.SAMBA4_DEL_SHARE, [(name, AT.SHARE)],
                     remove)
        path = self.getContent(name, 'path')
        if not path:
            raise Exception('Share "%s" does not exist' % name)
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
        ret = {}
        ret['desc'] = self.getContent(name, 'comment')
        if not ret['desc']:
            ret['desc'] = ""
        ret['sharePath'] = self.getContent(name, 'path')
        if self.isValueTrue(self.getContent(name, 'public')) == 1:
            ret['permAll'] = 1
        elif self.isValueTrue(self.getContent(name, 'guest ok')) == 1:
            ret['permAll'] = 1
        else:
            ret['permAll'] = 0

        # If we cannot find it
        if not self.getContent(name, 'vfs objects'):
            ret['antivirus'] = 0
        else:
            ret['antivirus'] = 1

        if not self.getContent(name, 'browseable'):
            ret["browseable"] = 1
        elif self.isValueTrue(self.getContent(name, 'browseable')):
            ret["browseable"] = 1
        else:
            ret["browseable"] = 0

        # Get the directory group owner
        if os.path.exists(str(ret['sharePath'])):
            stat_info = os.stat(ret['sharePath'])
            gid = stat_info.st_gid
            try:
                ret['group'] = grp.getgrgid(gid)[0]
            except:
                logger.error("Can't find the primary group of %s. "
                             "Check your libnss settings." % ret['sharePath'])
                return False

        return ret

    def addShare(self, name, path, comment, browseable, permAll, usergroups,
                 users, mod=False):
        """
        Add a share in smb.conf and create it physically
        """
        if mod:
            action = AA.SAMBA4_MOD_SHARE
            oldPath = self.config[name]['path']
        else:
            action = AA.SAMBA4_ADD_SHARE
        r = AF().log(PLUGIN_NAME, action, [(name, AT.SHARE)], path)

        if name in self.config and not mod:
            raise Exception('This share already exist')
        if not name in self.config and mod:
            raise Exception('This share does not exist')

        # If no path is given, create a default one
        if not path:
            path = os.path.join(self.default_shares_path, name)
        path = os.path.realpath(path)

        # Check that the path is authorized
        # FIXME: handle correctly archives in base plugin
        if not self.isAuthorizedSharePath(path) and "/home/archives" not in path:
            raise Exception("%s is not an authorized share path." % path)

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
            else: pass

        # Directory is owned by root
        os.chown(path, 0, 0)

        if mod:
            # Delete the old share
            del self.config[name]

        # create table and fix permission
        tmpInsert = {'comment': comment}

        if permAll:
            tmpInsert['public'] = 'yes'
            shlaunch("setfacl -b %s" % shellquote(path))
            os.chmod(path, stat.S_IRWXU | stat.S_IRWXG | stat.S_IRWXO)
        else:
            tmpInsert['public'] = 'no'
            os.chmod(path, stat.S_IRWXU | stat.S_IRWXG)
            # flush ACLs
            shlaunch("setfacl -b %s" % path)
            acl1 = posix1e.ACL(file=path)
            # Add and set default mask to rwx
            # This is needed by the ACL system, else the ACLs won't be valid
            e = acl1.append()
            e.permset.add(posix1e.ACL_READ)
            e.permset.add(posix1e.ACL_WRITE)
            e.permset.add(posix1e.ACL_EXECUTE)
            e.tag_type = posix1e.ACL_MASK
            # For each specified group, we add rwx access
            for group in usergroups:
                e = acl1.append()
                e.permset.add(posix1e.ACL_READ)
                e.permset.add(posix1e.ACL_WRITE)
                e.permset.add(posix1e.ACL_EXECUTE)
                e.tag_type = posix1e.ACL_GROUP
                # Search the gid number corresponding to the given group
                ldapobj = ldapUserGroupControl()
                try:
                    gidNumber = ldapobj.getDetailedGroup(group)['gidNumber'][0]
                except ldap.NO_SUCH_OBJECT:
                    gidNumber = grp.getgrnam(group).gr_gid
                e.qualifier = int(gidNumber)
                # FIXME howto use posix1e for this ?
                shlaunch("setfacl -d -m g:%s:rwx %s" % (str(gidNumber), path))
            for user in users:
                e = acl1.append()
                e.permset.add(posix1e.ACL_READ)
                e.permset.add(posix1e.ACL_WRITE)
                e.permset.add(posix1e.ACL_EXECUTE)
                e.tag_type = posix1e.ACL_USER
                # Search the gid number corresponding to the given group
                ldapobj = ldapUserGroupControl()
                try:
                    uidNumber = ldapobj.getDetailedUser(user)['uidNumber'][0]
                except KeyError:
                    uidNumber = pwd.getpwnam(user).pw_uid
                e.qualifier = int(uidNumber)
                # FIXME howto use posix1e for this ?
                shlaunch("setfacl -d -m u:%s:rwx %s" % (str(uidNumber), path))
            # Test if our ACLs are valid
            if acl1.valid():
                acl1.applyto(path)
            else:
                logger.error("Cannot save ACL on folder " + path)

        tmpInsert['writeable'] = 'yes'
        if not browseable:
            tmpInsert['browseable'] = 'No'
        tmpInsert['path'] = path

        self.config[name] = tmpInsert

        info = self.shareInfo(name)
        # FIXME are this signals used?
        if mod and share_modified:
            share_modified.send(sender=self, share_name=name, share_info=info)
        elif not mod and share_created:
            share_created.send(sender=self, share_name=name, share_info=info)
        r.commit()

    def getACLOnShare(self, name):
        """
        Return a list with all the groups that have rwx access to the share.

        @param name: name of the share (last component of the path)
        @type name: str

        @rtype: tuple
        @return: tuple of groups, users that have rwx access to the share.
        """
        path = self.getContent(name, "path")
        ret = ([], [])
        ldapobj = ldapUserGroupControl()
        acl1 = posix1e.ACL(file=path)
        for e in acl1:
            if e.permset.write:
                if e.tag_type == posix1e.ACL_GROUP:
                    res = ldapobj.getDetailedGroupById(str(e.qualifier))
                    if res:
                        ret[0].append(res['cn'][0])
                    else:
                        ret[0].append(grp.getgrgid(e.qualifier).gr_name)
                if e.tag_type == posix1e.ACL_USER:
                    res = ldapobj.getDetailedUserById(str(e.qualifier))
                    if res:
                        ret[1].append(res['uid'][0])
                    else:
                        ret[1].append(pwd.getpwuid(e.qualifier).pw_name)

        return ret

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
