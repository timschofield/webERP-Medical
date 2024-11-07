<?php


include('includes/session.php');
if (isset($_POST['NoteDate'])){$_POST['NoteDate'] = ConvertSQLDate($_POST['NoteDate']);};
$Title = _('Customer Notes');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'CustomerNotes';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Id'])){
	$Id = (int)$_GET['Id'];
} else if (isset($_POST['Id'])){
	$Id = (int)$_POST['Id'];
}
if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}

echo '<a href="' . $RootPath . '/SelectCustomer.php?DebtorNo=' . $DebtorNo . '">' . _('Back to Select Customer') . '</a>
	<br />';

if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_long((integer)$_POST['Priority'])) {
		$InputError = 1;
		prnMsg( _('The contact priority must be an integer.'), 'error');
	} elseif (mb_strlen($_POST['Note']) >200) {
		$InputError = 1;
		prnMsg( _('The contact\'s notes must be two hundred characters or less long'), 'error');
	} elseif( trim($_POST['Note']) == '' ) {
		$InputError = 1;
		prnMsg( _('The contact\'s notes may not be empty'), 'error');
	}

	if (isset($Id) and $InputError !=1) {

		$sql = "UPDATE custnotes SET note='" . $_POST['Note'] . "',
									date='" . FormatDateForSQL($_POST['NoteDate']) . "',
									href='" . $_POST['Href'] . "',
									priority='" . $_POST['Priority'] . "'
				WHERE debtorno ='".$DebtorNo."'
				AND noteid='".$Id."'";
		$msg = _('Customer Notes') . ' ' . $DebtorNo  . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO custnotes (debtorno,
										href,
										note,
										date,
										priority)
				VALUES ('" . $DebtorNo. "',
						'" . $_POST['Href'] . "',
						'" . $_POST['Note'] . "',
						'" . FormatDateForSQL($_POST['NoteDate']) . "',
						'" . $_POST['Priority'] . "')";
		$msg = _('The contact notes record has been added');
	}

	if ($InputError !=1) {
		$result = DB_query($sql);
				//echo '<br />' . $sql;

		echo '<br />';
		prnMsg($msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['Noteid']);
		unset($_POST['NoteDate']);
		unset($_POST['Href']);
		unset($_POST['Priority']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$sql="DELETE FROM custnotes
			WHERE noteid='".$Id."'
			AND debtorno='".$DebtorNo."'";
	$result = DB_query($sql);

	echo '<br />';
	prnMsg( _('The contact note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);
}

if (!isset($Id)) {
	$SQLname="SELECT * FROM debtorsmaster
				WHERE debtorno='".$DebtorNo."'";
	$Result = DB_query($SQLname);
	$row = DB_fetch_array($Result);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . _('Notes for Customer').': <b>' .$row['name'] . '</b></p>
		<br />';

	$sql = "SELECT noteid,
					debtorno,
					href,
					note,
					date,
					priority
				FROM custnotes
				WHERE debtorno='".$DebtorNo."'
				ORDER BY date DESC";
	$result = DB_query($sql);

	echo '<table class="selection">
		<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Note') . '</th>
			<th>' . _('WWW') . '</th>
			<th>' . _('Priority') . '</th>
		</tr>';

	while ($myrow = DB_fetch_array($result)) {
		printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td><a href="%s">%s</a></td>
				<td>%s</td>
				<td><a href="%sId=%s&DebtorNo=%s">' .  _('Edit').' </td>
				<td><a href="%sId=%s&DebtorNo=%s&delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this customer note?') . '\');">' .  _('Delete'). '</td>
				</tr>',
				ConvertSQLDate($myrow['date']),
				$myrow['note'],
				$myrow['href'],
				$myrow['href'],
				$myrow['priority'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['noteid'],
				$myrow['debtorno'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['noteid'],
				$myrow['debtorno']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="'.htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo='.$DebtorNo.'">' . _('Review all notes for this Customer') . '</a>
		</div>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo=' . $DebtorNo . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing

		$sql = "SELECT noteid,
						debtorno,
						href,
						note,
						date,
						priority
					FROM custnotes
					WHERE noteid='".$Id."'
						AND debtorno='".$DebtorNo."'";

		$result = DB_query($sql);

		$myrow = DB_fetch_array($result);

		$_POST['Noteid'] = $myrow['noteid'];
		$_POST['Note']	= $myrow['note'];
		$_POST['Href']  = $myrow['href'];
		$_POST['NoteDate']  = $myrow['date'];
		$_POST['Priority']  = $myrow['priority'];
		$_POST['debtorno']  = $myrow['debtorno'];
		echo '<input type="hidden" name="Id" value="'. $Id .'" />';
		echo '<input type="hidden" name="Con_ID" value="' . $_POST['Noteid'] . '" />';
		echo '<input type="hidden" name="DebtorNo" value="' . $_POST['debtorno'] . '" />';
		echo '<fieldset>
				<legend>', _('Edit existing customer note'), '</legend>
				<field>
					<label for="Noteid">' .  _('Note ID').':</label>
					<fieldtext>' . $_POST['Noteid'] . '</fieldtext>
				</field>';
	} else {
		echo '<fieldset>
				<legend>', _('Create new customer note'), '</legend>';
	}

	echo '<field>
			<label for="Note">' . _('Contact Note'). '</label>';
	if (isset($_POST['Note'])) {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32">' .$_POST['Note'] . '</textarea>
			<fieldhelp>', _('Write the customer note here'), '</fieldhelp>
		</field>';
	} else {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32"></textarea>
			<fieldhelp>', _('Write the customer note here'), '</fieldhelp>
		</field>';
	}
	echo '<field>
			<label for="Href">' .  _('WWW') . '</label>';
	if (isset($_POST['Href'])) {
		echo '<input type="url" name="Href" value="'.$_POST['Href'].'" size="35" maxlength="100" />
			<fieldhelp>', _('Any website associated with this note'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="url" name="Href" size="35" maxlength="100" />
			<fieldhelp>', _('Any website associated with this note'), '</fieldhelp>
		</field>';
	}
	echo '<field>
			<label for="NoteDate">' . _('Date') . '</label>';
	if (isset($_POST['NoteDate'])) {
		echo '<input type="date" required name="NoteDate"  value="' . FormatDateForSQL($_POST['NoteDate']) . '" size="11" maxlength="10" />
			<fieldhelp>', _('The date of this note'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="date" required name="NoteDate" value="' . date('Y-m-d') . '" size="11" maxlength="10" />
			<fieldhelp>', _('The date of this note'), '</fieldhelp>
		</field>';
	}
	echo '<field>
			<label for="Priority">' .  _('Priority'). '</label>';
	if (isset($_POST['Priority'])) {
		echo '<input type="text" class="number" required="required" name="Priority" class="number" value="' . $_POST['Priority']. '" size="1" maxlength="3" />
			<fieldhelp>', _('The priority level for this note, between 1 and 9'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="text" class="number" required="required"  name="Priority" value="1"  size="1" maxlength="3"/>
			<fieldhelp>', _('The priority level for this note, between 1 and 9'), '</fieldhelp>
		</field>';
	}
	echo '</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="submit" value="'._('Enter Information').'" />
		</div>
	</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>