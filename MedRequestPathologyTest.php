<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Pathology Laboratory Test Request');
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

	$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
	$PhoneNo = $MyRow['phone_1_nr'];
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Pathology Laboratory Test Request'), '" /> ', _('Pathology Laboratory Test Request'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $SelectedPatient, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Pathology Laboratory Test Request'), '" /> ', _('Pathology Laboratory Test Request'), '
		</p>';
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

	if (isset($_POST['ExpressCut'])) {
		$ExpressCut = 1;
	} else {
		$ExpressCut = 0;
	}

	if (isset($_POST['UrgentTest'])) {
		$UrgentTest = 1;
	} else {
		$UrgentTest = 0;
	}

	$History = 'Create: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'];

	$InsertSQL = "INSERT INTO care_test_request_patho (`batch_nr`,
														`encounter_nr`,
														`dept_nr`,
														`quick_cut`,
														`qc_phone`,
														`quick_diagnosis`,
														`qd_phone`,
														`material_type`,
														`material_desc`,
														`localization`,
														`clinical_note`,
														`extra_note`,
														`repeat_note`,
														`gyn_last_period`,
														`gyn_period_type`,
														`gyn_gravida`,
														`gyn_menopause_since`,
														`gyn_hysterectomy`,
														`gyn_contraceptive`,
														`gyn_iud`,
														`gyn_hormone_therapy`,
														`doctor_sign`,
														`op_date`,
														`send_date`,
														`status`,
														`entry_date`,
														`journal_nr`,
														`blocks_nr`,
														`deep_cuts`,
														`special_dye`,
														`immune_histochem`,
														`hormone_receptors`,
														`specials`,
														`history`,
														`modify_id`,
														`modify_time`,
														`create_id`,
														`create_time`,
														`process_id`,
														`process_time`
													) VALUES (
														NULL,
														'" . $Encounter . "',
														'" . $DeptNumber . "',
														'" . $ExpressCut . "',
														'" . $_POST['ExpressCutTel'] . "',
														'" . $UrgentTest . "',
														'" . $_POST['UrgentTestTel'] . "',
														'" . $_POST['SpecimenType'] . "',
														'" . $_POST['SpecimenComments'] . "',
														'" . $_POST['Localisation'] . "',
														'" . $_POST['Diagnosis'] . "',
														'" . $_POST['SupportingInfo'] . "',
														'" . $_POST['RepeatedTest'] . "',
														'" . $_POST['LastPeriod'] . "',
														'" . $_POST['PeriodType'] . "',
														'" . $_POST['Gravida'] . "',
														'" . $_POST['Menopause'] . "',
														'" . $_POST['Hysterectomy'] . "',
														'" . $_POST['Contraceptive'] . "',
														'" . $_POST['IUD'] . "',
														'" . $_POST['HormonalTherapy'] . "',
														'" . $_POST['Doctor'] . "',
														'" . FormatDateForSQL($_POST['OperationDate']) . "',
														NOW(),
														'pending',
														NOW(),
														'',
														0,
														0,
														'',
														'',
														'',
														'',
														'" . $History . "',
														'" . $_SESSION['UserID'] . "',
														NOW(),
														'" . $_SESSION['UserID'] . "',
														NOW(),
														'" . $_SESSION['UserID'] . "',
														NOW()
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
				<a href="KCMCPathologyLaboratory.php">', _('Return to Pathology Laboratory'), '</a>
			</div>';
		include ('includes/footer.php');
		exit;
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
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedPatient=', $MyRow['pid'], '&SampleDate=', $_POST['SampleDate'], '&identifier=', $Identifier, '&Notes=', $_POST['Notes'], '">', _('Select'), '</a></td>
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

	echo '<fieldset>
			<legend>', _('Request diagnostic test - Pathology'), '</legend>';

	echo '<fieldset style="width:auto">';
	echo '<field>
			<label for="ExpressCut">', _('Express Cut'), '</label>
			<input type="checkbox" name="ExpressCut" />  ', _('Telephone to notify'), '   <input type="tel" name="ExpressCutTel" value="" />
		</field>';

	echo '<field>
			<label for="UrgentTest">', _('Urgent Test'), '</label>
			<input type="checkbox" name="UrgentTest" />  ', _('Telephone to notify'), '   <input type="tel" name="UrgentTestTel" value="" />
		</field>';
	echo '</fieldset><br />';

	echo '<fieldset style="width:auto">
			<legend>', _('Specimen Type'), '</legend>';
	echo '<field>
			<input type="radio" name="SpecimenType" value="Biopsy" />', _('Biopsy, sample excision'), '
			<input type="radio" name="SpecimenType" value="OpSpec" />', _('Operation Specimen'), '
			<input type="radio" name="SpecimenType" value="Curettage" />', _('Curettage'), '
			<input type="radio" name="SpecimenType" value="Cytology" />', _('Cytology'), '<br /><br />
			<textarea name="SpecimenComments" cols="46" rows="8" wrap="physical"></textarea>
		</field>';
	echo '</fieldset>';

	echo '<field>
			<label for="Localisation">', _('Localisation'), '</label>
			<textarea name="Localisation" cols="46" rows="2" wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="Diagnosis">', _('Clinical Diagnoses, Reports, Queries'), '</label>
			<textarea name="Diagnosis" cols="46" rows="2" wrap="physical"></textarea>
		</field>';

	echo '<field>
			<label for="SupportingInfo">', _('Supporting information'), '</label>
			<textarea name="SupportingInfo" cols="46" rows="2" wrap="physical"></textarea>
			<fieldhelp>', _('e.g. important Lab results, after Radiation in excision area, after Chemotherapy'), '</fieldhelp>
		</field>';

	echo '<field>
			<label for="RepeatedTest">', _('Repeated Test'), '</label>
			<input type="text" size="50" name="RepeatedTest" cols="46" value="" />
			<fieldhelp>', _('For repeated test: please enter the early Journal number with date'), '</fieldhelp>
		</field>';

	echo '<fieldset style="width:auto">
			<legend>', _('For gynecological tests'), '</legend>';

	echo '<field>
			<label for="LastPeriod">', _('Last Period'), '</label>
			<input type="text" size="20" name="LastPeriod" value="" />
		</field>';

	echo '<field>
			<label for="PeriodType">', _('Period Type'), '</label>
			<input type="text" size="20" name="PeriodType" value="" />
		</field>';

	echo '<field>
			<label for="Gravida">', _('Gravida'), '</label>
			<input type="text" size="20" name="Gravida" value="" />
		</field>';

	echo '<field>
			<label for="Menopause">', _('Menopause since'), '</label>
			<input type="text" size="20" name="Menopause" value="" />
		</field>';

	echo '<field>
			<label for="Hysterectomy">', _('Hysterectomy'), '</label>
			<input type="text" size="20" name="Hysterectomy" value="" />
		</field>';

	echo '<field>
			<label for="Contraceptive">', _('Contraceptive'), '</label>
			<input type="text" size="20" name="Contraceptive" value="" />
		</field>';

	echo '<field>
			<label for="HormonalTherapy">', _('Hormonal therapy'), '</label>
			<input type="text" size="20" name="HormonalTherapy" value="" />
		</field>';

	echo '<field>
			<label for="IUD">', _('IUD'), '</label>
			<input type="text" size="20" name="IUD" value="" />
		</field>';

	echo '</fieldset>';

	echo '<field>
			<label for="OperationDate">', _('Date of operation'), '</label>
			<input type="text" class="date" size="20" name="OperationDate" value="', date($_SESSION['DefaultDateFormat']), '" />
		</field>';

	echo '<field>
			<label for="Doctor">', _('Doctor/Department'), '</label>
			<input type="text" size="30" name="Doctor" value="" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SendRequest" value="', _('Send Request'), '" />
		</div>';

	echo '</form>';
} else {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="Notes" value="', $_POST['Notes'], '" />';
	echo '<input type="hidden" name="SampleDate" value="', $_POST['SampleDate'], '" />';

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