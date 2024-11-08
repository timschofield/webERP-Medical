<?php
include ('includes/session.php');
$Title = _('Enter Bacteriology Test Findings');
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
			<a href="', $RootPath, '/KCMCPendingBacteriologyTests.php>', _('Select a batch to view'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

$CheckSQL = "SELECT COUNT(batch_nr) AS total FROM care_test_findings_baclabor WHERE batch_nr='" . $SelectedBatch . "'";
$CheckResult = DB_query($CheckSQL);
$CheckRow = DB_fetch_array($CheckResult);

if ($CheckRow['total'] > 0) {
	$IsAnUpdate = true;
} else {
	$IsAnUpdate = false;
}

if (isset($_POST['Save'])) {

	$SelectedPatient = $_POST['SelectedPatient'];
	$Encounter = GetEncounterFromPID($SelectedPatient);

	$EncounterSQL = "SELECT current_ward_nr,
							current_dept_nr
						FROM care_encounter
						WHERE encounter_nr='" . $Encounter . "'";
	$EncounterResult = DB_query($EncounterSQL);
	$EncounterRow = DB_fetch_array($EncounterResult);

	$Ward = $EncounterRow['current_ward_nr'];
	$Department = $EncounterRow['current_dept_nr'];

	if (isset($_POST['findings_init'])) {
		$_POST['findings_init'] = 1;
	} else {
		$_POST['findings_init'] = 0;
	}

	if (isset($_POST['findings_current'])) {
		$_POST['findings_current'] = 1;
	} else {
		$_POST['findings_current'] = 0;
	}

	if (isset($_POST['findings_final'])) {
		$_POST['findings_final'] = 1;
	} else {
		$_POST['findings_final'] = 0;
	}

	$TypeGeneral = '';
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 7) == 'labtest') {
			$TypeGeneral.= mb_substr($Key, 7) . '=1&';
		}
	}
	$TypeGeneral = mb_substr($TypeGeneral, 0, -1);

	$ResistAnaerob = '';
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 4) == '_rn_') {
			$ResistAnaerob.= $Key . '=1&';
		}
	}
	$ResistAnaerob = mb_substr($ResistAnaerob, 0, -1);

	$ResistAerob = '';
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 4) == '_ra_' or mb_substr($Key, 0, 4) == '_rx_') {
			$ResistAerob.= $Key . '=1&';
		}
	}
	$ResistAerob = mb_substr($ResistAerob, 0, -1);

	$Findings = '';
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 4) == '_tr_') {
			$Findings.= $Key . '=1&';
		}
	}
	$Findings = mb_substr($Findings, 0, -1);

	if ($IsAnUpdate) {
		$HistorySQL = "SELECT history FROM `care_test_findings_baclabor` WHERE batch_nr='" . $SelectedBatch . "'";
		$HistoryResult = DB_query($HistorySQL);
		$HistoryRow = DB_fetch_array($HistoryResult);
		$History = $HistoryRow['history'] . 'Modify: ' . date('Y-m.d H:i:s') . ' = ' . $_SESSION['UserID'] . "\n";
		$UpdateHeaderSQL = "UPDATE `care_test_findings_baclabor` SET `room_nr`='" . $Ward . "',
																		`dept_nr`='" . $Department . "',
																		`notes`='" . $_POST['notes'] . "',
																		`findings_init`='" . $_POST['findings_init'] . "',
																		`findings_current`='" . $_POST['findings_current'] . "',
																		`findings_final`='" . $_POST['findings_final'] . "',
																		`entry_nr`='" . $_POST['EntryNumber'] . "',
																		`rec_date`='" . FormatDateForSQL($_POST['ReceptionDate']) . "',
																		`doctor_id`='" . $_SESSION['UserID'] . "',
																		`findings_date`=CURRENT_DATE,
																		`findings_time`=CURRENT_TIME,
																		`status`='updated',
																		`history`='" . $History . "',
																		`modify_id`='" . $_SESSION['UserID'] . "'
																	WHERE batch_nr='" . $SelectedBatch . "'";
		$UpdateHeaderResult = DB_query($UpdateHeaderSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('There was a problem updating the header information for these results'), 'error');
			include ('includes/footer.php');
			exit;
		}
	} else {
		$InsertHeaderSQL = "INSERT INTO `care_test_findings_baclabor` (`batch_nr`,
																		`encounter_nr`,
																		`room_nr`,
																		`dept_nr`,
																		`notes`,
																		`findings_init`,
																		`findings_current`,
																		`findings_final`,
																		`entry_nr`,
																		`rec_date`,
																		`type_general`,
																		`resist_anaerob`,
																		`resist_aerob`,
																		`findings`,
																		`doctor_id`,
																		`findings_date`,
																		`findings_time`,
																		`status`,
																		`history`,
																		`modify_id`,
																		`create_id`,
																		`create_time`
																	) VALUES (
																		'" . $SelectedBatch . "',
																		'" . $Encounter . "',
																		'" . $Ward . "',
																		'" . $Department . "',
																		'" . $_POST['notes'] . "',
																		'" . $_POST['findings_init'] . "',
																		'" . $_POST['findings_current'] . "',
																		'" . $_POST['findings_final'] . "',
																		'" . $_POST['EntryNumber'] . "',
																		'" . FormatDateForSQL($_POST['ReceptionDate']) . "',
																		'',
																		'',
																		'',
																		'',
																		'" . $_SESSION['UserID'] . "',
																		CURRENT_DATE,
																		CURRENT_TIME,
																		'initial',
																		'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																		'" . $_SESSION['UserID'] . "',
																		'" . $_SESSION['UserID'] . "',
																		CURRENT_TIME
																	)";
		$InsertHeaderResult = DB_query($InsertHeaderSQL);
		if (DB_error_no() > 0) {
			prnMsg(_('There was a problem inserting the header information for these results'), 'error');
			include ('includes/footer.php');
			exit;
		}
	}

	$TypeGeneralList = explode("&", $TypeGeneral);
	/* We must remove any lines that are in the database but no
	 * longer in the submitted page
	*/
	$SQL = "SELECT sub_id, type_general FROM care_test_findings_baclabor_sub WHERE batch_nr='" . $SelectedBatch . "' AND type_general<>'0'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (!in_array($MyRow['type_general'], $TypeGeneralList)) {
			$DeleteSQL = "DELETE FROM care_test_findings_baclabor_sub WHERE sub_id='" . $MyRow['sub_id'] . "'";
			$DeleteResult = DB_query($DeleteSQL);
		}
	}
	foreach ($TypeGeneralList as $key => $value) {
		$Tests = explode("=", $value);
		if ($Tests[0] != '') {
			if ($IsAnUpdate) {
				$CheckSQL = "SELECT sub_id
								FROM care_test_findings_baclabor_sub
								WHERE batch_nr='" . $SelectedBatch . "'
									AND type_general='" . $Tests[0] . "'";
				$CheckResult = DB_query($CheckSQL);
				if (DB_num_rows($CheckResult) == 0) {
					$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																					`batch_nr`,
																					`encounter_nr`,
																					`type_general`,
																					`resist_anaerob`,
																					`resist_aerob`,
																					`findings`,
																					`findings_date`,
																					`findings_time`,
																					`status`,
																					`history`,
																					`modify_id`,
																					`create_id`,
																					`create_time`
																				) VALUES (
																					NULL,
																					'" . $SelectedBatch . "',
																					'" . $Encounter . "',
																					'" . $Tests[0] . "',
																					'0',
																					'0',
																					'0',
																					CURRENT_DATE,
																					CURRENT_TIME,
																					'initial',
																					'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																					'" . $_SESSION['UserID'] . "',
																					'" . $_SESSION['UserID'] . "',
																					CURRENT_TIME
																				)";
					$InsertLineResult = DB_query($InsertLineSQL);
					if (DB_error_no() > 0) {
						prnMsg(_('There was a problem inserting the line information for these results'), 'error');
						include ('includes/footer.php');
						exit;
					}
				}
			} else {
				$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																				`batch_nr`,
																				`encounter_nr`,
																				`type_general`,
																				`resist_anaerob`,
																				`resist_aerob`,
																				`findings`,
																				`findings_date`,
																				`findings_time`,
																				`status`,
																				`history`,
																				`modify_id`,
																				`create_id`,
																				`create_time`
																			) VALUES (
																				NULL,
																				'" . $SelectedBatch . "',
																				'" . $Encounter . "',
																				'" . $Tests[0] . "',
																				'0',
																				'0',
																				'0',
																				CURRENT_DATE,
																				CURRENT_TIME,
																				'initial',
																				'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																				'" . $_SESSION['UserID'] . "',
																				'" . $_SESSION['UserID'] . "',
																				CURRENT_TIME
																			)";
				$InsertLineResult = DB_query($InsertLineSQL);
				if (DB_error_no() > 0) {
					prnMsg(_('There was a problem inserting the line information for these results'), 'error');
					include ('includes/footer.php');
					exit;
				}
			}
		}
	}

	$ResistAnaerobList = explode("&", $ResistAnaerob);
	/* We must remove any lines that are in the database but no
	 * longer in the submitted page
	*/
	$SQL = "SELECT sub_id, resist_anaerob FROM care_test_findings_baclabor_sub WHERE batch_nr='" . $SelectedBatch . "' AND resist_anaerob<>'0'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (!in_array($MyRow['resist_anaerob'], $ResistAnaerobList)) {
			$DeleteSQL = "DELETE FROM care_test_findings_baclabor_sub WHERE sub_id='" . $MyRow['sub_id'] . "'";
			$DeleteResult = DB_query($DeleteSQL);
		}
	}
	foreach ($ResistAnaerobList as $key => $value) {
		$Tests = explode("=", $value);
		if ($Tests[0] != '') {
			if ($IsAnUpdate) {
				$CheckSQL = "SELECT sub_id
								FROM care_test_findings_baclabor_sub
								WHERE batch_nr='" . $SelectedBatch . "'
									AND resist_anaerob='" . $Tests[0] . "'";
				$CheckResult = DB_query($CheckSQL);
				if (DB_num_rows($CheckResult) == 0) {
					$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																					`batch_nr`,
																					`encounter_nr`,
																					`type_general`,
																					`resist_anaerob`,
																					`resist_aerob`,
																					`findings`,
																					`findings_date`,
																					`findings_time`,
																					`status`,
																					`history`,
																					`modify_id`,
																					`create_id`,
																					`create_time`
																				) VALUES (
																					NULL,
																					'" . $SelectedBatch . "',
																					'" . $Encounter . "',
																					'0',
																					'" . $Tests[0] . "',
																					'0',
																					'0',
																					CURRENT_DATE,
																					CURRENT_TIME,
																					'initial',
																					'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																					'" . $_SESSION['UserID'] . "',
																					'" . $_SESSION['UserID'] . "',
																					CURRENT_TIME
																				)";
					$InsertLineResult = DB_query($InsertLineSQL);
					if (DB_error_no() > 0) {
						prnMsg(_('There was a problem inserting the line information for these results'), 'error');
						include ('includes/footer.php');
						exit;
					}
				}
			} else {
				$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																				`batch_nr`,
																				`encounter_nr`,
																				`type_general`,
																				`resist_anaerob`,
																				`resist_aerob`,
																				`findings`,
																				`findings_date`,
																				`findings_time`,
																				`status`,
																				`history`,
																				`modify_id`,
																				`create_id`,
																				`create_time`
																			) VALUES (
																				NULL,
																				'" . $SelectedBatch . "',
																				'" . $Encounter . "',
																				'0',
																				'" . $Tests[0] . "',
																				'0',
																				'0',
																				CURRENT_DATE,
																				CURRENT_TIME,
																				'initial',
																				'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																				'" . $_SESSION['UserID'] . "',
																				'" . $_SESSION['UserID'] . "',
																				CURRENT_TIME
																			)";
				$InsertLineResult = DB_query($InsertLineSQL);
				if (DB_error_no() > 0) {
					prnMsg(_('There was a problem inserting the line information for these results'), 'error');
					include ('includes/footer.php');
					exit;
				}
			}
		}
	}

	$ResistAerobList = explode("&", $ResistAerob);
	/* We must remove any lines that are in the database but no
	 * longer in the submitted page
	*/
	$SQL = "SELECT sub_id, resist_aerob FROM care_test_findings_baclabor_sub WHERE batch_nr='" . $SelectedBatch . "' AND resist_aerob<>'0'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (!in_array($MyRow['resist_aerob'], $ResistAerobList)) {
			$DeleteSQL = "DELETE FROM care_test_findings_baclabor_sub WHERE sub_id='" . $MyRow['sub_id'] . "'";
			$DeleteResult = DB_query($DeleteSQL);
		}
	}
	foreach ($ResistAerobList as $key => $value) {
		$Tests = explode("=", $value);
		if ($Tests[0] != '') {
			if ($IsAnUpdate) {
				$CheckSQL = "SELECT sub_id
								FROM care_test_findings_baclabor_sub
								WHERE batch_nr='" . $SelectedBatch . "'
									AND resist_aerob='" . $Tests[0] . "'";
				$CheckResult = DB_query($CheckSQL);
				if (DB_num_rows($CheckResult) == 0) {
					$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																					`batch_nr`,
																					`encounter_nr`,
																					`type_general`,
																					`resist_anaerob`,
																					`resist_aerob`,
																					`findings`,
																					`findings_date`,
																					`findings_time`,
																					`status`,
																					`history`,
																					`modify_id`,
																					`create_id`,
																					`create_time`
																				) VALUES (
																					NULL,
																					'" . $SelectedBatch . "',
																					'" . $Encounter . "',
																					'0',
																					'0',
																					'" . $Tests[0] . "',
																					'0',
																					CURRENT_DATE,
																					CURRENT_TIME,
																					'initial',
																					'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																					'" . $_SESSION['UserID'] . "',
																					'" . $_SESSION['UserID'] . "',
																					CURRENT_TIME
																				)";
					$InsertLineResult = DB_query($InsertLineSQL);
					if (DB_error_no() > 0) {
						prnMsg(_('There was a problem inserting the line information for these results'), 'error');
						include ('includes/footer.php');
						exit;
					}
				}
			} else {
				$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																				`batch_nr`,
																				`encounter_nr`,
																				`type_general`,
																				`resist_anaerob`,
																				`resist_aerob`,
																				`findings`,
																				`findings_date`,
																				`findings_time`,
																				`status`,
																				`history`,
																				`modify_id`,
																				`create_id`,
																				`create_time`
																			) VALUES (
																				NULL,
																				'" . $SelectedBatch . "',
																				'" . $Encounter . "',
																				'0',
																				'0',
																				'" . $Tests[0] . "',
																				'0',
																				CURRENT_DATE,
																				CURRENT_TIME,
																				'initial',
																				'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																				'" . $_SESSION['UserID'] . "',
																				'" . $_SESSION['UserID'] . "',
																				CURRENT_TIME
																			)";
				$InsertLineResult = DB_query($InsertLineSQL);
				if (DB_error_no() > 0) {
					prnMsg(_('There was a problem inserting the line information for these results'), 'error');
					include ('includes/footer.php');
					exit;
				}
			}
		}
	}

	$FindingsList = explode("&", $Findings);
	/* We must remove any lines that are in the database but no
	 * longer in the submitted page
	*/
	$SQL = "SELECT sub_id, findings FROM care_test_findings_baclabor_sub WHERE batch_nr='" . $SelectedBatch . "' AND findings<>'0'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (!in_array($MyRow['findings'], $FindingsList)) {
			$DeleteSQL = "DELETE FROM care_test_findings_baclabor_sub WHERE sub_id='" . $MyRow['sub_id'] . "'";
			$DeleteResult = DB_query($DeleteSQL);
		}
	}
	foreach ($FindingsList as $key => $value) {
		$Tests = explode("=", $value);
		if ($Tests[0] != '') {
			if ($IsAnUpdate) {
				$CheckSQL = "SELECT sub_id
								FROM care_test_findings_baclabor_sub
								WHERE batch_nr='" . $SelectedBatch . "'
									AND findings='" . $Tests[0] . "'";
				$CheckResult = DB_query($CheckSQL);
				if (DB_num_rows($CheckResult) == 0) {
					$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																					`batch_nr`,
																					`encounter_nr`,
																					`type_general`,
																					`resist_anaerob`,
																					`resist_aerob`,
																					`findings`,
																					`findings_date`,
																					`findings_time`,
																					`status`,
																					`history`,
																					`modify_id`,
																					`create_id`,
																					`create_time`
																				) VALUES (
																					NULL,
																					'" . $SelectedBatch . "',
																					'" . $Encounter . "',
																					'0',
																					'0',
																					'0',
																					'" . $Tests[0] . "',
																					CURRENT_DATE,
																					CURRENT_TIME,
																					'initial',
																					'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																					'" . $_SESSION['UserID'] . "',
																					'" . $_SESSION['UserID'] . "',
																					CURRENT_TIME
																				)";
					$InsertLineResult = DB_query($InsertLineSQL);
					if (DB_error_no() > 0) {
						prnMsg(_('There was a problem inserting the line information for these results'), 'error');
						include ('includes/footer.php');
						exit;
					}
				}
			} else {
				$InsertLineSQL = "INSERT INTO `care_test_findings_baclabor_sub` (`sub_id`,
																				`batch_nr`,
																				`encounter_nr`,
																				`type_general`,
																				`resist_anaerob`,
																				`resist_aerob`,
																				`findings`,
																				`findings_date`,
																				`findings_time`,
																				`status`,
																				`history`,
																				`modify_id`,
																				`create_id`,
																				`create_time`
																			) VALUES (
																				NULL,
																				'" . $SelectedBatch . "',
																				'" . $Encounter . "',
																				'0',
																				'0',
																				'0',
																				'" . $Tests[0] . "',
																				CURRENT_DATE,
																				CURRENT_TIME,
																				'initial',
																				'Create: " . date('Y-m.d H:i:s') . " = " . $_SESSION['UserID'] . "\n" . "',
																				'" . $_SESSION['UserID'] . "',
																				'" . $_SESSION['UserID'] . "',
																				CURRENT_TIME
																			)";
				$InsertLineResult = DB_query($InsertLineSQL);
				if (DB_error_no() > 0) {
					prnMsg(_('There was a problem inserting the line information for these results'), 'error');
					include ('includes/footer.php');
					exit;
				}
			}
		}
	}
	prnMsg(_('The test results were saved successfully'), 'success');
}

