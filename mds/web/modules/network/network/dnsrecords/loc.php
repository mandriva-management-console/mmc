<?php


class locRecord extends RecordBase{
    
    function locRecord($config = array()){
	$this->recordBase($config);
	$this->values["latitude"]["deg"]="";
	$this->values["latitude"]["min"]="";
	$this->values["latitude"]["sec"]="";
	$this->values["latitude"]["dir"]="N";
	
	$this->values["longitude"]["deg"]="";
	$this->values["longitude"]["min"]="";
	$this->values["longitude"]["sec"]="";
	$this->values["longitude"]["dir"]="W";
	
	$this->values["altitude"]["value"]="";
	$this->values["altitude"]["unit"]="m";
	$this->values["size"]["base"]="";
	$this->values["size"]["deg"]="";
	$this->values["size"]["unit"]="m";
	
	$this->values["hprecision"]["value"]="";
	$this->values["hprecision"]["unit"]="m";
	$this->values["vprecision"]["value"]="";
	$this->values["vprecision"]["unit"]="m";

    }

    function check($zone = ""){
	if (strpos($this->hostname,"..") !== false)
	    return _T("Host name is incrorrect");
	return "";
	
    }

    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	$this->values["latitude"] = GlobalLocationTpl::arrayValue($arr[$this->pn("latitude")]);
	$this->values["longitude"] = GlobalLocationTpl::arrayValue($arr[$this->pn("longitude")]);
	$this->values["altitude"] = MeasureInputTpl::arrayValue($arr[$this->pn("altitude")]);
	$this->values["size"] = SizeMeasureInputTpl::arrayValue($arr[$this->pn("size")]);
	$this->values["hprecision"] = MeasureInputTpl::arrayValue($arr[$this->pn("hprecision")]);
	$this->values["vprecision"] = MeasureInputTpl::arrayValue($arr[$this->pn("vprecision")]);
    }

    function initValuesFromString($str){
	$parser = new LocDataParser();
	if ($parser->parse($str)==-1){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}
	$this->values = $parser->result();

    }


    function createUiContainers($editMode = false){

	$units = array("cm"=>_T("cm"), "m"=>_T("m"));
	
	$altRegExp = "/^((-([0-9]{0,5}(\.[0-9]{0,2})?|100000))|(([0-3]?[0-9]{0,7}|4[01][0-9]{0,6})(\.[0-9]{0,2})?|420{6}))$/";
	$precRegExp = "/^([0-8][0-9]{0,7}(\.[0-9]{0,2})?|90{7})$/";

	$t = new Table();
	
	$t->add($this->_createNameElement(_T("Host name")),
		array("value" => $this->hostname,/* "extra" => "." . $this->zone,*/ "required" => True));
	$t->add(new TrFormElement(_T("Latitude"), new GlobalLocationTpl($this->pn("latitude"),GlobalLocationTpl::LATITUDE)),
		array("value"=>$this->values["latitude"], "required"=>true));
	$t->add(new TrFormElement(_T("Longitude"), new GlobalLocationTpl($this->pn("longitude"),GlobalLocationTpl::LONGITUDE)),
		array("value"=>$this->values["longitude"], "required"=>true));
	$t->add(new TrFormElement(_T("Altitude"), new MeasureInputTpl($this->pn("altitude"),$units,9,$altRegExp)),
		array("value"=>$this->values["altitude"]));
	$t->add(new TrFormElement(_T("Diameter of a sphere enclosing the described entity"), new SizeMeasureInputTpl($this->pn("size"),$units)),
		array("value"=>$this->values["size"]));
	$t->add(new TrFormElement(_T("Horizontal precision"), new MeasureInputTpl($this->pn("hprecision"),$units,8, $precRegExp)),
		array("value"=>$this->values["hprecision"]));
	$t->add(new TrFormElement(_T("Vertical precision"), new MeasureInputTpl($this->pn("vprecision"),$units,8, $precRegExp)),
		array("value"=>$this->values["vprecision"]));


	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	$values = array();
	
	foreach (array("latitude","longitude") as $loc){
	    $values[] = intval($this->values[$loc]["deg"]);
	    if (intval($this->values[$loc]["sec"])){
		$values[] = intval($this->values[$loc]["min"]);
		$values[] = intval($this->values[$loc]["sec"]);
	    } else {
		if (intval($this->values[$loc]["min"])){
		    $values[] = intval($this->values[$loc]["min"]);
		}
	    }
	    $values[] = $this->values[$loc]["dir"];
	}
	
	$values[] = $this->values["altitude"]["value"] . (($this->values["altitude"]["unit"] == "m") ? "m":"");
	
	$size = strval(intval($this->values["size"]["base"])) . strval(intval($this->values["size"]["deg"])) . 
			(($this->values["size"]["unit"] == "m") ? "m":"");
	$hprecision = strval(floatval($this->values["hprecision"]["value"])) . (($this->values["hprecision"]["unit"] == "m") ? "m":"");
	$vprecision = strval(floatval($this->values["vprecision"]["value"])) . (($this->values["vprecision"]["unit"] == "m") ? "m":"");

	if (floatval($this->values["vprecision"]["value"])){
	    $values[] = $size;
	    $values[] = $hprecision;
	    $values[] = $vprecision;
	} else 
	    if (floatval($this->values["hprecision"]["value"])){
	        $values[] = $size;
		$values[] = $hprecision;
	    }
	    else
		if (intval($this->values["size"]["base"]) || intval($this->values["size"]["deg"])){
		    $values[] = $size;
		}
		
	return $this->_stringByValues($values);
    }

    function measureDescription($name){
	if (isset($this->values[$name]["value"]) && ($this->values[$name]["value"]!=="0"))
	    return sprintf("%s %s",$this->values[$name]["value"], ($this->values[$name]["unit"] === "cm") ? _T("cm") : _T("m"));
    }
    
    function coordinateDescription($name){
	$directions = GlobalLocationTpl::directions(GlobalLocationTpl::typeByString($name));

	$result = intval($this->values[$name]["deg"]) . "°";
	if (intval($this->values[$name]["min"]))// && ($this->values[$name]["min"]!=="0"))
	    $result .= " " . intval($this->values[$name]["min"]) . "′";
	if (intval($this->values[$name]["sec"]))
	    $result .= " " . intval($this->values[$name]["sec"]) . "′′";
	
	$result .= " " . $directions[$this->values[$name]["dir"]];
	

	return $result;
    }

    function sizeDescription(){
	$val = intval($this->values["size"]["base"]) * pow(10, intval($this->values["size"]["deg"]));
	return sprintf("%s %s",$val, ($this->values["size"]["unit"] === "cm") ? _T("cm") : _T("m"));
    }

    function valuesToDescription(){
	$descrMap = array(
			_T("Latitude") => $this->coordinateDescription("latitude"),
			_T("Longitude") => $this->coordinateDescription("longitude"),
			_T("Altitude") => $this->measureDescription("altitude")
			);
	if (intval($this->values["size"]["base"]))
	    $descrMap[_T("Diameter of a sphere enclosing the described entity")] = $this->sizeDescription();
	if (intval($this->values["hprecision"]["value"]))
	    $descrMap[_T("Horizontal precision")] = $this->measureDescription("hprecision");
	if (intval($this->values["vprecision"]["value"]))
	    $descrMap[_T("Vertical precision")] = $this->measureDescription("vprecision");
	
	return $this->_descriptionByValues($descrMap);
    }

    
}

