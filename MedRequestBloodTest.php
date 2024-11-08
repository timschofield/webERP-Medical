<?php
include ('includes/session.php');
$Title = _('Blood Request');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($_POST['SelectedPatient'])) {
	$SelectedPatient = $_POST['SelectedPatient'];
} else if (isset($_GET['SelectedPatient'])) {
	$SelectedPatient = $_GET['SelectedPatient'];
}

if (isset($_POST['SelectedBatch'])) {
	$SelectedBatch = $_POST['SelectedBatch'];
} else if (isset($_GET['SelectedBatch'])) {
	$SelectedBatch = $_GET['SelectedBatch'];
} else {
	$SelectedBatch = '';
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

	$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
	$PhoneNo = $MyRow['phone_1_nr'];
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Blood Request'), '" /> ', _('Blood Request'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $SelectedPatient, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Blood Request'), '" /> ', _('Blood Request'), '
		</p>';
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
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedPatient=', $MyRow['pid'], '">', _('Select'), '</a></td>
				</tr>';
		}
		echo '</tbody>
			</table>';
	}
} // end of if search
if (isset($_POST['Send'])) {

	$Encounter = GetEncounterFromPID($SelectedPatient);

	$SQL = "SELECT current_ward_nr,
					current_dept_nr
				FROM care_encounter
				WHERE encounter_nr='" . $Encounter . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$DeptNumber = $MyRow['current_dept_nr'];

	if (!isset($SelectedBatch) or $SelectedBatch == '') {
		$InsertSQL = "INSERT INTO care_test_request_blood (`batch_nr`,
															`encounter_nr`,
															`dept_nr`,
															`blood_group`,
															`rh_factor`,
															`kell`,
															`date_protoc_nr`,
															`pure_blood`,
															`red_blood`,
															`leukoless_blood`,
															`washed_blood`,
															`prp_blood`,
															`thrombo_con`,
															`ffp_plasma`,
															`transfusion_dev`,
															`match_sample`,
															`transfusion_date`,
															`diagnosis`,
															`notes`,
															`send_date`,
															`doctor`,
															`phone_nr`,
															`status`,
															`blood_pb`,
															`blood_rb`,
															`blood_llrb`,
															`blood_wrb`,
															`blood_prp`,
															`blood_tc`,
															`blood_ffp`,
															`x_test_1_name`,
															`x_test_2_name`,
															`x_test_3_name`,
															`release_via`,
															`receipt_ack`,
															`mainlog_nr`,
															`lab_nr`,
															`mainlog_sign`,
															`lab_sign`,
															`history`,
															`modify_id`,
															`create_id`,
															`create_time`
														) VALUES (
															NULL,
															'" . $Encounter . "',
															'" . $DeptNumber . "',
															'" . $_POST['BloodGroup'] . "',
															'" . $_POST['Rhesus'] . "',
															'" . $_POST['Kell'] . "',
															'" . $_POST['DateProtocolNumber'] . "',
															'" . $_POST['PureBlood'] . "',
															'" . $_POST['RedBlood'] . "',
															'" . $_POST['LeukolessBlood'] . "',
															'" . $_POST['WashedBlood'] . "',
															'" . $_POST['PRPBlood'] . "',
															'" . $_POST['PlateletConcentrate'] . "',
															'" . $_POST['FreshFrozenPlasma'] . "',
															'" . $_POST['TransfusionDev'] . "',
															'" . $_POST['MatchSample'] . "',
															'" . FormatDateForSQL($_POST['TransfusionDate']) . "',
															'" . $_POST['Diagnosis'] . "',
															'" . $_POST['Notes'] . "',
															'" . FormatDateForSQL($_POST['Date']) . "',
															'" . $_POST['Doctor'] . "',
															'" . $_POST['Telephone'] . "',
															'pending',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'',
															'Create: " . date('Y-m-d H:i:s') . " = " . $_SESSION['UserID'] . "\n',
															'" . $_SESSION['UserID'] . "',
															'" . $_SESSION['UserID'] . "',
															CURRENT_DATE
														)";
		$InsertResult = DB_query($InsertSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to insert the request into the database'), 'error');
			include ('includes/footer.php');
			exit;
		} else {
			prnMsg(_('The request was successfully sent'), 'success');
		}
		$SelectedBatch = DB_Last_Insert_ID('care_test_request_blood', 'batch_nr');
	} else {
		$HistorySQL = "SELECT history FROM care_test_request_blood WHERE batch_nr='" . $_POST['SelectedBatch'] . "'";
		$HistoryResult = DB_query($HistorySQL);
		$HistoryRow = DB_fetch_array($HistoryResult);

		$History = $HistoryRow['history'] . "\n" . 'Updated: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'];

		$UpdateSQL = "UPDATE care_test_request_blood SET `blood_group`='" . $_POST['BloodGroup'] . "',
														`rh_factor`='" . $_POST['Rhesus'] . "',
														`kell`='" . $_POST['Kell'] . "',
														`date_protoc_nr`='" . $_POST['DateProtocolNumber'] . "',
														`pure_blood`='" . $_POST['PureBlood'] . "',
														`red_blood`='" . $_POST['RedBlood'] . "',
														`leukoless_blood`='" . $_POST['LeukolessBlood'] . "',
														`washed_blood`='" . $_POST['WashedBlood'] . "',
														`prp_blood`='" . $_POST['PRPBlood'] . "',
														`thrombo_con`='" . $_POST['PlateletConcentrate'] . "',
														`ffp_plasma`='" . $_POST['FreshFrozenPlasma'] . "',
														`transfusion_dev`='" . $_POST['TransfusionDev'] . "',
														`match_sample`='" . $_POST['MatchSample'] . "',
														`transfusion_date`='" . FormatDateForSQL($_POST['TransfusionDate']) . "',
														`diagnosis`='" . $_POST['Diagnosis'] . "',
														`notes`='" . $_POST['Notes'] . "',
														`send_date`='" . FormatDateForSQL($_POST['Date']) . "',
														`doctor`='" . $_POST['Doctor'] . "',
														`phone_nr`='" . $_POST['Telephone'] . "',
														`status`='updated',
														`history`='" . $History . "',
														`modify_id`='" . $_SESSION['UserID'] . "'
													WHERE batch_nr='" . $SelectedBatch . "'";
		$UpdateResult = DB_query($UpdateSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to update the request'), 'error');
			include ('includes/footer.php');
			exit;
		} else {
			prnMsg(_('The request was successfully updated'), 'success');
		}
	}
}

