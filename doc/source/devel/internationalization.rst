=====================================
Internationalization and localization
=====================================

The MMC uses the GNU gettext system to produce multi-lingual
messages. If you are not famliar with GNU gettext, please read `the GNU
gettext manual <http://www.gnu.org/software/gettext/manual/gettext.html>`_.

Two special PHP methods are needed to translate the
interface:

- _($msg): the underscore is a PHP alias for the gettext($msg)
  method. The gettext method looks up a message in the current text
  domain. The default text domain is the one from the MMC "base"
  module. In other words, the _("$msg") method can be only used to
  translate strings from the MMC "base" module.

- _T($msg, $module): this function looks up a message for a
  given module. So if you create MMC web module called "module1", to
  translate a message you write:

  ::

      echo _T("This is a message to translate", "module1");

  As the module name is already in the URL to be displayed (see
  :ref:`mmc-page-display`, if you don't specify a module
  name it can be automatically guessed.
