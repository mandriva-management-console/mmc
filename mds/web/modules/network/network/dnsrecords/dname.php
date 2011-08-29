<?

class dnameRecord extends RecordBase{
    
    function dnameRecord($config = array()){
	$this->RecordBase($config);
        $this->values["dname"]="";
    }

    function check($zone = ""){
	return "";
    }
    
    function initValuesFromString($str){
	$this->values["dname"] = $str;
	//$this->values["dname"] = $this->_dnFromBindDn($str);
    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Subdomain alias")),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(_T("Subdomain"), new InputTpl($this->pn("dname")), $this->_dnRulesTooltip()),
		array("value" => $this->values["dname"], "required" => True));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByValues(array($this->values["dname"]
					    //$this->_dnToBindDn($this->values["dname"]) 
					    )
				     );
    }

    function valuesToDescription(){
	return $this->_descriptionByKeys(array(_T("Subdomain") => "dname"));
    }

    
}

?>