if (isset($SelectedBatch)) {

	$TypeGeneral = array();
	$ResistAnaerob = array();
	$ResistAerob = array();
	$FindingsArray = array();

	if ($IsAnUpdate) {
		$SQL = "SELECT `notes`,
						`findings_init`,
						`findings_current`,
						`findings_final`,
						`entry_nr`,
						`rec_date`,
						`status`
					FROM care_test_findings_baclabor
					WHERE batch_nr='" . $SelectedBatch . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$Notes = $MyRow['notes'];

		if ($MyRow['findings_init'] == 1) {
			$FindingsInit = ' checked="checked" ';
		} else {
			$FindingsInit = '';
		}
		if ($MyRow['findings_current'] == 1) {
			$FindingsCurrent = ' checked="checked" ';
		} else {
			$FindingsCurrent = '';
		}
		if ($MyRow['findings_final'] == 1) {
			$FindingsFinal = ' checked="checked" ';
		} else {
			$FindingsFinal = '';
		}

		$EntryNumber = $MyRow['entry_nr'];
		$ReceivedDate = ConvertSQLDate($MyRow['rec_date']);

		$SQL = "SELECT  `sub_id`,
						`batch_nr`,
						`encounter_nr`,
						`type_general`,
						`resist_anaerob`,
						`resist_aerob`,
						`findings`,
						`findings_date`,
						`findings_time`,
						`status`,
						`history`
					FROM `care_test_findings_baclabor_sub`
					WHERE batch_nr='" . $SelectedBatch . "'";
		$Result = DB_query($SQL);
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['type_general'] != '0') {
				$TypeGeneral[] = $MyRow['type_general'];
			}
			if ($MyRow['resist_anaerob'] != '0') {
				$ResistAnaerob[] = $MyRow['resist_anaerob'];
			}
			if ($MyRow['resist_aerob'] != '0') {
				$ResistAerob[] = $MyRow['resist_aerob'];
			}
			if ($MyRow['findings'] != '0') {
				$FindingsArray[] = $MyRow['findings'];
			}
		}
	} else {
		$Notes = '';
		$FindingsInit = '';
		$FindingsCurrent = '';
		$FindingsFinal = '';
		$EntryNumber = '';
		$ReceivedDate = date($_SESSION['DefaultDateFormat']);
	}

	$HeaderSQL = "SELECT `encounter_nr`,
						`dept_nr`,
						`material_note`,
						`diagnosis_note`,
						`immune_supp`,
						`sample_date`,
						`history`
					FROM care_test_request_baclabor
					WHERE batch_nr='" . $SelectedBatch . "'";
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
					WHERE batch_nr='" . $SelectedBatch . "'";
	$LinesResult = DB_query($LinesSQL);
	while ($LinesRow = DB_fetch_array($LinesResult)) {
		if ($LinesRow['test_type_value'] != 0) {
			$TestArray[] = $LinesRow['test_type'];
		} elseif ($LinesRow['material_value'] != 0) {
			$MaterialArray[] = $LinesRow['material'];
		}
	}
	$SQL = "SELECT encounter_nr
			FROM care_test_request_baclabor
			WHERE batch_nr='" . $SelectedBatch . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$Encounter = $MyRow['encounter_nr'];
	$PID = GetPIDFromEncounter($Encounter);

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
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Enter Bacteriology Test Findings'), '" /> ', _('Enter Bacteriology Test Findings'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $PID, ') - ', _('Batch Number'), ' ', $SelectedBatch, '
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Enter Bacteriology Test Findings'), '" /> ', _('Enter Bacteriology Test Findings'), '
		</p>';
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<input type="hidden" name="SelectedPatient" value="', $SelectedPatient, '" />';
echo '<input type="hidden" name="Batch" value="', $SelectedBatch, '" />';

