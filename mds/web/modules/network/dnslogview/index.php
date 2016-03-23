<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com/
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once("graph/navbar.inc.php");
require_once("includes/ajaxTools.php");
require("modules/base/logview/localSidebar.inc.php");

displayInputLiveSearch(urlStrRedirect('base/logview/dnssetsearch')); ?>

<div id="container">
</div>


<h2><?php echo  _T("DNS service log view", "network"); ?></h2>


<div class="fixheight"></div>
<div style="height: 400px; overflow: auto;" id="logupdater"></div>

<script>
    new Ajax.PeriodicalUpdater('logupdater','<?php echo  urlStrRedirect('base/logview/dnsshow') ?>','2',{evalScripts: true});
    new Ajax.Updater('container','<?php echo  urlStrRedirect('base/logview/dnssetsearch') ?>');
</script>

