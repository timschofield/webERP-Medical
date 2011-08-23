<?php

function RandomASCIIString($MaxLength) {
	$Answer = '';
	$Length = rand(0, $MaxLength-5);
	for ($i=0; $i<$Length; $i++) {
		$Answer .= chr(rand(32, 127));
	}
	return $Answer;
}

function RandomEmailString($MaxLength) {
	$Answer = '';
	$Length = rand(0, $MaxLength);
	for ($i=0; $i<$Length; $i++) {
		$Answer .= chr(rand(32, 127));
	}
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
				$PostData[$Field['name']]=RandomEmailString($Field['maxlength']);
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