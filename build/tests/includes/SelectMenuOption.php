<?php

function ChooseMenuOption($ch, $RootPath, $ServerPath, $TestSessionID, $IndexPage, $MenuOption) {
	$i=0;
	do {
		$i++;
	} while ($i<sizeOf($IndexPage[1]) and $IndexPage[1][$i]['value'] != $MenuOption);
	if ($i>=sizeOf($IndexPage[1])) {
		error_log('Error finding option '.$MenuOption.'. Link not found.'."\n", 3, '/home/tim/weberp'.date('Ymd').'.log');
		return false;
	}

	$SelectedPage = new URLDetails($TestSessionID);
	$SelectedPage->SetURL($ServerPath.$IndexPage[1][$i]['href']);
	$SelectedPage->SetPostArray(array());

	$Page=$SelectedPage->FetchPage($RootPath, $ServerPath, $ch);

	return $Page;

}

?>