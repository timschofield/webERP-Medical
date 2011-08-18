<?php

function FindModule($ch, $RootPath, $ServerPath, $TestSessionID, $IndexPage, $Module) {
	$i=0;
	do {
		$i++;
	} while ($i<sizeOf($IndexPage[1]) and $IndexPage[1][$i]['value'] != $Module);
	if ($i>=sizeOf($IndexPage[1])) {
		error_log('Error finding module '.$Module.'. Link not found.'."\n", 3, '/home/tim/weberp'.date('Ymd').'.log');
		return false;
	}
	$SelectedModuleScreen = new URLDetails($TestSessionID);
	$SelectedModuleScreen->SetURL($ServerPath.$IndexPage[1][$i]['href']);

	$ModulePage=$SelectedModuleScreen->FetchPage($RootPath, $ServerPath, $ch);

	return $ModulePage;
}

?>