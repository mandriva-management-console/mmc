#!/bin/bash
# (c) 2009 Open Systems Specilists - Glen Ogilvie.  License: GPL
# Example remove shell script

# uid in decimal
USERID=$1

# uid in hex
CLASSID=$2

# rate limits must match those in tcruleadd.sh and setupTCrules.sh must be run if changed
RATE="32kbps burst 0 ceil 32kbps"

# filter handle matches watchdog fwmark+uid
# echo "
tc filter del dev eth2 parent 1: protocol ip prio 1 handle 0x${CLASSID} fw flowid 1:$CLASSID
tc qdisc del dev eth2 parent 1:$CLASSID handle $CLASSID: sfq perturb 10
tc class del dev eth2 parent 1: classid 1:$CLASSID htb rate $RATE 
# " >> out.txt