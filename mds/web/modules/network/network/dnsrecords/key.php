<?php

class keyRecord extends RecordBase{

    function keyRecord($config = array()){
	$this->RecordBase($config);
        $this->values["flags"] = "0";
	$this->values["protocol"] = "1";
	$this->values["algorithm"] = "1";
	$this->values["signature"] = "";
    }

    function check($zone = ""){
	$error = "";
	if (strlen($this->values["protocol"]) == 0)
	    $error .= _T("Custom protocol is not set") . "<br>";
	if (strlen($this->values["algorithm"]) == 0)
	    $error .= _T("Custom algorithm is not set") . "<br>";

	$fh = new FlagsHandler($this->values["flags"]);
	if ($fh->needSignature())
	    if (strlen($this->values["signature"]) == 0)
		$error .= _T("Signature is not set") . "<br>";
	    else
		if (strlen($this->values["signature"]) % 4 > 0)
		    $error .= _T("Signature is incorrect") . "<br>";
	return $error;
    }

    function initValuesFromArray($arr){
	$this->hostname = $arr[$this->pn("hostname")];

        $algorithmsMap = $this->algorithms();
        $algorithms = array_values($algorithmsMap);
	$this->values["algorithm"] = ($arr[$this->pn("algorithm")] == count($algorithms) - 1) ?
					$arr[$this->pn("customalgorithm")] :
					array_search($algorithms[$arr[$this->pn("algorithm")]], $algorithmsMap);

	$protocolsMap = $this->protocols();
        $protocols = array_values($protocolsMap);
	$this->values["protocol"] = ($arr[$this->pn("protocol")] == count($protocols) - 1) ?
					$arr[$this->pn("customprotocol")] :
					array_search($protocols[$arr[$this->pn("protocol")]], $protocolsMap);
	$fh = new FlagsHandler();
	$fh->setUsePolicy($arr[$this->pn("usepolicy")]);
	$fh->setNameType($arr[$this->pn("nametype")]);
	$fh->setZoneUpdate(isset($arr[$this->pn("zoneupdate")]));
	$fh->setStrongUpdate(isset($arr[$this->pn("strongupdate")]));
	$fh->setNameUpdate(isset($arr[$this->pn("uniquenameupdate")]));
	$fh->adjust();

	$this->values["flags"] = $fh->flags();

	$this->values["signature"] =  $fh->needSignature() ? $arr[$this->pn("signature")] : "";

    }


    function initValuesFromString($str){
	$values = explode(" ", $str);
	if (count($values) < 3){
	    $this->markError(RecordError::PARSE, $str);
	    return;
	}

	$this->values["flags"] = $values[0];
	$this->values["protocol"] = $values[1];
	$this->values["algorithm"] = $values[2];
	if (count($values) > 3)
	    $this->values["signature"] = $values[3];
	//echo "sig size is " . strlen($this->values["signature"] );
    }

    function createUiContainers(){

	$protocolsMap = $this->protocols();
	$protocols = array_values($protocolsMap);
	if (isset($protocolsMap[$this->values["protocol"]])){
	    $protocolIndex = array_search($protocolsMap[$this->values["protocol"]], protocols);
	    $customProtocol = "";
	} else {
	    $protocolIndex = count($protocols) - 1;
	    $customProtocol = $this->values["protocol"];
	}
	$isCustomProtocol = ($protocolIndex == (count($protocols) - 1)) ? "checked" : "";


	$algorithmsMap = $this->algorithms();
	$algorithms = array_values($algorithmsMap);
	if (isset($algorithmsMap[$this->values["algorithm"]])){
	    $algorithmIndex = array_search($algorithmsMap[$this->values["algorithm"]], $algorithms);
	    $customAlgorithm = "";
	} else {
	    $algorithmIndex = count($algorithms) - 1;
	    $customAlgorithm = $this->values["algorithm"];
	}
	$isCustomAlgorithm = ($algorithmIndex == (count($algorithms) - 1)) ? "checked" : "";

	$fh = new FlagsHandler($this->values["flags"]);

	$usePolicyIndex = $fh->usePolicy();
	$usePolicies = $fh->usePolicies();
	$hasSignature = ($usePolicyIndex < (count($usePolicies) - 1)) ? "checked" : "";

	$nameTypeIndex = $fh->nameType();
	$nameTypes = $fh->nameTypes();

	$hasUpdate = $fh->zoneUpdate() ? "checked" : "";
	$hasStrongUpdate = $fh->strongUpdate() ? "checked" : "";
	$hasUniqueNameUpdate = $fh->nameUpdate() ? "checked" : "";

        $protocolComboBox = new ExtendedSelectItem($this->pn("protocol"));
 	$protocolComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("protocoldiv"). "',state);\"");
	$protocolComboBox->setElements(array_values($protocols));
	$protocolComboBox->setElementsVal(array_keys($protocols));

