<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Medical Laboratory Test Request');
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
	$_SESSION['TestRequest' . $Identifier] = array();
	$_POST['Notes'] = '';
	$_POST['SampleDate'] = date($_SESSION['DefaultDateFormat']);
}

if (isset($_GET['Notes'])) {
	$_POST['Notes'] = $_GET['Notes'];
}

if (isset($_GET['SampleDate'])) {
	$_POST['SampleDate'] = $_GET['SampleDate'];
}

if (isset($_POST['SelectedGroup'])) {
	$SelectedGroup = $_POST['SelectedGroup'];
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
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Medical Laboratory Test Request'), '" /> ', _('Medical Laboratory Test Request'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $SelectedPatient, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Medical Laboratory Test Request'), '" /> ', _('Medical Laboratory Test Request'), '
		</p>';
}

if (isset($_POST['SendBatch'])) {

	foreach ($_SESSION['TestRequest' . $Identifier] as $TestNumber) {
		$SQL = "SELECT id
				FROM care_test_param
				WHERE nr='" . $TestNumber . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$TestArray[$TestNumber] = $MyRow['id'];
	}

	$Encounter = GetEncounterFromPID($SelectedPatient);

	$SQL = "SELECT current_ward_nr,
					current_dept_nr
				FROM care_encounter
				WHERE encounter_nr='" . $Encounter . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$WardNumber = $MyRow['current_ward_nr'];
	$DeptNumber = $MyRow['current_dept_nr'];

	if (isset($_POST['Urgent'])) {
		$Urgent = 1;
	} else {
		$Urgent = 0;
	}

	$History = 'Create: ' . date('Y-m-d') . ' = ' . $_SESSION['UserID'];

	$BatchSQL = "INSERT INTO care_test_request_chemlabor (`batch_nr`,
															`encounter_nr`,
															`room_nr`,
															`dept_nr`,
															`parameters`,
															`doctor_sign`,
															`notes`,
															`send_date`,
															`sample_time`,
															`sample_weekday`,
															`status`,
															`urgent`,
															`history`,
															`modify_id`,
															`create_id`,
															`create_time`
														) VALUES (
															NULL,
															'" . $Encounter . "',
															'" . $WardNumber . "',
															'" . $DeptNumber . "',
															'" . implode(',', $TestArray) . "',
															'" . $_SESSION['UserID'] . "',
															'" . $_POST['Notes'] . "',
															NOW(),
															'" . $_POST['Hours'] . ":" . $_POST['Minutes'] . "',
															'" . $_POST['SampleDay'] . "',
															'pending',
															'" . $Urgent . "',
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
	$BatchNumber = DB_Last_Insert_ID('care_test_request_chemlabor', 'batch_nr');

	foreach ($TestArray as $TestNumber => $TestID) {
		$LineSQL = "INSERT INTO care_test_request_chemlabor_sub (`sub_id`,
																	`batch_nr`,
																	`encounter_nr`,
																	`paramater_name`,
																	`parameter_value`
																) VALUES (
																	NULL,
																	'" . $BatchNumber . "',
																	'" . $Encounter . "',
																	'" . $TestID . "',
																	'" . $TestNumber . "'
																)";
		$LineResult = DB_query($LineSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('Failed to insert the batch line into the database'), 'error');
			include ('includes/footer.php');
			exit;
		}
	}
	prnMsg(_('The batch of tests has been sent successfully'), 'success');
	unset($_SESSION['TestRequest' . $Identifier]);
	unset($SelectedPatient);
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
if (isset($_GET['Remove'])) {
	$ArrayItem = array_search($_GET['Test'], $_SESSION['TestRequest' . $Identifier]);
	unset($_SESSION['TestRequest' . $Identifier][$ArrayItem]);
}

if (isset($_POST['AddTests'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 5) == 'test_') {
			if (!in_array(mb_substr($Key, 5), $_SESSION['TestRequest' . $Identifier])) {
				$_SESSION['TestRequest' . $Identifier][] = mb_substr($Key, 5);
			}
		}
	}
}

