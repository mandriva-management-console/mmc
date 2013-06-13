<?php

class IPv6InputTpl extends InputTpl{
    function IPv6InputTpl($name){
	$this->InputTpl($name,
	'/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/');

    }
}

class ExtendedSelectItem extends SelectItem {
    var $additionalParams;

    function ExtendedSelectItem($idElt, $jsFunc = null, $style = null) {
        $this->id=$idElt;
        $this->name=$idElt;
        $this->jsFunc = $jsFunc;
        $this->style = $style;
        $this->additionalParams = null;
     }

    function setAdditionalParams($params){
	$this->additionalParams = $params;
    }


    function display($paramArray = null){
	print "<select";
        if ($this->style) {
            print " class=\"".$this->style."\"";
        }
        if ($this->jsFunc) {
    	    print " onchange=\"".$this->jsFunc."(";
    	    if ($this->jsFuncParams) {
    		print implode(", ", $this->jsFuncParams);
    	    }
    	    print "); return false;\"";
        }

        if ($this->additionalParams) {
    	    print " ".$this->additionalParams;
        }

        print " name=\"".$this->id."\" id=\"".$this->id."\">\n";
        $this->displayContent($paramArray);
        print "</select>\n";

    }
}

class MultipleRangeInputTpl extends AbstractTpl {

    function MultipleRangeInputTpl($name, $desc='', $new=false, $formId = "Form") {
        $this->name = $name;
        /*
          stripslashes is needed, because some characters may be backslashed
          when adding/removing an input field.
        */
        $this->desc = stripslashes($desc);
        $this->regexp = '/.*/';
        $this->new = $new;
        $this->formId = $formId;
    }

    function setRegexp($regexp) {
        $this->regexp = $regexp;
    }

