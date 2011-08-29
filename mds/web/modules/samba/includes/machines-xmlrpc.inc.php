<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
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
 */
?>
<?php
/* $Id$ */

function get_machines() {
    return xmlCall("samba.getMachinesLdap", null);
}

function add_machine($machine, $comment) {
    $param= array($machine,$comment);
    return xmlCall("samba.addMachine",$param);
}

function get_machine($machine) {
    return xmlCall("samba.getMachine",$machine);
}

function del_machine($machine) {
    return xmlCall("samba.delMachine",$machine);
}

function change_machine($machine, $options) {
    return xmlCall("samba.changeMachine", array($machine, $options));
}

function search_machines($filter = null) {
    if ($filter == "") $filter = null;
    else $filter = "*".$filter . "*";
    return xmlCall("samba.getMachinesLdap",$filter);
}

?>
