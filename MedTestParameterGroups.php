<?php
include ('includes/session.php');
$Title = _('Test Groups') . ' / ' . _('Maintenance');
include ('includes/header.php');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Test Groups'), '" alt="" />', _('Test Groups'), '
	</p>';
echo '<div class="page_help_text">', _('Add/edit/delete Test Groups'), '</div>';

if (isset($_POST['Insert']) or isset($_POST['Update'])) {

	//initialise no input errors assumed initially before we tmeasurement
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['GroupName']) > 100) {
		$InputError = 1;
		prnMsg(_('The test group type name description must be 100 characters or less long'), 'error');
	}

	if (mb_strlen($_POST['GroupName']) == 0) {
		$InputError = 1;
		prnMsg(_('The test group name description must contain at least one character'), 'error');
	}

	if (isset($_POST['Update']) and $InputError != 1) {

		$SQL = "UPDATE care_test_param
				SET id = '" . $_POST['GroupID'] . "',
					name = '" . $_POST['GroupName'] . "',
					sort_nr = '" . $_POST['Sequence'] . "',
					modify_id='" . $_SESSION['UserID'] . "'
				WHERE nr = '" . $SelectedType . "'";

		$Msg = _('The test group') . ' ' . $SelectedType . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		$CheckSql = "SELECT count(id)
						FROM care_test_param
						WHERE id = '" . $_POST['GroupID'] . "'";
		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);
		if ($CheckRow[0] > 0 and !isset($SelectedType)) {
			$InputError = 1;
			echo '<br />';
			prnMsg(_('You already have a test group type called') . ' ' . $_POST['TypeName'], 'error');
		} else {

			// Add new record on submit
			$SQL = "INSERT INTO care_test_param (nr,
												group_id,
												name,
												id,
												sort_nr,
												msr_unit,
												status,
												median,
												hi_bound,
												lo_bound,
												hi_critical,
												lo_critical,
												hi_toxic,
												lo_toxic,
												median_f,
												hi_bound_f,
												lo_bound_f,
												hi_critical_f,
												lo_critical_f,
												hi_toxic_f,
												lo_toxic_f,
												median_n,
												hi_bound_n,
												lo_bound_n,
												hi_critical_n,
												lo_critical_n,
												hi_toxic_n,
												lo_toxic_n,
												median_y,
												hi_bound_y,
												lo_bound_y,
												hi_critical_y,
												lo_critical_y,
												hi_toxic_y,
												lo_toxic_y,
												median_c,
												hi_bound_c,
												lo_bound_c,
												hi_critical_c,
												lo_critical_c,
												hi_toxic_c,
												lo_toxic_c,
												method,
												history,
												create_id,
												create_time,
												modify_id
											) VALUES (
												NULL,
												'-1',
												'" . $_POST['GroupName'] . "',
												'" . $_POST['GroupID'] . "',
												'" . $_POST['Sequence'] . "',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'',
												'" . $_SESSION['UserID'] . "',
												CURRENT_DATE,
												'" . $_SESSION['UserID'] . "'
											)";
			$Msg = _('Test Group') . ' ' . $_POST['GroupName'] . ' ' . _('has been created');
		}

	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');

		unset($SelectedType);
		unset($_POST['typeid']);
		unset($_POST['TypeName']);
	}

} elseif (isset($_GET['delete'])) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions
	$SQL = "DELETE FROM care_test_param WHERE nr='" . $SelectedType . "'";
	$ErrMsg = _('The Type record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<br />';
	prnMsg(_('The test group has been deleted'), 'success');

	unset($SelectedType);
	unset($_GET['delete']);

}

if (!isset($SelectedType)) {

	$SQL = "SELECT nr,
					name,
					id,
					sort_nr
				FROM care_test_param
				WHERE group_id='-1'
				ORDER BY sort_nr";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">', _('Sequence'), '</th>
						<th class="SortedColumn">', _('Group ID'), '</th>
						<th class="SortedColumn">', _('Group Name'), '</th>
						<th></th>
						<th></th>
					</tr>
				</thead>';

		$k = 0; //row colour counter
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>', $MyRow['sort_nr'], '</td>
					<td>', $MyRow['id'], '</td>
					<td>', $MyRow['name'], '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['nr']), '">' . _('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['nr']), '&amp;delete=yes" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this Test Group?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
				</tr>';
		}
		//END WHILE LIST LOOP
		echo '</tbody>';
		echo '</table>';
	}
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	// The user wish to EDIT an existing type
	if (isset($SelectedType) and $SelectedType != '') {

		$SQL = "SELECT nr,
						name,
						id,
						sort_nr
				FROM care_test_param
				WHERE nr='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['GroupID'] = $MyRow['id'];
		$_POST['GroupName'] = $MyRow['name'];
		$_POST['Sequence'] = $MyRow['sort_nr'];

		echo '<input type="hidden" name="SelectedType" value="', $SelectedType, '" />';
		echo '<fieldset>
				<legend>', _('Edit Test Group'), ' - ', $MyRow['name'], '</legend>';

		echo '<field>
				<label for="Nr">', _('Group Number'), ':</label>
				<div class="fieldtext">', $SelectedType, '</div>
			</field>';

	} else {
		$_POST['GroupID'] = '';
		$_POST['GroupName'] = '';
		$_POST['Sequence'] = 0;

		echo '<fieldset>
				<legend>', _('Create New Test Group'), '</legend>';
	}

	echo '<field>
			<label for="Sequence">', _('Sequence'), ':</label>
			<input type="text" name="Sequence" required="required" class="number" maxlength="60" size="10" value="', $_POST['Sequence'], '" />
		</field>';

	echo '<field>
			<label for="GroupID">', _('Group ID'), ':</label>
			<input type="text" name="GroupID" required="required" maxlength="60" size="50" value="', $_POST['GroupID'], '" />
		</field>';

	echo '<field>
			<label for="GroupName">', _('Group Name'), ':</label>
			<input type="text" name="GroupName" required="required" maxlength="60" size="50" value="', $_POST['GroupName'], '" />
		</field>';

	echo '</fieldset>'; // close main table
	if (isset($SelectedType) and $SelectedType != '') {
		echo '<div class="centre">
				<input type="submit" name="Update" value="', _('Update Test Group'), '" />
			</div>';
	} else {
		echo '<div class="centre">
				<input type="submit" name="Insert" value="', _('Create Test Group'), '" />
			</div>';
	}

	echo '</form>';

} // end if user wish to delete
include ('includes/footer.php');
?>