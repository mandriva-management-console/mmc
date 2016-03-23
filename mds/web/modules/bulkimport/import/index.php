<?php
/**
 * (c) 2009 Open Systems Specilists - Glen Ogilvie
 *
 * $Id$
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

require ("modules/bulkimport/includes/importUsers.php");
require ("modules/base/users/localSidebar.php");
require ("graph/navbar.inc.php");
require ("modules/base/includes/users.inc.php");

// controller
$stage = key_exists("stage", $_REQUEST) ? $_REQUEST["stage"] : false;
if (isset ($_REQUEST["cancelbutton"])) $stage = false;

switch ($stage) {
case "import":
    $p = new PageGenerator(_T("CSV import result", "bulkimport"));
    $p->setSideMenu($sidemenu);
    $p->display();
    if (isset ($_REQUEST["importbutton"]) && bulkImport());
    if (isset ($_REQUEST["deletebutton"]) && bulkDelete());
    if (isset ($_REQUEST["modifybutton"]) && bulkModify());
    displayResultsTable();
    break;

case "preimportpaging":
case "preimport":
    if  (key_exists("csvfile",$_FILES) || isset ($_SESSION['importusers'])) {
        $p = new PageGenerator(_T("CSV data to import", "bulkimport"));
        $p->setSideMenu($sidemenu);
        $p->display();
        displayPreImportTable();
        break;
    }

default:
    unset ($_SESSION['importusers']);
    $p = new PageGenerator(_T("Bulk account modification from CSV file", "bulkimport"));
    $p->setSideMenu($sidemenu);
    $p->display();
    uploadFormView();
}

function getImportObject() {
    if (isset($_SESSION['importusers']))
        return unserialize($_SESSION['importusers']);
    else {
        uploadFormView();
        return False;
    }
}

function bulkImport() {
    $importusers = getImportObject();
    if (!$importusers) return False;
    try {
        $importusers->import();
        $_SESSION['importusers'] = serialize($importusers);
        return True;
    }
    catch (Exception $e) {
        print "<b>" . $e->getMessage() . "</b>";
        return False;
    }
}

function bulkDelete() {
    $importusers = getImportObject();
    if (!$importusers ) return False;
    $importusers->delete();
    $_SESSION['importusers'] = serialize($importusers);
    return True;
}

function bulkModify() {
    $importusers = getImportObject();
    if (!$importusers ) return False;
    $importusers->modify();
    $_SESSION['importusers'] = serialize($importusers);
    return True;
}

function displayResultsTable() {
    $importusers = getImportObject();
    if (!$importusers ) return False;
    $l = $importusers->getListInfos("&amp;stage=import");
    $l->setName(_T("CSV import results", "bulkimport"));
    /* Display the widget */
    $l->display();
    return;
}

function displayPreImportTable() {
?>
    <div>
<?php
    if (file_exists($_FILES["csvfile"]["tmp_name"])) {
        $fh = fopen($_FILES["csvfile"]["tmp_name"], "r");
        $csvkeys = fgetcsv_compat($fh);
        try {
            $importusers = new ImportUsers($csvkeys);
        } catch (Exception $e) {
            print "<b>" . $e->getMessage() . "</b>";
            uploadFormView();
            return;
        }
        try {
            $importusers->verifyImportHeaders();
        } catch (Exception $e) {
            print "<b>" . $e->getMessage() . "</b>";
        }
        while ($data = fgetcsv_compat($fh)) {
            $importusers->adduser($data);
        }
    }
    else {
        if (isset ($_SESSION['importusers']))
            $importusers = unserialize($_SESSION['importusers']);
    }
    if ($importusers->bigList())
        echo "<p><b>" . _T("Warning: This is a big list so could take a long time. You may need to increase the timeout settings on your web server and browser", "bulkimport") . "</b></p>";
    $l = $importusers->getListInfos();
    $l->setName(_T("CSV Import", "bulkimport"));
    /* Display the widget */
    $l->display();
?>
<form id="bulkimport" enctype="multipart/form-data" method="post">
<?php if ($importusers->allowImport()) { ?>
<input name="importbutton" type="submit" class="btnPrimary" value="<?php echo  _T("Import", "bulkimport"); ?>" />
<?php } else { ?>
<input name="importbutton" type="submit" class="btnDisabled" value="<?php echo  _T("Import", "bulkimport"); ?>" disabled="disabled" />
<?php

};
if ($importusers->allowModify()) {
?>
<input name="modifybutton" type="submit" class="btnPrimary" value="<?php echo  _T("Modify", "bulkimport"); ?>" />
<?php } else { ?>
<input name="modifybutton" type="submit" class="btnDisabled" value="<?php echo  _T("Modify", "bulkimport"); ?>" disabled="disabled" />
<?php

};
if ($importusers->allowDelete()) {
?>
<input name="deletebutton" type="submit" class="btnPrimary" value="<?php echo  _T("Delete", "bulkimport"); ?>" />
<?php } else { ?>
<input name="deletebutton" type="submit" class="btnDisabled" value="<?php echo  _T("Delete", "bulkimport"); ?>" disabled="disabled" />
<?php }; ?>
<input name="cancelbutton" type="submit" class="btnSecondary" value="<?php echo  _("Cancel"); ?>" />
<input type="hidden" name="stage" value="import"/>
</form>

	</div>
<?php
     $_SESSION['importusers'] = serialize($importusers);
}

