<?php

/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2011 Mandriva, http://www.mandriva.com
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
 * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once("modules/mail/includes/mail.inc.php");

if (isset($_GET["filter"]))
    $filter = $_GET["filter"];
else
    $filter = "";

$aliases = array();
$count = array();
$active = array();

foreach(getVAliases($filter) as $dn => $entry) {
    $aliases[$entry[1]["mailalias"][0]] = $entry[1]["mailalias"][0];
    $active[] = $entry[1]["mailenable"][0] == "OK" ? true : false;
    $count[] = ' (' . (count($entry[1]["mailaliasmember"]) + count($entry[1]["mail"])) . ')';
}

$n = new ListInfos(array_keys($aliases), _T("Virtual alias", "mail"));
$n->setNavBar(new AjaxNavBar(count($aliases), $filter));
$n->setAdditionalInfo($count);
$n->addExtraInfo($active, _T("Enabled", "mail"));
$n->first_elt_padding = 1;
$n->setCssClass("virtualAlias");
$n->setName(_T("Virtual alias", "mail"));

$n->addActionItem(new ActionItem(_T("Edit alias", "mail"), "edit", "edit", "alias", "mail", "aliases"));
$n->addActionItem(new ActionPopupItem(_T("Delete alias", "mail"), "delete", "delete", "alias", "mail", "aliases"));

$n->display();

?>
