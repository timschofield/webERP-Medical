<?php
include ('includes/session.php');
$Title = _('View Pathology Tests');
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
			<a href="', $RootPath, '/KCMCPendingPathologyTests.php>', _('Select a batch to view'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($SelectedBatch)) {
	$SQL = "SELECT encounter_nr
			FROM care_test_request_patho
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
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
	$PhoneNo = $MyRow['phone_1_nr'];
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('View Pathology Test Batch'), '" /> ', _('View Pathology Test Batch'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $PID, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('View Pathology Test Batch'), '" /> ', _('View Pathology Test Batch'), '
		</p>';
}

if (isset($_POST['SendRequest'])) {
	$Encounter = GetEncounterFromPID($PID);

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

	$HistorySQL = "SELECT history FROM care_test_request_patho WHERE batch_nr='" . $SelectedBatch . "'";
	$HistoryResult = DB_query($HistorySQL);
	$HistoryRow = DB_fetch_array($HistoryResult);

	$History = $HistoryRow['history'] . "\n" . 'Updated: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'];

	$UpdateSQL = "UPDATE  care_test_request_patho SET `encounter_nr`='" . $Encounter . "',
														`dept_nr`='" . $DeptNumber . "',
														`quick_cut`='" . $ExpressCut . "',
														`qc_phone`='" . $_POST['ExpressCutTel'] . "',
														`quick_diagnosis`='" . $UrgentTest . "',
														`qd_phone`='" . $_POST['UrgentTestTel'] . "',
														`material_type`='" . $_POST['SpecimenType'] . "',
														`material_desc`='" . $_POST['SpecimenComments'] . "',
														`localization`='" . $_POST['Localisation'] . "',
														`clinical_note`='" . $_POST['Diagnosis'] . "',
														`extra_note`='" . $_POST['SupportingInfo'] . "',
														`repeat_note`='" . $_POST['RepeatedTest'] . "',
														`gyn_last_period`='" . $_POST['LastPeriod'] . "',
														`gyn_period_type`='" . $_POST['PeriodType'] . "',
														`gyn_gravida`='" . $_POST['Gravida'] . "',
														`gyn_menopause_since`='" . $_POST['Menopause'] . "',
														`gyn_hysterectomy`='" . $_POST['Hysterectomy'] . "',
														`gyn_contraceptive`='" . $_POST['Contraceptive'] . "',
														`gyn_iud`='" . $_POST['IUD'] . "',
														`gyn_hormone_therapy`='" . $_POST['HormonalTherapy'] . "',
														`doctor_sign`='" . $_POST['Doctor'] . "',
														`op_date`='" . FormatDateForSQL($_POST['OperationDate']) . "',
														`entry_date`='" . FormatDateForSQL($_POST['EntryDate']) . "',
														`journal_nr`='" . $_POST['JournalNumber'] . "',
														`blocks_nr`='" . $_POST['NumberOfBlocks'] . "',
														`deep_cuts`='" . $_POST['DeepBlocks'] . "',
														`special_dye`='" . $_POST['SpecialStain'] . "',
														`immune_histochem`='" . $_POST['HistoChemistry'] . "',
														`hormone_receptors`='" . $_POST['HormoneReceptors'] . "',
														`specials`='" . $_POST['Specials'] . "',
														`history`='" . $History . "',
														`modify_id`='" . $_SESSION['UserID'] . "'
													WHERE batch_nr='" . $SelectedBatch . "'";
	$UpdateResult = DB_query($UpdateSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('Failed to update the test request to the database'), 'error');
		include ('includes/footer.php');
		exit;
	} else {
		prnMsg(_('The batch of tests has been updated successfully'), 'success');
		unset($SelectedBatch);
		echo '<div class="centre">
				<a href="KCMCPathologyLaboratory.php">', _('Return to Pathology Laboratory'), '</a>
			</div>';
		include ('includes/footer.php');
		exit;
	}
}

