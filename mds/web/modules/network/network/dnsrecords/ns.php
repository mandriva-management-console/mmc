<?php

class nsRecord extends RecordBase{
    
    function nsRecord($config = array()){
	$this->RecordBase($config);
        $this->values["dnsserver"]="";
    }

    function check($zone = ""){
	return "";
    }
    
    function initValuesFromString($str){
	$this->values["dnsserver"]=$str;
	//$this->values["dnsserver"]=$this->_dnFromBindDn($str);
    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(_T("Name server"), new InputTpl($this->pn("dnsserver")), $this->_dnRulesTooltip()),
		array("value" => $this->values["dnsserver"], "required" => True));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByValues(array( 
					$this->values["dnsserver"]
				     ));
    }

    function valuesToDescription(){
	return $this->_descriptionByKeys(array(_T("Name server") => "dnsserver"));
    }

    
}

?>

