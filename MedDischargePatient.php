<?php
include ('includes/session.php');
$Title = _('Discharge Patient');

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

$WardSQL = "SELECT nr, location_nr FROM care_encounter_location WHERE type_nr=2 AND encounter_nr='" . $Encounter . "' AND date_to='0000-00-00'";
$WardResult = DB_query($WardSQL);
$WardRow = DB_fetch_array($WardResult);
$WardNumber = $WardRow['location_nr'];
$WardEncounterNumber = $WardRow['nr'];

$RoomSQL = "SELECT nr, location_nr FROM care_encounter_location WHERE type_nr=4 AND encounter_nr='" . $Encounter . "' AND date_to='0000-00-00'";
$RoomResult = DB_query($RoomSQL);
$RoomRow = DB_fetch_array($RoomResult);
$RoomNumber = $RoomRow['location_nr'];
$RoomEncounterNumber = $RoomRow['nr'];

$BedSQL = "SELECT nr, location_nr FROM care_encounter_location WHERE type_nr=5 AND encounter_nr='" . $Encounter . "' AND date_to='0000-00-00'";
$BedResult = DB_query($BedSQL);
$BedRow = DB_fetch_array($BedResult);
$BedNumber = $BedRow['location_nr'];
$BedEncounterNumber = $BedRow['nr'];

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/discharge.png" title="', _('Discharge Patient'), '" alt="" />', _('Discharge Patient'), ' ', $PatientName, ' (', _('File Number'), ' - ', $FileNumber, ')
	</p>';

if (isset($_POST['DischargePatient'])) {
	$UpdateEncounterSQL = "UPDATE care_encounter SET encounter_status='discharged',
													current_ward_nr='0',
													current_room_nr='0',
													in_ward=0,
													current_dept_nr='0',
													in_dept=0,
													is_discharged=1,
													discharge_date='" . FormatDateForSQL($_POST['DischargeDate']) . "',
													discharge_time='" . $_POST['DischargeTime'] . "',
													discharge_dr='" . $_POST['Clinician'] . "',
													post_encounter_notes='" . $_POST['DischargeNotes'] . "',
													status='discharged',
													modify_id='" . $_SESSION['UserID'] . "'
												WHERE encounter_nr='" . $Encounter . "'";
	$UpdateEncounterResult = DB_query($UpdateEncounterSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was an error updating the encounter record'), 'error');
		include ('includes/footer.php');
		exit;
	}

	$UpdateWardSQL = "UPDATE care_encounter_location
							SET	discharge_type_nr='" . $_POST['DischargeType'] . "',
								date_to='" . FormatDateForSQL($_POST['DischargeDate']) . "',
								time_to='" . $_POST['DischargeTime'] . "',
								status='discharged',
								modify_id='" . $_SESSION['UserID'] . "'
							WHERE nr='" . $WardEncounterNumber . "'";
	$UpdateWardResult = DB_query($UpdateWardSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was an error updating the ward record'), 'error');
		include ('includes/footer.php');
		exit;
	}

	$UpdateRoomSQL = "UPDATE care_encounter_location
							SET	discharge_type_nr='" . $_POST['DischargeType'] . "',
								date_to='" . FormatDateForSQL($_POST['DischargeDate']) . "',
								time_to='" . $_POST['DischargeTime'] . "',
								status='discharged',
								modify_id='" . $_SESSION['UserID'] . "'
							WHERE nr='" . $RoomEncounterNumber . "'";
	$UpdateRoomResult = DB_query($UpdateRoomSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was an error updating the room record'), 'error');
		include ('includes/footer.php');
		exit;
	}

	$UpdateBedSQL = "UPDATE care_encounter_location
							SET	discharge_type_nr='" . $_POST['DischargeType'] . "',
								date_to='" . FormatDateForSQL($_POST['DischargeDate']) . "',
								time_to='" . $_POST['DischargeTime'] . "',
								status='discharged',
								modify_id='" . $_SESSION['UserID'] . "'
							WHERE nr='" . $BedEncounterNumber . "'";
	$UpdateBedResult = DB_query($UpdateBedSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was an error updating the room record'), 'error');
		include ('includes/footer.php');
		exit;
	}

	prnMsg(_('The patieent has been successfully discharged'), 'success');
	include ('includes/footer.php');
	exit;
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Encounter=', $Encounter, '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Patient Discharge Details'), '</legend>';

echo '<field>
		<label for="FileNumber">', _('File Number'), '</label>
		<div class="fieldtext">', $FileNumber, '</div>
	</field>';

echo '<field>
		<label for="Name">', _('Patient Name'), '</label>
		<div class="fieldtext">', $PatientName, '</div>
	</field>';

echo '<field>
		<label for="DOB">', _('Date of Birth'), '</label>
		<div class="fieldtext">', ConvertSQLDate($DateOfBirth), '</div>
	</field>';

$SQL = "SELECT ward_id, name, roomprefix FROM care_ward WHERE nr='" . $WardNumber . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$RoomPrefix = $MyRow['roomprefix'];
echo '<field>
		<label for="Ward">', _('Ward'), '</label>
		<div class="fieldtext">', $MyRow['ward_id'], ' - ', $MyRow['name'], '</div>
	</field>';

echo '<field>
		<label for="Room">', _('Room'), '</label>
		<div class="fieldtext">', $RoomPrefix, ' - ', $RoomNumber, '</div>
	</field>';

echo '<field>
		<label for="DischargeDate">', _('Discharge Date'), '</label>
		<input type="text" class="date" name="DischargeDate" size="10" value="', date($_SESSION['DefaultDateFormat']), '" />
	</field>';

echo '<field>
		<label for="DischargeTime">', _('Discharge Time'), '</label>
		<input type="text" name="DischargeTime" size="10" value="', date('H:m'), '" />
	</field>';

$SQL = "SELECT nr, name FROM care_type_discharge";
$Result = DB_query($SQL);
echo '<field>
		<label for="DischargeType">', _('Discharge Type'), '</label>
		<select name="DischargeType">';
while ($DischargeRow = DB_fetch_array($Result)) {
	echo '<option value="', $DischargeRow['nr'], '">', $DischargeRow['name'], '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="DischargeNotes">', _('Discharge Notes'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="DischargeNotes"></textarea>
	</field>';

$SQL = "SELECT userid, realname FROM www_users INNER JOIN securityroles ON www_users.fullaccess=securityroles.secroleid WHERE securityroles.clinician='1'";
$Result = DB_query($SQL);
if (!isset($_POST['Clinician'])) {
	$_POST['Clinician'] = '';
}
echo '<field>
		<label for="Clinician">', _('Clinician'), '</label>
		<select name="Clinician">
			<option value="" selected="selected"></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['Clinician'] == $MyRow['userid']) {
		echo '<option value="', $MyRow['userid'], '" selected="selected">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
	} else {
		echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
	}
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="DischargePatient" value="', _('Discharge Patient'), '" />
	</div>';

echo '</form>';

include ('includes/footer.php');
?>