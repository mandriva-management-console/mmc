#!/bin/sh
# (c) 2009 Open Systems Specilists - Glen Ogilvie.  License: GPL
# Example show status shell script

# print the status
tc -s qdisc ls dev eth2
tc filter show dev eth2
tc class show dev eth2
