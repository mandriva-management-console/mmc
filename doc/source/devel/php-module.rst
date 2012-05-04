.. highlight:: php

===================================================
How to write a PHP module for the MMC web interface
===================================================

Related documentations
======================

- `Full MMC PHP web interface documentation <http://mds.mandriva.org/content/doxygen-trunk/html/>`_.
- :doc:`php-code`

MMC Page format
===============

A MMC page is made of 5 elements:

- page header: expert mode button, disconnect button
- page footer: displays MMC components version
- top navigation bar: shows all available MMC sub-modules. A MMC
  module can offer more than on sub-modules. For example, the "base"
  module display the "Users" and "Groups" pane.
- left sidebar: shows all available actions inside a
  sub-modules.
- content: HTML content that allows a user to make an action
  (forms, button, etc.)

Here is a simple schema:

::

    /-------------------------\
    |         HEADER          |
    \-------------------------/
    /-------------------------\
    |                         |
    |      NAVIGATION BAR     |
    |                         |
    \-------------------------/
    /-----\/------------------\
    | L S ||                  |
    | E I ||                  |
    | F D ||                  |
    | T E ||      CONTENT     |
    |   B ||                  |
    |   A ||                  |
    |   R ||                  |
    |     ||                  |
    \-----/\------------------/
    /-------------------------\
    |         FOOTER          |
    \-------------------------/

When writing a MMC web module, you can:

- defines new sub-modules (new panes) into the navigation
  bar
- defines new actions into the left sidebar
- set a content for each action

.. _mmc-page-display:

How MMC pages are displayed
===========================

The :file:`/usr/share/mmc/main.php` file is the key.

