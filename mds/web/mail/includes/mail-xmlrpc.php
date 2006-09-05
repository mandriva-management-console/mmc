<?php
/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id$
 *
 * This file is part of LMC.
 *
 * LMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * LMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


function cleanup_arr($array) {
    $res = array();
    foreach ($array as $item) {
        if ((preg_match("/^([0-9a-zA-Z@.]){1,}$/",$item))&&(array_search($item,$res)===False)) {
            $res[] = $item;
        }
    }

    return $res;
}

function changeMail($login,$mail) {
  return xmlCall("mail.changeMail",array($login,$mail));
}

function changeMailEnable($login,$boolean) {
  return xmlCall("mail.changeMailEnable",array($login,$boolean));
}

function changeMaildrop($login,$droplist) {
  $arr = cleanup_arr($droplist);
  if (count($arr)==0) {
     return; //if no maildrop specified
  }
  return xmlCall("mail.changeMaildrop",array($login,$arr));
}

function changeMailalias($login,$aliaslist) {
  return xmlCall("mail.changeMailalias",array($login,cleanup_arr($aliaslist)));
}

function removeMail($login) {
    return xmlCall("mail.removeMail",array($login));
}

function hasMailObjectClass($login) {
    return xmlCall("mail.hasMailObjectClass",array($login));
}

?>