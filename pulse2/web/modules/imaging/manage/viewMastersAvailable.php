<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com
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

print "<h1>"._T('Available Masters','imaging')."</h1>";

include('modules/imaging/includes/includes.php');
require_once('modules/imaging/includes/xmlrpc.inc.php');
$masters = getMasterAvailable();
$masters_titles = array();
$count = count($masters);
foreach ($masters as $master){
    $masters_titles[] = $master['Computer']." : ".$master['Image'];
}
$n = new OptimizedListInfos($masters_titles, _T("Master", "imaging"));
$n->setItemCount($count);
$n->setNavBar(new AjaxNavBar($count, $filter1));
$n->start = 0;
$n->end = 50;
ob_start();
$n->display();
?> 
