# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com/
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
Pulse2 database handler
"""

# SqlAlchemy
from sqlalchemy import create_engine, MetaData, Table, Column, Text
from sqlalchemy.orm import create_session, mapper
from sqlalchemy.sql import and_
from sqlalchemy.exc import DBAPIError

# PULSE2 modules
# from mmc.database.database_helper import DBObject
from mmc.database import database_helper
from pulse2.database.dyngroup.dyngroup_database_helper import DyngroupDatabaseHelper

# Imported last
import logging


class Pulse2Database(DyngroupDatabaseHelper):
    """
    Singleton Class to query the pulse2 database.

    """
    is_activated = False

    def db_check(self):
        self.my_name = "pulse2"
        self.configfile = "pulse2.ini"
        return DyngroupDatabaseHelper.db_check(self)

    def activate(self, config):
        self.logger = logging.getLogger()
        if self.is_activated:
            return None

        self.logger.info("Pulse2 database is connecting")
        self.config = config
        self.db = create_engine(self.makeConnectionPath(), pool_recycle = self.config.dbpoolrecycle, pool_size = self.config.dbpoolsize)
        self.metadata = MetaData(self.db)
        if not self.initMappersCatchException():
            self.session = None
            return False
        self.metadata.create_all()
        self.session = create_session()
        self.is_activated = True
        self.logger.debug("Pulse2 database connected (version:%s)"%(self.version.select().execute().fetchone()[0]))
        return True

    def initMappers(self):
        """
        Initialize all SQLalchemy mappers needed for the pulse2 database
        """

        # entity/package server association
        self.packageServerEntity = Table("PackageServerEntity",
                self.metadata,
                Column('entity_uuid', Text, primary_key=True),
                Column('package_server_uuid', Text, primary_key=True),
                autoload = True)
        mapper(PackageServerEntity, self.packageServerEntity)

        # version
        self.version = Table("Version", self.metadata, autoload = True)

    def getDbConnection(self):
        NB_DB_CONN_TRY = 2
        ret = None
        for i in range(NB_DB_CONN_TRY):
            try:
                ret = self.db.connect()
            except DBAPIError as e:
                self.logger.error(e)
            except Exception as e:
                self.logger.error(e)
            if ret: break
        if not ret:
            raise "Database connection error"
        return ret

    ####################################
    ## entity/package server

    def getPackageServerEntityByPackageServer(self, ps_uuid):
        """
        @param ps_uuid: the package server uuid
        @type ps_uuid: str

        @returns: the PackageServerEntity that correspond to the given ps_uuid
        @rtype: the PackageServerEntity object
        """
        session = create_session()
        ret = session.query(PackageServerEntity).filter(self.packageServerEntity.c.package_server_uuid == ps_uuid).one()
        session.close()
        return ret

    def getPackageServerEntityByEntity(self, e_uuid):
        """
        @param e_uuid: the entity uuid
        @type e_uuid: str

        @returns: the PackageServerEntities that correspond to the given e_uuid
        @rtype: a list of PackageServerEntity object
        """
        session = create_session()
        ret = session.query(PackageServerEntity).filter(self.packageServerEntity.c.entity_uuid == e_uuid).all()
        session.close()
        return ret

    def getPackageServerEntityByEntities(self, e_uuids):
        session = create_session()
        ret1 = session.query(PackageServerEntity).add_column(self.packageServerEntity.c.entity_uuid).filter(self.packageServerEntity.c.entity_uuid.in_(e_uuids)).all()
        session.close()
        ret = {}
        for pes, e_uuid in ret1:
            if e_uuid not in ret:
                ret[e_uuid] = []
            ret[e_uuid].append(pes)
        return ret

    def getPackageServerEntity(self, ps_uuid, e_uuid):
        """
        @param ps_uuid: the package server uuid
        @type ps_uuid: str

        @param e_uuid: the entity uuid
        @type e_uuid: str

        @returns: the PackageServerEntity that correspond to the given ps_uuid and e_uuid
        @rtype: the PackageServerEntity object
        """
        session = create_session()
        ret = session.query(PackageServerEntity).filter(and_(self.packageServerEntity.c.package_server_uuid == ps_uuid, self.packageServerEntity.c.entity_uuid == e_uuid)).one()
        session.close()
        return ret

    def putPackageServerEntity(self, ps_uuid, e_uuid):
        """
        @param ps_uuid: the package server uuid
        @type ps_uuid: str

        @param e_uuid: the entity uuid
        @type e_uuid: str

        @returns: the PackageServerEntity that correspond to the given ps_uuid and e_uuid
        @rtype: the PackageServerEntity object
        """
        session = create_session()
        pse = PackageServerEntity()
        pse.package_server_uuid = ps_uuid
        pse.entity_uuid = e_uuid
        session.add(pse)
        session.flush()
        session.close()
        return pse

    def delPackageServerEntity(self, e_uuid):
        """
        @param e_uuid: the entity uuid
        @type e_uuid: str

        @return: True if success
        @rtype: bool
        """
        session = create_session()
        pse = session.query(PackageServerEntity).filter(self.packageServerEntity.c.entity_uuid==e_uuid).all()
        if pse and len(pse) == 1:
            session.delete(pse[0])
        else :
            session.close()
            return False
            
        session.flush()
        session.close()
        return True


    ################################
    ## MEMBERS

def id2uuid(id):
    return "UUID%s"%(str(id))

def uuid2id(uuid):
    return uuid.replace('UUID', '')

##############################################################################################################
class PackageServerEntity(database_helper.DBObject):
    to_be_exported = ['entity_uuid', 'package_server_uuid']

