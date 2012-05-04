===================
Writing MMC scripts
===================

Following are a few examples of scripts that you can write using the
`MMC API <http://mds.mandriva.org/content/epydoc/frames.html>`_.

This script adds a few users to the LDAP directory:

::

    #!/usr/bin/env python

    from mmc.plugins.base import ldapUserGroupControl

    users = [('login1', 'passwd1', 'firstname1', 'lastname1'),
             ('login2', 'passwd2', 'firstname2', 'lastname2'),
             ('login3', 'passwd3', 'firstname3', 'lastname3'),
             # ...
             ]

    l = ldapUserGroupControl()

    for login, password, firstname, lastname in users:
        # Store user into LDAP
        l.addUser(login, password, firstname, lastname)
        # Change user "mail" attribute value
        l.changeUserAttributes(login, 'mail', login + '@example.com')


This script creates SAMBA users into the LDAP directory, with all needed mail
attributes for Postfix delivery:

::

    #!/usr/bin/env python

    from mmc.plugins.samba import sambaLdapControl
    from mmc.plugins.mail import MailControl

    users = [('username', 'pass', 'name', 'lastname'),
            ('username2', 'pass2', 'name2', 'lastname2'),
            ]

    l = MailControl()
    s = sambaLdapControl()

    # Add group 'allusers' to the LDAP
    l.addGroup('allusers')
    for login, password, firstname, lastname in users:
        # Create user into the LDAP
        l.addUser(login, password, firstname, lastname)
        # Add user to a group 'allusers'
        l.addUserToGroup('allusers', login)
        # Set user mail
        l.changeUserAttributes(login,'mail',login+'@domain')
        # Add user needed mail objectClass
        l.addMailObjectClass(login,login)
        # Set user mail quota
        l.changeUserAttributes(login,'mailuserquota','512000')
        # Set user mail alias
        l.changeUserAttributes(login,'mailalias','allusers')
        # Add all SAMBA related attributes to the user
        # The SAMBA account will log in with the given SAMBA password
        s.addSmbAttr(login, password)
        # Set user POSIX account password (set the userPassword LDAP field)
        l.changeUserPasswd(login,password)
        # Enable mail delivery for this user
        l.changeMailEnable(login, True)