if (isset($SelectedBatch) and $SelectedBatch != '') {
	$SQL = "SELECT `encounter_nr`,
					`dept_nr`,
					`blood_group`,
					`rh_factor`,
					`kell`,
					`date_protoc_nr`,
					`pure_blood`,
					`red_blood`,
					`leukoless_blood`,
					`washed_blood`,
					`prp_blood`,
					`thrombo_con`,
					`ffp_plasma`,
					`transfusion_dev`,
					`match_sample`,
					`transfusion_date`,
					`diagnosis`,
					`notes`,
					`send_date`,
					`doctor`,
					`phone_nr`
				FROM care_test_request_blood
				WHERE batch_nr='" . $SelectedBatch . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$BloodGroup = $MyRow['blood_group'];
	$Rhesus = $MyRow['rh_factor'];
	$Kell = $MyRow['kell'];
	$DateProtocolNumber = $MyRow['date_protoc_nr'];
	$PureBlood = $MyRow['pure_blood'];
	$RedBlood = $MyRow['red_blood'];
	$LeukolessBlood = $MyRow['leukoless_blood'];
	$WashedBlood = $MyRow['washed_blood'];
	$PRPBlood = $MyRow['prp_blood'];
	$PlateletConcentrate = $MyRow['thrombo_con'];
	$FreshFrozenPlasma = $MyRow['ffp_plasma'];
	$TransfusionDev = $MyRow['transfusion_dev'];
	$MatchSample = $MyRow['match_sample'];
	$TransfusionDate = ConvertSQLDate($MyRow['transfusion_date']);
	$Diagnosis = $MyRow['diagnosis'];
	$Notes = $MyRow['notes'];
	$Date = ConvertSQLDate($MyRow['send_date']);
	$Doctor = $MyRow['doctor'];
	$Telephone = $MyRow['phone_nr'];

} else {
	$BloodGroup = '';
	$Rhesus = '';
	$Kell = '';
	$DateProtocolNumber = '';
	$PureBlood = '';
	$RedBlood = '';
	$LeukolessBlood = '';
	$WashedBlood = '';
	$PRPBlood = '';
	$PlateletConcentrate = '';
	$FreshFrozenPlasma = '';
	$TransfusionDev = '';
	$MatchSample = '';
	$TransfusionDate = date($_SESSION['DefaultDateFormat']);
	$Diagnosis = '';
	$Notes = '';
	$Date = date($_SESSION['DefaultDateFormat']);
	$Doctor = '';
	$Telephone = '';
}

