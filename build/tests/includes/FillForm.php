<?php

function RandomASCIIString($MaxLength) {
	$Answer = '';
	$Length = rand(1, $MaxLength);
	for ($i=0; $i<$Length; $i++) {
		$Answer .= chr(rand(32, 127));
	}
	return $Answer;
}

function RandomEmailString($MaxLength) {
	$Answer = '';
	$Length = rand(4, $MaxLength-5);
	for ($i=0; $i<$Length; $i++) {
		$Answer .= chr(rand(97, 122));
	}
	$Offset = rand(0, $Length-1);
	$part1 = substr($Answer, 0, $Offset);
	$part2 = substr($Answer, $Offset);

	$part1 = $part1 . '@';
	$Answer = $part1 . $part2 . '.com';
	return $Answer;
}

function RandomNumberString($MaxLength) {
	$Answer = '';
	$Length = rand(0, $MaxLength);
	for ($i=0; $i<=$Length; $i++) {
		$Answer .= chr(rand(48, 57));
	}
	return $Answer;
}

function RandomDateString($startDate,$endDate){
	$days = round((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24));
	$n = rand(0,$days);
	return date("d/m/Y",strtotime("$startDate + $n days"));
}

function FillFormWithRandomData($FormDetails) {
	foreach ($FormDetails['Texts'] as $Name=>$Value) {
		foreach ($Value as $Field) {
			if (isset($Field['class']) and $Field['class']=='number') {
				$PostData[$Field['name']]=RandomNumberString($Field['maxlength']);
			} else if (isset($Field['class']) and $Field['class']=='date') {
				$PostData[$Field['name']]=RandomDateString('2000-01-01', '2012-12-31');
			} else if (isset($Field['class']) and $Field['class']=='email') {
				$PostData[$Field['name']]=RandomEmailString($Field['maxlength']-6);
			} else {
				$PostData[$Field['name']]=RandomASCIIString($Field['maxlength']);
			}
		}
	}
	foreach ($FormDetails['Selects'] as $Name=>$Value) {
		foreach ($Value as $FieldName=>$Field) {
			$ChosenOption=$Field['options'][rand(0, sizeOf($Field['options'])-1)]['value'];
			$PostData[$FieldName]=$ChosenOption;
		}
	}
	foreach ($FormDetails['Hiddens'] as $Name=>$Value) {
		foreach ($Value as $FieldName=>$Field) {
			$PostData[$Field['name']]=$Field['value'];
		}
	}
	foreach ($FormDetails['Submits'] as $Name=>$Value) {
		foreach ($Value as $FieldName=>$Field) {
			$PostData[$Field['name']]=$Field['value'];
		}
	}
	return $PostData;
}

?>