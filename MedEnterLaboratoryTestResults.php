<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Enter Medical Laboratory Test Data');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

if (isset($_POST['Batch'])) {
	$SelectedBatch = $_POST['Batch'];
} else if (isset($_GET['Batch'])) {
	$SelectedBatch = $_GET['Batch'];
} else {
	prnMsg(_('You must first select a batch of tests to enter results for'), 'warn');
	echo '<div class="centre">
			<a href="', $RootPath, '/KCMCPendingLaboratoryTests.php">', _('Select a batch to process'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('View Lab Test Batch'), '" /> ', _('View Lab Test Batch'), ' ', $SelectedBatch, '
	</p>';

$DaysOfWeek = array(0 => _('Sunday'), 1 => _('Monday'), 2 => _('Tuesday'), 3 => _('Wednesday'), 4 => _('Thursday'), 5 => _('Friday'), 6 => _('Saturday'));

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');
$HeaderSQL = "SELECT batch_nr,
				encounter_nr,
				doctor_sign,
				notes,
				send_date,
				sample_time,
				sample_weekday,
				urgent
			FROM care_test_request_chemlabor
			WHERE batch_nr='" . $SelectedBatch . "'";
$HeaderResult = DB_query($HeaderSQL);
$HeaderRow = DB_fetch_array($HeaderResult);

$PID = GetPIDFromEncounter($HeaderRow['encounter_nr']);

$SQL = "SELECT pid,
				hospital_file_nr,
				name_first,
				name_last,
				phone_1_nr
			FROM care_person
			WHERE pid='" . $PID . "'";
$PatientResult = DB_query($SQL);
$PatientRow = DB_fetch_array($PatientResult);

$PatientName = $PatientRow['name_first'] . ' ' . $PatientRow['name_last'];
$PhoneNo = $PatientRow['phone_1_nr'];

$SQL = "SELECT realname FROM www_users WHERE userid='" . $HeaderRow['doctor_sign'] . "'";
$DoctorResult = DB_query($SQL);
$DoctorRow = DB_fetch_array($DoctorResult);

if ($HeaderRow['urgent'] == 0) {
	$Urgent = _('No');
} else {
	$Urgent = _('Yes');
}

$History = 'Created - ' . date($_SESSION['DefaultDateFormat'] . ' - ' . $_SESSION['UserID']);

if (isset($_POST['SaveResults'])) {
	$InsertHeaderSQL = "INSERT INTO `care_test_findings_chemlab` (`batch_nr`,
																`encounter_nr`,
																`job_id`,
																`test_date`,
																`test_time`,
																`group_id`,
																`serial_value`,
																`validator`,
																`validate_dt`,
																`status`,
																`history`,
																`modify_id`,
																`modify_time`,
																`create_id`,
																`create_time`
															) VALUES (
																NULL,
																'" . $HeaderRow['encounter_nr'] . "',
																'" . $HeaderRow['batch_nr'] . "',
																'" . FormatDateForSQL($_POST['ExaminationDate']) . "',
																CURRENT_TIME,
																'',
																'',
																'',
																CURRENT_TIME,
																'entered',
																'" . $History . "',
																'" . $_SESSION['UserID'] . "',
																NOW(),
																'" . $_SESSION['UserID'] . "',
																NOW()
															)";
	$InsertHeaderResult = DB_query($InsertHeaderSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was a problem inserting the header information for these results'), 'error');
		include ('includes/footer.php');
		exit;
	}

	$BatchNumber = DB_Last_Insert_ID('care_test_findings_chemlab', 'batch_nr');

	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 6) == 'param_') {
			$TestID = mb_substr($Key, 6);
			$InsertLineSQL = "INSERT INTO `care_test_findings_chemlabor_sub` (`sub_id`,
																			`batch_nr`,
																			`job_id`,
																			`encounter_nr`,
																			`paramater_name`,
																			`parameter_value`,
																			`status`,
																			`history`,
																			`test_date`,
																			`test_time`,
																			`create_id`,
																			`create_time`
																		) VALUES (
																			NULL,
																			'" . $BatchNumber . "',
																			'" . $HeaderRow['batch_nr'] . "',
																			'" . $HeaderRow['encounter_nr'] . "',
																			'" . $TestID . "',
																			'" . $Value . "',
																			'entered',
																			'" . $History . "',
																			'" . FormatDateForSQL($_POST['ExaminationDate']) . "',
																			CURRENT_TIME,
																			'" . $_SESSION['UserID'] . "',
																			NOW()
																		)";
			$InsertLineResult = DB_query($InsertLineSQL);
			if (DB_error_no() > 0) {
				prnMsg(_('There was a problem inserting the line information for these results'), 'error');
				include ('includes/footer.php');
				exit;
			}
		}
	}

	$SQL = "UPDATE care_test_request_chemlabor SET status='entered' WHERE batch_nr='" . $HeaderRow['batch_nr'] . "'";
	$Result = DB_query($SQL);
	if (DB_error_no() > 0) {
		prnMsg(_('There was a problem updating the status of the request'), 'error');
		include ('includes/footer.php');
		exit;
	}

	prnMsg(_('The test results were saved successfully'), 'success');

	echo '<div class="centre">
			<a href="', $RootPath, '/KCMCPendingLaboratoryTests.php">', _('Select another batch to enter'), '</a>
		</div>';

	include ('includes/footer.php');
	exit;
} else {

	echo '<table>
			<tr>
				<th colspan="5">', _('Batch Summary'), '
					<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print Test Request'), '" alt="', _('Print Test Request'), '" onclick="window.print();" />
				</th>
			</tr>
			<tr class="striped_row">
				<td>', _('Batch Number'), '</td>
				<td class="number">', $SelectedBatch, '</td>
			</tr>
			<tr class="striped_row">
				<td>', _('Patient'), '</td>
				<td>', $PatientName, ' (', _('PID'), ' - ', $PID, ')</td>
			</tr>
			<tr class="striped_row">
				<td>', _('Patient Phone'), '</td>
				<td>', $PhoneNo, '</td>
			</tr>
			<tr class="striped_row">
				<td>', _('Clinicians Notes'), '</td>
				<td>', $HeaderRow['notes'], '</td>
			</tr>
			<tr class="striped_row">
				<td>', _('Date Sent'), '</td>
				<td>', ConvertSQLDate($HeaderRow['send_date']), '</td>
			</tr>
			<tr class="striped_row">
				<td>', _('Sample Taken'), '</td>
				<td>', $DaysOfWeek[$HeaderRow['sample_weekday']], ' - ', $HeaderRow['sample_time'], '</td>
			</tr>
			<tr class="striped_row">
				<td>', _('Clinician'), '</td>
				<td>', $DoctorRow['realname'], ' - ', $HeaderRow['doctor_sign'], '</td>
			</tr>
			<tr class="striped_row">
				<td>', _('Request Urgent'), '</td>
				<td>', $Urgent, '</td>
			</tr>
		</table>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="Batch" value="', $SelectedBatch, '" />';

	echo '<fieldset>
			<legend>', _('Enter Test Results For Batch'), ' - ', $SelectedBatch, '</legend>';

	echo '<field>
			<label for="ExainationDate">', _('Examination Date'), '</label>
			<input type="text" size="10" class="date" name="ExaminationDate" value="', date($_SESSION['DefaultDateFormat']), '" />
		</field>';

	$SQL = "SELECT care_test_request_chemlabor_sub.paramater_name,
					care_test_request_chemlabor_sub.parameter_value,
					care_test_param.name,
					care_test_param.id,
					care_test_param.group_id
				FROM care_test_request_chemlabor_sub
				INNER JOIN care_test_param
					ON care_test_request_chemlabor_sub.paramater_name=care_test_param.id
				WHERE batch_nr='" . $SelectedBatch . "'";
	$Result = DB_query($SQL);

	while ($TestRow = DB_fetch_array($Result)) {
		echo '<field>
				<label for="">', $TestRow['id'], ' - ', $TestRow['name'], '</label>
				<input type="text" name="param_', $TestRow['id'], '" value="" />
			</field>';
	}

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SaveResults" value="', _('Save Results'), '" />
		</div>';

	echo '</form>';
}

include ('includes/footer.php');

?>