$SQL = "SELECT type, name FROM care_baclabor_material_type";
$Result = DB_query($SQL);

echo '<div class="baclab_container centre">';

echo '<div class="baclab_material">
		<fieldset>
			<legend>', _('Material'), '</legend>';

while ($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['type'], $MaterialArray)) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = '';
	}
	echo '<field>
			<label class="container" for="', $MyRow['type'], '" onclick="">', _($MyRow['name']), '
				<input type="checkbox" ', $Checked, ' id="material', $MyRow['type'], '" name="material', $MyRow['type'], '" />
				<span class="checkmark"></span>
			</label>
		</field>';
}

echo '</fieldset>
	</div>'; // Material
$SQL = "SELECT type, name FROM care_baclabor_test_type";
$Result = DB_query($SQL);
echo '<div class="baclab_test">
		<fieldset>
			<legend>', _('Requested Tests'), '</legend>';

while ($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['type'], $TestArray)) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = '';
	}
	echo '<field>
				<label class="container" for="', $MyRow['type'], '" onclick="">', _($MyRow['name']), '
					<input type="checkbox" ', $Checked, ' id="test', $MyRow['type'], '" name="test', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>
			</field>';
}

echo '</fieldset>
	</div>'; //Test
echo '<div class="baclab_label">
		<fieldset>
			<legend>', _('Label'), '</legend>';
