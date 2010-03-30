<?php

/**
 * (c) 2009 Open Systems Specilists - Glen Ogilvie
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

class ImportUsers {
    var $array_keys;
    var $users;
    var $allow_import = false;

    function __construct($header) {
        // validate header and throw an exception if bad
        ImportUsers :: verifyHeaders($header);
        $this->array_keys = $header;
    }

    function getValidAttributes() {
        $attributes = array_merge(self :: getOptionalAttributes(), self :: getImportRequiredAttributes(), self :: getRequiredAttributes());
        sort($attributes);
        return $attributes;
    }

    function allowImport() {
        foreach ($this->users as $user) {
            if ($user->importable)
                return true;
        }
        return false;
    }

    function allowModify() {
        foreach ($this->users as $user) {
            if ($user->modifiable)
                return true;
        }
        return false;
    }

    function allowDelete() {
        foreach ($this->users as $user) {
            if ($user->deletable)
                return true;
        }
        return false;
    }

    function bigList() {
        if (count($this->users) > 100) {
            return true;
        }
    }

    function getOptionalAttributes() {
        // maybe eventually return list from ldap schema
        return array (
                      "mail",
                      "primarygroup",
                      "loginShell",
                      "telephoneNumber",
                      "mobile",
                      "gecos",
                      "description",
                      "carLicense",
                      "manager",
                      "audio",
                      "departmentNumber",
                      "pager",
                      "physicalDeliveryOfficeName",
                      "postalAddress",
                      "postalCode",
                      "title",
                      "roomNumber",
                      "preferredLanguage",
                      "localityName",
                      "organizationName",
                      "files"
                      );
    }

    function getRequiredAttributes() {
        return array (
                      "login"
                      );
    }

    function getImportRequiredAttributes() {
        return array (
                      "password",
                      "firstname",
                      "surname"
                      );
    }

    function progress($type, $complete) {
        echo "<h1>$type in progress</h1>";
        echo "<p>Do not close your browser</p>";
        echo "<p>" . $complete . " of " . count($this->users) . " completed.</p>";
    }

    function import() {
        if (!$this->allow_import)
            throw new Exception("Import headers not verified");		
        foreach ($this->users as $user) {
            $user->import();
        }
    }

    function delete() {
        foreach ($this->users as $user) {
            $user->delete();
        }
    }

    function modify() {
        foreach ($this->users as $user) {
            $user->modify();
        }
    }

    function adduser($userrecord) {
        for ($i = 0; $i < count($this->array_keys); $i++) {
            $record[$this->array_keys[$i]] = $userrecord[$i];
        }
        $this->users[] = new CSVUser($record);
    }

    function getListInfos($extra = "&amp;stage=preimport") {
        $cols = array ();
        foreach ($this->users as $user) {
            foreach ($user->getArray() as $key => $value) {
                $cols[$key][] = $value;
            }
            $cols["State"][] = "<strong>".$user->getState()."</strong>";
        }
        foreach ($cols as $key => $value) {
            if (isset ($l))
                $l->addExtraInfo($cols[$key], $key);
            else
                $l = new ListInfos($cols[$key], $key, $extra);
        }
        return $l;
    }
	
    function verifyImportHeaders() {

        foreach (self :: getImportRequiredAttributes() as $attribute) {
            if (!in_array($attribute, $this->array_keys)) {
                throw new Exception("Import disabled because missing attribute required for import in CSV header: $attribute");
            }
        }
        if (in_array("files", $this->array_keys)) {
            throw new Exception("files attribute is set, which should only be used on delete.");
        }
        $this->allow_import = true;
        return $this->allow_import;
    }
	
    function verifyHeaders($header) {
        foreach ($header as $attribute) {
            if (!in_array($attribute, self :: getValidAttributes())) {
                throw new Exception("Invalid attribute in CSV header: $attribute");
            }
        }
        foreach (self :: getRequiredAttributes() as $attribute) {
            if (!in_array($attribute, $header)) {
                throw new Exception("Missing required attribute in CSV header: $attribute");
            }
        }
    }

}
class CSVUser {
    var $user;
    var $importable = false;
    var $modifiable = false;
    var $valid = false;
    var $deletable = false;
    var $user_exists = false;
    var $result = "";

    function __construct($user) {
        $this->user = $user;
    }
    function getArray() {
        return $this->user;
    }
    function delete() {
        if ($this->deletable) {
            $login = $this->user["login"];
            del_user($login, array_key_exists("files", $this->user) ? $this->user["files"] : "on");
            $this->result = "Deleted";
        }
    }
    function getState() {
        if (!empty($this->result)) {
            return $this->result;
        }

        $this->validBase();
        $state = "Unvalidated";

        if (!$this->valid) {
            $state = "Invalid";
            return $state;
        }
        if ($this->importable) {
            $state = "Importable";
        }
        elseif ($this->deletable && $this->modifiable) {
            $state = "Deletable or Modifiable";
        }
        elseif ($this->deletable) {
            $state = "Deletable";
        }
        elseif ($this->modifiable) {
            $state = "Modifiable";
        }
        else {
            $state = "Unable to validate";
        }
        return $state;

    }

    function validBase() {
        $this->valid = $this->checkattribute("login");
        if (!$this->valid)
            return;
        $this->user_exists = exist_user($this->user["login"]);

        if (!$this->user_exists) {
            $this->importable = true;
            foreach (ImportUsers :: getImportRequiredAttributes() as $attribute) {
                if (!$this->checkattribute($attribute)) {
                    $this->importable = false;
                }
            }
        }
        else {
            $this->deletable = true;
            // check for user
            if ($this->user_exists && count($this->user) > 1)
                $this->modifiable = true;
        }
    }

    function checkAttribute($key) {
        if (array_key_exists($key, $this->user)) {
            if (strlen($this->user[$key]) > 0)
                return true;
        }
        return false;
    }

    function modify() {
        if ($this->modifiable) {
            $user = $this->user;
            $login = $user["login"];
            // the following are not permitted to be changed yet
            unset($user["login"],  $user["firstname"], $user["surname"], 
                  $user["homedir"], $user["createhomedir"], $user["primaryGroup"]);
            if (key_exists("password", $user)) {
                $ret = callPluginFunction("changeUserPasswd", 
                                          array(array($login, prepare_string($user["password"]))));
                if(isXMLRPCError()) {
                    foreach($ret as $info) {
                        $this->result .= _("Password not updated")."<br/>";
                    }
                    # set errorStatus to 0 in order to make next xmlcalls
                        global $errorStatus;
                    $errorStatus = 0;
                }
                else {
                    //update result display
                    $this->result .= _("Password updated.")."<br />";
                }
                unset ($user["password"]);
            }
            if (count($user) > 0) {
                foreach ($user as $attribute => $value) {
                    if ($this->checkAttribute($attribute)) {
                        changeUserAttributes($login, $attribute, $value);
                    }
                    else {
                        unset ($user[$attribute]);
                    }
                }
                $this->result .= count($user) . " Attribute(s) Modified";
            }
        }

    }

    function import() {
        if ($this->importable) {
            echo "import ".$this->user;
            $user = $this->user;
            $login = $user["login"];
            $ret = add_user($login, $user["password"], $user["firstname"], $user["surname"], array_key_exists("homedir", $user) ? $user["homedir"] : "", array_key_exists("createhomedir", $user) ? $user["createhomedir"] : "yes", array_key_exists("primaryGroup", $user) ? $user["primaryGroup"] : "");
            $this->result .= $ret["info"]." ";
            /*$this->modifiable = true;
              $this->modify();*/
        }
    }

}
?>
