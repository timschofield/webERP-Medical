#!/bin/bash

ROOT_DIR=~/workbench/weberp-bzr/web-erp/trunk
cd $ROOT_DIR
for f in `find . -name "*.php"`
do
    #need to lop off leading './' from filename, but I havent worked out how to use
    #cut yet
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

    output=`php5 -l $filename 2>/dev/null`
    if [ $? != 0 ]
    then
	echo $filename
	echo $output
    fi
done

for f in `find . -name "*.inc"`
do
    #need to lop off leading './' from filename, but I havent worked out how to use
    #cut yet
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

    output=`php5 -l $filename 2>/dev/null`
    if [ $? != 0 ]
    then
        echo $filename
        echo $output
    fi
done

