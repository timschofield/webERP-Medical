<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Pending Pathology Laboratory Test Requests');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', $Title, '" /> ', $Title, '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

$SQL = "SELECT batch_nr,
				encounter_nr,
				doctor_sign,
				send_date
			FROM care_test_request_patho
			WHERE status='pending'";
$Result = DB_query($SQL);

echo '<table>
		<thead>
			<tr>
				<th colspan="10">', _('Pending Test Requests'), '</th>
			</tr>
			<tr>
				<th class="SortedColumn">', _('Batch Number'), '</th>
				<th class="SortedColumn">', _('Patient ID'), '</th>
				<th class="SortedColumn">', _('Patient Name'), '</th>
				<th class="SortedColumn">', _('Patient Phone'), '</th>
				<th class="SortedColumn">', _('Doctors Name'), '</th>
				<th class="SortedColumn">', _('Date Batch Sent'), '</th>
				<th></th>
				<th></th>
			</tr>
		</thead>';

echo '<tbody>';

while ($MyRow = DB_fetch_array($Result)) {

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

	echo '<tr class="striped_row">
			<td>', $MyRow['batch_nr'], '</td>
			<td>', $PID, '</td>
			<td>', $PatientName, '</td>
			<td>', $PhoneNo, '</td>
			<td>', $DoctorRow['realname'], '</td>
			<td>', ConvertSQLDate($MyRow['send_date']), '</td>
			<td><a href="', $RootPath, '/KCMCViewPathologyTest.php?Batch=', $MyRow['batch_nr'], '">', _('View Batch'), '</a></td>
			<td><a href="', $RootPath, '/KCMCEnterPathologyTestResults.php?Batch=', $MyRow['batch_nr'], '">', _('Enter Results'), '</a></td>
		</tr>';
}

echo '</tbody>
	</table>';

echo '<div class="centre">
		<a href="', $RootPath, '/KCMCPathologyLaboratory.php">', _('Return to Pathology Laboratory Centre'), '</a>
	</div>';

include ('includes/footer.php');

?>