#!/bin/bash

ROOT_DIR=$PWD
cd $ROOT_DIR
for f in `find . -name "*.php"`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

    output=`php_beautifier -f $filename -o ~/test/$newname -t -l "ArrayNested() IndentStyles(style=k&r) NewLines(before=T_CLASS:function:T_COMMENT,after=T_COMMENT) Lowercase()"`
    if [ $? != 0 ]
    then
	echo $filename
	echo $output
    fi
done

for f in `find . -name "*.inc"`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"

    output=`php5 -l $filename`
    if [ $? != 0 ]
    then
        echo $filename
        echo $output
    fi
done

