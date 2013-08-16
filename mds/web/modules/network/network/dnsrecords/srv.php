<?php

class srvRecord extends RecordBase{

    function srvRecord($config = array()){
	$this->RecordBase($config);
        $this->values["priority"]="10";
        $this->values["weight"]="1";
        $this->values["port"]="";
        $this->values["target"]="";
    }

    function check($zone = ""){
	print_r ($_POST);
	$err = "";
	if (!preg_match("/\._.+$/", $this->hostname))
	    $err .= _T("Custom protocol name is required") . "<br>";
	if (!preg_match("/(_.+|\*)\._.*$/", $this->hostname))
	    $err .= _T("Service name is required") . "<br>";
	if ($this->values["target"] == "")
	    $err .= _T("Host name that will provide this service is required") . "<br>";
	if ($this->values["priority"] > 65535)
	    $err .= _T("Priority can't be more than 65535") . "<br>";
	if ($this->values["weight"] > 65535)
	    $err .= _T("Weight can't be more than 65535") . "<br>";
	if ($this->values["port"] > 65535)
	    $err .= _T("Port can't be more than 65535") . "<br>";

	return $err;

    }

    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 4){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}

	$this->values["priority"] = $values[0];
        $this->values["weight"] = $values[1];
        $this->values["port"] = $values[2];
        $this->values["target"] = $values[3];
        //$this->values["target"] = $this->_dnFromBindDn($values[3]);
    }


    function initValuesFromArray($arr){
	parent::initValuesFromArray($arr);

	$isAnyService = isset($arr[$this->pn("isAnyService")]) ? True : False;
	$service = $isAnyService ? "*" : $arr[$this->pn("service")];
	$hasTarget = isset($arr[$this->pn("hasTarget")]) ? True : False;
	if (!$hasTarget)
	    $this->values["target"] = ".";
	$protos = $this->protocols();
	$proto = strtolower($protos[$arr[$this->pn("proto")]]);
	if ($proto === _T("custom")){
	    $proto = $arr[$this->pn("customProto")];
	    echo "proto defined";
	}
	$prefix = ($service == "*") ? "" : "_";
	$this->hostname = $prefix . $service . "._" . $proto;
    }


    function createUiContainers($editMode = false){
	if (preg_match("/_?(.*)\._(.*)/", $this->hostname, $vals)){
	    $service = $vals[1];
	    $protoIndex = array_search(strtoupper($vals[2]), $this->protocols());
	    if ($protoIndex === false){
		$protoIndex = count($this->protocols())-1;
		$customProto = $vals[2];
	    }
	} else {
	    $service = "";
	    $protoIndex = 0;
	}

	$isAnyService = "";
	if ($service == "*") {
	    $service = "";
	    $isAnyService = "checked";
	}

	$hasTarget = "checked";
	if ($this->values["target"] == ".") {
	    $hasTarget = "";
	    $this->values["target"]="";
	}



	$isCustomProto = ($protoIndex == count($this->protocols())-1) ? "checked" : "";
	$protoComboBox = new ExtendedSelectItem($this->pn("proto"));
	$protoComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("protodiv"). "',state);\"");
	$protoComboBox->setElements(array_values($this->protocols()));
	$protoComboBox->setElementsVal(array_keys($this->protocols()));

	$t1 = new Table();
	$t1->add(new TrFormElement(_T("It can be an any service, if checked"), new CheckboxTpl($this->pn("isAnyService"))),
		array("value" => $isAnyService, "extraArg" => 'onclick="toggleVisibility(\'' . $this->pn("servicediv") . '\');"'));

	$servicediv = new Div(array("id" => $this->pn("servicediv")));
	$servicediv->setVisibility(!$isAnyService);


	$t2 = new Table();

	$t2->add(new TrFormElement(_T("Service"), new InputTpl($this->pn("service"))),
		 array("value" => $service/*, "required" => True*/));

	$t3 = new Table();

	$t3->add(new TrFormElement(_T("Protocol"), $protoComboBox),
		 array("value" => $protoIndex));


	$protodiv = new Div(array("id" => $this->pn("protodiv")));
	$protodiv->setVisibility($isCustomProto);

	$t4 = new Table();
	$t4->add(new TrFormElement(_T("Custom protocol name"), new InputTpl($this->pn("customProto"))),
		array("value" => $customProto));


	$t5 = new Table();
	$t5->add(new TrFormElement(_T("There is a host that will provide this service, if checked"), new CheckboxTpl($this->pn("hasTarget"))),
		array("value" => $hasTarget, "extraArg" => 'onclick="toggleVisibility(\'' . $this->pn("targetdiv") . '\');"'));

	$targetdiv = new Div(array("id" => $this->pn("targetdiv")));
	$targetdiv->setVisibility($hasTarget);

	$t6 = new Table();
	$t6->add(new TrFormElement(_T("Host that will provide this service"), new InputTpl($this->pn("target"))),
		array("value" => $this->values["target"]));

	$t7 = new Table();

	$t7->add(new TrFormElement(_T("Port"), new InputTpl($this->pn("port"),'/\d+/')),
		array("value"=>$this->values["port"], "required" => True));
	$t7->add(new TrFormElement(_T("Priority"), new InputTpl($this->pn("priority"),'/\d+/')),
		array("value"=>$this->values["priority"], "required" => True));
	$t7->add(new TrFormElement(_T("Weight"), new InputTpl($this->pn("weight"),'/\d+/')),
		array("value"=>$this->values["weight"], "required" => True));

	return array($this->stackedUi($t1),$this->stackedUi($servicediv,0), $this->stackedUi($t2,2),
		     $this->stackedUi($t3),$this->stackedUi($protodiv,0), $this->stackedUi($t4,2),
		     $this->stackedUi($t5),$this->stackedUi($targetdiv,0), $this->stackedUi($t6,2),$this->stackedUi($t7));

    }

    function valuesToString(){
	return $this->_stringByValues(array($this->values["priority"],
					    $this->values["weight"],
					    $this->values["port"],
					    $this->values["target"]
					    //$this->_dnToBindDn($this->values["target"])
					    ));
    }

    function valuesToDescription(){
    	if (count($this->errors))
	    return $this->errorsDescription();
	$target = ($this->values["target"] == ".") ? _T("no") : $this->values["target"];
	return $this->_descriptionByValues(array(_T("Host that will provide this service") => $target,
						 _T("Service port") => $this->values["port"],
						 _T("Priority") => $this->values["priority"],
						 _T("Weight") => $this->values["weight"])
					  );
    }

    function protocols()   {
	return array("TCP", "UDP", _T("custom"));
    }

}

?>