class LocDataParser{
    var $values = array();

    function LocDataParser(){
	$this->values["latitude"]["deg"]="";
	$this->values["latitude"]["min"]="";
	$this->values["latitude"]["sec"]="";
	$this->values["latitude"]["dir"]="N";
	
	$this->values["longitude"]["deg"]="";
	$this->values["longitude"]["min"]="";
	$this->values["longitude"]["sec"]="";
	$this->values["longitude"]["dir"]="W";
	
	$this->values["altitude"]["value"]="";
	$this->values["altitude"]["unit"]="m";
	$this->values["size"]["base"]="";
	$this->values["size"]["deg"]="";
	$this->values["size"]["unit"]="m";
	
	$this->values["hprecision"]["value"]="";
	$this->values["hprecision"]["unit"]="m";
	$this->values["vprecision"]["value"]="";
	$this->values["vprecision"]["unit"]="m";
    }
    
    function parse($data){
	$values = split(" ", $data);
	
	$pos = $this->parseCoordinate("latitude", $values, 0);
	if ($pos == -1)
	    return -1;
	$pos = $this->parseCoordinate("longitude", $values, $pos+1);
	if ($pos == -1)
	    return -1;

	if (!$this->parseMeasure("altitude",$values[$pos + 1]))
	    return -1;
	//extra parameters of record. can be ignored.
	if ($pos + 2 < count($values))
	    $this->parseSize($values[$pos + 2]);
	if ($pos + 3 < count($values))
	    $this->parseMeasure("hprecision",$values[$pos + 3]);
	if ($pos + 4 < count($values))
	    $this->parseMeasure("vprecision",$values[$pos + 4]);
	return 0;
    
    }
    function result(){
	return $this->values;
    }