include ('includes/KCMCDrawPatientLabel.php');
echo '</fieldset>
	</div>'; // Label
echo '<div class="baclab_batch_details">
		<fieldset>';

echo '<field>
		<label>', _('Material Note'), '</label>
		<input type="text" size="50" readonly="readonly" value="', $HeaderRow['material_note'], '" />
	</field>';

echo '<field>
		<label>', _('Diagnosis Note'), '</label>
		<input type="text" size="50" readonly="readonly" value="', $HeaderRow['diagnosis_note'], '" />
	</field>';

echo '<field style="margin-top:5px">
		<label>', _('Status'), '</label>
		<label style="width:auto;margin-right:10px" class="container" for="findings_init" onclick="">', _('Initial Findings'), '
			<input type="checkbox"', $FindingsInit, ' id="findings_init" name="findings_init" />
			<span class="checkmark"></span>
		</label>
		<label style="width:auto;margin-right:10px" class="container" for="findings_current" onclick="">', _('Current Findings'), '
			<input type="checkbox"', $FindingsCurrent, ' id="findings_current" name="findings_current" />
			<span class="checkmark"></span>
		</label>
		<label style="width:auto;margin-right:10px" class="container" for="findings_final" onclick="">', _('Final Findings'), '
			<input type="checkbox"', $FindingsFinal, '  id="findings_final" name="findings_final" />
			<span class="checkmark"></span>
		</label><br />
	</field><br />';

if ($HeaderRow['immune_supp'] == 0) {
	$ImmuneSuppressed = _('No');
} else {
	$ImmuneSuppressed = _('Yes');
}
echo '<field>
		<label for="ImmuneSupp">', _('Immune Suppressed'), '</label>
		<input type="text" size="10" readonly="readonly" value="', $ImmuneSuppressed, '" />
	</field>';