	$algorithmComboBox = new ExtendedSelectItem($this->pn("algorithm"));
 	$algorithmComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex == this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("algorithmdiv"). "',state);\"");
	$algorithmComboBox->setElements(array_values($algorithms));
	$algorithmComboBox->setElementsVal(array_keys($algorithms));

	$usePolicyComboBox = new ExtendedSelectItem($this->pn("usepolicy"));
 	$usePolicyComboBox->setAdditionalParams("onkeyup=\"this.blur();this.focus();\" onchange=\"var state = (this.selectedIndex < this.length - 1) ? 'inline' : 'none'; changeObjectDisplay('" . $this->pn("signaturediv"). "',state);\"");
	$usePolicyComboBox->setElements(array_values($usePolicies));
	$usePolicyComboBox->setElementsVal(array_keys($usePolicies));

	$nameTypeComboBox = new ExtendedSelectItem($this->pn("nametype"));
	$nameTypeComboBox->setElements(array_values($nameTypes));
	$nameTypeComboBox->setElementsVal(array_keys($nameTypes));



	$signatureTextarea = new TextareaTpl($this->pn("signature"));
	$signatureTextarea->setCols(43);

	$t1 = new Table();
	$t1->add($this->_createNameElement(_T("Domain name")),
		array("value" => $this->hostname, "required" => True));

	$t1->add(new TrFormElement(_T("Protocol"), $protocolComboBox),
		array("value"=>$protocolIndex));

	$protocolDiv = new Div(array("id" => $this->pn("protocoldiv")));
        $protocolDiv->setVisibility($isCustomProtocol);

	$t2 = new Table();
	$t2->add(new TrFormElement(
		    _T("Custom protocol"),
		    new InputTpl($this->pn("customprotocol"), '/^([01]?\d?\d|2[0-4]\d|25[0-5])$/'),
		    array("tooltip" => _T("Protocol ranges from 0 to 255"))
		    ),
		array("value"=>$customProtocol));

	$t3 = new Table();

	$t3->add(new TrFormElement(_T("Algorithm"), $algorithmComboBox),
		array("value"=>$algorithmIndex));

	$algorithmDiv = new Div(array("id" => $this->pn("algorithmdiv")));
        $algorithmDiv->setVisibility($isCustomAlgorithm);

	$t4 = new Table();
	$t4->add(new TrFormElement(
		    _T("Custom algorithm"),
		    new InputTpl($this->pn("customalgorithm"), '/^([01]?\d?\d|2[0-4]\d|25[0-5])$/'),
		    array("tooltip" => _T("Algorithm ranges from 0 to 255"))
		    ),
		 array("value"=>$customAlgorithm));

	$t5 = new Table();

	$t5->add(new TrFormElement( _T("Use policy"), $usePolicyComboBox),
		array("value" => $usePolicyIndex));

	$t5->add(new TrFormElement( _T("Name type"), $nameTypeComboBox),
		array("value" => $nameTypeIndex));

	$t5->add(new TrFormElement(
		    _T("Update mode for this zone"),
		    new CheckboxTpl($this->pn("zoneupdate")),
		    array("tooltip" => _T("If checked, this key is authorized to attach, detach, and move zones by creating and deleting NS, glue A, and zone record(s).  If unchecked, the key can not authorize any update that would affect such records"))
		                  ),
		array("value" => $hasUpdate));

	$t5->add(new TrFormElement(
		    _T("Strong update"),
		    new CheckboxTpl($this->pn("strongupdate")),
		    array("tooltip" => _T("If checked, this key is authorized to add and delete records even if there are other records with the same owner name and class that are authenticated by a SIG signed with a different dynamic update KEY. If unchecked, the key can only authorize updates where any existing records of the same owner and class are authenticated by a SIG using the same key."))
		    ),
		array("value" => $hasStrongUpdate));

	$t5->add(new TrFormElement(
		    _T("Unique name update"),
		    new CheckboxTpl($this->pn("uniquenameupdate")),
		    array("tooltip" => _T("If checked, this key is authorized to add and update records for only a single owner name."))
		    ),
		array("value" => $hasUniqueNameUpdate));

	$signatureDiv = new Div(array("id" => $this->pn("signaturediv")));
        $signatureDiv->setVisibility($hasSignature);

	$t6 = new Table();
	$t6->add(new TrFormElement(_T("Signature"), $signatureTextarea),
		array("value" => $this->values["signature"]));


	return array($this->stackedUi($t1), $this->stackedUi($protocolDiv,0), $this->stackedUi($t2,2),
		     $this->stackedUi($t3), $this->stackedUi($algorithmDiv,0), $this->stackedUi($t4,2),
		     $this->stackedUi($t5), $this->stackedUi($signatureDiv,0), $this->stackedUi($t6,2));

    }

    function valuesToString(){
	$keys = array("flags","protocol","algorithm");
	$fh = new FlagsHandler($this->values["flags"]);
	if ($fh->needSignature())
	    $keys[] = "signature";
	return $this->_stringByKeys($keys);
    }

    function valuesToDescription(){
	if (count($this->errors))
	    return $this->errorsDescription();

	$fh = new FlagsHandler($this->values["flags"]);
	$policies = $fh->usePolicies();
	$types = $fh->nameTypes();

	$descrMap = array(
			//_T("") => $this->values["type"],
			_T("Algorithm") => $this->algorithmDescription($this->values["algorithm"]),
			_T("Protocol") => $this->protocolDescription($this->values["protocol"]),
			_T("Use policy") => $policies[$fh->usePolicy()],
			_T("Name type") => $types[$fh->nameType()],
			_T("Update mode for this zone") => $fh->zoneUpdate() ? _T("Yes") : _T("No"),
			_T("Strong update") => $fh->strongUpdate() ? _T("Yes") : _T("No"),
			_T("Unique name update") => $fh->nameUpdate() ? _T("Yes") : _T("No"),
			_T("Signature") => strlen($this->values["signature"]) ? $this->signatureDescription($this->values["signature"]) : _T("No")
			);
	return $this->_descriptionByValues($descrMap);
    }


    function algorithms(){
	$algorithms = array("1" => _T("RSA/MD5"),
			    "2" => _T("Diffie-Hellman"),
			    "3" => _T("DSA"),
			    "4" => _T("Elliptic curve crypto"),
			    "252" => _T("Indirect key format"),
			    "253" => _T("Domain name (Private)"),
			    "254" => _T("OID (Private)"),
			    "256" => _T("custom")
			   );
	return $algorithms;
    }

    function protocolDescription($proto){
	$descr=_T("Unknown") . " (" . $proto . ")";
	$protos = $this->protocols();
	if (in_array($proto, array_keys($protos)))
	    $descr = $protos[$proto];
	return $descr;
    }

    function protocols(){
	$protocols = array("1" => _T("TLS"),
			   "2" => _T("Email"),
			   "3" => _T("DNSSEC"),
			   "4" => _T("IPSEC"),
			   "255" => _T("Any"),
			   "256" => _T("custom")
			   );
    return $protocols;
    }

    function algorithmDescription($alg){
	$descr=_T("Unknown") . " (" . $alg . ")";
	$algs = $this->algorithms();
	if (in_array($alg, array_keys($algs)))
	    $descr = $algs[$alg];
	return $descr;
    }

    function signatureDescription($signature){
	//$search = array("\x00", "\x0a", "\x0d", "\x1a", "\x09");
	//$replace = array('\0', '\n', '\r', '\Z' , '\t');
	//$description = str_replace($search, $replace, $signature);
	$description = wordwrap($signature, 32, "<br>", true);
	return $description;
    }


}