if (isset($_SESSION['TestRequest' . $Identifier]) and count($_SESSION['TestRequest' . $Identifier]) > 0) {
	echo '<table>
			<thead
				<tr>
					<th colspan="4"><h3>', _('Tests in Batch'), '</h3></th>
				</tr>
				<tr>
					<th class="SortedColumn">', _('Test Number'), '</th>
					<th class="SortedColumn">', _('Test Name'), '</th>
					<th class="SortedColumn">', _('Test ID'), '</th>
					<th></th>
				</tr>
			</thead>';
	echo '<tbody>';

	foreach ($_SESSION['TestRequest' . $Identifier] as $TestNumber) {
		$SQL = "SELECT nr,
					name,
					id,
					sort_nr
				FROM care_test_param
				WHERE nr='" . $TestNumber . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		echo '<tr class="striped_row">
				<td>', $TestNumber, '</td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['id'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Remove=Yes&SampleDate=', $_POST['SampleDate'], '&Test=', $MyRow['nr'], '&SelectedPatient=', $SelectedPatient, '&identifier=', $Identifier, '&Notes=', $_POST['Notes'], '">', _('Remove From Batch'), '</a></td>
			</tr>';
	}

	echo '</tbody>
		</table>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="SelectedPatient" value="', $SelectedPatient, '" />';
	echo '<input type="hidden" name="SampleDate" value="', $_POST['SampleDate'], '" />';

	echo '<fieldset>
			<legend>', _('Clinicians Notes'), '</legend>
			<field>
				<textarea cols=60 rows=5 wrap="physical" name="Notes">', $_POST['Notes'], '</textarea>
			</field>
			<field>
				', _('Sample Taken On'), '
				<input type="radio" name="SampleDay" value="0" />', _('Sun'), '
				<input type="radio" name="SampleDay" value="1" />', _('Mon'), '
				<input type="radio" name="SampleDay" value="2" />', _('Tue'), '
				<input type="radio" name="SampleDay" value="3" />', _('Wed'), '
				<input type="radio" name="SampleDay" value="4" />', _('Thu'), '
				<input type="radio" name="SampleDay" value="5" />', _('Fri'), '
				<input type="radio" name="SampleDay" value="6" />', _('Sat'), '
			</field>
			<field>
				', ('Sample Taken At'), '
				<select name="Hours">';
	for ($Hours = 0;$Hours < 24;$Hours++) {
		if ($Hours == date('H')) {
			echo '<option value="', $Hours, '" selected="selected">', $Hours, '</option>';
		} else {
			echo '<option value="', $Hours, '">', $Hours, '</option>';
		}
	}
	echo '</select> ', _('Hours');;
	echo ' <select name="Minutes">';
	for ($Minutes = 0;$Minutes < 60;$Minutes = $Minutes + 15) {
		if ($Minutes == (date('i') - (date('i') % 15))) {
			echo '<option value="', $Minutes, '" selected="selected">', $Minutes, '</option>';
		} else {
			echo '<option value="', $Minutes, '">', $Minutes, '</option>';
		}
	}
	echo '</select> ', _('Minutes');

	echo '<field>
			', _('Urgent Test'), '
			<input type="checkbox" name="Urgent" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SendBatch" value="', _('Send Batch'), '" />
		</div>';

	echo '</form>';
}

if (isset($_POST['GroupSelect'])) {
	$SQL = "SELECT nr,
					name,
					id,
					sort_nr
				FROM care_test_param
				WHERE group_id='" . $_POST['SelectedGroup'] . "'
				ORDER BY name";
	$Result = DB_query($SQL);

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="Notes" value="', $_POST['Notes'], '" />';
	echo '<input type="hidden" name="SampleDate" value="', $_POST['SampleDate'], '" />';

	echo '<table>
			<thead>
				<tr>
					<th colspan="3"><h3>', _('Select Tests'), '</h3></th>
				</tr>
				<tr>
					<th class="SortedColumn">', _('Test Name'), '</th>
					<th class="SortedColumn">', _('Test ID'), '</th>
					<th>', _('Select Test'), '</th>
				</tr>
			</thead>';

	echo '<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		if (!in_array($MyRow['nr'], $_SESSION['TestRequest' . $Identifier])) {
			echo '<tr class="striped_row">
					<td>', $MyRow['name'], '</td>
					<td>', $MyRow['id'], '</td>
					<td><input type="checkbox" name="test_', $MyRow['nr'], '" /></td>
				</tr>';
		}
	}

	echo '</tbody>
		</table>';

	echo '<div class="centre">
			<input type="submit" name="AddTests" value="', _('Add Tests To Batch'), '" />
		</div>';
}

if (isset($SelectedPatient) and $SelectedPatient != '') {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="SelectedPatient" value="', $SelectedPatient, '" />';
	echo '<input type="hidden" name="Notes" value="', $_POST['Notes'], '" />';
	echo '<input type="hidden" name="SampleDate" value="', $_POST['SampleDate'], '" />';

	echo '<fieldset>
			<legend>', _('Select a test group'), '</legend>
			<field>
				<label for="SelectedGroup">', _('Parameter Group'), '</label>
				<select name="SelectedGroup">';

	$SQL = "SELECT nr,
					name,
					id,
					sort_nr
				FROM care_test_param
				WHERE group_id='-1'
				ORDER BY sort_nr";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if ($SelectedGroup == $MyRow['id']) {
			echo '<option value="', $MyRow['id'], '" selected="selected">', $MyRow['name'], '</option>';
		} else {
			echo '<option value="', $MyRow['id'], '">', $MyRow['name'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="GroupSelect" value="', _('Select Group'), '" />
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