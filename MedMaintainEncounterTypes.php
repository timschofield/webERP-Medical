<?php
include ('includes/session.php');
$Title = _('Maintain Encounter Types');
include ('includes/header.php');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', $Title, '" alt="" />', $Title, '
	</p>';

if (isset($_POST['insert']) or isset($_POST['update'])) {
	$InputError = 0;

	if (mb_strlen($_POST['Name']) == 0) {
		prnMsg(_('The encounter type name cannot be empty'), 'error');
		$InputError = 1;
	}

	if (mb_strlen($_POST['Description']) == 0) {
		prnMsg(_('The encounter type Description cannot be empty'), 'error');
		$InputError = 1;
	}

	if ($InputError == 1) {
		prnMsg(_('The data cannot be input as there are errors. Please correct these errors and try again'), 'warn');
	} else {
		if (isset($_POST['insert'])) {
			$SQL = "INSERT INTO care_type_encounter (type,
													 name,
													 description,
													 status,
													 modify_id,
													 modify_time,
													 create_id,
													 create_time
												) VALUES (
													 '" . mb_strtolower($_POST['Name']) . "',
													 '" . $_POST['Name'] . "',
													 '" . $_POST['Description'] . "',
													 '" . $_POST['Status'] . "',
													 '" . $_SESSION['UserID'] . "',
													 NOW(),
													 '" . $_SESSION['UserID'] . "',
													 NOW()
												)";
			$ErrMsg = _('There was a problem inserting the encounter type record because');
			$DbgMsg = _('The SQL used to insert the encounter type record was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The encounter type was successfully created'), 'success');
		} elseif (isset($_POST['update'])) {
			$SQL = "UPDATE care_type_encounter SET type='" . mb_strtolower($_POST['Name']) . "',
													name='" . $_POST['Name'] . "',
													description='" . $_POST['Description'] . "',
													status='" . $_POST['Status'] . "',
													modify_id='" . $_SESSION['UserID'] . "',
													modify_time=NOW()
												WHERE type_nr='" . $SelectedType . "'";
			$ErrMsg = _('There was a problem updating the encounter type record because');
			$DbgMsg = _('The SQL used to update the encounter type record was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The encounter type was successfully updated'), 'success');
		}
		unset($SelectedType);
	}
} elseif (isset($_GET['delete'])) {

}

if (!isset($SelectedType)) {
	$SQL = "SELECT type_nr,
					name,
					description,
					status
				FROM care_type_encounter";
	$Result = DB_query($SQL);

	echo '<table>
			<tr>
				<th>', _('Type ID'), '</th>
				<th>', _('Name'), '</th>
				<th>', _('Description'), '</th>
				<th>', _('Status'), '</th>
				<th></th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['status'] == '0') {
			$MyRow['status'] = _('Active');
		} else {
			$MyRow['status'] = _('InActive');
		}
		echo '<tr class="striped_row">
				<td>', $MyRow['type_nr'], '</td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['description'], '</td>
				<td>', $MyRow['status'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedType=', urlencode($MyRow['type_nr']), '">', _('Edit'), '</a></td>
			</tr>';
	}
	echo '</table>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" >';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	// The user wish to EDIT an existing type
	if (isset($SelectedType) and $SelectedType != '') {

		$SQL = "SELECT type_nr,
						name,
						description,
						status
					FROM care_type_encounter
					WHERE type_nr='" . $SelectedType . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['Name'] = $MyRow['name'];
		$_POST['Description'] = $MyRow['description'];
		$_POST['Status'] = $MyRow['status'];

		echo '<input type="hidden" name="SelectedType" value="', $SelectedType, '" />';
		echo '<fieldset>
				<legend>', _('Edit Encounter Type'), '</legend>';

	} else {
		$_POST['Name'] = '';
		$_POST['Description'] = '';
		$_POST['Status'] = '0';
		// This is a new type so the user may volunteer a type code
		echo '<fieldset>
				<legend>', _('Create New Encounter Type'), '</legend>';
	}

	echo '<field>
			<label for="Name">', _('Type Name'), '</label>
			<input type="text" autofocus="autofocus" size="20" name="Name" value="', $_POST['Name'], '" />
			<fieldhelp</fieldhelp>
		</field>';

	echo '<field>
			<label for="Description">', _('Description'), '</label>
			<input type="text" size="100" name="Description" value="', $_POST['Description'], '" />
			<fieldhelp</fieldhelp>
		</field>';

	$Status = array('0' => _('Active'), '1' => _('Inactive'));
	echo '<field>
		<label for="Status">', _('Status'), ':</label>
		<select name="Status">';
	foreach ($Status as $Key => $Value) {
		if ($Key == $_POST['Status']) {
			echo '<option selected="selected" value="', $Key, '">', $Value, '</option>';
		} else {
			echo '<option value="', $Key, '">', $Value, '</option>';
		}
	}
	echo '</select>
	<fieldhelp>', _('If this encounter type is currently in use select Active otherwise select InActive.'), '</fieldhelp>
</field>';

	echo '</fieldset>'; // close main table
	if (!isset($SelectedType)) {
		echo '<div class="centre">
			<input type="submit" name="insert" value="', _('Create New Type'), '" />
		</div>';
	} else {
		echo '<div class="centre">
			<input type="submit" name="update" value="', _('Update Type'), '" />
		</div>';
	}

	echo '</form>';

} // end if user wish to delete
include ('includes/footer.php');
?>