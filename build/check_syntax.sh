#!/bin/bash

ROOT_DIR=$PWD
cd $ROOT_DIR
for f in `find . -name "*.php" -o -name "*.inc"`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

    output=`php5 -l $filename 2> /dev/null`
    if [ $? != 0 ]
    then
		echo '**Error** '$output >> ~/weberp$(date +%Y%m%d).log
    fi
done