    function parseCoordinate($coord, $arr, $pos){
	$measures = array("deg","min","sec","dir");
	$curpos = $pos;
	$arrLength = count($arr);
	foreach ($measures as $m){
	    //element index out of array
	    if ($curpos + 1 > $arrLength)
		return -1;
	    if (preg_match("/[NSWE]/i",$arr[$curpos])){
		$this->values[$coord]["dir"] = strtoupper($arr[$curpos]);
		//direction is the first parameter
		if ($pos == $curpos)
		    return -1;
		return $curpos;
	    }
	    $this->values[$coord][$m] = $arr[$curpos];
	    $curpos++;
	}
	//direction could not be found
	return -1;
    }
    
    function parseMeasure($name, $value){
	if (!isset($value)) return false;
	$unit = substr($value,-1,1);
	if ($unit === "m" || $unit === "M"){
	    $this->values[$name]["unit"] = "m";
	    $this->values[$name]["value"] = substr($value,0,-1);
	    return true;
	}
	$this->values[$name]["unit"] = "cm";
	$this->values[$name]["value"] = $value;
	return true;
    }

    function parseSize($value){
        $this->values["size"]["base"] = substr($value,0,1);
        if (strlen($value) == 1){
    	    $this->values["size"]["unit"] = "cm";
    	    return;
    	}
    	
        if (strlen($value) == 2){
    	    $tmp = substr($value,1,1);
	    if ($tmp === "m" || $tmp === "M")
		$this->values["size"]["unit"] = "m";
	    else {
		$this->values["size"]["unit"] = "cm";
		$this->values["size"]["deg"] = $tmp;
	    }
	    return;
        }

	$this->values["size"]["deg"] = substr($value,1,1);
	$this->values["size"]["unit"] = (strlen($value) == 3) ? "m" : "cm";
    }
    
    
}

class GlobalLocationTpl extends InputTpl{
    var $type;
    const LATITUDE = true;
    const LONGITUDE = false;
    
