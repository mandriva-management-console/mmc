# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007 Mandriva, http://www.mandriva.com/
#
# $Id$
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
# along with MMC; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

"""
Class to manage imaging mmc-agent api
imaging plugin
"""

import logging
from twisted.internet import defer

from mmc.support.mmctools import xmlrpcCleanup
from mmc.support.mmctools import RpcProxyI, ContextMakerI, SecurityContext
from mmc.plugins.imaging.config import ImagingConfig
from mmc.plugins.imaging.profile import ImagingProfile
from mmc.plugins.base.computers import ComputerManager
from pulse2.managers.profile import ComputerProfileManager
from pulse2.managers.location import ComputerLocationManager
from pulse2.database.imaging import ImagingDatabase
from pulse2.database.imaging.types import P2IT, P2ISS, P2IM
from pulse2.apis.clients.imaging import ImagingApi
import pulse2.utils

VERSION = "0.1"
APIVERSION = "0:0:0"
REVISION = int("$Rev$".split(':')[1].strip(' $'))

NOAUTHNEEDED = ['computerRegister',
                'imagingServerRegister',
                'getComputerByMac',
                'imageRegister',
                'logClientAction',
                'injectInventory',
                'getDefaultMenuForSuscription',
                'linkImagingServerToLocation',
                'computerChangeDefaultMenuItem']


def getVersion():
    return VERSION


def getApiVersion():
    return APIVERSION


def getRevision():
    return REVISION


def activate():
    """
    Read the plugin configuration, initialize it, and run some tests to ensure
    it is ready to operate.
    """
    logger = logging.getLogger()
    config = ImagingConfig("imaging")

    if config.disabled:
        logger.warning("Plugin imaging: disabled by configuration.")
        return False

    # Initialize imaging database
    if not ImagingDatabase().activate(config):
        logger.warning("Plugin imaging: an error occurred during the database initialization")
        return False

    # register ImagingProfile in ComputerProfileManager but only as a client
    ComputerProfileManager().register("imaging", ImagingProfile)

    return True


class ContextMaker(ContextMakerI):

    def getContext(self):
        s = SecurityContext()
        s.userid = self.userid
        return s


