#!/bin/bash

ROOT_DIR=$PWD
cd $ROOT_DIR
for f in `find . -name "*.php" | sort`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"
	tag='<'$1
	ctag='</'$1
    output_opening=`grep $tag -c $filename`
    output_closing=`grep $ctag -c $filename`
    if [ $output_opening != $output_closing ]
    then
	echo $filename
#	echo $output
    fi
done

for f in `find . -name "*.inc" | sort`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

    output_opening=`grep $tag -c $filename`
    output_closing=`grep $ctag -c $filename`
    if [ $output_opening != $output_closing ]
    then
	echo $filename
#	echo $output
    fi
done
