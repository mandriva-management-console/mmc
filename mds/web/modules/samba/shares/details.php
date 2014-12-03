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

require("modules/samba/includes/shares.inc.php");
require("modules/base/includes/users.inc.php");
require("modules/base/includes/groups.inc.php");
require("modules/samba/mainSidebar.php");
require("graph/navbar.inc.php");

if (isset($_POST["bcreate"])) {
    $shareName = $_POST["shareName"];
    $sharePath = $_POST["sharePath"];
    $shareDesc = $_POST["shareDesc"];
    $adminGroups = $_POST["admingroupsselected"];
    $customParameters = $_POST["customparameters"];
    if ($_POST["hasAv"])
        $av = 1;
    else
        $av = 0;
    if ($_POST["browseable"])
        $browseable = 1;
    else
        $browseable = 0;

    if (!(preg_match("/^[a-zA-Z][a-zA-Z0-9.]*$/", $shareName))) {
    	new NotifyWidgetFailure(_T("Invalid share name"));
    }
    else {
        $add = True;
        if (strlen($sharePath)) {
            if (!isAuthorizedSharePath($sharePath)) {
                new NotifyWidgetFailure(_T("The share path is not authorized by configuration"));
                $add = False;
            }
        }
        if ($add) {

            if (isset($_POST["permAll"]) and $_POST["permAll"] == "on") {
                $perms = array("rwx" => array("@all"));
            }
            else {
                $perms = array();
                foreach($_POST['perms_read'] as $user => $check) {
                    $perm = 'r';
                    if (array_key_exists($user, $_POST['perms_write']))
                        $perm .= 'w';
                    // alway add 'x' right
                    $perm .= 'x';

                    if (!isset($perms[$perm]))
                        $perms[$perm] = array();
                    $perms[$perm][] = $user;
                }
            }

            $params = array($shareName, $sharePath, $shareDesc, $perms, $adminGroups, $browseable, $av, $customParameters);
            add_share($params);
            if (!isXMLRPCError()) {
                new NotifyWidgetSuccess(sprintf(_T("Share %s successfully added"), $shareName));
                header("Location: " . urlStrRedirect("samba/shares/index" ));
                exit;
            }
        }
    }
}

if (isset($_POST["bmodify"]))
{
    $share = $_GET["share"];
    $shareName = $_POST["shareName"];
    $sharePath = $_POST["sharePath"];
    $shareDesc = $_POST["shareDesc"];
    if (isset($_POST["admingroupsselected"]))
        $adminGroups = $_POST["admingroupsselected"];
    else
        $adminGroups = array();
    $customParameters = $_POST["customparameters"];
    if (isset($_POST["hasAv"]))
        $av = 1;
    else
        $av = 0;
    if (isset($_POST["browseable"]))
        $browseable = 1;
    else
        $browseable = 0;

    if (isset($_POST["permAll"]) and $_POST["permAll"] == "on") {
        $perms = array("rwx" => array("@all"));
    }
    else {
        $perms = array();
        foreach($_POST['perms_read'] as $user => $check) {
            $perm = 'r';
            if (array_key_exists($user, $_POST['perms_write']))
                $perm .= 'w';
            // alway add 'x' right
            $perm .= 'x';

            if (!isset($perms[$perm]))
                $perms[$perm] = array();
            $perms[$perm][] = $user;
        }
    }

    $params = array($share, $sharePath, $shareDesc, $perms, $adminGroups, $browseable, $av, $customParameters);
    mod_share($params);

    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_T("Share %s successfully modified"), $shareName));
    }
    else {
        // Catch exception
        // but continue to show the page
        global $errorStatus;
        $errorStatus = 0;
    }
}

if ($_GET["action"] == "add") {
    $title = _T("Add a share");
    $activeItem = "add";
    $share = "";
    $shareDesc = "";
    $permAll = False;
    $av = False;
    $browseable = True;
    $customParameters = array("");
    $perms = array("rwx" => array("@all"));
    $permAll = true;
} else {
    $share = urldecode($_GET["share"]);
    $title = _T("Properties of share $share");
    $activeItem = "index";
    $shareInfos = share_infos($share);
    $customParameters = share_custom_parameters($share);
    $shareDesc = $shareInfos["desc"];
    $sharePath = $shareInfos["sharePath"];
    $av = $shareInfos["antivirus"];
    $browseable = $shareInfos["browseable"];
    $perms = getACLOnShare($share);
    $permAll = in_array('@all', $perms['rwx']);
}

