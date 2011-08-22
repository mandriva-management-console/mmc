<?php

class aRecord extends RecordBase{

    function aRecord($config = array()){
    	$this->recordBase($config);
    	$this->values["ip"] = "";
    }

    function check($zone = ""){
    	return "";
    }

    function initValuesFromString($str){
    	$this->values["ip"] = $str;
    }

    function createUiContainers($editMode = false) {

        if (!$editMode && getReverseZone($this->zone))
            $this->values["ip"] = getZoneFreeIp($this->zone);

        $t = new Table();
        $t->add($this->_createNameElement(_T("Host name")),
            array("value" => $this->hostname, "required" => True));
        $t->add(new TrFormElement(_T("IP address","network"), new IPInputTpl($this->pn("ip"))),
            array("value" => $this->values["ip"]));

	    return array($this->stackedUi($t));
    }

    function valuesToString(){
        return $this->_stringByKeys(array("ip"));
    }

    function valuesToDescription(){
        return $this->_descriptionByKeys(array(_T("IP address") => "ip"));
    }


}

?>
