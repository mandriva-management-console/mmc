# -*- coding: utf-8; -*-
# (c) 2009 Open Systems Specilists - Glen Ogilvie
#
# $Id: __init__.py $
#
# This file is a plugin for Mandriva Management Console (MMC).
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
# along with MMC; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

"""
MMC quota plugin.

This plugin allows to add user quota on filesystem
It also provide a ldap attribute for network quotas
"""

import socket
import ldap
import logging
import os
import os.path
import grp
import tempfile

from mmc.plugins.base import ldapUserGroupControl
from mmc.support.config import *
from mmc.support import mmctools
from string import Template


import mmc

INI = "/etc/mmc/plugins/userquota.ini"

VERSION = "0.0.4"
APIVERSION = "1:0:0"
REVISION = int("$Rev: 1 $".split(':')[1].strip(' $'))

def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION


def activate():
    return True

def getActiveComponents():
    return UserQuotaControl().getActiveComponents()

def getDevicemap():
    return UserQuotaControl().getDevicemap()

def getNetworkmap():
    return UserQuotaControl().getNetworkmap()

def setDiskQuota(uid, device, quota):
    return UserQuotaControl().setDiskQuota(uid, device, quota)

def setNetworkQuota(uid, network, quota):
    return UserQuotaControl().setNetworkQuota(uid, network, quota)

def setGroupDiskQuota(group, device, quota, overwrite):
    return UserQuotaControl().setGroupDiskQuota(group, device, quota, overwrite)

def deleteGroupDiskQuota(cn, device):
    return UserQuotaControl().deleteGroupDiskQuotas(cn, device)

def setGroupNetworkQuota(group, network, quota, overwrite):
    return UserQuotaControl().setGroupNetworkQuota(group, network, quota, overwrite)

def deleteGroupNetworkQuota(cn, device):
    return UserQuotaControl().deleteGroupNetworkQuotas(cn, device)


def deleteDiskQuota(uid, device):
    return UserQuotaControl().deleteDiskQuota(uid, device)

def deleteNetworkQuota(uid, network):
    return UserQuotaControl().deleteNetworkQuota(uid, network)

def setUserQuotaDefaults(user, group):
    return UserQuotaControl().setUserQuotaDefaults(user, group)

class UserQuotaConfig(PluginConfig):

    def readConf(self):
        PluginConfig.readConf(self)
        try: self.diskquotaenable = self.getboolean("diskquota", "enable")
        except: pass
        try: self.networkquotaenable = self.getboolean("networkquota", "enable")
        except: pass
        self.devicemap = self.get("diskquota", "devicemap").split(',')
        self.inodesperblock = self.getfloat("diskquota", "inodesperblock")
        self.softquotablocks = self.getfloat("diskquota", "softquotablocks")
        self.softquotainodes = self.getfloat("diskquota", "softquotainodes")
        self.setquotascript = self.get("diskquota", "setquotascript")
        self.delquotascript = self.get("diskquota", "delquotascript")
        self.runquotascript = self.get("diskquota", "runquotascript")
        self.networkmap = self.get("networkquota", "networkmap").split(',')
        
    def setDefault(self):
        PluginConfig.setDefault(self)
        self.diskquotaenable = True
        self.networkquotaenable = False
       

class UserQuotaControl(ldapUserGroupControl):
     def __init__(self, conffile=None, conffilebase=None):
        mmc.plugins.base.ldapUserGroupControl.__init__(self, conffilebase)
        self.configuserquota = UserQuotaConfig("userquota", conffile)
        self.tempfilename = False
        self.tempdelfilename = False
     def getDevicemap(self):
         return self.configuserquota.devicemap
     def getNetworkmap(self):
         return self.configuserquota.networkmap
     def getActiveComponents(self):
         return ({"disk":self.configuserquota.diskquotaenable, "network":self.configuserquota.networkquotaenable})
     
     def deleteNetworkQuota(self, uid, network):
         logger = logging.getLogger()
         try:
             currentquotas = self.getDetailedUser(uid)["networkquota"]
             newquotas = []
             for x in currentquotas:
                if not x.split(',')[0] == network:
                    newquotas.append(x)
                    
             if len(newquotas) == 0:
                 self.changeUserAttributes(uid, 'networkquota', None)
                 self.delQuotaObjectClass(uid)
             
             else: 
                 self.changeUserAttributes(uid, 'networkquota', newquotas)
         
         except KeyError:
             pass
         return True
         
     def deleteDiskQuota(self, uid, device, single=True):
         logger = logging.getLogger()
