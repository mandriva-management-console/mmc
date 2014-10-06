#!/bin/bash
set -x

if [ "`dirname $0`x" != ".x" ]; then
	echo "Make  sure you run the script from where `basename $0` is located"
	exit 1
fi

CD=$PWD
mds_version=2.5.1
mds_dir=$CD/../../mds
spec_dir=$CD/mds

echo "Build MDS distribution"
pushd $mds_dir
./autogen.sh
./configure --prefix=/usr --sysconfdir=/etc --localstatedir=/var
make
make dist
popd

echo "packaging"
src_targz_name=mds-${mds_version}.tar.gz
if [ ! -f "${mds_dir}/${src_targz_name}" ]; then
	echo "MDS package does not exits, nothing to do. bye!"
	exit 2
fi
rm -rf ${spec_dir}/RPMS
rm -rf ${spec_dir}/SOURCES
mkdir ${spec_dir}/SOURCES
mv ${mds_dir}/${src_targz_name} ${spec_dir}/SOURCES
pushd ${spec_dir}/SPECS
bm -l mds.spec
popd

echo "gather RPMs"
rm -r *.rpm
cp ${spec_dir}/RPMS/x86_64/mmc-web-samba4-2.5.1-1.x86_64.rpm ./
cp ${spec_dir}/RPMS/x86_64/python-mmc-samba4-2.5.1-1.x86_64.rpm ./
cp ${spec_dir}/RPMS/x86_64/python-s4sync-2.5.1-1.x86_64.rpm ./
if [ ! -f mmc-web-samba4-2.5.1-1.x86_64.rpm ] || [ ! -f python-mmc-samba4-2.5.1-1.x86_64.rpm ]; then
	echo "No RPMs to gather..."
	exit 3
fi

echo "done."
