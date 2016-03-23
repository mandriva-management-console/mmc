<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
 *
 * $Id: publicFunc.php 87 2008-03-04 08:59:44Z cedric $
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class MultipleMailInputTpl extends MultipleInputTpl {

    function MultipleMailInputTpl($name, $desc='', $new=false) {
        parent::MultipleInputTpl($name, $desc, $new);
        $this->regexp = '/^([A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+){0,1}$/';
    }

}

class QuotaTpl extends InputTpl {

    function QuotaTpl($name, $regexp="/.*/") {
        $this->InputTpl($name, $regexp);
    }

    function display($arrParam = array()) {
        if ($arrParam["value"] === "0") {
            $unlimited = "true";
            $old_value = "on";
        } else {
            $unlimited = "false";
            $old_value = "";
        }
        parent::display($arrParam);
        print "&nbsp;" . _T("Unlimited quota", "mail") . '
        <input type="hidden" name="old_unlimitedquota" value="'.$old_value.'" />
        <input type="checkbox" id="unlimitedquota" name="unlimitedquota" onclick="unlimitedquotaclick();"';
        if ($unlimited == "true")
            print ' checked="checked"';
        print '/>
        <script type="text/javascript">
            jQuery("#mailuserquota, #domainquota").prop("disabled", ' . $unlimited . ');
            function unlimitedquotaclick() {
                var disabled = !jQuery("#mailuserquota, #domainquota").prop("disabled");
                jQuery("#mailuserquota, #domainquota").prop("disabled", disabled);
                if (disabled)
                    jQuery("#mailuserquota, #domainquota").val("0");
                else
                    jQuery("#mailuserquota, #domainquota").val("");
            }
        </script>';
    }

    function displayRo($arrParam) {
        if ($arrParam["value"] === "0") print _T("Unlimited quota", "mail");
        else print $arrParam["value"];
    }

}

?>
