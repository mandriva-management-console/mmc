<?php

$domains = array();
foreach(getVDomains($filter) as $dn => $entry) {
    $domains[$entry[1]["virtualdomain"][0]] = $entry[1]["virtualdomaindescription"][0];
}
ksort($domains);

print "<ul>";
foreach($domains as $domain => $desc) {
    print "<li>$domain<br/><span class=\"informal\">$desc</span></li>";
}

print "</ul>";

?>
