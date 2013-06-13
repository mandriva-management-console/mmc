<?php

class sigRecord extends RecordBase{
    
    function sigRecord($config = array()){
	$this->RecordBase($config);
        $this->values["type"] = "srv";
	$this->values["algorithm"] = "1";
	$this->values["labels"] = "2";
	$this->values["ttl"] = "86400";
	$this->values["expiration"] = "";
	$this->values["inception"] = date("Ymd") . "000000";
	$this->values["keytag"] = "";
	$this->values["signer"] = "";
	$this->values["signature"] = "";
    }

    function check($zone = ""){
	$error = "";
	if (strlen($this->values["type"]) == 0)
	    $error .= _T("Custom type is no setted") . "<br>";
	if (strlen($this->values["algorithm"]) == 0)
	    $error .= _T("Custom algorithm is no setted") . "<br>";
	if (!$this->checkBindTime($this->values["inception"]))
	    $error .= _T("Signature inception time is incorrect") . "<br>";
	if (!$this->checkBindTime($this->values["expiration"]))
	    $error .= _T("Signature expiration time is incorrect") . "<br>";
	if (strlen($this->values["signature"]) == 0)
	    $error .= _T("Signature is no setted") . "<br>";
	else 
	    if (strlen($this->values["signature"]) % 4 > 0)
		$error .= _T("Signature is incorrect") . "<br>";

	return $error;
    }
    
    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	
	$directSettedItems = array("labels", "keytag", "signer", "signature");
	foreach ($directSettedItems as $i)
	    if (isset($arr[$this->pn($i)])) 
		$this->values[$i] = $arr[$this->pn($i)];
	
	$recordTypes = supportedRecordsTypes("all");
	$this->values["type"] = ($arr[$this->pn("type")] == count($recordTypes) - 1) ? 
					$arr[$this->pn("customtype")] : $recordTypes[$arr[$this->pn("type")]];

        $algorithmsMap = $this->algorithms();
        $algorithms = array_values($algorithmsMap);
	$this->values["algorithm"] = ($arr[$this->pn("algorithm")] == count($algorithms) - 1) ? 
					$arr[$this->pn("customalgorithm")] : 
					array_search($algorithms[$arr[$this->pn("algorithm")]], $algorithmsMap);
	
