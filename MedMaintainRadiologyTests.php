<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Maintain Radiology Tests');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<div class="toplink">
		<a href="', $RootPath, '/MedRadiologyLaboratory.php">', _('Back to Main Radiology page'), '</a>
	</div>';

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/radiology.png" title="', _('Radiology'), '" /> ', _('Radiology'), '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($_POST['Update'])) {
	$UpdateSQL = "UPDATE care_radio_test_type SET name='" . $_POST['Name'] . "',
													stockid='" . $_POST['StockID'] . "',
													history=CONCAT(history, ' - Updated by " . $_SESSION['UserID'] . " ON ',NOW()),
													modify_time=NOW(),
													modify_id='" . $_SESSION['UserID'] . "'
												WHERE type='" . $_POST['Type'] . "'";
	$UpdateResult = DB_query($UpdateSQL);
	if (DB_error_no() == 0) {
		prnMsg(_('Bacteriology test details have been updated'), 'success');
	} else {
		prnMsg(_('Bacteriology test details could not be updated'), 'error');
	}

}

if (isset($_POST['Insert'])) {
	$InsertSQL = "INSERT INTO care_radio_test_type (type,
														name,
														stockid,
														create_time,
														create_id,
														modify_time,
														modify_id,
														history
													) VALUES (
														'" . '_tx_' . $_POST['Type'] . "',
														'" . $_POST['Name'] . "',
														'" . $_POST['StockID'] . "',
														NOW(),
														'" . $_SESSION['UserID'] . "',
														NOW(),
														'" . $_SESSION['UserID'] . "',
														CONCAT('CREATED - " . $_SESSION['UserID'] . " ON ',NOW())
													)";
	$InsertResult = DB_query($InsertSQL);
	if (DB_error_no() == 0) {
		prnMsg(_('Radiology test details have been created'), 'success');
	} else {
		prnMsg(_('Radiology test details could not be created'), 'error');
	}

}

if (isset($_GET['Delete'])) {
	$DeleteSQL = "DELETE FROM care_radio_test_type WHERE type='" . $_GET['type'] . "'";
	$DeleteResult = DB_query($DeleteSQL);
	if (DB_error_no() == 0) {
		prnMsg(_('Radiology test has been deleted'), 'success');
	} else {
		prnMsg(_('Radiology test could not be deleted'), 'error');
	}
}

if (isset($_GET['Edit'])) {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="Type" value="', $_GET['type'], '" />';

	$SQL = "SELECT name,
					stockid
				FROM care_radio_test_type
				WHERE type='" . $_GET['type'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['StockID'] = $MyRow['stockid'];

	echo '<fieldset>
			<legend>', _('Edit Radiology Test Details'), '</legend>';

	echo '<field>
			<label for="Type">', _('Type'), '</label>
			<div class="fieldtext">', mb_substr($_GET['type'], 4), '</div>
		</field>';

	echo '<field>
			<label for="Name">', _('Name'), '</label>
			<input type="text" name="Name" value="', $MyRow['name'], '" />
		</field>';

	$SQL = "SELECT stockid, description FROM stockmaster WHERE categoryid='" . $_SESSION['radiology_cat'] . "' ORDER BY description";
	$StockResult = DB_query($SQL);

	echo '<field>
			<label for="StockID">', _('Stock code to use for billing'), '</label>
			<select name="StockID">';

	while ($MyStockRow = DB_fetch_array($StockResult)) {
		if (isset($_POST['StockID']) and $MyStockRow['stockid'] == $_POST['StockID']) {
			echo '<option selected="selected" value="', $MyStockRow['stockid'], '">', $MyStockRow['description'], '</option>';
		} else {
			echo '<option value="', $MyStockRow['stockid'], '">', $MyStockRow['description'], '</option>';
		}
	} //end while loop
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Update" value="', _('Update Test Details'), '" />
		</div>';

	echo '</form>';
} else {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Create Radiology Test Details'), '</legend>';

	echo '<field>
			<label for="Type">', _('Type'), '</label>
			<input type="text" name="Type" value="" />
		</field>';

	echo '<field>
			<label for="Name">', _('Name'), '</label>
			<input type="text" name="Name" value="" />
		</field>';

	$SQL = "SELECT stockid, description FROM stockmaster WHERE categoryid='" . $_SESSION['radiology_cat'] . "' ORDER BY description";
	$StockResult = DB_query($SQL);

	echo '<field>
			<label for="StockID">', _('Stock code to use for billing'), '</label>
			<select name="StockID">';

	while ($MyStockRow = DB_fetch_array($StockResult)) {
		echo '<option value="', $MyStockRow['stockid'], '">', $MyStockRow['description'], '</option>';
	} //end while loop
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Insert" value="', _('Create Test Details'), '" />
		</div>';

	echo '</form>';

	$SQL = "SELECT type,
					name,
					stockid
				FROM care_radio_test_type";
	$Result = DB_query($SQL);

	echo '<table>
			<thead>
				<th class="SortedColumn">', _('Type'), '</th>
				<th class="SortedColumn">', _('Name'), '</th>
				<th class="SortedColumn">', _('Stock ID'), '</th>
				<th></th>
				<th></th>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', mb_substr($MyRow['type'], 4), '</td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['stockid'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?type=', $MyRow['type'], '&Edit=Yes">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?type=', $MyRow['type'], '&Delete=Yes">', _('Delete'), '</a></td>
			</tr>';
	}

	echo '</tbody>
		</table>';
}

include ('includes/footer.php');

?>