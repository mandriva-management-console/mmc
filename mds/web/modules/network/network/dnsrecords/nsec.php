<?

class nsecRecord extends RecordBase{
    
    function nsecRecord($config = array()){
	$this->RecordBase($config);
        $this->values["nextdomain"]="";
	$this->values["types"]="";
    }

    function check($zone = ""){
	return "";
    }
    

    function initValuesFromArray($arr){
	parent::initValuesFromArray($arr);
	//removing empty elements
	$types = array();
	foreach ($arr[$this->pn("types")] as $type){
	    if (strlen($type))
		$types[] = $type;
	}
	$this->values["types"] = implode(" ",$types);
    }

    
    function initValuesFromString($str){
//	echo $str;
	$firstSpacePos = strpos($str, " ");
	if ($firstSpacePos !== false){
	    $this->values["nextdomain"]=substr($str, 0, $firstSpacePos);
	    $this->values["types"]= substr($str,$firstSpacePos+1);
	}
	else
	    $this->values["nextdomain"]=$str;

    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(_T("Next domain name" ), new InputTpl($this->pn("nextdomain")), $this->_dnRulesTooltip()),
		array("value" => $this->values["nextdomain"], "required"=> True));
	$typesUi = array(new FormElement(
			    _T("Types that exist at the record's owner name"),
			    new MultipleInputTpl($this->pn("types"),_T("Types"))
			    ),
			explode(" ",$this->values["types"]));

	return array($this->stackedUi($t), $this->stackedUi($typesUi,0,true));

    }
    
    function valuesToString(){
	return $this->_stringByValues(array(
					    //$this->_dnToBindDn($this->values["nextdomain"]), 
					    $this->values["nextdomain"],
					    $this->values["types"])
				     );
    }

    function valuesToDescription(){
	return $this->_descriptionByValues(array(_T("Next domain name") => $this->values["nextdomain"],
					         _T("Types that exist at the record's owner name") => 
					    	    str_replace(" ",", ",$this->values["types"])  
					        )
					  );
    }

    
    
}

?>

