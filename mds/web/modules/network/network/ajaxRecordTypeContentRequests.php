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
require("../../../includes/PageGenerator.php");

require("../includes/network.inc.php");
require("../includes/network2.inc.php");

$_GET["module"]="network";



function makeResponse($id, $value){
    echo sprintf("id=%s&value=%s", $id, $value);
}

function makeContentResponse($id, $trtype, $zone){
    makeResponse($id, "");
    $type = ($trtype == _T("custom")) ? "custom" : $trtype;
    $content = "dnsrecords/".strtolower($type).".php";
    require_once($content);
    $RecordClass = $type . "Record";
    $obj = new $RecordClass(array("zone"=>$zone, "type"=>strtolower($type)));
    $obj->display();

}

switch($_GET['request']) {
    case 'getRecordTypeContent':
        list($type, $zone) = split(" ", $_GET['params']);
        $content = "dnsrecords/".strtolower($type).".php";
        makeContentResponse("getRecordTypeContent",$type, $zone);
        break;
}

?>
