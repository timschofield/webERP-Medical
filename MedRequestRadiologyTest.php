<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Radiology Test Request');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other adjustment sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_GET['New'])) {
	unset($_SESSION['TestRequest' . $Identifier]);
}

if (isset($_POST['SelectedPatient'])) {
	$SelectedPatient = $_POST['SelectedPatient'];
} else if (isset($_GET['SelectedPatient'])) {
	$SelectedPatient = $_GET['SelectedPatient'];
}

if (isset($SelectedPatient)) {
	$SQL = "SELECT pid,
					hospital_file_nr,
					name_first,
					name_last,
					phone_1_nr
				FROM care_person
				WHERE pid='" . $SelectedPatient . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$PID = $SelectedPatient;
	$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
	$PhoneNo = $MyRow['phone_1_nr'];
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/radiology.png" title="', _('Radiology Test Request'), '" /> ', _('Radiology Test Request'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $SelectedPatient, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/radiology.png" title="', _('Radiology Test Request'), '" /> ', _('Radiology Test Request'), '
		</p>';
}

if (isset($_POST['UpdateRequest'])) {
}

if (isset($_POST['SendRequest'])) {
	$Encounter = GetEncounterFromPID($SelectedPatient);

	$SQL = "SELECT current_ward_nr,
					current_dept_nr
				FROM care_encounter
				WHERE encounter_nr='" . $Encounter . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$WardNumber = $MyRow['current_ward_nr'];
	$DeptNumber = $MyRow['current_dept_nr'];

	$TestBatchNumber = GetNextTransNo(100);
	$TotalRequestsInBatch = count($_POST['TestNames']);

	for ($Count = 0;$Count < $TotalRequestsInBatch;$Count++) {
		$TestRequested = $_POST['TestNames'][$Count];
		$InsertSQL = "INSERT INTO care_test_request_radio (batch_nr,
															encounter_nr,
															dept_nr,
															test_nr,
															test_type,
															if_patmobile,
															if_allergy,
															if_hyperten,
															if_pregnant,
															clinical_info,
															test_request,
															send_date,
															send_doctor,
															r_cm_2,
															mtr,
															results,
															results_doctor,
															status,
															history,
															modify_id,
															modify_time,
															create_id,
															create_time,
															process_id
														) VALUES (
															NULL,
															'" . $Encounter . "',
															'" . $DeptNumber . "',
															'" . $TestBatchNumber . "',
															'" . $TestRequested . "',
															'" . $_POST['Mobile'] . "',
															'" . $_POST['Allergies'] . "',
															'" . $_POST['Hyperthyreosis'] . "',
															'" . $_POST['Pregnancy'] . "',
															'" . $_POST['ClinicalInfo'] . "',
															'" . $_POST['RequiredTest'] . "',
															'" . FormatDateForSQL($_POST['RequestDate']) . "',
															'" . $_POST['Doctor'] . "',
															'',
															'',
															'',
															'',
															'pending',
															'" . 'Create: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'] . "',
															'" . $_SESSION['UserID'] . "',
															NOW(),
															'" . $_SESSION['UserID'] . "',
															NOW(),
															''
														)";

		$InsertResult = DB_query($InsertSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to insert the test request into the database'), 'error');
			include ('includes/footer.php');
			exit;
		} else {
			prnMsg(_('The batch of tests has been sent successfully'), 'success');
			unset($SelectedPatient);
			echo '<div class="centre">
					<a href="MedRadiologyLaboratory.php">', _('Return to Radiology Department'), '</a>
				</div>';
		}
	}

}

