<?php

class aaaaRecord extends RecordBase{
    
    function aaaaRecord($config = array()){
	$this->recordBase($config);
	$this->values["ip"]="";
    }

    function check($zone = ""){
	return "";
    }

    function initValuesFromString($str){
	$this->values["ip"]=$str;
    }


    function createUiContainers($editMode = false){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Host name")),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(_T("IPv6 address"), new IPv6InputTpl($this->pn("ip"))),
		array("value"=>$this->values["ip"], "required"=>true));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByKeys(array("ip"));
    }

    function valuesToDescription(){
	return $this->_descriptionByKeys(array(_T("IPv6 address") => "ip"));
    }

    
}

?>

