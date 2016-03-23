<?php

/**
 * (c) 2014 Mandriva, http://www.mandriva.com
 *
 * This file is part of Management Console.
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

require_once("modules/base/includes/users-xmlrpc.inc.php");
require_once("radius-xmlrpc.php");

/**
 * Form on user edit page
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _radius_baseEdit($FH, $mode) {

    // default value
    $show = false;

    if ($mode == 'edit' &&
        hasRadiusObjectClass($FH->getArrayOrPostValue("uid"))) {
        $show = true;
    }
    else {
        if ($FH->getValue("showradius") == "on")
            $show = true;
    }

    $f = new DivForModule(_T("Radius management","radius"), "#E0FFDF");

    $f->push(new Table());
    $f->add(
        new TrFormElement(_T("Enable Radius management", "radius"),
            new CheckboxTpl("showradius")),
            array("value" => $show ? "checked" : "",
                "extraArg"=>'onclick="toggleVisibility(\'radiusdiv\');"')
        );
    $f->pop();

    $radiusdiv = new Div(array("id" => "radiusdiv"));
    $radiusdiv->setVisibility($show);
    $f->push($radiusdiv);

    $radiusCallingStationId = $FH->getArrayOrPostValue('radiusCallingStationId', 'array');
    $f->add(new TrFormElement('',
        new MultipleInputTpl("radiusCallingStationId", _T("Calling Station ID", "radius"))),
        $radiusCallingStationId
    );

    $f->pop();

    return $f;

}

/**
 * Function called before changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _radius_verifInfo($FH, $mode) {
    return 0;
}

/**
 * Function called for changing user attributes
 * @param $FH FormHandler of the page
 * @param $mode add or edit mode
 */
function _radius_changeUser($FH, $mode) {

    global $result;
    $uid = $FH->getPostValue('uid');

    if ($FH->getPostValue("showradius")) {
        addRadiusObjectClass($uid);
        if ($FH->isUpdated("radiusCallingStationId")) {
            changeUserAttributes($uid, "radiusCallingStationId", $FH->getValue("radiusCallingStationId"), false);
            $result .= _T("Radius attributes updated.", "radius") . "<br />";
        }
    }
    else {
        if ($mode == 'edit' && hasRadiusObjectClass($uid)) {
            delRadiusObjectClass($FH->getPostValue('uid'));
            $result .= _T("Radius attributes deleted.", "radius") . "<br />";
        }
    }

    return 0;
}

?>
