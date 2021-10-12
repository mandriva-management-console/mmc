# -*- coding: utf-8; -*-
#
# (c) 2007-2010 Mandriva, http://www.mandriva.com
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
# along with Pulse 2.  If not, see <http://www.gnu.org/licenses/>.

"""
Contains some utilities methods.
"""

def getInventoryParts():
    """
    @return: Return all available inventory parts
    @rtype: list
    """
    return [ "Bios", "BootDisk", "BootGeneral", "BootMem", "BootPart", "BootPCI", "Controller", "Custom", "Drive", "Hardware", "Input", "Memory", "Modem", "Monitor", "Network", "Port", "Printer", "Slot", "Software", "Sound", "Storage", "VideoCard", "Registry", "Entity" ]


def getInventoryNoms(table = None):
    """
    @return: Return all available nomenclatures tables
    @rtype: dict
    """
    noms = {
        'Registry':['Path']
    }

    if table == None:
        return noms
    if table in noms:
        return noms[table]
    return None
    
