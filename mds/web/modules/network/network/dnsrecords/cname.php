<?

class cnameRecord extends RecordBase{
    
    function cnameRecord($config = array()){
	$this->RecordBase($config);
        $this->values["cname"]="";
    }

    function check($zone = ""){
	return "";
    }
    
    function initValuesFromString($str){
	$this->values["cname"] = $str;//$this->_dnFromBindDn($str);
    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Alias name")),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(_T("Canonical name"), new InputTpl($this->pn("cname")), $this->_dnRulesTooltip() ),
		array("value" => $this->values["cname"], "required" => True));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByValues(array( $this->values["cname"]
					    /*$this->_dnToBindDn($this->values["cname"]) */)
				     );
    }

    function valuesToDescription(){
	return $this->_descriptionByKeys(array(_T("Canonical name") => "cname"));
    }

    
}

?>
