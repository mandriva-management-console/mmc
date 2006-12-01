<?

$sidemenu= new SideMenu();

$sidemenu->setClass("mail");
$sidemenu->addSideMenuItem(new SideMenuItem("Mail domain list", "mail", "mail", "index"));
$sidemenu->addSideMenuItem(new SideMenuItem("Add a domain", "mail", "mail", "add"));

?>