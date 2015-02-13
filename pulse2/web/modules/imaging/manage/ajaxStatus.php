<?php
/*
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2010 Mandriva, http://www.mandriva.com
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

/* common ajax includes */
require("../includes/ajaxcommon.inc.php");

$t = new TitleElement(_T("Status", "imaging"), 3);
$t->display();

$customMenu_count = xmlrpc_getCustomMenuCount($location);
$global_status = xmlrpc_getGlobalStatus($location);
if (!empty($global_status)) {
    $disk_info = format_disk_info($global_status['disk_info']);
    $health = format_health($global_status['uptime'], $global_status['mem_info']);
    $short_status = $global_status['short_status'];
    ?>

    <div class="status">
        <div class="status_block">
            <h3><?php echo _T('Space available on server', 'imaging') ?></h3>
            <?php echo $disk_info; ?>
        </div>
        <div class="status_block">
            <h3><?php echo _T('Load on server', 'imaging') ?></h3>
            <?php echo $health; ?>
        </div>
    </div>

    <div class="status">
        <!--<div class="status_block">
            <h3 style="display: inline"><?php echo _T('Synchronization state', 'imaging') ?> : </h3>
        <?php
        $led = new LedElement('green');
        $led->display();
        echo "&nbsp;" . _T("Up-to-date", "imaging");
        ?>
        </div>-->
        <div class="status_block">
            <?php //<a href=" echo urlStrRedirect("imaging/imaging/createCustomMenuStaticGroup"); &location=UUID1">ZZZ</a> ?>
            <h3><?php echo _T('Stats', 'imaging') ?></h3>
            <p class="stat"><img src="img/machines/icn_machinesList.gif" /> <strong><?php echo $short_status['total']; ?></strong> <?php echo _T("client(s) registered", "imaging") ?> (<?php echo $customMenu_count; ?> <?php echo _T("with custom menu", "imaging") ?>)</p>
            <p class="stat"><img src="img/machines/icn_machinesList.gif" /> <strong><?php echo $short_status['rescue']; ?></strong>/<?php echo $short_status['total']; ?> <?php echo _T("client(s) have rescue image(s)", "imaging") ?></p>
            <?php
             if ( $short_status['master']!=0)
             {
             echo'
             <p class="stat">
             <a
                 href="javascript:;"
                 onclick="PopupWindow(event,'."'main.php?module=imaging&amp;submod=manage&amp;action=viewMastersAvailable'".', 300); return false;"><img src="img/common/cd.png" />
                 <strong>'.$short_status['master'].'</strong>'._T("masters are available", "imaging").'</a>
             </p>';
             }else
             {
             echo'
             <p class="stat">
             <img src="img/common/cd.png" />
                 <strong>'.$short_status['master'].'</strong>'._T("masters are available", "imaging").'
             </p>';
             }
             ?>
        </div>
    </div>

    <div class="spacer"></div>

    <h3 class="activity"><?php echo _T('Recent activity', 'imaging') ?></h3>

    <?php
    $ajax = new AjaxFilter("modules/imaging/manage/ajaxLogs.php", "container_logs", array(), "Logs");
    //$ajax->setRefresh(10000);
    $ajax->display();
    echo "<br/><br/><br/>";
    $ajax->displayDivToUpdate();
} else {
    $e = new ErrorMessage(_T("Can't connect to the imaging server linked to the selected entity.", "imaging"));
    print $e->display();
}

require("../includes/ajaxcommon_bottom.inc.php");
?>
