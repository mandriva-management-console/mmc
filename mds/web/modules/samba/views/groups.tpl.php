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
?>

    <tr><td width="40%" style="text-align: right; vertical-align: top;"><?php echo  _("Groups"); ?> </td><td>
        <select multiple size="10" class="list" name="<?php echo  $autocomplete; ?>groupsselected[]" id="auto<?= $autocomplete; ?>select">
            <?php
            $sorted = $tpl_groups;

            //sorting group
            sort($sorted);
            foreach ($sorted as $group)
                {
                echo "<option value=\"".$group."\">".$group."</option>\n";
            }
            ?>
        </select>

    <script type="text/javascript">

    <!--

        function auto<?php echo  $autocomplete; ?>() {
            this.select = document.getElementById('auto<?php echo  $autocomplete; ?>select');
            this.groups = new Array();
        <?php
            foreach (get_groups($error) as $group)
            {
                echo "this.groups.push('$group[0]');\n";
            }
        ?>
        }

        auto<?php echo  $autocomplete; ?>.prototype.validOnEnter = function(field,event) {
            if (event.keyCode==13) {
                return false;
            }
            return true;
        }

        //add an element in selectbox
        auto<?php echo  $autocomplete; ?>.prototype.addElt = function(elt) {
            if (this.eltInArr(elt, this.groups)) {
                this.addEltInSelectBox(elt);
                $('auto<?php echo  $autocomplete; ?>').value = '';

            }
            else {
                window.alert("<?php echo  _T("This group doesn't exist"); ?>");
            }
        }

        //verify if an element is in an array
        auto<?php echo  $autocomplete; ?>.prototype.eltInArr = function(elt,array) {
            for(var i =0; i<array.length; i++) {
                if (array[i] == elt) return true;
            }
            return false;
        }

        auto<?php echo  $autocomplete; ?>.prototype.addEltInSelectBox = function(elt) {
            var tmp = new Array();
            var len = this.select.options.length;
            for(var i =0; i<len; i++) {
                    tmp.push(this.select.options[0].value);
                    //window.alert(document.getElementById('select').options[0].value);
                    this.select.options[0] = null;

            }
            if (!this.eltInArr(elt,tmp)) {
                tmp.push(elt);
            }

            tmp.sort();

            for(var i = 0; i<tmp.length; i++) {
                this.select.options[i] = new Option(tmp[i],tmp[i]);
            }

        }

        auto<?php echo  $autocomplete; ?>.prototype.delEltInSelectBox = function() {
            var len = this.select.options.length;
            for(var i =len-1; i>=0; i--) {
                if (this.select.options[i].selected) {
                    this.select.options[i] = null;
                }
            }
        }

       auto<?php echo  $autocomplete; ?>.prototype.selectAll = function() {
            var len = this.select.options.length;
            for(var i = 0 ; i<len; i++) {
                this.select.options[i].selected = true;
            }
       }
 
       auto<?php echo  $autocomplete; ?>Obj = new auto<?= $autocomplete; ?>();

    -->

    </script>

    <input name="bdel<?php echo  $autocomplete; ?>" type="submit" class="btnPrimary" value="<?= _("Delete"); ?>" onClick="auto<?= $autocomplete; ?>Obj.delEltInSelectBox(); return false;"/>

    </td>
    </tr>
    <tr><td style="text-align: right;"><?php echo  _T("Add a new group"); ?></td><td>

    <input type="text" id="auto<?php echo  $autocomplete; ?>" name="auto<?= $autocomplete; ?>" size="23" onkeypress="return auto<?= $autocomplete; ?>Obj.validOnEnter(this,event);" />
    <div id="auto<?php echo  $autocomplete; ?>_choices" class="autocomplete">
        <ul>
            <li></li>
            <li></li>
        </ul>
    </div>
    <input name="badd<?php echo  $autocomplete; ?>" type="submit" class="btnPrimary" value="<?= _("Add"); ?>" onClick="auto<?= $autocomplete; ?>Obj.addElt($F('auto<?= $autocomplete; ?>')); return false;"/>
    </td></tr>

    <script type="text/javascript">
    <!--
        new Ajax.Autocompleter('auto<?php echo  $autocomplete; ?>','auto<?= $autocomplete; ?>_choices','modules/base/users/ajaxAutocompleteGroup.php', {paramName: "value"});
    -->
    </script>
