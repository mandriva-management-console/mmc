<?php

class afsdbRecord extends RecordBase{
    
    function afsdbRecord($config = array()){
	$this->RecordBase($config);
        $this->values["ownernamehost"]="";
	$this->values["subtype"]="1";
    }

    function check($zone = ""){
        if (strlen($this->values["subtype"]) == 0)
            return _T("Custom subtype is no setted");
    }

    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	
	$directSettedItems = array("ownernamehost");
	foreach ($directSettedItems as $i)
	    if (isset($arr[$this->pn($i)])) 
		$this->values[$i] = $arr[$this->pn($i)];
        
        $subtypesMap = $this->subtypes();
        $subtypes = array_values($subtypesMap);
	$this->values["subtype"] = ($arr[$this->pn("subtype")] == count($subtypes) - 1) ? 
					$arr[$this->pn("customsubtype")] : 
					array_search($subtypes[$arr[$this->pn("subtype")]], $subtypesMap);
    }
    
    
    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 2){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}
	$this->values["subtype"] = $values[0];
	$this->values["ownernamehost"] = $values[1];
    }

    function createUiContainers(){
    	
    	$subtypesMap = $this->subtypes();
	$subtypes = array_values($subtypesMap);
	if (isset($subtypesMap[$this->values["subtype"]])){
	    $subtypeIndex = array_search($subtypesMap[$this->values["subtype"]], $subtypes);
	    $customSubtype = "";
	} else {
	    $subtypeIndex = count($subtypes) - 1;
	    $customSubtype = $this->values["subtype"];
	}
	$isCustomSubtype = ($subtypeIndex == (count($subtypes) - 1)) ? "checked" : ""; 
	
        $subtypeComboBox = new ExtendedSelectItem($this->pn("subtype"));
 	$subtypeComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("subtypediv"). "',state);\"");
	$subtypeComboBox->setElements(array_values($subtypes));
	$subtypeComboBox->setElementsVal(array_keys($subtypes));


	$t1 = new Table();
	$t1->add($this->_createNameElement(_T("Owner name"),true,"/(@|[a-z0-9][a-z0-9-_.]*[a-z0-9]$)/"),
		array("value" => $this->hostname, "required" => True));
	$t1->add(new TrFormElement(_T("Subtype"), $subtypeComboBox),
		array("value"=>$subtypeIndex));
	
	$subtypeDiv = new Div(array("id" => $this->pn("subtypediv")));
        $subtypeDiv->setVisibility($isCustomSubtype);
	
	$t2 = new Table();
	$t2->add(new TrFormElement(
		    _T("Custom subtype"), 
		    new InputTpl($this->pn("customsubtype"),'/^([0-5]?\d?\d?\d?\d|6[0-4]\d\d\d|65[0-4]\d\d|655[0-2]\d|6553[0-5])$/'),
		    array("tooltip" => _T("Custom subtype ranges from 0 to 65535"))
		    ),
		array("value"=>$customSubtype));
		
	$t3 = new Table();

	$t3->add(new TrFormElement(
		    _T("Domain name of a host that has a server for the cell named by the owner name"), 
		    new InputTpl($this->pn("ownernamehost")),
		    $this->_dnRulesTooltip()
		    ),
		array("value" => $this->values["ownernamehost"], "required" => True));

	return array($this->stackedUi($t1), $this->stackedUi($subtypeDiv,0), $this->stackedUi($t2,2),
		     $this->stackedUi($t3));
	
    }
    
    function valuesToString(){
	return $this->_stringByValues(array($this->values["subtype"], 
					    $this->values["ownernamehost"] )
				     );
    }

    function valuesToDescription(){
	if (count($this->errors))
	    return $this->errorsDescription();
	return $this->_descriptionByValues(array(
		_T("Domain name of a host that has a server for the cell named by the owner name") => $this->values["ownernamehost"],
//		    $this->_dnFromBindDn($this->values["ownernamehost"]),
		_T("Subtype") => $this->subtypeDescription($this->values["subtype"])));
    }

    function subtypeDescription($subtype){
        return $this->mapElemDescription($subtype, $this->subtypes());
    }

    function mapElemDescription($elem, $elems){
	$descr=_T("Unknown") . " (" . $elem . ")";
        if (in_array($elem, array_keys($elems)))
            $descr = $elems[$elem];
	return $descr;
    }
                                                
                
    function subtypes(){
	$subtypes = array(	"1" => _T("AFS cell database server"),
		    		"2" => _T("DCE authenticated server"),
                    		"66666" =>_T("custom"));
	return $subtypes;
    }
                                    


    
}

?>
