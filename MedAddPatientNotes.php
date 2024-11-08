<?php
include ('includes/session.php');
$Title = _('Patient Records');
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/CustomerSearch.php');

if (isset($_POST['DebtorNo'])) {
	$Patient[0] = $_POST['DebtorNo'];
} elseif (isset($_GET['Debtor'])) {
	$Patient[0] = stripslashes($_GET['Debtor']);
}

if (!isset($Patient)) {
	ShowCustomerSearchFields($RootPath, $_SESSION['Theme']);
}

if (isset($_POST['Search']) or isset($_POST['Go1']) or isset($_POST['Go2']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

	$PatientResult = CustomerSearchSQL();
	if (DB_num_rows($PatientResult) == 0) {
		prnMsg(_('No patient records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search
if (isset($PatientResult)) {
	ShowReturnedCustomers($PatientResult);
}

if (isset($Patient)) {
	echo '<div class="toplink"><a href="' . $RootPath . '/KCMCAddPatientNotes.php">' . _('Select Another Patient') . '</a></div>';

	if (isset($_POST['submit'])) {

		//initialise no input errors assumed initially before we test
		$InputError = 0;
		/* actions to take once the user has clicked the submit button
		 ie the page has called itself with some user input */

		//first off validate inputs sensible
		if (!is_long((integer)$_POST['Priority'])) {
			$InputError = 1;
			prnMsg(_('The contact priority must be an integer.'), 'error');
		} elseif (mb_strlen($_POST['Note']) > 200) {
			$InputError = 1;
			prnMsg(_('The contact\'s notes must be two hundred characters or less long'), 'error');
		} elseif (trim($_POST['Note']) == '') {
			$InputError = 1;
			prnMsg(_('The contact\'s notes may not be empty'), 'error');
		}

		if (isset($Id) and $InputError != 1) {

			$SQL = "UPDATE custnotes SET note='" . $_POST['Note'] . "',
									date='" . FormatDateForSQL($_POST['NoteDate']) . "',
									priority='" . $_POST['Priority'] . "'
				WHERE debtorno ='" . $Patient[0] . "'
				AND noteid='" . $Id . "'";
			$Msg = _('Customer Notes') . ' ' . $Patient[0] . ' ' . _('has been updated');
		} elseif ($InputError != 1) {

			$SQL = "INSERT INTO custnotes (debtorno,
											userid,
											note,
											date,
											priority)
										VALUES (
											'" . $_POST['DebtorNo'] . "',
											'" . $_SESSION['UserID'] . "',
											'" . $_POST['Note'] . "',
											'" . FormatDateForSQL($_POST['NoteDate']) . "',
											'" . $_POST['Priority'] . "')";
			$Msg = _('The contact notes record has been added');
		}

		if ($InputError != 1) {
			$Result = DB_query($SQL);

			prnMsg($Msg, 'success');
			unset($Id);
			unset($_POST['Note']);
			unset($_POST['Noteid']);
			unset($_POST['NoteDate']);
			unset($_POST['Priority']);
		}
	} elseif (isset($_GET['delete'])) {
		//the link to delete a selected record was clicked instead of the submit button
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'
		$SQL = "DELETE FROM custnotes
			WHERE noteid='" . $Id . "'
			AND debtorno='" . $Patient[0] . "'";
		$Result = DB_query($SQL);

		prnMsg(_('The contact note record has been deleted'), 'success');
		unset($Id);
		unset($_GET['delete']);
	}

	if (!isset($PageNumber) and !isset($_GET['New'])) {
		// There is no page selected yet and not creating a new one
		$SQL = "SELECT patientrecord.id,
						www_users.realname,
						patientrecord.doctor,
						patientrecord.creationdate,
						patientrecord.record
					FROM patientrecord
					INNER JOIN www_users
					ON patientrecord.createdby=www_users.userid
					WHERE patientrecord.debtorno='" . $Patient[0] . "'";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) > 0) {
			echo '<table>
					<tr>
						<th>' . _('Record Id') . '</th>
						<th>' . _('Created By') . '</th>
						<th>' . _('Created On') . '</th>
						<th>' . _('Doctor') . '</th>
					</tr>';

			while ($MyRow = DB_fetch_array($Result)) {
				echo '<tr title="' . $MyRow['record'] . '">
						<td>' . $MyRow['id'] . '</td>
						<td>' . $MyRow['realname'] . '</td>
						<td>' . ConvertSQLDate($MyRow['creationdate']) . '</td>
						<td>' . $MyRow['doctor'] . '</td>
						<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=Yes&Id=' . $MyRow['id'] . '">' . _('Edit') . '</a></td>
						<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=Yes&Id=' . $MyRow['id'] . '">' . _('Delete') . '</a></td>
					</tr>';
			}
			echo '</table>';
		} else {
			echo '<div class="centre">' . _('There are no records setup for this patient.') . '</div>';
		}
		echo '<div class="centre">' . _('Click') . '&nbsp;' . '<a class="ButtonLink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?New=Yes&Debtor=' . $Patient[0] . '">' . _('here') . '</a>' . ' ' . _('to create a new record') . '</div>';
	} else {
		echo '<table style="width:95%;">
				<tr>';
		if (!isset($_GET['New'])) {
			//We are editing an old record
			$SQL = "SELECT patientrecord.debtorno,
							debtorsmaster.name,
							www_users.realname,
							patientrecord.createdby,
							patientrecord.doctor,
							patientrecord.creationdate,
							patientrecord.record
						FROM patientrecord
						INNER JOIN www_users
							ON patientrecord.createdby=www_users.userid
						INNER JOIN debtorsmaster
							ON patientrecord.debtorno=debtorsmaster.debtorno
						WHERE patientrecord.id='" . $PageNumber . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			$_POST['PatientNumber'] = $MyRow['debtorno'];
			$_POST['Name'] = $MyRow['realname'];
			$_POST['CreatedBy'] = $MyRow['createdby'];
			$_POST['Doctor'] = $MyRow['doctor'];
			$_POST['CreationDate'] = ConvertSQLDate($MyRow['creationdate']);
			$_POST['PatientRecord'] = $MyRow['record'];
		} else {
			// It's a new record
			$SQL = "SELECT name FROM debtorsmaster WHERE debtorno='" . $Patient[0] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$_POST['PatientNumber'] = '';
			$_POST['Name'] = stripslashes($_SESSION['UsersRealName']);
			$_POST['CreatedBy'] = $_SESSION['UserID'];
			$_POST['Doctor'] = '';
			$_POST['CreationDate'] = date($_SESSION['DefaultDateFormat']);
			$_POST['PatientRecord'] = '';
			$NotesSQL = "SELECT id, title FROM standardnotes";
			$NotesResult = DB_query($NotesSQL);
			echo '<td style="width:30%;vertical-align:top;padding:20px;">
					<table>
						<tr>
							<td style="width:50%">' . _('Patients Name') . ': </td>
							<td style="width:50%">' . $MyRow['name'] . '</td>
							<td></td>
						</tr>
						<tr>
							<td style="width:50%">' . _('Created') . ': </td>
							<td style="width:50%">' . $_POST['CreationDate'] . '</td>
							<td></td>
						</tr>';
			echo '<tr>
					<td>' . _('Select a Note') . '</td>
					<td>
						<select name="Notes">';
			while ($MyNotesRow = DB_fetch_array($NotesResult)) {
				echo '<option name="' . $MyNotesRow['id'] . '">' . $MyNotesRow['title'] . '</option>';
			}
			echo '</select>
						</td>
						<td><input type="submit" name="AddNote" value="Add to report" /></td>
					</tr>';
			echo '</table>
				</td>';
		}
		echo '<td style="width:65%;padding:20px;">
				<textarea name="Note" rows="25" cols="110" style="width:100%;">' . $_POST['Note'] . '</textarea>
			</td>';
		echo '</tr>
		</table>';
	}
}
include ('includes/footer.php');
?>