Called without argument (e.g. http://127.0.0.1/mmc/main.php), the
MMC portal page is displayed. When a user login into the interface, this
is the first page that is displayed.

To display other pages, the following parameters must be given to
this PHP scipt:

- module: the name of the module (top navigation bar pane) where
  the page is located
- submod: the name of the sub-module (left navigation bar pane)
  where the page is located
- action: the base name of the PHP script that displays the
  page

For example:
http://127.0.0.1/mmc/main.php&module=base&submod=users&action=add
will call the "add.php" script of the "users" sub-module of the "base"
module.

PHP module structure
====================

A PHP module of the MMC web interface is fully contained into the
:file:`/usr/share/mmc/modules/[module_name]` directory of a MMC installation.

This directory should looks like this:

::

    .
    |-- graph
    |   |-- img
    |   |   |-- ...
    |   `-- submodule1
    |       `-- index.css
    |-- includes
    |   |-- module-xmlrpc.php
    |   |-- publicFunc.php
    |-- infoPackage.inc.php
    |-- submodule1
    |   |-- page1.php
    |   |-- page2.php
    |   |-- page3.php
    |   |-- ...
    |   |-- localSidebar.php
    |-- submodule2
    |   |-- localSidebar.php
    |   |-- ...
    |-- locale
    |-- fr_FR.utf8
    |   `-- LC_MESSAGES
    |       `-- module.po
    |-- nb_NO.utf8
    |   `-- LC_MESSAGES
    |       `-- module.po
    |-- ...

- infoPackage.inc.php: module declaration. See the section :ref:`info-package`
- includes: where should be put module include files: module
  widgets, module XMLRPC calls, etc.
- includes/publicFunc.php: this file included by various MMC
  pages. For example, if the module allows to manage user LDAP fields,
  his file can be used when rendering the user edit page.
- graph: where should be stored all graphical elements: images
  (in graph/img), extra CSS, etc.
- submoduleN: owns all the pages of a submodule
- submoduleN/localSidebar: left sidebar of a submodule when
  displaying sub-module pages
- locale: owns the i18n internationalization files of the
  module, used by gettext.

Mapping between main.php arguments and modules
----------------------------------------------

The main.php arguments are directly related to modules directory
organization.

For example, when calling
http://127.0.0.1/mmc/main.php&module=base&submod=users&action=add,
the file
:file:`/usr/share/mmc/modules/base/users/add.php` is
executed.

.. _info-package:

Module declaration: infoPackage.inc.php
=======================================

This mandatory file defines:

- the module name and description
- the sub-modules name, description, and their corresponding icons into the
  top navigation bar
- all the available module web pages, their names and their options
- form input fields that are protected by the ACL system

These informations are also used by the MMC home page to display
the module summary.

Commented example:

::

    <?php

    # Register a new module called "module1"
    $mod = new Module("module1");
    # MMC module version, should follow MDS version release
    $mod->setVersion("2.0.0");
    # SVN revision bumber
    $mod->setRevision("$Rev$");
    # module description. The _T("") syntax will be explained later
    $mod->setDescription(_T("Module 1 service"),"module1");
    /*
    Module API version this version can use.
    The MMC agent Python module and the web interface PHP module
    API version must match.
    */
    $mod->setAPIVersion("4:1:3");
    /* Register a new sub-module */
    $submod = new SubModule("submodule1");
    /* Set submodule description */
    $submod->setDescription(_T("Sub module 1", "module1"));
    /*
    Icons to use in the top navigation bar for this sub-module.
    The following images will be displayed:
    - /usr/share/mmc/modules/module1/graph/img/submodule1.png: sub-module not selected
    - .../submodule1_hl.png: mouse hover on the sub-module icon (the icon is highlighted)
    - .../submodule1_select: the sub-module is selected
    */
    $submod->setImg("modules/module1/graph/img/submodule1");
    /*
    The page to load when selecting the sub-module
    e.g.: main.php?submod=module1&submod=submodule1&action=index
    */
    $submod->setDefaultPage("module1/submodule1/index");
    /* Sub-module priority in the top navigation bar */
    $submod->setPriority(300);
    /* Register pages in this sub-module */
    /*
    This new page will be displayed when using this URL:
    e.g.: main.php?submod=module1&submod=submodule1&action=index
    The corresponding PHP file will be: /usr/share/mmc/modules/module1/submodule1/index.php
    A page must be registered to be displayed.
    */
    $page = new Page("index", _T("Sub-module index page", "module1"));
    /* Add this page to the sub-module */
    $submod->addPage($page);
    /* Another page */
    $page = new Page("edit",_T("Sub-module edit page", "module1"));
    /*
    Options can be set on pages.
    If "visible" is set to False, the page won't be displayed in the sub-module summary on the MMC home page.
    */
    $page->setOptions(array("visible"=>False));
    /* A page can contain tabs. These tabs must be declared to get ACL support on them */
    $page->addTab(new Tab("tabid1", "Tab description 1"));
    $page->addTab(new Tab("tabid2", "Tab description 2"));
    $submod->addPage($page);
    /* Add the sub-module to the module */
    $mod->addSubmod($submod);
    /* Defines other submodules and pages */
    $submod = new SubModule("submodule2");
    ...
    ...
    /* And put the module into MMC application */
    $MMCApp = &MMCApp::getInstance();
    $MMCApp->addModule(&$mod);

    ?>

The following options can be set on a page:

- visible: if set to False, the page won't be displayed in the
  sub-module summary on the MMC home page. Always True by
  default.

- noHeader: If set to True, the header and the footer won't be
  inserted automatically when rendering the page. This option is
  useful for popup page and AJAX related pages. False by
  default.

- noACL: If set to True, no ACL entry is linked to this page.
  False by default.

- AJAX: same as setting noACL to True and noHeader to true.
  Always use this for URL that will be called by scriptaculous
  Ajax.Updater objects. False by default.

How to render a page
====================

Once a page is registered into the infoPackage.php file, it can be
rendered. The main.php script take care of this:

- It checks that the current user has the rights to see the
  page. If not, the user is redirected to the MMC home page

- If page noHeader option is set to False, the MMC header is
  rendered

- The registered PHP script corresponding to the page is
  executed

- If page noHeader option is set to False, the MMC footer is
  rendered

Notice that only the header and the footer can be rendered
automatically. The top navigation bar, the left sidebar and the page
content must be provided by the registered PHP script.

Notice that for special page like the popup, there is no need of
header, footer and bars, only a content should be provide.

The PageGenerator class
-----------------------

This class allows to easily creates a page with the top
navigation bar and the left sidebar. Here is a commented example of a
simple MMC page:

::

    <?php

    /* localSidebar.php contains the left sidebar elements of all the pages sub-module. See next section. */
    require("localSidebar.php");
    /*
    Display the top navigation bar, and prepare the page rendering.
    The current sub-module pane is automatically selected.
    */
    require("graph/navbar.inc.php");
    /*
    Create a page with a title
    The title will be displayed as a H2
    */
    $p = new PageGenerator(_T("Simple page example"));
    /*
    $sidemenu has been defined in the localSidebar.php file
    We set it as the page left side bar
    */
    $p->setSideMenu($sidemenu);
    /*
    We ask to the PageGenerator instance to render.
    The page title and the left sidebar are displayed.
    The current page corresponding pane is automatically selected in the left side bar.
    */
    $p->display();
    /* Fill the page with content */
    ...

    ?>

The SideMenu and SideMenuItem classes
-------------------------------------

The SideMenu class allows to build the left sidebar menu of a
page. Here is an example, that could have been the content of the
"localSidebar.php" of the previous section.

::

    <?php

    $sidemenu = new SideMenu();
    /*
    CSS class name to use when rendering the sidebar.
    You should use the sub-module name
    */
    $sidemenu->setClass("submodule1");
    /*
    Register new SideMenuItem objects in the menu.
    Each item is a menu pane.
    */
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("Simple page"),
        "module1", "submodule1", "index", "modules/module1/graph/img/module1_active.png",
        "modules/module1/graph/img/module1_inactive.png")
    );
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("Another page"),
        "module1", "submodule1", "add", "modules/module1/graph/img/module1_active.png",
        "modules/module1/graph/img/module1_inactive.png")
    );

    ?>

