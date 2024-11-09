<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Bacteriology Laboratory Test Request');
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

if (isset($_GET['SelectedBatch'])) {
	$HeaderSQL = "SELECT `encounter_nr`,
						`dept_nr`,
						`material_note`,
						`diagnosis_note`,
						`immune_supp`,
						`sample_date`,
						`history`
					FROM care_test_request_baclabor
					WHERE batch_nr='" . $_GET['SelectedBatch'] . "'";
	$HeaderResult = DB_query($HeaderSQL);
	$HeaderRow = DB_fetch_array($HeaderResult);

	$Encounter = $HeaderRow['encounter_nr'];
	$SelectedPatient = GetPIDFromEncounter($Encounter);

	$Department = $HeaderRow['dept_nr'];
	$MaterialNote = $HeaderRow['material_note'];
	$DiagnosisNote = $HeaderRow['diagnosis_note'];
	$ImmuneSupp = $HeaderRow['immune_supp'];
	$SampleDate = $HeaderRow['sample_date'];

	$History = $HeaderRow['history'];

	$LinesSQL = "SELECT sub_id,
						encounter_nr,
						test_type,
						test_type_value,
						material,
						material_value
					FROM care_test_request_baclabor_sub
					WHERE batch_nr='" . $_GET['SelectedBatch'] . "'";
	$LinesResult = DB_query($LinesSQL);
	while ($LinesRow = DB_fetch_array($LinesResult)) {
		if ($LinesRow['test_type_value'] != 0) {
			$TestArray[] = $LinesRow['test_type'];
		} elseif ($LinesRow['material_value'] != 0) {
			$MaterialArray[] = $LinesRow['material'];
		}
	}
} else {
	$Department = 0;
	$MaterialNote = '';
	$DiagnosisNote = '';
	$SampleDate = date('Y-m-d');;

	$MaterialArray = array();
	$TestArray = array();
	$ImmuneSupp = 0;
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
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Bacteriology Laboratory Test Request'), '" /> ', _('Bacteriology Laboratory Test Request'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $SelectedPatient, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Bacteriology Laboratory Test Request'), '" /> ', _('Bacteriology Laboratory Test Request'), '
		</p>';
}