    function GlobalLocationTpl($name, $type) {
        $this->name = $name;
        $this->type = $type;
    }
    function display($arrParam) {
        print '<div id="div'.$this->name.'">';
        //print '<table cellspacing="0">';
        $i = 0;
	
        $degRegExp = ($this->type == LATITUDE) ? "/^([0-8]?[0-9]|90)$/" : "/^([01]?[0-7]?[0-9]|[89]?[0-9]|180)$/";
        $minRegExp = "/^([0-5]?[0-9])$/";
        $secRegExp = "/^([0-5]?[0-9](\.[0-9]?[0-9]?[0-9])?)$/";
        foreach (array(
    			'deg'=>array(_T('Degr.: '), 3, $degRegExp), 
    			'min'=>array(_T('Min.: '), 2, $minRegExp), 
    			'sec'=>array(_T('Sec.: '), 5, $secRegExp)
    		      ) as $elem=>$a_params) {
            $e = new InputTpl($this->name.'_'.$elem, $a_params[2]); //, array('value'=>$arrParam[$elem]));
            $e->setSize($a_params[1]);
            print $a_params[0];
	
	    $e->display(array('value'=>intval($arrParam["value"][$elem]) > 0 ? intval($arrParam["value"][$elem]) : "", 'onchange'=>'
                var elem = document.getElementById("'.$this->name.'");
                var loc = elem.value;
                var part = '.$i.';
                var value = document.getElementById("'.$this->name.'_'.$elem.'").value;
                var newloc = changeLocPart(loc, part, value);
                elem.value = newloc;
            '));
            $i += 1;
        }
        
        $this->displayDirection($arrParam["value"]["dir"]);
        print '<input name="'.$this->name.'" id="'.$this->name.'" type="hidden" value="' . $this->stringValue($arrParam["value"]) . '"/>';

        print '</div>';


        print '<script type="text/javascript">
                function changeLocPart(loc, part, value) {
                    var re = new RegExp("/", "g");
                    var aloc = loc.split(re);
                    aloc[part] = value;
                    return aloc.join("/");
                }
               </script>';
    }

    function displayDirection($dir){
	$onchange = 'onchange = \'
		var elem = document.getElementById("'.$this->name.'");
                var loc = elem.value;
                var elems = document.getElementsByName("'.$this->name.'_dir");
                var len = elems.length;
                var value="";
                for (i = 0; i <len; i++) {
            	    if (elems[i].checked) {
            		value = elems[i].value;
			break;            		
            	    }
            	}
                var newloc = changeLocPart(loc, 3, value);
                elem.value = newloc;
                \'';
                
	foreach ($this->directions($this->type) as $k => $v){
	    $checked = ($k === $dir) ? "checked" : "";
	    print '<input type=radio name="' . $this->name . '_dir"  value="' . $k . '" '. $checked . ' ' . $onchange .'>' . $v;
	}	    
	
    }
    
    
    static function directions($type){
	return ($type == LATITUDE) ? array('N' => _T('N'),'S' => _T('S')) : array('W' => _T('W'),'E' => _T('E'));
    }
    
    static function typeByString($strtype){
	return ($strtype === "latitude") ? LATITUDE : LONGITUDE;
    }

    private function stringValue($arrValue, $format = "%s/%s/%s/%s"){
	$deg = $arrValue["deg"]?$arrValue["deg"]:0;
	$min = $arrValue["min"]?$arrValue["min"]:0;
	$sec = $arrValue["sec"]?$arrValue["sec"]:0;
	$dirs = array_keys($this->directions());
	$dir = $arrValue["dir"]?$arrValue["dir"]:$dirs[0];
	return sprintf($format, $deg, $min, $sec, $dir);
    }
    
    static function arrayValue($strValue){
	$value = split("/", $strValue);
	$keys = array("deg","min","sec","dir");
	foreach($keys as $k => $v)
	    $result[$v]=$value[$k];
	return $result;
    }

}


class MeasureInputTpl extends InputTpl{
    var $units;
    
    function MeasureInputTpl($name, $units=array(), $size=23, $regExp="/.+/"){
	$this->name = $name;
	$this->units = $units;
	$this->size = $size;
	$this->regexp = $regExp;
    }

    function display($arrParam){
        print '<div id="div'.$this->name.'">';

        //print '<table cellspacing="0">';

        $e = new InputTpl($this->name.'_value', $this->regexp); //, array('value'=>$arrParam[$elem]));
        $e->setSize($this->size);
	$e->display(array('value'=>floatval($arrParam["value"]["value"]) != 0 ? floatval($arrParam["value"]["value"]) : "", 
			  'onchange'=>'
                var elem = document.getElementById("'.$this->name.'");
                var data = elem.value;
                var value = document.getElementById("'.$this->name .'_value").value;
                var newdata = changePart(data,0,value);
                elem.value = newdata;'));
        
        $onchange = 'onchange = \'
		var elem = document.getElementById("'.$this->name.'");
                var data = elem.value;
                var elems = document.getElementsByName("'.$this->name.'_unit");
                
                var len = elems.length;
                var value="";
                for (i = 0; i <len; i++) {
            	    if (elems[i].checked) {
            		value = elems[i].value;
            		break;            		
            	    }
            	}
                var newdata = changePart(data, 1, value);
                elem.value = newdata;
        	
                \'';
                
	foreach ($this->units as $k => $v){
	    $checked = ($k === $arrParam["value"]["unit"]) ? "checked" : "";
	    print '<input type=radio name="' . $this->name . '_unit"  value="' . $k . '" '. $checked . ' ' . $onchange .'>' . $v;

	}	    

        print '<input name="'.$this->name.'" id="'.$this->name.'" type="hidden" value="' . $this->stringValue($arrParam["value"]) . '"/>';

        print '</div>';


        print '<script type="text/javascript">
                function changePart(data, part, value) {
                    var re = new RegExp("/", "g");
                    var adata = data.split(re);
                    adata[part] = value;
                    return adata.join("/");
                }
               </script>';
    }

    private function stringValue($valueArr, $format = "%s/%s"){
	$value = $valueArr["value"]?$valueArr["value"]:0;
	$units = array_keys($this->units);
	$unit = $valueArr["unit"] ? $valueArr["unit"]:$units[0];
	return sprintf($format, $value, $unit);
    }
    
    static function arrayValue($strValue){
	$value = split("/", $strValue);
	$keys = array("value","unit");
	foreach($keys as $k => $v)
	    $result[$v]=$value[$k];
	return $result;
    }

    

}

class SizeMeasureInputTpl extends InputTpl{

    var $units;


    function SizeMeasureInputTpl($name, $units=array()){
	$this->name = $name;
	$this->units = $units;
    }

    function display($arrParam){
        print '<div id="div'.$this->name.'">';

        //print '<table cellspacing="0">';
        $re = "/^\d$/";
        $i = 0;
        foreach (array( 'base'=>'', 'deg'=>''. " * 10 ^ ") as $k => $v){
    	    $e = new CustomInputTpl($this->name."_".$k,$re);
    	    $e->setSize(1);
    	    print $v;
	    $e->display(array('value'=>intval($arrParam["value"][$k]) > 0 ? intval($arrParam["value"][$k]) : "", 
	        'onchange'=>'
                var elem = document.getElementById("'.$this->name.'");
                var data = elem.value;
                var part = '.$i.';
                var value = document.getElementById("'.$this->name.'_'.$k.'").value;
                var newdata = changePart(data, part, value);
                elem.value = newdata;',
                'onkeyup'=>'
            	    isInt1 = function( s ) {return !isNaN( parseInt( s )) && s>=0 && s<10 || s==""; }
            	    var base = document.getElementById("'.$this->name.'_base").value;
            	    var deg = document.getElementById("'.$this->name.'_deg").value;
            	    var result = "'._T("incorrect") .'";
            	    if (isInt1(base) && isInt1(deg))
            	   	result = base * Math.pow(10,deg);
            	    document.getElementById("' . $this->name.'_result").innerHTML = result;'
            
            ));
            $i += 1;
        
    
    	}
        print " = ";
	//print '<div id="div'.$this->name.'result">';
	$base = intval($arrParam["value"]["base"]);
	$deg = intval($arrParam["value"]["deg"]);
	print '<b id="' . $this->name.'_result">' . strval($base * pow(10, $deg)) . "</b>";
	
	//print '</div>';
        
        $onchange = 'onchange = \'
		var elem = document.getElementById("'.$this->name.'");
                var data = elem.value;
                var elems = document.getElementsByName("'.$this->name.'_unit");
                
                var len = elems.length;
                var value="";
                for (i = 0; i <len; i++) {
            	    if (elems[i].checked) {
            		value = elems[i].value;
            		break;            		
            	    }
            	}
                var newdata = changePart(data, 2, value);
                elem.value = newdata;
        	
                \'';
                
	foreach ($this->units as $k => $v){
	    $checked = ($k === $arrParam["value"]["unit"]) ? "checked" : "";
	    print '<input type=radio name="' . $this->name . '_unit"  value="' . $k . '" '. $checked . ' ' . $onchange .'>' . $v;

	}	    

        print '<input name="'.$this->name.'" id="'.$this->name.'" type="hidden" value="' . $this->stringValue($arrParam["value"]) . '"/>';

        print '</div>';


        print '<script type="text/javascript">
                function changePart(data, part, value) {
                    var re = new RegExp("/", "g");
                    var adata = data.split(re);
                    adata[part] = value;
                    return adata.join("/");
                }
               </script>';
    }




    private function stringValue($valueArr, $format = "%s/%s/%s"){
	$value = $valueArr["base"]?$valueArr["base"]:0;
	$deg = $valueArr["deg"]?$valueArr["deg"]:0;
	$units = array_keys($this->units);
	$unit = $valueArr["unit"] ? $valueArr["unit"]:$units[0];
	return sprintf($format, $value, $deg, $unit);
    }

    static function arrayValue($strValue){
	$value = split("/", $strValue);
	$keys = array("base","deg","unit");
	foreach($keys as $k => $v)
	    $result[$v]=$value[$k];
	return $result;
    }



}

class CustomInputTpl extends InputTpl{
    function CustomInputTpl($name, $re){
	$this->InputTpl($name,$re);
    }
    function display($arrParam){
	parent::display($arrParam);
	print '<script type="text/javascript">';
	if (isset($arrParam["onkeyup"])) {
	    print '$(\''.$this->name.'\').onkeyup = function() {' . $arrParam["onkeyup"] . '};';
	}
	print '</script>';
    }
}



?>
