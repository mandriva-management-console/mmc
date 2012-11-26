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

class UserQuotaTitleElement extends HtmlElement {
	function __construct($title){
		$this->title=$title;
	}

	function display(){
		print '<h2>'.$this->title.'</h2>';
	}
}
class TrCommentElement extends HtmlElement {
	var $comment;
	function __construct($comment){
		$this->comment=$comment;
	}

	function display(){
		print '<tr><td>&nbsp;</td><td><p>'.$this->comment.'</p></td></tr>';
	}
}
class quotainputgroup extends AbstractTpl {
	private $id;
	function __construct($id) {
		$this->id = $id;
	}
	function display() {
		print 'Quota:<input type="text" name="quota'.$this->id.'" size="8" value="">';
		print 'Blocks hard:<input type="text" name="quota1'.$this->id.'" size="8" value="">';
	}
}

class NetworkQuota {
	private $name;
	private $network;
	private $protocol;
	private $id;
	private $quotasize;
	function __construct($map){
		$items = preg_split('/:/', $map);
		$this->name = $items[0];
		$this->network = $items[1];
		$this->protocol = $items[2];
		$this->id = md5($this->name);
	}
	function getQuotaField() {
		return "networkquota".$this->id;
	}

	function getQuotaForm(){
		$e = new TrFormElement($this->name." (in Mb)",new InputTpl($this->getQuotaField(), '/^[0-9]*$/'));
		$e->tooltip = $this->network;
		return $e;
	}
	function getQuotaSize() {
		return (string)$this->quotasize;
	}
	function setCurrentQuotas($quotas) {
		if (count($quotas) > 0) {
			foreach ($quotas as $quota) {
				$parts = preg_split("/[,:]+/", $quota);
				if ( $parts[0] == $this->name ) {
					$this->quotasize = $parts[3] / 1048576;
				}
			}
		}
	}
}


class DiskQuota {
	private $device;
	private $blocks;
	private $name;
	private $id;
	private $quotasize = "";
	function __construct($devicemap){
		$items = preg_split('/:/', $devicemap);
		$this->device = $items[0];
		$this->blocks = $items[1];
		$this->name = $items[2];
		$this->id = md5($this->name);
	}

	function getQuotaField() {
		return "diskquota".$this->id;
	}
	function getQuotaForm(){
		$e = new TrFormElement($this->name." (in Mb)" ,new InputTpl($this->getQuotaField(), '/^[0-9]*$/'));
		$e->tooltip = $this->device;
		return $e;
	}

	function getQuotaSize() {
		return (string)$this->quotasize;
	}
	function setCurrentQuotas($quotas) {
		if (count($quotas) > 0) {
			foreach ($quotas as $quota) {
				$parts = preg_split("/[=:]+/", $quota);
				if ( $parts[0] == $this->device ) {
					$this->quotasize = $parts[1]*$this->blocks / 1048576;
				}
			}
		}
	}
}
