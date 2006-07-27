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
?>
<?
        /**
         * module declaration
         */
        $mod = new Module("samba");
        $mod->setVersion("1.0.1");
        $mod->setRevision("$Rev$");
        $mod->setAPIVersion('1:0:0');

        /**
         * shares submod definition
         */

        $submod = new SubModule("shares");
        $submod->setDescription(_T("Shares","samba"));
        $submod->setImg('img/navbar/share');
        $submod->setDefaultPage("samba/shares/index");

        $page = new Page("index",_T("List shares","samba"));
        $submod->addPage($page);

        $page = new Page("add",_T("Add a share","samba"));
        $submod->addPage($page);

        $page = new Page("backup",_T("Backup a share","samba"));
        $page->setOptions( array ("noHeader" => True));
        $submod->addPage($page);

        $page = new Page("delete",_T("Remove a share","samba"));
        $page->setOptions( array ("noHeader" => True));
        $submod->addPage($page);

        $page = new Page("details",_T("Share details","samba"));
        $submod->addPage($page);

        $mod->addSubmod($submod);


        /**
         *  Machines submod
         */
        $submod = new SubModule("machines");
        $submod->setVisibility(False);
        $submod->setDefaultPage("samba/machines/index");
        $submod->setDescription(_T("Machines"),"samba");

        $page = new Page("index",_T("Computer list","samba"));
        $submod->addPage($page);

        $page = new Page("add",_T("Add a computer","samba"));
        $submod->addPage($page);

        $page = new Page("delete",_T("Delete a computer","samba"));
        $page->setOptions( array ("noHeader" => True));
        $submod->addPage($page);

        $mod->addSubmod($submod);


        /**
         * Config submod
         */
        $submod = new SubModule("config");
        $submod->setDefaultPage("samba/config/index");
        $submod->setDescription(_T("Configuration"),"samba");
        $submod->setVisibility(False);
        $submod->setAlias('shares');


        $page = new Page("index",_T("SAMBA configuration","samba"));
        $submod->addPage($page);

        $page = new Page("restart",_T("SAMBA configuration","restart"));
        $submod->addPage($page);

        $mod->addSubmod($submod);

        /**
         * Declare ACL
         */
        $mod->addACL("isSamba",_T("Add/delete SAMBA's attributes","samba"));
        $mod->addACL("isSmbDesactive",_T("Enable/Disable account","samba"));
        $mod->addACL("isSmbLocked",_T("Lock/Unlock account","samba"));

        $LMCApp =&LMCApp::getInstance();
        $LMCApp->addModule($mod);



?>
