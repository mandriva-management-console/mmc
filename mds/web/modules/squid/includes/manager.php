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
 * Description: This page render html elements and get user input,
 *              check the input and call action page or function
*/

require("squid.inc.php"); //call squid-xmlrpc.inc.php (xml-rpc functions)

//Check Add Botton
if (isset($_POST["btAdd"])) {
    $data = $_POST["eltdata"];
    $serviceName = getServiceName();
	if (!preg_match($re, $data)) {
		new NotifyWidgetFailure($errorMessage);
        redirectTo(urlStrRedirect($page));
    }
    else {
		addElementInList($list, $data);
		if (!isXMLRPCError()) {
            $n = new NotifyWidgetSuccess($successMessage);
            handleServicesModule($n, array($serviceName => _T("Proxy", "squid")), "reload");
            redirectTo(urlStrRedirect($page));
		}
	}
}

// If the services module is not enabled, fallback
// to the old ServiceManager
if (!servicesModuleEnabled()) {
    // restart service button
    if (isset($_POST["btApply"]))
	    include('restart.php');
}

// Get keywords and Domain list from /etc/squid/rules/group_internet/normal_blacklist.txt
$arrB = get_list($list);

//New page with side menu, create and show
$p = new PageGenerator($main_title);
$p->setSideMenu($sidemenu);
$p->display();

//Create a list of informations
$n = new ListInfos($arrB, $title_datagrid);
$n->first_elt_padding = 1;
$n->disableFirstColumnActionLink();
$n->setName(_T("List"));

//Add action on list and show list
$n->addActionItem(new ActionPopupItem(_T("Delete"),$del_page,"delete","eltdata") );
$n->display();

//Create Title and show
$t = new TitleElement($sub_title, 2);
$t->display();

//Create Form to get informations
$f = new ValidatingForm();

//Create table inside Form
$f->push(new Table());

//Add element input in table
$f->add(new TrFormElement($elt_label, new InputTpl("eltdata"), array("tooltip" => $elt_help)),
        array("value" => "", "required" => true));

//Add Botton in Form and show
$f->pop();
$f->addButton("btAdd", _T("Add"));
$f->display();

if (!servicesModuleEnabled()) {
    echo '<br/>';
    $f = new ValidatingForm(array("id" => "service"));
    $f->addButton("btApply", _T("Apply configuration"));
    $f->display();
}

?>
