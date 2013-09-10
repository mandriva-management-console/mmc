import smbpasswd
import time
import ldap
import logging
import xmlrpclib

from mmc.support.mmctools import generateBackgroundProcess, shLaunch, shLaunchDeferred
from mmc.plugins.base import ldapUserGroupControl, delete_diacritics
from mmc.core.audit import AuditFactory as AF

from mmc.plugins.samba.audit import AT, AA, PLUGIN_NAME
from mmc.plugins.samba.config import SambaConfig
from mmc.plugins.samba.smb_conf import SambaConf

logger = logging.getLogger()

class SambaLDAP(ldapUserGroupControl):

    def __init__(self, conffile = None, conffilebase = None):
        ldapUserGroupControl.__init__(self, conffilebase)
        self.configSamba = SambaConfig("samba")
        self.baseComputersDN = self.configSamba.baseComputersDN
        self.hooks.update(self.configSamba.hooks)

    def getDomainAdminsGroup(self):
        """
        Return the LDAP posixGroup entry corresponding to the 'Domain Admins' group.

        @return: a posixGroup entry
        @rtype: dict
        """
        domain = self.getDomain()
        sambaSID = domain["sambaSID"][0]
        result = self.search("(&(objectClass=sambaGroupMapping)(sambaSID=%s-512))" % sambaSID)
        if len(result):
            ret = result[0][0][1]
        else:
            ret = {}
        return ret

    def getDomainUsersGroup(self):
        """
        Return the LDAP posixGroup entry corresponding to the 'Domain Users' group.

        @return: a posixGroup entry
        @rtype: dict
        """
        domain = self.getDomain()
        sambaSID = domain["sambaSID"][0]
        result = self.search("(&(objectClass=sambaGroupMapping)(sambaSID=%s-513))" % sambaSID)
        if len(result): ret = result[0][0][1]
        else: ret = {}
        return ret

    def getDomainGuestsGroup(self):
        """
        Return the LDAP posixGroup entry corresponding to the 'Domain Guests' group.

        @return: a posixGroup entry
        @rtype: dict
        """
        domain = self.getDomain()
        sambaSID = domain["sambaSID"][0]
        result = self.search("(&(objectClass=sambaGroupMapping)(sambaSID=%s-514))" % sambaSID)
        if len(result): ret = result[0][0][1]
        else: ret = {}
        return ret

    def getDomainComputersGroup(self):
        """
        Return the LDAP posixGroup entry corresponding to the 'Domain Computers' group.

        @return: a posixGroup entry
        @rtype: dict
        """
        domain = self.getDomain()
        sambaSID = domain["sambaSID"][0]
        result = self.search("(&(objectClass=sambaGroupMapping)(sambaSID=%s-515))" % sambaSID)
        if len(result): ret = result[0][0][1]
        else: ret = {}
        return ret

    def getDomain(self):
        """
        Return the LDAP sambaDomainName entry corresponding to the domain specified in smb.conf

        @return: the sambaDomainName entry
        @rtype: dict
        """
        conf = SambaConf()
        domain = conf.getContent("global", "workgroup")
        result = self.search("(&(objectClass=sambaDomain)(sambaDomainName=%s))" % domain)
        if len(result): ret = result[0][0][1]
        else: ret = {}
        return ret

    def updateDomainNextRID(self):
        """
        Increment sambaNextRID
        """
        conf = SambaConf()
        domain = conf.getContent("global", "workgroup")
        result = self.search("(&(objectClass=sambaDomain)(sambaDomainName=%s))" % domain)
        dn, old = result[0][0]
        # update the old attributes
        new = old.copy()
        new['sambaNextRid'] = [ str(int(old['sambaNextRid'][0]) + 1) ]
        modlist = ldap.modlist.modifyModlist(old, new)
        self.l.modify_s(dn, modlist)

    def setDomainPolicy(self):
        """
        Try to sync the samba domain policy with the default OpenLDAP policy
        """
        conf = SambaConf()
        domain = conf.getContent("global", "workgroup")
        result = self.search("(&(objectClass=sambaDomain)(sambaDomainName=%s))" % domain)
        dn, old = result[0][0]
        # update the old attributes
        new = old.copy()
        # get the default ppolicy values
        try:
            from mmc.plugins.ppolicy import getDefaultPPolicy
        except ImportError:
            # don't try to change samba policies
            pass
        else:
            try:
                ppolicy = getDefaultPPolicy()[1]
            except ldap.NO_SUCH_OBJECT:
                # no default password policy set
                pass
            else:
                # samba default values
                options = {
                    "sambaMinPwdLength": ["5"],
                    "sambaMaxPwdAge": ["-1"],
                    "sambaMinPwdAge": ["0"],
                    "sambaPwdHistoryLength": ["0"],
                    "sambaLockoutThreshold": ["0"],
                    "sambaLockoutDuration": ["30"]
                }
                if 'pwdMinLength' in ppolicy:
                    options['sambaMinPwdLength'] = ppolicy['pwdMinLength']
                if 'pwdMaxAge' in ppolicy and ppolicy['pwdMaxAge'][0] != "0":
                    options['sambaMaxPwdAge'] = ppolicy['pwdMaxAge']
                if 'pwdMinAge' in ppolicy:
                    options['sambaMinPwdAge'] = ppolicy['pwdMinAge']
                if 'pwdInHistory' in ppolicy:
                    options['sambaPwdHistoryLength'] = ppolicy['pwdInHistory']
                if 'pwdLockout' in ppolicy and ppolicy['pwdLockout'][0] == "TRUE" \
                    and 'pwdMaxFailure' in ppolicy and ppolicy['pwdMaxFailure'][0] != '0':
                        if 'pwdLockoutDuration' in ppolicy:
                            options['sambaLockoutDuration'] = ppolicy['pwdLockoutDuration']
                        options['sambaLockoutThreshold'] = ppolicy['pwdMaxFailure']
                else:
                    options['sambaLockoutThreshold'] = ["0"]

                update = False
                for attr, value in options.iteritems():
                    # Update attributes if needed
                    if new[attr] != value:
                        new[attr] = value
                        update = True

                if update:
                    modlist = ldap.modlist.modifyModlist(old, new)
                    try:
                        self.l.modify_s(dn, modlist)
                    except ldap.UNDEFINED_TYPE:
                        # don't fail if attributes don't exist
                        pass
                    logger.info("SAMBA domain policy synchronized with password policies")

    def addMachine(self, uid, comment, addMachineScript = False):
        """
        Add a PosixAccount for a machine account.
        if addMachineScript is False, we run smbpasswd to create the needed LDAP attributes.

        @param uid: name of new machine (no space)
        @type uid: str

        @param comment: comment of machine (full string accept)
        @type comment: str
        """
        r = AF().log(PLUGIN_NAME, AA.SAMBA_ADD_MACHINE, [(uid, AT.MACHINE)], comment)
        origuid = uid
        uid = uid + '$'
        uidNumber = self.freeUID();

        if not comment:
            comment = "Machine account"

        comment_UTF8 = str(delete_diacritics((comment.encode("UTF-8"))))
        gidNumber = self.getDomainComputersGroup()["gidNumber"][0]
        # creating machine skel
        user_info = {
            'objectclass':('account', 'posixAccount', 'top'),
            'uid':uid,
            'cn':uid,
            'uidNumber':str(uidNumber),
            'gidNumber': str(gidNumber),
            'gecos':str(comment_UTF8),
            'homeDirectory':'/dev/null',
            'loginShell':'/bin/false'
            }

        ident = 'uid=' + uid + ',' + self.baseComputersDN
        attributes=[ (k,v) for k,v in user_info.items() ]
        self.l.add_s(ident,attributes)

        if not addMachineScript:
            cmd = 'smbpasswd -a -m ' + uid
            shProcess = generateBackgroundProcess(cmd)
            ret = shProcess.getExitCode()

            if ret:
                self.delMachine(origuid) # Delete machine account we just created
                raise Exception("Failed to add computer entry\n" + shProcess.stdall)

        r.commit()
        return 0

    def delMachine(self, uid):
        """
        Remove a computer account from LDAP

        @param uid: computer name
        @type  uid: str
        """
        name='uid=' + uid + ',' + self.baseComputersDN
        r = AF().log(PLUGIN_NAME, AA.SAMBA_DEL_MACHINE, [(name, AT.MACHINE)])
        uid = uid + "$"
        self.l.delete_s('uid=' + uid + ',' + self.baseComputersDN)
        r.commit()
        return 0

    def getMachine(self, uid, base = None):
        """
        Return a computer account from LDAP

        @param uid: computer name
        @type uid: string
        """
        if not base: base = self.baseComputersDN
        return self.getEntry("uid=%s$,%s" % (uid, base))

    def changeMachine(self, uid, options, base = None):

        logs = []
        if not base: base = self.baseComputersDN

        if options['disable']:
            # String of 11 characters surrounded by square brackets [ ]
            # representing account flags such as U (user), W (workstation),
            # X (no password expiration), I (domain trust account),
            # H (home dir required), S (server trust account), and D (disabled).
            options['sambaAcctFlags'] = "[DW         ]"
        else:
            options['sambaAcctFlags'] = "[W          ]"
        del options['disable']

        dn = "uid=%s$,%s" % (uid, base)
        s = self.l.search_s(dn, ldap.SCOPE_BASE)
        c, old = s[0]
        # We update the old attributes array with the new SAMBA attributes
        new = old.copy()
        for key in options.keys():
            value = options[key]
            if value == "":
                # Maybe delete this SAMBA LDAP attribute
                try:
                    del new[key]
                    logs.append(AF().log(PLUGIN_NAME, AA.SAMBA_DEL_ATTR,
                        [(dn, AT.MACHINE), (key, AT.ATTRIBUTE)], value))
                except KeyError:
                    pass
            else:
                # Update this SAMBA LDAP attribute
                new[key] = value
                logs.append(AF().log(PLUGIN_NAME, AA.SAMBA_CHANGE_ATTR,
                    [(dn, AT.MACHINE), (key, AT.ATTRIBUTE)], value))
        modlist = ldap.modlist.modifyModlist(old, new)
        self.l.modify_s(dn, modlist)
        for log in logs:
            log.commit()

        return 0

    def searchMachine(self, pattern = '', base = None):
        """
        @return: a list of SAMBA computer accounts
        @rtype: list
        """
        if (pattern==''): searchFilter = "uid=*"
        else: searchFilter = "uid=" + pattern
        # Always add $ to the search pattern, because a SAMBA computer account
        # ends with a $.
        searchFilter = searchFilter + "$"
        if not base: base = self.baseComputersDN
        result_set = self.search(searchFilter, base, ["uid", "displayName", "sambaAcctFlags"], ldap.SCOPE_ONELEVEL)
        resArr = []
        for i in range(len(result_set)):
            for entry in result_set[i]:
                localArr= []
                uid = entry[1]['uid'][0]
                try:
                    displayName = entry[1]['displayName'][0]
                except KeyError:
                    displayName = ""
                active = True
                if 'sambaAcctFlags' in entry[1] and "D" in entry[1]['sambaAcctFlags'][0]:
                    active = False
                localArr.append(uid[0:-1])
                localArr.append(displayName)
                localArr.append(active)
                resArr.append(localArr)
        resArr.sort()
        return resArr

    def addSmbAttr(self, uid, password):
        """
        Add SAMBA password and attributes on a new user
        """
        # Get domain info
        domainInfo = self.getDomain()
        # Get current user entry
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.SAMBA_ADD_SAMBA_CLASS, [(userdn,AT.USER)])
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = self._applyUserDefault(old.copy(), self.configSamba.userDefault)
        if not "sambaSamAccount" in new['objectClass']:
            new['objectClass'].append("sambaSamAccount")
        new["sambaAcctFlags"] = ["[U          ]"]
        new["sambaSID"] = [domainInfo['sambaSID'][0] + '-' + str(int(domainInfo['sambaNextRid'][0]) + 1)]
        # If the passwd has been encoded in the XML-RPC stream, decode it
        if isinstance(password, xmlrpclib.Binary):
            password = str(password)
        new['sambaLMPassword'] = [smbpasswd.lmhash(password)]
        new['sambaNTPassword'] = [smbpasswd.nthash(password)]
        new['sambaPwdLastSet'] = [str(int(time()))]
        # Update LDAP
        modlist = ldap.modlist.modifyModlist(old, new)
        self.l.modify_s(userdn, modlist)
        self.updateDomainNextRID()
        self.runHook("samba.addsmbattr", uid, password)
        r.commit()

    def changeUserPasswd(self, uid, passwd, oldpasswd = None, bind = False):
        """
        change SAMBA user password

        @param uid: user name
        @type  uid: str

        @param passwd: non encrypted password
        @type  passwd: str
        """

        # Don't update the password if we are using smbk5passwd
        conf = SambaConf()
        if conf.isValueTrue(conf.getContent("global", "ldap passwd sync")) in (0, 1):
            userdn = self.searchUserDN(uid)
            r = AF().log(PLUGIN_NAME, AA.SAMBA_CHANGE_USER_PASS, [(userdn,AT.USER)])
            # If the passwd has been encoded in the XML-RPC stream, decode it
            if isinstance(passwd, xmlrpclib.Binary):
                passwd = str(passwd)
            s = self.l.search_s(userdn, ldap.SCOPE_BASE)
            c, old = s[0]
            new = old.copy()
            new['sambaLMPassword'] = [smbpasswd.lmhash(passwd)]
            new['sambaNTPassword'] = [smbpasswd.nthash(passwd)]
            new['sambaPwdLastSet'] = [str(int(time()))]
            # Update LDAP
            modlist = ldap.modlist.modifyModlist(old, new)
            self.l.modify_s(userdn, modlist)
            self.runHook("samba.changeuserpasswd", uid, passwd)
            r.commit()

        return 0

    def isSmbUser(self, uid):
        """
        @return: True if the user is a SAMBA user, else False
        @rtype: bool
        """
        ret = False
        if self.existUser(uid): ret = "sambaSamAccount" in self.getDetailedUser(uid)["objectClass"]
        return ret

    def changeSambaAttributes(self, uid, attributes):
        """
        Change the SAMBA attributes for an user.
        If an attribute is an empty string, it is deleted.

        @param uid: login of the user
        @type uid: str
        @param attributes: dictionnary of the SAMBA attributes
        @type attributes: dict
        """

        logs = []
        userdn = self.searchUserDN(uid)
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]

        # We update the old attributes array with the new SAMBA attributes
        new = old.copy()
        for key in attributes.keys():
            if key.startswith("samba"):
                value = attributes[key]
                if "old_" + key in attributes:
                    old_value = attributes["old_" + key]
                else:
                    old_value = None
                if value == "" and value != old_value:
                    # Maybe delete this SAMBA LDAP attribute
                    try:
                        del new[key]
                        logs.append(AF().log(PLUGIN_NAME, AA.SAMBA_DEL_ATTR,
                            [(userdn, AT.USER), (key, AT.ATTRIBUTE)], value))
                    except KeyError:
                        pass
                elif value != old_value:
                    if value.startswith("\\\\\\\\"):
                        value = value.replace("\\\\", "\\")
                    # Update this SAMBA LDAP attribute
                    new[key] = value
                    logs.append(AF().log(PLUGIN_NAME, AA.SAMBA_CHANGE_ATTR,
                        [(userdn, AT.USER), (key, AT.ATTRIBUTE)], value))

        if new != old:
            modlist = ldap.modlist.modifyModlist(old, new)
            if modlist: self.l.modify_s(userdn, modlist)
            self.runHook("samba.changesambaattributes", uid)
            for log in logs:
                log.commit()
        return 0

    def changeUserPrimaryGroup(self, uid, group):
        """
        Change the SAMBA primary group of a user, if the sambaPrimaryGroupSID
        of this user is defined. Else do nothing.

        @param uid: login of the user
        @type uid: unicode

        @param group: new primary group
        @type uid: unicode
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.SAMBA_CHANGE_USER_PRIMARY_GRP, [(userdn,AT.USER),(group, AT.GROUP)])
        try:
            self.getDetailedUser(uid)["sambaPrimaryGroupSID"]
        except KeyError:
            # This user has no sambaPrimaryGroupSID set
            # So nothing to do
            return
        gidNumber = self.getDetailedGroup(group)["gidNumber"][0]
        sid = self.gid2sid(gidNumber)
        if sid:
            self.changeUserAttributes(uid, "sambaPrimaryGroupSID", sid)
        r.commit()

    def gid2sid(self, gidNumber):
        """
        Return the SID corresponding to a gid number.

        @param gidNumber: gid number of a group
        @type gidNumber: int

        @return: SID number, or None if no corresponding SID found
        @rtype: str
        """
        group = self.getDetailedGroupById(gidNumber)
        try:
            sid = group["sambaSID"][0]
        except KeyError:
            sid = None
        return sid

    def delSmbAttr(self, uid):
        """
        Remove SAMBA attributes

        @param uid: username
        @type uid: str
        @return: boolean
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.SAMBA_DEL_SAMBA_CLASS, [(userdn,AT.USER)])
        r.commit()
        return self.removeUserObjectClass(uid, "sambaSamAccount")

    def isEnabledUser(self, uid):
        """
        Return True if the SAMBA user is enabled
        """
        userdn = self.searchUserDN(uid)
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = old.copy()
        flags = new["sambaAcctFlags"][0]
        flags = flags.strip("[]")
        flags = flags.strip()
        return not flags.startswith("D")

    def isLockedUser(self, uid):
        """
        Return True if the SAMBA user is locked
        """
        userdn = self.searchUserDN(uid)
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = old.copy()
        flags = new["sambaAcctFlags"][0]
        flags = flags.strip("[]")
        flags = flags.strip()
        return "L" in flags

    def enableUser(self, uid):
        """
        Enable the SAMBA user
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.SAMBA_ENABLE_USER, [(userdn, AT.USER)])
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = old.copy()
        flags = new["sambaAcctFlags"][0]
        flags = flags.strip("[]")
        flags = flags.strip()
        if not flags.startswith("D"):
            # Huh ? User has been already enabled
            # Do nothing
            pass
        else:
            flags = flags[1:]
            flags = "[" + flags.ljust(11) + "]"
            new["sambaAcctFlags"] = [flags]
            modlist = ldap.modlist.modifyModlist(old, new)
            self.l.modify_s(userdn, modlist)
        r.commit()
        return 0

    def disableUser(self, uid):
        """
        Disable the SAMBA user
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.SAMBA_DISABLE_USER, [(userdn, AT.USER)])
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = old.copy()
        flags = new["sambaAcctFlags"][0]
        # flags should be something like "[U          ]"
        flags = flags.strip("[]")
        flags = flags.strip()
        if flags.startswith("D"):
            # Huh ? User has been already disabled
            # Do nothing
            pass
        else:
            flags = "D" + flags
            flags = "[" + flags.ljust(11) + "]"
            new["sambaAcctFlags"] = [flags]
            modlist = ldap.modlist.modifyModlist(old, new)
            self.l.modify_s(userdn, modlist)
        r.commit()
        return 0

    def unlockUser(self, uid):
        """
        Unlock the SAMBA user
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.SAMBA_UNLOCK_USER, [(userdn, AT.USER)])
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = old.copy()
        flags = new["sambaAcctFlags"][0]
        # flags should be something like "[U          ]"
        if "L" in flags:
            flags = flags.strip("[]")
            flags = flags.strip()
            flags = flags.replace("L", "")
            flags = "[" + flags.ljust(11) + "]"
            new["sambaAcctFlags"] = [flags]
            modlist = ldap.modlist.modifyModlist(old, new)
            self.l.modify_s(userdn, modlist)
        r.commit()
        return 0

    def lockUser(self, uid):
        """
        Lock the SAMBA user
        """
        userdn = self.searchUserDN(uid)
        r = AF().log(PLUGIN_NAME, AA.SAMBA_LOCK_USER, [(userdn, AT.USER)])
        s = self.l.search_s(userdn, ldap.SCOPE_BASE)
        c, old = s[0]
        new = old.copy()
        flags = new["sambaAcctFlags"][0]
        # flags should be something like "[U          ]"
        if not "L" in flags:
            flags = flags.strip("[]")
            flags = flags.strip()
            flags = flags + "L"
            flags = "[" + flags.ljust(11) + "]"
            new["sambaAcctFlags"] = [flags]
            modlist = ldap.modlist.modifyModlist(old, new)
            self.l.modify_s(userdn, modlist)
        r.commit()
        return 0

    def userPasswdHasExpired(self, uid):
        """
        Return true if the SAMBA password has expired for the given user
        """
        ret = False
        try:
            domain = self.getDomain()
            if "sambaMaxPwdAge" in domain and int(domain["sambaMaxPwdAge"][0]) > 0:
                sambaPwdMustChange = int(self.getDetailedUser(uid)["sambaPwdLastSet"][0]) + int(domain["sambaMaxPwdAge"][0])
                ret = int(sambaPwdMustChange) < time()
        except KeyError:
            pass
        return ret

    def _getMakeSambaGroupCommand(self, group):
        return "net groupmap add unixgroup='%s'" % group

    def makeSambaGroupBlocking(self, group):
        """
        Transform a POSIX group as a SAMBA group.
        It adds in the LDAP the necessary attributes to the group.
        This code blocks the twisted reactor until the command terminates.

        @param group: the group name
        @type group: str

        @return: the SAMBA net process exit code
        """
        return shLaunch(self._getMakeSambaGroupCommand(group)).exitCode

    def makeSambaGroup(self, group):
        """
        Transform a POSIX group as a SAMBA group.
        It adds in the LDAP the necessary attributes to the group.

        @param group: the group name
        @type group: str

        @return: a deferred object resulting to the SAMBA net process exit code
        """
        r = AF().log(PLUGIN_NAME, AA.SAMBA_MAKE_SAMBA_GRP, [(group, AT.GROUP)])
        d = shLaunchDeferred(self._getMakeSambaGroupCommand(group))
        d.addCallback(lambda p: p.exitCode)
        r.commit()
        return d

    def isSambaGroup(self, group):
        ret = False
        if self.existGroup(group): ret = "sambaGroupMapping" in self.getDetailedGroup(group)["objectClass"]
        return ret