class RpcProxy(RpcProxyI):
    """ XML/RPC Bindings """

    ################################################### web def
    """ Functions to access the web default values as defined in the configuration """
    def get_web_def_date_fmt(self):
        """ get the date format """
        return xmlrpcCleanup(ImagingConfig().web_def_date_fmt)

    def get_web_def_possible_protocols(self):
        """ get the possible protocols """
        return xmlrpcCleanup(map(lambda p: p.toH(), ImagingDatabase().getAllProtocols()))

    def get_web_def_default_protocol(self):
        """ get the default protocol """
        return xmlrpcCleanup(ImagingConfig().web_def_default_protocol)

    def get_web_def_kernel_parameters(self):
        """ get the default kernel parameters """
        return xmlrpcCleanup(ImagingConfig().web_def_kernel_parameters)

    def get_web_def_image_parameters(self):
        """ get the default image backup and restoration parameters """
        return xmlrpcCleanup(ImagingConfig().web_def_image_parameters)

    ###########################################################
    ###### BOOT MENU (image+boot service on the target)
    def __convertType(self, target_type):
        """ convert type from '' or 'group' to P2IT.COMPUTER and P2IT.PROFILE """
        if target_type == '':
            target_type = P2IT.COMPUTER
        elif target_type == 'group':
            target_type = P2IT.PROFILE
        return target_type

    def __getTargetBootMenu(self, target_id, start = 0, end = -1, filter = ''):
        db = ImagingDatabase()
        menu = map(lambda l: l.toH(), db.getBootMenu(target_id, start, end, filter))
        count = db.countBootMenu(target_id, filter)
        return [count, xmlrpcCleanup(menu)]

    def getProfileBootMenu(self, target_id, start = 0, end = -1, filter = ''):
        """
        get a profile boot menu

        @param target_id: the uuid of the profile (field Target.uuid)
        @type target_id: str

        @param start: the beginning of the list, default 0
        @type start: int

        @param end: the end of the list, if == -1, no end limit, default -1
        @type end: int

        @param filter: a string to filter the list, default ''
        @type filter: str

        @returns: return a list of two elements :
            1) the size of the list
            2) the list delimited by start and end
        @rtype: list
        """
        return self.__getTargetBootMenu(target_id, start, end, filter)

    def getComputerBootMenu(self, target_id, start = 0, end = -1, filter = ''):
        """
        get a computer boot menu

        @param target_id: the uuid of the profile (field Target.uuid)
        @type target_id: str

        @param start: the beginning of the list, default 0
        @type start: int

        @param end: the end of the list, if == -1, no end limit, default -1
        @type end: int

        @param filter: a string to filter the list, default ''
        @type filter: str

        @returns: return a list of two elements :
            1) the size of the list
            2) the list delimited by start and end
        @rtype: list
        """
        return self.__getTargetBootMenu(target_id, start, end, filter)

    def getLocationBootMenu(self, loc_id, start = 0, end = -1, filter = ''):
        """
        get a location boot menu

        @param loc_id: the uuid of the location (field Entity.uuid)
        @type loc_id: str

        @param start: the beginning of the list, default 0
        @type start: int

        @param end: the end of the list, if == -1, no end limit, default -1
        @type end: int

        @param filter: a string to filter the list, default ''
        @type filter: str

        @returns: return a list of two elements :
            1) the size of the list
            2) the list delimited by start and end
        @rtype: list
        """
        # Entities are names Location in the php part, here we convert them from Location to Entity
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getEntityBootMenu(loc_id, start, end, filter))
        count = db.countEntityBootMenu(loc_id, filter)
        return [count, xmlrpcCleanup(ret)]

    # EDITION
    def moveItemUpInMenu(self, target_uuid, target_type, mi_uuid):
        """
        move a menu item up in the target's boot menu

        @param target_uuid: the uuid of the target (field Target.uuid)
        @type target_uuid: str

        @param target_type: the target type can be one of those two :
            1) '' or P2IT.COMPUTER (1) for a computer
            2) 'group" or P2IT.PrOFILE (2) for a profile
        @type target_type: str or int

        @param mi_uuid: the menu item to move UUID
        @type mi_uuid: str

        @returns: True if succeed to move the menu item, else return False
        @rtype: boolean
        """
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
        return db.moveItemUpInMenu(target_uuid, mi_uuid)

    def moveItemDownInMenu(self, target_uuid, target_type, mi_uuid):
        """
        move a menu item down in the target's boot menu

        @param target_uuid: the uuid of the target (field Target.uuid)
        @type target_uuid: str

        @param target_type: the target type can be one of those two :
            1) '' or P2IT.COMPUTER (1) for a computer
            2) 'group" or P2IT.PrOFILE (2) for a profile
        @type target_type: str or int

        @param mi_uuid: the menu item to move UUID
        @type mi_uuid: str

        @returns: True if succeed to move the menu item, else return False
        @rtype: boolean
        """
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
        return db.moveItemDownInMenu(target_uuid, mi_uuid)

    def moveItemUpInMenu4Location(self, loc_id, mi_uuid):
        """
        move a menu item up in the location boot menu

        @param target_uuid: the uuid of the location (field Entity.uuid)
        @type target_uuid: str

        @param mi_uuid: the menu item to move UUID
        @type mi_uuid: str

        @returns: True if succeed to move the menu item, else return False
        @rtype: boolean
        """
        db = ImagingDatabase()
        db.setLocationSynchroState(loc_id, P2ISS.TODO)
        return db.moveItemUpInMenu4Location(loc_id, mi_uuid)

    def moveItemDownInMenu4Location(self, loc_id, mi_uuid):
        """
        move a menu item down in the location boot menu

        @param target_uuid: the uuid of the location (field Entity.uuid)
        @type target_uuid: str

        @param mi_uuid: the menu item to move UUID
        @type mi_uuid: str

        @returns: True if succeed to move the menu item, else return False
        @rtype: boolean
        """
        db = ImagingDatabase()
        db.setLocationSynchroState(loc_id, P2ISS.TODO)
        return db.moveItemDownInMenu4Location(loc_id, mi_uuid)

    ###### IMAGES
    def __getTargetImages(self, id, target_type, start = 0, end = -1, filter = ''):
        # be careful the end is used for each list (image and master)
        db = ImagingDatabase()
        reti = map(lambda l: l.toH(), db.getPossibleImages(id, start, end, filter))
        counti = db.countPossibleImages(id, filter)

        retm = map(lambda l: l.toH(), db.getPossibleMasters(id, start, end, filter))
        countm = db.countPossibleMasters(id, filter)

        return {
            'images': [counti, xmlrpcCleanup(reti)],
            'masters': [countm, xmlrpcCleanup(retm)]}

    def getComputerImages(self, id, start = 0, end = -1, filter = ''):
        return self.__getTargetImages(id, P2IT.COMPUTER, start, end, filter)

    def getProfileImages(self, id, start = 0, end = -1, filter = ''):
        return self.__getTargetImages(id, P2IT.PROFILE, start, end, filter)

    def getLocationImages(self, loc_id, start = 0, end = -1, filter = ''):
        # Entities are names Location in the php part, here we convert them from Location to Entity
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getEntityMasters(loc_id, start, end, filter))
        count = db.countEntityMasters(loc_id, filter)
        return [count, xmlrpcCleanup(ret)]

    # EDITION
    def addImageToTarget(self, item_uuid, target_uuid, params, target_type):
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        try:
            db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
            ret = db.addImageToTarget(item_uuid, target_uuid, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            raise e
            return xmlrpcCleanup([False, e])

    def editImageToTarget(self, item_uuid, target_uuid, params, target_type):
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        try:
            db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
            ret = db.editImageToTarget(item_uuid, target_uuid, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def editImage(self, item_uuid, target_uuid, params, target_type):
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        try:
            db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
            ret = db.editImage(item_uuid, target_uuid, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            raise e
            return xmlrpcCleanup([False, e])

    def delImageToTarget(self, item_uuid, target_uuid, target_type):
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        try:
            db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
            ret = db.delImageToTarget(item_uuid, target_uuid)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def addImageToLocation(self, item_uuid, loc_id, params):
        db = ImagingDatabase()
        try:
            db.setLocationSynchroState(loc_id, P2ISS.TODO)
            ret = db.addImageToEntity(item_uuid, loc_id, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            raise e
            return xmlrpcCleanup([False, e])

    def editImageToLocation(self, item_uuid, loc_id, params):
        db = ImagingDatabase()
        try:
            db.setLocationSynchroState(loc_id, P2ISS.TODO)
            ret = db.editImageToEntity(item_uuid, loc_id, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def delImageToLocation(self, item_uuid, loc_id):
        db = ImagingDatabase()
        try:
            db.setLocationSynchroState(loc_id, P2ISS.TODO)
            ret = db.delImageToEntity(item_uuid, loc_id)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    ###### BOOT SERVICES
    def __getTargetBootServices(self, id, target_type, start = 0, end = -1, filter = ''):
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getBootServicesOnTargetById(id, start, end, filter))
        count = db.countBootServicesOnTargetById(id, filter)
        return [count, xmlrpcCleanup(ret)]

    def getComputerBootServices(self, id, start = 0, end = -1, filter = ''):
        return self.__getTargetBootServices(id, P2IT.COMPUTER, start, end, filter)

    def getProfileBootServices(self, id, start = 0, end = -1, filter = ''):
        return self.__getTargetBootServices(id, P2IT.PROFILE, start, end, filter)

    def getPossibleBootServices(self, target_uuid, start = 0, end = -1, filter = ''):
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getPossibleBootServices(target_uuid, start, end, filter))
        count = db.countPossibleBootServices(target_uuid, filter)
        return [count, xmlrpcCleanup(ret)]

    def getLocationBootServices(self, loc_id, start = 0, end = -1, filter = ''):
        # Entities are names Location in the php part, here we convert them from Location to Entity
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getEntityBootServices(loc_id, start, end, filter))
        count = db.countEntityBootServices(loc_id, filter)
        return [count, xmlrpcCleanup(ret)]

    # EDITION
    def addServiceToTarget(self, bs_uuid, target_uuid, params, target_type):
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        try:
            db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
            ret = ImagingDatabase().addServiceToTarget(bs_uuid, target_uuid, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def delServiceToTarget(self, bs_uuid, target_uuid, target_type):
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        try:
            db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
            ret = ImagingDatabase().delServiceToTarget(bs_uuid, target_uuid)
            return xmlrpcCleanup(ret)
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def editServiceToTarget(self, bs_uuid, target_uuid, params, target_type):
        db = ImagingDatabase()
        target_type = self.__convertType(target_type)
        try:
            db.changeTargetsSynchroState([target_uuid], target_type, P2ISS.TODO)
            ret = ImagingDatabase().editServiceToTarget(bs_uuid, target_uuid, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            raise e
            return xmlrpcCleanup([False, e])

    def addServiceToLocation(self, bs_uuid, location_id, params):
        db = ImagingDatabase()
        try:
            db.setLocationSynchroState(location_id, P2ISS.TODO)
            ret = ImagingDatabase().addServiceToEntity(bs_uuid, location_id, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def delServiceToLocation(self, bs_uuid, location_id):
        db = ImagingDatabase()
        try:
            db.setLocationSynchroState(location_id, P2ISS.TODO)
            ret = ImagingDatabase().delServiceToEntity(bs_uuid, location_id)
            return xmlrpcCleanup(ret)
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def editServiceToLocation(self, mi_uuid, location_id, params):
        db = ImagingDatabase()
        try:
            db.setLocationSynchroState(location_id, P2ISS.TODO)
            ret = ImagingDatabase().editServiceToEntity(mi_uuid, location_id, params)
            return xmlrpcCleanup([True, ret])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    ###### MENU ITEMS
    def getMenuItemByUUID(self, bs_uuid):
        mi = ImagingDatabase().getMenuItemByUUID(bs_uuid)
        if mi != None:
            return xmlrpcCleanup(mi.toH())
        return False

    ###### LOGS
    def __getTargetImagingLogs(self, id, target_type, start = 0, end = -1, filter = ''):
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getImagingLogsOnTargetByIdAndType(id, target_type, start, end, filter))
        count = db.countImagingLogsOnTargetByIdAndType(id, target_type, filter)
        return [count, xmlrpcCleanup(ret)]

    def getComputerLogs(self, id, start = 0, end = -1, filter = ''):
        return self.__getTargetImagingLogs(id, P2IT.COMPUTER, start, end, filter)

    def getProfileLogs(self, id, start = 0, end = -1, filter = ''):
        return self.__getTargetImagingLogs(id, P2IT.PROFILE, start, end, filter)

    def getLogs4Location(self, location_uuid, start = 0, end = -1, filter = ''):
        if location_uuid == False:
            return [0, []]
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getImagingLogs4Location(location_uuid, start, end, filter))
        count = db.countImagingLogs4Location(location_uuid, filter)
        return [count, xmlrpcCleanup(ret)]

    ###### GET IMAGING API URL
    def __chooseImagingApiUrl(self, location):
        return ImagingDatabase().getEntityUrl(location)

    ###### IMAGING API CALLS
    def getGlobalStatus(self, location):
        url = self.__chooseImagingApiUrl(location)
        i = ImagingApi(url.encode('utf8')) # TODO why do we need to encode....
        # TODO need to be done in async
        if i != None:
            return xmlrpcCleanup(i.imagingServerStatus())
        return {}

    ####### IMAGING SERVER
    def getAllNonLinkedImagingServer(self, start = 0, end = -1, filter = ''):
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getAllNonLinkedImagingServer(start, end, filter))
        count = db.countAllNonLinkedImagingServer(filter)
        return [count, xmlrpcCleanup(ret)]

    def linkImagingServerToLocation(self, is_uuid, loc_id, loc_name):
        db = ImagingDatabase()
        try:
            db.linkImagingServerToEntity(is_uuid, loc_id, loc_name) # FIXME : are not we supposed to deal with the return value ?
            db.setLocationSynchroState(loc_id, P2ISS.TODO)
        except Exception, e:
            logging.getLogger().warn("Imaging.linkImagingServerToLocation : %s" % e)
            return [False, "Failed to link Imaging Server to Location : %s" % e]
        return [True]

    def getImagingServerConfig(self, location):
        imaging_server = ImagingDatabase().getImagingServerByEntityUUID(location)
        default_menu = ImagingDatabase().getEntityDefaultMenu(location)
        if imaging_server and default_menu:
            return xmlrpcCleanup((imaging_server.toH(), default_menu.toH()))
        elif default_menu:
            return [False, ":cant find imaging server linked to location %s"%(location)]
        elif imaging_server:
            return [False, ":cant find the default menu for location %s"%(location), xmlrpcCleanup(imaging_server.toH())]

    def setImagingServerConfig(self, location, config):
        menu = ImagingDatabase().getEntityDefaultMenu(location)
        menu = menu.toH()
        db = ImagingDatabase()
        try:
            db.setLocationSynchroState(location, P2ISS.TODO)
            return xmlrpcCleanup([db.modifyMenu(menu['imaging_uuid'], config)])
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def doesLocationHasImagingServer(self, loc_id):
        return ImagingDatabase().doesLocationHasImagingServer(loc_id)

    ###### REGISTRATION
    def isTargetRegister(self, uuid, target_type):
        return ImagingDatabase().isTargetRegister(uuid, target_type)

    def isComputerRegistered(self, machine_uuid):
        return self.isTargetRegister(machine_uuid, P2IT.COMPUTER)

    def isProfileRegistered(self, profile_uuid):
        return self.isTargetRegister(profile_uuid, P2IT.PROFILE)

    ###### Synchronisation
    def getTargetSynchroState(self, uuid, target_type):
        ret = ImagingDatabase().getTargetsSynchroState([uuid], target_type)
        return ret[0]

    def getComputerSynchroState(self, uuid):
        if not self.isTargetRegister(uuid, P2IT.COMPUTER):
            return {'id':0}
        ret = self.getTargetSynchroState(uuid, P2IT.COMPUTER)
        return xmlrpcCleanup(ret.toH())

    def getProfileSynchroState(self, uuid):
        if not self.isTargetRegister(uuid, P2IT.PROFILE):
            return {'id':0}
        ret = self.getTargetSynchroState(uuid, P2IT.PROFILE)
        return xmlrpcCleanup(ret.toH())

    def getLocationSynchroState(self, uuid):
        if not self.doesLocationHasImagingServer(uuid):
            return {'id':0}
        ret = ImagingDatabase().getLocationSynchroState(uuid)
        if type(ret) != dict:
            ret = ret.toH()
        return xmlrpcCleanup(ret)

    def __generateMenusContent(self, menu, menu_items, loc_uuid, target_uuid = None, h_pis = {}):
        menu['bootservices'] = {}
        menu['images'] = {}
        for mi in menu_items:
            if menu['fk_default_item'] == mi.id:
                menu['default_item'] = mi.order
            if menu['fk_default_item_WOL'] == mi.id:
                menu['default_item_WOL'] = mi.order
                menu['default_item_wol'] = mi.order # TODO : remove
            mi = mi.toH()
            if mi.has_key('image'):
                if h_pis.has_key(mi['image']['id']):
                    h_pis[mi['image']['id']].append([loc_uuid, target_uuid, str(mi['order'])])
                else:
                    h_pis[mi['image']['id']] = [[loc_uuid, target_uuid, str(mi['order'])]]
                im = {
                    'uuid' : mi['image']['uuid'],
                    'name' : mi['image']['name'],
                    'desc' : mi['image']['desc']
                }
                menu['images'][str(mi['order'])] = im
            else:
                bs = {
                    'name' : mi['boot_service']['default_name'],
                    'desc' : mi['boot_service']['default_desc'],
                    'value' : mi['boot_service']['value'],
                    'hidden' : mi['hidden'],
                    'hidden_WOL' : mi['hidden_WOL']
                }
                menu['bootservices'][str(mi['order'])] = bs
        return (menu, menu_items, h_pis)

    def __generateDefaultSuscribeMenu(self, logger, db):
        menu = db.getDefaultSuscribeMenu()
        menu_items = db.getMenuContent(menu.id, P2IM.ALL, 0, -1, '')
        menu = menu.toH()
        menu, menu_items, h_pis = self.__generateMenusContent(menu, menu_items, None)
        ims = h_pis.keys()
        a_pis = db.getImagesPostInstallScript(ims)
        for pis, im in a_pis:
            pis = {
                'id':pis.id,
                'name':pis.default_name,
                'desc':pis.default_desc,
                'value':pis.value
            }
            a_targets = h_pis[im.id]
            for loc_uuid, t_uuid, order in a_targets:
                # loc_uuid = None
                # t_uuid = None
                menu['images'][order]['post_install_script'] = pis
        return menu

    def __generateLocationMenu(self, logger, db, loc_uuid):
        menu = db.getEntityDefaultMenu(loc_uuid)
        menu_items = db.getMenuContent(menu.id, P2IM.ALL, 0, -1, '')
        menu = menu.toH()
        menu, menu_items, h_pis = self.__generateMenusContent(menu, menu_items, loc_uuid)
        ims = h_pis.keys()
        a_pis = db.getImagesPostInstallScript(ims)
        for pis, im in a_pis:
            pis = {
                'id':pis.id,
                'name':pis.default_name,
                'desc':pis.default_desc,
                'value':pis.value
            }
            a_targets = h_pis[im.id]
            for loc_uuid, t_uuid, order in a_targets:
                menu['images'][order]['post_install_script'] = pis
        return menu

    def __generateMenus(self, logger, db, uuids, target_type):
        # WIP
        # get target location
        distinct_loc = {}
        distinct_loc_own_menu = {}
        if target_type == P2IT.PROFILE:
            pid = uuids[0]
            uuids = map(lambda c: c.uuid, ComputerProfileManager().getProfileContent(pid))

            # remove the computers that already have their own menu
            own_menu_uuids = []
            registered = db.isTargetRegister(uuids, P2IT.COMPUTER)
            uuids = []
            for uuid in registered:
                if registered[uuid]:
                    own_menu_uuids.append(uuid)
                else:
                    uuids.append(uuid)
            distinct_loc_own_menu = self.__generateMenus(logger, db, own_menu_uuids, P2IT.COMPUTER)

            # get the locations for the remaining
            locations = ComputerLocationManager().getMachinesLocations(uuids)
            if len(locations.keys()) != len(uuids):
                # do fail
                logger.error("couldn't get the target entity for %s"%(str(uuids)))

            # get all the targets
            ptarget = db.getTargetsByUUID([pid])
            ptarget = ptarget[0]
            targets = db.getTargetsByUUID(uuids)
            # will get nothing that way, we need to ask the backend level
            # what is needed :
            #  {u'image_parameters': u'', 'fk_entity': 2L, u'name': u'131031', u'kernel_parameters': u'quiet', 'imaging_uuid': 'UUID2', 'fk_menu': 5L, u'type': 1L, u'id': 2L, u'uuid': u'UUID5481'},
            h_targets = {}
            for uuid in uuids:
                h_targets[uuid] = {
                    u'image_parameters':ptarget.image_parameters,
                    u'kernel_parameters':ptarget.kernel_parameters,
                    u'type':P2IT.COMPUTER,
                    u'name':'',
                    u'uuid':uuid,
                }

            # get the profile menu
            menu_items = db.getBootMenu(pid, 0, -1, '')
            menu = db.getTargetsMenuTUUID(pid)
            menu = menu.toH()
            menu, menu_items, h_pis = self.__generateMenusContent(menu, menu_items, None)

            # fill distinct_loc with the computers uuids and the appropriate menu
            for m_uuid in locations:
                loc_uuid = "UUID%s"%locations[m_uuid]['id']

                if distinct_loc.has_key(loc_uuid):
                    distinct_loc[loc_uuid][1][m_uuid] = menu.copy()
                else:
                    url = self.__chooseImagingApiUrl(loc_uuid)
                    distinct_loc[loc_uuid] = [url, {m_uuid:menu.copy()}]

                distinct_loc[loc_uuid][1][m_uuid]['target'] = h_targets[m_uuid]

        else: # P2IT.COMPUTER
            locations = ComputerLocationManager().getMachinesLocations(uuids)
            if len(locations.keys()) != len(uuids):
                # do fail
                logger.error("couldn't get the target entity for %s"%(str(uuids)))
            h_pis = {}

            targets = db.getTargetsByUUID(uuids)
            h_targets = {}
            for target in targets:
                h_targets[target.uuid] = target.toH()

            for m_uuid in locations:
                loc_uuid = "UUID%s"%locations[m_uuid]['id']
                menu_items = db.getBootMenu(m_uuid, 0, -1, '')
                menu = db.getTargetsMenuTUUID(m_uuid)
                menu = menu.toH()
                menu['target'] = h_targets[m_uuid]
                menu, menu_items, h_pis = self.__generateMenusContent(menu, menu_items, loc_uuid, m_uuid, h_pis)

                if distinct_loc.has_key(loc_uuid):
                    distinct_loc[loc_uuid][1][m_uuid] = menu
                else:
                    url = self.__chooseImagingApiUrl(loc_uuid)
                    distinct_loc[loc_uuid] = [url, {m_uuid:menu}]

        ims = h_pis.keys()
        a_pis = db.getImagesPostInstallScript(ims)
        for pis, im in a_pis:
            pis = {
                'id':pis.id,
                'name':pis.default_name,
                'desc':pis.default_desc,
                'value':pis.value
            }
            a_targets = h_pis[im.id]
            for loc_uuid, t_uuid, order in a_targets:
                distinct_loc[loc_uuid][1][t_uuid]['images'][order]['post_install_script'] = pis
        if target_type == P2IT.PROFILE:
            # merge distinct_loc and distinct_loc_own_menu
            for loc_uuid in distinct_loc_own_menu:
                if not distinct_loc.has_key(loc_uuid):
                    url = self.__chooseImagingApiUrl(loc_uuid)
                    distinct_loc[loc_uuid] = [url, {}]
                for m_uuid in distinct_loc_own_menu[loc_uuid][1]:
                    distinct_loc[loc_uuid][1][m_uuid] = distinct_loc_own_menu[loc_uuid][1][m_uuid]
        return distinct_loc

    def __synchroLocation(self, loc_uuid):
        logger = logging.getLogger()
        db = ImagingDatabase()
        ret = db.setLocationSynchroState(loc_uuid, P2ISS.RUNNING)
        menu = self.__generateLocationMenu(logger, db, loc_uuid)
        def treatFailures(result, location_uuid = loc_uuid, menu = menu, logger = logger):
            if result:
                db.setLocationSynchroState(loc_uuid, P2ISS.DONE)
            else:
                db.setLocationSynchroState(loc_uuid, P2ISS.TODO)
            return result

        url = self.__chooseImagingApiUrl(loc_uuid)
        i = ImagingApi(url.encode('utf8')) # TODO why do we need to encode....
        if i == None:
            # do fail
            logger.error("couldn't initialize the ImagingApi to %s"%(url))
        d = i.imagingServerDefaultMenuSet(menu) # WIP
        d.addCallback(treatFailures)
        return d

    def __synchroTargets(self, uuids, target_type):
        logger = logging.getLogger()
        db = ImagingDatabase()
        ret = db.changeTargetsSynchroState(uuids, target_type, P2ISS.RUNNING)
        distinct_loc = self.__generateMenus(logger, db, uuids, target_type)
        pid = None
        defer_list = []
        if target_type == P2IT.PROFILE:
            h_computers = {}
            for pid in uuids:
                uuids = []
                for loc_uuid in distinct_loc:
                    uuids.extend(distinct_loc[loc_uuid][1].keys())

                registered = db.isTargetRegister(uuids, P2IT.COMPUTER_IN_PROFILE)
                for loc_uuid in distinct_loc:
                    url = distinct_loc[loc_uuid][0]
                    menus = distinct_loc[loc_uuid][1]
                    to_register = {}
                    for uuid in menus:
                        if not registered[uuid]:
                            to_register[uuid] = menus[uuid]

                    if len(to_register.keys()) != 0:
                        ctx = self.currentContext
                        hostnames = ComputerManager().getMachineHostname(ctx, {'uuids':to_register.keys()})
                        macaddress = ComputerManager().getMachineMac(ctx, {'uuids':to_register.keys()})
                        h_hostnames = {}
                        if type(hostnames) == list:
                            for computer in hostnames:
                                h_hostnames[computer['uuid']] = computer['hostname']
                        else:
                             h_hostnames[hostnames['uuid']] = hostnames['hostname']
                        h_macaddress = {}
                        index = 0
                        if type(macaddress[0]) == list:
                            for computer in macaddress:
                                h_macaddress[to_register.keys()[index]] = computer[0]
                                index += 1
                        else:
                            h_macaddress[to_register.keys()[0]] = macaddress[0]

                        computers = []
                        for uuid in to_register:
                            if db.isTargetRegister(uuid, P2IT.COMPUTER):
                                logger.debug("computer %s is already registered as a P2IT.COMPUTER"%(uuid))
                                continue
                            menu = menus[uuid]
                            imagingData = {'menu':{uuid:menu}, 'uuid':uuid}
                            computers.append((h_hostnames[uuid], h_macaddress[uuid], imagingData))
                        if not h_computers.has_key(url):
                            h_computers[url] = []
                        h_computers[url].extend(computers)

            # some new computers are in the profile
            for url in h_computers:
                computers = h_computers[url]
                i = ImagingApi(url.encode('utf8')) # TODO why do we need to encode....
                if i != None:
                    def treatRegister(results, uuids = to_register.keys()):
                        failures = uuids
                        for l_uuid in results:
                            uuids.remove(l_uuid)
                        return failures

                    d = i.computersRegister(computers)
                    d.addCallback(treatRegister)
                    defer_list.append(d)
                else:
                    logger.error("couldn't initialize the ImagingApi to %s"%(url))

        if len(defer_list) == 0:
            return self.__synchroTargetsSecondPart(distinct_loc, target_type, pid)
        else:
            def sendResult(results, distinct_loc = distinct_loc, target_type = target_type, pid = pid, db = db):
                for result, uuids in results:
                    db.delProfileMenuTarget(uuids)
                return self.__synchroTargetsSecondPart(distinct_loc, target_type, pid)
            defer_list = defer.DeferredList(defer_list)
            defer_list.addCallback(sendResult)
            return defer_list

    def __synchroTargetsSecondPart(self, distinct_loc, target_type, pid):
        logger = logging.getLogger()
        db = ImagingDatabase()
        def treatFailures(result, location_uuid, distinct_loc = distinct_loc, logger = logger, target_type = target_type, pid = pid, db = db):
            failures = []
            success = []
            for uuid in result:
                logger.debug("succeed to synchronize menu for %s"%(str(uuid)))
                success.append(uuid)

            for uuid in distinct_loc[location_uuid][1]:
                if not uuid in success:
                    logger.warn("failed to synchronize menu for %s"%(str(uuid)))
                    failures.append(uuid)
                    # failure menu distinct_loc[location_uuid][1][fuuid]

            if pid != None:
                if len(failures) != 0:
                    db.changeTargetsSynchroState([pid], target_type, P2ISS.TODO)
                else:
                    db.changeTargetsSynchroState([pid], target_type, P2ISS.DONE)
            else:
                db.changeTargetsSynchroState(failures, target_type, P2ISS.TODO)
                db.changeTargetsSynchroState(success, target_type, P2ISS.DONE)
            return failures

        dl = []
        for location_uuid in distinct_loc:
            url = distinct_loc[location_uuid][0]
            i = ImagingApi(url.encode('utf8')) # TODO why do we need to encode....
            if i == None:
                # do fail
                logger.error("couldn't initialize the ImagingApi to %s"%(url))

            l_menus = distinct_loc[location_uuid][1]
            d = i.computersMenuSet(l_menus)
            d.addCallback(treatFailures, location_uuid)
            dl.append(d)

        def sendResult(results):
            failures = []
            for s, uuids in results:
                failures.extend(uuids)
            if len(failures) == 0:
                return [True]
            return [False, failures]

        dl = defer.DeferredList(dl)
        dl.addCallback(sendResult)
        return dl

    def synchroComputer(self, uuid):
        if not self.isTargetRegister(uuid, P2IT.COMPUTER):
            return False
        ret = self.__synchroTargets([uuid], P2IT.COMPUTER)
        return xmlrpcCleanup(ret)

    def synchroProfile(self, uuid):
        if not self.isTargetRegister(uuid, P2IT.PROFILE):
            return False
        ret = self.__synchroTargets([uuid], P2IT.PROFILE)
        return xmlrpcCleanup(ret)

    def synchroLocation(self, uuid):
        logger = logging.getLogger()
        db = ImagingDatabase()
        dl = []
        def __getUUID(x):
            x = x[0].toH()
            return x['uuid']

        # get computers in location that need synchro
        uuids = db.getComputersThatNeedSynchroInEntity(uuid)
        uuids = map(__getUUID, uuids)

        def treatComputers(results):
            logger.debug("treatComputers>>>>>>")
            logger.debug(results)

        if len(uuids) != 0:
            d1 = self.__synchroTargets(uuids, P2IT.COMPUTER)
            d1.addCallback(treatComputers)
            dl.append(d1)

        # get profiles in location that need synchro
        pids = db.getProfilesThatNeedSynchroInEntity(uuid)
        pids = map(__getUUID, pids)

        def treatProfiles(results):
            logger.debug("treatProfiles>>>>>>")
            logger.debug(results)

        if len(pids) != 0:
            d2 = self.__synchroTargets(pids, P2IT.PROFILE)
            d2.addCallback(treatProfiles)
            dl.append(d2)

        # synchro the location
        def treatLocation(results):
            logger.debug("treatLocation>>>>>>")
            logger.debug(results)

        d3 = self.__synchroLocation(uuid)
        d3.addCallback(treatLocation)
        dl.append(d3)

        def sendResult(results):
            return xmlrpcCleanup(results)

        dl = defer.DeferredList(dl)
        dl.addCallback(sendResult)
        return dl

    ###### Menus
    def getMyMenuTarget(self, uuid, target_type):
        ret = ImagingDatabase().getMyMenuTarget(uuid, target_type)
        if ret[1]:
            ret[1] = ret[1].toH()
        return ret

    def setMyMenuTarget(self, uuid, params, target_type):
        db = ImagingDatabase()
        isRegistered = db.isTargetRegister(uuid, target_type)
        if not isRegistered and target_type == P2IT.COMPUTER and db.isTargetRegister(uuid, P2IT.COMPUTER_IN_PROFILE):
            # if the computer change from a profile to it's own registering,
            # we remove the COMPUTER_IN_PROFILE target and register a COMPUTER one
            try:
                db.delProfileMenuTarget(uuid)
            except Exception, e:
                return [False, "delProfileMenuTarget : %s"%(str(e))]

        try:
            ret, target = db.setMyMenuTarget(uuid, params, target_type)
        except Exception, e:
            return [False, "setMyMenuTarget : %s"%(str(e))]

        #WIP
        if not isRegistered:
            logger = logging.getLogger()
            ret = db.changeTargetsSynchroState([uuid], target_type, P2ISS.RUNNING)
            distinct_loc = self.__generateMenus(logger, db, [uuid], target_type)

            if target_type == P2IT.COMPUTER:
                location = db.getTargetsEntity([uuid])[0]
                url = self.__chooseImagingApiUrl(location[0].uuid)
                i = ImagingApi(url.encode('utf8')) # TODO why do we need to encode....
                if i != None:
                    # imagingData = {'uuid':uuid}
                    menu = distinct_loc[location[0].uuid][1]
                    imagingData = {'menu':menu, 'uuid':uuid}
                    ctx = self.currentContext
                    MACAddress = ComputerManager().getMachineMac(ctx, {'uuid':uuid})
                    def treatRegister(result, location = location, uuid = uuid, db = db):
                        if result:
                            db.changeTargetsSynchroState([uuid], target_type, P2ISS.DONE)
                            return [True]
                        else:
                            # revert the target registering!
                            db.changeTargetsSynchroState([uuid], target_type, P2ISS.INIT_ERROR)
                            return [False, 'P2ISS.INIT_ERROR']

                    d = i.computerRegister(params['target_name'], MACAddress[0], imagingData)
                    d.addCallback(treatRegister)
                    return d
                else:
                    logger.error("couldn't initialize the ImagingApi to %s"%(url))
                    return [False, ""]
            elif target_type == P2IT.PROFILE:
                pid = uuid
                defer_list = []
                uuids = []
                for loc_uuid in distinct_loc:
                    uuids.extend(distinct_loc[loc_uuid][1].keys())
                ctx = self.currentContext
                hostnames = ComputerManager().getMachineHostname(ctx, {'uuids':uuids})
                macaddress = ComputerManager().getMachineMac(ctx, {'uuids':uuids})
                h_hostnames = {}
                if type(hostnames) == list:
                    for computer in hostnames:
                        h_hostnames[computer['uuid']] = computer['hostname']
                else:
                     h_hostnames[hostnames['uuid']] = hostnames['hostname']
                params['hostnames'] = h_hostnames
                h_macaddress = {}
                index = 0
                if type(macaddress) == list:
                    for computer in macaddress:
                        h_macaddress[uuids[index]] = computer[0]
                        index += 1
                else:
                    h_macaddress[uuids[0]] = macaddress

                try:
                    params['target_name'] = '' # put the real name!
                    ret = db.setProfileMenuTarget(uuids, pid, params)
                except Exception, e:
                    return [False, "setProfileMenuTarget : %s"%(str(e))]

                for loc_uuid in distinct_loc:
                    url = distinct_loc[loc_uuid][0]
                    menus = distinct_loc[loc_uuid][1]
                    # to do again when computerRegister is plural
                    i = ImagingApi(url.encode('utf8')) # TODO why do we need to encode....
                    if i != None:
                        dl = []
                        computers = []
                        for uuid in menus:
                            if db.isTargetRegister(uuid, P2IT.COMPUTER):
                                logger.debug("computer %s is already registered as a P2IT.COMPUTER"%(uuid))
                                continue
                            menu = menus[uuid]
                            imagingData = {'menu':{uuid:menu}, 'uuid':uuid}
                            computers.append((h_hostnames[uuid], h_macaddress[uuid], imagingData))

                        def treatRegister(results, uuids = uuids):
                            failures = uuids
                            for l_uuid in results:
                                uuids.remove(l_uuid)
                            return failures

                        d = i.computersRegister(computers)
                        d.addCallback(treatRegister)
                        defer_list.append(d)
                    else:
                        logger.error("couldn't initialize the ImagingApi to %s"%(url))
                        return [False, ""]

                def sendResult(results, pid = pid, db = db):
                    failures = []
                    for fail in results:
                        failures.extend(fail[1])
                    if len(failures) == 0:
                        db.changeTargetsSynchroState([pid], P2IT.PROFILE, P2ISS.DONE)
                        return [True]
                    db.delProfileMenuTarget(failures)
                    db.changeTargetsSynchroState([pid], P2IT.PROFILE, P2ISS.INIT_ERROR)
                    return [False, failures]

                if len(defer_list) == 0:
                    if len(uuids) == 0: # the profile is empty ...
                        db.changeTargetsSynchroState([pid], P2IT.PROFILE, P2ISS.DONE)
                        return [True]
                    else: # the profile wasn't empty => we fail to treat it
                        db.changeTargetsSynchroState([pid], P2IT.PROFILE, P2ISS.INIT_ERROR)
                        return [False]

                defer_list = defer.DeferredList(defer_list)
                defer_list.addCallback(sendResult)
                return defer_list

        return [True]

    def getMyMenuComputer(self, uuid):
        return xmlrpcCleanup(self.getMyMenuTarget(uuid, P2IT.COMPUTER))

    def setMyMenuComputer(self, target_uuid, params):
        return xmlrpcCleanup(self.setMyMenuTarget(target_uuid, params, P2IT.COMPUTER))

    def getMyMenuProfile(self, uuid):
        return xmlrpcCleanup(self.getMyMenuTarget(uuid, P2IT.PROFILE))

    def setMyMenuProfile(self, target_uuid, params):
        return xmlrpcCleanup(self.setMyMenuTarget(target_uuid, params, P2IT.PROFILE))

    ###### POST INSTALL SCRIPTS
    def getAllTargetPostInstallScript(self, target_uuid, start = 0, end = -1, filter = ''):
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getAllTargetPostInstallScript(target_uuid, start, end, filter))
        count = db.countAllTargetPostInstallScript(target_uuid, filter)
        return [count, xmlrpcCleanup(ret)]

    def getAllPostInstallScripts(self, location, start = 0, end = -1, filter = ''):
        db = ImagingDatabase()
        ret = map(lambda l: l.toH(), db.getAllPostInstallScripts(location, start, end, filter))
        count = db.countAllPostInstallScripts(location, filter)
        return [count, xmlrpcCleanup(ret)]

    def getPostInstallScript(self, pis_uuid):
        pis = ImagingDatabase().getPostInstallScript(pis_uuid)
        if pis:
            return xmlrpcCleanup(pis.toH())
        return xmlrpcCleanup(False)

    # edit
    def delPostInstallScript(self, pis_uuid):
        # TODO should be sync
        try:
            return xmlrpcCleanup(ImagingDatabase().delPostInstallScript(pis_uuid))
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def editPostInstallScript(self, pis_uuid, params):
        # TODO should be sync
        try:
            return xmlrpcCleanup(ImagingDatabase().editPostInstallScript(pis_uuid, params))
        except Exception, e:
            return xmlrpcCleanup([False, e])

    def addPostInstallScript(self, loc_id, params):
        # TODO should be sync
        try:
            return xmlrpcCleanup(ImagingDatabase().addPostInstallScript(loc_id, params))
        except Exception, e:
            return xmlrpcCleanup([False, e])

    ###### API to be called from the imaging server (ie : without authentication)
    def computerRegister(self, imaging_server_uuid, hostname, domain, MACAddress, profile, entity = None):
        """
        Called by the Package Server to register a new computer.
        The computer name may contain a profile and an entity path (like profile:/entityA/entityB/computer)
        """

        logger = logging.getLogger()
        db = ImagingDatabase()

        imaging_server = db.getImagingServerByPackageServerUUID(imaging_server_uuid, True)
        if not imaging_server:
            return [False, "Failed to find the imaging server %s" % imaging_server_uuid]
        imaging_server = imaging_server[0]
        if imaging_server == None:
            return [False, "Failed to find the imaging server %s" % imaging_server_uuid]

        loc_id = imaging_server[1].uuid
        computer = {
            'computername': hostname, # FIXME : what about domain ?
            'computerdescription': '',
            'computerip': '',
            'computermac': MACAddress,
            'computernet': '',
            'location_uuid': loc_id}

        uuid = None
        db_computer = ComputerManager().getComputerByMac(MACAddress)
        if db_computer != None:
            uuid = db_computer['uuid']
        if uuid == None or type(uuid) == list and len(uuid) == 0:
            logger.info("the computer %s (%s) does not exist in the backend, trying to add it" % (hostname, MACAddress))
            # the computer does not exists, so we create it
            uuid = ComputerManager().addComputer(None, computer)
            if uuid == None:
                logger.error("failed to create computer %s (%s)" % (hostname, MACAddress))
                return [False, "failed to create computer %s (%s)" % (hostname, MACAddress)]
            else:
                logger.debug("The computer %s (%s) has been successfully added to the inventory database" % (hostname, MACAddress))
        else:
            logger.debug("computer %s (%s) already exists, we dont need to declare it again" % (hostname, MACAddress))

        target_type = P2IT.COMPUTER
        if not db.isTargetRegister(uuid, target_type):
            logger.info("computer %s (%s) needs to be registered" % (hostname, MACAddress))
            params = {
                'target_name': hostname,
            }
            # Set the computer menu
            db.setMyMenuTarget(uuid, params, target_type) # FIXME : are not we supposed to deal with the return value ?
            # Tell the MMC agent to synchronize the menu
            # As it in some way returns a deferred object, it is run in
            # background
            self.synchroComputer(uuid)
        else:
            logger.debug("computer %s (%s) dont need registration" % (hostname, MACAddress))

        if profile:
            # TODO
            pass

#        if entities:
#            # TODO
#            pass
        return [True, uuid]

    def imagingServerRegister(self, name, url, uuid):
        """
        Called by the imagingServer register script, it fills all the required fields for an
        imaging server, then the server is available in the list of server not linked to any entity
        and need to be linked.
        """
        db = ImagingDatabase()
        if db.countImagingServerByPackageServerUUID(uuid) != 0:
            return [False, "The UUID you try to declare (%s) already exists in the database, please check you know what you are doing." % (uuid)]
        db.registerImagingServer(name, url, uuid)
        return [True, "Your Imaging Server has been correctly registered. You can now associate it to the correct entity in the MMC."]

    def getComputerByMac(self, mac):
        """
        Called by the package server, to obtain a computer UUID/shortname/fqdn in exchange of its MAC address
        """
        assert pulse2.utils.isMACAddress(mac)
        computer = ComputerManager().getComputerByMac(mac)
        if not computer:
            return [False, "imaging.getComputerByMac() : I was unable to find a computer corresponding to the MAC address %s" % mac]
        return [True, {'uuid': "UUID%s" % computer['uuid'], 'mac': mac, 'shortname': computer['hostname'], 'fqdn': computer['hostname']}]

    def logClientAction(self, imaging_server_uuid, computer_uuid, level, phase, message):
        """
        Called by the package server, to log some info
        """
        logger = logging.getLogger()
        log = {
            'level':level,
            'detail':message,
            'state':phase
        }
        db = ImagingDatabase()
        if db.countImagingServerByPackageServerUUID(imaging_server_uuid) == 0:
            return [False, "The imaging server UUID you try to access doesn't exist in the imaging database."]
        if not db.isTargetRegister(computer_uuid, P2IT.COMPUTER):
            return [False, "The computer UUID you try to access doesn't exists in the imaging database."]

        try:
            ret = db.logClientAction(imaging_server_uuid, computer_uuid, log)
            ret = [ret, '']
        except Exception, e:
            logger.exception(e)
            ret = [False, str(e)]
        return ret

    def imageRegister(self, imaging_server_uuid, computer_uuid, image_uuid, is_master, name, desc, path, size, creation_date, creator='root'):
        """
        Called by the Package Server to register a new Image.
        """
        image = {
            'name': name,
            'desc': desc,
            'path': path,
            'uuid': image_uuid,
            'checksum': '',
            'size': size,
            'creation_date': creation_date,
            'is_master': is_master,
            'creator': creator}
        db = ImagingDatabase()
        if db.countImagingServerByPackageServerUUID(imaging_server_uuid) == 0:
            return [False, "The imaging server UUID you try to access doesn't exist in the imaging database."]
        if not db.isTargetRegister(computer_uuid, P2IT.COMPUTER):
            return [False, "The computer UUID (%s) you try to access doesn't exists in the imaging database." % computer_uuid]

        try:
            ret = db.registerImage(imaging_server_uuid, computer_uuid, image)
            ret = [ret, '']
        except Exception, e:
            logging.getlogger().exception(e)
            ret = [False, str(e)]
        return ret

    def injectInventory(self, imaging_server_uuid, computer_uuid, inventory=None):
        """
        Called by the Package Server to inject an inventory.
        """
        # TODO !
        return [True, True]

    def getDefaultMenuForSuscription(self):
        """
        Called by the Package Server to get the default menu used by computers to suscribe from the database.
        """
        db = ImagingDatabase()
        logger = logging.getLogger()
        menu = self.__generateDefaultSuscribeMenu(logger, db)
        return xmlrpcCleanup(menu)

    def computerChangeDefaultMenuItem(self, imaging_server_uuid, computer_uuid, item_number):
        """
        Called by the Package Server to change the default value of a menu
        """
        # TODO !
        return [True, True]