echo '<field>
		<label>', _('Laboratory Entry Number'), '</label>
		<input type="text" size="20" value="', $EntryNumber, '" name="EntryNumber" />
	</field>';

echo '<field>
		<label>', _('Date'), '</label>
		<input type="text" class="date" size="10" value="', $ReceivedDate, '" name="ReceptionDate" />
	</field>';

for ($i = 1;$i <= 10;$i++) {
	$SQL = "SELECT type_group, type, name FROM care_baclabor_labtest_type WHERE name<>'' AND type_group='" . $i . "' ORDER BY nr";
	$Result = DB_query($SQL);
	echo '<fieldset style="width:auto">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (in_array($MyRow['type'], $TypeGeneral)) {
			$Checked = ' checked="checked" ';
		} else {
			$Checked = '  ';
		}
		echo '<field>
				<label title="', $MyRow['type'], '" style="width:50px" class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox"', $Checked, 'id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
					<span class="checkmark"></span>
			</label>';
		if ($MyRow = DB_fetch_array($Result)) {
			if (in_array($MyRow['type'], $TypeGeneral)) {
				$Checked = ' checked="checked" ';
			} else {
				$Checked = '  ';
			}
			echo '<label title="', $MyRow['type'], '" style="width:50px" class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox"', $Checked, 'id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>';
		}
		if ($MyRow = DB_fetch_array($Result)) {
			if (in_array($MyRow['type'], $TypeGeneral)) {
				$Checked = ' checked="checked" ';
			} else {
				$Checked = '  ';
			}
			echo '<label title="', $MyRow['type'], '" style="width:50px" class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox"', $Checked, 'id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>';
		}
		if ($MyRow = DB_fetch_array($Result)) {
			if (in_array($MyRow['type'], $TypeGeneral)) {
				$Checked = ' checked="checked" ';
			} else {
				$Checked = '  ';
			}
			echo '<label title="', $MyRow['type'], '" style="width:50px" class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox"', $Checked, 'id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>';
		}
		if ($MyRow = DB_fetch_array($Result)) {
			if (in_array($MyRow['type'], $TypeGeneral)) {
				$Checked = ' checked="checked" ';
			} else {
				$Checked = '  ';
			}
			echo '<label title="', $MyRow['type'], '" style="width:50px" class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox"', $Checked, 'id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>';
		}
		if ($MyRow = DB_fetch_array($Result)) {
			if (in_array($MyRow['type'], $TypeGeneral)) {
				$Checked = ' checked="checked" ';
			} else {
				$Checked = '  ';
			}
			echo '<label title="', $MyRow['type'], '" style="width:50px" class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox"', $Checked, 'id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>';
		}
		if ($MyRow = DB_fetch_array($Result)) {
			if (in_array($MyRow['type'], $TypeGeneral)) {
				$Checked = ' checked="checked" ';
			} else {
				$Checked = '  ';
			}
			echo '<label title="', $MyRow['type'], '" style="width:50px" class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
					<input type="checkbox"', $Checked, 'id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>
			</field>';
		}
	}
	echo '</fieldset>';
}

echo '</fieldset>';

echo '</fieldset>
	</div>';

echo '<div class="baclab_lab_notes">
		<fieldset>
			<legend>', _('For lab use only'), '</legend>';

echo '<field>
		<label></label>
		<textarea name="notes" cols=45 rows=12 wrap="physical">', $Notes, '</textarea>
	</field>';

$SQL = "SELECT name FROM care_lab_resistanaerobacro ORDER BY nr";
$Result = DB_query($SQL);

echo '<fieldset>
		<legend>', _('Anaerobic Resistance Test'), '</legend>
		<table>';
while ($MyRow = DB_fetch_array($Result)) {
	if (in_array('_rn_' . strtolower($MyRow['name']) . '_1_s_', $ResistAnaerob)) {
		$Checked1S = ' checked="checked" ';
	} else {
		$Checked1S = ' ';
	}
	if (in_array('_rn_' . strtolower($MyRow['name']) . '_1_r_', $ResistAnaerob)) {
		$Checked1R = ' checked="checked" ';
	} else {
		$Checked1R = ' ';
	}
	if (in_array('_rn_' . strtolower($MyRow['name']) . '_2_s_', $ResistAnaerob)) {
		$Checked2S = ' checked="checked" ';
	} else {
		$Checked2S = ' ';
	}
	if (in_array('_rn_' . strtolower($MyRow['name']) . '_2_r_', $ResistAnaerob)) {
		$Checked2R = ' checked="checked" ';
	} else {
		$Checked2R = ' ';
	}
	if (in_array('_rn_' . strtolower($MyRow['name']) . '_3_s_', $ResistAnaerob)) {
		$Checked3S = ' checked="checked" ';
	} else {
		$Checked3S = ' ';
	}
	if (in_array('_rn_' . strtolower($MyRow['name']) . '_3_r_', $ResistAnaerob)) {
		$Checked3R = ' checked="checked" ';
	} else {
		$Checked3R = ' ';
	}
	echo '<tr>
			<th rowspan="5">', $MyRow['name'], '</th>
			<th>S</th>
			<th>R</th>
			<th>S</th>
			<th>R</th>
			<th>S</th>
			<th>R</th>
		</tr>
		<tr>
			<td><label class="container" for="_rn_', strtolower($MyRow['name']), '_1_s_" onclick="ToggleCheckbox(document.getElementById(\'_rn_', strtolower($MyRow['name']), '_1_s_\'));">
					<input type="checkbox"', $Checked1S, 'id="_rn_', strtolower($MyRow['name']), '_1_s_" name="_rn_', strtolower($MyRow['name']), '_1_s_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_rn_', strtolower($MyRow['name']), '_1_r_" onclick="ToggleCheckbox(document.getElementById(\'_rn_', strtolower($MyRow['name']), '_1_r_\'));">
					<input type="checkbox"', $Checked1R, 'id="_rn_', strtolower($MyRow['name']), '_1_r_" name="_rn_', strtolower($MyRow['name']), '_1_r_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_rn_', strtolower($MyRow['name']), '_2_s_" onclick="ToggleCheckbox(document.getElementById(\'_rn_', strtolower($MyRow['name']), '_2_s_\'));">
					<input type="checkbox"', $Checked2S, 'id="_rn_', strtolower($MyRow['name']), '_2_s_" name="_rn_', strtolower($MyRow['name']), '_2_s_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_rn_', strtolower($MyRow['name']), '_2_r_" onclick="ToggleCheckbox(document.getElementById(\'_rn_', strtolower($MyRow['name']), '_2_r_\'));">
					<input type="checkbox"', $Checked2R, 'id="_rn_', strtolower($MyRow['name']), '_2_r_" name="_rn_', strtolower($MyRow['name']), '_2_r_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_rn_', strtolower($MyRow['name']), '_3_s_" onclick="ToggleCheckbox(document.getElementById(\'_rn_', strtolower($MyRow['name']), '_3_s_\'));">
					<input type="checkbox"', $Checked3S, 'id="_rn_', strtolower($MyRow['name']), '_3_s_" name="_rn_', strtolower($MyRow['name']), '_3_s_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_rn_', strtolower($MyRow['name']), '_3_r_" onclick="ToggleCheckbox(document.getElementById(\'_rn_', strtolower($MyRow['name']), '_3_r_\'));">
					<input type="checkbox"', $Checked3R, 'id="_rn_', strtolower($MyRow['name']), '_3_r_" name="_rn_', strtolower($MyRow['name']), '_3_r_" />
					<span class="checkmark"></span>
				</label>
			</td>
		</tr>
		<tr></tr>
		<tr></tr>
		<tr></tr>';
}

