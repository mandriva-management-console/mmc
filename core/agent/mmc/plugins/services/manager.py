# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2012 Mandriva, http://www.mandriva.com
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

"""
Systemd service manager
"""

import json
import logging
from systemd_dbus.manager import Manager

from mmc.agent import PluginManager
from mmc.support.mmctools import SingletonN, shlaunch
from mmc.plugins.services.config import ServicesConfig

logger = logging.getLogger()


class ServiceManager(object, metaclass=SingletonN):
    def __init__(self):
        self.m = Manager()
        self.config = ServicesConfig("services")
        self.units = []

    def get_plugin_services(self, plugin):
        if plugin in self.config.services:
            return self.config.services[plugin]
        else:
            return []

    def is_plugin_service(self, service):
        """
        Return true if service is managed by the MMC
        """
        service = service.rstrip(".service")
        for plugin in self.config.services:
            if service in self.config.services[plugin]:
                return True
        return False

    def list_plugins_services(self):
        """
        Returns list of services ordered by MMC plugins
        """
        list = {}
        plugins = PluginManager().getEnabledPluginNames()
        for plugin in plugins:
            for plugin_services, services in list(self.config.services.items()):
                if plugin == plugin_services and services:
                    list[plugin] = []
                    for service in services:
                        if service not in self.config.blacklist:
                            list[plugin].append(self.get_unit_info(service))
                    list[plugin] = sorted(list[plugin], key=lambda s: s['id'].lower())
        return list

    def has_inactive_plugins_services(self):
        """
        Return True if one of the plugin's services
        is not active
        """
        for plugin, services in list(self.list_plugins_services().items()):
            for service in services:
                if service['active_state'] not in ("active", "unavailable"):
                    return True
        return False

    def list_others_services(self, filter=None):
        """
        Returns list of services not managed by MMC plugins
        """
        list = []
        for unit in self.list():
            if not self.is_plugin_service(unit['id']) and \
               unit['id'].endswith(".service") and \
               unit['unit_file_state'] != "static":
                if filter and any(v for k, v in list(unit.items()) if filter in str(v)):
                    list.append(unit)
                if not filter:
                    list.append(unit)
        list = sorted(list, key=lambda s: s['id'].lower())
        return list

    def list(self):
        if not self.units:
            self.units = self.m.list_units()
        units = []
        for unit in self.units:
            unit = self.serialize_unit(unit)
            if unit['id'].split(".")[0] not in self.config.blacklist:
                units.append(unit)
        units = sorted(units, key=lambda u: u['id'].lower())
        return units

    def get_unit(self, service):
        service = service.replace(".service", "", 1)
        for unit in self.units:
            if unit.properties.Id == '%s.service' % service:
                return unit
        return False

    def get_unit_info(self, service):
        service = service.replace(".service", "", 1)
        unit = self.get_unit(service)
        return self.serialize_unit(unit, service)

    def serialize_unit(self, unit, service=""):
        if unit:
            return {'id': str(unit.properties.Id),
                    'description': str(unit.properties.Description),
                    'active_state': str(unit.properties.ActiveState),
                    'can_start': bool(unit.properties.CanStart),
                    'can_stop': bool(unit.properties.CanStop),
                    'can_reload': bool(unit.properties.CanReload),
                    'unit_file_state': str(unit.properties.UnitFileState)}
        else:
            return {'id': service + ".service",
                    'description': service,
                    'active_state': "unavailable",
                    'can_start': False,
                    'can_stop': False,
                    'can_reload': False,
                    'unit_file_state': "unavailable"}

    def start(self, service):
        unit = self.get_unit(service)
        unit.start('fail')
        return True

    def stop(self, service):
        unit = self.get_unit(service)
        unit.stop('fail')
        return True

    def restart(self, service):
        unit = self.get_unit(service)
        unit.restart('fail')
        return True

    def reload(self, service):
        unit = self.get_unit(service)
        unit.reload('fail')
        return True

    def status(self, service):
        unit = self.get_unit(service)
        return (str(unit.properties.LoadState),
                str(unit.properties.ActiveState),
                str(unit.properties.SubState))

    def log(self, service="", filter=""):
        service = service.replace(".service", "", 1)
        result = []
        service_filter = ""
        fields = ("PRIORITY", "_HOSTNAME", "TIMESTAMP", "_UID", "_GID", "_PID", "MESSAGE", "_SYSTEMD_UNIT")
        if service:
            service_filter += '_SYSTEMD_UNIT=%s.service' % service
        code, out, err = shlaunch('%s -n 500 -o json %s' % (self.config.journalctl_path, service_filter))
        logs = []
        for line in out:
            try:
                logs.append(json.loads(line))
            except:
                if "Reboot" in line:
                    logs.append({"MESSAGE": "Reboot"})
        for message in logs:
            if "MESSAGE" in message and isinstance(message["MESSAGE"], str):
                if "_SOURCE_REALTIME_TIMESTAMP" in message:
                    message["TIMESTAMP"] = int(int(message["_SOURCE_REALTIME_TIMESTAMP"]) / 1000000)
                else:
                    message["TIMESTAMP"] = False
                # remove unneeded fields
                for key, value in list(message.copy().items()):
                    if key not in fields:
                        del message[key]
                if filter and any(filter in str(v) for k, v in list(message.items())):
                    result.append(message)
                if not filter:
                    result.append(message)
        return result[::-1]
