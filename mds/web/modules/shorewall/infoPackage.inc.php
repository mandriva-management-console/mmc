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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * shorewall module declaration
 */

include('modules/shorewall/includes/shorewall-xmlrpc.inc.php');
$zones_types = getZonesTypes();
$lan_zones = getShorewallZones($zones_types['internal']);
$wan_zones = getShorewallZones($zones_types['external']);

$mod = new Module("shorewall");
$mod->setVersion("2.4.3");
$mod->setRevision('');
$mod->setDescription(_T("Firewall management", "shorewall"));
$mod->setAPIVersion("0:0:0");
$mod->setPriority(60);

$submod = new SubModule("shorewall", _T("Firewall", "shorewall"));
$submod->setDefaultPage("shorewall/shorewall/internal_fw");
$submod->setImg('modules/shorewall/graph/navbar/shorewall');
$submod->setPriority(60);

/* Add the page to the module */
$page = new Page("internal_fw", _T("Internal &rarr; Server", "shorewall"));
$submod->addPage($page);
if (!$lan_zones)
    $page->setOptions(array("visible" => False));

$page = new Page("ajax_internal_fw");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("delete_internal_fw_rule");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("external_fw", _T("External &rarr; Server", "shorewall"));
$submod->addPage($page);
if (!$wan_zones)
    $page->setOptions(array("visible" => False));
else if (!$lan_zones)
    $submod->setDefaultPage("shorewall/shorewall/external_fw");

$page = new Page("ajax_external_fw");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("delete_external_fw_rule");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("internal_external", _T("Internal &rarr; External", "shorewall"));
$submod->addPage($page);
if (!$lan_zones || !$wan_zones)
    $page->setOptions(array("visible" => False));

$page = new Page("ajax_internal_external");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("delete_internal_external_rule");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("external_internal", _T("External &rarr; Internal", "shorewall"));
$submod->addPage($page);
if (!$lan_zones || !$wan_zones)
    $page->setOptions(array("visible" => False));

$page = new Page("ajax_external_internal");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("delete_external_internal_rule");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("masquerade", _T("NAT"));
$submod->addPage($page);
if (!$lan_zones || !$wan_zones)
    $page->setOptions(array("visible" => False));

$page = new Page("ajax_masquerade");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("delete_masquerade_rule");
$page->setOptions(array("visible" => False, "AJAX" => True));
$submod->addPage($page);

$page = new Page("restart_service", _T("Restart service", "shorewall"));
$page->setOptions(array("visible" => False, "AJAX" => False));
$submod->addPage($page);

$mod->addSubmod($submod);

$MMCApp =& MMCApp::getInstance();
$MMCApp->addModule($mod);

?>
