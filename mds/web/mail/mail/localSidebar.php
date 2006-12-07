<?

$sidemenu= new SideMenu();

$sidemenu->setClass("mail");
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Mail domain list"), "mail", "mail", "index"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Add a domain"), "mail", "mail", "add"));

?>