if (isset($_POST['UpdateRequest'])) {

	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 8) == 'material') {
			$MaterialArray[] = mb_substr($Key, 8);
		}
		if (mb_substr($Key, 0, 4) == 'test') {
			$TestArray[] = mb_substr($Key, 4);
		}
	}

	$Encounter = GetEncounterFromPID($SelectedPatient);

	$SQL = "SELECT current_ward_nr,
					current_dept_nr
				FROM care_encounter
				WHERE encounter_nr='" . $Encounter . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$DeptNumber = $MyRow['current_dept_nr'];

	$HistorySQL = "SELECT history FROM care_test_request_baclabor WHERE batch_nr='" . $_POST['SelectedBatch'] . "'";
	$HistoryResult = DB_query($HistorySQL);
	$HistoryRow = DB_fetch_array($HistoryResult);

	$History = $HistoryRow['history'] . "\n" . 'Updated: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'];

	$BatchSQL = "UPDATE care_test_request_baclabor SET `encounter_nr`='" . $Encounter . "',
														`dept_nr`='" . $DeptNumber . "',
														`material`='" . implode(',', $MaterialArray) . "',
														`test_type`='" . implode(',', $TestArray) . "',
														`material_note`='" . $_POST['Material'] . "',
														`diagnosis_note`='" . $_POST['Diagnosis'] . "',
														`immune_supp`='" . $_POST['ImmuneSupp'] . "',
														`send_date`=NOW(),
														`sample_date`='" . FormatDateForSQL($_POST['SampleDate']) . "',
														`history`='" . $History . "',
														`modify_id`='" . $_SESSION['UserID'] . "'
													WHERE batch_nr='" . $_POST['SelectedBatch'] . "'";
	$BatchResult = DB_query($BatchSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('Failed to update the batch header into the database'), 'error');
		include ('includes/footer.php');
		exit;
	}

	$RemoveOldLinesSQL = "DELETE FROM care_test_request_baclabor_sub WHERE batch_nr='" . $_POST['SelectedBatch'] . "'";
	$RemoveOldLinesResult = DB_query($RemoveOldLinesSQL);

	foreach ($MaterialArray as $MaterialNumber => $Material) {
		$LineSQL = "INSERT INTO care_test_request_baclabor_sub (`sub_id`,
																`batch_nr`,
																`encounter_nr`,
																`test_type`,
																`test_type_value`,
																`material`,
																`material_value`
															) VALUES (
																NULL,
																'" . $_POST['SelectedBatch'] . "',
																'" . $Encounter . "',
																'0',
																'0',
																'" . $Material . "',
																'1'
															)";
		$LineResult = DB_query($LineSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to update the batch line into the database'), 'error');
			include ('includes/footer.php');
			exit;
		}
	}

	foreach ($TestArray as $TestNumber => $Test) {
		$LineSQL = "INSERT INTO care_test_request_baclabor_sub (`sub_id`,
																`batch_nr`,
																`encounter_nr`,
																`test_type`,
																`test_type_value`,
																`material`,
																`material_value`
															) VALUES (
																NULL,
																'" . $_POST['SelectedBatch'] . "',
																'" . $Encounter . "',
																'" . $Test . "',
																'1',
																'0',
																'0'
															)";
		$LineResult = DB_query($LineSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to update the batch line into the database'), 'error');
			include ('includes/footer.php');
			exit;
		}
		if ($_SESSION['BillForBacteriologyTest'] == 1) {

			$StockSQL = "SELECT stockid FROM care_baclabor_test_type WHERE type='" . $Test . "'";
			$StockResult = DB_query($StockSQL);
			$StockRow = DB_fetch_array($StockResult);

			$PriceListSQL = "SELECT salestype FROM debtorsmaster WHERE debtorno='" . $PID . "'";
			$PriceListResult = DB_query($PriceListSQL);
			$PriceListRow = DB_fetch_array($PriceListResult);

			$SQL = "INSERT INTO care_billable_items (`pid`,
													`stockid`,
													`price_list`,
													`create_id`,
													`create_time`
												) VALUES (
													'" . $SelectedPatient . "',
													'" . $StockRow['stockid'] . "',
													'" . $PriceListRow['salestype'] . "',
													'" . $_SESSION['UserID'] . "',
													NOW()
											)";
			$ErrMsg = _('There was a problem inserting the billable items because');
			$DbgMsg = _('The SQL used to insert the billable items was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
	}
	prnMsg(_('The batch of tests has been updated successfully'), 'success');

	echo '<div class="centre">
			<a href="', $RootPath, '/MedViewBacteriologyTest.php?SelectedBatch=', $_POST['SelectedBatch'], '">', _('Print the request form'), '</a><br />
			<a href="', $RootPath, '/MedRequestBacteriologyTest.php?Edit=Yes&SelectedBatch=', $_POST['SelectedBatch'], '">', _('Edit the same test request'), '</a><br />
			<a href="', $RootPath, '/MedRequestBacteriologyTest.php?SelectedPatient=', $SelectedPatient, '">', _('Create a new test request for the same patient'), '</a><br />
			<a href="', $RootPath, '/MedRequestBacteriologyTest.php">', _('Create a test request for another patient'), '</a>
		</div>';

	unset($_SESSION['TestRequest' . $Identifier]);
	unset($SelectedPatient);

	include ('includes/footer.php');
	exit;
}

