<?

class naptrRecord extends RecordBase{
    
    function naptrRecord($config = array()){
	$this->RecordBase($config);
        $this->values["order"]="";
	$this->values["priority"]="10";
	$this->values["flags"]="";
	$this->values["services"]="";
	$this->values["regexp"]="";
	$this->values["replacement"]="";
    }

    function check($zone = ""){
	return "";
    }
    

    function initValuesFromArray($arr){
	parent::initValuesFromArray($arr);
	//removing empty elements
	$services = array();
        foreach ($arr[$this->pn("services")] as $service){
    	    if (strlen($service))
	        $services[] = $service;
	}
	$this->values["services"] = implode("+",$services);
    }

    
    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 6){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}

        $this->values["order"]=$values[0];
	$this->values["priority"]=$values[1];
	$this->values["flags"]=str_replace("\"","",$values[2]);
	$this->values["services"]=str_replace("\"","",$values[3]);
	$this->values["regexp"]=str_replace("\"","",$values[4]);
	$this->values["replacement"]=$values[5];
	//$this->values["replacement"]=($values[5]==".") ? "" : $this->_dnFromBindDn($values[5]);
    }

    function createUiContainers(){
	$re = '/^([0-5]?\d?\d?\d?\d|6[0-4]\d\d\d|65[0-4]\d\d|655[0-2]\d|6553[0-5])$/';
	$t = new Table();
	$t->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname,/* "extra" => "." . $this->zone,*/ "required" => True));
	$t->add(new TrFormElement(
		    _T("Next domain-name to query"), 
		    new InputTpl($this->pn("replacement")), 
		     $this->_dnRulesTooltip(AllowedDn::FQDN)
		     ),
		array("value" => $this->values["replacement"]));
	$t->add(new TrFormElement(
		    _T("Priority"), 
		    new InputTpl($this->pn("priority"),$re), 
		    array("tooltip" => _T("Priority ranges from 0 to 65535"))),
		array("value"=>$this->values["priority"], "required" => True));
	$t->add(new TrFormElement(_T("Order"), new InputTpl($this->pn("order"),$re)),
		array("value"=>$this->values["order"], "required" => True));
	$t->add(new TrFormElement(_T("Flags"), new InputTpl($this->pn("flags"),'/[a-zA-Z0-9]/')),
		array("value"=>$this->values["flags"]));
	$t->add(new TrFormElement(_T("Regular expression"), new InputTpl($this->pn("regexp"))),
		array("value"=>$this->values["regexp"]));
	$servicesUi = array(new FormElement(_T("Services"),new MultipleInputTpl($this->pn("services"),_T("Services"))),
			    explode("+",$this->values["services"]));

	return array($this->stackedUi($t), $this->stackedUi($servicesUi,0,true));

    }
    
    function valuesToString(){
	return $this->_stringByValues(array($this->values["order"], 
					    $this->values["priority"], 
					    "\"" . $this->values["flags"] . "\"",
					    "\"" . $this->values["services"] ."\"",
					    "\"" . $this->values["regexp"] ."\"",
					    $this->values["replacement"] /*. "."*/ )
				     );
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();
	return $this->_descriptionByValues(array(_T("Order") => $this->values["order"], 
					       _T("Priority") => $this->values["priority"],
					       _T("Flags") => $this->values["flags"],
					       _T("Services") => str_replace("+",", ",$this->values["services"]),
					       _T("Regular expression") => $this->values["regexp"],
					       _T("Next domain-name to query") => $this->values["replacement"]
					      ));
    }

    
    
}

?>

