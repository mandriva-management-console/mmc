<?

class txtRecord extends RecordBase{
    
    function txtRecord($config = array()){
	$this->recordBase($config);
        $this->values["text"]="";
    	//$this->values[$this->pn("hostname")]="";
    }

    function check($zone = ""){
	if (!strlen($this->values["text"]))
	    return _T("Text field can't be empty");
    }

    function initValuesFromString($str){
	$this->values["text"]=$str;
    }


    function createUiContainers($editMode = false){
	$t = new Table();
	if ((strrpos($this->hostname,".") == strlen($this->hostname) - 1) && $editMode)
	    $nameElem = $this->_createNameElement(_T("Domain Name"),false);
	else $nameElem = $this->_createNameElement(_T("Domain Name"),true,"/^.*[^\.]$/");

	$t->add($nameElem,
	        array("value" => $this->hostname, /*"extra" => "." . $this->zone,*/ "required" => True));

	$t->add(new TrFormElement(_T("Text"), new TextareaTpl($this->pn("text"))),
		array("value"=>$this->values["text"]));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	return $this->_stringByKeys(array("text"));
    }

    function valuesToDescription(){
	return $this->_descriptionByKeys(array(_T("Text") => "text"));
    }

    
}

?>

