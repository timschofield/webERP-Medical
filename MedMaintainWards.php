<?php
include ('includes/session.php');

$Title = _('Hospital Ward Configuration');

include ('includes/header.php');

if (isset($_POST['SelectedWard'])) {
	$SelectedWard = $_POST['SelectedWard'];
} elseif (isset($_GET['SelectedWard'])) {
	$SelectedWard = $_GET['SelectedWard'];
}

echo '<div class="toplink"><a href="' . $RootPath . '/MedWardOverview.php?SelectedWard=', $SelectedWard, '">' . _('Ward Overview Screen') . '</a></div>';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/hospital.png" title="', _('Hospital Ward Configuration'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['insert']) or isset($_POST['update'])) {
	$InputError = 0;

	if (!isset($_POST['WardID']) or $_POST['WardID'] == '') {
		$InputError = 1;
		prnMsg(_('The ward ID cannot be empty. Please enter a correct ID.'), 'error');
	}

	if (!isset($_POST['WardID']) or mb_strlen($_POST['WardID']) > 35) {
		$InputError = 1;
		prnMsg(_('The ward ID cannot be more than 35 characters long.'), 'error');
	}

	if (!isset($_POST['WardName']) or $_POST['WardName'] == '') {
		$InputError = 1;
		prnMsg(_('The ward name cannot be empty. Please enter a correct name.'), 'error');
	}

	if (!isset($_POST['WardName']) or mb_strlen($_POST['WardName']) > 35) {
		$InputError = 1;
		prnMsg(_('The ward name cannot be more than 35 characters long.'), 'error');
	}

	if (!isset($_POST['FirstRoomNumber']) or $_POST['FirstRoomNumber'] == '') {
		$InputError = 1;
		prnMsg(_('The first room number cannot be empty. Please enter a correct number.'), 'error');
	}

	if (!is_int((int)$_POST['FirstRoomNumber'])) {
		$InputError = 1;
		prnMsg(_('The room numbers must be integers.'), 'error');
	}

	if (!isset($_POST['LastRoomNumber']) or $_POST['LastRoomNumber'] == '') {
		$InputError = 1;
		prnMsg(_('The last room number cannot be empty. Please enter a correct number.'), 'error');
	}

	if (!is_int((int)$_POST['LastRoomNumber'])) {
		$InputError = 1;
		prnMsg(_('The room numbers must be integers.'), 'error');
	}

	if ($InputError == 0) {
		if (isset($_POST['insert'])) {
			$Result = DB_Txn_Begin();

			$SQL = "INSERT INTO care_ward (ward_id,
											name,
											date_create,
											description,
											dept_nr,
											room_nr_start,
											room_nr_end,
											roomprefix,
											status,
											modify_id,
											modify_time,
											create_id,
											create_time
										) VALUES (
											'" . $_POST['WardID'] . "',
											'" . $_POST['WardName'] . "',
											CURRENT_DATE(),
											'" . $_POST['Description'] . "',
											'" . $_POST['Department'] . "',
											'" . $_POST['FirstRoomNumber'] . "',
											'" . $_POST['LastRoomNumber'] . "',
											'" . $_POST['RoomPrefix'] . "',
											'" . $_POST['Status'] . "',
											'" . $_SESSION['UserID'] . "',
											NOW(),
											'" . $_SESSION['UserID'] . "',
											NOW()
										)";
			$ErrMsg = _('There was a problem inserting the ward record because');
			$DbgMsg = _('The SQL used to insert the ward record was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$WardNo = DB_Last_Insert_ID('care_ward', 'nr');

			for ($Room = $_POST['FirstRoomNumber'];$Room <= $_POST['LastRoomNumber'];$Room++) {
				$SQL = "INSERT INTO care_room (type_nr,
												date_create,
												room_nr,
												ward_nr,
												dept_nr,
												nr_of_beds,
												modify_id,
												modify_time,
												create_id,
												create_time
											) VALUES (
												2,
												CURRENT_DATE,
												'" . $Room . "',
												'" . $WardNo . "',
												'" . $_POST['Department'] . "',
												1,
												'" . $_SESSION['UserID'] . "',
												NOW(),
												'" . $_SESSION['UserID'] . "',
												NOW()
											)";
				$ErrMsg = _('There was a problem inserting the room record because');
				$DbgMsg = _('The SQL used to insert the room record was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
			$Result = DB_Txn_Commit();
			prnMsg(_('The ward and its rooms have been successfully created'), 'success');
		} elseif (isset($_POST['update'])) {
			//Get room number information
			$SQL = "SELECT room_nr_start, room_nr_end FROM care_ward WHERE nr='" . $SelectedWard . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$OldStartNumber = $MyRow['room_nr_start'];
			$OldEndNumber = $MyRow['room_nr_end'];

			$Result = DB_Txn_Begin();

			$SQL = "UPDATE care_ward SET ward_id='" . $_POST['WardID'] . "',
										 name='" . $_POST['WardName'] . "',
										 description='" . $_POST['Description'] . "',
										 dept_nr='" . $_POST['Department'] . "',
										 room_nr_start='" . $_POST['FirstRoomNumber'] . "',
										 room_nr_end='" . $_POST['LastRoomNumber'] . "',
										 roomprefix='" . $_POST['RoomPrefix'] . "',
										 status='" . $_POST['Status'] . "',
										 modify_id='" . $_SESSION['UserID'] . "',
										 modify_time=NOW()
									WHERE nr=" . $SelectedWard . "";
			$ErrMsg = _('There was a problem updating the ward record because');
			$DbgMsg = _('The SQL used to update the ward record was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			if ($OldStartNumber > $_POST['FirstRoomNumber']) {
				//There are rooms to add at the start
				for ($Room = $_POST['FirstRoomNumber'];$Room < $OldStartNumber;$Room++) {
					$SQL = "INSERT INTO care_room (type_nr,
													date_create,
													room_nr,
													ward_nr,
													dept_nr,
													nr_of_beds,
													modify_id,
													modify_time,
													create_id,
													create_time
												) VALUES (
													2,
													CURRENT_DATE,
													'" . $Room . "',
													'" . $SelectedWard . "',
													'" . $_POST['Department'] . "',
													1,
													'" . $_SESSION['UserID'] . "',
													NOW(),
													'" . $_SESSION['UserID'] . "',
													NOW()
												)";
					$ErrMsg = _('There was a problem inserting the room record because');
					$DbgMsg = _('The SQL used to insert the room record was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
			}
			if ($OldStartNumber < $_POST['FirstRoomNumber']) {
				//There are rooms to delete at the start
				for ($Room = $OldStartNumber;$Room < $_POST['FirstRoomNumber'];$Room++) {
					$SQL = "DELETE FROM care_room WHERE room_nr='" . $Room . "' AND ward_nr='" . $SelectedWard . "'";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
			}
			if ($OldEndNumber > $_POST['LastRoomNumber']) {
				//There are rooms to delete at the end
				for ($Room = $_POST['LastRoomNumber'] + 1;$Room <= $OldEndNumber;$Room++) {
					$SQL = "DELETE FROM care_room WHERE room_nr='" . $Room . "' AND ward_nr='" . $SelectedWard . "'";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
			}
			if ($OldEndNumber < $_POST['LastRoomNumber']) {
				//There are rooms to add at the end
				for ($Room = $OldEndNumber + 1;$Room <= $_POST['LastRoomNumber'];$Room++) {
					$SQL = "INSERT INTO care_room (type_nr,
													date_create,
													room_nr,
													ward_nr,
													dept_nr,
													nr_of_beds,
													modify_id,
													modify_time,
													create_id,
													create_time
												) VALUES (
													2,
													CURRENT_DATE,
													'" . $Room . "',
													'" . $SelectedWard . "',
													'" . $_POST['Department'] . "',
													1,
													'" . $_SESSION['UserID'] . "',
													NOW(),
													'" . $_SESSION['UserID'] . "',
													NOW()
												)";
					$ErrMsg = _('There was a problem inserting the room record because');
					$DbgMsg = _('The SQL used to insert the room record was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
			}
			$Result = DB_Txn_Commit();
			prnMsg(_('The ward and its rooms have been successfully updated'), 'success');
		}
		unset($SelectedWard);
	}
}

if (!isset($SelectedWard)) {
	$SQL = "SELECT nr,
					ward_id,
					name,
					status,
					care_ward.description as ward_description,
					dept_nr,
					departments.description,
					room_nr_start,
					room_nr_end,
					roomprefix
				FROM care_ward
				INNER JOIN departments
					ON care_ward.dept_nr=departments.departmentid";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<table>
				<tr>
					<th>', _('Ward ID'), '</th>
					<th>', _('Name'), '</th>
					<th>', _('Description'), '</th>
					<th>', _('Status'), '</th>
					<th>', _('Department'), '</th>
					<th>', _('Room Numbers'), '</th>
					<th></th>
					<th></th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['status'] == '0') {
				$MyRow['status'] = _('Open');
			} else {
				$MyRow['status'] = _('Closed');
			}
			echo '<tr class="striped_row">
					<td>', $MyRow['ward_id'], '</td>
					<td>', $MyRow['name'], '</td>
					<td>', $MyRow['ward_description'], '</td>
					<td>', $MyRow['status'], '</td>
					<td>', $MyRow['dept_nr'], ' - ', $MyRow['description'], '</td>
					<td>', $MyRow['roomprefix'], $MyRow['room_nr_start'], ' ', _('to'), ' ', $MyRow['roomprefix'], $MyRow['room_nr_end'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedWard=', urlencode($MyRow['nr']), '">', _('Edit'), '</a></td>
					<td><a href="MedMaintainWardRooms.php?SelectedWard=', urlencode($MyRow['nr']), '">', _('Maintain Rooms'), '</a></td>
				</tr>';
		}
		echo '</table>';
	}
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($SelectedWard)) {
	$SQL = "SELECT nr,
					ward_id,
					name,
					status,
					description,
					dept_nr,
					room_nr_start,
					room_nr_end,
					roomprefix
				FROM care_ward";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['WardID'] = $MyRow['ward_id'];
	$_POST['WardName'] = $MyRow['name'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['Department'] = $MyRow['dept_nr'];
	$_POST['FirstRoomNumber'] = $MyRow['room_nr_start'];
	$_POST['LastRoomNumber'] = $MyRow['room_nr_end'];
	$_POST['RoomPrefix'] = $MyRow['roomprefix'];
	$_POST['Status'] = $MyRow['status'];
	echo '<input type="hidden" name="SelectedWard" value="', $SelectedWard, '" />';
	echo '<input type="hidden" name="WardID" value="', $_POST['WardID'], '" />';

	echo '<fieldset>
			<legend>', _('Edit New Ward Details'), '</legend>
			<field>
				<label for="WardID">', _('Ward ID'), '</label>
				<div class="fieldtext">', $_POST['WardID'], '</div/>
			</field>';
} else {
	$_POST['WardID'] = '';
	$_POST['WardName'] = '';
	$_POST['Description'] = '';
	$_POST['FirstRoomNumber'] = 1;
	$_POST['LastRoomNumber'] = 1;
	$_POST['RoomPrefix'] = '';

	echo '<fieldset>
			<legend>', _('Create New Ward Details'), '</legend>
			<field>
				<label for="WardID">', _('Ward ID'), '</label>
				<input type="text" size="10" maxlength="35" name="WardID" value="', $_POST['WardID'], '" />
				<fieldhelp>', _('A unique ID by which this wrd is referenced'), '</fieldhelp>
			</field>';
}

echo '<field>
		<label for="WardName">', _('Ward Name'), '</label>
		<input type="text" size="35" maxlength="35" name="WardName" value="', $_POST['WardName'], '" />
		<fieldhelp>', _('The name of this ward.'), '</fieldhelp>
	</field>';

$SQL = "SELECT departmentid, description FROM departments WHERE medical=1";
$Result = DB_query($SQL);
echo '<field>
		<label for="Department">', _('Department'), '</label>
		<select name="Department">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Department']) and $_POST['Department'] == $MyRow['departmentid']) {
		echo '<option selected="selected" value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
	} else {
		echo '<option value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('The department that this ward belongs to.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="Description">', _('Description'), ':</label>
		<textarea name="Description" rows="6" cols="40">', $_POST['Description'], '</textarea>
		<fieldhelp>', _('A detailed description of this ward'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="FirstRoomNumber">', _('Room number of the first room'), '</label>
		<input type="text" class="number" size="6" name="FirstRoomNumber" value="', $_POST['FirstRoomNumber'], '" />
		<fieldhelp>', _('The name of this ward.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="LastRoomNumber">', _('Room number of the last room'), '</label>
		<input type="text" class="number" size="6" name="LastRoomNumber" value="', $_POST['LastRoomNumber'], '" />
		<fieldhelp>', _('The name of this ward.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="RoomPrefix">', _('Room prefix'), '</label>
		<input type="text" size="6" maxlength="4" name="RoomPrefix" value="', $_POST['RoomPrefix'], '" />
		<fieldhelp>', _('The prefix to come before the room number. A maximum of 4 characters may be used.'), '</fieldhelp>
	</field>';

$Status = array('0' => _('Open'), '1' => _('Closed'));
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

echo '</fieldset>';

if (isset($SelectedWard)) {
	echo '<div class="centre">
			<input type="submit" name="update" value="', _('Amend the ward details'), '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="insert" value="', _('Create the ward'), '" />
		</div>';
}

include ('includes/footer.php');
?>