<?php

# ACL support missing on partition containing /home/samba/
$errItem = new ErrorHandlingItem('\[Errno 95\] Operation not supported');
$errItem->setMsg(_T("ACLs are not supported in your partition containing /home/samba/"));
$errItem->setAdvice(_T("Try to remount your partition with ACLs support
			<ul>
			<li>You could use XFS which support ACLs natively</li>
			<li>For ext3 filesystem, add \"acl\" to mount options in /etc/fstab<br/>
			    <pre>ie: /dev/hda6  /home  ext3  defaults,acl  1  2</pre></li>
			</ul>
			"));
$errObj->add($errItem);


$errItem = new ErrorHandlingItem('share "([A-Za-z0-9]*)" does not exist');
$errItem->setMsg(_T("This share does not exist"));
$errItem->setAdvice(_T("Verify specified share exist."));

$errObj->add($errItem);


$errItem = new ErrorHandlingItem('This share already exists');
$errItem->setMsg(_T("This share already exist"));
$errItem->setAdvice(_T("<ul>
                           <li>Delete this share before recreate it.</li>
                           <li>Choose another share name</li>
                        </ul>"));
$errItem->setLevel(0);
$errItem->setSize(450);
$errItem->setTraceBackDisplay(false);
$errObj->add($errItem);



?>
