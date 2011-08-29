<?

class dsRecord extends RecordBase{
    
    function dsRecord($config = array()){
	$this->RecordBase($config);
	$this->values["keytag"] = "";
	$this->values["algorithm"] = "1";
        $this->values["type"] = "1";
	$this->values["digest"] = "";
    }

    function check($zone = ""){
	$error = "";
	if (strlen($this->values["type"]) == 0)
	    $error .= _T("Custom digest type is no setted") . "<br>";
	if ((intval($this->values["type"]) > 65535)&& (intval($this->values["type"]) < 0))
	    $error .= _T("Custom digest type value out of range") . "<br>";
	if (strlen($this->values["algorithm"]) == 0)
	    $error .= _T("Custom algorithm is no setted") . "<br>";
	if (strlen($this->values["digest"]) == 0)
	    $error .= _T("Digest is no setted") . "<br>";
	else 
	    if (strlen($this->values["digest"]) != 40)
		$error .= _T("Digest is incorrect") . "<br>";

	return $error;
    }
    
    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	
	$directSettedItems = array("keytag", "digest");
	foreach ($directSettedItems as $i)
	    if (isset($arr[$this->pn($i)])) 
		$this->values[$i] = $arr[$this->pn($i)];
	
        $algorithmsMap = $this->algorithms();
        $algorithms = array_values($algorithmsMap);
	$this->values["algorithm"] = ($arr[$this->pn("algorithm")] == count($algorithms) - 1) ? 
					$arr[$this->pn("customalgorithm")] : 
					array_search($algorithms[$arr[$this->pn("algorithm")]], $algorithmsMap);

	$this->values["type"] =  isset($arr[$this->pn("isdefaulttype")]) ? "1" : $arr[$this->pn("customtype")];
    }

        
    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 4){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}
	$this->values["keytag"] = $values[0];
	$this->values["algorithm"] = $this->algorithmValue($values[1]);
	$this->values["type"] = $values[2];
	$this->values["digest"] = $values[3];
    }

    function createUiContainers(){
                                                                                         
	$isDefaultType = ($this->values["type"] == "1") ? "checked" : ""; 
	
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
	
	
	$algorithmComboBox = new ExtendedSelectItem($this->pn("algorithm"));
 	$algorithmComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("algorithmdiv"). "',state);\"");
	$algorithmComboBox->setElements(array_values($algorithms));
	$algorithmComboBox->setElementsVal(array_keys($algorithms));
		
	$digestTextarea = new TextareaTpl($this->pn("digest"));
	$digestTextarea->setCols(43);
	
	
	$t1 = new Table();
	$t1->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));


	$t1->add(new TrFormElement(_T("SHA-1 digest"),new CheckboxTpl($this->pn("isdefaulttype"))),
    		array("value"=>$isDefaultType, "extraArg"=>'onclick="toggleVisibility(\'' . $this->pn("typediv").'\');"'));
        
	$typeDiv = new Div(array("id" => $this->pn("typediv")));
        $typeDiv->setVisibility($isDefaultType ? "":"checked");
	
	$t2 = new Table();
	$t2->add(new TrFormElement(
		    _T("Custom digest type"), 
		    new InputTpl($this->pn("customtype"),'/^\d+$/'),
		    array("tooltip" => _T("Digest type ranges from 0 to 255"))
		    ),
		array("value"=>$this->values["type"]));
		
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
	                                                                                                 
	$t5->add(new TrFormElement(
		    _T("Digest"), 
		    $digestTextarea,  
		    array("tooltip" => _T("The Digest MUST be represented as a sequence of case-insensitive hexadecimal digits"))
		    ),
		array("value" => $this->values["digest"]));
		
	
	return array($this->stackedUi($t1), $this->stackedUi($typeDiv,0), $this->stackedUi($t2,2),
		     $this->stackedUi($t3), $this->stackedUi($algorithmDiv,0), $this->stackedUi($t4,2),
		     $this->stackedUi($t5));
	
    }
    
    function valuesToString(){
	return $this->_stringByKeys(array("keytag", "algorithm", "type", "digest"));
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();

	$descrMap = array(
			_T("Algorithm") => $this->algorithmDescription($this->values["algorithm"]),
			_T("Digest type") => $this->typeDescription($this->values["type"]),
			_T("Key tag") => $this->values["keytag"],
			_T("Digest") => $this->digestDescription($this->values["digest"]));
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

    
    function algorithmDescription($alg){
	return $this->mapElemDescription($alg, $this->algorithms());
    }
    
    function typeDescription($type){
	return $this->mapElemDescription($type, array("1" => _T("SHA-1")));
    }
    
    function mapElemDescription($elem, $elems){
	$descr=_T("Unknown") . " (" . $elem . ")";
	if (in_array($elem, array_keys($elems)))
	    $descr = $elems[$elem];
	return $descr;
    
    }
    
    function digestDescription($digest){
	return wordwrap($digest, 32, "<br>", true);
    }
    
}

?>
