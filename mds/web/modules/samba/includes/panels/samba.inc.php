<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2012 Mandriva, http://www.mandriva.com
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

include_once("modules/dashboard/includes/panel.class.php");

$options = array(
    "class" => "SambaPanel",
    "id" => "samba",
    "refresh" => 30,
    "title" => _T("SAMBA status", "samba"),
);

class SambaPanel extends Panel {

    function display_content() {
        echo '<p><a href="' . urlStrRedirect('samba/config/sambastatus') .'">';
        printf(dngettext("samba", "%d open session", "%d open sessions", $this->data['sessions']), $this->data['sessions']);
        echo '</a><br/>';
        echo '<a href="' . urlStrRedirect('samba/config/sambastatus') .'">';
        printf(dngettext("samba", "%d share connection", "%d share connections", $this->data['shares']), $this->data['shares']);
        echo '</a></p>';
    }
}
