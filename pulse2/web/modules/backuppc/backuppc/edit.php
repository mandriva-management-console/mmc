<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com
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

require_once("modules/backuppc/includes/xmlrpc.php");
require_once("modules/backuppc/includes/functions.php");
require_once("modules/backuppc/includes/html.inc.php");

// Receiving POST DATA
if (isset($_POST['bconfirm'],$_POST['host'])){
    // Setting host profiles
    set_host_backup_profile($_POST['host'], $_POST['backup_profile']);
    set_host_period_profile($_POST['host'], $_POST['period_profile']);
    // Sending Host config to backupPC
    $cfg = array();
    // 1 - Shares and exclude settings
    $cfg['RsyncShareName'] = $_POST['sharenames'];
    
    // Splitting excludes by \n
    foreach ($_POST['excludes'] as $key => $value) 
        $_POST['excludes'][$key] = explode("\n",trim($value));
    
    $cfg['BackupFilesExclude'] = array_combine($_POST['sharenames'],$_POST['excludes']);
    
    // 2 -Backup Period settings
    
    $cfg['FullPeriod'] = $_POST['full'];
    $cfg['IncrPeriod'] = $_POST['incr'];
    
    // Blackout periods
    $starthours = $_POST['starthour'];
    $endhours = $_POST['endhour'];
    
    $cfg['BlackoutPeriods'] = array();
    
    for ($i = 0 ; $i<count($starthours); $i++) {
        $daystring = implode(', ',$_POST['days'.$i]);
        $cfg['BlackoutPeriods'][] = array(
            'hourBegin' => hhmm2float($starthours[$i]), 
            'hourEnd'   => hhmm2float($endhours[$i]),
            'weekDays'  => $daystring
                );
    }
    
    set_host_config($_POST['host'], $cfg);
}

// ===========================================================================
// ===========================================================================

$package = array();

// display an edit config form 
$f = new ValidatingForm();
$f->push(new Table());

$host = $_GET['objectUUID'];

$backup_profile_id = get_host_backup_profile($host);
$period_profile_id = get_host_period_profile($host);

if ( $backup_profile_id == -1 )
{
    print "Host backup is not set.";
    return;
    // TODO: Create a record for new host
    // And send it to backuppc HOST LIST
}

$response = get_host_config($host);

// Check if error occured
if ($response['err']) {
    new NotifyWidgetFailure(nl2br($response['errtext']));
    return;
}


// Getting all avavaible profiles
$backup_profiles = get_backup_profiles();
$period_profiles = get_period_profiles();

// BackupPC config for this host
$host_config = $response['host_config'];

$f->add(new HiddenTpl("host"), array("value" => $host, "hide" => True));

// Backup Active
$f->add(
        new TrFormElement("Backup active", new CheckboxTpl('active')),
        array("value" => (1 ? 'checked' : ''))
    );

// BACKUP PROFILE SELECT FIELD

$sel = new SelectItem("backup_profile");
$list = array();
$list[0] = _T('Custom config','backuppc');
foreach ($backup_profiles as $profile)
    $list[intval($profile['id'])] = $profile['profilename'];
$sel->setElements(array_values($list));
$sel->setElementsVal(array_keys($list));
$sel->setSelected($backup_profile_id);

 $f->add(
    new TrFormElement("Backup profile", $sel,
    array())
);

// =====================================================================

// Exclude lists
$sharenames = $host_config['RsyncShareName'];
$excludes = $host_config['BackupFilesExclude'];

$i = 0;

foreach ($sharenames as $sharename) {
    
    $_excludes = isset($excludes[$sharename])?$excludes[$sharename]:array();    
    $_excludes = implode("\n", $_excludes);
    
    // Fields
    $fields = array(
        new InputTpl('sharenames[]'),
        new textTpl(_T('Excluded files','backuppc')),
        new TextareaTpl('excludes[]'),
        new buttonTpl('removeShare',_T('Remove'),'removeShare')
        );
    
    $values = array(
        $sharename,
        '',
        $_excludes,
        ''
    );
    
    $f->add(
        new TrFormElement(_T('Backupped directories','backuppc'), new multifieldTpl($fields)),
        array("value" => $values,"required" => True)
    );
}

// Add Share button
$addShareBtn = new buttonTpl('addShare',_T('Add Sharename','backuppc'));
$addShareBtn->setClass('btnPrimary');
$f->add(
    new TrFormElement('', $addShareBtn),
    array()
);

//=======================================
// PERIOD PROFILE SELECT FIELD

$sel = new SelectItem("period_profile");
$list = array();
$list[0] = _T('Custom config','backuppc');
foreach ($period_profiles as $profile)
    $list[intval($profile['id'])] = $profile['profilename'];