#         logger.info("Deleting quota for: "+ uid)
         devicepath = device.split(':')[0]
         try:
             currentquotas = self.getDetailedUser(uid)["quota"]
             newquotas = []
             for x in currentquotas:
                if not x.split('=')[0] == devicepath:
                    newquotas.append(x)
                    
             if len(newquotas) == 0:
                 self.changeUserAttributes(uid, 'quota', None)
                 self.delQuotaObjectClass(uid)
             
             else: 
                 self.changeUserAttributes(uid, 'quota', newquotas)
             
             self.appendDeleteQuotatasks(uid, devicepath)
             if single:
                 logger.info("Delete Single quota")
                 self.deleteQuotaOnFS();
            
         except KeyError:
             pass
             
         return True

     
     def setNetworkQuota(self, uid, network, quota, overwrite = "all"):
         logger = logging.getLogger()
         ldapquota = '%s,%s' % (network, str(int(quota) * 1048576))
         logger.info("Network Quota:" + ldapquota)
         
         if not self.hasDiskQuotaObjectClass(uid):
            self.addDiskQuotaObjectClass(uid)
        # @todo, fix copy from disk quotas.
         try:
             userdetails = self.getDetailedUser(uid)
             currentquotas = userdetails["networkquota"]
             newquotas = []
             quotachanged = False
             for x in currentquotas:              
                if x.split(',')[0] == network:
                    logger.info("Current network quota sizes: " + str(self.convertNetworkQuotaToMB(x)))
                    logger.info("Requested quota size: " + quota)
                    logger.info("Overwrite mode: " + overwrite)
                    if (overwrite == "none"):
                        logger.info('No overwrite mode set. so not overwriting.')
                        return False
                    if overwrite == "smaller" and self.convertNetworkQuotaToMB(x) > int(quota):
                        logger.info('Current network quota is bigger than new quota, so not overwriting')
                        return False
                    if overwrite == "larger" and int(quota) > self.convertNetworkQuotaToMB(x):
                        logger.info('Current network quota is smaller than new quota, so not overwriting')
                        return False
                    newquotas.append(ldapquota)
                    quotachanged = True
                else:
                    newquotas.append(x)
            
             if not quotachanged:
                 newquotas.append(ldapquota)
            
             self.changeUserAttributes(uid, 'networkquota', newquotas)
         except KeyError:
             self.changeUserAttributes(uid, 'networkquota', ldapquota)
             pass
         
         return True
         
     def setDiskQuota(self, uid, device, quota, overwrite = "all",single = True):
         logger = logging.getLogger()
         logger.info("received quota for " + uid + ", device: " + device + ", size: " + quota)
         blocks = self.convertMBtoBlocks(quota, device);
         softblocks = int (blocks * self.configuserquota.softquotablocks)
         inodes = int(blocks * self.configuserquota.inodesperblock)
         softinodes = int(inodes * self.configuserquota.softquotainodes)
         devicepath = device.split(':')[0]
