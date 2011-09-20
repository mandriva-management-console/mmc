<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2010 Mandriva, http://www.mandriva.com
 * (c) 2011 http://www.osinit.ru
 *
 * $Id:
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


require("../../../includes/config.inc.php");
require("../../../includes/i18n.inc.php");
require("../../../includes/acl.inc.php");
require("../../../includes/session.inc.php");
require("../../../modules/network/includes/network-xmlrpc.inc.php");
require("../../../includes/PageGenerator.php");
require("../../../modules/network/includes/network2.inc.php");


$filter = $_GET["filter"];
$zone = $_GET["zone"];
$sortby = $_GET["sortby"];
$asc = $_GET["asc"];
$reverse = $_GET["reverse"];

if (isset($_GET["sortby"])){
    $_SESSION["network"]["sortby"] = $_GET["sortby"];
    $_SESSION["network"]["asc"] = $_GET["asc"];
} else {
    if (isset($_SESSION["network"])){
	$sortby = $_SESSION["network"]["sortby"];
	$asc = $_SESSION["network"]["asc"];
    }
}


if ($asc == "") 
    $asc="1";
    

$addresses = array();

$curzone = $zone;
if ($reverse){
    $rzone = getReverseZone($zone);
    if (count($rzone))
        $curzone = $rzone[0];
}
                


$records = getZoneRecords($curzone, "");
$soa = getSOARecord($curzone);
$serial = $soa["serial"];

if ($filter){
    $tmprecords = array();
    foreach($records as $r){
    	if (strpos($r["hostname"], $filter) !== False || strpos($r["type"], $filter) !== False)
    	    $tmprecords[] = $r;
    }
    $records = $tmprecords;
}


function getRecordValueDescription($zone, $type, $value){
    $typeToLoad = in_array(strtoupper($type),supportedRecordsTypes()) ? strtolower($type) : "custom"; 
    $RecordClass = $typeToLoad . "Record";
    require_once("../../../modules/network/network/dnsrecords/" . $typeToLoad . ".php");
    $r = new $RecordClass(array("zone" => $zone));
    $r->initValuesFromString($value);
    return $r->valuesToDescription();
}

function compare_hostname_asc($a, $b){ 
    return strnatcmp($a["hostname"], $b["hostname"]); 
}

function compare_hostname_desc($a, $b){ 
    return strnatcmp($b["hostname"], $a["hostname"]); 
}

function compare_type_asc($a, $b){ 
    return strnatcmp($a["type"], $b["type"]); 
}

function compare_type_desc($a, $b){ 
    return strnatcmp($b["type"], $a["type"]); 
}

$func = "compare_hostname_asc";
if ($sortby!=""){
    $func = "compare_" . $sortby . "_" . (($asc) ? "asc" : "desc") ;   
}

usort($records, $func); 

global $conf;
$maxperpage=$conf["global"]["maxperpage"];
$start = isset($_GET["start"])?$_GET["start"]:0;
$itemsatpage = count($records)-$start>$maxperpage?$maxperpage:count($records)-$start;
$recordsatpage = array_slice($records,$start,$itemsatpage);




$params = array();
$hosts = array();
$types = array();
$values = array();
$actionsDel = array();
$actionsMod = array();
$delAction = new ActionPopupItem(_T("Delete record", "network"),"deleterecord","delete","", "network", "network");
$emptyAction = new EmptyActionItem();
$editAction = new ActionItem(_T("Edit record", "network"),"editrecord","edit","", "network", "network");

$_GET["module"]="network";
foreach($recordsatpage as $r) {
    if ($filter && strpos($r["hostname"], $filter) === False && strpos($r["type"], $filter) === False)
	continue;

    $hosts[] = $r["hostname"];//rtrim($r[1],".");
    $types[] = $r["type"];
    $values[] = getRecordValueDescription($zone, $r["type"],$r["value"]);
    $params[] = array("zone"=>$zone, "reverse"=>$reverse, "id"=>$r["id"], "serial"=>$serial);
    $actionsMod[] = $editAction;
    if ((($r["hostname"] === $curzone . ".") && ($r["type"] === "TXT")) || ($r["type"] == "SOA"))
	$actionsDel[] = $emptyAction;
    else
	$actionsDel[] = $delAction;

}


//print_r($values);
$typeAsc = ($sortby == "type") ? intval(!$asc) : "1";
$hostnameAsc= ($sortby == "hostname") ? intval(!$asc) : "1";

$typeUrl = urlStr("network/network/zonerecords",array("zone"=>$zone, "reverse"=>$reverse, "sortby"=>"type", "asc"=>$typeAsc));
$hostnameUrl = urlStr("network/network/zonerecords",array("zone"=>$zone, "reverse"=>$reverse, "sortby"=>"hostname", "asc"=>$hostnameAsc));

$n = new OptimizedListInfos($hosts, "<a href='". $hostnameUrl. "'>" . _T("Host name", "network"). "</a>");
$n->setTableHeaderPadding(1);
$n->setNavBar(new AjaxNavBar(count($records), $filter));
$n->setItemCount(count($records));
$n->start = 0;
$n->end = count($recordsatpage)-1;

$n->addExtraInfo($types, "<a href='". $typeUrl. "'>" . _T("Record type", "network")). "</a>";
$n->addExtraInfo($values, _T("Record parameters", "network"));
$n->setName(_T("Host", "network"));
$n->setParamInfo($params);
$n->disableFirstColumnActionLink();
$n->addActionItemArray($actionsMod);
$n->addActionItemArray($actionsDel);

$n->display();
?>
