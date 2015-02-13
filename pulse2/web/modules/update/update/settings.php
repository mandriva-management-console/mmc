<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2013 Mandriva, http://www.mandriva.com
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
<?php

require_once("modules/update/includes/xmlrpc.inc.php");
require_once("modules/update/includes/html.inc.php");


// ============================================================
// Receive form data
// ============================================================

// Setting enabled os_classes
if (isset($_POST['bconfirm'])){
    $enabled_os_classes_ids = array_keys($_POST['enable_os_classes']);
    enable_only_os_classes($enabled_os_classes_ids);
}

// Create update commands
if(isset($_POST['update'])){
    create_update_commands();
    new NotifyWidgetSuccess(sprintf(_T("The update deployments scheduled successfully.", "update")));
}

// ============================================================

// Including sidebar
include dirname(__FILE__) . '/sidebar.php';


if (! ($_GET['module'] == 'base' && $_GET['submod'] == 'computers')) {
    $p = new PageGenerator(_T("Update settings", 'update'));
    $p->setSideMenu($sidemenu);
    $p->display();
}

print '<h2><br/>' . _T('Enable update managment for:', 'update') . '</h2>';

$f = new ValidatingForm();
$f->push(new Table());


foreach($os_classes['data'] as $os_class){
    $f->add(
            new TrFormElement($os_class['name'], new CheckboxTpl('enable_os_classes[' . $os_class['id'] . ']')), array("value" => ($os_class['enabled'] == 1 ? 'checked' : ''))
    );
}

$f->addValidateButton("bconfirm");

$f->display();

print '<br/><hr/>';
$f3 = new PopupForm( _T('Updates deployment', 'update'));
$f3->addButtonwithconfirm("bconfirm", _T('Confirm Updates deployment'), _T('Force now', 'update'));
$hidden = new HiddenTpl("update");
$f3->add($hidden, array("value" => "update", "hide" => True));
$f3->display();

?>
