#!/bin/bash

ROOT_DIR=$PWD
cd $ROOT_DIR
ClosedTagTests=("<input" "<img" "<br")
QuotingTests=("maxlength" "size" "colspan" "cellpadding" "cellspacing" "tabindex")
UnquotedTypes=("submit" "hidden" "text" "checkbox" "radio" "file")
for f in `find . -name "*.php" -o -name "*.inc" -o -name "*.html"`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/$newname"
	if [[ $filename != */locale/* && $filename != *Change.log.html && $filename != */phplot/* && $filename != */tcpdf/* && $filename != */build/* ]]
	then
		for test in `seq 1 "${#ClosedTagTests[@]}"`;
		do
			count=0
			output=()
			while read -r line; do
				output[((count++))]="$line"
			done < <(grep -i "${ClosedTagTests[test-1]}"  $filename -n  | grep '/>' -v)
			if [ "${#output[@]}" != 0 ]
			then
				for i in `seq 1 "${#output[@]}"`;
				do
					echo $filename >> ~/weberp$(date +%Y%m%d).log
					echo '**Warning** Line number '`echo ${output[i-1]} | cut -f1 -d':' `' appears to have an '${ClosedTagTests[test-1]}' tag that is not closed' >> ~/weberp$(date +%Y%m%d).log
					echo '' >> ~/weberp$(date +%Y%m%d).log
				done
			fi
		done
		for test in `seq 1 "${#QuotingTests[@]}"`;
		do
			for index in `seq 0 9`;
			do
				count=0
				output=()
				while read -r line; do
					output[((count++))]="$line"
				done < <(grep -i " ${QuotingTests[test-1]}=$index"  $filename -n)
				if [ "${#output[@]}" != 0 ]
				then
					for i in `seq 1 "${#output[@]}"`;
					do
						echo $filename >> ~/weberp$(date +%Y%m%d).log
						echo '**Warning** Line number '`echo ${output[i-1]} | cut -f1 -d':' `' appears to have a '${QuotingTests[test-1]}' attribute the value of which is not quoted' >> ~/weberp$(date +%Y%m%d).log
						echo '' >> ~/weberp$(date +%Y%m%d).log
					done
				fi
			done
		done
		for test in `seq 1 "${#UnquotedTypes[@]}"`;
		do
			count=0
			output=()
			while read -r line; do
				output[((count++))]="$line"
			done < <(grep -i "type=${UnquotedTypes[test-1]}"  $filename -n)
			if [ "${#output[@]}" != 0 ]
			then
				for i in `seq 1 "${#output[@]}"`;
				do
					echo $filename >> ~/weberp$(date +%Y%m%d).log
					echo '**Warning** Line number '`echo ${output[i-1]} | cut -f1 -d':' `' appears to have a '${QuotingTests[test-1]}' attribute that is not quoted' >> ~/weberp$(date +%Y%m%d).log
					echo '' >> ~/weberp$(date +%Y%m%d).log
				done
			fi
		done
	fi
done

# grep -i '<center>' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

# grep -i 'textbox' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
