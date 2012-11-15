<?php

/**
 * (c) 2012 Mandriva, http://www.mandriva.com
 *
 * This file is part of Mandriva Management Console (MMC).
 *
 * MMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * MMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

function getZoneType($zoneName) {
    $zones_types = getZonesTypes();
    if (startsWith($zoneName, $zones_types['internal']))
        return _T("Internal");
    if (startsWith($zoneName, $zones_types['external']))
        return _T("External");
    if ($zoneName == "fw")
        return _T("Server");
    if ($zoneName == "all")
        return _T("All");
    return _T("Unknow");
}

function startsWith($haystack, $needle) {
    return !strncmp($haystack, $needle, strlen($needle));
}

function handleServicesModule($popup) {
    if (in_array("services", $_SESSION["modulesList"])) {
        $urlRestart = urlStrRedirect('services/control/restart', array("service" => "postfix", "output" => "json"));
        $urlCheck = urlStrRedirect('services/control/status', array("service" => "postfix", "output" => "json"));
        $popup->add('<br /><p>' . _T("Restart the firewall service ?") . ' <button id="restartBtn" class="btn btn-small" onclick="restartFirewall(\'' . $urlRestart . '\', \'' . $urlCheck . '\')"> '. _T("Restart") .'</button> <span id="restartInfo"></span></p>');
    }
    return $popup;
}

?>
