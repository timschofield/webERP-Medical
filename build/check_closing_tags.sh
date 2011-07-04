#!/bin/bash

tags=('p', 'option', 'table')

ROOT_DIR=$PWD
cd $ROOT_DIR
for f in `find . -name "*.php" | sort`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"
	for tagname in "${tags[@]}"
	do
		tag='<'$tagname
		ctag='</'$tagname
		output_opening=`grep $tag -c $filename`
		output_closing=`grep $ctag -c $filename`
		if [ $output_opening != $output_closing ]
		then
			echo $newname' has '$[$output_opening-$output_closing]' problem lines for tag '$tagname
		fi
	done
done

for f in `find . -name "*.inc" | sort`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"
	for tagname in "${tags[@]}"
	do
		tag='<'$tagname
		ctag='</'$tagname
		output_opening=`grep $tag -c $filename`
		output_closing=`grep $ctag -c $filename`
		if [ $output_opening != $output_closing ]
		then
			echo $newname' has '$[$output_opening-$output_closing]' problem lines for tag '$tagname
		fi
	done
done
