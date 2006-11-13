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


/**
         * module declaration
         */
        $mod = new Module("mail");
        $mod->setVersion("1.1.1");
        $mod->setRevision("$Rev$");
        $mod->setDescription(_T("Mail service"),"mail");
        $mod->setAPIVersion('2:0:0');
        $mod->setPriority(600);

        $mod->addACL("mailaccess", _T("Mail access","mail"));
        $mod->addACL("maildisable", _T("Disable mail delivery","mail"));
        $mod->addACL("maildrop", _T("Mail drop","mail"));
        $mod->addACL("mailalias", _T("Mail aliases","mail"));

        $LMCApp =& LMCApp::getInstance();
        $LMCApp->addModule($mod);
?>
