#!/bin/sh
# (c) 2009 Open Systems Specilists - Glen Ogilvie.  License: GPL
# Example setup shell script

tc qdisc del dev eth2 root
tc qdisc add dev eth2 root handle 1: htb default 1
tc class add dev eth2 parent 1: classid 1:1 htb rate 10000mbit burst 0 ceil 10000mbit
tc qdisc add dev eth2 parent 1:1 handle 10: sfq perturb 10
