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

require("modules/shorewall/shorewall/localSidebar.php");
require("graph/navbar.inc.php");

// NAT rules list display

$ajax = new AjaxFilter(urlStrRedirect("shorewall/shorewall/ajax_masquerade"));
$ajax->display();

$p = new PageGenerator(_T("NAT Rules", "shorewall"));
$p->setSideMenu($sidemenu);
$p->display();
echo '<p>' . _T("Provide internet access to internal network(s).") . '</p>';

$ajax->displayDivToUpdate();

// Handle form return

if (isset($_POST['badd'])) {
    addMasqueradeRule($_POST['external_if'], ($_POST['internal_if']));
    enableIpFoward();
    $n = new NotifyWidgetSuccess(_T("NAT rule added."));
    handleServicesModule($n, array("shorewall" => _T("Firewall")));
    redirectTo(urlStrRedirect("shorewall/shorewall/masquerade"));
}

if (isset($_POST['brestart'])) {
    redirectTo(urlStrRedirect("shorewall/shorewall/restart_service",
                              array("page" => "masquerade")));
}

// Add NAT rule form

$t = new TitleElement(_T("Add NAT rule"), 2);
$t->display();

$f = new ValidatingForm();
$f->push(new Table());

$zones_types = getZonesTypes();

$external = array();
$externalVals = array();
foreach(getZonesInterfaces($zones_types["external"]) as $zone) {
    $external[] = sprintf("%s (%s)", $zone[0], $zone[1]);
    $externalVals[] = $zone[1];
}
$externalTpl = new SelectItem("external_if");
$externalTpl->setElements($external);
$externalTpl->setElementsVal($externalVals);

$f->add(new TrFormElement(_T("External network (Internet)"), $externalTpl));

$internal = array();
$internalVals = array();
foreach(getZonesInterfaces($zones_types["internal"]) as $zone) {
    $internal[] = sprintf("%s (%s)", $zone[0], $zone[1]);
    $internalVals[] = $zone[1];
}
$internalTpl = new SelectItem("internal_if");
$internalTpl->setElements($internal);
$internalTpl->setElementsVal($internalVals);

$f->add(new TrFormElement(_T("Internal network"), $internalTpl));

$f->pop();
$f->addButton("badd", _T("Add NAT rule"));
$f->display();

if (!servicesModuleEnabled()) {
    echo '<br/>';
    $f = new ValidatingForm(array("id" => "service"));
    $f->addButton("brestart", _T("Restart service"));
    $f->display();
}
?>
