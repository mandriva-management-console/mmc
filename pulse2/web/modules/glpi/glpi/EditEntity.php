<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com
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
 
require("modules/base/computers/localSidebar.php");
require("graph/navbar.inc.php");


// recupere id
$ID = intval(@max($_GET['id'],$_POST['id']));
$NAME = $_GET['name'];
$COMMENT = $_GET['comment'];

//traitement si confirm
if (isset($_POST['bconfirm'])){
// Array
// (
//     [old_name] => MY CORPORATE
//     [name] => MY CORPORATE1
//     [id] => 1
//     [bconfirm] => Confirm
// )

    $cfg = array(
        'old_name' => $_POST['old_name'],
        'name' => $_POST['name'],
        'id'  =>  $_POST['id'],
        'comment'  =>  $_POST['comment']
    );
   
    if ($ID) {
    updateEntities($cfg['id'],$cfg['name'],$cfg['old_name'],$cfg['comment']);
    header('location: main.php?module=base&submod=computers&action=entityList' );
    }
}
;

$p = new PageGenerator(_T("Edit entity", "glpi"));
$p->setSideMenu($sidemenu);
$p->display();


// // display an edit config form 
// $f = new ValidatingForm();
// $f->push(new Table());
// 
// // Profile name
// // valeur de entity
// $f->add(
//     new TrFormElement(_T('Name','glpi'), new InputTpl('Entity')),
//     array("value" => "entity","required" => True)
// );
// // Add Share button
// 
// $addShareBtn->setClass('btnPrimary');
// // $f->add(
// //     new TrFormElement('', $addShareBtn),
// //     array()
// // );
// //  $f->add(new HiddenTpl("id"), array("value" => $ID, "hide" => True));
// // // If BackupProfile id is transmitten, we write it into the form
// // if ($ID) {
// //     $f->add(new HiddenTpl("id"), array("value" => $ID, "hide" => True));
// // }
// // elseif (isset($profile['id']))
// //     $f->add(new HiddenTpl("id"), array("value" => $profile['id'], "hide" => True));
// 
// 
// $f->pop();
// $f->addValidateButton("bconfirm");
// $f->display();
// 

$f = new ValidatingForm();
$f->push(new Table());

$f->add(
    new TrFormElement(_T('Name','glpi'), new InputTpl('name')),
    array("value" => $NAME,"required" => True)
);
$f->add(
    new TrFormElement(_T('Comment','glpi'), new InputTpl('comment')),
    array("value" => $COMMENT,"required" => True)
);
if ($ID) {
  $f->add(new HiddenTpl("id"), array("value" => $ID, "hide" => True));
}
$f->pop();
$f->addValidateButton("bconfirm");
$f->display();
?>
