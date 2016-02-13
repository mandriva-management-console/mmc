<?php
/*
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2009 Mandriva, http://www.mandriva.com
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
include('modules/imaging/includes/includes.php');
include('modules/imaging/includes/xmlrpc.inc.php');

if (isset($_GET['gid'])){
    $nb = extract($_GET);
}else{
    header("Location: " . urlStrRedirect("imaging/manage/index"));
    exit;
}
$label = urldecode($_GET['itemlabel']);
$params = getParams();
if ($_POST) {
    $nb = extract($_POST);
    $location = getCurrentLocation();
    $list = getRestrictedComputersList(0, -1, array('gid'=> $gid, 'hostname'=> '', 'location'=> $location), False);
    list($count, $masters) = xmlrpc_getLocationImages($location);
    if (count($list) == 0 )
    {
        $msg = _T("Multicast menu has not been created : there are no computers in the group", "imaging");
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    }
    if (!isset($numbercomputer)){
        $msg = sprintf( _T("Multicast menu has not been created : number of computers missing"));
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    } else
    {
        $numbercomputer = intval($numbercomputer);
    }
    if (!(gettype ( $numbercomputer ) == "integer")){
        $msg = sprintf( _T("Multicast menu has not been created : number of computers missing"));
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    }
    if ( count($list) < intval($numbercomputer)){
        $msg = sprintf( _T("Multicast menu has not been created : the imaging group contains %d computers and you have entered %d"),count($list),intval($numbercomputer));
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    };
    if (intval($numbercomputer)==0 ){
        $msg = sprintf( _T("Multicast menu has not been created : the imaging group contains %d computers and you have entered %d"),count($list),intval($numbercomputer));
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    }
    $objval=array();
    $objval['computer']=array();
    if ( count($list) < intval($numbercomputer)){
        $msg = sprintf( _T("Multicast menu has not been created : the imaging group contains %d computers and you have entered %d"),count($list),intval($numbercomputer));   
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    };

    $objval['location']=$location;
    $objval['nbcomputer'] = intval($numbercomputer);
    foreach($masters as $val){
        if ($val['uuid'] == $_POST['uuidmaster']){
            $objval['size'] = $val['size'];
            $objval['description'] =$val['name'];
            $objval['path'] =$val['path'];
            $objval['master'] =$_POST['uuidmaster'];
        }
    }

    $objval['group'] = $target_uuid;
    $profileNetworks1 = xmlrpc_getProfileNetworks($target_uuid);

    $mach=array();
    foreach($profileNetworks1 as $net){
        for ($t=0;$t<count($net[1]['macAddress']);$t++){
            $ip = explode(":", $net[1]['ipHostNumber'][$t]);
            if (filter_var($ip[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {;
                $mach[$net[1]['macAddress'][$t]]=$ip[0];
                printf ("%s :: %s<br>",$net[1]['macAddress'][$t],$ip[0]);
            }
        }
    }
    // list machine sans ipv4
    $machine = array();
    foreach($profileNetworks1 as $net){
        $recherche = False;
        foreach($net[1]['ipHostNumber'] as $ip){
            $ip1 = explode(":", $ip);
            if (filter_var($ip1[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $recherche = True;
                break;
            }
        }
        if ($recherche == False ){
            $machine[]=$net[1]['objectUUID'][0];
        };
    }
    $nbmachine = count($machine);
    if($nbmachine !=0 ){
        $msg = $nbmachine." "._T("computers have a [IPV6] interfaces address exclusively in the group", "imaging")."\n".
        "list machines : [".implode(" ",$machine)."]";
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    }

     
    $objval['computer']= $mach;
    

    if (count($objval['computer']) == 0 )
    {
        $msg = _T("Multicast menu has not been created : there are no computers in the group", "imaging");
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    }
    if (!isset($objval['path']) || $objval['path']=="" ){
        $msg = _T("Multicast menu has not been created : the selected master is missing on disk", "imaging");
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/list_profiles"));
        exit;
    }
//     echo "<pre>";
//     print_r ($objval);
//     echo "</pre>";
//     exit;
    $list =  xmlrpc_imagingServermenuMulticast($objval);
    if($list[0] == 1){
        $msg = _T("Multicast menu has been successfully created.", "imaging");
        new NotifyWidgetSuccess($msg);
        header("Location: " . urlStrRedirect("imaging/manage/index"));
        exit;
    }
    else{
        $msg = _T("Multicast menu has not been created : check that the imaging server is running", "imaging");
        new NotifyWidgetFailure($msg);
        header("Location: " . urlStrRedirect("imaging/manage/index"));
        exit;
    }
}
?>
<h2>
<?php echo sprintf(_T("deploy the master <strong>%s</strong> to this group <strong>%s</strong> in multicast", "imaging"), $label,$_GET[target_name]) ?>
</h2>
<form action="<?php echo urlStr("base/computers/multicast", $params) ?>" method="post">
    <table>
        <tr>
            <td>
                <?php echo _T('Number of machines to wait for', 'imaging'); ?>
            </td>
            <td>
                <input style="width:5em;" name="numbercomputer" type="text" value="" />
            </td>
        </tr>
     </table>
   
        <input name="gid"  type="hidden" value="<?php echo $gid; ?>" />
        <input name="from"  type="hidden" value="<?php echo $from; ?>" />
        <input name="is_master" type="hidden" value="<?php echo $is_master; ?>" />
        <input name="uuidmaster" type="hidden" value="<?php echo $uuidmaster; ?>" />
        <input name="is_master" type="hidden" value="<?php echo $is_master; ?>" />
        <input name="itemid" type="hidden" value="<?php echo $itemid; ?>" />
        <input name="itemlabel" type="hidden" value="<?php echo $itemlabel; ?>" />
        <input name="itemid" type="hidden" value="<?php echo $itemid; ?>" />
        <input name="target_uuid" type="hidden" value="<?php echo $target_uuid; ?>" />
        <input name="target_name" type="hidden" value="<?php echo $target_name; ?>" />
  
    <input name="bgo" type="submit" class="btnPrimary" value="<?php echo _T("Start multicast deploy", "imaging"); ?>" />
    <input name="bback" type="submit" class="btnSecondary" value="<?php echo _("Cancel"); ?>" onclick="closePopup();
            return false;" />
</form>