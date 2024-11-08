<?php
include ('includes/session.php');
$Title = _('Admit Outpatient');
include ('includes/header.php');

if (isset($_POST['SelectedPatient'])) {
	$SelectedPatient = $_POST['SelectedPatient'];
} elseif (isset($_GET['SelectedPatient'])) {
	$SelectedPatient = $_GET['SelectedPatient'];
}

if (isset($SelectedPatient) and isset($_GET['Edit'])) {
	$SQL = "SELECT current_dept_nr FROM care_encounter WHERE pid='" . $SelectedPatient . "' AND is_discharged=0 AND current_dept_nr<>0";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('This patient is not currently admitted as an outpatient'), 'error');
		echo '<div class="centre">
				<a href="', $RootPath, '/KCMCSelectPatient.php?Select=', $SelectedPatient, '">', _('Return to Patient Screen'), '</a>
			</div>';
		include ('includes/footer.php');
		exit;
	}
}

if (isset($_POST['Create'])) {

	/* First off check that this patient isn't currently admitted */
	$SQL = "SELECT pid FROM care_encounter WHERE is_discharged=0";
	$CheckResult = DB_query($SQL);

	if (DB_num_rows($CheckResult) == 0) {

		$SQL = "SELECT pid FROM care_person WHERE pid='" . $SelectedPatient . "'";
		$Result = DB_query($SQL);
		$MyPIDRow = DB_fetch_array($Result);

		$SQL = "INSERT INTO care_encounter (pid,
											encounter_date,
											encounter_class_nr,
											referrer_diagnosis,
											referrer_dr,
											referrer_recom_therapy,
											referrer_notes,
											triage,
											admit_type,
											in_dept,
											current_dept_nr,
											status,
											insurance_firm_id,
											insurance_nr,
											modify_id,
											modify_time,
											create_id,
											create_time
										) VALUES (
											'" . $MyPIDRow['pid'] . "',
											NOW(),
											2,
											'" . $_POST['Diagnosis'] . "',
											'" . $_POST['ReferredBy'] . "',
											'" . $_POST['Therapy'] . "',
											'" . $_POST['ReferrerNotes'] . "',
											'" . $_POST['TriageCode'] . "',
											'" . $_POST['AdmissionType'] . "',
											1,
											'" . $_POST['Department'] . "',
											'in_dept',
											'" . $_POST['Insurance'] . "',
											'" . $_POST['InsuranceNo'] . "',
											'" . $_SESSION['UserID'] . "',
											NOW(),
											'" . $_SESSION['UserID'] . "',
											NOW()
										)";
		$ErrMsg = _('There was a problem inserting the encounter record because');
		$DbgMsg = _('The SQL used to insert the encounter record was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('The patient was successfully admitted.'), 'success');

		if (isset($_SESSION['OutpatientAdmissionsBillingItem']) and $_SESSION['OutpatientAdmissionsBillingItem'] != '') {

			$SQL = "INSERT INTO care_billable_items (`pid`,
													`stockid`,
													`price_list`,
													`create_id`,
													`create_time`
												) VALUES (
													'" . $MyPIDRow['pid'] . "',
													'" . $_SESSION['OutpatientAdmissionsBillingItem'] . "',
													'" . GetPriceListFromPID($MyPIDRow['pid']) . "',
													'" . $_SESSION['UserID'] . "',
													NOW()
											)";
			$ErrMsg = _('There was a problem inserting the billable items because');
			$DbgMsg = _('The SQL used to insert the billable items was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}

		unset($SelectedPatient);
		unset($_POST['Diagnosis']);
		unset($_POST['ReferredBy']);
		unset($_POST['Therapy']);
		unset($_POST['ReferrerNotes']);
		unset($_POST['TriageCode']);
		unset($_POST['AdmissionType']);
		unset($_POST['Department']);
		unset($_POST['Insurance']);
		unset($_POST['InsuranceNo']);

	} else {
		prnMsg(_('The patient is already admitted. They must be discharged before they can be re-admitted'), 'error');
	}

} elseif (isset($_POST['Update'])) {
	$SQL = "UPDATE care_encounter SET referrer_diagnosis='" . $_POST['Diagnosis'] . "',
											referrer_dr='" . $_POST['ReferredBy'] . "',
											referrer_recom_therapy='" . $_POST['Therapy'] . "',
											referrer_notes='" . $_POST['ReferrerNotes'] . "',
											triage='" . $_POST['TriageCode'] . "',
											admit_type='" . $_POST['AdmissionType'] . "',
											current_dept_nr='" . $_POST['Department'] . "',
											status='in_dept',
											insurance_firm_id='" . $_POST['Insurance'] . "',
											insurance_nr='" . $_POST['InsuranceNo'] . "',
											modify_id='" . $_SESSION['UserID'] . "',
											modify_time=NOW()
										WHERE pid='" . $SelectedPatient . "'";
	$ErrMsg = _('There was a problem updating the encounter record because');
	$DbgMsg = _('The SQL used to insert the encounter record was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	prnMsg(_('The patient data was successfully updated.'), 'success');

	if (isset($_SESSION['OutpatientAdmissionsBillingItem']) and $_SESSION['OutpatientAdmissionsBillingItem'] != '') {

		$SQL = "UPDATE care_billable_items SET `stockid`='" . $_SESSION['OutpatientAdmissionsBillingItem'] . "',
												`price_list`='" . GetPriceListFromPID($MyPIDRow['pid']) . "',
												`modify_id`='" . $_SESSION['UserID'] . "',
												`modify_time`=NOW()
											WHERE pid='" . $SelectedPatient . "'";
		$ErrMsg = _('There was a problem updating the billable items because');
		$DbgMsg = _('The SQL used to update the billable items was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	}

	unset($SelectedPatient);
	unset($_POST['Diagnosis']);
	unset($_POST['ReferredBy']);
	unset($_POST['Therapy']);
	unset($_POST['ReferrerNotes']);
	unset($_POST['TriageCode']);
	unset($_POST['AdmissionType']);
	unset($_POST['Department']);
	unset($_POST['Insurance']);
	unset($_POST['InsuranceNo']);

	echo '<div class="centre">
				<a href="', $RootPath, '/KCMCSelectPatient.php?SelectedPatient=', $SelectedPatient, '">', _('Return to Patient Screen'), '</a>
			</div>';

	include ('includes/footer.php');
	exit;
}

if (!isset($SelectedPatient)) {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', _('Search For Patient'), '
		</p>';

	echo '<form action="', $_SERVER['PHP_SELF'], '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (!isset($_POST['FileNumberSearch'])) {
		$_POST['FileNumberSearch'] = '';
	}
	if (!isset($_POST['NameSearch'])) {
		$_POST['NameSearch'] = '';
	}
	if (!isset($_POST['AddressSearch'])) {
		$_POST['AddressSearch'] = '';
	}
	if (!isset($_POST['PhoneSearch'])) {
		$_POST['PhoneSearch'] = '';
	}

	echo '<fieldset>
			<legend class="search">', _('Search for patient details'), '</legend>
			<field>
				<label for="FileNumberSearch">', _('File number'), '</label>
				<input type="search" autofocus="autofocus" name="FileNumberSearch" value="', $_POST['FileNumberSearch'], '" />
				<fieldhelp>', _('Enter all or part of the patients file number if it is known'), '</fieldhelp>
			</field>
			<field>
				<label for="NameSearch">', _('Patients name'), '</label>
				<input type="search" autofocus="autofocus" name="NameSearch" value="', $_POST['NameSearch'], '" />
				<fieldhelp>', _('Enter all or part of the patients name if it is known'), '</fieldhelp>
			</field>
			<field>
				<label for="AddressSearch">', _('Patients Address'), '</label>
				<input type="search" autofocus="autofocus" name="AddressSearch" size="50" value="', $_POST['AddressSearch'], '" />
				<fieldhelp>', _('Enter all or part of the patients address if it is known'), '</fieldhelp>
			</field>
			<field>
				<label for="PhoneSearch">', _('Patients Phone Number'), '</label>
				<input type="search" autofocus="autofocus" name="PhoneSearch" value="', $_POST['PhoneSearch'], '" />
				<fieldhelp>', _('Enter all or part of the patients phone number if it is known'), '</fieldhelp>
			</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Search" value="', _('Search'), '" />
		</div>
	</form>';

	if (isset($_POST['Search'])) {
		$SQL = "SELECT debtorsmaster.debtorno,
						name,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						custbranch.phoneno
					FROM debtorsmaster
					INNER JOIN care_person
						ON debtorsmaster.debtorno=care_person.pid
					INNER JOIN custbranch
						ON debtorsmaster.debtorno=custbranch.debtorno
						AND custbranch.branchcode='CASH'
					WHERE name LIKE '%" . $_POST['NameSearch'] . "%'
						AND debtorsmaster.debtorno LIKE '%" . $_POST['FileNumberSearch'] . "%'
						AND custbranch.phoneno LIKE '%" . $_POST['PhoneSearch'] . "%'
						AND CONCAT(address1, address2, address3, address4, address5, address6) LIKE '%" . $_POST['AddressSearch'] . "%'";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) > 0) {
			echo '<table>
					<tr>
						<th>', _('Patient Number'), '</th>
						<th>', _('Patient Name'), '</th>
						<th>', _('Address'), '</th>
						<th>', _('Phone Number'), '</th>
						<th></th>
					</tr>';

			while ($MyRow = DB_fetch_array($Result)) {
				$SQL = "SELECT pid FROM care_encounter WHERE pid='" . $MyRow['debtorno'] . "' AND is_discharged=1 AND (current_dept_nr<>0 OR current_ward_nr<>0)";
				$CheckResult = DB_query($SQL);
				if (DB_num_rows($CheckResult) == 0) {
					$Address = '';
					for ($i = 1;$i <= 6;$i++) {
						if ($MyRow['address' . $i] != '') {
							$Address.= $MyRow['address' . $i];
						}
					}
					echo '<tr class="striped_row">
							<td>', $MyRow['debtorno'], '</td>
							<td>', $MyRow['name'], '</td>
							<td>', $Address, '</td>
							<td>', $MyRow['phoneno'], '</td>
							<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedPatient=', urlencode($MyRow['debtorno']), '">', _('Admit Patient'), '</a></td>
						</tr>';
				}
			}

			echo '</table>';
		} else {
			prnMsg(_('There are no patients matching this criteria'), 'info');
		}
	}
	include ('includes/footer.php');
	exit;
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" title="', $Title, '" alt="" />', $Title, '
	</p>';

$SQL = "SELECT debtorsmaster.debtorno,
				care_person.title,
				name,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				custbranch.phoneno,
				care_person.blood_group,
				care_person.sex,
				care_person.date_birth
			FROM debtorsmaster
			INNER JOIN care_person
				ON debtorsmaster.debtorno=care_person.pid
			INNER JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				AND custbranch.branchcode='CASH'
			WHERE debtorsmaster.debtorno='" . $SelectedPatient . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

if (isset($_GET['Edit'])) {
	$SQL = "SELECT referrer_diagnosis,
					referrer_recom_therapy,
					referrer_dr,
					referrer_dept,
					referrer_notes,
					triage,
					insurance_firm_id,
					insurance_nr,
					admit_type
				FROM care_encounter
				WHERE pid='" . $SelectedPatient . "'";
	$EncounterResult = DB_query($SQL);
	$EncounterRow = DB_fetch_array($EncounterResult);
} else {
	$EncounterRow['referrer_diagnosis'] = '';
	$EncounterRow['referrer_recom_therapy'] = '';
	$EncounterRow['referrer_dr'] = '';
	$EncounterRow['referrer_dept'] = '';
	$EncounterRow['referrer_notes'] = '';
	$EncounterRow['insurance_nr'] = '';
}

echo '<form action="', $_SERVER['PHP_SELF'], '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<input type="hidden" name="SelectedPatient" value="', $SelectedPatient, '" />';
$Titles = array(1 => _('Mr'), 2 => _('Ms'), 3 => _('Miss'), 4 => _('Mrs'), 5 => _('Dr'));

$Address = $MyRow['address1'];
for ($i = 2;$i <= 6;$i++) {
	if ($MyRow['address' . $i] != '') {
		$Address.= ', ' . $MyRow['address' . $i];
	}
}

$Gender['m'] = _('Male');
$Gender['f'] = _('Female');

echo '<fieldset>
		<legend>', _('Admit'), ' ', $MyRow['name'], ' (', $SelectedPatient, ') ', _('as an outpatient'), '</legend>
		<field>
			<label for="AdmissionDate">', _('Admission Date'), ':</label>
			<div class="fieldtext">', Date($_SESSION['DefaultDateFormat']), '</div>
		</field>
		<field>
			<label for="AdmissionTime">', _('Admission Time'), ':</label>
			<div class="fieldtext">', Date('H:i'), '</div>
		</field>
		<field>
			<label for="Name">', _('Name'), ':</label>
			<div class="fieldtext">', $Titles[$MyRow['title']], '. ', $MyRow['name'], '</div>
		</field>
		<field>
			<label for="Address">', _('Address'), ':</label>
			<div class="fieldtext">', $Address, '</div>
		</field>
		<field>
			<label for="BloodGroup">', _('Blood Group'), ':</label>
			<div class="fieldtext">', $MyRow['blood_group'], '</div>
		</field>
		<field>
			<label for="Gender">', _('Gender'), ':</label>
			<div class="fieldtext">', $Gender[$MyRow['sex']], '</div>
		</field>
		</field>
		<field>
			<label for="DateOfBirth">', _('Date Of Birth'), ':</label>
			<div class="fieldtext">', ConvertSQLDate($MyRow['date_birth']), '</div>
		</field>';

$SQL = "SELECT type_nr, name FROM care_type_encounter WHERE status='0'";
$Result = DB_query($SQL);
echo '<field>
		<label for="AdmissionType">', _('Admission Type'), '</label>
		<select name="AdmissionType" autofocus="autofocus">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['AdmissionType']) and $_POST['AdmissionType'] == $MyRow['type_nr']) {
		echo '<option selected="selected" value="', $MyRow['type_nr'], '">', $MyRow['name'], '</option>';
	} else {
		echo '<option value="', $MyRow['type_nr'], '">', $MyRow['name'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('A type of admission.'), '</fieldhelp>
</field>';

$TriageCodes = array('white' => _('White'), 'green' => _('Green'), 'yellow' => _('Yellow'), 'red' => _('Red'));
echo '<field>
		<label for="TriageCode">', _('Triage Code'), ':</label>
		<select name="TriageCode">';
foreach ($TriageCodes as $Key => $Value) {
	if ($Key == $EncounterRow['triage']) {
		echo '<option selected="selected" value="', $Key, '">', $Value, '</option>';
	} else {
		echo '<option value="', $Key, '">', $Value, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('The triage code for this patient, white being the least serious, and red being the most serious.'), '</fieldhelp>
</field>';

$SQL = "SELECT departmentid, description FROM departments WHERE medical=1";
$Result = DB_query($SQL);
echo '<field>
		<label for="Department">', _('Department'), '</label>
		<select name="Department">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Department']) and $_POST['Department'] == $MyRow['departmentid']) {
		echo '<option selected="selected" value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
	} else {
		echo '<option value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('The department that this patient is being admitted to.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="Diagnosis">', _('Referrer Diagnosis'), ':</label>
		<input type="text" name="Diagnosis" size="100" value="', $EncounterRow['referrer_diagnosis'], '" />
	</field>';

echo '<field>
		<label for="ReferredBy">', _('Referred By'), ':</label>
		<input type="text" name="ReferredBy" size="100" value="', $EncounterRow['referrer_dr'], '" />
	</field>';

echo '<field>
		<label for="Therapy">', _('Referrer Therapy'), ':</label>
		<input type="text" name="Therapy" size="100" value="', $EncounterRow['referrer_recom_therapy'], '" />
	</field>';

echo '<field>
		<label for="ReferrerNotes">', _('Referrer Notes'), ':</label>
		<input type="text" name="ReferrerNotes" size="100" value="', $EncounterRow['referrer_notes'], '" />
	</field>';

$SQL = "SELECT debtorno,
				name
			FROM debtorsmaster
			WHERE typeid='" . $_SESSION['InsuranceDebtorType'] . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<field>
			<label for="Insurance">', _('Insurance Company'), ':</label>
			<select name="Insurance">';
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($EncounterRow['insurance_firm_id'] == $MyRow['debtorno']) {
			echo '<option selected="selected" value="', $MyRow['debtorno'], '">', $MyRow['name'], '</option>';
		} else {
			echo '<option value="', $MyRow['debtorno'], '">', $MyRow['name'], '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('The insurance company, if any, that this patient belongs to'), '</fieldhelp>
	</field>';
}

echo '<field>
		<label for="InsuranceNo">', _('Insurance Number'), ':</label>
		<input type="text" name="InsuranceNo" size="20" value="', $EncounterRow['insurance_nr'], '" />
	</field>';

echo '</fieldset>';

if (isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Update" value="', ('Update Admission data'), '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Create" value="', ('Admit the patient'), '" />
		</div>';
}

include ('includes/footer.php');
?>