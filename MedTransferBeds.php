<?php
include ('includes/session.php');
$Title = _('Transfer patient to other bed');

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($_GET['Encounter'])) {
	$Encounter = $_GET['Encounter'];
} else if (isset($_POST['Encounter'])) {
	$Encounter = $_POST['Encounter'];
}

$PID = GetPIDFromEncounter($Encounter);

$SQL = "SELECT hospital_file_nr,
				name_last,
				name_first,
				date_birth
			FROM care_person
			WHERE pid='" . $PID . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

$FileNumber = $MyRow['hospital_file_nr'];
$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
$DateOfBirth = $MyRow['date_birth'];

if (isset($_POST['NewWardNumber'])) {
	$WardSQL = "SELECT encounter_nr FROM care_encounter_location WHERE type_nr=2 AND location_nr='" . $_POST['NewWardNumber'] . "' AND date_to='0000-00-00'";
	$WardResult = DB_query($WardSQL);
	while ($MyWardRow = DB_fetch_array($WardResult)) {
		$RoomSQL = "SELECT location_nr FROM care_encounter_location WHERE type_nr=4 AND encounter_nr='" . $MyWardRow['encounter_nr'] . "' AND date_to='0000-00-00'";
		$RoomResult = DB_query($RoomSQL);
		while ($MyRoomRow = DB_fetch_array($RoomResult)) {
			$BedSQL = "SELECT location_nr FROM care_encounter_location WHERE type_nr=5 AND encounter_nr='" . $MyWardRow['encounter_nr'] . "' AND date_to='0000-00-00'";
			$BedResult = DB_query($BedSQL);
			$MyBedRow = DB_fetch_array($BedResult);
			$PatientsInWard[$_POST['NewWardNumber']][$MyRoomRow['location_nr']][$MyBedRow['location_nr']] = $MyWardRow['encounter_nr'];
		}
	}
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/bed.png" title="', _('Move Patient'), '" alt="" />', _('Move Patient'), ' ', $PatientName, ' (', _('File Number'), ' - ', $FileNumber, ')
	</p>';

if (isset($_POST['SelectBed'])) {
	/* If the ward number has changed, then create a new ward record in care_encounter_location
	 * end the previous one, and update the current ward in care_encounter
	*/
	if ($_POST['CurrentWardNumber'] != $_POST['NewWardNumber']) {
		$UpdateWardSQL = "UPDATE care_encounter_location SET date_to=CURRENT_DATE,
															 time_to=CURRENT_TIME,
															 discharge_type_nr=4
															WHERE encounter_nr='" . $Encounter . "'
																AND date_to='0000-00-00'
																AND type_nr=2";
		$Result = DB_query($UpdateWardSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('There was a problem updating the old ward record'), 'error');
			include ('includes/footer.php');
			exit;
		}

		$InsertWardSQL = "INSERT INTO care_encounter_location (encounter_nr,
																type_nr,
																location_nr,
																group_nr,
																date_from,
																date_to,
																time_from,
																discharge_type_nr,
																history,
																create_id,
																create_time
															) VALUES (
																'" . $Encounter . "',
																2,
																'" . $_POST['NewWardNumber'] . "',
																3,
																CURRENT_DATE,
																'0000-00-00',
																CURRENT_TIME,
																0,
																'CREATE: " . date('Y-m-d H:i:s') . " " . $_SESSION['UserID'] . "',
																'" . $_SESSION['UserID'] . "',
																NOW()
															)";
		$Result = DB_query($InsertWardSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('There was a problem creating the new ward record'), 'error');
			include ('includes/footer.php');
			exit;
		}

		$UpdateEncounterSQL = "UPDATE care_encounter SET current_ward_nr='" . $_POST['NewWardNumber'] . "',
														current_room_nr='" . $_POST['NewRoomNumber'] . "'
													where encounter_nr='" . $Encounter . "'";
		$Result = DB_query($UpdateEncounterSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('There was a problem updating the encounter record'), 'error');
			include ('includes/footer.php');
			exit;
		}
	}

	/* If the ward has changed, or the ward is the same but the room has changed then create a new
	 * care_encounter_location record and terminate the old one.
	*/
	if (($_POST['CurrentWardNumber'] != $_POST['NewWardNumber']) or (($_POST['CurrentWardNumber'] == $_POST['NewWardNumber']) and ($_POST['CurrentRoomNumber'] != $_POST['NewRoomNumber']))) {
		$UpdateRoomSQL = "UPDATE care_encounter_location SET date_to=CURRENT_DATE,
															 time_to=CURRENT_TIME,
															 discharge_type_nr=5
															WHERE encounter_nr='" . $Encounter . "'
																AND date_to='0000-00-00'
																AND type_nr=4";
		$Result = DB_query($UpdateRoomSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('There was a problem updating the room record'), 'error');
			include ('includes/footer.php');
			exit;
		}

		$InsertRoomSQL = "INSERT INTO care_encounter_location (encounter_nr,
															type_nr,
															location_nr,
															group_nr,
															date_from,
															date_to,
															time_from,
															discharge_type_nr,
															history,
															create_id,
															create_time
														) VALUES (
															'" . $Encounter . "',
															4,
															'" . $_POST['NewRoomNumber'] . "',
															3,
															CURRENT_DATE,
															'0000-00-00',
															CURRENT_TIME,
															0,
															'CREATE: " . date('Y-m-d H:i:s') . " " . $_SESSION['UserID'] . "',
															'" . $_SESSION['UserID'] . "',
															NOW()
														)";
		$Result = DB_query($InsertRoomSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('There was a problem creating the new room record'), 'error');
			include ('includes/footer.php');
			exit;
		}
	}
	/* If we have got this far then the bed must have changed so terminate old record and create new one
	*/
	$UpdateBedSQL = "UPDATE care_encounter_location SET date_to=CURRENT_DATE,
														time_to=CURRENT_TIME,
														discharge_type_nr=6
													WHERE encounter_nr='" . $Encounter . "'
														AND date_to='0000-00-00'
														AND type_nr=5";
	$Result = DB_query($UpdateBedSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was a problem updating the bed record'), 'error');
		include ('includes/footer.php');
		exit;
	}

	$InsertBedSQL = "INSERT INTO care_encounter_location (encounter_nr,
															type_nr,
															location_nr,
															group_nr,
															date_from,
															date_to,
															time_from,
															discharge_type_nr,
															history,
															create_id,
															create_time
														) VALUES (
															'" . $Encounter . "',
															5,
															'" . $_POST['NewBedNumber'] . "',
															3,
															CURRENT_DATE,
															'0000-00-00',
															CURRENT_TIME,
															0,
															'CREATE: " . date('Y-m-d H:i:s') . " " . $_SESSION['UserID'] . "',
															'" . $_SESSION['UserID'] . "',
															NOW()
														)";
	$Result = DB_query($InsertBedSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was a problem creating the new bed record'), 'error');
		include ('includes/footer.php');
		exit;
	}

	prnMsg(_('The patient has been successfully transferred'), 'success');
	echo '<div class="centre">
			<a href="KCMCWardOverview.php">', _('Return to ward overview'), '</a>
		</div>';

	include ('includes/footer.php');
	exit;
}

