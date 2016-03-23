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
?>
<?php 

/**
 *	Fonction qui ajoute un tableau a un fichier
 */
function file_put_array_contents($filename, $data, $file_append = false) {
   $fp = fopen($filename, (!$file_append ? 'w+' : 'a+'));
   if(!$fp) {
     trigger_error('file_put_contents cannot write in file.', E_USER_ERROR);
     return;
   }
   foreach ($data as $line) {
   	fputs($fp, "$line\n");
        }
   fclose($fp);
}


/**
 *	Fonction qui recup�re la blacklist sous forme d'un array avec clef 
 * 	correspondant au contenu
 *      
 * 	ex: $arrB["google.fr"]="google.fr" (obligatoire pour les suppressions)
 */

function get_blacklist()
{
	return xmlCall("proxy.getBlackList",null);
}

/**
 *	Renvoie la blacklist indexé de 0 à X
 *	Uniquement pour l'affichage
 */
function get_nonIndexBlackList() {
	$arrB =get_blacklist();
	
	sort($arrB);
	return $arrB;
}

/**
 *	Fonction qui sauvegarde la blacklist (m�moire) en fichier
 */
function save_blacklist($arrayBlackList)
{
  //global $conf;
  //$smbconf = $conf["proxy"]["squidguard"];

  //$smb = file_put_array_contents($smbconf,$arrayBlackList);

}

/**
 *	Ajoute un element dans la blacklist (mémoire)
 */
function addElementInBlackList($element,&$arrBlackList) {
	/*$arrBlackList["$element"]=strtolower($element);
	return $arrBlackList;*/
    xmlCall("proxy.addBlackList",$element);
}

/**
 *	Supprime un element dans la blacklist (mémoire)
 */
function delElementInBlackList($element,&$arrBlackList) {
	/*unset($arrBlackList[$element]);
	return $arrBlackList;*/
    xmlCall("proxy.delBlackList",$element);
}

function getStatutProxy() {
    return xmlCall("proxy.getStatutProxy",null);
}

?>
