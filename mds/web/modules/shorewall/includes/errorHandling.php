<?php
/**
 * (c) 2014 Mandriva, http://www.mandriva.com
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
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

$errItem = new ErrorHandlingItem(": Invalid shorewall line");
$errItem->setMsg(_T("The rule is invalid", "shorewall"));
$errItem->setLevel(1);
$errItem->setTraceBackDisplay(False);
$errObj->add($errItem);

$errItem = new ErrorHandlingItem(": Invalid port number");
$errItem->setMsg(_T("Invalid port number", "shorewall"));
$errItem->setAdvice(_T("Port should be between 0 and 65535.", "shorewall"));
$errItem->setLevel(1);
$errItem->setTraceBackDisplay(False);
$errObj->add($errItem);

$errItem = new ErrorHandlingItem(": Invalid port range");
$errItem->setMsg(_T("Invalid port range", "shorewall"));
$errItem->setAdvice(_T("Ports should be between 0 and 65535 and the left side port must be lower that the right side port.", "shorewall"));
$errItem->setLevel(1);
$errItem->setTraceBackDisplay(False);
$errObj->add($errItem);

?>
