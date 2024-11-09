<?php
include ('includes/session.php');

if (isset($_POST['SelectedWard'])) {
	$SelectedWard = $_POST['SelectedWard'];
} elseif (isset($_GET['SelectedWard'])) {
	$SelectedWard = $_GET['SelectedWard'];
} else {
	$Title = _('This script can only be called with a ward ID as reference');
	include ('includes/header.php');
	prnMsg(_('This script can only be called with a ward ID as reference'), 'info');
	include ('includes/footer.php');
	exit;
}

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
				ON care_ward.dept_nr=departments.departmentid
			WHERE nr='" . $SelectedWard . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

$Title = _('Configure rooms for') . ' ' . $MyRow['name'];

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/hospital.png" title="', _('Maintain Rooms'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['update'])) {
	foreach ($_POST as $key => $value) {
		if (mb_substr($key, 0, 11) == 'BedsForRoom') {
			$Room = mb_substr($key, 11);
			$SQL = "UPDATE care_room SET nr_of_beds='" . $value . "'
					WHERE ward_nr='" . $SelectedWard . "'
						AND room_nr='" . $Room . "'";
			DB_query($SQL);
		}
	}
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<input type="hidden" name="SelectedWard" value="', $SelectedWard, '" />';

echo '<fieldset>
		<legend>', _('Configure number of beds per room'), '</legend>
		<field>
			<label for="WardID">', _('Ward ID'), ':</label>
			<div class="fieldtext">', $MyRow['ward_id'], '</div>
		</field>
		<field>
			<label for="WardName">', _('Ward Name'), ':</label>
			<div class="fieldtext">', $MyRow['name'], '</div>
		</field>
		<field>
			<label for="Description">', _('Description'), ':</label>
			<div class="fieldtext">', $MyRow['ward_description'], '</div>
		</field>
		<field>
			<label for="Department">', _('Department'), ':</label>
			<div class="fieldtext">', $MyRow['description'], '</div>
		</field>';

$SQL = "SELECT room_nr, nr_of_beds FROM care_room WHERE ward_nr='" . $SelectedWard . "' ORDER BY room_nr";
$Result = DB_query($SQL);

while ($MyRoomRow = DB_fetch_array($Result)) {
	echo '<field>
			<label for="BedsForRoom', $MyRoomRow['room_nr'], '">', _('Number of beds in room'), ' ', $MyRow['roomprefix'], $MyRoomRow['room_nr'], '</label>
			<input type="text" autofocus="autofocus" size="16" class="number" name="BedsForRoom', $MyRoomRow['room_nr'], '" value="', $MyRoomRow['nr_of_beds'], '" />
			<fieldhelp>', _('Enter the total number of beds in room'), ' ', $MyRow['roomprefix'], $MyRoomRow['room_nr'], '<fieldhelp>
		</field>';
}

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="update" value="', _('Update Bed Numbers'), '" />
		<a href="MedMaintainWards.php?SelectedWard=', $SelectedWard . '">', _('Return to main Ward Configuration Screen'), '</a>
	</div>
</form>';

include('includes/footer.php');
?>