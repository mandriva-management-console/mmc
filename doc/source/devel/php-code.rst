.. highlight:: php

========================
Style guide for PHP code
========================

Coding conventions for the PHP code of all MMC components.

Introduction
############

This document sets the coding conventions for the PHP code of all
MMC components (like the MMC web interface for example).

Convention from http://pear.php.net/manual/en/standards.php apply too.

Code layout
###########

Indentation: use 4 spaces per indentation level, no tabs allowed.

Encoding: the source code must always use the UTF-8 encoding.

Code indentation and organisation
#################################

block "for", "function", "switch", "if", etc... always end with
opening braces on the same line.

::

    <?php

    if ($val == value) {
        echo $val;
    } else {
        return -1;
    }
    foreach ($arrParam as $singleItem) {
        print $singleItem;
    }

    ?>

Function with long args (more than one line size)

::

    <?php

    myFunction($value1,
        $value2,
        $morevalue4,
        $val5);

    ?>

Comments
########

They are written in english.

They always start with a capitalized first word.

There is always a space between the // and the begin of the comment.
// and /* are fine. Don't use #.

All functions must have a correct doxygen header.

Naming conventions
##################

- ClassName : CapitalizedWords
- functionName : mixedCase for all function name
- _membersValue : member value of a class begin with a "_"

PHP language version compatibility
##################################

The code must be compatible with PHP 5.0.

PHP error reporting level
#########################

All possible PHP errors, warnings and notices must be fixed in the PHP code.
Use these lines in your :file:`php.ini` file when working on the code to find
them all:

::

    error_reporting = E_ALL
    display_errors = On

PHP code copyright header
#########################

Here is the header that must be used:

::

   /**
    * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
    * (c) 2007-2011 Mandriva, http://www.mandriva.com
    *
    * This file is part of Mandriva Management Console (MMC).
    *
    * MMC is free software; you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation; either version 2 of the License, or
    * (at your option) any later version.
    *
    * MMC is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with MMC.  If not, see <http://www.gnu.org/licenses/>.
    */
