<?

class ptrRecord extends RecordBase{
    
    function ptrRecord($config = array()){
	$this->RecordBase($config);
        $this->values["cname"]="";
        
    }

    function check($zone = ""){
	return "";
    }
    
    function initValuesFromString($str){
	$this->values["cname"]=substr($str,0,-strlen("." . $this->zone . "."));

    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Name")),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(_T("Canonical name"), new InputTpl($this->pn("cname")), $this->_dnRulesTooltip(AllowedDn::RELATIVE)),
		array("value" => $this->values["cname"],"extra"=>"." . $this->zone, "required" => True));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	//return $this->stringByKeys(array("priority", "mailserver"));
	return $this->_stringByValues(array($this->values["cname"] . "." . $this->zone. "."));
    }

    function valuesToDescription(){
	return $this->_descriptionByValues(array(_T("Canonical name") => $this->values["cname"] . "." . $this->zone));
    }

    
}

?>

