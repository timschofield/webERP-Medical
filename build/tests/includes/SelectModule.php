<?php

function FindModule($ch, $RootPath, $ServerPath, $TestSessionID, $IndexPage, $Module) {
	$SelectedModuleScreen = new URLDetails($TestSessionID);
	$SelectedModuleScreen->SetURL('index.php?Application='.$Module);

	$ModulePage=$SelectedModuleScreen->FetchPage($RootPath, $ServerPath, $ch);

	return $ModulePage;
}

?>