if (isset($SelectedPatient) and $SelectedPatient != '') {
	$PID = $SelectedPatient;
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="SelectedPatient" value="', $SelectedPatient, '" />';
	echo '<input type="hidden" name="SelectedBatch" value="', $SelectedBatch, '" />';

	echo '<div class="blood_request_container centre">';

	echo '<div class="blood_request_general">
			<fieldset>
				<legend>', _('General Details'), '</legend>';

	if ($BloodGroup == 'A') {
		$CheckedA = ' selected="selected" ';
	} else {
		$CheckedA = ' ';
	}
	if ($BloodGroup == 'B') {
		$CheckedB = ' selected="selected" ';
	} else {
		$CheckedB = ' ';
	}
	if ($BloodGroup == 'AB') {
		$CheckedAB = ' selected="selected" ';
	} else {
		$CheckedAB = ' ';
	}
	if ($BloodGroup == 'O') {
		$CheckedO = ' selected="selected" ';
	} else {
		$CheckedO = ' ';
	}
	echo '<field>
			<label for="BloodGroup">', _('Blood Group'), '</label>
			<select name="BloodGroup">
				<option value=""></option>
				<option', $CheckedA, 'value="A">A</option>
				<option', $CheckedB, 'value="B">B</option>
				<option', $CheckedAB, 'value="AB">AB</option>
				<option', $CheckedO, 'value="O">O</option>
			</select>
		</field>';

	if ($Rhesus == 'Pos') {
		$CheckedPos = ' selected="selected" ';
	} else {
		$CheckedPos = ' ';
	}
	if ($Rhesus == 'Neg') {
		$CheckedNeg = ' selected="selected" ';
	} else {
		$CheckedNeg = ' ';
	}
	echo '<field>
			<label for="Rhesus">', _('Rhesus Factor'), '</label>
			<select name="Rhesus">
				<option value=""></option>
				<option', $CheckedPos, 'value="Pos">Rh+</option>
				<option', $CheckedNeg, 'value="Neg">Rh-</option>
			</select>
		</field>';

	if ($Kell == 'Pos') {
		$CheckedPos = ' selected="selected" ';
	} else {
		$CheckedPos = ' ';
	}
	if ($Kell == 'Neg') {
		$CheckedNeg = ' selected="selected" ';
	} else {
		$CheckedNeg = ' ';
	}
	echo '<field>
			<label for="Kell">', _('Kell'), '</label>
			<select name="Kell">
				<option value=""></option>
				<option', $CheckedPos, 'value="Pos">k+</option>
				<option', $CheckedNeg, 'value="Neg">k-</option>
			</select>
		</field>';

	echo '<field>
			<label for="DateProtocolNumber">', _('Date & protocol nr. of test by'), '</label>
			<input type="text" name="DateProtocolNumber" size=45 maxlength=45 value="', $DateProtocolNumber, '">
		</field>';

	echo '</fieldset>
		</div>';

	echo '<div class="blood_request_label">
			<fieldset>
				<legend>', _('Label'), '</legend>';

	if ($MatchSample == 1) {
		$CheckedYes = ' selected="selected" ';
	} else {
		$CheckedYes = ' ';
	}
	if ($MatchSample == 0) {
		$CheckedNo = ' selected="selected" ';
	} else {
		$CheckedNo = ' ';
	}
	echo '<field>
			<label for="MatchSample">', _('With match test sample(s) from the blood bank'), '</label>
			<select name="MatchSample">
				<option value=""></option>
				<option', $CheckedYes, 'value="1">', _('Yes'), '</option>
				<option', $CheckedNo, 'value="0">', _('No'), '</option>
			</select>
		</field>';

	include ('includes/KCMCDrawPatientLabel.php');
	echo '</fieldset>
		</div>';

	echo '<div class="blood_request_specimen">
			<fieldset>
				<legend>', _('Specimen Counts'), '</legend>';

	echo '<field>
			<label for="PureBlood">', _('Pure blood'), '</label>
			<input type="text" name="PureBlood" size=15 maxlength=45 value="', $PureBlood, '">
		</field>';

	echo '<field>
			<label for="RedBlood">', _('Red blood cell concentrate'), '</label>
			<input type="text" name="RedBlood" size=15 maxlength=45 value="', $RedBlood, '">
		</field>';

	echo '<field>
			<label for="LeukolessBlood">', _('Leukocytedepleted red blood cell'), '</label>
			<input type="text" name="LeukolessBlood" size=15 maxlength=45 value="', $LeukolessBlood, '">
		</field>';

	echo '<field>
			<label for="WashedBlood">', _('Washed red blood cells'), '</label>
			<input type="text" name="WashedBlood" size=15 maxlength=45 value="', $WashedBlood, '">
		</field>';

	echo '<field>
			<label for="PRPBlood">', _('Platelet rich plasma'), '</label>
			<input type="text" name="PRPBlood" size=15 maxlength=45 value="', $PRPBlood, '">
		</field>';

	echo '<field>
			<label for="PlateletConcentrate">', _('Platelet concentrate'), '</label>
			<input type="text" name="PlateletConcentrate" size=15 maxlength=45 value="', $PlateletConcentrate, '">
		</field>';

	echo '<field>
			<label for="FreshFrozenPlasma">', _('Fresh frozen plasma'), '</label>
			<input type="text" name="FreshFrozenPlasma" size=15 maxlength=45 value="', $FreshFrozenPlasma, '">
		</field>';

	echo '<field>
			<label for="TransfusionDev">', _('Transfusion devices'), '</label>
			<input type="text" name="TransfusionDev" size=20 maxlength=45 value="', $TransfusionDev, '">
		</field>';

	echo '</fieldset>
		</div>';

	echo '<div class="blood_request_notes">
			<fieldset>
				<legend>', _('Notes'), '</legend>';

	echo '<field>
			<label for="TransfusionDate">', _('Transfusion date'), '</label>
			<input type="text" class="date" name="TransfusionDate" size=10 value="', $TransfusionDate, '">
		</field>';

	echo '<field>
			<label for="Diagnosis">', _('Diagnosis'), '</label>
			<textarea name="Diagnosis" cols=25 rows=4 wrap="physical">', $Diagnosis, '</textarea>
		</field>';

	echo '<field>
			<label for="Notes">', _('Notes/special orders'), '</label>
			<textarea name="Notes" cols=25 rows=4 wrap="physical">', $Notes, '</textarea>
		</field>';

	echo '<field>
			<label for="Date">', _('Date'), '</label>
			<input type="text" class="date" name="Date" size=10 value="', $Date, '">
		</field>';

	echo '<field>
			<label for="Doctor">', _('Doctor'), '</label>
			<input type="text" name="Doctor" size=20 maxlength=45 value="', $Doctor, '">
		</field>';

	echo '<field>
			<label for="Telephone">', _('Telephone'), '</label>
			<input type="tel" name="Telephone" size=20 maxlength=45 value="', $Telephone, '">
		</field>';

	echo '<field>
			<b>', _('The ordering doctor is responsible for this order!'), '</b><br />', _('For security reasons, only sample tubes with full name & birthdate will be accepted.'), '</field>';

	echo '</fieldset>
		</div>';

	echo '</div>';

	echo '<div class="centre">
			<input type="submit" name="Send" value="', _('Send Request'), '" />
		</div>';

	echo '</form>';
} else {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
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