<script type="text/javascript" src="modules/network/includes/ajaxRecordHandler.js"></script>
<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: edithost.php 172 2008-12-10 15:13:01Z cdelfosse $
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

require("modules/network/includes/network.inc.php");
require("modules/network/includes/network2.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

$DEBUG = 0;

$zone = $_GET["zone"];
$reverse = $_GET["reverse"];
$recordId = $_GET["id"];

$curzone = $zone;
if ($reverse){
    $rzone = getReverseZone($zone);
    if (count($rzone))
        $curzone = $rzone[0];
}


$soa = getSOARecord($curzone);

if ($_GET["action"] == "editrecord"){
    if ($_GET["serial"] != $soa["serial"]){
        new NotifyWidgetFailure(_T("Zone data was updated. Selected record may be incorrect."));
        header("Location: " . urlStrRedirect("network/network/zonerecords", array("zone" => $zone, "reverse" => $reverse)));
        exit;
    }
}

$title="";
if ($reverse)
    $title = ($_GET["action"] == "addrecord") ?
		    sprintf(_T("Add record to reverse zone for zone %s"), $zone) :
		    sprintf(_T("Edit record of reverse zone for zone %s"), $zone) ;
else
    $title = ($_GET["action"] == "addrecord") ?
		    sprintf(_T("Add record to zone %s"), $zone) :
		    sprintf(_T("Edit record of zone %s"), $zone) ;

$p = new PageGenerator($title);
$sidemenu->forceActiveItem("index");
$p->setSideMenu($sidemenu);
$p->display();


if ($_GET["action"] == "addrecord")
    $recordTypes = supportedRecordsTypes($reverse ? "reverse" : "direct");
else
    $recordTypes = supportedRecordsTypes("all");

$typeIndex = isset($_POST["recordtype"])?$_POST["recordtype"]:"0";

global $error;

$recordUiContainers = NULL;


/* Adding a new record */
if (isset($_POST["badd"])) {
    $typeIndex = $_POST["recordtype"];
    $type = strtolower($recordTypes[$typeIndex]);
    if ($type == _T("custom"))
	$type = "custom";
    $typeToLoad = (in_array(strtoupper($type),$recordTypes))? strtolower($type) : "custom";

    require_once("dnsrecords/".$typeToLoad.".php");
    $RecordClass = $typeToLoad . "Record";
    $record = new $RecordClass(array("zone"=>$zone,"type" => $type));
    if ($DEBUG)
        print_r($_POST);
    $record->initValuesFromArray($_POST);
    $errors = $record->check();
    if ($errors) {
	if ($DEBUG) echo "checking - false";
	$recordUiContainers = $record->createUiContainers();
	new NotifyWidgetFailure($errors);
    } else {
	if ($DEBUG) echo "checking - ok";
	$value = $record->valuesToString();
	if ($typeToLoad == "custom")
	    $type = $record->typeName();
	if ($DEBUG)
	    echo sprintf("zone:%s type:%s hn:%s val:%s",$zone, $type, $record->hostname(), $value);
	else {
	    addRecord($curzone, $type, $record->hostname(), $value);
	    header("Location: " . urlStrRedirect("network/network/zonerecords", array("zone" => $zone, "reverse" => $reverse)));
        exit;
	}
    }
}

/* Editing a record */
if (isset($_POST["bedit"])) {
    $type = $_POST["recordtype"];

    $typeToLoad = (in_array(strtoupper($type),$recordTypes))? strtolower($type) : "custom";
    require_once("dnsrecords/".$typeToLoad.".php");
    $RecordClass = $typeToLoad . "Record";

    $record = new $RecordClass(array("zone"=>$zone,"type" => strtolower($type)));

    $record->initValuesFromArray($_POST);
    $errors = $record->check();
    if ($errors) {
    	if ($DEBUG) echo "checking - false";
	    $recordUiContainers = $record->createUiContainers(true);
    	new NotifyWidgetFailure($errors);
    } else {
	    if ($DEBUG) echo "checking - ok";
    	$value = $record->valuesToString();
	if ($DEBUG)
	    echo sprintf("zone:%s id:%s hn:%s val:%s",$zone, $recordId, $record->hostname(), $value);
	else {
	    modifyRecordById($curzone, $recordId, $record->hostname(), $value);
	    header("Location: " . urlStrRedirect("network/network/zonerecords", array("zone" => $zone, "reverse" => $reverse)));
        exit;
	}
    }

}


if ($_GET["action"] == "editrecord" && !isset($_POST["bedit"])) {
    $record = getZoneRecordById($curzone, $recordId);
    if ($record){
	$hostname = $record["hostname"];
	$type = $record["type"];
	$value = $record["value"];
	$typeToLoad = (in_array(strtoupper($type),$recordTypes))? strtolower($type) : "custom";
        require_once("dnsrecords/".$typeToLoad.".php");

	$RecordClass = $typeToLoad . "Record";
	$r = new $RecordClass(array("zone" => $zone,"type" => strtolower($type),"hostname" => $hostname));
	$r->initValuesFromString($value);
	$recordUiContainers = $r->createUiContainers(true);

    }
}

$f = new ValidatingForm();
$f->push(new Table());

if ($_GET["action"] == "addrecord"){
    $typeComboBox = new ExtendedSelectItem("recordtype","onRecordTypeChanged");
    $typeComboBox->setJsFuncParams(array("'".$zone."'"));
    $typeComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\"");
    $typeComboBox->setElements(array_values($recordTypes));
    $typeComboBox->setElementsVal(array_keys($recordTypes));
    $f->add(
    	    new TrFormElement(_T("Record type"),$typeComboBox),
    	    array("value"=>$typeIndex)
        );
} else {

    $f->add(
    	    new TrFormElement(_T("Record type"),new HiddenTpl("recordtype")),
    	    array("value"=>$type)
        );
}


$f->pop();


$typeContentDiv = new Div(array("id" => "typecontentdiv"));
$f->push($typeContentDiv);


if (isset($recordUiContainers))
    foreach ($recordUiContainers as $cnt){
	if ($cnt[2]){
	    $f->add($cnt[0][0],$cnt[0][1]);
	    continue;
	}
	$f->push($cnt[0]);
	for ($i=0; $i<$cnt[1];$i++)
	    $f->pop();
    }



$f->pop();

/*
$f->push(new Table());
$f->add(
    new FormElement("note",new HiddenTpl("note")),
    array("value"=>"<b>" . _T("Note"). ":</b> " .
    _T("All domain and host names should be setted as relative or fully qualified (FQDN), e.g.") .
    " <b>host</b> (" ._T("for FQDN"). " <b>host.".$curzone.".</b> ), <b>host.customzone.com.</b>"

    )
);
$f->pop();
*/

if ($_GET["action"] == "addrecord") {
    $f->addButton("badd", _("Create"));
} else {
    $f->addValidateButton("bedit");
}
$f->display();


if (!isset($_POST["badd"]) &&  ($_GET["action"] != "editrecord")){
    echo '<script type="text/javascript">onRecordTypeChanged(\''.$zone.'\');</script>';
}

?>
