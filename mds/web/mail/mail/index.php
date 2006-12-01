<?

$path = array(array("name" => _T("Home"),
                    "link" => "main.php"),
	      array("name" => _T("Mail domain list")));
	      
require("localSidebar.php");
require("graph/navbar.inc.php");

?>

<h2><?= _T("Mail domain list"); ?></h2>

<div class="fixheight"></div>

<?

$p = new PageGenerator();
$p->setSideMenu($sidemenu);
$p->displaySideMenu();

$domains = array();

foreach(getVDomains() as $dn => $entry) {
  $domains[$entry[1]["virtualdomain"][0]] = $entry[1]["virtualdomaindescription"][0];
}

ksort($domains);

$n = new ListInfos(array_keys($domains));
$n->addExtraInfo(array_values($domains));
$n->setName(_T("Mail domain"));

$n->addActionItem(new ActionItem(_("Edit"),"edit","afficher","mail") );
$n->addActionItem(new ActionPopupItem(_("Delete"),"delete","supprimer","mail") );

$n->display();

?>