<?php
function GetEncounterFromPID($PID) {
	$SQL = "SELECT encounter_nr FROM care_encounter WHERE pid='" . $PID . "' AND is_discharged=0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (DB_error_no($Result) == 0) {
		return $MyRow['encounter_nr'];
	} else {
		return -1;
	}
}

function GetPIDFRomEncounter($Encounter) {
	$SQL = "SELECT pid FROM care_encounter WHERE encounter_nr='" . $Encounter . "' AND is_discharged=0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (DB_error_no($Result) == 0) {
		return $MyRow['pid'];
	} else {
		return -1;
	}
}

function GetPriceListFromPID($PID) {
	$PriceListSQL = "SELECT salestype FROM debtorsmaster WHERE debtorno='" . $PID . "'";
	$PriceListResult = DB_query($PriceListSQL);
	$PriceListRow = DB_fetch_array($PriceListResult);
	if (DB_error_no($Result) == 0) {
		return $PriceListRow['salestype'];
	} else {
		return -1;
	}
}

?>