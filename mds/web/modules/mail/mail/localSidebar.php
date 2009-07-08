<?

$sidemenu= new SideMenu();

$sidemenu->setClass("mail");
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Mail domain list"), "mail", "mail", "index", "modules/mail/graph/img/mail_active.png", "modules/mail/graph/img/mail_inactive.png"));
$sidemenu->addSideMenuItem(new SideMenuItem(_T("Add a domain"), "mail", "mail", "add", "modules/mail/graph/img/mailadd_active.png", "modules/mail/graph/img/mailadd_inactive.png"));

?>