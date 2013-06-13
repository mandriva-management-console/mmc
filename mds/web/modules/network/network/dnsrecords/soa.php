<?php

class soaRecord extends RecordBase{
    
    function soaRecord($config = array()){
	$this->RecordBase($config);
        $this->values["dnsserver"] = "";
	$this->values["mail"] = "";
	$this->values["serial"] = "";
	$this->values["refresh"] = "";
	$this->values["retry"] = "";
	$this->values["expiry"] = "";
	$this->values["minttl"] = "";
	$this->hostname = "@";
    }

    function check($zone = ""){

    }
    
    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];
	
	$directSettedItems = array("dnsserver", "mail", "serial");
	foreach ($directSettedItems as $i)
	    if (isset($arr[$this->pn($i)])) 
		$this->values[$i] = $arr[$this->pn($i)];

	$timeItems = array("refresh", "retry", "expiry", "minttl");
	foreach ($timeItems as $i)
	    if (isset($arr[$this->pn($i)])) 
		$this->values[$i] = BindRemainingTimeTpl::bindTimeStringFromValue($arr[$this->pn($i)]);
    }

        
    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 7){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}

	$this->values["dnsserver"] = $values[0];
	$this->values["mail"] = $values[1];

	//$this->values["dnsserver"] = $this->_dnFromBindDn($values[0]);
	//$this->values["mail"] = $this->_dnFromBindDn($values[1]);
	$this->values["serial"] = $values[2];
	$this->values["refresh"] = $values[3];
	$this->values["retry"] = $values[4];
	$this->values["expiry"] = $values[5];
	$this->values["minttl"] = $values[6];
	
    }

    function createUiContainers(){
	$t = new Table();
	$t->add($this->_createNameElement(_T("Zone name"),false),
		array("value" => $this->hostname, "required" => True));
	$t->add(new TrFormElement(	_T("Name server"), 
					new InputTpl($this->pn("dnsserver")), 
					$this->_dnRulesTooltip(AllowedDn::RELATIVE|AllowedDn::FQDN)
					),
		array("value" => $this->values["dnsserver"], "required" => True));
	$t->add(new TrFormElement(	_T("Responsible person e-mail"), 
					new InputTpl($this->pn("mail")),
					$this->_dnRulesTooltip(AllowedDn::RELATIVE|AllowedDn::FQDN)
					),
		array("value"=>$this->values["mail"], "required" => True));
	$t->add(new TrFormElement(_T("Time interval before the zone should be refreshed"), new BindRemainingTimeTpl($this->pn("refresh"))),
		array("value"=>BindRemainingTimeTpl::valueFromBindTimeString($this->values["refresh"])));
	$t->add(new TrFormElement(_T("Time interval that should elapse before a failed refresh should be retried"), new BindRemainingTimeTpl($this->pn("retry"))),
		array("value"=>BindRemainingTimeTpl::valueFromBindTimeString($this->values["retry"])));
	$t->add(new TrFormElement(_T("Expiry time"), new BindRemainingTimeTpl($this->pn("expiry"))),
		array("value"=>BindRemainingTimeTpl::valueFromBindTimeString($this->values["expiry"])));
	$t->add(new TrFormElement(_T("Minimum TTL"), new BindRemainingTimeTpl($this->pn("minttl"))),
		array("value"=>BindRemainingTimeTpl::valueFromBindTimeString($this->values["minttl"])));
	$t->add(new TrFormElement(_T("Serial number"), new HiddenTpl($this->pn("serial"))),
		array("value"=>$this->values["serial"], "required" => True));
	return array($this->stackedUi($t));
    }
    
    function valuesToString(){
	//return $this->stringByKeys(array("priority", "mailserver"));
	return $this->_stringByValues(array(
		    			    $this->values["dnsserver"], 
					    $this->values["mail"],
					    //$this->_dnToBindDn($this->values["dnsserver"]), 
					    //$this->_dnToBindDn($this->values["mail"]),
					    $this->values["serial"],
					    $this->values["refresh"],
					    $this->values["retry"],
					    $this->values["expiry"],
					    $this->values["minttl"]
			                   ) );
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();
	$descrMap = array(
			_T("Name server") => $this->values["dnsserver"],
			_T("Responsible person e-mail") => $this->values["mail"],
			_T("Time interval before the zone should be refreshed") => BindRemainingTimeTpl::descriptionForBindTimeString($this->values["refresh"]),
			_T("Time interval that should elapse before a failed refresh should be retried") => BindRemainingTimeTpl::descriptionForBindTimeString($this->values["retry"]),
			_T("Expiry time") => BindRemainingTimeTpl::descriptionForBindTimeString($this->values["expiry"]),
			_T("Minimum TTL") => BindRemainingTimeTpl::descriptionForBindTimeString($this->values["minttl"]),
			_T("Serial number") => $this->values["serial"]);
	return $this->_descriptionByValues($descrMap);
    }

    
}

?>

