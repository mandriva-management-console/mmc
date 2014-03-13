<?php
/**
 * (c) 2014 Zentyal, http://www.zentyal.com
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
 *
 * Author(s):
 *   Julien Kerihuel <jkerihuel@zentyal.com>
 */

if (isset($_POST["bdel"])) {

   header("Location: " . urlStrRedirect("samba4/domaincontroller/purge"));
   exit;

}  else if (isset($_POST["bback"])) {

   header("Location: " . urlStrRedirect("samba4/domaincontroller/index"));

} else if (isset($_POST["bpurge"])) {
   $text = '
   <div style="padding-top: 15px; float: left; text-align: center"><img src="img/common/icn_alert.gif"></div>
   <div style="margin-left: 60px">
   	<div class="alert">
	     <p><b>This action non-reversible</b><br/><br/>
	     If you proceed, <b>ANY</b> information related to the domain, its configuration or data will be purged from the system.</p>
	</div>
   </div>
   ';

   $p = new ValidatingForm();
   $p->addSummary(_T($text));
   $p->addValidateButton("bdel");
   $p->addCancelButton("bback");
   $p->display();

} else {
  $f = new Form(array("class" => "samba4"));
  $f->addExpertButton("bpurge", _T("Reset Domain"));
  $f->display();
}

?>