The SideMenuItem constructor arguments are

- the item label

- the next three arguments are needed to create the URL link
  so that clicking on the menu item loads the right page. They
  corresponds to a module name ("module1"), a sub-module name
  ("submodule1"), and a registered page ("index").

- the last two optional arguments allow to define an icon to
  use when the sidemenu item is selected, and when not selected. If
  not specified, no icon will be used.

Adding page from a module to another module
-------------------------------------------

With the :file:`infoPackage.inc.php` file, you can also
add the page of a module to another module. This is useful if you
want to provide new features to an already existing module.

In our example, we add a new page to the "computers" sub-module of the "base"
module. Here is the corresponding infoPackage.inc.php:

::

    <?php

    /* Get the base module instance reference */
    $base = &$MMCApp->getModule('base');
    /* Get the computers sub-module instance reference */
    $computers = &$base->getSubmod('computers');
    /* Add the page to the module */
    $page = new Page("extrapage", _T("Extra page", "module1"));
    $page->setFile("modules/module1/extra/extrapage.php");
    $computers->addPage($page);
    /* You should unset the references when you finished using them */
    unset($base);
    unset($computers);

    ?>

With this code, the PHP script :file:`modules/module1/extra/extrapage.php`
will be called when using the :file:`main.php?module=base&submod=computers&action=extrapage`.

The remaining problem is the sidebar management. In the called PHP script, you
must include the :file:`localSidebar.php` script from the other sub-module
module, and add your SideMenuItem object to it.

For example:

::

    <?php
    require("modules/base/computers/localSidebar.php");
    require("graph/navbar.inc.php");
    $p = new PageGenerator(_T("Extra page with new functions"));
    /* Add new sidemenu item \*/
    $sidemenu->addSideMenuItem(new SideMenuItem(_T("Extra page"),"base",
        "computers", "extrapage", "modules/base/graph/img/computers_active.png",
        "modules/base/graph/img/computers_inactive.png")
    );
    $p->setSideMenu($sidemenu);
    $p->display();
    ...
    ?>

Including CSS file
------------------

When a page is rendered, the framework includes the file
:file:`modules/currentmodule/graph/currentmodule/currentsubmodule/index.css`
if it exists.

"currentmodule" and "currentsubmodule" are guessed from the current URL.

The MMC widget framework
========================

The MMC widget framework is a set of classes that allows to wrap
HTML code into PHP classes. The goal of this very simple framework
is:

- separate HTML code and PHP code

- factorize HTML and PHP code

- use the same set of widgets accross all the module interface,
  for a better user experience

There are two kinds of widgets: widgets that contains other
widgets, widgets that doesn't contain other widgets.

MMC widgets that are containers inherits from the HtmlContainer
class, and the other widgets inherits from the HtmlElement.

Every MMC pages have been built using instances of these classes.
Here is a little example:

::

    <?php
    /* Build a new validating form */
    $f = new ValidatingForm();
    /* Push a table into the form, and go to the table level */
    $f->push(new Table());
    /* Add two TR to the table */
    /* Ask for a given name */
    $f->add(
    new TrFormElement(_T("Given name"), new InputTpl("givenName"),
    array("value" => "", "required" => True)
    );
    /* Ask for a family name */
    $f->add(
    new TrFormElement(_T("Family name"), new InputTpl("name"),
    array("value" => "", "required" => True)
    );
    /* Go back to the validating form level */
    $f->pop();
    /* Add a button to the form */
    $f->addButton("bvalid", _T("Validate"));
    /* Close the form */
    $f->pop();
    /* Render all the form and the objects it contains \*/
    $f->display();
    ?>

This example renders a HTML form, with two input fields asking for
a given name and a family name.

In this example, ValidatingForm and Table are two HtmlContainer
sub-classes. TrFormElement and InputTpl are two HtmlElement
sub-classes.

HtmlContainer objects
---------------------

A HtmlContainer object owns an ordered list of elements. An
element is either an instance from a HtmlContainer sub-class, either
an instance from a HtmlElement sub-class.

This list of elements is either opened (new elements can be
added to the list), either closed (no more elements can be
added).

When adding a HtmlElement or a HtmlContainer object to a
HtmlContainer, the object is added to the last added HtmlContainer
which does not have a closed element list.

The HtmlContainer class main methods are:

- push($newHtmlContainer): recursively push into the widget
  element list a new container

- pop(): pop the last pushed HtmlContainer with an opened
  element list, and close the list.

- add(NewHtmlElement): recursively add into the widget element
  list a new element

- display(): recursively render HTML code. The display method
  is called on each element of the list.

Here is an example. The indentation helps to show which
container is used:

::

    <?php
    $o = new HtmlContainer;
    $o->add(HtmlElement());
    $o->push(HtmlContainer());
    /* The HtmlElement are added to the latest added and open HtmlContainer */
    $o->add(HtmlElement());
    $o->push(HtmlContainer());
    /* The HtmlElement are added to the latest added and open HtmlContainer */
    $o->add(HtmlElement());
    $o->add(HtmlElement());
    /* closing the element list of the latest HtmlContainer */
    $o->pop();
    /* falling back to the previous HtmlContainer */
    $o->add(HtmlElement());
    /* closing the element list of the latest HtmlContainer */
    $o->pop();
    $o->add(HtmlElement());
    /* Popping the root container */
    $o->pop();
    /* Display the HTML code */
    $o->display();
    ?>

To render HTML code, a HtmContainer subclass needs only to
implement these two functions:

- begin: before recursivelly calling display() on each element
  of its list, the container must put its starting HTML tag. This
  method returns the HTML tag as a string.

- end: After recursivelly calling display() on each element of
  its list, the container must put its ending HTML tag. This method
  returns the HTML tag as a string.

Here is an example of a HtmlContainer subclass that wraps a HTML
table:

::

    <?php

    class Table extends HtmlContainer {
        function Table() {
            $this->HtmlContainer();
        }

        function begin() {
            return "<table>";
        }

        function end() {
            return "</table>";
        }
    }

    ?>

HtmlElement objects
-------------------

These objects are very simple PHP class wrapper around HTML
code, and can be stored into a HtmlContainer object.

To render HTML code, a HtmElement subclass needs only to
implement the display() function. This function just prints the HTML
code implementing the widget. For example:

::

    <?php

    class Title Extends HtmlElement {

        function Title($text) {
            $this->$text = $text
        }

        function display() {
            print "<h1>" . $this->text . "</h1>";
        }
    }

    ?>

Useful MMC widgets
==================

The following widgets are defined in the :file:`includes/PageGenerator.php`
file.

The ListInfos class
-------------------

The ListInfos class allows to create a paged multi-column table
with a navigation bar, and to link each row to a set of actions. For
example, the MMC user list is implemented using a ListInfos
widget.

Here is an example. We create a table with two columns: the
first is a fruit, the second is a quantity.

::

    <?php

    require ("includes/PageGenerator.php");
    $fruits = array("apple", "banana", "lemon", "papaya", "fig", "olive",
        "clementine", "orange", "mandarin", "grapes", "kumquat");
    $stock = array("5", "8", "40", "12", "40", "51", "12", "7", "9", "15", "21");
    /*
    Create the widget. The first column will be labeled "Fruit name",
    and each cell will contain an item of the $fruits array.
    */
    $l = new ListInfos($fruits, _T("Fruit name"));
    /* Add the second column */
    $l->addExtraInfo($stock, _T("Quantity"));
    /*
    Set the item counter label.
    The counter is displayed just above the table:
    Fruits 1 to 10 - Total 11 (page 1/2)
    */
    $l->setName(_T("Fruits"));
    /* Display the widget */
    $l->display();

    ?>

The item counter label is displayed just above the table. In our
example, it shows: Fruits 1 to 10 - Total 11 (page 1/2). It
means:

- Fruits 1 to 10: from all table rows, the row #1 to row #10
  are displayed. By default, the ListInfos widget is configured to
  display only 10 rows. This setting is set into the "maxperpage"
  option of the :file:`/etc/mmc/mmc.ini` file.

- Total 11: the total table rows number

- (page 1/2): the first page, that corresponds to the first 10
  rows of the table, is displayed. If you click on the "Next"
  button, the second page will be displayed, with the single row
  #11.

Now we are going to add some action items to each rows:

::

    <?php

    require ("includes/PageGenerator.php");
    $l = new ListInfos($fruits, _T("Fruit name"));
    $l->addExtraInfo($stock, _T("Quantity"));
    $l->setName(_T("Fruits"));
    /* Add actions */
    $l->addActionItem(new ActionItem(_T("View fruit"), "view", "display", "fruit"));
    $l->addActionItem(new ActionPopupItem(_T("Delete fruit"), "view", "delete", "fruit"));
    $l->display();

    ?>

Thanks to addActionItem, we add to each row two actions: view
the fruit, and delete the fruit.

ActionItem constructor arguments are:

- action label ("View fruit"), displayed when the mouse hover
  on the action icon
- the web page ("view") of the current sub-module to use to
  perform the action
  These
- the CSS class ("display") to use to set the action icons
- the URL parameter name ("fruit") used to give to the web
  page that will perform the action the object. The content of the
  first row is always used as the parameter value.

In our example, the URL link for the first row will be:
``main.php?module=module1&submod=submodule1&action=view&fruit=apple``.
For the second row, "``...&fruit=banana``", etc.

Sometimes an action link needs to send the user to another module or submodule,
instead of the current one. To do this, you add these parameters to the
ActionItem constructor:

- $module: the module part of the URL link
- $submod: the sub-module part of the URL link
- $tab: the tab part of the URL link (if the link goes to a specific tab of a widget

ActionPopupItem displays a little popup page when clicked. This
is useful for actions that just need an extra validation to be
performed.

When there are actions, the first column cells are automatically
linked to the first action. But this can be disabled with:

::

    <?php

    $l->disableFirstColumnActionLink();

    ?>

The default size of the JavaScript popup window is 300 pixel. This
can be changed like this:

::

    <?php

    $p = new ActionPopupItem(_T("Delete fruit"), "view", "delete", "fruit");
    $p->setWidth(500); /* Size is now 500 px */
    $l->addActionItem($p);

    ?>

Conditional actions
-------------------

With the addActionItem method, you add an action to every row of a ListInfos
widget. In some cases, an action can't be performed for a specific row, so you
don't want the action link to be available.

The addActionItemArray method allows to pass to the ListInfos widget an array
of actions to display:

::

    <?php

    require ("includes/PageGenerator.php");
    $fruits = array("apple", "banana", "lemon", "papaya", "fig", "olive",
        "clementine", "orange", "mandarin", "grapes", "kumquat");
    $stock = array("5", "8", "40", "12", "40", "51", "12", "7", "9", "15", "21");
    $viewAction = new ActionItem(_T("View fruit"), "view", "afficher", "fruit");
    $deleteAction = new ActionPopupItem(_T("Delete fruit"), "view", "supprimer", "fruit");
    /* an EmptyActionItem will be displayed as a blank space */
    $emptyAction = new EmptyActionItem();
    $actionsView = array();
    $actionsDel = array();
    foreach($stock as $value) {
        if ($value < 10) {
            /* Only put the deleteAction link if value is lower than 10 */
            $actionsDel[] = $deleteAction;
            $actionsView[] = $emptyAction;
        } else {
            /* else only put the viewAction link */
            $actionsView[] = $viewAction;
            $actionsDel[] = $emptyAction;
        }
    }
    $l = new ListInfos($fruits, _T("Fruit name"));
    $l->addExtraInfo($stock, _T("Quantity"));
    $l->setName(_T("Fruits"));
    $l->addActionItemArray($actionsView);
    $l->addActionItemArray($actionsDel);
    $l->display();

    ?>

Ajaxified ListInfos
-------------------

A ListInfos widget content can be dynamically filtered.

First, we write the page that render the ListInfos widget. This
page gets the filter to apply to the ListInfos widget as a GET
parameter. Here is the code of
:file:`/usr/share/mmc/modules/module1/submodule1/ajaxFruits.php`:

::

    <?php

    $filter = $_GET["filter"];
    $fruits = array("apple", "banana", "lemon", "papaya", "fig", "olive",
        "clementine", "orange", "mandarin", "grapes", "kumquat");
    /* Make a fruit list using the filter */
    $filtered = array();
    foreach($fruits as $fruit) {
        if ($filter == "" or !(strpos($fruit, $filter) === False))
            $filtered[] = $fruit;
    }
    $l = new ListInfos($filtered, _T("Fruit name"));
    /*
    Instead of using the standard widget navigation bar, use the AJAX version.
    This version allows to keeps the filter when clicking on previous / next.
    */
    $l->setNavBar(new AjaxNavBar(count($filtered), $filter));
    $l->setName(_T("Fruits"));
    $l->display();

    ?>

This PHP code just displays a ListInfos widget where the
elements are filtered.

Now we create a page where the ListInfos widget is automatically
updated using a filter. Here is the code of
:file:`/usr/share/mmc/modules/module1/submodule1/index.php`:

::

    <?php

    require("localSidebar.php");
    require("graph/navbar.inc.php");
    /*
    Create the filtering form with a input field, and bind this input field
    to an AJAX updater that will use the specified URL to dynamically fill
    in a DIV (see below) container.
    */
    $ajax = new AjaxFilter(urlStrRedirect("module1/submodule1/ajaxFruits"));
    /* You can ask the AJAX updater to be called every 10s */
    $ajax->setRefresh(10000);
    $ajax->display();
    /* Set page title and left side bar */
    $p = new PageGenerator(sprintf(_T("Fruits"), "module1"));
    $p->setSideMenu($sidemenu);
    $p->display();
    /* Display the DIV container that will be updated */
    $ajax->displayDivToUpdate();

    ?>

In :file:`infoPackage.inc.php`, these two PHP scripts should be registered
like this:

::

    <?php

    $mod = new Module("module1");
    ...
    $submod = new SubModule("submodule1");
    ...
    /* Register the first page */
    $page = new Page("index", _T("Fruit list", "module1"));
    $submod->addPage($page);
    /* Register the page called using the AJAX DIV updater */
    $page = new Page("ajaxFruit");
    $page->setFile("modules/module1/submodule1/ajaxFruits.php",
        array("AJAX" => True, "visible" => False)
    );
    $submod->addPage($page);
    ...

    ?>

The ValidatingForm widget
-------------------------

This widget (a subclass of HtmlContainer) is a HTML form with
input fields validation. The form can't be validated (POSTed) if some
required fields are not filled in, or if their values don't match a
given regex.

A lot of MMC pages display a HTML form, containing a HTML table
with multiple rows of a single labeled input field. Here is an
example

::

    <?php

    /* Build a new validating form */
    $f = new ValidatingForm();
    /* Push a table into the form, and go to the table level */
    $f->push(new Table());
    /* Add two TR to the table */
    /* Ask for a given name */
    $f->add(
        new TrFormElement(_T("Given name"), new InputTpl("givenName"),
            array("value" => "", "required" => True)
        )
    );
    /* Ask for a family name */
    $f->add(
        new TrFormElement(_T("Family name"), new InputTpl("name"),
            array("value" => "", "required" => True)
        )
    );
    /* Go back to the validating form level */
    $f->pop();
    /* Add a button to the form */
    $f->addButton("bvalid", _T("Validate"));
    /* Close the form */
    $f->pop();
    /* Render all the form and the objects it contains */
    $f->display();

    ?>

The TrFormElement class creates objects that will render a HTML
row (a TR) with two columns (two TDs). The first column contains a
describing label, and the second column an input field. In the
example:

::

    <?php
    /* Ask for a given name */
    $f->add(
        new TrFormElement(_T("Given name"), new InputTpl("givenName"),
            array("value" => "", "required" => True)
        )
    );

    ?>

TrFormElement takes three argument:

- "Given name" is the label of the input field.
- InputTpl("givenName") is a standard HTML input field, with
  "givenName" as the HTML "name" attribute.
- array("value" => "", "required" => True) is an array
  of option for the InputTpl object. "value" => "" means the HTML
  "value" attribute of the input field is empty. "required" =>
  True means that the form can't be posted if the input field is
  empty.

See next section about all the InputTpl widget options.

The InputTpl based widgets
--------------------------

The InputTpl class allows to render a standard HTML input field.
The constructor takes two arguments:

- $name: the value of the "name" attribute of the INPUT HTML
  field

- $regexp: a regexp that must be matched by the input field,
  else the HTML form won't be posted. The regexp is used only if the
  input field is inserted into a ValidatingForm object. If not
  given, the default regexp is "/.+/".

When rendering the widget, additional options can be given to
the "display" method thanks to an array:

- "value": an empty string by default. That's the input field
  value.

- "required": False by default. If set to true and the
  InputTpl object is inside a ValidatingForm object, the form can't
  be posted if the field is empty

A lots of class that inherits from InputTpl have been written.
For example: MACInputTpl is an HTML input field that only accepts MAC
address, NumericInputTpl only accepts numeric value. Theses kind of
classes are very easy to write:

::

    <?php

    class NumericInputTpl extends InputTpl {
        function NumericInputTpl($name) {
            $this->name = $name;
            $this->regexp = '/^[0-9]*$/';
        }
    }
    class MACInputTpl extends InputTpl {
        function MACInputTpl($name) {
            $this->name = $name;
            $this->regexp = '/^(\[0-9a-f]{2}:){5}[0-9a-f]{2}$/i';
        }
    }

    ?>

The PopupForm widget
--------------------

This widget allows to build a MMC popup form triggered by a
ActionPopupItem very quickly. For example:

::

    <?php

    if (isset(_POST["bdel"])) {
        /* action to remove the fruit */
        ...
    } else {
        $fruit = urldecode($_GET["fruit"]);
        /* Create the form and set its title */
        $f = new PopupForm(_T("Delete a fruit"));
        /* Add a little description text */
        $f->addText(_T("This action will delete all the fruit"));
        /*
        Put a hidden input field into the form.
        The HiddenTpl is explained later in this document
        */
        $hidden = new HiddenTpl("fruit");
        /* Add this field to the form */
        $f->add($hidden, array("value" => $fruit, "hide" => True));
        /* Add validation and cancel buttons */
        $f->addValidateButton("bdel");
        $f->addCancelButton("bback");
        $f->display();
    }

    ?>

The NotifyWidgetSuccess and NotifyWidgetFailure class
-----------------------------------------------------

These two widgets displays a javascrip popup with a message,
with a OK button.

::

    <?php

    /* Error message popup */
    new NotifyWidgetFailure(_T("Error ! /o\"));
    /* Success */
    new NotifyWidgetSuccess(_T("Reboot was successful ! \o/"));

    ?>

Creating page with tabs
-----------------------

This widget allows to include a tab selector that displays a page
when clicking on a tab.

For example:

::

    <?php

    require("localSidebar.php");
    require("graph/navbar.inc.php");
    /* We use the TabbedPageGenerator class */
    $p = new TabbedPageGenerator();
    /* Set the sidemenu, as the PageGenerator class */
    $p->setSideMenu($sidemenu);
    /*
    Not required: you can add some content above the tab selector
    The content is a title, and a PHP file to include.
    */
    $p->addTop("Page title", "modules/module1/submodule1/top.php");
    /*
    Now we add new tab to the tab selector.
    Each tab is associated to an id, a tab title, a page title, and a PHP file to include.
    */
    $p->addTab("tab1", "Tab 1 title", "Page 1 title", "modules/module1/submodule1/tab1.php");
    $p->addTab("tab2", "Tab 2 title", "Page 2 title", "modules/module1/submodule1/tab2.php");
    $p->addTab("tab3", "Tab 3 title", "Page 3 title", "modules/module1/submodule1/tab3.php");
    /*
    You can add a fifth argument, which is an array of URL parameters
    that will be used when building the URL link of the tab.
    */
    $p->addTab("tab4", "Tab 4 title", "Page 4 title", "modules/module1/submodule1/tab4.php",
        array("uid" => "foo")
    );
    $p->display();

    ?>

If no tab is selected, the first tab is automatically activated.

To build a tab URL link, the current module, submodule and action are used, with the given tab id and the given array of URL parameters.
For example:

::

    <?php

    $p->addTab("tab4", "Tab 4 title", "Page 4 title", "modules/module1/submodule1/tab4.php",
        array("uid" => "foo")
    );

    ?>

will build this link: ``module=currentmod&submod=currentsubmod&action=currentaction&tab=tab4&uid=foo``
