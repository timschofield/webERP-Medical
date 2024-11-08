<?php
include ('includes/session.php');
$Title = _('Maintain Discharge Types');
include ('includes/header.php');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	if ($_POST['Type'] == '' or $_POST['Type'] == ' ' or $_POST['Type'] == '  ') {
		$InputError = 1;
		prnMsg(_('The discharge type code cannot be an empty string or spaces'), 'error');
	} elseif (trim($_POST['TypeName']) == '') {
		$InputError = 1;
		prnMsg(_('The discharge type description cannot be empty'), 'error');
	} elseif (mb_strlen($_POST['TypeName']) > 100) {
		$InputError = 1;
		echo prnMsg(_('The discharge type description must be one hundred characters or less long'), 'error');
	}

	if (isset($SelectedType) and $InputError != 1) {

		$SQL = "UPDATE care_type_discharge
					SET type='" . $_POST['Type'] . "',
						name='" . $_POST['TypeName'] . "'
					WHERE nr = '" . stripslashes($SelectedType) . "'";

		$Msg = _('The discharge type') . ' ' . stripslashes($SelectedType) . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		// First check the type is not being duplicated
		$CheckSql = "SELECT count(*)
				 FROM care_type_discharge
				 WHERE type = '" . $_POST['Type'] . "'";

		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The discharge type ') . $_POST['TypeAbbrev'] . _(' already exists.'), 'error');
		} else {

			// Add new record on submit
			$SQL = "INSERT INTO care_type_discharge (type,
													name)
												VALUES
													('" . str_replace(' ', '', $_POST['Type']) . "',
												'" . $_POST['TypeName'] . "')";
			$Msg = _('Discharge type') . ' ' . stripslashes($_POST['Type']) . ' ' . _('has been created');
		}
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);

		prnMsg($Msg, 'success');

		unset($SelectedType);
		unset($_POST['Type']);
		unset($_POST['TypeName']);
	}

} elseif (isset($_GET['delete'])) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions
	$SQL = "SELECT COUNT(*)
		   FROM care_encounter_location
		   WHERE discharge_type_nr='" . $SelectedType . "'";

	$ErrMsg = _('The number of occasions this type has been used could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this discharge type because patients have already been discharged with it'), 'error');

	} else {

		$SQL = "DELETE FROM care_type_discharge WHERE nr='" . $SelectedType . "'";
		$ErrMsg = _('The Discharge Type record could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('Discharge type') . stripslashes($SelectedType) . ' ' . _('has been deleted'), 'success');

		unset($SelectedType);
		unset($_GET['delete']);
	} //end if sales type used in debtor transactions or in customers set up
	
}

if (isset($_POST['Cancel'])) {
	unset($SelectedType);
	unset($_POST['Type']);
	unset($_POST['TypeName']);
}

if (!isset($SelectedType)) {

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Discharge Types'), '" alt="" />', ' ', $Title, '
		</p>';

	$SQL = "SELECT nr,
					type,
					name
				FROM care_type_discharge";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<div class="page_help_text">', _('As this is the first time that the system has been used, you must first create a discharge type.'), '<br />', _('Once you have filled in all the details, click on the button at the bottom of the screen'), '
			</div>';
		$_SESSION['RestrictLocations'] = 0;
	}

	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Type Code'), '</th>
					<th class="SortedColumn">', _('Type Name'), '</th>
					<th colspan="2"></th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['type'], '</td>
				<td>', $MyRow['name'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['nr']), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['nr']), '&delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this type?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {
	echo '<div class="toplink">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">', _('Show All Discharge Types Defined'), '</a>
		</div>';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
		</p>';
}

if (!isset($_GET['delete'])) {

	echo '<form name="DischargeTypesForm" method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" >';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	// The user wish to EDIT an existing type
	if (isset($SelectedType) and $SelectedType != '') {

		$SQL = "SELECT nr,
						type,
						name
				FROM care_type_discharge
				WHERE nr='" . $SelectedType . "'";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) == 0) {
			echo '<div class="page_help_text">', _('As this is the first time that the system has been used, you must first create a discharge type.'), '<br />', _('Once you have filled in all the details, click on the button at the bottom of the screen'), '
				</div>';
		}

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['Type'] = $MyRow['type'];
		$_POST['TypeName'] = $MyRow['name'];

		echo '<input type="hidden" name="SelectedType" value="', $SelectedType, '" />';
		echo '<input type="hidden" name="Type" value="', $_POST['Type'], '" />';
		echo '<fieldset>
				<legend>', _('Edit Discharge Type'), '</legend>
				<field>
					<label for="Type">', _('Type Code'), ':</label>
					<div class="fieldtext">', $_POST['Type'], '</div>
				</field>';

	} else {
		$_POST['TypeName'] = '';

		// This is a new type so the user may volunteer a type code
		echo '<fieldset>
				<legend>', _('Create New Discharge Type'), '</legend>
				<field>
					<label for="Type">', _('Type Code'), ':</label>
					<input type="text" required="required" autofocus="autofocus" name="Type" />
					<fieldhelp>', _('The code for this discharge type.'), '</fieldhelp>
				</field>';
	}

	if (!isset($_POST['Sales_Type'])) {
		$_POST['Sales_Type'] = '';
	}
	echo '<field>
			<label for="TypeName">', _('Discharge Type Name'), ':</label>
			<input type="text" required="required" autofocus="autofocus" size="40" name="TypeName" value="', $_POST['TypeName'], '" />
			<fieldhelp>', _('Description of this discharge type.'), '</fieldhelp>
		</field>';

	echo '</fieldset>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';

	echo '</form>';

} // end if user wish to delete
include ('includes/footer.php');
?>