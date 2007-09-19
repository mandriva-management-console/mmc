<?

$sidemenu= new SideMenu();
$sidemenu->setClass("lsc");
$sidemenu->addSideMenuItem(new SideMenuItem(_T("General"), "lsc", "lsc", "general"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Repository"), "lsc", "lsc", "repository"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Explorer"), "lsc", "lsc", "explorer"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Commands states"), "lsc", "lsc", "cmd_state"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("All commands states"), "lsc", "lsc", "all_cmd_state"));

?>