if (isset($SelectedBatch)) {

	$SQL = "SELECT `encounter_nr`,
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
			FROM care_test_request_patho
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

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="Batch" value="', $SelectedBatch, '" />';

	echo '<fieldset>
			<legend>', _('Pending diagnostic test - Pathology'), '</legend>';

	echo '<fieldset style="width:auto">
			<legend>', _('Pathology'), '</legend>';

	echo '<field>
			<label for="EntryDate">', _('Entry Date'), '</label>
			<input type="text" size="10" name="EntryDate" class="date" value="', ConvertSQLDate($MyRow['entry_date']), '" />
		</field>';

	echo '<field>
			<label for="JournalNumber">', _('Journal Number'), '</label>
			<input type="text" name="JournalNumber" value="', $MyRow['journal_nr'], '" />
		</field>';

	echo '<field>
			<label for="NumberOfBlocks">', _('Number of Blocks'), '</label>
			<input type="text" class="number" name="NumberOfBlocks" value="', $MyRow['blocks_nr'], '" />
		</field>';

	echo '<field>
			<label for="DeepBlocks">', _('Deep Blocks'), '</label>
			<input type="text" class="number" name="DeepBlocks" value="', $MyRow['deep_cuts'], '" />
		</field>';

	echo '<field>
			<label for="SpecialStain">', _('Special Stain'), '</label>
			<input type="text" name="SpecialStain" value="', $MyRow['special_dye'], '" />
		</field>';

	echo '<field>
			<label for="HistoChemistry">', _('Immune Histo-Chemistry'), '</label>
			<input type="text" name="HistoChemistry" value="', $MyRow['immune_histochem'], '" />
		</field>';

	echo '<field>
			<label for="HormoneReceptors">', _('Hormone Receptors'), '</label>
			<input type="text" name="HormoneReceptors" value="', $MyRow['hormone_receptors'], '" />
		</field>';

	echo '<field>
			<label for="Specials">', _('Specials'), '</label>
			<input type="text" name="Specials" value="', $MyRow['specials'], '" />
		</field>';

	echo '</fieldset><br />';

	if ($MyRow['quick_cut'] == 1) {
		$ExpressCut = ' checked="checked" ';
	} else {
		$ExpressCut = ' ';
	}

	echo '<fieldset style="width:auto">';
	echo '<field>
			<label for="ExpressCut">', _('Express Cut'), '</label>
			<input type="checkbox" ', $ExpressCut, ' name="ExpressCut" onclick="return false;" />  ', _('Telephone to notify'), '   <input type="tel" name="ExpressCutTel" readonly="readonly" value="', $MyRow['qc_phone'], '" />
		</field>';

	if ($MyRow['quick_diagnosis'] == 1) {
		$UrgentTest = ' checked="checked" ';
	} else {
		$UrgentTest = ' ';
	}

	echo '<field>
			<label for="UrgentTest">', _('Urgent Test'), '</label>
			<input type="checkbox" ', $UrgentTest, ' name="UrgentTest" onclick="return false;" />  ', _('Telephone to notify'), '   <input type="tel" name="UrgentTestTel" readonly="readonly" value="', $MyRow['qd_phone'], '" />
		</field>';
	echo '</fieldset><br />';

	echo '<fieldset style="width:auto">
			<legend>', _('Specimen Type'), '</legend>';
	$Biopsy = '';
	if ($MyRow['material_type'] == 'Biopsy') {
		$Biopsy = ' checked="checked" ';
	}
	$OpSpec = '';
	if ($MyRow['material_type'] == 'OpSpec') {
		$OpSpec = ' checked="checked" ';
	}
	$Curettage = '';
	if ($MyRow['material_type'] == 'Curettage') {
		$Curettage = ' checked="checked" ';
	}
	$Cytology = '';
	if ($MyRow['material_type'] == 'Cytology') {
		$Cytology = ' checked="checked" ';
	}
	echo '<field>
			<input type="radio" name="SpecimenType" onclick="return false;"', $Biopsy, ' value="Biopsy" />', _('Biopsy, sample excision'), '
			<input type="radio" name="SpecimenType" onclick="return false;"', $OpSpec, ' value="OpSpec" />', _('Operation Specimen'), '
			<input type="radio" name="SpecimenType" onclick="return false;"', $Curettage, ' value="Curettage" />', _('Curettage'), '
			<input type="radio" name="SpecimenType" onclick="return false;"', $Cytology, ' value="Cytology" />', _('Cytology'), '<br /><br />
			<textarea name="SpecimenComments" readonly="readonly" cols="46" rows="8" wrap="physical">', $MyRow['material_desc'], '</textarea>
		</field>';
	echo '</fieldset>';

	echo '<field>
			<label for="Localisation">', _('Localisation'), '</label>
			<textarea name="Localisation" readonly="readonly" cols="46" rows="2" wrap="physical">', $MyRow['localization'], '</textarea>
		</field>';

	echo '<field>
			<label for="Diagnosis">', _('Clinical Diagnoses, Reports, Queries'), '</label>
			<textarea name="Diagnosis" readonly="readonly" cols="46" rows="2" wrap="physical">', $MyRow['clinical_note'], '</textarea>
		</field>';

	echo '<field>
			<label for="SupportingInfo">', _('Supporting information'), '</label>
			<textarea name="SupportingInfo" readonly="readonly" cols="46" rows="2" wrap="physical">', $MyRow['extra_note'], '</textarea>
			<fieldhelp>', _('e.g. important Lab results, after Radiation in excision area, after Chemotherapy'), '</fieldhelp>
		</field>';

	echo '<field>
			<label for="RepeatedTest">', _('Repeated Test'), '</label>
			<input type="text" size="50" name="RepeatedTest" readonly="readonly" cols="46" value="', $MyRow['repeat_note'], '" />
			<fieldhelp>', _('For repeated test: please enter the early Journal number with date'), '</fieldhelp>
		</field>';

	echo '<fieldset style="width:auto">
			<legend>', _('For gynecological tests'), '</legend>';

	echo '<field>
			<label for="LastPeriod">', _('Last Period'), '</label>
			<input type="text" size="20" name="LastPeriod" readonly="readonly" value="', $MyRow['gyn_last_period'], '" />
		</field>';

	echo '<field>
			<label for="PeriodType">', _('Period Type'), '</label>
			<input type="text" size="20" name="PeriodType" readonly="readonly" value="', $MyRow['gyn_period_type'], '" />
		</field>';

	echo '<field>
			<label for="Gravida">', _('Gravida'), '</label>
			<input type="text" size="20" name="Gravida" readonly="readonly" value="', $MyRow['gyn_gravida'], '" />
		</field>';

	echo '<field>
			<label for="Menopause">', _('Menopause since'), '</label>
			<input type="text" size="20" name="Menopause" readonly="readonly" value="', $MyRow['gyn_menopause_since'], '" />
		</field>';

	echo '<field>
			<label for="Hysterectomy">', _('Hysterectomy'), '</label>
			<input type="text" size="20" name="Hysterectomy" readonly="readonly" value="', $MyRow['gyn_hysterectomy'], '" />
		</field>';

	echo '<field>
			<label for="Contraceptive">', _('Contraceptive'), '</label>
			<input type="text" size="20" name="Contraceptive" readonly="readonly" value="', $MyRow['gyn_contraceptive'], '" />
		</field>';

	echo '<field>
			<label for="HormonalTherapy">', _('Hormonal therapy'), '</label>
			<input type="text" size="20" name="HormonalTherapy" readonly="readonly" value="', $MyRow['gyn_hormone_therapy'], '" />
		</field>';

	echo '<field>
			<label for="IUD">', _('IUD'), '</label>
			<input type="text" size="20" name="IUD" readonly="readonly" value="', $MyRow['gyn_iud'], '" />
		</field>';

	echo '</fieldset>';

	echo '<field>
			<label for="OperationDate">', _('Date of operation'), '</label>
			<input type="text" class="date" size="20" name="OperationDate" readonly="readonly" value="', ConvertSQLDate($MyRow['op_date']), '" />
		</field>';

	echo '<field>
			<label for="Doctor">', _('Doctor/Department'), '</label>
			<input type="text" size="30" name="Doctor" readonly="readonly" value="', $MyRow['doctor_sign'], '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SendRequest" value="', _('Save Details'), '" />
		</div>';

	echo '</form>';
}

echo '<div class="centre">
		<a href="', $RootPath, '/KCMCPathologyLaboratory.php">', _('Return to Pathology Centre'), '</a>
	</div>';

include ('includes/footer.php');

?>