echo '</table>
	</fieldset>';

echo '</fieldset>
	</div>';

echo '<div class="baclab_findings">
		<fieldset class="baclab_findings">
			<legend>', _('Test results / Findings'), '</legend>';

echo '<fieldset>';

if (in_array('_tr_blocker_pos', $FindingsArray)) {
	$CheckedPos = ' checked="checked" ';
} else {
	$CheckedPos = ' ';
}

echo '<field>
		<label class="container" for="_tr_blocker_pos" onclick="ToggleCheckbox(document.getElementById(\'_tr_blocker_pos\'));">', _('Blocker Positive'), '
			<input type="checkbox"', $CheckedPos, 'id="_tr_blocker_pos" name="_tr_blocker_pos" />
			<span class="checkmark"></span>
		</label>
	</field>';

if (in_array('_tr_blocker_neg', $FindingsArray)) {
	$CheckedNeg = ' checked="checked" ';
} else {
	$CheckedNeg = ' ';
}

echo '<field>
		<label class="container" for="_tr_blocker_neg" onclick="ToggleCheckbox(document.getElementById(\'_tr_blocker_neg\'));">', _('Blocker Negative'), '
			<input type="checkbox"', $CheckedNeg, 'id="_tr_blocker_neg" name="_tr_blocker_neg" />
			<span class="checkmark"></span>
		</label>
	</field>';
echo '</fieldset>';

echo '<fieldset>';

if (in_array('_tr_mark_streptococcus', $FindingsArray)) {
	$CheckedStrep = ' checked="checked" ';
} else {
	$CheckedStrep = ' ';
}
echo '<field>
		<label class="container" for="_tr_mark_streptococcus" onclick="ToggleCheckbox(document.getElementById(\'_tr_mark_streptococcus\'));">', _('Mark by streptococcus resistance'), '
			<input type="checkbox"', $CheckedStrep, 'id="_tr_mark_streptococcus" name="_tr_mark_streptococcus" />
			<span class="checkmark"></span>
		</label>
	</field>';

if (in_array('_tr_pathogenmore', $FindingsArray)) {
	$CheckedMore = ' checked="checked" ';
} else {
	$CheckedMore = ' ';
}
echo '<field>
		<label class="container" for="_tr_pathogenmore" onclick="ToggleCheckbox(document.getElementById(\'_tr_pathogenmore\'));">', _('Bac.ct.>10^5'), '
			<input type="checkbox"', $CheckedMore, 'id="_tr_pathogenmore" name="_tr_pathogenmore" />
			<span class="checkmark"></span>
		</label>
	</field>';

if (in_array('_tr_pathogenless', $FindingsArray)) {
	$CheckedLess = ' checked="checked" ';
} else {
	$CheckedLess = ' ';
}
echo '<field>
		<label class="container" for="_tr_pathogenless" onclick="ToggleCheckbox(document.getElementById(\'_tr_pathogenless\'));">', _('Bac.ct.<10^5'), '
			<input type="checkbox"', $CheckedLess, 'id="_tr_pathogenless" name="_tr_pathogenless" />
			<span class="checkmark"></span>
		</label>
	</field>';

if (in_array('_tr_patho_neg', $FindingsArray)) {
	$CheckedPathoNeg = ' checked="checked" ';
} else {
	$CheckedPathoNeg = ' ';
}
echo '<field>
		<label class="container" for="_tr_patho_neg" onclick="ToggleCheckbox(document.getElementById(\'_tr_patho_neg\'));">', _('Bac.ct.neg'), '
			<input type="checkbox"', $CheckedPathoNeg, 'id="_tr_patho_neg" name="_tr_patho_neg" />
			<span class="checkmark"></span>
		</label>
	</field>';
echo '</fieldset>';

$SQL = "SELECT nr, name FROM care_lab_testresultid ORDER BY nr";
$Result = DB_query($SQL);

echo '<fieldset style="width:80%">';

