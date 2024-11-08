<?php
include ('includes/session.php');
$Title = _('Enter Pathology Test Findings');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

if (isset($_POST['Batch'])) {
	$SelectedBatch = $_POST['Batch'];
} else if (isset($_GET['Batch'])) {
	$SelectedBatch = $_GET['Batch'];
} else {
	prnMsg(_('You must first select a batch of tests to view'), 'warn');
	echo '<div class="centre">
			<a href="', $RootPath, '/KCMCPendingPathologyTests.php>', _('Select a batch to view'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($SelectedBatch)) {
	$SQL = "SELECT encounter_nr
			FROM care_test_request_patho
			WHERE batch_nr='" . $SelectedBatch . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$Encounter = $MyRow['encounter_nr'];
	$PID = GetPIDFromEncounter($Encounter);

	$SQL = "SELECT pid,
					hospital_file_nr,
					name_first,
					name_last,
					phone_1_nr
				FROM care_person
				WHERE pid='" . $PID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
	$PhoneNo = $MyRow['phone_1_nr'];
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Enter Pathology Test Findings'), '" /> ', _('Enter Pathology Test Findings'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $PID, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Enter Pathology Test Findings'), '" /> ', _('Enter Pathology Test Findings'), '
		</p>';
}

if (isset($_POST['Save'])) {
	$EncounterSQL = "SELECT current_ward_nr, current_dept_nr FROM care_encounter WHERE encounter_nr='" . $Encounter . "'";
	$EncounterResult = DB_query($EncounterSQL);
	$EncounterRow = DB_fetch_array($EncounterResult);

	$History = 'Create: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'];

	$InsertSQL = "INSERT INTO `care_test_findings_patho` (`batch_nr`,
														`encounter_nr`,
														`room_nr`,
														`dept_nr`,
														`material`,
														`macro`,
														`micro`,
														`findings`,
														`diagnosis`,
														`doctor_id`,
														`findings_date`,
														`findings_time`,
														`status`,
														`history`,
														`modify_id`,
														`modify_time`,
														`create_id`,
														`create_time`
													) VALUES (
														'" . $SelectedBatch . "',
														'" . $Encounter . "',
														'" . $EncounterRow['current_ward_nr'] . "',
														'" . $EncounterRow['current_dept_nr'] . "',
														'" . $_POST['Material'] . "',
														'" . $_POST['MacroFindings'] . "',
														'" . $_POST['MicroFindings'] . "',
														'" . $_POST['AdditionalNotes'] . "',
														'" . $_POST['Diagnosis'] . "',
														'" . $_POST['Doctor'] . "',
														'" . FormatDateForSQL($_POST['Date']) . "',
														'00:00:00',
														'initial',
														'" . $History . "',
														'" . $_SESSION['UserID'] . "',
														NOW(),
														'" . $_SESSION['UserID'] . "',
														NOW()
													)";
	$InsertResult = DB_query($InsertSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('Failed to insert the test findings into the database'), 'error');
		include ('includes/footer.php');
		exit;
	} else {
		prnMsg(_('The findings been saved successfully'), 'success');
		unset($SelectedPatient);
		echo '<div class="centre">
				<a href="KCMCPathologyLaboratory.php">', _('Return to Pathology Laboratory'), '</a>
			</div>';
		include ('includes/footer.php');
		exit;
	}

}

if (isset($SelectedBatch)) {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="Batch" value="', $SelectedBatch, '" />';

	echo '<fieldset>
			<legend>', _('Enter Pathology Test Findings'), '</legend>';

	echo '<field>
			<label for="Material">', _('Material'), '</label>
			<textarea name="Material" cols=82 rows=4 wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="MacroFindings">', _('Macro Findings'), '</label>
			<textarea name="MacroFindings" cols=82 rows=4 wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="MicroFindings">', _('Micro Findings'), '</label>
			<textarea name="MicroFindings" cols=82 rows=4 wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="AdditionalNotes">', _('Additional Note'), '</label>
			<textarea name="AdditionalNotes" cols=82 rows=4 wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="Diagnosis">', _('Diagnosis'), '</label>
			<textarea name="Diagnosis" cols=82 rows=4 wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="Date">', _('Date'), '</label>
			<input type="text" name="Date" size="10" class="date" value="', date($_SESSION['DefaultDateFormat']), '" />
		</field>';

	echo '<field>
			<label for="Doctor">', _('Reporting Doctor'), '</label>
			<input type="text" name="Doctor" value="" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Save" value="', _('Save Findings'), '" />
		</div>';

	echo '</form>';
}

include ('includes/footer.php');

?>