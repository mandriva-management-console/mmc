<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
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
 * Author: Alexandre Proença e-mail alexandre@mandriva.com.br
 * Date: 09/02/2012
 * Last Change: 11/20/2012
 * Description: This a page to render html elements and get user input, check the input and call action page or function
*/

require("localSidebar.php");
require("graph/navbar.inc.php");

$list = "machines";
$main_title = _T("IP whitelist", "squid");
$sub_title = _T("Add IP", "squid");
$title_datagrid= _T("List of allowed IP addresses", "squid");
$page = "squid/internet/machmanager";
$errorMessage = _T("Invalid IP address.", "squid");
$successMessage = _T("IP added.", "squid");
$re = "/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/";
$elt_label = _T("IP address", "squid");
$elt_help = "Provide full Internet access to the IP. Overrides all blacklists.";
$del_page = "deletem";

include('modules/squid/includes/manager.php');

?>
