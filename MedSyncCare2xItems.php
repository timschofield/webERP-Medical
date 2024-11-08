<?php
include ('includes/session.php');
$Title = _('Synchronise with Care2x Item Table');
include ('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/Vial-Pills.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['UpdateLink'])) {
	for ($i = 0;$i < $_POST['TotalItems'];$i++) {
		$SQL = "UPDATE care_tz_drugsandservices
				SET partcode='" . $_POST['StockID' . $i] . "'
				WHERE item_id='" . $_POST['Care2xItem' . $i] . "'";
		$Result = DB_query($SQL);
	}
}

if (!isset($_POST['CategoryID'])) {
	echo '<form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'; // Nested table
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<td>' . _('Category') . ':</td>
				<td><select name="CategoryID">';

	$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
	$ErrMsg = _('The stock categories could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	while ($MyRow = DB_fetch_array($Result)) {
		if (!isset($_POST['CategoryID']) or $MyRow['categoryid'] == $_POST['CategoryID']) {
			echo '<option selected="True" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
		$Category = $MyRow['categoryid'];
	}

	if (!isset($_POST['CategoryID'])) {
		$_POST['CategoryID'] = $Category;
	}

	echo '</select></td>
			<td><a target="_blank" href="' . $RootPath . '/StockCategories.php">' . _('Add or Modify Stock Categories') . '</a></td>
		</tr>';

	echo '<tr>
			<td colspan="3"><div class="centre"><button type="submit" name="FindItems">' . _('Find Items') . '</button></div></td>
		</tr>';
	echo '</table></form>';
} else {
	echo '<form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'; // Nested table
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE categoryid='" . $_POST['CategoryID'] . "'";
	$Result = DB_query($SQL);
	$Care2xSQL = "SELECT item_id,
						partcode,
						item_full_description
					FROM care_tz_drugsandservices
					ORDER BY UCASE(item_full_description)";
	$Care2xResult = DB_query($Care2xSQL);
	$Care2xItems = array();
	while ($Care2xRow = DB_fetch_array($Care2xResult)) {
		$Care2xItems[$Care2xRow['item_id']] = $Care2xRow['item_full_description'];
		$Care2xPartCode[$Care2xRow['item_id']] = $Care2xRow['partcode'];
	}
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>';
		echo '<td><select name="Care2xItem' . $i . '">';
		foreach ($Care2xItems as $ItemID => $Description) {
			if ($Care2xPartCode[$ItemID] == $MyRow['stockid']) {
				echo '<option selected="selected" value="' . $ItemID . '">' . $Description . '</option>';
			} else {
				echo '<option value="' . $ItemID . '">' . $Description . '</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<input type="hidden" name="StockID' . $i . '" value="' . $MyRow['stockid'] . '" />';
		$i++;
	}
	echo '<input type="hidden" name="TotalItems" value="' . $i . '" />';
	echo '<tr>
			<td colspan="3"><div class="centre"><button type="submit" name="UpdateLink">' . _('Update') . '</button></div></td>
		</tr>';
	echo '</table></form>';
}

include ('includes/footer.php');

?>