$sel->setElements(array_values($list));
$sel->setElementsVal(array_keys($list));
$sel->setSelected($period_profile_id);

 $f->add(
    new TrFormElement("Backup Periods profile", $sel,
    array())
);

// =====================================================================
// Period config

// FULL period
$f->add(
    new TrFormElement('Full period', new InputTpl('full')),
    array("value" => $host_config['FullPeriod'],"required" => True)
);

// INCR period
$f->add(
    new TrFormElement('Inremental period', new InputTpl('incr')),
    array("value" => $host_config['IncrPeriod'],"required" => True)
);

$daynames = array(
    _T('Monday','backuppc'),
    _T('Tuesday','backuppc'),
    _T('Wednesday','backuppc'),
    _T('Thursday','backuppc'),
    _T('Friday','backuppc'),
    _T('Saturday','backuppc'),
    _T('Sunday','backuppc')
);

// Exclude periods
$exclude_periods = explode("\n",$profile['exclude_periods']);
$z = 0;

foreach ($host_config['BlackoutPeriods'] as $period) {
    
    list($from,$to,$days) = array($period['hourBegin'],$period['hourEnd'],$period['weekDays']);

    $days = explode(',',$days);
   
    // DAYS SELECT
    $sel = new MultipleSelect('days'.$z++);
    $sel->setElements($daynames);
    $sel->setElementsVal(array('1','2','3','4','5','6','7'));
    foreach ($days as $day)
        $sel->setSelected(trim($day));
    
    // Start hour
    $fields = array(
        new hourInputTpl('starthour[]'),
        new textTpl('to'),
        new hourInputTpl('endhour[]'),
        new textTpl('during'),
        $sel,
        new buttonTpl('removePeriod',_T('Remove'),'removePeriod')
        );
    
    $values = array(
        float2hhmm($from),
        '',
        float2hhmm($to),
        '',
        ''
    );
    
    $f->add(
        new TrFormElement('Do not backup from', new multifieldTpl($fields)),
        array("value" => $values,"required" => True)
    );

    
}

// Add Period button
$addPeriodBtn = new buttonTpl('addPeriod','Add period');
$addPeriodBtn->setClass('btnPrimary');
$f->add(
    new TrFormElement('', $addPeriodBtn),
    array()
);

// =====================================================================

$f->pop();
$f->addValidateButton("bconfirm");
$f->display();
?>

<script src="modules/backuppc/lib/jquery-1.10.1.min.js"></script>
<script src="modules/backuppc/lib/jquery-ui.min.js"></script>
<script src="modules/backuppc/lib/jquery.maskedinput-1.3.min.js"></script>
<script src="modules/backuppc/lib/jquery.multiselect.js"></script>
<script type="text/javascript">
// Avoid prototype <> jQuery conflicts
jQuery.noConflict();

