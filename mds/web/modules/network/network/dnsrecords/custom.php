<?php

class customRecord extends RecordBase{
    
    function customRecord($config = array()){
	$this->recordBase($config);
        $this->values["value"]="";
        $this->values["type"]="";
	//$this->values[$this->pn("hostname")]="";
    }

    function check($zone = ""){
    
    }

    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	$this->values["type"] = $arr[$this->pn("type")];
	$this->values["value"] = str_replace("\\","",$arr[$this->pn("value")]);
    }


    function initValuesFromString($str){
	$this->values["value"]=$str;
    }

    function createUiContainers($editMode = false){
	$t = new Table();
	if (!$editMode){
	    $t->add(new TrFormElement(_T("Type name"), new InputTpl($this->pn("type"))),
		    array("value" => $this->values["type"], "required" => True));
	}
	$t->add($this->_createNameElement(_T("Host name")),
		array("value" => $this->hostname,  "required" => True));
	$t->add(new TrFormElement(_T("Value"), new InputTpl($this->pn("value"))),
		array("value"=>htmlspecialchars($this->values["value"])));

	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByKeys(array("value"));
    }
    
    function valuesToDescription(){
	$descrMap = array(_T("Value") => "value");
	return $this->_descriptionByKeys($descrMap);
    }
    
    function typeName(){
	return $this->values["type"];
    }
    
    
}

?>