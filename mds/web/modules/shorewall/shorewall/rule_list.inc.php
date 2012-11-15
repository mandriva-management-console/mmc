<?php

$ids[] = array('id' => $index, 'page' => $page);

$action = explode('/', $rule[0]);
if (count($action) > 1) {
    $service[] = $action[0];
    $decision[] = $action[1];
    $proto[] = "";
    $port[] = "";
    $source[] = $rule[1];
}
else {
    $decision[] = $action[0];
    $service[] = "";
    $proto[] = $rule[3];
    $port[] = $rule[4];
    $source[] = $rule[1];
}
$actionsDelete[] = $deleteAction;

?>
