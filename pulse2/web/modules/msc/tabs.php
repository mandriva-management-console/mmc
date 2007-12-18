<?

require("modules/base/computers/localSidebar.php");
require("graph/navbar.inc.php");

$p = new TabbedPageGenerator();
$p->setSideMenu($sidemenu);
$p->addTop(sprintf(_T("%s's secure control"), $_GET['name']), "modules/msc/msc/header.php");
$p->addTab("tablaunch", _T("Launch Actions"), "", "modules/msc/msc/launch.php", array('name'=>$_GET['name']));
$p->addTab("tablogs", _T("Logs"), "", "modules/msc/msc/logs.php", array('name'=>$_GET['name']));
$p->addTab("tabhistory", _T("History"), "", "modules/msc/msc/history.php", array('name'=>$_GET['name']));
$p->display();

?>
