<?php

class certRecord extends RecordBase{
    
    function certRecord($config = array()){
	$this->RecordBase($config);
        $this->values["type"] = "1";
	$this->values["algorithm"] = "1";
	$this->values["keytag"] = "";
	$this->values["certificate"] = "";
    }

    function check($zone = ""){
	$error = "";
	if (strlen($this->values["type"]) == 0)
	    $error .= _T("Custom type is no setted") . "<br>";
	if (strlen($this->values["algorithm"]) == 0)
	    $error .= _T("Custom algorithm is no setted") . "<br>";
	if (strlen($this->values["certificate"]) == 0)
	    $error .= _T("Certificate is no setted") . "<br>";
	else 
	    if (strlen($this->values["certificate"]) % 4 > 0)
		$error .= _T("Certificate is incorrect") . "<br>";

	return $error;
    }
    
    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	
	$directSettedItems = array("keytag", "certificate");
	foreach ($directSettedItems as $i)
	    if (isset($arr[$this->pn($i)])) 
		$this->values[$i] = $arr[$this->pn($i)];
	
        $algorithmsMap = $this->algorithms();
        $algorithms = array_values($algorithmsMap);
	$this->values["algorithm"] = ($arr[$this->pn("algorithm")] == count($algorithms) - 1) ? 
					$arr[$this->pn("customalgorithm")] : 
					array_search($algorithms[$arr[$this->pn("algorithm")]], $algorithmsMap);

        $typesMap = $this->types();
        $types = array_values($typesMap);
	$this->values["type"] = ($arr[$this->pn("type")] == count($types) - 1) ? 
					$arr[$this->pn("customtype")] : 
					array_search($types[$arr[$this->pn("type")]], $typesMap);

    }

        
    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 4){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}

	$this->values["type"] = $this->typeValue($values[0]);
	$this->values["keytag"] = $values[1];
	$this->values["algorithm"] = $this->algorithmValue($values[2]);
	$this->values["certificate"] = $values[3];
    }

    function createUiContainers(){
                                                                                         
	$typesMap = $this->types();
	$types = array_values($typesMap);
	if (isset($typesMap[$this->values["type"]])){
	    $typeIndex = array_search($typesMap[$this->values["type"]], $types);
	    $customType = "";
	} else {
	    $typeIndex = count($types) - 1;
	    $customType = $this->values["type"];
	}
	$isCustomType = ($typeIndex == (count($types) - 1)) ? "checked" : ""; 
	
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
	$typeComboBox->setElements(array_values($types));
	$typeComboBox->setElementsVal(array_keys($types));
	
	$algorithmComboBox = new ExtendedSelectItem($this->pn("algorithm"));
 	$algorithmComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("algorithmdiv"). "',state);\"");
	$algorithmComboBox->setElements(array_values($algorithms));
	$algorithmComboBox->setElementsVal(array_keys($algorithms));
		
	$certificateTextarea = new TextareaTpl($this->pn("certificate"));
	$certificateTextarea->setCols(43);
	
	
	$t1 = new Table();
	$t1->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));
	$t1->add(new TrFormElement(_T("Type"), $typeComboBox),
		array("value"=>$typeIndex));
	
	$typeDiv = new Div(array("id" => $this->pn("typediv")));
        $typeDiv->setVisibility($isCustomType);
	
	$t2 = new Table();
	$t2->add(new TrFormElement(
		    _T("Custom type"), 
		    new InputTpl($this->pn("customtype"),'/^([0-5]?\d?\d?\d?\d|6[0-4]\d\d\d|65[0-4]\d\d|655[0-2]\d|6553[0-5])$/'),
		    array("tooltip" => _T("Type ranges from 0 to 65535"))
		    ),
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
	                 _T("Key tag"), 
	                 new InputTpl($this->pn("keytag"), '/^([0-5]?\d?\d?\d?\d|6[0-4]\d\d\d|65[0-4]\d\d|655[0-2]\d|6553[0-5])$/'),
	                 array("tooltip" => _T("Key tag is a decimal number that ranges from 0 to 65535"))
	                ),
	        array("value" => $this->values["keytag"], "required" => True));
	                                                                                                 
	
	$t5->add(new TrFormElement(_T("Certificate"), $certificateTextarea),
		array("value" => $this->values["certificate"]));
		
	
	return array($this->stackedUi($t1), $this->stackedUi($typeDiv,0), $this->stackedUi($t2,2),
		     $this->stackedUi($t3), $this->stackedUi($algorithmDiv,0), $this->stackedUi($t4,2),
		     $this->stackedUi($t5));
	
    }
    
    function valuesToString(){
    	if (count($this->errors))
	    return $this->errorsDescription();
	return $this->_stringByKeys(array("type", "keytag", "algorithm", "certificate"));
    }

    function valuesToDescription(){
	$descrMap = array(
			_T("Certificate type") => $this->typeDescription($this->values["type"]),
			_T("Algorithm") => $this->algorithmDescription($this->values["algorithm"]),
			_T("Key tag") => $this->values["keytag"],
			_T("Certificate") => $this->certificateDescription($this->values["certificate"]));
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
			    "0" => _T("Unknown to a secure DNS"),
			    "256" => _T("custom")
			   );
	return $algorithms;
    }
    
    function algorithmDescription($alg){
	return $this->mapElemDescription($alg, $this->algorithms());
    }
    
    function types(){
	$types = array(	"1" => _T("X.509 as per PKIX"),
			"2" => _T("SPKI certificate"),
			"3" => _T("Open PGP packet"),
			"4" => _T("The URL of an X.509 data object"),
			"5" => _T("The URL of SPKI certificate"),
			"6" => _T("The fingerprint and URL of Open PGP packet"),
			"7" => _T("Attribute certificate"),
			"8" => _T("The URL of attribute certificate"),
			"253" => _T("URI private"),
			"254" => _T("OID private"),
			"66666" => _T("custom")
			);
	return $types;
    }
    
    function typeValue($type){
	if (is_numeric ($type))
	    return $type;
	
	$mnemoTypesMap = array(	"PKIX" => "1",
				"SPKI" => "2",
				"PGP" => "3",
				"IPKIX" => "4",
				"ISPKI" => "5",
				"IPGP" => "6",
				"ACPKIX" => "7",
				"IACPKIX" => "8",
				"URI" => "253",
				"OID" => "254");
	if (isset($mnemoTypesMap[$type]))
	    return $mnemoTypesMap[$type];
	return "0";
    }

    function algorithmValue($alg){
	if (is_numeric ($alg))
	    return $alg;

	$mnemoAlgorithmsMap = array(	"RSAMD5" => "1",
					"DH" => "2",
					"DSA" => "3",
					"ECC" => "4",
					"RSASHA1" => "5",
					"INDIRECT" => "252",
					"PRIVATEDNS" => "253",
					"PRIVATEOID" => "254");
	if (isset($mnemoAlgorithmsMap[$alg]))
	    return $mnemoAlgorithmsMap[$alg];
	return "0";
    }
    
    

    function typeDescription($type){
	return $this->mapElemDescription($type, $this->types());
    }

    function mapElemDescription($elem, $elems){
	$descr=_T("Unknown") . " (" . $elem . ")";
	if (in_array($elem, array_keys($elems)))
	    $descr = $elems[$elem];
	return $descr;
    
    }
    
    function certificateDescription($cert){
	//$search = array("\x00", "\x0a", "\x0d", "\x1a", "\x09");
	//$replace = array('\0', '\n', '\r', '\Z' , '\t');
	//$description = str_replace($search, $replace, $signature);
	return wordwrap($cert, 32, "<br>", true);
    }
    
}

?>
