<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Pending Blood Test Requests');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', $Title, '" /> ', $Title, '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

$SQL = "SELECT `batch_nr`,
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
				`phone_nr`
			FROM care_test_request_blood
			WHERE status='pending'";
$Result = DB_query($SQL);

echo '<table>
		<thead>
			<tr>
				<th colspan="11">', _('Pending Test Requests'), '</th>
			</tr>
			<tr>
				<th class="SortedColumn">', _('Batch Number'), '</th>
				<th class="SortedColumn">', _('Patient ID'), '</th>
				<th class="SortedColumn">', _('Patient Name'), '</th>
				<th class="SortedColumn">', _('Patient Phone'), '</th>
				<th class="SortedColumn">', _('Date Batch Sent'), '</th>
				<th class="SortedColumn">', _('Blood Group'), '</th>
				<th class="SortedColumn">', _('Diagnosis'), '</th>
				<th class="SortedColumn">', _('Notes'), '</th>
				<th></th>
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

	echo '<tr class="striped_row">
			<td>', $MyRow['batch_nr'], '</td>
			<td>', $PID, '</td>
			<td>', $PatientName, '</td>
			<td>', $PhoneNo, '</td>
			<td>', ConvertSQLDate($MyRow['send_date']), '</td>
			<td>', $MyRow['blood_group'], ' ', $MyRow['rh_factor'], '</td>
			<td>', $MyRow['diagnosis'], '</td>
			<td>', $MyRow['notes'], '</td>
			<td><a href="', $RootPath, '/KCMCRequestBloodTest.php?Edit=Yes&SelectedBatch=', $MyRow['batch_nr'], '&SelectedPatient=', $PID, '">', _('Edit Batch'), '</a></td>
			<td><a href="', $RootPath, '/KCMCViewBloodTest.php?SelectedBatch=', $MyRow['batch_nr'], '">', _('View Batch'), '</a></td>
			<td><a href="', $RootPath, '/KCMCEnterBloodTestResults.php?Batch=', $MyRow['batch_nr'], '">', _('Enter Results'), '</a></td>
		</tr>';
}

echo '</tbody>
	</table>';

echo '<div class="centre">
		<a href="', $RootPath, '/KCMCBloodLaboratory.php">', _('Return to Bllod Centre'), '</a>
	</div>';

include ('includes/footer.php');

?>