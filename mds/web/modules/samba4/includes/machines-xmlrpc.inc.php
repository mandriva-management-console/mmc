<?php
/**
 * (c) 2014 Zentyal, http://www.zentyal.com
 *
 * $Id$
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * Author(s):
 *   Miguel Julián <mjulian@zentyal.com>
 */
?>
<?php
function searchMachines($filter = null) {
    if ($filter == "") $filter = null;
    else $filter = "*".$filter . "*";

    //return xmlCall("samba4.searchMachines", $filter);
    return array(
        array("vaca1", "Vaca1 computer", True),
        array("vaca2", "Vaca2 computer", True),
        array("vaca3", "Vaca3 computer", False),
        array("vaca4", "Vaca4 computer", True),
    );
}

function getMachine($machineName) {
    //return xmlCall("samba4.getMachine", $machineName);
    return array("name" => "vaca1", "description" => "Vaca1 computer", "enabled" => True);
}

function editMachine($machineName) {
    //return xmlCall("samba4.editMachine", array($name, $description, $enabled));
    return True;
}
?>
