<?php if ($_SESSION['__notify'])  { ?>
<script>
    window.location= 'main.php'
</script>
<?

exit(6);

}
?>
<div style="width:99%">
<?

$extra = array();
$date = array();

$errStrings = array("file not found", "could not find NS and/or SOA records", "has no NS records", "has 0 SOA records", "bad zone transfer request");

foreach (xmlCall("network.getDnsLog",array($_SESSION['ajax']['filter'])) as $line) {
    if (is_array($line)) {
        $found = False;
        foreach($errStrings as $err) {
            if (strpos($line["extra"], $err) !== False) {
                $extra[] = '<div class="error">' . $line["extra"] . "</div>";
                $found = True;
                break;
            }
        }
        if (!$found) $extra[] = $line["extra"];
        $dateparsed = strftime('%b %d %H:%M:%S',$line["time"]);
        $date[] = str_replace(" ", "&nbsp;", $dateparsed);
    } else {
        $date[] = "";
        $extra[] = $line;
    }
}

$n = new ListInfos($date,_T("Date", "network"),"1px");
$n->addExtraInfo($extra,_T("Informations", "network"));
$n->end = 200;
$n->setTableHeaderPadding(1);
$n->display(0,0);

?>
</div>