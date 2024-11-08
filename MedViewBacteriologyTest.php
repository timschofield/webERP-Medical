<?php
include ('includes/session.php');
$Title = _('View Bacteriology Tests');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

if (isset($_POST['SelectedBatch'])) {
	$SelectedBatch = $_POST['SelectedBatch'];
} else if (isset($_GET['SelectedBatch'])) {
	$SelectedBatch = $_GET['SelectedBatch'];
} else {
	prnMsg(_('You must first select a batch of tests to view'), 'warn');
	echo '<div class="centre">
			<a href="', $RootPath, '/KCMCBacteriologyLaboratory.php>', _('Select a batch to view'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('View Bacteriology Test Batch'), '" /> ', _('View Bacteriology Test Batch'), ' ', $SelectedBatch, '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

$SQL = "SELECT `encounter_nr`,
				`dept_nr`,
				`material_note`,
				`diagnosis_note`,
				`immune_supp`,
				`sample_date`,
				`send_date`,
				`history`
			FROM care_test_request_baclabor
			WHERE batch_nr='" . $SelectedBatch . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

$PID = GetPIDFromEncounter($MyRow['encounter_nr']);

if ($MyRow['immune_supp'] == 1) {
	$ImmuneSuppressed = _('Yes');
} else {
	$ImmuneSuppressed = _('No');
}

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
			<td>', _('Date Sent'), '</td>
			<td>', ConvertSQLDate($MyRow['send_date']), '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Sample Taken'), '</td>
			<td>', ConvertSQLDate($MyRow['sample_date']), '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Materials Note'), '</td>
			<td>', $MyRow['material_note'], '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Diagnosis Note'), '</td>
			<td>', $MyRow['diagnosis_note'], '</td>
		</tr>
		<tr class="striped_row">
			<td>', _('Immune Suppressed'), '</td>
			<td>', $ImmuneSuppressed, '</td>
		</tr>
	</table>';

$SQL = "SELECT encounter_nr,
				material,
				care_baclabor_material_type.name
			FROM care_test_request_baclabor_sub
			INNER JOIN care_baclabor_material_type
				ON care_baclabor_material_type.type=care_test_request_baclabor_sub.material
			WHERE batch_nr='" . $SelectedBatch . "'
			AND material_value<>0";
$Result = DB_query($SQL);

echo '<table>
		<thead>
			<tr>
				<th colspan="5">', _('Materials'), '</th>
			</tr>
			<tr>
				<th class="SortedColumn">', _('Material Name'), '</th>
				<th class="SortedColumn">', _('Material ID'), '</th>
			</tr>
		</thead>';

echo '<tbody>';

while ($TestRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td>', $TestRow['name'], '</td>
			<td>', $TestRow['material'], '</td>
		</tr>';
}

echo '</tbody>
	</table>';

$SQL = "SELECT encounter_nr,
				test_type,
				care_baclabor_test_type.name
			FROM care_test_request_baclabor_sub
			INNER JOIN care_baclabor_test_type
				ON care_baclabor_test_type.type=care_test_request_baclabor_sub.test_type
			WHERE batch_nr='" . $SelectedBatch . "'
			AND test_type_value<>0";
$Result = DB_query($SQL);

echo '<table>
		<thead>
			<tr>
				<th colspan="5">', _('Tests to be carried out'), '</th>
			</tr>
			<tr>
				<th class="SortedColumn">', _('Test Name'), '</th>
				<th class="SortedColumn">', _('Test ID'), '</th>
			</tr>
		</thead>';

echo '<tbody>';

while ($TestRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td>', $TestRow['name'], '</td>
			<td>', $TestRow['test_type'], '</td>
		</tr>';
}

echo '</tbody>
	</table>';

echo '<div class="centre">
		<a href="', $RootPath, '/KCMCBacteriologyLaboratory.php">', _('Return to Bacteriology Centre'), '</a>
	</div>';

include ('includes/footer.php');

?>