$p = new PageGenerator($title);
$sidemenu->forceActiveItem($activeItem);
$p->setSideMenu($sidemenu);
$p->display();

?>

<?php if ($_GET["action"] == "add")  { ?>
<p>
<?php echo  _T("The share name can only contains letters (lowercase and uppercase) and numbers, and must begin with a letter."); ?>
</p>

<?php
}
?>

<form id="Form" method="post" action="" onSubmit="autoadminObj.selectAll(); return validateForm();">

<?php

$t = new Table();
if ($_GET["action"] == "add")  {
    $input = new InputTpl("shareName");
} else {
    $input = new HiddenTpl("shareName");
}
$t->add(
        new TrFormElement(_T("Name"), $input),
        array("value" => $share)
        );

$t->add(
        new TrFormElement(_T("Comment"), new InputTpl("shareDesc")),
        array("value" => $shareDesc)
        );

if (hasAv()) {
    $checked = "";
    if ($av) {
        $checked = "checked";
    }
    $param = array ("value" => $checked);
    $t->add(
            new TrFormElement(_T("AntiVirus on this share"), new CheckboxTpl("hasAv")),
            $param
            );
}
$t->display();

$d = new DivExpertMode();
$d->push(new Table());

/* As long as we have no own modShare() (Ticket #96), the sharePath is readonly in edit mode */
if ($_GET["action"] == "add")  {
    $sharePath = "";
    $sharePathText = sprintf(_T("Share path (leave empty for a default path in %s)"), default_shares_path());
    $input = new IA5InputTpl("sharePath");
} else {
    $sharePath = $shareInfos["sharePath"];
    $sharePathText = "Path";
    $input = new IA5InputTpl("sharePath");
}

$d->add(
        new TrFormElement(_T($sharePathText), $input),
        array("value" => $sharePath)
        );

if ($browseable) $param = array("value" => "CHECKED");
else $param = array("value" => "");

$d->add(
        new TrFormElement(_T("This share is visible on the domain"), new CheckboxTpl("browseable")),
        $param
        );
$d->pop();
$d->display();

?>

<script src="jsframework/lib/angular.min.js"></script>
<script>
'use strict';

angular.module('mmc.samba.perms', [])

.controller('permsCtrl', function($scope) {
    $scope.perms = <?= json_encode($perms); ?>;
    var users = <?= json_encode(get_users_detailed($error, 'objectClass=sambaSamAccount', 0, 200000)); ?>;
    var groups = <?= json_encode(search_groups('*')); ?>;
    // build a list of entities (users, groups)
    $scope.entities = groups.map(function(arr) {
        return {name: '@' + arr[0], label: arr[0]}
    });
    $scope.entities = $scope.entities.concat(users[1].map(function(obj) {
        var name = function() {
            if (obj.givenName && obj.sn && (obj.givenName != obj.sn))
                return obj.givenName + " " + obj.sn;
            else
                return obj.uid;
        };
        return {name: obj.uid, label: name()}
    }));
})