jQuery(function(){
    
    shareLine = jQuery('.removeShare:first').parents('tr:first').clone();
        
     // Remove Share button
     jQuery('.removeShare').click(function(){
         if (jQuery('.removeShare').length > 1)
             jQuery(this).parents('tr:first').remove();
         // Switch to custom profile
         jQuery('select#backup_profile').val(0);
     });
     
     
     // Add Share button
     jQuery('#addShare').click(function(){
        var newline = shareLine.clone().insertBefore(jQuery(this).parents('tr:first'));
         newline.find('input[type=text]').val('');
         newline.find('textarea').val('');

         newline.find('.removeShare').click(function(){
            if (jQuery('.removeShare').length > 1)
                jQuery(this).parents('tr:first').remove();
        });
        // Switch to custom profile
         jQuery('select#backup_profile').val(0);
     });
     
     // PERIOD FUNCS
     
    periodLine = jQuery('.removePeriod:first').parents('tr:first').clone();
    
    // Multiselect listbox
    multiselConfig = {
        height: 120,
        header: false,
        minWidth : 180,
        noneSelectedText : '<?php echo _T('Select days','backuppc'); ?>',
        selectedText : '<?php echo _T('Select days','backuppc'); ?>'
     };
    jQuery("select[multiple=true]").multiselect(multiselConfig);
     
     // Remove period button
     jQuery('.removePeriod').click(function(){
         if (jQuery('.removePeriod').length > 1)
             jQuery(this).parents('tr:first').remove();
         // Switch to custom profile
         jQuery('select#period_profile').val(0);
     });
     
     // Hour mask inputs
     jQuery('input[name="starthour[]"]').mask('99:99');
     jQuery('input[name="endhour[]"]').mask('99:99');
     
     // Add period button
     jQuery('#addPeriod').click(function(event,nobtn){
        var idx = parseInt(jQuery('select:last').attr('name').replace('days','').replace('[]',''))+1;        
        if (isNaN(idx)) idx = 0;
        var newline = periodLine.clone().insertBefore(jQuery(this).parents('tr:first'));
         newline.find('input[type=text]').val('');
         newline.find('select').val([])
                 .attr({'name':'days'+idx+'[]','id':'days'+idx+'[]'})
         if (!nobtn)
            newline.find('select').multiselect(multiselConfig);
         newline.find('.removePeriod').click(function(){
            if (jQuery('.removePeriod').length > 1)
                jQuery(this).parents('tr:first').remove();
        });
        // Hour mask inputs
        newline.find('input[name="starthour[]"]').mask('99:99');
        newline.find('input[name="endhour[]"]').mask('99:99');
        // Switch to custom profile
         jQuery('select#period_profile').val(0);
     });
    
    
    // If any input changes, profile => custom
    function switchBckToCustom(){
        // If profile select we pass
        if (jQuery(this).attr('name') != 'backup_profile')
            jQuery('select#backup_profile').val(0);
    }
    jQuery('select[multiple=true],input[name="sharenames[]"],textarea[name="excludes[]"]').change(switchBckToCustom);   
    function switchPrdToCustom(){
        // If profile select we pass
        if (jQuery(this).attr('name') != 'period_profile')
            jQuery('select#period_profile').val(0);
    }
    jQuery('select[multiple=true],input[name=full],input[name=incr],input[name="starthour[]"],input[name="endhour[]"]').change(switchPrdToCustom);   
    
    // Profiles definition
    backup_profiles = <?php print json_encode($backup_profiles) ?> ;
    period_profiles = <?php print json_encode($period_profiles) ?> ;
    
    // Backup Profile selection
    jQuery('select#backup_profile').change(function(){
        // Selected profile
        selProfile = jQuery(this).val();    
        for (var i = 0 ; i < backup_profiles.length ; i++ )
            if (backup_profiles[i]['id'] == selProfile) {
                // Deleting Sharenames lines
                jQuery('.removeShare').each(function(){
                    jQuery(this).parents('tr:first').remove();
                });
                // Adding profile shares
                var _sharenames = backup_profiles[i]['sharenames'].split('\n');
                var _excludes = backup_profiles[i]['excludes'].split('||');
                for (var z = 0 ; z < _sharenames.length ; z++ ){
                    jQuery('#addShare').trigger('click');
                    jQuery('input[name="sharenames[]"]:last').val(_sharenames[z]).change(switchBckToCustom);
                    jQuery('textarea[name="excludes[]"]:last').val(_excludes[z]).change(switchBckToCustom);
                    jQuery('.removeShare:last').click(switchBckToCustom);
                }
                
                break;
            }
        jQuery(this).val(selProfile);
        
    });
    
    // Period Profile selection
    jQuery('select#period_profile').change(function(){
        // Selected profile
        selProfile = jQuery(this).val();    
        for (var i = 0 ; i < period_profiles.length ; i++ )
            if (period_profiles[i]['id'] == selProfile) {
                // Deleting Sharenames lines
                jQuery('.removePeriod').each(function(){
                    jQuery(this).parents('tr:first').remove();
                });
                
                jQuery('input[name=full]:last').val(period_profiles[i]['full']).change(switchPrdToCustom);
                jQuery('input[name=incr]:last').val(period_profiles[i]['incr']).change(switchPrdToCustom);
                
                // Adding profile periods
                var regex = /([0-9.]+)=>([0-9.]+):([^:]+)/;
                
                var _periods = period_profiles[i]['exclude_periods'].split('\n');
                for (var z = 0 ; z < _periods.length ; z++ ){
                    jQuery('#addPeriod').trigger('click',[1]);
                    var matches = _periods[z].match(regex);
                    var _starthour = parseFloat(matches[1]);
                    var _endhour = parseFloat(matches[2]);
                    var _days = matches[3].split(',');
                    (_days);
                    
                    jQuery('input[name="starthour[]"]:last').val(("0" + parseInt(_starthour)).slice(-2)+':'+("0" + parseInt((_starthour-parseInt(_starthour))*60)).slice(-2))
                            .change(switchPrdToCustom);
                    jQuery('input[name="endhour[]"]:last').val(("0" + parseInt(_endhour)).slice(-2)+':'+("0" + parseInt((_endhour-parseInt(_endhour))*60)).slice(-2))
                            .change(switchPrdToCustom);
                    jQuery('select[multiple=true]:last').val(_days).multiselect(multiselConfig).change(switchPrdToCustom);
                    jQuery('.removeShare:last').click(switchPrdToCustom);
                }
                
                break;
            }
        jQuery(this).val(selProfile);
        
    });
});   
   
    
</script>