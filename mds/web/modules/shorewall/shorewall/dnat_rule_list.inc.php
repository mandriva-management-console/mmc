<?php

$ids[] = array('id' => $index, 'page' => $page);

$action = explode('/', $rule[0]);
if (count($action) > 1) {
    $service[] = $action[0];
    $decision[] = $action[1];
    $proto[] = "";
    $port[] = "";
}
else {
    $decision[] = $action[0];
    $service[] = "";
    $proto[] = $rule[3];
    $port[] = $rule[4];
}


$src = explode(':', $rule[1]);
$src_tmp = "";
if (count($zones_wan) > 1)
    $src_tmp .= $src[0] . ":";
if (isset($src[1]))
    $src_tmp .= $src[1];
else
    $src_tmp = substr($src_tmp, 0, strlen($src_tmp) - 1);
$src_ip[] = $src_tmp;
   
$dest = explode(':', $rule[2]);
$dest_tmp = "";
if (count($zones_lan) > 1)
    $dest_tmp .= $dest[0] . ":";
$dest_tmp .= $dest[1];
if (isset($dest[2]))
    $dest_tmp .= ":" . $dest[2];
$dest_ip[] = $dest_tmp;

$actionsDelete[] = $deleteAction;

?>
