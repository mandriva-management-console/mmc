===========================
Style guide for python code
===========================

Coding conventions for the python code of all MMC components

Introduction
############

A lot of MMC components are written in Python, among them the MMC agent and
its python plugins.

This document sets the coding conventions for the Python code of all MMC
components.

This document is totally based on Guido Van Rossum "Style Guide for ython Code"
document (see http://www.python.org/dev/peps/pep-0008/): you must read it too.
This document only emphases on important coding conventions.

Code layout
###########

Indentation: use 4 spaces per indentation level, no tabs allowed. It's ok with
Emacs Python mode.

Encoding: the source code must always use the UTF-8 encoding.

Whitespace in Expressions and Statements
########################################

::

    Yes: spam(ham[1], {eggs: 2})
    No:  spam( ham[ 1 ], { eggs: 2 } )
    Yes: if x == 4: print x, y; x, y = y, x
    No:  if (x == 4): print x, y; x, y = y, x
    No:  if x == 4 : print x , y ; x , y = y , x
    Yes: spam(1)
    No:  spam (1)
    Yes: dict['key'] = list[index]
    No:  dict \['key'] = list \[index]
    Yes:
    x = 1
    y = 2
    long_variable = 3
    No:
    x             = 1
    y             = 2
    long_variable = 3

Naming conventions
##################

Module name: short, lowercase names, without underscores

Class Names: CapitalizedWords

Functions Names: mixedCase for instance method, lower_case_with_underscores for other.

Constants: UPPER_CASE_WITH_UNDERSCORE

Comments
########

They are written in english.

They always start with a capitalized first word.

There is always a space between the # and the begin of the comment.

Docstrings
##########

All modules, functions and classes must have a docstring.

The docstring must be written in the Epytext Markup Language
format. We use epydoc to generate the API documentation. See
http://epydoc.sourceforge.net/epytext.html and
http://epydoc.sourceforge.net/fields.html for more
information.

The recommanded epydoc fields are:

::

    def foo(a, b, c):
        """
        This methods performs funny things.
        @param a: first parameter of foo
        @type a: int
        @param b: second parameter of foo
        @type b: str
        @param c: third parameter of foo
        @type c: unicode
        @raise ExceptionFoo: raised if b == 'bar'
        @rtype: int
        @return: the result should be 42
        """

Remarks:

- Sometimes the method description can be written in @return if the function
  is simple.
- If you skip @param because the parameter name seems really explicit to you,
  use at least: @rtype and @return
- Please use a spellchecker for your docstrings

Python module import rules
##########################

``from mod import *`` is forbidden, because it doesn't allow us to track module
dependencies effectively.

The import order should be:

::

    # Import standard python module
    import os
    import sys
    # Import external modules (SQLAlchemy, Twisted, python-ldap, etc.)
    from sqlalchemy.orm import create_session
    # Import internal modules
    from mmc.plugins.base import ...

SQLAlchemy code convention
##########################

Querying with the ORM
=====================

Here are the recommended code guidelines when querying using the ORM:

- First select the objects you want as a result:

  ::

      results = session.query(Table1).add_entity(Table2).add_entity(...)

  If your query will return more than one row, please call the query "results",
  or "rows". If you are querying for one object only, please use a variable name
  corresponding to this object.

- Then if needed perform a join between the tables. It is usually done using
  join in a select_from expression

  ::

      .select_from(table1.join(table2).join(...))

- Then add filter expressions to filter down the query:

  ::

      .filter(Table1.num == 42)
      .filter(Table2.num == -42)

  Please use "Table1.num" instead of "table1.c.num", because it's more pythonish.

- At least add the query limit:

  ::
      .all() # .first() .one(), or count()

Here is the complete query code:

::

    results = session.query(Table1).add_entity(Table2).add_entity(...)
    .select_from(table1.join(table2).join(...))
    .filter(Table1.num == 42)
    .filter(Table2.num == -42)
    .all()
    # Also accepted
    results = session.query(Table1).add_entity(Table2).add_entity(...)
    select_from(table1.join(table2).join(...))
    filter(Table1.num == 42)
    filter(Table2.num == -42)
    all()
    # Also accepted
    results = session.query(Table1).add_entity(Table2).add_entity(...)
    results = results.select_from(table1.join(table2).join(...))
    results = results.filter(Table1.num == 42)
    results = results.filter(Table2.num == -42)
    results = results.all()

If you're looking for one result only (e.g. to get the properties of an object
or check its existence) please use "one()" instead of "first()". "one()" will
raise an exception if no object or more than one objects if returned, and so it
forces you to deal with the exception.

Tools to check Python code
##########################

Use the ``pyflakes`` tool to check your code. The code must be fixed if these
messages are displayed:

- "import * used; unable to detect undefined names"
- "'x' undefined variable"
- "'x' imported but unused"

Python language version compatibility
#####################################

The code must be compatible with Python 2.5. That's a rather old version,
but we never had any problems that forced us to use a newer version.

Python additional library compatibility
#######################################

The code must be compatible with these library versions:

- Python Twisted: 8.1.0
- Python LDAP: 2.0
- Python SQLAlchemy: 0.5

Python code copyright header
############################

Here is the header that must be used:

::

    # -*- coding: utf-8; -*-
    #
    # (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
    # (c) 2007-2011 Mandriva, http://www.mandriva.com
    #
    # This file is part of Mandriva Management Console (MMC).
    #
    # MMC is free software; you can redistribute it and/or modify
    # it under the terms of the GNU General Public License as published by
    # the Free Software Foundation; either version 2 of the License, or
    # (at your option) any later version.
    #
    # MMC is distributed in the hope that it will be useful,
    # but WITHOUT ANY WARRANTY; without even the implied warranty of
    # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    # GNU General Public License for more details.
    #
    # You should have received a copy of the GNU General Public License
    # along with MMC.  If not, see <http://www.gnu.org/licenses/>.
