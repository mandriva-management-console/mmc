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

require_once('modules/network/includes/network-xmlrpc.inc.php');

/**
 * module declaration
 */
$mod = new Module("network");
$mod->setVersion("2.5.0");
$mod->setRevision('$Rev$');
$mod->setDescription(_T("Network management", "network"));
$mod->setAPIVersion('2:1:0');
$mod->setPriority(30);

/**
 * user submod definition
 */

$submod = new SubModule("network");
$submod->setDescription(_T("Network", "network"));
$submod->setImg('modules/network/graph/img/network');
$submod->setDefaultPage("network/network/index");
$submod->setPriority(30);
$visibleDNS = True;
if (!hasDNS()) {
    $submod->setDefaultPage("network/network/subnetindex");
    $visibleDNS = False;
}
$visibleDHCP = True;
if (!hasDHCP()) {
    $submod->setDefaultPage("network/network/index");
    $visibleDHCP = False;
}
$visibleServices = True;
if (in_array("services", $_SESSION['supportModList']))
    $visibleServices = False;

$page = new Page("index",_T("DNS zones","network"));
$page->setFile("modules/network/network/index.php",
	       array("visible" => $visibleDNS)
	       );
$submod->addPage($page);

$page = new Page("ajaxZoneFilter");
$page->setFile("modules/network/network/ajaxZoneFilter.php",
	       array("AJAX" =>True,"visible"=>False)
	       );
$submod->addPage($page);

$page = new Page("ajaxSubnetFilter");
$page->setFile("modules/network/network/ajaxSubnetFilter.php",
	       array("AJAX" =>True,"visible"=>False)
	       );
$submod->addPage($page);

$page = new Page("ajaxZoneMembersFilter");
$page->setFile("modules/network/network/ajaxZoneMembersFilter.php",
	       array("AJAX" =>True,"visible"=>False)
	       );
$submod->addPage($page);

$page = new Page("ajaxZoneRecordsFilter");
$page->setFile("modules/network/network/ajaxZoneRecordsFilter.php",
	       array("AJAX" =>True,"visible"=>False)
	       );
$submod->addPage($page);

$page = new Page("ajaxSubnetMembersFilter");
$page->setFile("modules/network/network/ajaxSubnetMembersFilter.php",
	       array("AJAX" =>True,"visible"=>False)
	       );
$submod->addPage($page);

$page = new Page("ajaxDnsGetZoneFreeIp");
$page->setFile("modules/network/network/ajaxDnsGetZoneFreeIp.php",
	       array("AJAX" =>True,"visible"=>False)
	       );
$submod->addPage($page);

$page = new Page("ajaxDhcpGetSubnetFreeIp");
$page->setFile("modules/network/network/ajaxDhcpGetSubnetFreeIp.php",
	       array("AJAX" =>True,"visible"=>False)
	       );
$submod->addPage($page);

$page = new Page("delete",_T("Delete a DNS zone","network"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("deletehost",_T("Delete a host","network"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("deleterecord",_T("Delete a record","network"));
$page->setOptions( array ("noHeader" => True,"visible"=>False));
$submod->addPage($page);

$page = new Page("edithost",_T("Edit a DNS record","network"));
$page->setOptions( array ("visible"=>False));
$submod->addPage($page);

$page = new Page("editrecord",_T("Edit a DNS record","network"));
$page->setOptions( array ("visible"=>False));
$submod->addPage($page);

$page = new Page("add",_T("Add a DNS zone", "network"));
$page->setFile("modules/network/network/add.php",
               array("visible" => $visibleDNS)
              );
$submod->addPage($page);

$page = new Page("edit",_T("Edit a DNS zone", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("addhost",_T("Add a host to a zone", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("addrecord",_T("Add a record to a zone", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("zonemembers",_T("Members of a zone", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("zonerecords",_T("Records of a zone", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("subnetadd",_T("Add a DHCP subnet", "network"));
$page->setFile("modules/network/network/subnetadd.php",
               array("visible" => $visibleDHCP)
               );
$submod->addPage($page);

$page = new Page("subnetedit",_T("Edit DHCP subnet", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("subnetindex",_T("DHCP subnets","network"));
$page->setFile("modules/network/network/subnetindex.php",
               array("visible" => $visibleDHCP)
               );
$submod->addPage($page);

$page = new Page("subnetdelete",_T("Delete a subnet", "network"));
$page->setOptions(array("noHeader" => True, "visible"=>False));
$submod->addPage($page);

$page = new Page("subnetaddhost",_T("Add a host to a DHCP subnet", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("subnetedithost",_T("Edit a host from a DHCP subnet", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("subnetdeletehost",_T("Delete a host from a subnet", "network"));
$page->setOptions(array("noHeader" => True, "visible"=>False));
$submod->addPage($page);

$page = new Page("subnetmembers",_T("Members of a DHCP subnet", "network"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("servicedhcpfailover",_T("DHCP failover","network"));
$page->setOptions(array("visible" => $visibleDHCP));
$submod->addPage($page);

$page = new Page("services",_T("Network services management","network"));
$page->setOptions(array("visible" => $visibleServices));
$submod->addPage($page);

$page = new Page("servicelog",_T("Network services management","network"));
$page->setOptions(array("noHeader" => True, "visible"=>False));
$submod->addPage($page);

$page = new Page("servicestart",_T("Network services management","network"));
$page->setOptions(array("noHeader" => True, "visible"=>False));
$submod->addPage($page);

$page = new Page("servicestop",_T("Network services management","network"));
$page->setOptions(array("noHeader" => True, "visible"=>False));
$submod->addPage($page);

$page = new Page("servicereload",_T("Network services management","network"));
$page->setOptions(array("noHeader" => True, "visible"=>False));
$submod->addPage($page);

$page = new Page("servicerestart",_T("Network services management","network"));
$page->setOptions(array("noHeader" => True, "visible"=>False));
$submod->addPage($page);

$mod->addSubmod($submod);

$MMCApp =&MMCApp::getInstance();
$MMCApp->addModule($mod);

/* Add DHCP service log viewer */
/* LogView is deprecated ! */
$base = &$MMCApp->getModule("base");
$logview = &$base->getSubmod("logview");

if ($logview) {
    $page = new Page("dhcpindex",_T("DHCP service log", "network"));
    $page->setFile("modules/network/dhcplogview/index.php", array("expert" => true));

    $logview->addPage($page);

    $page = new Page("dhcpshow");
    $page->setFile("modules/network/dhcplogview/ajax_showlog.php",
               array("AJAX" =>True,"visible"=>False)
               );
    $logview->addPage($page);

    $page = new Page("dhcpsetsearch");
    $page->setFile("modules/network/dhcplogview/ajax_setSearch.php",
               array("AJAX" =>True,"visible"=>False)
               );
    $logview->addPage($page);

    /* Add DNS service log viewer */
    $page = new Page("dnsindex",_T("DNS service log", "network"));
    $page->setFile("modules/network/dnslogview/index.php", array("expert" => true));

    $logview->addPage($page);

    $page = new Page("dnsshow");
    $page->setFile("modules/network/dnslogview/ajax_showlog.php",
               array("AJAX" =>True,"visible"=>False)
               );
    $logview->addPage($page);

    $page = new Page("dnssetsearch");
    $page->setFile("modules/network/dnslogview/ajax_setSearch.php",
               array("AJAX" =>True,"visible"=>False)
               );
    $logview->addPage($page);
}

?>
