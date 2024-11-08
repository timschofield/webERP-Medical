<?php
include ('includes/session.php');
$Title = _('Time Types') . ' / ' . _('Maintenance');
include ('includes/header.php');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Outcome Types'), '" alt="" />', _('Time Type Setup'), '
	</p>';
echo '<div class="page_help_text">', _('Add/edit/delete Time Types'), '</div>';

if (isset($_POST['Insert']) or isset($_POST['Update'])) {

	//initialise no input errors assumed initially before we time
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['TypeName']) > 100) {
		$InputError = 1;
		prnMsg(_('The time type name description must be 100 characters or less long'), 'error');
	}

	if (mb_strlen($_POST['TypeName']) == 0) {
		$InputError = 1;
		prnMsg(_('The time type name description must contain at least one character'), 'error');
	}

	if (isset($_POST['Update']) and $InputError != 1) {

		$SQL = "UPDATE care_type_time
				SET type = '" . $_POST['Type'] . "',
					name = '" . $_POST['TypeName'] . "',
					description = '" . $_POST['Description'] . "',
					status= '" . $_POST['Status'] . "',
					modify_id='" . $_SESSION['UserID'] . "'
				WHERE nr = '" . $SelectedType . "'";

		$Msg = _('The time type') . ' ' . $SelectedType . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		$CheckSql = "SELECT count(type)
						FROM care_type_time
						WHERE type = '" . $_POST['Type'] . "'";
		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);
		if ($CheckRow[0] > 0 and !isset($SelectedType)) {
			$InputError = 1;
			echo '<br />';
			prnMsg(_('You already have a time type called') . ' ' . $_POST['TypeName'], 'error');
		} else {

			// Add new record on submit
			$SQL = "INSERT INTO care_type_time (nr,
													type,
													name,
													description,
													status,
													create_id,
													create_time,
													modify_id,
													LD_var
												) VALUES (
													NULL,
													'" . $_POST['Type'] . "',
													'" . $_POST['TypeName'] . "',
													'" . $_POST['Description'] . "',
													'" . $_POST['Status'] . "',
													'" . $_SESSION['UserID'] . "',
													CURRENT_DATE,
													'" . $_SESSION['UserID'] . "',
													''
												)";
			$Msg = _('Time type') . ' ' . $_POST['TypeName'] . ' ' . _('has been created');
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
	$SQL = "DELETE FROM care_type_time WHERE nr='" . $SelectedType . "'";
	$ErrMsg = _('The Type record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<br />';
	prnMsg(_('The time type has been deleted'), 'success');

	unset($SelectedType);
	unset($_GET['delete']);

}

if (!isset($SelectedType)) {

	$SQL = "SELECT nr,
					type,
					name,
					description,
					status
				FROM care_type_time";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">', _('Type'), '</th>
						<th class="SortedColumn">', _('Type Name'), '</th>
						<th class="SortedColumn">', _('Description'), '</th>
						<th class="SortedColumn">', _('Status'), '</th>
						<th></th>
						<th></th>
					</tr>
				</thead>';

		$k = 0; //row colour counter
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>', $MyRow['type'], '</td>
					<td>', $MyRow['name'], '</td>
					<td>', $MyRow['description'], '</td>
					<td>', _($MyRow['status']), '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['type_nr']), '">' . _('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['type_nr']), '&amp;delete=yes" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this Time Type?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
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
						type,
						name,
						description,
						status
				FROM care_type_time
				WHERE nr='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['Type'] = $MyRow['type'];
		$_POST['TypeName'] = $MyRow['name'];
		$_POST['Description'] = $MyRow['description'];
		$_POST['Status'] = $MyRow['status'];

		echo '<input type="hidden" name="SelectedType" value="', $SelectedType, '" />';
		echo '<fieldset>
				<legend>', _('Edit Time Type'), ' - ', $MyRow['type'], '</legend>';

		echo '<field>
				<label for="TypeID">', _('Type ID'), ':</label>
				<div class="fieldtext">', $SelectedType, '</div>
			</field>';

	} else {
		$_POST['Type'] = '';
		$_POST['TypeName'] = '';
		$_POST['Description'] = '';
		$_POST['Status'] = _('Active');

		echo '<fieldset>
				<legend>', _('Create New Time Type'), '</legend>';
	}

	echo '<field>
			<label for="Type">', _('Type'), ':</label>
			<input type="text" name="Type" required="required" maxlength="60" size="50" value="', $_POST['Type'], '" />
		</field>';

	echo '<field>
			<label for="TypeName">', _('Type Name'), ':</label>
			<input type="text" name="TypeName" required="required" maxlength="60" size="50" value="', $_POST['TypeName'], '" />
		</field>';

	echo '<field>
			<label for="Description">', _('Description'), ':</label>
			<input type="text" name="Description" required="required" maxlength="255" size="100" value="', $_POST['Description'], '" />
		</field>';

	echo '<field>
			<label for="Status">', _('Status'), '</label>
			<select name="Status">';
	if ($_POST['Status'] == 'Active') {
		echo '<option value="Active" selected="selected">', _('Active'), '</option>';
		echo '<option value="InActive">', _('Inactive'), '</option>';
	} else {
		echo '<option value="Active">', _('Active'), '</option>';
		echo '<option value="InActive" selected="selected">', _('Inactive'), '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>'; // close main table
	if (isset($SelectedType) and $SelectedType != '') {
		echo '<div class="centre">
				<input type="submit" name="Update" value="', _('Update Time Type'), '" />
			</div>';
	} else {
		echo '<div class="centre">
				<input type="submit" name="Insert" value="', _('Create Time Type'), '" />
			</div>';
	}

	echo '</form>';

} // end if user wish to delete
include ('includes/footer.php');
?>