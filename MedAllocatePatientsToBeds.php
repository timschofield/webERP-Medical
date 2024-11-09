<?php
include ('includes/session.php');
$Title = _('Allocate Patients to Beds');

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($_GET['Allocate'])) {
	$_POST['Allocate'] = $_GET['Allocate'];
}

if (isset($_POST['SelectedWard'])) {
	$SelectedWard = $_POST['SelectedWard'];
} elseif (isset($_GET['SelectedWard'])) {
	$SelectedWard = $_GET['SelectedWard'];
} else {
	$Title = _('This script can only be called with a ward ID as reference');
	include ('includes/header.php');
	prnMsg(_('This script can only be called with a ward ID as reference'), 'info');
	include ('includes/footer.php');
	exit;
}

if (isset($_GET['PID'])) {
	$Encounter = GetEncounterFromPID($_GET['PID']);
	$InsertWardSQL = "INSERT INTO care_encounter_location (encounter_nr,
															type_nr,
															location_nr,
															group_nr,
															date_from,
															date_to,
															time_from,
															discharge_type_nr,
															history,
															status,
															create_id,
															create_time,
															modify_id,
															modify_time
														) VALUES (
															'" . $Encounter . "',
															2,
															'" . $SelectedWard . "',
															3,
															CURRENT_DATE,
															'0000-00-00',
															CURRENT_TIME,
															0,
															'CREATE: " . date('Y-m-d H:i:s') . " " . $_SESSION['UserID'] . "',
															'admitted',
															'" . $_SESSION['UserID'] . "',
															NOW(),
															'" . $_SESSION['UserID'] . "',
															NOW()
														)";
	$InsertRoomSQL = "INSERT INTO care_encounter_location (encounter_nr,
															type_nr,
															location_nr,
															group_nr,
															date_from,
															date_to,
															time_from,
															discharge_type_nr,
															history,
															status,
															create_id,
															create_time,
															modify_id,
															modify_time
														) VALUES (
															'" . $Encounter . "',
															4,
															'" . $_GET['SelectedRoom'] . "',
															3,
															CURRENT_DATE,
															'0000-00-00',
															CURRENT_TIME,
															0,
															'CREATE: " . date('Y-m-d H:i:s') . " " . $_SESSION['UserID'] . "',
															'admitted',
															'" . $_SESSION['UserID'] . "',
															NOW(),
															'" . $_SESSION['UserID'] . "',
															NOW()
														)";
	$InsertBedSQL = "INSERT INTO care_encounter_location (encounter_nr,
															type_nr,
															location_nr,
															group_nr,
															date_from,
															date_to,
															time_from,
															discharge_type_nr,
															history,
															status,
															create_id,
															create_time,
															modify_id,
															modify_time
														) VALUES (
															'" . $Encounter . "',
															5,
															'" . $_GET['SelectedBed'] . "',
															3,
															CURRENT_DATE,
															'0000-00-00',
															CURRENT_TIME,
															0,
															'CREATE: " . date('Y-m-d H:i:s') . " " . $_SESSION['UserID'] . "',
															'admitted',
															'" . $_SESSION['UserID'] . "',
															NOW(),
															'" . $_SESSION['UserID'] . "',
															NOW()
														)";
	$WardResult = DB_query($InsertWardSQL);
	$RoomResult = DB_query($InsertRoomSQL);
	$BedResult = DB_query($InsertBedSQL);
}

if (isset($_POST['Allocate']) and $_POST['Allocate'] == 'Yes') {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', _('Patients Awaiting Bed'), '
		</p>';
	$AllocatedToWard = array();
	$SQL = "SELECT pid
			FROM care_encounter
			WHERE current_ward_nr='" . $SelectedWard . "'
				AND in_ward=1
				AND is_discharged=0";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$Encounter = GetEncounterFromPID($MyRow['pid']);
		$AllocatedToWard[] = $MyRow['pid'];
	}
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('There are no patients awaiting a bed in this ward'), 'info');
	} else {
		echo '<table>
				<tr>
					<th colspan="6">', _('Allocate to Ward'), ' - ', $SelectedWard, '  ', _('Room Number'), ' - ', $_GET['SelectedRoom'], '  ', _('Bed Number'), ' - ', $_GET['SelectedBed'], '</th>
				</tr>
				<tr>
					<th>', _('PID'), '</th>
					<th>', _('File Number'), '</th>
					<th>', _('Family Name'), '</th>
					<th>', _('First Name'), '</th>
					<th>', _('Birth Date'), '</th>
					<th></th>
				</tr>';

		foreach ($AllocatedToWard as $Patient) {
			$SQL = "SELECT hospital_file_nr,
							name_last,
							name_first,
							date_birth
						FROM care_person
						WHERE pid='" . $Patient . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			echo '<tr class="striped_row">
					<td>', $Patient, '</td>
					<td>', $MyRow['hospital_file_nr'], '</td>
					<td>', $MyRow['name_last'], '</td>
					<td>', $MyRow['name_first'], '</td>
					<td>', ConvertSQLDate($MyRow['date_birth']), '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?PID=', $Patient, '&SelectedWard=', $SelectedWard, '&SelectedRoom=', $_GET['SelectedRoom'], '&SelectedBed=', $_GET['SelectedBed'], '">', _('Allocate This Patient To This Bed'), '</a></td>
				</tr>';
		}

		echo '</table>';
	}
	include ('includes/footer.php');
	exit;
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', $Title, '
	</p>';

