#!/bin/bash

tags=('p' 'option' 'table' 'th' 'td' 'tr' 'a' 'div' 'form' 'font')

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
		output_opening=`grep -i $tag $filename | awk -F$tag '{ print NF-1}' | awk '{ SUM += $1} END { print SUM }'`
		output_closing=`grep -i $ctag $filename | awk -F$ctag '{ print NF-1}' | awk '{ SUM += $1} END { print SUM }'`
		if [[  $output_opening != ""  && $output_closing != ""  &&  $output_opening != $output_closing ]]
		then
			echo $newname' has '$[$output_opening-$output_closing]' problem lines for tag <'$tagname'>'
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
		ctag='</'$tagname'>'
		output_opening=`grep -i $tag $filename | awk -F"$tag" '{ print NF-1}' | awk '{ SUM += $1} END { print SUM }'`
		output_closing=`grep -i $ctag $filename | awk -F"$ctag" '{ print NF-1}' | awk '{ SUM += $1} END { print SUM }'`
#		echo $tagname' '$tag' '$ctag
		if [[  $output_opening != ""  && $output_closing != ""  &&  $output_opening != $output_closing ]]
		then
			echo $newname' has '$[$output_opening-$output_closing]' problem lines for tag <'$tagname'>'
		fi
	done
done
