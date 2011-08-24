<h2 style="margin-top: 1em;"><?php echo _T("DHCP Failover configuration"); ?></h2>

<?php

include("includes/FormHandler.php");

global $result;
global $error;

if ($_POST)
    $FH = new FormHandler("dhcpFailover", $_POST);
else
    $FH = new FormHandler("dhcpFailover");

// get current configuration
$failoverConfig = getFailoverConfig();
$FH->setArr($failoverConfig);
// default values
$show = false;
if (isset($failoverConfig['secondary'])) {
    $show = true;
}

if ($_POST) {
    $result = "";
    $error = "";

    if ($FH->isUpdated("dhcp_failover") and $FH->getValue("dhcp_failover") == "off") {
        delFailoverConfig();
        delSecondaryServer();
        if(!isXMLRPCError())
            $result .= _T("Failover configuration disabled.") . "<br />";
        else
            $error .= _T("Failed to disable the failover configuration.") . "<br />";
    }
    if ($FH->getPostValue("dhcp_failover") == "on") {
        if ($FH->isUpdated("secondary")) {
            updateSecondaryServer($FH->getValue("secondary"));
            if(!isXMLRPCError()) {
                $result .= _T(sprintf("%s set as the secondary DHCP server.", $FH->getValue("secondary"))) . "<br />";
                setFailoverConfig($FH->getPostValue("primaryIp"), $FH->getPostValue("secondaryIp"));
                if(!isXMLRPCError())
                    $result .= _T("Failover configuration updated.") . "<br />";
                else
                    $error .= _T("Failed to update the failover configuration.") . "<br />";
            }
            else
                $error .= _T(sprintf("Failed to set %s as the secondary DHCP server.", $FH->getValue("secondary"))) . "<br />";
        }
        else if ($FH->isUpdated("secondaryIp") or $FH->isUpdated("primaryIp")) {
            setFailoverConfig($FH->getPostValue("primaryIp"), $FH->getPostValue("secondaryIp"));
            if(!isXMLRPCError())
                $result .= _T("Failover configuration updated.") . "<br />";
            else
                $error .= _T("Failed to update the failover configuration.") . "<br />";
        }
    }

    // prepare the result popup
    $resultPopup = new NotifyWidget();
    // add error messages
    if ($error) {
        $resultPopup->add('<div class="errorCode">' . $error . '</div>');
        $resultPopup->setLevel(5);
    }
    // add info messages
    if ($result)
        $resultPopup->add('<div class="validCode">' . $result . '</div>');

    if(!$error)
        header("Location: " . urlStrRedirect("network/network/services"));
}



$f = new ValidatingForm();
$f->addValidateButton("bdhcpfailover");
$f->addCancelButton("breset");

$f->push(new Table());
$f->add(
    new TrFormElement(_T("Enable DHCP failover"), new CheckboxTpl("dhcp_failover")),
        array("value"=> $show ? "checked": "", "extraArg" => 'onclick="toggleVisibility(\'dhcpfailoverdiv\');"')
);
$f->pop();

$dhcpfailoverdiv = new Div(array("id" => "dhcpfailoverdiv"));
$dhcpfailoverdiv->setVisibility($show);

$f->push($dhcpfailoverdiv);
$f->push(new Table());

$f->add(
    new TrFormElement(_T("Primary DHCP server name"), new HiddenTpl("primary")),
    array("value" => $FH->getArrayOrPostValue("primary"))
);

$f->add(
    new TrFormElement(_T("Primary DHCP IP address"), new IPInputTpl("primaryIp")),
    array("value" => $FH->getArrayOrPostValue("primaryIp"), "required" => true)
);

$f->add(
    new TrFormElement(_T("Secondary DHCP server name"), new InputTpl("secondary")),
    array("value" => $FH->getArrayOrPostValue("secondary"), "required" => true)
);

$f->add(
    new TrFormElement(_T("Secondary DHCP IP address"), new IPInputTpl("secondaryIp")),
    array("value" => $FH->getArrayOrPostValue("secondaryIp"), "required" => true)
);

$f->pop();
$f->display();

?>
