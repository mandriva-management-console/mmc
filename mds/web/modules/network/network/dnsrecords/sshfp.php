<?

class sshfpRecord extends RecordBase{
    
    function sshfpRecord($config = array()){
	$this->RecordBase($config);
	$this->values["algorithm"] = "1";
        $this->values["type"] = "1";
	$this->values["fingerprint"] = "";
    }

    function check($zone = ""){
	$error = "";
	if (strlen($this->values["algorithm"]) == 0)
	    $error .= _T("Custom algorithm is no setted") . "<br>";
	
	if (strlen($this->values["fingerprint"]) == 0)
	    $error .= _T("Fingerprint is no setted") . "<br>";
	else 
	    if (strlen($this->values["fingerprint"]) % 4 > 0)
		$error .= _T("Fingerprint is incorrect") . "<br>";

	return $error;
    }
    
    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	
	$directSettedItems = array("fingerprint");
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
	if (count($values) < 3){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}

	$this->values["algorithm"] = $values[0];
	$this->values["type"] = $values[1];
	$this->values["fingerprint"] = $values[2];
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
		
	$fingerprintTextarea = new TextareaTpl($this->pn("fingerprint"));
	$fingerprintTextarea->setCols(43);
	
	
	$t1 = new Table();
	$t1->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));


	$t1->add(new TrFormElement(_T("SHA-1 fingerprint"),new CheckboxTpl($this->pn("isdefaulttype"))),
    		array("value"=>$isDefaultType, "extraArg"=>'onclick="toggleVisibility(\'' . $this->pn("typediv").'\');"'));
        
	$typeDiv = new Div(array("id" => $this->pn("typediv")));
        $typeDiv->setVisibility($isDefaultType ? "":"checked");
	
	$t2 = new Table();
	$t2->add(new TrFormElement(
		    _T("Custom fingerprint type"), 
		    new InputTpl($this->pn("customtype"),'/^\d+$/'),
		    array("tooltip" => _T("Fingerprint type ranges from 0 to 255"))
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
		    _T("Fingerprint"), 
		    $fingerprintTextarea,  
		    array("tooltip" => _T("The Fingerprint MUST be represented as a sequence of case-insensitive hexadecimal digits"))
		    ),
		array("value" => $this->values["fingerprint"]));
		
	
	return array($this->stackedUi($t1), $this->stackedUi($typeDiv,0), $this->stackedUi($t2,2),
		     $this->stackedUi($t3), $this->stackedUi($algorithmDiv,0), $this->stackedUi($t4,2),
		     $this->stackedUi($t5));
	
    }
    
    function valuesToString(){
	return $this->_stringByKeys(array("keytag", "algorithm", "type", "fingerprint"));
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();
	$descrMap = array(
			_T("Algorithm") => $this->algorithmDescription($this->values["algorithm"]),
			_T("Fingerprint type") => $this->typeDescription($this->values["type"]),
			_T("Fingerprint") => $this->fingerprintDescription($this->values["fingerprint"]));
	return $this->_descriptionByValues($descrMap);
    }
    
    function algorithms(){
	$algorithms = array("1" => _T("RSA"), 
			    "2" => _T("DSS"),
			    "256" => _T("custom")
			   );
	return $algorithms;
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
    
    function fingerprintDescription($fingerprint){
	return wordwrap($fingerprint, 32, "<br>", true);
    }
    
}

?>

