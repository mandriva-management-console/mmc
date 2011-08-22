<?

class kxRecord extends RecordBase{
    
    function kxRecord($config = array()){
	$this->RecordBase($config);
        $this->values["exchanger"]="";
	$this->values["priority"]="10";
    }

    function check($zone = ""){
	return "";
    }
    
    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 2){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}
	$this->values["priority"] = $values[0];
	$this->values["exchanger"] = $values[1];
	//$this->values["exchanger"] = $this->_dnFromBindDn($values[1]);
    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname,/* "extra" => "." . $this->zone,*/ "required" => True));
	$t->add(new TrFormElement(_T("Exchanger"), new InputTpl($this->pn("exchanger")), $this->_dnRulesTooltip()),
		array("value" => $this->values["exchanger"], "required" => True));
	$t->add(new TrFormElement(
		    _T("Priority"), 
		    new InputTpl($this->pn("priority"),'/^([0-5]?\d?\d?\d?\d|6[0-4]\d\d\d|65[0-4]\d\d|655[0-2]\d|6553[0-5])$/'),
		    array("tooltip"=>_T("Priority ranges from 0 to 65535"))
		    ),
		array("value"=>$this->values["priority"], "required" => True));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByValues(array($this->values["priority"], 
					    $this->values["exchanger"] 
					    //$this->_dnToBindDn($this->values["exchanger"]) 
					    )
				     );
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();

	return $this->_descriptionByKeys(array(_T("Exchanger") => "exchanger", _T("Priority") => "priority"));
    }

    
}

?>