if (isset($_POST['SendRequest'])) {

	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 8) == 'material') {
			$MaterialArray[] = mb_substr($Key, 8);
		}
		if (mb_substr($Key, 0, 4) == 'test') {
			$TestArray[] = mb_substr($Key, 4);
		}
	}

	$Encounter = GetEncounterFromPID($SelectedPatient);

	$SQL = "SELECT current_ward_nr,
					current_dept_nr
				FROM care_encounter
				WHERE encounter_nr='" . $Encounter . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$DeptNumber = $MyRow['current_dept_nr'];

	$History = 'Create: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'];

	$BatchSQL = "INSERT INTO care_test_request_baclabor (`batch_nr`,
														`encounter_nr`,
														`dept_nr`,
														`material`,
														`test_type`,
														`material_note`,
														`diagnosis_note`,
														`immune_supp`,
														`send_date`,
														`sample_date`,
														`status`,
														`history`,
														`modify_id`,
														`create_id`,
														`create_time`
													) VALUES (
														NULL,
														'" . $Encounter . "',
														'" . $DeptNumber . "',
														'" . implode(',', $MaterialArray) . "',
														'" . implode(',', $TestArray) . "',
														'" . $_POST['Material'] . "',
														'" . $_POST['Diagnosis'] . "',
														'" . $_POST['ImmuneSupp'] . "',
														NOW(),
														'" . FormatDateForSQL($_POST['SampleDate']) . "',
														'pending',
														'" . $History . "',
														'" . $_SESSION['UserID'] . "',
														'" . $_SESSION['UserID'] . "',
														NOW()
													)";
	$BatchResult = DB_query($BatchSQL);
	if (DB_error_no() > 0) {
		prnMsg(_('Failed to insert the batch header into the database'), 'error');
		include ('includes/footer.php');
		exit;
	}
	$BatchNumber = DB_Last_Insert_ID('care_test_request_baclabor', 'batch_nr');

	foreach ($MaterialArray as $MaterialNumber => $Material) {
		$LineSQL = "INSERT INTO care_test_request_baclabor_sub (`sub_id`,
																`batch_nr`,
																`encounter_nr`,
																`test_type`,
																`test_type_value`,
																`material`,
																`material_value`
															) VALUES (
																NULL,
																'" . $BatchNumber . "',
																'" . $Encounter . "',
																'0',
																'0',
																'" . $Material . "',
																'1'
															)";
		$LineResult = DB_query($LineSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to insert the batch line into the database'), 'error');
			include ('includes/footer.php');
			exit;
		}
	}

	foreach ($TestArray as $TestNumber => $Test) {
		$LineSQL = "INSERT INTO care_test_request_baclabor_sub (`sub_id`,
																`batch_nr`,
																`encounter_nr`,
																`test_type`,
																`test_type_value`,
																`material`,
																`material_value`
															) VALUES (
																NULL,
																'" . $BatchNumber . "',
																'" . $Encounter . "',
																'" . $Test . "',
																'1',
																'0',
																'0'
															)";
		$LineResult = DB_query($LineSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to insert the batch line into the database'), 'error');
			include ('includes/footer.php');
			exit;
		}
		if ($_SESSION['BillForBacteriologyTest'] == 1) {

			$StockSQL = "SELECT stockid FROM care_baclabor_test_type WHERE type='" . $Test . "'";
			$StockResult = DB_query($StockSQL);
			$StockRow = DB_fetch_array($StockResult);

			$PriceListSQL = "SELECT salestype FROM debtorsmaster WHERE debtorno='" . $SelectedPatient . "'";
			$PriceListResult = DB_query($PriceListSQL);
			$PriceListRow = DB_fetch_array($PriceListResult);

			$SQL = "INSERT INTO care_billable_items (`pid`,
													`stockid`,
													`price_list`,
													`create_id`,
													`create_time`
												) VALUES (
													'" . $SelectedPatient . "',
													'" . $StockRow['stockid'] . "',
													'" . $PriceListRow['salestype'] . "',
													'" . $_SESSION['UserID'] . "',
													NOW()
											)";
			$ErrMsg = _('There was a problem inserting the billable items because');
			$DbgMsg = _('The SQL used to insert the billable items was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
	}
	prnMsg(_('The batch of tests has been sent successfully'), 'success');

	echo '<div class="centre">
			<a href="', $RootPath, '/MedViewBacteriologyTest.php?SelectedBatch=', $SelectedBatch, '">', _('Print the request form'), '</a><br />
			<a href="', $RootPath, '/MedRequestBacteriologyTest.php?Edit=Yes&SelectedBatch=', $BatchNumber, '">', _('Edit the same test request'), '</a><br />
			<a href="', $RootPath, '/MedRequestBacteriologyTest.php?SelectedPatient=', $SelectedPatient, '">', _('Create a new test request for the same patient'), '</a><br />
			<a href="', $RootPath, '/MedRequestBacteriologyTest.php">', _('Create a test request for another patient'), '</a>
		</div>';

	unset($_SESSION['TestRequest' . $Identifier]);
	unset($SelectedPatient);

	include ('includes/footer.php');
	exit;

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
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedPatient=', $MyRow['pid'], '&identifier=', $Identifier, '">', _('Select'), '</a></td>
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

	$SQL = "SELECT type, name FROM care_baclabor_material_type";
	$Result = DB_query($SQL);

	echo '<fieldset>
			<legend>', _('Request diagnostic test - Bacteriology'), '</legend>';

	echo '<fieldset style="width:auto; margin-right:5px;">
			<legend>', _('Material'), '</legend>';

	while ($MyRow = DB_fetch_array($Result)) {
		if (in_array($MyRow['type'], $MaterialArray)) {
			$Checked = ' checked="checked" ';
		} else {
			$Checked = '';
		}
		echo '<field>
				<label class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'material', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox" ', $Checked, ' id="material', $MyRow['type'], '" name="material', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>
			</field>';
	}

	echo '</fieldset>';

	$SQL = "SELECT type, name FROM care_baclabor_test_type";
	$Result = DB_query($SQL);

	echo '<fieldset style="width:auto">
			<legend>', _('Requested Test'), '</legend>';

	while ($MyRow = DB_fetch_array($Result)) {
		if (in_array($MyRow['type'], $TestArray)) {
			$Checked = ' checked="checked" ';
		} else {
			$Checked = '';
		}
		echo '<field>
				<label class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'test', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox" ', $Checked, ' id="test', $MyRow['type'], '" name="test', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>
			</field>';
	}

	echo '</fieldset>';

	echo '<field>
			<label for="Material">', _('Material'), '</label>
			<input type="text" name="Material" value="', $MaterialNote, '" />
		</field>';

	echo '<field>
			<label for="Diagnosis">', _('Diagnosis'), '</label>
			<input type="text" name="Diagnosis" value="', $DiagnosisNote, '" />
		</field>';

	$Selected1 = '';
	$Selected0 = '';
	if ($ImmuneSupp == 1) {
		$Selected1 = ' selected="selected" ';
	} else {
		$Selected0 = ' selected="selected" ';
	}
	echo '<field>
			<label for="ImmuneSupp">', _('Immune Suppressed'), '</label>
			<select name="ImmuneSupp">
				<option value="1"', $Selected1, '>', _('Yes'), '</option>
				<option value="0"', $Selected0, '>', _('No'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="SampleDate">', _('Sample Date'), '</label>
			<input type="text" size="10" class="date" name="SampleDate" value="', ConvertSQLDate($SampleDate), '" />
		</field>';

	echo '</fieldset>';

	if (isset($_GET['Edit'])) {
		echo '<div class="centre">
				<input type="submit" name="UpdateRequest" value="', _('Update Request'), '" />
			</div>';
		echo '<input type="hidden" name="SelectedBatch" value="', $_GET['SelectedBatch'], '" />';
	} else {
		echo '<div class="centre">
				<input type="submit" name="SendRequest" value="', _('Send Request'), '" />
			</div>';
	}

	echo '</form>';
} else {

	if (!isset($_POST['Notes'])) {
		$_POST['Notes'] = '';
	}

	if (!isset($_POST['SampleDate'])) {
		$_POST['SampleDate'] = '';
	}

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