    function display($arrParam) {
        print '<div id="'.$this->name.'">';
        print '<table cellspacing="0">';

        if (!$arrParam)
    	    $arrParam[] = " ";
        foreach ($arrParam as $key => $param) {
            $test = new CustomDeletableTrFormElement($this->desc,
                                               new DhcpRangeTpl($this->name.'['.$key.']'),
                                               array('key' => $key,
                                                     'name' => $this->name,
                                                     'new' => $this->new,
                                                     'ajaxPage' => "modules/network/network/ajaxMultipleRange.php"

                                                 ),
                                                $this->formId
                                               );
            $test->display(array("value" => DhcpRangeTpl::valueFromDhcpRangeString($param)));

        }
        print '<tr><td width="40%" style="text-align:right;">';
        if (count($arrParam) == 0) {
            print $this->desc;
        }
        print '</td><td>';

        print '<input name="buser" type="submit" class="btnPrimary" value="'._("Add").'" onclick="
        new Ajax.Updater(\''.$this->name.'\',\'modules/network/network/ajaxMultipleRange.php\',
        { evalScripts: true, parameters: Form.serialize($(\'' . $this->formId . '\'))+\'&amp;minputname='.$this->name.'&amp;desc='.urlencode($this->desc) . '&amp;regexp='.rawurlencode($this->regexp).'\' }); return false;"/>';
        print '</td></tr>';
        print '</table>';
        print '</div>';

    }

}

class CustomDeletableTrFormElement extends DeletableTrFormElement{

    var $ajaxPage;

    function CustomDeletableTrFormElement($desc,$tpl,$extraInfo = array(), $formId) {
        $this->desc=$desc;
        $this->template=&$tpl;
        foreach ($extraInfo as $key => $value) {
            $this->$key = $value;
        }
        $this->formId = $formId;
    }

    function display($arrParam = array()) {
        if (empty($arrParam)) $arrParam = $this->options;

        if ($this->key==0) {
            $desc = $this->desc;
        } else {
            $desc = '';
        }

	if ($this->ajaxPage == ""){
            $this->ajaxPage = "includes/FormGenerator/MultipleInput.tpl.php";
	}

        // set hidden form with old_value for each DeletableTrFormElement field
        // set a random old_value if some field has been created
        if($this->new) {
            $old_value = uniqid();
        }
        else if(isset($arrParam["value"])) {
            $old_value = $arrParam["value"];
        }
        else {
            $old_value = "";
        }
        if(is_object($this->template)) {
            $field_name = $this->template->name;
        }
        else if(is_array($this->template)) {
            $field_name = $this->template["name"];
        }
        else {
            $field_name = "";
        }
        if ($field_name) {
            print '<input type="hidden" name="old_'.$field_name.'" value="'.$old_value.'" />';
        }

        print '<tr><td width="40%" ';
        print displayErrorCss($this->cssErrorName);
        print 'style = "text-align: right;">';

        //if we got a tooltip, we show it
        if ($this->tooltip) {
            print "<a href=\"#\" class=\"tooltip\">".$desc."<span>".$this->tooltip."</span></a>";
        } else {
            print $desc;
        }
        print '</td><td>';

        // reald field display
        FormElement::display($arrParam);
        print '<input name="bdel" type="submit" class="btnSecondary" value="'._("Delete").'" onclick="
        new Ajax.Updater(\''.$this->name.'\',\''.$this->ajaxPage.'\',
        { parameters: Form.serialize($(\'' . $this->formId . '\'))+\'&amp;minputname='.$this->name.'&amp;del='.$this->key.'&amp;desc='.urlencode($this->desc) . '&amp;regexp='.rawurlencode($this->template->regexp) . '\' }); return false;"/>';

        print '</td></tr>';

    }

}

class DhcpRangeTpl extends InputTpl {

    function DhcpRangeTpl($name) {
        $this->name = $name;
    }

    function display($arrParam) {
	//print '<div id="div'.$this->name.'">';
        //print '<table cellspacing="0">';
        $i = 0;

        foreach (array("start"=>_T('IP range start: ',"network"),
    		       "end"=>_T('IP range end: ',"network")
    		       ) as $elemName=>$elemText) {
    	    $elem = new IPInputTpl($this->name."_".$elemName);
    	    $elem->setSize(12);
	    if (!isset($arrParam["value"][$elemName]))
		$arrParam["value"][$elemName]="";


	    print $elemText;
	    print "&nbsp;";
            $elem->display(array('value'=>$arrParam["value"][$elemName], 'onchange'=>'
                var elem = document.getElementById("'.$this->name.'");
                var range = elem.value;
                var part = '.$i.';
                var value = document.getElementById("'.$this->name.'_'.$elemName.'").value;
                var newrange = changeRangePart(range, part, value);
                elem.value = newrange;
            '));
            print "&nbsp;&nbsp;";
            $i += 1;
        }

        print '<input name="'.$this->name.'" id="'.$this->name.'" type="hidden" value="'.$this->stringValue($arrParam["value"]).'"/>';
        //print '</table>';
        //print '</div>';

        print '<script type="text/javascript">
                function changeRangePart(range, part, value) {
                    var re = new RegExp(" ", "g");
                    var arange = range.split(re);
                    arange[part] = value;
                    return arange[0] + " " + arange[1];
                }
               </script>';

    }

    private function stringValue($value, $format = "%s %s"){
	$start = $value["start"]?$value["start"]:"";
	$end = $value["end"]?$value["end"]:"";
	return sprintf($format,$start,$end);
    }


    static function valueFromDhcpRangeString($str){
	$result = array();
	$values = split(" ",$str);
	if (count($values)>=2){
	    $result["start"] = $values[0];
	    $result["end"] = $values[1];
	}
	return $result;
    }
    /*
    static function descriptionForDhcpRangeString($str){
	$result = "";
	$val = DhcpRangeTpl::valueFromDhcpRangeString($str);
	return $val["start"] . " -> " . $val["end"];
    }

    static function dhcpRangeStringFromValue($value){
	return $value["start"] . " " . $value["end"];
    }
    */

}



class ExtendedDateTpl extends InputTpl {
    function ExtendedDateTpl($name) {
        $this->name = $name;
    }
    function display($arrParam) {
        print '<div id="div'.$this->name.'">';
        //print '<table cellspacing="0">';

        $i = 0;
        foreach (array('year'=>array(_('Year: '), 4), 'month'=>array(_('Month: '), 2), 'day'=>array(_('Day: '), 2),
                'hour'=>array(_('Hour: '), 2), 'min'=>array(_('Min.: '), 2), 'sec'=>array(_('Sec.: '), 2)) as $elem=>$a_params) {
            $e = new InputTpl($this->name.'_'.$elem); //, array('value'=>$arrParam[$elem]));
            $e->setSize($a_params[1]);
            print $a_params[0];
	    $e->display(array('value'=>$arrParam["value"][$elem], 'onchange'=>'
                var elem = document.getElementById("'.$this->name.'");
                var date = elem.value;
                var part = '.$i.';
                var value = document.getElementById("'.$this->name.'_'.$elem.'").value;
                var newdate = changePartDate(date, part, value);
                elem.value = newdate;
            '));
            $i += 1;
        }
        print '<input name="'.$this->name.'" id="'.$this->name.'" type="hidden" value="' . $this->stringValue($arrParam["value"]) . '"/>';
        //print '</table>';
        print '</div>';


        print '<script type="text/javascript">
                function changePartDate(date, part, value) {
                    var re = new RegExp("/", "g");
                    var adate = date.split(re);
                    adate[part] = value;
                    return adate.join("/");
                }
               </script>';
    }

    private function stringValue($value, $format = "%s/%s/%s/%s/%s/%s"){
	$year = $value["year"]?$value["year"]:0;
	$month = $value["month"]?$value["month"]:0;
	$day = $value["day"]?$value["day"]:0;
	$hour = $value["hour"]?$value["hour"]:0;
	$min = $value["min"]?$value["min"]:0;
	$sec = $value["sec"]?$value["sec"]:0;
	return sprintf($format, $year, $month, $day, $hour, $min, $sec);
    }


}


class BindRemainingTimeTpl extends InputTpl {

    function BindRemainingTimeTpl($name) {
        $this->name = $name;
    }

    function display($arrParam) {
	print '<div id="div'.$this->name.'">';
        //print '<table cellspacing="0">';
        $i = 0;
        foreach (array('days'=>array(_T('Days: '), 4), 'hours'=>array(_T('Hours: '), 2),
        'minutes'=>array(_T('Minutes: '), 2), 'seconds'=>array(_T('Seconds: '), 2)) as $elem=>$a_params) {
            $e = new InputTpl($this->name.'_'.$elem,'/^\d*$/');
            $e->setSize($a_params[1]);
            print $a_params[0];
	    print "&nbsp;";

            $e->display(array('value'=>$arrParam["value"][$elem], 'onchange'=>'
                var elem = document.getElementById("'.$this->name.'");
                var date = elem.value;
                var part = '.$i.';
                var value = document.getElementById("'.$this->name.'_'.$elem.'").value;
                var newdate = changePartDate(date, part, value);
                elem.value = newdate;
            '));
            print "&nbsp;";

            $i += 1;
        }

        print '<input name="'.$this->name.'" id="'.$this->name.'" type="hidden" value="'.$this->stringValue($arrParam["value"]).'"/>';
        //print '</table>';
        print '</div>';

        print '<script type="text/javascript">
                function changePartDate(date, part, value) {
                    var re = new RegExp("/", "g");
                    var adate = date.split(re);
                    adate[part] = value;
                    return adate.join("/");
                }
               </script>';

    }

    private function stringValue($value, $format = "%s/%s/%s/%s"){
	$days = $value["days"]?$value["days"]:0;
	$hours = $value["hours"]?$value["hours"]:0;
	$minutes = $value["minutes"]?$value["minutes"]:0;
	$seconds = $value["seconds"]?$value["seconds"]:0;
	return sprintf($format,$days,$hours,$minutes,$seconds);
    }


    static function valueFromBindTimeString($str){
	$result = array();

	if (is_numeric($str)){
	    $t=$str;

	    $MaxDaysCount = 99999;
	    $times = array("seconds"=>60,"minutes"=>60,"hours"=>24, "days"=>$MaxDaysCount);

	    foreach ($times as $name => $maxval){
		$rem = $t % $maxval;
		$result[$name]=$rem?$rem:"";
		$t/=$maxval;
	    }
	    return $result;
	}

	if (preg_match("/(\d+W)?(\d+D)?(\d+H)?(\d+M)?(\d+S?)?/", $str, $matches)) {
	    $result["days"] = ($matches[1] || $matches[2]) ? intval(rtrim($matches[1],"W")) * 7 + intval(rtrim($matches[2],"D")) : "";
	    $result["hours"] = rtrim($matches[3],"H");
	    $result["minutes"] = rtrim($matches[4],"M");
	    $result["seconds"] = rtrim($matches[5],"S");
	}
        return $result;

    }

    static function descriptionForBindTimeString($str){
	$result = "";
	$val = BindRemainingTimeTpl::valueFromBindTimeString($str);
	$weeks = intval ($val["days"] / 7);
	if ($weeks)
	    $result .= $weeks . " " . _T("week(s)") ." ";
	$days = intval ($val["days"] % 7);
	if ($days)
	    $result .= $days . " " . _T("day(s)") . " ";
	if ($val["hours"])
	    $result .= $val["hours"] . " " . _T("hr") . " ";
	if ($val["minutes"])
	    $result .= $val["minutes"] . " " . _T("min") . " ";
	if ($val["seconds"])
	    $result .= $val["seconds"] . " " . _T("sec") . " ";
	return rtrim($result);


    }

    static function bindTimeStringFromValue($value, $inSeconds = false){
	$times = array(60*60*24,60*60, 60, 1);
	$values = split("/",$value);

	if ($inSeconds){
	    $result = 0;
	    for ($i=0; $i < count($values); $i++)
		$result += intval($values[$i]) * $times[$i];
	    return $result;
	}

	$result = "";
	if ($values[0]){
	    if (intval($values[0] / 7))
		$result .= strval(intval($values[0] / 7)) . "W";
	    if ($values[0] % 7)
		$result .= strval($values[0] % 7) . "D";
	}
	if ($values[1]) $result .= $values[1] . "H";
	if ($values[2]) $result .= $values[2] . "M";
	if ($values[3]) $result .= $values[3] . "S";

	return $result;
    }


}


class RecordError{
    const PARSE = "1";
}

class AllowedDn{
    const FQDN = 1;
    const RELATIVE = 2;
    const AT = 4;
    const ALL = 7;
}


class RecordBase {
    var $values;
    var $zone;
    var $type;
    var $hostname;
    var $errors = array();

    function RecordBase($config = array()){
	$this->zone = $config["zone"];
	$this->type = $config["type"];
	$this->hostname = $config["hostname"];
    }

    function initValuesFromArray($arr){
	foreach ($this->values as $k => $v){
	    if (isset($arr[$this->pn($k)]))
		$this->values[$k] = $arr[$this->pn($k)];
	}
	$this->hostname = $arr[$this->pn("hostname")];
    }

    function hostname(){
	return $this->hostname;
    }

    function initValuesFromString($str){
    }

    function check($zone = ""){
	return "";
    }

    function createUiContainers($editMode = False){
	return new Table();
    }

    function display($editMode = False){
	$uics = $this->createUiContainers($editMode);
	$cnt =  new Div();
	foreach ($uics as $uic){
	    if ($uic[2]){
		$cnt->add($uic[0][0],$uic[0][1]);
		continue;
	    }
	    $cnt->push($uic[0]);
	    for ($i=0; $i<$uic[1];$i++)
		$cnt->pop();
	}

	$cnt->display();
    }

    function pn($name){
	return "_". $this->type . "_" . $name;
    }

    function valuesToString(){
	return "";
    }

    function valuesToDescription(){
	return "";
    }

    function _createNameElement($name, $editable = True, $filter = "/^(@)|([a-z0-9](([a-z0-9-_])|(\.(?!\.)))*[a-z0-9]?)$/"){
	$ename = $this->pn("hostname");
	$e = ($editable) ? new InputTpl($ename, $filter) : new HiddenTpl($ename);
	return new TrFormElement($name, $e, $this->_dnRulesTooltip(AllowedDn::RELATIVE|AllowedDn::AT));
    }

    function _stringByKeys($keys){
	$params_str = "";
	foreach ($keys as $k)
	    $params_str .= $this->values[$k] . " ";
	$params_str = trim($params_str);
	return $params_str;
    }

    function _stringByValues($values){
    	$params_str = implode(" ",$values);
	return $params_str;
    }

    function _description($descrMap, $showedRowsCount=2, $byValues = False){
    	$result = "";
	$rowsCount = 0;
	$size = count($descrMap);
	foreach($descrMap as $descr => $obj){
	    $rowsCount++;
	    $result .= "<strong>" . $descr . "</strong>: " . (($byValues) ? $obj : $this->values[$obj]);
	    if ($rowsCount < $size){
		if ($rowsCount == $showedRowsCount)
		    $result .= " <a href=\"#\" class=\"tooltip\">" . _T("More...") . "<span>";
		else
		    $result .="<br>";
	    }
	}
	if ($rowsCount > $showedRowsCount)
	    $result .= "</span></a>";
	return $result;
    }

    function _descriptionByKeys($descrMap, $showedRowsCount = 2){
	return $this->_description($descrMap,$showedRowsCount, False);
    }

    function _descriptionByValues($descrMap, $showedRowsCount = 2){
	return $this->_description($descrMap,$showedRowsCount, True);
    }

    function stackedUi($ui, $popCount = 1, $needAdd = false){
	return array($ui, $popCount, $needAdd);
    }

    function _dnFromBindDn($bindDn){
	if (($bindDn === ".") || (substr($bindDn,-1) != "."))
	    return $bindDn;
	return substr($bindDn,0,-1);
    }

    function _dnToBindDn($dn){
	if (($dn === ".") || (strpos($dn,".") === false))
	    return $dn;
	return ($dn . ".");
    }

    function _dnRulesTooltip($type = AllowedDn::ALL){
	switch ($type) {
	    case (AllowedDn::ALL):
		return array("tooltip" => sprintf(_T("It should be relative name (e.g., %s), FQDN (e.g., %s) or %s"),
						    "<strong>host</strong>",
						    "<strong>host.zone.com.</strong>",
						    "<strong>@</strong>"
					    ));

	    case (AllowedDn::RELATIVE | AllowedDn::AT):
	    	return array("tooltip" => sprintf(_T("It should be relative name (e.g., %s) or %s"),
						    "<strong>host</strong>",
						    "<strong>@</strong>"
						    ));
	    case (AllowedDn::RELATIVE | AllowedDn::FQDN):
	    	return array("tooltip" => sprintf(_T("It should be relative name (e.g., %s) or FQDN (e.g., %s)"),
						    "<strong>host</strong>",
						    "<strong>host.zone.com.</strong>"
						    ));
	    case (AllowedDn::RELATIVE):
		return array("tooltip" => sprintf(_T("It should be relative name (e.g., %s)"),
						    "<strong>host</strong>"
					    ));
	    case (AllowedDn::FQDN):
		return array("tooltip" => sprintf(_T("It should be FQDN (e.g., %s)"),
						    "<strong>host.zone.com.</strong>"
					    ));


	}
    }

    function markError($errorType, $errorValue){
	$this->errors[$errorType] = $errorValue;
    }

    function errorsDescription(){
	$desc = "";
	if (array_key_exists(RecordError::PARSE,$this->errors))
	    $desc .= _T("Couldn't determine record parameters. Incorrect ldap data:") . " <b>" . $this->errors[RecordError::PARSE] . "</b>";
	return $desc;
    }

}



function supportedRecordsTypes($filter = "all"){
    $result = array();
    $all = array("A","CNAME","TXT", "MX","NS","PTR","SOA","SRV","AFSDB","SIG","KEY","AAAA","LOC","NAPTR","KX", "CERT", "DNAME", "DS","SSHFP", "RRSIG", "NSEC", _T("custom","network"));
    //$addable = array("A","CNAME","TXT", "MX","NS","PTR", "SRV", "custom");
    $reverse = array("PTR","SRV", "NS", "TXT", "DNAME", "NAPTR",  _T("custom","network"));
    $direct = array("A", "CNAME", "SRV", "TXT", "MX", "NS", "AFSDB", "SIG","KEY","AAAA","LOC", "NAPTR", "KX", "CERT", "DNAME","DS","SSHFP", "RRSIG","NSEC", _T("custom","network"));

    //$clean_array = array_filter($all, "canAdd");

    switch ($filter){
	case "all":
	    $result = $all;
	    break;
	case "addable":
	    $result = $addable;
	    break;
	case "reverse":
	    $result = $reverse;
	    break;
	case "direct":
	    $result = $direct;
	    break;
    }

    return $result;
}

?>