while ($MyRow = DB_fetch_array($Result)) {

	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_1', $FindingsArray)) {
		$Checked1 = ' checked="checked" ';
	} else {
		$Checked1 = ' ';
	}
	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_2', $FindingsArray)) {
		$Checked2 = ' checked="checked" ';
	} else {
		$Checked2 = ' ';
	}
	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_3', $FindingsArray)) {
		$Checked3 = ' checked="checked" ';
	} else {
		$Checked3 = ' ';
	}
	echo '<field>
			<label class="container" style="width:0px;margin-bottom:10px" for="_tr_', $MyRow['name'], '_1" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_1\'));">
				<input type="checkbox"', $Checked1, 'id="_tr_', $MyRow['name'], '_1" name="_tr_', $MyRow['name'], '_1" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:0px" for="_tr_', $MyRow['name'], '_2" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_2\'));">
				<input type="checkbox"', $Checked2, 'id="_tr_', $MyRow['name'], '_2" name="_tr_', $MyRow['name'], '_2" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:20%;margin-right:10px" for="_tr_', $MyRow['name'], '_3" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox"', $Checked3, 'id="_tr_', $MyRow['name'], '_3" name="_tr_', $MyRow['name'], '_3" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);

	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_1', $FindingsArray)) {
		$Checked1 = ' checked="checked" ';
	} else {
		$Checked1 = ' ';
	}
	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_2', $FindingsArray)) {
		$Checked2 = ' checked="checked" ';
	} else {
		$Checked2 = ' ';
	}
	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_3', $FindingsArray)) {
		$Checked3 = ' checked="checked" ';
	} else {
		$Checked3 = ' ';
	}
	echo '<label class="container" style="width:0px;margin-bottom:10px" for="_tr_', $MyRow['name'], '_1" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_1\'));">
				<input type="checkbox"', $Checked1, 'id="_tr_', $MyRow['name'], '_1" name="_tr_', $MyRow['name'], '_1" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:0px" for="_tr_', $MyRow['name'], '_2" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_2\'));">
				<input type="checkbox"', $Checked2, 'id="_tr_', $MyRow['name'], '_2" name="_tr_', $MyRow['name'], '_2" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:20%;margin-right:10px" for="_tr_', $MyRow['name'], '_3" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox"', $Checked3, 'id="_tr_', $MyRow['name'], '_3" name="_tr_', $MyRow['name'], '_3" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);

	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_1', $FindingsArray)) {
		$Checked1 = ' checked="checked" ';
	} else {
		$Checked1 = ' ';
	}
	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_2', $FindingsArray)) {
		$Checked2 = ' checked="checked" ';
	} else {
		$Checked2 = ' ';
	}
	if (in_array('_tr_' . str_replace('.', '_', $MyRow['name']) . '_3', $FindingsArray)) {
		$Checked3 = ' checked="checked" ';
	} else {
		$Checked3 = ' ';
	}
	echo '<label class="container" style="width:0px;margin-bottom:10px" for="_tr_', $MyRow['name'], '_1" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_1\'));">
				<input type="checkbox"', $Checked1, 'id="_tr_', $MyRow['name'], '_1" name="_tr_', $MyRow['name'], '_1" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:0px" for="_tr_', $MyRow['name'], '_2" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_2\'));">
				<input type="checkbox"', $Checked2, 'id="_tr_', $MyRow['name'], '_2" name="_tr_', $MyRow['name'], '_2" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:auto;margin-right:10px" for="_tr_', $MyRow['name'], '_3" onclick="ToggleCheckbox(document.getElementById(\'_tr_', $MyRow['name'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox"', $Checked3, 'id="_tr_', $MyRow['name'], '_3" name="_tr_', $MyRow['name'], '_3" />
				<span class="checkmark"></span>
			</label>
		</field><br />';
}

echo '</fieldset>';

$SQL = "SELECT type, name FROM care_lab_testresultid_2 ORDER BY nr";
$Result = DB_query($SQL);

echo '<fieldset style="width:80%">';

while ($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['type'], $FindingsArray)) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label class="container" style="width:20%;margin-right:10px" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'', $MyRow['type'], '\'));">', $MyRow['name'], '
				<input type="checkbox"', $Checked, 'id="', $MyRow['type'], '" name="', $MyRow['type'], '" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);
	if (in_array($MyRow['type'], $FindingsArray)) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}

	echo '<label class="container" style="width:20%;margin-right:10px" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'', $MyRow['type'], '\'));">', $MyRow['name'], '
				<input type="checkbox"', $Checked, 'id="', $MyRow['type'], '" name="', $MyRow['type'], '" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);
	if (in_array($MyRow['type'], $FindingsArray)) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}

	echo '<label class="container" style="width:auto;margin-right:10px" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'', $MyRow['type'], '\'));">', $MyRow['name'], '
				<input type="checkbox"', $Checked, 'id="', $MyRow['type'], '" name="', $MyRow['type'], '" />
				<span class="checkmark"></span>
			</label>
		</field><br />';
}

echo '</fieldset>';

echo '</fieldset>
	</div>';

echo '<div class="baclab_resistance_test">
		<fieldset>
			<legend>', _('Aerobic Resistance Test'), '</legend>';

$SQL = "SELECT nr, name FROM care_aerobic_resistance_acro ORDER BY nr";
$Result = DB_query($SQL);

echo '<table>';
if (in_array('_rx_pathogen_1_', $ResistAerob)) {
	$Checked1 = ' checked="checked" ';
} else {
	$Checked1 = ' ';
}
if (in_array('_rx_pathogen_2_', $ResistAerob)) {
	$Checked2 = ' checked="checked" ';
} else {
	$Checked2 = ' ';
}
if (in_array('_rx_pathogen_3_', $ResistAerob)) {
	$Checked3 = ' checked="checked" ';
} else {
	$Checked3 = ' ';
}