#         logger.info("Devicepath: " + devicepath)
#         logger.info("Blocks: " + str(blocks))
#         logger.info("Soft Blocks: " + str(softblocks))
#         logger.info("Inodes: " + str(inodes))
#         logger.info("Soft Inodes: " + str(softinodes))     
         ldapquota = '%s=%s:%s:%s:%s' % (devicepath, str(blocks), str(softblocks), str(inodes), str(softinodes))
         logger.info("Quota for: " + uid + " - " + ldapquota)
         
         if not self.hasDiskQuotaObjectClass(uid):
            self.addDiskQuotaObjectClass(uid)
         try:
             userdetails = self.getDetailedUser(uid)
             currentquotas = userdetails["quota"]
             newquotas = []
             quotachanged = False
             for x in currentquotas:
                if x.split('=')[0] == devicepath:
                    logger.info("Current network quota sizes: " + str(self.convertDiskQuotaToMB(x)))
                    logger.info("Requested quota size: " + quota)
                    logger.info("Overwrite mode: " + overwrite)
                    if overwrite == "none":
                        return False
                    if overwrite == "smaller" and self.convertDiskQuotaToMB(x) > int(quota):
                        logger.info('Current quota is bigger than new quota, so not overwriting')
                        return False
                    if overwrite == "larger" and int(quota) > self.convertDiskQuotaToMB(x):
                        logger.info('Current quota is smaller than new quota, so not overwriting')
                        return False
                          
                    newquotas.append(ldapquota)
                    quotachanged = True
                else:
                    newquotas.append(x)
            
             if not quotachanged:
                 newquotas.append(ldapquota)
             self.changeUserAttributes(uid, 'quota', newquotas)
         except KeyError:
             self.changeUserAttributes(uid, 'quota', ldapquota)
             pass
         
         self.appendQuotatasks(uid, blocks, softblocks, inodes, softinodes, devicepath)
         if single:
             self.applyQuotaToFS()  
         
         return True
     
     def appendQuotatasks(self, uid, blocks, softblocks, inodes, softinodes, devicepath):
         logger = logging.getLogger()
         if not self.tempfilename:
            self.tempfilename = tempfile.mktemp()
            logger.info("Temp file: %s" % (self.tempfilename))
         
         
         s = Template(self.configuserquota.setquotascript)
         shellscript = s.substitute(uid=uid, blocks=blocks, softblocks=softblocks, inodes=inodes, softinodes=softinodes, devicepath=devicepath)
         logger.info("Append SetQuotaScript: " + shellscript);
         f = open(self.tempfilename, 'a')
         f.write("%s\n" % (shellscript))
         f.close
         
     
     def applyQuotaToFS(self):
         if not self.tempfilename:
            return
         logger = logging.getLogger()
         shellscript = "%s %s" % (self.configuserquota.runquotascript, self.tempfilename)
         logger.info("ApplyQuotas: " + shellscript);
         def cb(shprocess):
              # The callback just return the process outputs
              # 
              if shprocess.exitCode != 0:
                  logger.error("Error applying quotas: " + shprocess.err)
                  logger.info("shell result:" + shprocess.out)
                  logger.error("See: " + self.tempfilename + " for details of the commands run") 
              else:
                  os.remove(self.tempfilename)
              
              self.tempfilename = False
              return shprocess.exitCode, shprocess.out, shprocess.err
         
         d = mmctools.shLaunchDeferred(shellscript)
         # shLaunchDeferred returns a Deferred() object
         # We add the cb function as a callback
         d.addCallback(cb)
         # We return the Deferred() object
         return d
     
     def appendDeleteQuotatasks(self, uid, devicepath):
         logger = logging.getLogger()
         if not self.tempdelfilename:
            self.tempdelfilename = tempfile.mktemp()
            logger.info("Temp file: %s" % (self.tempdelfilename))
         
         s = Template(self.configuserquota.delquotascript)
         shellscript = s.substitute(uid=uid, devicepath=devicepath)
         logger.info("Append DelQuotaScript: " + shellscript);
         f = open(self.tempdelfilename, 'a')
         f.write("%s\n" % (shellscript))
         f.close
         
             
     def deleteQuotaOnFS(self): 
         if not self.tempdelfilename:
            return
         logger = logging.getLogger()
         shellscript = "%s %s" % (self.configuserquota.runquotascript, self.tempfilename)
         logger.info("DelQuotaScript: " + shellscript);
         def cb(shprocess):
              # The callback just return the process outputs
              if shprocess.exitCode != 0:
                  logger.error("Error applying del quotas: " + shprocess.err)
                  logger.info("shell result:" + shprocess.out)
                  logger.error("See: " + self.tempfilename + " for details of the commands run") 
              else:
                  os.remove(self.tempdelfilename)
                  
              self.tempdelfilename = False
              return shprocess.exitCode, shprocess.out, shprocess.err
         d = mmctools.shLaunchDeferred(shellscript)
         # shLaunchDeferred returns a Deferred() object
         # We add the cb function as a callback
         d.addCallback(cb)
         # We return the Deferred() object
         return d   
   
     def convertMBtoBlocks(self, quota, device): 
         parts = device.split(':')
         blocks = int(parts[1])
         bytes = int(quota) * 1048576
         return int(bytes / blocks)
        
     def convertNetworkQuotaToMB(self, quota):
         return int(quota.split(',')[1])/1048576
     
     def convertDiskQuotaToMB(self,quota):
         devicemap = self.getDevicemap()
         devicepath = quota.split('=')[0]
         for x in devicemap:
             if x.split(':')[0] == devicepath:
                 return int(quota.split('=')[1].split(":")[0]) * int(x.split(':')[1]) / 1048576
         return False    
         
            
     def hasDiskQuotaObjectClass(self, uid):
        """
        Return true if the user owns the systemQuotas objectClass.

        @param uid: user name
        @type uid: str

        @return: return True if the user owns the mailAccount objectClass.
        @rtype: boolean
        """
        return "systemQuotas" in self.getDetailedUser(uid)["objectClass"]
     
     def delQuotaObjectClass(self, uid):
         """
         Return true if the objectClass is removed.
         
         @return: return True if the object class is able to be removed
         @rtype: boolean
         """
         logger = logging.getLogger()
         user = self.getDetailedUser(uid)
         logger.info("Del object class")
         
         if "quota" in user.keys() or "networkquota" in user.keys():
                return False
            
         logger.info("Del object class removal")
         if "systemQuotas" in user["objectClass"]:
                user["objectClass"].remove("systemQuotas")
                self.changeUserAttributes(uid, 'objectClass', user["objectClass"])
                return True
         return False
     
     def delGroupQuotaObjectClass(self, cn):
         """
         Return true if the objectClass is removed.
         
         @return: return True if the object class is able to be removed
         @rtype: boolean
         """
         logger = logging.getLogger()
         group = self.getDetailedGroup(cn)
         logger.info("Del Group object class")
         logger.info("group keys" + str(group.keys()))
         if "quota" in group.keys() or "networkquota" in group.keys():
                return False
            
         logger.info("Del object class removal")
         if "defaultQuotas" in group["objectClass"]:
                group["objectClass"].remove("defaultQuotas")
                logger.info("ObjectClass to save:" + str( group["objectClass"]) )
                self.changeGroupAttribute(cn, 'objectClass', group["objectClass"])
                return True
         return False
         
     def addDiskQuotaObjectClass(self, uid):
        user = self.getDetailedUser(uid)['objectClass']
        if not "systemQuotas" in user:
            user.append("systemQuotas")
            self.l.modify_ext_s('uid=' + uid + ',' + self.baseUsersDN, [(ldap.MOD_REPLACE, 'objectClass', user)]) 
        return True
    
    
     def setGroupDiskQuota(self, group, device, quota, overwrite):
         logger = logging.getLogger()
         logger.info("SetGroupDiskQuota: Overwrite mode: " + overwrite)
         logger.info("ldap timeout:" + str(self.l.timeout))
         self.l.set_option(ldap.OPT_NETWORK_TIMEOUT, 100)
         logger.info("ldap network timeout:" + str(self.l.get_option(ldap.OPT_NETWORK_TIMEOUT)))
         
         
         self.addGroupDefaultDiskQuotaObjectClass(group)
         
         blocks = self.convertMBtoBlocks(quota, device);
         softblocks = int (blocks * self.configuserquota.softquotablocks)
         inodes = int(blocks * self.configuserquota.inodesperblock)
         softinodes = int(inodes * self.configuserquota.softquotainodes)
         devicepath = device.split(':')[0]
         ldapquota = '%s=%s:%s:%s:%s' % (devicepath, str(blocks), str(softblocks), str(inodes), str(softinodes))
         # @todo improve this, it's a copy of set disk quota.
         try:
             currentquotas = self.getDetailedGroup(group)["quota"]
             newquotas = []
             quotachanged = False
             for x in currentquotas:
                if x.split('=')[0] == devicepath:
                    newquotas.append(ldapquota)
                    quotachanged = True
                else:
                    newquotas.append(x)
            
             if not quotachanged:
                 newquotas.append(ldapquota)
             
             self.changeGroupAttribute(group, "quota", newquotas)
             
         except KeyError:
             self.changeGroupAttribute(group, "quota", ldapquota)
             pass
   
    
         for uid in self.getMembers(group):
            self.setDiskQuota(uid, device, quota, overwrite, single=False)
         
         # apply the group quotas to FS.
         self.applyQuotaToFS()
         
         
         return True
     
     def setGroupNetworkQuota(self, group, network, quota, overwrite):
         logger = logging.getLogger()
         logger.info("SetGroupNetworkQuota Overwrite mode: " + overwrite)
         self.addGroupDefaultDiskQuotaObjectClass(group)
         ldapquota = '%s,%s' % (network, str(int(quota) * 1048576))
         # @todo improve this, it's a copy of set disk quota.
         try:
             currentquotas = self.getDetailedGroup(group)["networkquota"]
             newquotas = []
             quotachanged = False
             for x in currentquotas:
                if x.split(',')[0] == network:
                    newquotas.append(ldapquota)
                    quotachanged = True
                else:
                    newquotas.append(x)
            
             if not quotachanged:
                 newquotas.append(ldapquota)
             
             self.changeGroupAttribute(group, "networkquota", newquotas)
             
         except KeyError:
             self.changeGroupAttribute(group, "networkquota", ldapquota)
             pass
   
         for uid in self.getMembers(group):
            self.setNetworkQuota(uid, network, quota, overwrite)
         return True
     
     def setUserQuotaDefaults(self, uid, group):
         # @todo: unfinished, does nothing yet.
         logger = logging.getLogger()
         logger.info("Set user quota defaults: user: " + uid + " group: " + group)     
         keys = []  
         # don't set the quota if one has been set before.
         logger.info(self.getDetailedUser(uid)["objectClass"])
         logger.info("Value of: self.hasDiskQuotaObjectClass(uid)" + str(self.hasDiskQuotaObjectClass(uid)))
         if self.hasDiskQuotaObjectClass(uid):
            logger.info("User already has quota Object class")
            return keys
         
         groupdefaults = self.getDetailedGroup(group) 
        
         # @todo, check components before action
         if "quota" in groupdefaults.keys():
                 # copy quota values to user
                 logger.info("copy quota values to user:" + uid)   
                 self.addDiskQuotaObjectClass(uid)
                 self.changeUserAttributes(uid, "quota", groupdefaults["quota"])
                 keys.append('quota')
        
         if "networkquota" in groupdefaults.keys():
                 # copy networkquota values to user
                 logger.info("copy network quota values to user:" + uid)   
                 self.addDiskQuotaObjectClass(uid)
                 self.changeUserAttributes(uid, "networkquota", groupdefaults["networkquota"])
                 keys.append('networkquota')
         
         return keys
     
     def addGroupDefaultDiskQuotaObjectClass(self, cn):
        group = self.getDetailedGroup(cn)['objectClass']
        if not "defaultQuotas" in group:
            group.append("defaultQuotas")
            self.changeGroupAttribute(cn, 'objectClass', group)
        return True
    
     def changeGroupAttribute(self, cn, attr, attrval):
         self.l.modify_ext_s('cn=' + cn + ',' + self.baseGroupsDN, [(ldap.MOD_REPLACE, attr, attrval)]) 
         
     def deleteGroupDiskQuotas(self, cn, device):
         logger = logging.getLogger()
         devicepath = device.split(':')[0]
         logger.info("Delete quotas for members of:" + cn)
         logger.info("ldap timeout:" + str(self.l.timeout))
         self.l.set_option(ldap.OPT_NETWORK_TIMEOUT, 100)
         logger.info("ldap network timeout:" + str(self.l.get_option(ldap.OPT_NETWORK_TIMEOUT)))
                     

         for uid in self.getMembers(cn):
            self.deleteDiskQuota(uid, device, single=False)
         
         self.deleteQuotaOnFS();
         
         try:
             currentquotas = self.getDetailedGroup(cn)["quota"]
             newquotas = []
             for x in currentquotas:
                if not x.split('=')[0] == devicepath:
                    newquotas.append(x)
                    
             if len(newquotas) == 0:
                 self.changeGroupAttribute(cn, 'quota', None)
                 self.delGroupQuotaObjectClass(cn)
             else: 
                 self.changeGroupAttribute(cn, 'quota', newquotas)
         
         except KeyError:
             pass
             
         return True
      
     def deleteGroupNetworkQuotas(self, cn, network):
         logger = logging.getLogger()
         logger.info("Delete networkquotas for members of: " + cn)
         for uid in self.getMembers(cn):
            self.deleteNetworkQuota(uid, network)
         try:
             currentquotas = self.getDetailedGroup(cn)["networkquota"]
             newquotas = []
             
             for x in currentquotas:
                if not x.split(',')[0] == network:
                    newquotas.append(x)
                    
             if len(newquotas) == 0:
                 self.changeGroupAttribute(cn, 'networkquota', None)
                 self.delGroupQuotaObjectClass(cn)
             else: 
                 self.changeGroupAttribute(cn, 'networkquota', newquotas)
         
         except KeyError:
             pass
         return True
      