	$this->values["ttl"] = BindRemainingTimeTpl::bindTimeStringFromValue($arr[$this->pn("ttl")],true);
	$this->values["inception"] = $this->dateTplTimeToBindTime($arr[$this->pn("inception")]);
	$this->values["expiration"] = $this->dateTplTimeToBindTime($arr[$this->pn("expiration")]);
		

    }

        
    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 7){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}

	$this->values["type"] = $values[0];
	$this->values["algorithm"] = $values[1];
	$this->values["labels"] = $values[2];
	$this->values["ttl"] = $values[3];
	$this->values["expiration"] = $values[4];
	$this->values["inception"] = $values[5];
	$this->values["keytag"] = $values[6];
	$this->values["signer"] = $values[7];
	//$this->values["signer"] = $this->_dnFromBindDn($values[7]);
	$this->values["signature"] = $values[8];
    }

    function createUiContainers(){
                                                                                         
	$recordTypes = supportedRecordsTypes("all");
	$typeIndex = array_search(strtoupper($this->values["type"]),$recordTypes);
	if ($typeIndex === false){
    	    $typeIndex = count($recordTypes)-1;
            $customType = $this->values["type"];
        } else {
            $customType = "";
            //$typeIndex = 0;
        }
	$isCustomType = ($typeIndex == (count($recordTypes) - 1)) ? "checked" : ""; 
	
	$algorithmsMap = $this->algorithms();
	$algorithms = array_values($algorithmsMap);
	if (isset($algorithmsMap[$this->values["algorithm"]])){
	    $algorithmIndex = array_search($algorithmsMap[$this->values["algorithm"]], $algorithms);
	    $customAlgorithm = "";
	} else {
	    $algorithmIndex = count($algorithms) - 1;
	    $customAlgorithm = $this->values["algorithm"];
	}
	$isCustomAlgorithm = ($algorithmIndex == (count($algorithms) - 1)) ? "checked" : ""; 

	
        $typeComboBox = new ExtendedSelectItem($this->pn("type"));
 	$typeComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("typediv"). "',state);\"");
	$typeComboBox->setElements(array_values($recordTypes));
	$typeComboBox->setElementsVal(array_keys($recordTypes));
	
	$algorithmComboBox = new ExtendedSelectItem($this->pn("algorithm"));
 	$algorithmComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("algorithmdiv"). "',state);\"");
	$algorithmComboBox->setElements(array_values($algorithms));
	$algorithmComboBox->setElementsVal(array_keys($algorithms));
		
	$signatureTextarea = new TextareaTpl($this->pn("signature"));
	$signatureTextarea->setCols(43);
	
	
	$t1 = new Table();
	$t1->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));
	$t1->add(new TrFormElement(	_T("Domain name of the signer, generating this SIG"), 
					new InputTpl($this->pn("signer")),
					 $this->_dnRulesTooltip()),
		array("value" => $this->values["signer"], "required" => True));
	$t1->add(new TrFormElement(_T("Record type covered by this SIG"), $typeComboBox),
		array("value"=>$typeIndex));
	
	$typeDiv = new Div(array("id" => $this->pn("typediv")));
        $typeDiv->setVisibility($isCustomType);
	
	$t2 = new Table();
	$t2->add(new TrFormElement(_T("Custom record type"), new InputTpl($this->pn("customtype"),'/\w+/')),
		array("value"=>$customType));
		
	$t3 = new Table();
			
	$t3->add(new TrFormElement(_T("Algorithm"), $algorithmComboBox),
		array("value"=>$algorithmIndex));
		
	$algorithmDiv = new Div(array("id" => $this->pn("algorithmdiv")));
        $algorithmDiv->setVisibility($isCustomAlgorithm);
	
	$t4 = new Table();
	$t4->add(new TrFormElement(
		    _T("Custom algorithm"), 
		    new InputTpl($this->pn("customalgorithm"), '/^([01]?\d?\d|2[0-4]\d|25[0-5])$/'), 
		    array("tooltip" => _T("Algorithm ranges from 0 to 255"))
		    ),
		 array("value"=>$customAlgorithm));
	
	$t5 = new Table();
	$t5->add(new TrFormElement(
		    _T("Labels"), 
		    new InputTpl($this->pn("labels"), '/^([01]?\d?\d|2[0-4]\d|25[0-5])$/'),
		    array("tooltip" => _T("Define an unsigned count of how many labels there are in the original SIG record owner name not counting the null label for root and not counting any initial \"*\" for a wildcard.") . "<br>" .
		        		   _T("Labels count ranges from 0 to 255"))
		    ),
		array("value" => $this->values["labels"], "required" => True));
		
	$t5->add(new TrFormElement(_T("Original TTL"), new BindRemainingTimeTpl($this->pn("ttl"))),
		array("value"=>BindRemainingTimeTpl::valueFromBindTimeString($this->values["ttl"])));
	$t5->add(new TrFormElement(_T("Signature inception time"), new ExtendedDateTpl($this->pn("inception"))),
		array("value"=>$this->bindTimeToDateTplTime($this->values["inception"])));
	$t5->add(new TrFormElement(_T("Signature expiration time"), new ExtendedDateTpl($this->pn("expiration"))),
		array("value"=>$this->bindTimeToDateTplTime($this->values["expiration"])));
	$t5->add(new TrFormElement(
		    _T("Key tag"), 
		    new InputTpl($this->pn("keytag"), '/^([0-5]?\d?\d?\d?\d|6[0-4]\d\d\d|65[0-4]\d\d|655[0-2]\d|6553[0-5])$/'),
		    array("tooltip" => _T("Key tag is a decimal number that ranges from 0 to 65535"))
		    ),
		array("value" => $this->values["keytag"], "required" => True));
	$t5->add(new TrFormElement(_T("Signature"), $signatureTextarea),
		array("value" => $this->values["signature"]));
		
	
	return array($this->stackedUi($t1), $this->stackedUi($typeDiv,0), $this->stackedUi($t2,2),
		     $this->stackedUi($t3), $this->stackedUi($algorithmDiv,0), $this->stackedUi($t4,2),
		     $this->stackedUi($t5));
	
    }
    
    function valuesToString(){
	//return $this->stringByKeys(array("priority", "mailserver"));
	return $this->_stringByValues(array($this->values["type"], 
					    $this->values["algorithm"],
					    $this->values["labels"],
					    $this->values["ttl"],
					    $this->values["expiration"],
					    $this->values["inception"],
					    $this->values["keytag"],
					    $this->values["signer"],
					    //$this->_dnToBindDn($this->values["signer"]),
					    $this->values["signature"]
			                   ) );
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();
	$descrMap = array(
			_T("Record type covered by this SIG") => $this->values["type"],
			_T("Algorithm") => $this->algorithmDescription($this->values["algorithm"]),
			_T("Labels") => $this->values["labels"],
			_T("Original TTL") => BindRemainingTimeTpl::descriptionForBindTimeString($this->values["ttl"]),
			_T("Signature inception time") => $this->timeDescription($this->values["inception"]),
			_T("Signature expiration time") => $this->timeDescription($this->values["expiration"]),
			_T("Key tag") => $this->values["keytag"],
			_T("Domain name of the signer, generating this SIG") => $this->values["signer"],
			_T("Signature") => $this->signatureDescription($this->values["signature"]));
	return $this->_descriptionByValues($descrMap);
    }
    
    function algorithms(){
	$algorithms = array("1" => _T("RSA/MD5"), 
			    "2" => _T("Diffie-Hellman"),
			    "3" => _T("DSA"),
			    "4" => _T("Elliptic curve crypto"),
			    "252" => _T("Indirect key format"),
			    "253" => _T("Domain name (Private)"),
			    "254" => _T("OID (Private)"),
			    "256" => _T("custom")
			   );
	return $algorithms;
    }
    
    function algorithmDescription($alg){
	$descr=_T("Unknown") . " (" . $alg . ")";
	$algs = $this->algorithms();
	if (in_array($alg, array_keys($algs)))
	    $descr = $algs[$alg];
	return $descr;
    }
    
    function parseBindTime($time){
	$result["year"] = ltrim(substr($time,0,4),"0");
	$result["month"] = ltrim(substr($time,4,2),"0");
	$result["day"] = ltrim(substr($time,6,2),"0");
	$result["hour"] = ltrim(substr($time,8,2),"0");
	$result["min"] = ltrim(substr($time,10,2),"0");
	$result["sec"] = ltrim(substr($time,12,2),"0");
	return $result;
    }
    
    function checkBindTime($time){
	$d = $this->parseBindTime($time);

	$year = intval($d["year"]);
	$month = intval($d["month"]);
	$day = intval($d["day"]);
	$hour = intval($d["hour"]);
	$min = intval($d["min"]);
	$sec = intval($d["sec"]);
	
	return (checkdate($month, $day, $year) &&  $hour >= 0 && $hour < 24 && $min >=0 && $min < 60 && $sec >=0 && $sec < 60);
    }
    
    
    function timeDescription($time){
	$d = $this->parseBindTime($time);
	return sprintf("%04s.%02s.%02s %02s:%02s:%02s", $d["year"], $d["month"], $d["day"], $d["hour"], $d["min"], $d["sec"]);	
    }
    
    function signatureDescription($signature){
	//$search = array("\x00", "\x0a", "\x0d", "\x1a", "\x09");
	//$replace = array('\0', '\n', '\r', '\Z' , '\t');
	//$description = str_replace($search, $replace, $signature);
	$description = wordwrap($signature, 32, "<br>", true);
	return $description;
    }
    
    function bindTimeToDateTplTime($time){
	$times = $this->parseBindTime($time);
	foreach ($times as $t)
	    $t = ltrim($t,"0");
	return $times;
    }
    
    function dateTplTimeToBindTime($time){
	$result = "";
	$times = split("/", $time);
	$result = sprintf("%04s%02s%02s%02s%02s%02s",$times[0], $times[1], $times[2], $times[3], $times[4], $times[5]);
	/*$sizes = array(4, 2, 2, 2, 2, 2);
	for ($i = 0; $i < count($t); $i++){
	    $result .= str_pad($times[$i], $sizes[$i], "0", STR_PAD_LEFT);
	}*/
	return $result;
    }
    
}

?>

