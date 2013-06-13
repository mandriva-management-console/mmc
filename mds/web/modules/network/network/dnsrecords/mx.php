<?php

class mxRecord extends RecordBase{
    
    function mxRecord($config = array()){
	$this->RecordBase($config);
        $this->values["mailserver"]="";
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
	$this->values["mailserver"] = $values[1];
	//$this->values["mailserver"] = $this->_dnFromBindDn($values[1]);
    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(_T("Mail server"), new InputTpl($this->pn("mailserver")), $this->_dnRulesTooltip()),
		array("value" => $this->values["mailserver"], "required" => True));
	$t->add(new TrFormElement(
		    _T("Priority"), 
		    new InputTpl($this->pn("priority"), '/^([0-5]?\d?\d?\d?\d|6[0-4]\d\d\d|65[0-4]\d\d|655[0-2]\d|6553[0-5])$/'),
		    array("tooltip" => _T("Priority ranges from 0 to 65535"))
		    ),
		array("value"=>$this->values["priority"], "required" => True));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByValues(array($this->values["priority"], 
					    $this->values["mailserver"] 
					    //$this->_dnToBindDn($this->values["mailserver"]) 
					    )
				     );
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();
	return $this->_descriptionByKeys(array(_T("Mail server") => "mailserver", _T("Priority") => "priority"));
    }

    
}

?>

