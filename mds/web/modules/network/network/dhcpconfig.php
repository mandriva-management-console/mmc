<?
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: edithost.php 4272 2009-06-22 12:38:06Z cdelfosse $
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

require("modules/network/includes/network-xmlrpc.inc.php");
require("modules/network/includes/network.inc.php");
require("localSidebar.php");                                                                                                                
require("graph/navbar.inc.php"); 

$title = _T("DHCP launch configuration");
$p = new PageGenerator($title);
$sidemenu->forceActiveItem("services");
$p->setSideMenu($sidemenu);
$p->display();


/* Editing*/
if (isset($_POST["bconfirm"])) {
    $isAllInterfaces = $_POST["isallinterfaces"] ? "checked":"";
    $interfaces = $isAllInterfaces ? array() : $_POST["interfaces"];
    $msg="";
    if (!$isAllInterfaces){
	foreach ($interfaces as $i)
	    if (!strlen($i)){
		$msg = _T("Some of interfaces are not set");
		break;	
	    }
    }
    if ($msg)
	new NotifyWidgetFailure($msg);
    else {
	$result = setDhcpInterfaces($interfaces);
	if (!isXMLRPCError()) {
    	    if (empty($result)) {
        	new NotifyWidgetSuccess(_T("DHCP launch configuration successfully modified. You must restart the DHCP service"));
        	header("Location: " . urlStrRedirect("network/network/services"));
    	    } else {
        	$msg = _T("DHCP launch configuration can't be modified");
        	new NotifyWidgetFailure($msg);
    	    }
    	}
    }
} else {
    $config = getDhcpLaunchConfig();
    $interfaces = $config["interfaces"];
    $isAllInterfaces = count($interfaces) ? "" : "checked";
    if (!count($interfaces))
	$interfaces[]="";
}
$interfacesInfo = getInterfacesInfo();
$interfacesInfoDescr = array();

foreach ($interfacesInfo as $ii){
    $interfacesInfoDescr[] =  sprintf("%s [%s]","<b>" . $ii["interface"] . "</b> ",  $ii["subnet"]);
}


$f = new ValidatingForm();
$f->push(new Table());
$f->add(
    new TrFormElement(_T("Listen all interfaces", "network"),new CheckboxTpl("isallinterfaces")),
    array("value"=>$isAllInterfaces, "extraArg"=>'onclick="toggleVisibility(\'interfacesdiv\');"')
    );
$f->pop();
        
$interfacesDiv = new Div(array("id" => "interfacesdiv"));
$interfacesDiv->setVisibility(!$isAllInterfaces);
$f->push($interfacesDiv);

$f->add(new FormElement(_T("interfaces"),new MultipleInputTpl("interfaces",_T("Listened interfaces"))),
                        $interfaces);
$f->push(new Table());
$f->add(new TrFormElement(_T("Current system Ethernet interfaces"),new HiddenTpl("sysethinterfaces")),
        array("value"=> implode(", ",$interfacesInfoDescr)));

$f->pop();
                                    

$f->pop();

$f->addButton("bconfirm", _T("Confirm"));
//$f->pop();

$f->display();

?>