echo '<tr>
		<th></th>
		<td colspan="3">
			<label class="container" for="_rx_pathogen_1_" onclick="ToggleCheckbox(document.getElementById(\'_rx_pathogen_1_\'));">', _('Patho 1'), '
				<input type="checkbox"', $Checked1, 'id="_rx_pathogen_1_" name="_rx_pathogen_1_" />
				<span class="checkmark"></span>
			</label>
		</td>
		<th colspan="6"></th>
		<td colspan="3">
			<label class="container" for="_rx_pathogen_2_" onclick="ToggleCheckbox(document.getElementById(\'_rx_pathogen_2_\'));">', _('Patho 2'), '
				<input type="checkbox"', $Checked2, 'id="_rx_pathogen_2_" name="_rx_pathogen_2_" />
				<span class="checkmark"></span>
			</label>
		</td>
		<th colspan="6"></th>
		<td colspan="3">
			<label class="container" for="_rx_pathogen_3_" onclick="ToggleCheckbox(document.getElementById(\'_rx_pathogen_3_\'));">', _('Patho 3'), '
				<input type="checkbox"', $Checked3, 'id="_rx_pathogen_3_" name="_rx_pathogen_3_" />
				<span class="checkmark"></span>
			</label>
		</td>
	</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_1_s_', $ResistAerob)) {
		$Checked1S = ' checked="checked" ';
	} else {
		$Checked1S = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_1_m_', $ResistAerob)) {
		$Checked1M = ' checked="checked" ';
	} else {
		$Checked1M = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_1_r_', $ResistAerob)) {
		$Checked1R = ' checked="checked" ';
	} else {
		$Checked1R = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_2_s_', $ResistAerob)) {
		$Checked2S = ' checked="checked" ';
	} else {
		$Checked2S = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_2_m_', $ResistAerob)) {
		$Checked2M = ' checked="checked" ';
	} else {
		$Checked2M = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_2_r_', $ResistAerob)) {
		$Checked2R = ' checked="checked" ';
	} else {
		$Checked2R = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_3_s_', $ResistAerob)) {
		$Checked3S = ' checked="checked" ';
	} else {
		$Checked3S = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_3_m_', $ResistAerob)) {
		$Checked3M = ' checked="checked" ';
	} else {
		$Checked3M = ' ';
	}
	if (in_array('_ra_' . strtolower($MyRow['name']) . '_3_r_', $ResistAerob)) {
		$Checked3R = ' checked="checked" ';
	} else {
		$Checked3R = ' ';
	}

	echo '<tr>
			<th rowspan="5">', $MyRow['name'], '</th>
			<th>S</th>
			<th>M</th>
			<th>R</th>
			<td colspan="6"></td>
			<th>S</th>
			<th>M</th>
			<th>R</th>
			<td colspan="6"></td>
			<th>S</th>
			<th>M</th>
			<th>R</th>
		</tr>
		<tr>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_1_s_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_1_s_\'));">
					<input type="checkbox"', $Checked1S, 'id="_ra_', strtolower($MyRow['name']), '_1_s_" name="_ra_', strtolower($MyRow['name']), '_1_s_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_1_m_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_1_m_\'));">
					<input type="checkbox"', $Checked1M, 'id="_ra_', strtolower($MyRow['name']), '_1_m_" name="_ra_', strtolower($MyRow['name']), '_1_m_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_1_r_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_1_r_\'));">
					<input type="checkbox"', $Checked1R, 'id="_ra_', strtolower($MyRow['name']), '_1_r_" name="_ra_', strtolower($MyRow['name']), '_1_r_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td colspan="6"></td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_2_s_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_2_s_\'));">
					<input type="checkbox"', $Checked2S, 'id="_ra_', strtolower($MyRow['name']), '_2_s_" name="_ra_', strtolower($MyRow['name']), '_2_s_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_2_m_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_2_m_\'));">
					<input type="checkbox"', $Checked2M, 'id="_ra_', strtolower($MyRow['name']), '_2_m_" name="_ra_', strtolower($MyRow['name']), '_2_m_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_2_r_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_2_r_\'));">
					<input type="checkbox"', $Checked2R, 'id="_ra_', strtolower($MyRow['name']), '_2_r_" name="_ra_', strtolower($MyRow['name']), '_2_r_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td colspan="6"></td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_3_s_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_3_s_\'));">
					<input type="checkbox"', $Checked3S, 'id="_ra_', strtolower($MyRow['name']), '_3_s_" name="_ra_', strtolower($MyRow['name']), '_3_s_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_3_m_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_3_m_\'));">
					<input type="checkbox"', $Checked3M, 'id="_ra_', strtolower($MyRow['name']), '_3_m_" name="_ra_', strtolower($MyRow['name']), '_3_m_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_ra_', strtolower($MyRow['name']), '_3_r_" onclick="ToggleCheckbox(document.getElementById(\'_ra_', strtolower($MyRow['name']), '_3_r_\'));">
					<input type="checkbox"', $Checked3R, 'id="_ra_', strtolower($MyRow['name']), '_3_r_" name="_ra_', strtolower($MyRow['name']), '_3_r_" />
					<span class="checkmark"></span>
				</label>
			</td>
		</tr>
		<tr></tr>
		<tr></tr>
		<tr></tr>';
}

echo '</table>';

echo '</fieldset>
	</div>';

echo '<div class="baclab_resistance_test_fungi">
		<fieldset>';

$SQL = "SELECT nr, type, name FROM care_aerobic_resistance_extra ORDER BY nr";
$Result = DB_query($SQL);

echo '<table>';

if (in_array('_rx_fungus_', $ResistAerob)) {
	$Checked = ' checked="checked" ';
} else {
	$Checked = ' ';
}

echo '<tr>
		<td colspan="4">
			<label class="container" for="_rx_fungus_" onclick="ToggleCheckbox(document.getElementById(\'_rx_fungus_\'));">', _('Fungi'), '
				<input type="checkbox"', $Checked, 'id="_rx_fungus_" name="_rx_fungus_" />
				<span class="checkmark"></span>
			</label>
		</td>
	</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	if (in_array('_rx_' . $MyRow['type'] . '_s_', $ResistAerob)) {
		$Checked1 = ' checked="checked" ';
	} else {
		$Checked1 = ' ';
	}
	if (in_array('_rx_' . $MyRow['type'] . '_m_', $ResistAerob)) {
		$Checked2 = ' checked="checked" ';
	} else {
		$Checked2 = ' ';
	}
	if (in_array('_rx_' . $MyRow['type'] . '_r_', $ResistAerob)) {
		$Checked3 = ' checked="checked" ';
	} else {
		$Checked3 = ' ';
	}

	echo '<tr>
			<th rowspan="5">', $MyRow['name'], '</th>
			<th>S</th>
			<th>M</th>
			<th>R</th>
		</tr>
		<tr>
			<td><label class="container" for="_rx_', $MyRow['type'], '_s_" onclick="ToggleCheckbox(document.getElementById(\'_rx_', $MyRow['type'], '_s_\'));">
					<input type="checkbox"', $Checked1, 'id="_rx_', $MyRow['type'], '_s_" name="_rx_', $MyRow['type'], '_s_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_rx_', $MyRow['type'], '_m_" onclick="ToggleCheckbox(document.getElementById(\'_rx_', $MyRow['type'], '_m_\'));">
					<input type="checkbox"', $Checked2, 'id="_rx_', $MyRow['type'], '_m_" name="_rx_', $MyRow['type'], '_m_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="_rx_', $MyRow['type'], '_r_" onclick="ToggleCheckbox(document.getElementById(\'_rx_', $MyRow['type'], '_r_\'));">
					<input type="checkbox"', $Checked3, 'id="_rx_', $MyRow['type'], '_r_" name="_rx_', $MyRow['type'], '_r_" />
					<span class="checkmark"></span>
				</label>
			</td>
			<tr></tr>
			<tr></tr>
			<tr></tr>
			<tr></tr>
		</tr>';
}

echo '</table>';

echo '</fieldset>
	</div>';

echo '</div>';

echo '<div class="centre">
		<input type="submit" name="Save" value="', _('Save Data'), '" />
	</div>';

echo '</form>';

include ('includes/footer.php');

?>