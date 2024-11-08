<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Pending Bacteriology Laboratory Test Requests');
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
				send_date,
				material_note,
				diagnosis_note,
				immune_supp
			FROM care_test_request_baclabor
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
				<th class="SortedColumn">', _('Material Note'), '</th>
				<th class="SortedColumn">', _('Diagnosis Note'), '</th>
				<th class="SortedColumn">', _('Immune Suppressed'), '</th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>';

echo '<tbody>';

while ($MyRow = DB_fetch_array($Result)) {

	$PID = GetPIDFromEncounter($MyRow['encounter_nr']);

	$CheckSQL = "SELECT nr FROM care_billable_items WHERE pid='" . $PID . "' AND is_paid==0";
	$CheckResult = DB_query($CheckSQL);

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

	if ($MyRow['immune_supp'] == 1) {
		$ImmuneSuppressed = _('Yes');
	} else {
		$ImmuneSuppressed = _('No');
	}

	echo '<tr class="striped_row">
			<td>', $MyRow['batch_nr'], '</td>
			<td>', $PID, '</td>
			<td>', $PatientName, '</td>
			<td>', $PhoneNo, '</td>
			<td>', ConvertSQLDate($MyRow['send_date']), '</td>
			<td>', $MyRow['material_note'], '</td>
			<td>', $MyRow['diagnosis_note'], '</td>
			<td>', $ImmuneSuppressed, '</td>
			<td><a href="', $RootPath, '/KCMCRequestBacteriologyTest.php?Edit=Yes&SelectedBatch=', $MyRow['batch_nr'], '">', _('Edit Batch'), '</a></td>
			<td><a href="', $RootPath, '/KCMCViewBacteriologyTest.php?SelectedBatch=', $MyRow['batch_nr'], '">', _('View Batch'), '</a></td>
			<td><a href="', $RootPath, '/KCMCEnterBacteriologyTestResults.php?Batch=', $MyRow['batch_nr'], '">', _('Enter Results'), '</a></td>
		</tr>';
}

echo '</tbody>
	</table>';

echo '<div class="centre">
		<a href="', $RootPath, '/KCMCBacteriologyLaboratory.php">', _('Return to Bacteriology Centre'), '</a>
	</div>';

include ('includes/footer.php');

?>