.directive('perms', function() {

    return {
        restrict: 'E',

        scope: {
            perms: '=',
            entities: '='
        },

        controller: function($scope) {

            function Perm(entity, rights) {
                this.entity = entity;
                this.read = rights.read || false;
                this.write = rights.write || false;
                this.execute = rights.execute || false;

            };
            Perm.prototype = {
                get rights() {
                    var rights = ['r', 'w', 'x'];
                    return [this.read, this.write, this.execute].map(function(v, idx) {
                        if (v)
                            return rights[idx];
                        return '';
                    }).join('');
                }
            };

            $scope.toList = function() {
                var perms = [];
                for (var rights in $scope.perms) {
                    $scope.perms[rights].forEach(function(entityName) {
                        if (entityName == "@all")
                            return;
                        perms.push(new Perm(entityName, {read: rights.indexOf('r') !== -1,
                                                         write: rights.indexOf('w') !== -1,
                                                         execute: rights.indexOf('x') !== -1}));
                    });
                }
                return perms;
            };
            $scope.permsList = $scope.toList();

            $scope.getEntity = function(name) {
                for (var i in $scope.entities) {
                    if ($scope.entities[i].name == name)
                        return $scope.entities[i];
                }
            };

            $scope.deletePerm = function(entity) {
                for (var i in $scope.permsList) {
                    if ($scope.permsList[i].entity == entity) {
                        $scope.permsList.splice(i, 1);
                        break;
                    }
                }
                return true;
            };

            $scope.addPerm = function(entity) {
                if (!$scope.hasPerm(entity)) {
                    $scope.permsList.push(new Perm(entity, {read: true, write: true, execute: true}));
                    $scope.cleanSearch();
                }
            };

            $scope.hasPerm = function(entity) {
                for (var i in $scope.permsList) {
                    if ($scope.permsList[i].entity == entity)
                        return $scope.permsList[i];
                }
                return false;
            };

            $scope.cleanSearch = function() {
                $scope.search = "";
                $scope.searchResult = [];
            };

            $scope.isGroup = function(entity) {
                return entity.substr(0, 1) == '@';
            };

            $scope.$watch('search', function(newVal) {
                if (newVal) {
                    $scope.searchResult = $scope.entities.filter(function(entity) {
                        if ((entity.label.toLowerCase().indexOf(newVal.toLowerCase()) !== -1
                                || entity.name.toLowerCase().indexOf(newVal.toLowerCase()) !== -1)
                                && !$scope.hasPerm(entity.name)) {
                            return true;
                        }
                        else
                            return false
                    });
                }
                else {
                    $scope.cleanSearch();
                }
            });

        },

        templateUrl: 'modules/samba/shares/perms.html'
    }

});

</script>

<table cellspacing="0">
<?php
    $checked = "";
    if ($permAll)
	    $checked = "checked";

    $param = array ("value" => $checked,"extraArg"=>'onclick="toggleVisibility(\'grouptable\');"');
    $test = new TrFormElement(_T("Access for all"), new CheckboxTpl("permAll"));
    $test->setCssError("permAll");
    $test->display($param);

    if ($permAll) {
        echo '<tr id="grouptable" style="display:none">';
    } else {
        echo '<tr id="grouptable">';
    }
?>
        <td class="label" style="text-align: right;">Permissions</td>
        <td>
            <div id="samba-perms" ng-app="mmc.samba.perms" ng-controller="permsCtrl">
                <perms perms="perms" entities="entities" />
            </div>
        </td>
    <tr>
</table>

<div id="expertMode" class="expertMode" <?php displayExpertCss(); ?>>
<table cellspacing="0">
    <tr>
    <td>
    </td>
    <td>
        <?php echo  _T("Administrator groups for this share"); ?>
    </td>
   </tr>

<?php
    if ($_GET["action"] == "add") {
        $domadmin = getDomainAdminsGroup();
        if ($domadmin)
            setVar("tpl_groups", array($domadmin["cn"][0]));
        else
            setVar("tpl_groups", array());
    }
    else {
        $domadmin = getAdminUsersOnShare($share);
        if ($domadmin)
            setVar("tpl_groups", $domadmin);
        else
            setVar("tpl_groups", array());
    }
    global $__TPLref;
    $__TPLref["autocomplete"] = "admin";
    renderTPL("groups");
?>

</table>

<?php

    if (!isset($customParameters) || empty($customParameters)) {
        $customParameters = array('');
    }
    $cp = new MultipleInputTpl("customparameters",_("Custom parameters"));
    $cp->setRegexp('/^[a-z: _]+[ ]*=.*$/');
    $cpf = new FormElement(_("Custom parameters"), $cp);
    $cpf->display($customParameters);

?>

</div>

<?php if ($_GET["action"] == "add")  { ?>
<input name="bcreate" type="submit" class="btnPrimary" value="<?php echo  _T("Create"); ?>" />
<?php } else { ?>
<input name="share" type="hidden" value="<?php echo $share; ?>" />
<input name="bmodify" type="submit" class="btnPrimary" value="<?php echo  _T("Confirm"); ?>" />
<?php }

?>

</form>
