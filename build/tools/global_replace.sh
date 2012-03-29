#!/bin/bash

ROOT_DIR=$PWD
cd $ROOT_DIR
for f in `find . -name "*.php"`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

	mv $filename $filename'.old'
    sed 's/'$1'/'$2'/g' $filename'.old' > $filename
    if [ $? != 0 ]
    then
	echo $filename
	echo $output
    fi
    rm $filename'.old'
done

for f in `find . -name "*.inc"`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

	mv $filename $filename'.old'
    sed 's/'$1'/'$2'/g' $filename'.old' > $filename
    if [ $? != 0 ]
    then
	echo $filename
	echo $output
    fi
    rm $filename'.old'
done