$SQL = "SELECT current_ward_nr
		FROM care_encounter
		WHERE pid='" . $PID . "'
			AND in_ward=1
			AND is_discharged=0";
$Result = DB_query($SQL);
$WardRow = DB_fetch_array($Result);
$CurrentWardNumber = $WardRow['current_ward_nr'];
$SQL = "SELECT name, roomprefix FROM care_ward WHERE nr='" . $CurrentWardNumber . "'";
$Result = DB_query($SQL);
$WardNameRow = DB_fetch_array($Result);
$CurrentWardName = $WardNameRow['name'];

$RoomSQL = "SELECT location_nr FROM care_encounter_location WHERE type_nr=4 AND encounter_nr='" . $Encounter . "' AND date_to='0000-00-00'";
$RoomResult = DB_query($RoomSQL);
$MyRoomRow = DB_fetch_array($RoomResult);
$CurrentRoomNumber = $MyRoomRow['location_nr'];
$CurrentRoomName = $WardNameRow['roomprefix'] . ' - ' . $CurrentRoomNumber;

$BedSQL = "SELECT location_nr FROM care_encounter_location WHERE type_nr=5 AND encounter_nr='" . $Encounter . "' AND date_to='0000-00-00'";
$BedResult = DB_query($BedSQL);
$MyBedRow = DB_fetch_array($BedResult);
$CurrentBedNumber = $MyBedRow['location_nr'];

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Encounter=', $Encounter, '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<input type="hidden" name="Encounter" value="', $Encounter, '" />';
echo '<input type="hidden" name="CurrentWardNumber" value="', $CurrentWardNumber, '" />';
echo '<input type="hidden" name="CurrentRoomNumber" value="', $CurrentRoomNumber, '" />';
echo '<input type="hidden" name="CurrentBedNumber" value="', $CurrentBedNumber, '" />';