if (isset($_POST['Search'])) {
	$SQL = "SELECT pid,
					hospital_file_nr,
					name_first,
					name_last,
					phone_1_nr
				FROM care_person
				WHERE CONCAT(name_first, ' ', name_last) LIKE '%" . $_POST['Keywords'] . "%'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		echo '<table>
				<thead>
					<tr>
						<th>', _('File Number'), '</th>
						<th>', _('Name'), '</th>
						<th>', _('Phone Number'), '</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_Fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>', $MyRow['hospital_file_nr'], '</td>
					<td>', $MyRow['name_first'], ' ', $MyRow['name_last'], '</td>
					<td>', $MyRow['phone_1_nr'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedPatient=', $MyRow['pid'], '&identifier=', $Identifier, '">', _('Select'), '</a></td>
				</tr>';
		}
		echo '</tbody>
			</table>';
	}
} // end of if search
if (isset($SelectedPatient) and $SelectedPatient != '') {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="SelectedPatient" value="', $SelectedPatient, '" />';

	echo '<fieldset style="width:51%">
			<legend>', _('Request Radiology Test'), '</legend>';

	include ('includes/MedDrawPatientLabel.php');

	echo '<br /><fieldset style="width:51%">
			<legend>', _('Select Diagnostic Test'), '</legend>';

	$SQL = "SELECT type, name FROM care_radio_test_type";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<field>
				<input type="checkbox" name="TestNames[]" value="', $MyRow['type'], '" />
				<label for="TestNames">', $MyRow['name'], '</label>
			</field>';
	}

	echo '</fieldset><br />';

	echo '<fieldset style="width:51%">
			<legend>', _('Patient Information'), '</legend>';

	echo '<field>
			<label for="Mobile">', _('Is the patient mobile?'), '</label>
			<select name="Mobile">
				<option value=""></option>
				<option value="1">', _('Yes'), '</option>
				<option value="0">', _('No'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="Allergies">', _('Any known allergies?'), '</label>
			<select name="Allergies">
				<option value=""></option>
				<option value="1">', _('Yes'), '</option>
				<option value="0">', _('No'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="Hyperthyreosis">', _('Hyperthyreosis known?'), '</label>
			<select name="Hyperthyreosis">
				<option value=""></option>
				<option value="1">', _('Yes'), '</option>
				<option value="0">', _('No'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="Pregnancy">', _('Pregnancy possible?'), '</label>
			<select name="Pregnancy">
				<option value=""></option>
				<option value="1">', _('Yes'), '</option>
				<option value="0">', _('No'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="ClinicalInfo">', _('Clinical Information'), '</label>
			<textarea name="ClinicalInfo" cols="80" rows="6" wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="RequiredTest">', _('Requested diagnostic test'), '</label>
			<textarea name="RequiredTest" cols="80" rows="6" wrap="physical"></textarea>
		</field>';

	echo '</fieldset>';

	echo '<fieldset style="width:51%">';

	echo '<field>
			<label for="RequestDate">', _('Date test requested'), '</label>
			<input type="text" class="date" size="20" name="RequestDate" value="', date($_SESSION['DefaultDateFormat']), '" />
		</field>';

	echo '<field>
			<label for="Doctor">', _('Doctor/Department'), '</label>
			<input type="text" size="30" name="Doctor" value="" />
		</field>';

	echo '</fieldset>';

	echo '</fieldset>';

	if (isset($_GET['Edit'])) {
		echo '<div class="centre">
				<input type="submit" name="UpdateRequest" value="', _('Update Request'), '" />
			</div>';
		echo '<input type="hidden" name="SelectedBatch" value="', $_GET['SelectedBatch'], '" />';
	} else {
		echo '<div class="centre">
				<input type="submit" name="SendRequest" value="', _('Send Request'), '" />
			</div>';
	}

	echo '</form>';
} else {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" /> ', _('Search for Patients'), '
		</p>'; // Page title.
	echo '<fieldset>
			<legend>', _('Search Criteria'), '</legend>
			<field>
				<label for="Keywords">', _('Enter a partial Name'), ':</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="search" name="Keywords" value="', $_POST['Keywords'], '" size="20" maxlength="25" />';
	} else {
		echo '<input type="search" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label for="PatientCode">', _('Enter a partial patient file number'), ':</label>';
	if (isset($_POST['PatientCode'])) {
		echo '<input autofocus="autofocus" maxlength="18" name="CustCode" size="15" type="search" value="', $_POST['PatientCode'], '" />';
	} else {
		echo '<input autofocus="autofocus" maxlength="18" name="CustCode" size="15" type="search" />';
	} // isset($_POST['CustCode'])
	echo '</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label for="PatientPhone">', _('Enter a partial Phone Number'), ':</label>';
	if (isset($_POST['PatientPhone'])) {
		echo '<input maxlength="18" name="PatientPhone" size="15" type="tel" value="', $_POST['PatientPhone'], '" />';
	} else {
		echo '<input maxlength="18" name="PatientPhone" size="15" type="tel" />';
	} // isset($_POST['CustPhone'])
	echo '</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label for="PatientAdd">', _('Enter part of the Address'), ':</label>';
	if (isset($_POST['PatientAdd'])) {
		echo '<input maxlength="25" name="PatientAdd" size="20" type="search" value="', $_POST['PatientAdd'], '" />';
	} else {
		echo '<input maxlength="25" name="PatientAdd" size="20" type="search" />';
	} // isset($_POST['CustAdd'])
	echo '</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input name="Search" type="submit" value="', _('Search Now'), '" />
		</div>';

	echo '</form>';
}

include ('includes/footer.php');
?>