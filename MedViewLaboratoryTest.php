<?php
include ('includes/session.php');
$Title = _('View Lab Tests');
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
			<a href="', $RootPath, '/KCMCPendingLaboratoryTests.php>', _('Select a batch to view'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}

$DaysOfWeek = array(0 => _('Sunday'), 1 => _('Monday'), 2 => _('Tuesday'), 3 => _('Wednesday'), 4 => _('Thursday'), 5 => _('Friday'), 6 => _('Saturday'));

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('View Lab Test Batch'), '" /> ', _('View Lab Test Batch'), ' ', $SelectedBatch, '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

$SQL = "SELECT batch_nr,
				encounter_nr,
				doctor_sign,
				notes,
				send_date,
				sample_time,
				sample_weekday,
				urgent
			FROM care_test_request_chemlabor
			WHERE batch_nr='" . $SelectedBatch . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

$PID = GetPIDFromEncounter($MyRow['encounter_nr']);

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

$SQL = "SELECT realname FROM www_users WHERE userid='" . $MyRow['doctor_sign'] . "'";
$DoctorResult = DB_query($SQL);
$DoctorRow = DB_fetch_array($DoctorResult);

if ($MyRow['urgent'] == 0) {
	$Urgent = _('No');
} else {
	$Urgent = _('Yes');
}

echo '<table>
		<tr>
			<th colspan="5">', _('Batch Summary'), '
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print Test Request'), '" alt="', _('Print Test Request'), '" onclick="window.print();" />
			</th>
		</tr>
		<tr class="striped_row">
			<td>', _('Batch Number'), '</td>
			<td class="number">', $MyRow['batch_nr'], '</td>
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
			<td>', $MyRow['notes'], '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Date Sent'), '</td>
			<td>', ConvertSQLDate($MyRow['send_date']), '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Sample Taken'), '</td>
			<td>', $DaysOfWeek[$MyRow['sample_weekday']], ' - ', $MyRow['sample_time'], '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Clinician'), '</td>
			<td>', $DoctorRow['realname'], ' - ', $MyRow['doctor_sign'], '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Request Urgent'), '</td>
			<td>', $Urgent, '</td>
		</tr>
	</table>';

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

echo '<table>
		<thead>
			<tr>
				<th colspan="5">', _('Tests to be carried out'), '</th>
			</tr>
			<tr>
				<th class="SortedColumn">', _('Test Name'), '</th>
				<th class="SortedColumn">', _('Test ID'), '</th>
				<th class="SortedColumn">', _('Test Group'), '</th>
			</tr>
		</thead>';

echo '<tbody>';

while ($TestRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td>', $TestRow['name'], '</td>
			<td>', $TestRow['id'], '</td>
			<td>', $TestRow['group_id'], '</td>
		</tr>';
}

echo '</tbody>
	</table>';

echo '<div class="centre">
		<a href="', $RootPath, '/KCMCMedicalLaboratory.php">', _('Return to Laboratory Centre'), '</a>
	</div>';

include ('includes/footer.php');

?>