echo '<table>
		<tr>
			<th colspan="2">', _('Move Patient'), ' - ', $PatientName, ' (PID-', $PID, ' File Number-', $FileNumber, ')</th>
		</tr>
		<tr>
			<th></th>
			<th>', _('Current Bed'), '</th>
		</tr>';

echo '<tr class="striped_row">
		<td>', _('Ward'), '</td>
		<td>', $CurrentWardNumber, ' - ', $CurrentWardName, '</td>
	</tr>';

echo '<tr class="striped_row">
		<td>', _('Room'), '</td>
		<td>', $CurrentRoomName, '</td>
	</tr>';

echo '<tr class="striped_row">
		<td>', _('Bed'), '</td>
		<td>', $CurrentBedNumber, '</td>
	</tr>';

echo '</table>';

$WardSQL = "SELECT nr, ward_id, name FROM care_ward WHERE is_temp_closed=0";
$WardResult = DB_query($WardSQL);

echo '<fieldset>
		<legend>', ('New Bed Details'), '</legend>';

if (!isset($_POST['NewWardNumber'])) {
	echo '<field>
			<label for="NewWardNumber">', _('Transfer to ward'), '</label>
			<select name="NewWardNumber">';
	while ($WardRow = DB_fetch_array($WardResult)) {
		echo '<option value="', $WardRow['nr'], '">', $WardRow['ward_id'], ' - ', $WardRow['name'], '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SelectWard" value="', _('Select Ward'), '" />
		</div>';

} else if (isset($_POST['SelectWard'])) {
	$SQL = "SELECT name FROM care_ward WHERE nr='" . $_POST['NewWardNumber'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	echo '<input type="hidden" name="NewWardNumber" value="', $_POST['NewWardNumber'], '" />';
	echo '<field>
			<label for="NewWardNumber">', _('New Ward Number'), '</label>
			<div class="fieldtext">', $_POST['NewWardNumber'], ' - ', $MyRow['name'], '</div>
		</field>';

	$SQL = "SELECT care_room.nr,
					care_room.room_nr,
					care_ward.roomprefix
				FROM care_room
				INNER JOIN care_ward
					ON care_room.ward_nr=care_ward.nr
				WHERE care_room.ward_nr='" . $_POST['NewWardNumber'] . "'
				ORDER BY room_nr";
	$RoomResult = DB_query($SQL);
	echo '<field>
			<label for="NewRoomNumber">', _('Transfer to room'), '</label>
			<select name="NewRoomNumber">';
	while ($RoomRow = DB_fetch_array($RoomResult)) {
		echo '<option value="', $RoomRow['nr'], '">', $RoomRow['roomprefix'], ' - ', $RoomRow['room_nr'], '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SelectRoom" value="', _('Select Room'), '" />
		</div>';
} else if (isset($_POST['SelectRoom'])) {
	$SQL = "SELECT name, roomprefix FROM care_ward WHERE nr='" . $_POST['NewWardNumber'] . "'";
	$Result = DB_query($SQL);
	$MyWardRow = DB_fetch_array($Result);

	$SQL = "SELECT room_nr, nr_of_beds FROM care_room WHERE ward_nr='" . $_POST['NewWardNumber'] . "' AND nr='" . $_POST['NewRoomNumber'] . "'";
	$BedResult = DB_query($SQL);
	$BedRow = DB_fetch_array($BedResult);
	echo '<input type="hidden" name="NewWardNumber" value="', $_POST['NewWardNumber'], '" />';
	echo '<input type="hidden" name="NewRoomNumber" value="', $_POST['NewRoomNumber'], '" />';
	echo '<field>
			<label for="NewWardNumber">', _('New Ward Number'), '</label>
			<div class="fieldtext">', $_POST['NewWardNumber'], ' - ', $MyWardRow['name'], '</div>
		</field>';

	echo '<field>
			<label for="NewRoomNumber">', _('New Room Number'), '</label>
			<div class="fieldtext">', $MyWardRow['roomprefix'], ' - ', $BedRow['room_nr'], '</div>
		</field>';

	echo '<field>
			<label for="NewBedNumber">', _('New Bed Number'), '</label>
			<select name="NewBedNumber">';
	for ($BedNumber = 1;$BedNumber <= $BedRow['nr_of_beds'];$BedNumber++) {
		if (!isset($PatientsInWard[$_POST['NewWardNumber']][$_POST['NewRoomNumber']][$BedNumber])) {
			echo '<option value="', $BedNumber, '">', _('Bed Number'), ' - ', $BedNumber, '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SelectBed" value="', _('Select Bed'), '" />
		</div>';
}
echo '</form>';

include ('includes/footer.php');

?>