$SQL = "SELECT care_ward.roomprefix,
				room_nr,
				nr_of_beds
			FROM care_room
			INNER JOIN care_ward
				ON care_room.ward_nr=care_ward.nr
			WHERE ward_nr='" . $SelectedWard . "'
			ORDER BY room_nr";
$Result = DB_query($SQL);

$WardSQL = "SELECT encounter_nr FROM care_encounter_location WHERE type_nr=2 AND location_nr='" . $SelectedWard . "' AND date_to='0000-00-00'";
$WardResult = DB_query($WardSQL);
while ($MyWardRow = DB_fetch_array($WardResult)) {
	$RoomSQL = "SELECT location_nr FROM care_encounter_location WHERE type_nr=4 AND encounter_nr='" . $MyWardRow['encounter_nr'] . "' AND date_to='0000-00-00'";
	$RoomResult = DB_query($RoomSQL);
	while ($MyRoomRow = DB_fetch_array($RoomResult)) {
		$BedSQL = "SELECT location_nr FROM care_encounter_location WHERE type_nr=5 AND encounter_nr='" . $MyWardRow['encounter_nr'] . "' AND date_to='0000-00-00'";
		$BedResult = DB_query($BedSQL);
		$MyBedRow = DB_fetch_array($BedResult);
		$PatientsInWard[$SelectedWard][$MyRoomRow['location_nr']][$MyBedRow['location_nr']] = $MyWardRow['encounter_nr'];
	}
}

if (DB_num_rows($Result) > 0) {
	echo '<table>
			<tr>
				<th>', _('Room Number'), '</th>
				<th>', _('Bed Number'), '</th>
				<th>', _('Family Name'), '</th>
				<th>', _('First Name'), '</th>
				<th>', _('Birth Date'), '</th>
				<th>', _('Patient No.'), '</th>
				<th>', _('Insurance Co.'), '</th>
				<th colspan="6">', _('Options'), '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {

		for ($BedNumber = 1;$BedNumber <= $MyRow['nr_of_beds'];$BedNumber++) {
			echo '<tr class="striped_row">';
			if ($BedNumber == 1) {
				echo '<td>', $MyRow['roomprefix'], $MyRow['room_nr'], '</td>';
			} else {
				echo '<td>&nbsp;</td>';
			}
			echo '<td>', _('Bed'), ' ', $BedNumber, '</td>';
			if (!isset($PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber])) {
				echo '<td colspan="5">', _('Bed is currently vacant'), '</td>';
				echo '<td colspan="7"><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Allocate=Yes&SelectedWard=', $SelectedWard, '&SelectedRoom=', $MyRow['room_nr'], '&SelectedBed=', $BedNumber, '">', _('Allocate Patient To This Bed'), '</a></td>';
			} else {
				$SQL = "SELECT name_last,
								name_first,
								date_birth,
								hospital_file_nr
						FROM care_person
						WHERE pid='" . GetPIDFRomEncounter($PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber]) . "'";
				$PersonResult = DB_query($SQL);
				$MyPersonRow = DB_fetch_array($PersonResult);
				$SQL = "SELECT debtorsmaster.name
							FROM debtorsmaster
							INNER JOIN care_encounter
								ON debtorsmaster.debtorno=care_encounter.insurance_firm_id
							WHERE care_encounter.encounter_nr='" . $PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber] . "'";
				$InsuranceResult = DB_query($SQL);
				$MyInsuranceRow = DB_fetch_array($InsuranceResult);
				echo '<td>', $MyPersonRow['name_last'], '</td>';
				echo '<td>', $MyPersonRow['name_first'], '</td>';
				echo '<td>', ConvertSQLDate($MyPersonRow['date_birth']), '</td>';
				echo '<td>', $MyPersonRow['hospital_file_nr'], '</td>';
				echo '<td>', $MyInsuranceRow['name'], '</td>';
				echo '<td>
						<a href="', $RootPath . '/MedYellowPaper.php?Encounter=', $PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber], '&Ward=', $SelectedWard, '" target="_blank">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/yellow.png" title="', _('Patient Notes'), '" /></a>
					</td>';
				echo '<td>
						<a href="', $RootPath . '/MedDailyWardNotes.php?Encounter=', $PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber], '&Ward=', $SelectedWard, '" target="_blank">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/pinned_note.png" title="', _('Daily Ward Notes'), '" /></a>
					</td>';
				echo '<td>
						<a href="', $RootPath . '/MedDailyWardNotes.php?Encounter=', $PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber], '" target="_blank">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/folder_add.png" title="', _('Patients Info Chart'), '" /></a>
					</td>';
				echo '<td>
						<a href="', $RootPath . '/MedInpatientAdmission.php?Edit=Yes&SelectedPatient=', GetPIDFRomEncounter($PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber]), '" target="_blank">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/index_card.png" title="', _('Admission Data'), '" /></a>
					</td>';
				echo '<td>
						<a href="', $RootPath . '/MedTransferBeds.php?Encounter=', $PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber], '" target="_blank">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/transfer.png" title="', _('Transfer Patient to another bed'), '" /></a>
					</td>';
				echo '<td>
						<a href="', $RootPath . '/MedDischargePatient.php?Encounter=', $PatientsInWard[$SelectedWard][$MyRow['room_nr']][$BedNumber], '" target="_blank">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/discharge.png" title="', _('Discharge Patient'), '" /></a>
					</td>';
			}
			echo '</tr>';
		}
	}
	echo '</table>';
}

include ('includes/footer.php');

?>