#!/bin/bash
export PATH="/bin:/usr/bin"
QUOTAFILE=$1
if [ ! -O $QUOTAFILE ]; then
	echo "Cannot find file to apply quotas"
	exit 1;
fi

QUOTABASE=$(basename $QUOTAFILE)
scp $QUOTAFILE fileserver:~/
ssh -t fileserver /bin/sh ~/$QUOTABASE
ssh fileserver mv ~/$QUOTABASE ~/applied_quotas/$QUOTABASE-$(date -I)
