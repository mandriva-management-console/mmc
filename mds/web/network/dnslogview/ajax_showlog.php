<? if ($_SESSION['__notify'])  { ?>
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

foreach (xmlCall("network.getDnsLog",array($_SESSION['ajax']['filter'])) as $line) {
    if (is_array($line)) {
        $extra[] = $line["extra"];
        $dateparsed = strftime('%b %d %H:%M:%S',$line["time"]);
        $date[] = str_replace(" ", "&nbsp;", $dateparsed);
    } else {
        $date[] = "";
        $extra[] = $line;
    }
}

$n = new ListInfos($date,_("Date"),"1px");
$n->addExtraInfo($extra,_("Informations"));
$n->end = 200;
$n->setTableHeaderPadding(1);
$n->display(0,0);

?>
</div>