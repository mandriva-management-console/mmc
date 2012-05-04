==============================================
How to write a python module for the MMC agent
==============================================

Related documentations
======================

- `Full MMC Python API documentation <http://mds.mandriva.org/content/epydoc-trunk/>`_.
- :doc:`python-code`
- `Some basic Python / LDAP bindings documentation <http://python-ldap.sourceforge.net/doc/python-ldap/index.html>`_.

Creating a Python module
========================

Each MMC agent module must be located in the
:file:`$PYTHONPATH/site-packages/mmc/plugins` directory.

When the MMC agent starts, it looks for all Python modules in this
path, and tries to activate them.

Each MMC Python module must declare a function call "activate".
This function should make all needed tests that ensures the module will
works. This function returns True if all the tests are OK, else False.
In the later case, the MMC agent will give up on this module, and won't
export it on the network.

The following method must also be implemented

- getVersion: must return the MMC version of the Python
  module, which is the same then the MDS version number

- getApiVersion: must return the Python module API
  number

- getApiRevision: must return the SVN revision number

Here is a MMC Python module skeleton. For example
:file:`/usr/lib/python2.5/site-packages/mmc/plugins/modulename/__init__.py`:

.. code-block:: python

    VERSION = "2.0.0"
    APIVERSION = "4:1:3"
    REVISION = int("$Rev$".split(':')[1].strip(' $'))
    def getVersion(): return VERSION
    def getApiVersion(): return APIVERSION
    def getRevision(): return REVISION
    def activate(): return True

A MMC Python module is in the Python language terminology a
"package". So making a :file:`__init__.py` file is required to make Python
treats a directory as containing a package. Please read `this
section <http://docs.python.org/release/2.6.7/tutorial/modules.html>`_
from the Python language tutorial to know more about Python packages system.


Python module configuration file
================================

The module configuration file must be located into the
:file:`/etc/mmc/plugins/module_name.ini` file.

The configuration file should be read using a PluginConfig class
instance. This class inherits from the :py:mod:`ConfigParser` class.

This configuration file must at least contains a "main" section
with the "disable" option, telling if the module is disabled or
not:

::

    [main]
    disable = 0

If the configuration file doesn't exist, or doesn't have the
"disable" option, the module is by default considered as
disabled.

::

    from mmc.support.config import PluginConfig, ConfigException

    class ModulenameConfig(PluginConfig):

        def setDefault(self):
            """
            Set good default for the module if a parameter is missing the
            configuration file.
            This function is called in the class constructor, so what you
            set here will be overwritten by the readConf method.
            """
            PluginConfig.setDefault(self)
            self.confOption = "option1"
            # ...

        def readConf(self):
            """
            Read the configuration file using the ConfigParser API.
            The PluginConfig.readConf reads the "disable" option of the
            "main" section.
            """
            PluginConfig.readConf(self)
            self.confOption = self.get("sectionname", "optionname")
            # ...

        def check(self):
            """
            Check the values set in the configuration file.
            Must be implemented by the subclass. ConfigException is raised
            with a corresponding error string if a check fails.
            """
            if not self.confOption: raise ConfigException("Conf error")

        def activate():
            # Get module config from "/etc/mmc/plugins/module_name.ini"
            config = ModulenameConfig("module_name")
            ...
            return True

Exporting Python module API
===========================

All methods defined in the Python module are exported by the MMC
agent, and can be directy called using XML-RPC.

For example:

::

    def activate():
        return True

    # Module attribute can't be exported with XML-RPC
    value = 1234

    # This method will be exported
    def func1(arg1A, arg1B):
        # ...
        return SomeClass().func1(arg1A, arg1B)

    # This method will be exported too
    def func2(arg2A, arg2B):
        # ...
        return SomeClass().func2(arg2A, arg2B)

    # Class can't be exported with XML-RPC !
    class SomeClass:
        def func1(self, argA, argB):
            # ...
            return "xxx"

        def func2(self, argA, argB):
            # ...
            return "zzz"

How to launch shell commands inside a Python module
===================================================

As the MMC agent is written on top of Python Twisted, you can't
use the dedicated standard Python modules (like commands or popen) to
run shell commands. You must use the Twisted API, and write `ProcessProtocol
classes <http://twistedmatrix.com/projects/core/documentation/howto/process.html>`_.

But we provide simple ProcessProtocol based functions to run a
process, and get its outputs.

Blocking mode
-------------

In blocking mode, if we start a shell command, the twisted server
will loop until a process terminates. Blocking mode should not be
used for functions that can be called by XML-RPC, because they will
completely block the server.
The server won't process other requests until the blocking code is
terminated.

But when using the MMC API in command line, it's simpler to use the
blocking mode.

Here is an example:

::

    # Import the shLaunch method
    from mmc.support.mmctools import shLaunch
    # Run "ls -l"
    # shLaunch returns once the shell command terminates
    proc = shLaunch("ls -l")
    # Return shell command exit code
    print proc.exitCode
    # Return shell command stdout
    print proc.out
    # Return shell command stderr
    print proc.err

Non blocking mode
-----------------

Non blocking-mode should be used when a method called by XML-RPC
may block.
Basically, the method should not return the result, but a Deferred
object attached to a callback corresponding to the result.
The twisted reactor will process the deferred, send the result
to the callback, and the callback will finally return the wanted
result.

Here is an example:

::

    # Import the shLaunchDeferred method
    from mmc.support.mmctools import shLaunchDeferred

    def runLs():
        def cb(shprocess):
            # The callback just return the process outputs
            return shprocess.exitCode, shprocess.out, shprocess.err
        d = shLaunchDeferred("ls -l")
        # shLaunchDeferred returns a Deferred() object
        # We add the cb function as a callback
        d.addCallback(cb)
        # We return the Deferred() object
        return d

For more explanation about Python Twisted and Deferred objects, please read
`this page <http://twistedmatrix.com/projects/core/documentation/howto/defer.html>`_.

To use the runLs function in a python script, without the XML-RPC server:

::

    from twisted.internet import reactor, defer
    from xxx import runLs

    def printResult(ret):
        print ret
        reactor.stop()

    d = runLs()
    # runLs returns a deferred object, we add a callback that is just printing the result
    d.addCallback(printResult)
    reactor.run()
