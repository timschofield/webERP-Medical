<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Search Patients');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', _('Patients'), '" /> ', _('Patients'), '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($_GET['Select'])) {
	$_SESSION['PatientID'] = $_GET['Select'];
} // isset($_GET['Select'])
if (!isset($_SESSION['PatientID'])) { // initialise if not already done
	$_SESSION['PatientID'] = '';
} // !isset($_SESSION['PatientID'])
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
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Select=', $MyRow['pid'], '">', _('Select'), '</a></td>
				</tr>';
		}
		echo '</tbody>
			</table>';
	}
} // end of if search
if ($_SESSION['PatientID'] != '' and !isset($_POST['Search'])) {
	$SQL = "SELECT pid,
					hospital_file_nr,
					name_first,
					name_last,
					phone_1_nr
				FROM care_person
				WHERE pid='" . $_SESSION['PatientID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
	$PhoneNo = $MyRow['phone_1_nr'];

	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', _('Patient'), '" /> ', // Icon title.
	_('Patient'), ' : ', stripslashes($_SESSION['PatientID']), ' - ', $PatientName, ' - ', $PhoneNo, _(' has been selected'), '
		</p>';
	echo '<div class="page_help_text">', _('Select a menu option to operate using this patient'), '.</div>';

	echo '<fieldset style="text-align:center">';
	// Customer inquiries options:
	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', _('Inquiries and Reports'), '" />', _('Patient Inquiries'), '</legend>
			<ul>
			</ul>
		</fieldset>';

	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', _('Patient Transactions'), '" />', _('Patient Transactions'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="', $RootPath, '/KCMCRegister.php?Edit=Yes&SelectedPatient=', urlencode($_SESSION['PatientID']), '">', _('Modify patient registration data'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/KCMCInpatientAdmission.php?Edit=Yes&SelectedPatient=', urlencode($_SESSION['PatientID']), '">', _('Modify Inpatient admission data'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/KCMCOutpatientAdmission.php?Edit=Yes&SelectedPatient=', urlencode($_SESSION['PatientID']), '">', _('Modify Outpatient admission data'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/KCMCDischargePatient.php?Encounter=', urlencode(GetEncounterFromPID($_SESSION['PatientID'])), '">', _('Discharge this patient'), '</a>
				</li>
			</ul>
		</fieldset>';

	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" data-title="', _('Patient Maintenance'), '" />', _('Patient Maintenance'), '</legend>
			<ul>
			</ul>
		</fieldset>';

	echo '</fieldset>';

}

// Search for customers:
echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">
		<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" /> ', _('Search for Patients'), '
	</p>'; // Page title.
echo '<fieldset>
		<legend>', _('Search Criteria'), '</legend>
		<field>
			<label for="Keywords">', _('Enter a partial Name'), ':</label>';
if (isset($_POST['Keywords'])) {
	echo '<input type="search" name="Keywords" value="', $_POST['Keywords'], '" size="20" maxlength="25" />';
} // isset($_POST['Keywords'])
else {
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
		<input name="CSV" type="submit" value="', _('CSV Format'), '" />
	</div>';

echo '</form>';

include ('includes/footer.php');
?>