function uploadFormView() {
?>

<style type="text/css">
.center {
    text-align: center;
}
.attributelist li {
	display: inline;
	float: left;
	padding: 3px;
	margin: 0px;
	list-style: none;
}
.attributelist ul {
    margin: 0px;
    padding: 0px;
}
</style>
<form id="bulkimport" enctype="multipart/form-data" method="post">
    <div class="formblock" style="background-color: #F4F4F4;">
        <input type="hidden" name="stage" value="preimport"/>
        <table cellspacing="0">
<?php
    $test = new TrFormElement(_T("CSV file: ", "bulkimport"), new FileTpl("csvfile"));
    $test->display(null);
?>

        </table>
        <p class="center"><input name="next" type="submit" class="btnPrimary" value="<?php echo  _T("Load CSV", "bulkimport"); ?>" /></p>
        <table cellspacing="0">
            <tr>
                <td>
                    <p><strong><?=_T("Description:", "bulkimport")?></strong></p>
                    <?=_T("You can import, modify and delete user accounts using a CSV file.", "bulkimport")?>
                    <ol>
                        <li><?=_T("If the users in the CSV file don't exist, you can import them.", "bulkimport")?></li>
                        <li><?=_T("If the users in the CSV file exist, you can modify them or delete them.", "bulkimport")?></li>
                    </ol>
                    <strong><?=_T("CSV Header requirements:", "bulkimport")?></strong>
                    <ul>
                        <li><?=_T("Required attribute:", "bulkimport")?> "login"</li>
                        <li><?=_T("Required for import:", "bulkimport")?> "password", "firstname", "surname"</li>
                        <li><?=_T("Additional headers can be set and must match the attribute name in LDAP, for example:", "bulkimport")?> "login", "password", "firstname", "surname", "primaryGroup", "mail"</li>
                    </ul>
                    <strong><?=_T("CSV Formatting:", "bulkimport")?></strong>
                    <ul>
                        <li><?=_T("Delimiter:", "bulkimport")?> ,</li>
                        <li><?=_T("Wrapper:", "bulkimport")?> &quot;</li>
                        <li><?=_T("Escape character:", "bulkimport")?> \</li>
                    </ul>
                    <strong><?=_T("Special attributes", "bulkimport")?></strong> ('yes' <?=_T("or", "bulkimport")?> 'no')<strong>:</strong>
                    <ul>
                        <li>createhomedir ('yes' <?=_T("by default", "bulkimport")?>)</li>
                        <li>files ('yes' <?=_T("by default for delete operation, users home directory will be removed.", "bulkimport")?>)</li>
                    </ul>
                    <strong><?=_T("Defaults:", "bulkimport")?></strong>
                    <ul>
                        <li>homedir (<?=_T("the default home directory path", "bulkimport")?>)</li>
                        <li>primaryGroup (<?=_T("the default primary group", "bulkimport")?>)</li>
                    </ul>
                    <strong><?=_T("Supported attributes:", "bulkimport")?></strong>
                    <ul class="attributelist">
                    <?php foreach (importusers::getValidAttributes() as $attribute) echo "<li>".$attribute."</li>"; ?>
                    </ul>
                </td>
            </tr>
        </table>
    </div>
</form>

<?php
}

/*
 * Function that works with php 5.2 and 5.3.
 */

function fgetcsv_compat($fh, $length='1000',$delimiter = ',' , $enclosure = '"' , $escape = '\\') {
    if (strpos('5.3', phpversion()) !== false) {
        return fgetcsv($fh, $length ,$delimiter , $enclosure , $escape );
    } else {
        return fgetcsv($fh, $length ,$delimiter , $enclosure );
    }
}
?>
