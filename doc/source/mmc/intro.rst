============
Introduction
============

The MMC (**Mandriva Management Console**) is made of two parts:

- An agent running on the machine to manage. We call it « MMC agent ».
  The agent exports to the network several plugins that allow to manage the
  machine. Of course, there can be multiple agents running on the network.
  The agent and its plugins are written in Python.

- A web interface, that talks to the agent(s) using XML-RPC.
  The interface is written in PHP, and use the scriptaculous and prototype 
  frameworks to provide an AJAX experience across all major browsers including
  Internet Explorer 6.

In this document, we will first explain how to install and configure the MMC
agent and the base plugins, and then how to install the web interface.

The MMC core provides 3 plugins:

- **base** : a plugin for managing users and groups in LDAP
- **ppolicy** : a plugin for managing user password policies
- **audit** : a framework for recording all operations done in the MMC interface

.. note:: Other plugins are available in the :ref:`mds` and :ref:`pulse2` projects.

These installations instructions are generic: this means they should work on
most Linux distributions.

If you have any installation issues, please use the `MDS users mailing list
<http://mds.mandriva.org/wiki/MailingLists>`_.