class FlagsHandler {
    var $bits;

    function FlagsHandler($flags = 0){
	$tmp = intval($flags);
	for ($i = 0; $i < 16; $i++){
	    $this->bits[15 - $i] = $tmp % 2;
	    $tmp = $tmp >> 1;
	}
    }

    static function usePolicies(){
	$policies = array(_T("Permitted for authentication and/or confidentiality"),
			  _T("Prohibited for confidentiality"),
			  _T("Prohibited for authentication"),
			  _T("No key"),
			 );
	return $policies;
    }

    static function nameTypes(){
	$types = array(_T("User/Account"),
		       _T("Zone"),
		       _T("Non-zone entity"),
			);
	return $types;
    }

    function adjust(){
	$this->setGeneralUpdate(!($this->zoneUpdate() || $this->nameUpdate() || $this->strongUpdate()));
    }

    function flags(){
	$result = 0;
	for ($i = 0; $i < 16; $i++)
	    $result += $this->bits[15 - $i] * pow(2, $i);

	return strval($result);
    }

    function setUsePolicy($policy){
	$this->bits[0] = ($policy / 2) % 2;
	$this->bits[1] = $policy % 2;
    }

    function usePolicy(){
	return $this->bits[0] * 2 + $this->bits[1];
    }

    function setNameType($type){
	$this->bits[6] = ($type / 2) % 2;
	$this->bits[7] = $type % 2;
    }

    function nameType(){
	return $this->bits[6] * 2 + $this->bits[7];
    }

    function setZoneUpdate($isUpdate){
	$this->bits[12] = $isUpdate ? 1 : 0;
    }

    function zoneUpdate(){
	return $this->bits[12] == 1;
    }

    function setStrongUpdate($isUpdate){
	$this->bits[13] = $isUpdate ? 1 : 0;
    }

    function strongUpdate(){
    	return $this->bits[13] == 1;
    }

    function setNameUpdate($isUpdate){
    	$this->bits[14] = $isUpdate ? 1 : 0;
    }

    function nameUpdate(){
    	return $this->bits[14] == 1;
    }

    function setGeneralUpdate($isUpdate){
    	$this->bits[15] = $isUpdate ? 1 : 0;
    }

    function generalUpdate(){
    	return $this->bits[15] == 1;
    }

    function needSignature(){
	return $this->usePolicy() < 3;
    }

}


?>
