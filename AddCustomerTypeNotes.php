<?php

include('includes/session.php');
$Title = _('Customer Type (Group) Notes');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'CustomerTypeNotes';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['NoteDate'])) {$_POST['NoteDate'] = ConvertSQLDate($_POST['NoteDate']);}

if (isset($_GET['Id'])){
	$Id = (int)$_GET['Id'];
} else if (isset($_POST['Id'])){
	$Id = (int)$_POST['Id'];
}
if (isset($_POST['DebtorType'])){
	$DebtorType = $_POST['DebtorType'];
} elseif (isset($_GET['DebtorType'])){
	$DebtorType = $_GET['DebtorType'];
}
echo '<a href="' . $RootPath . '/SelectCustomer.php?DebtorType='.$DebtorType.'">' . _('Back to Select Customer') . '</a><br />';

if (isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_long((integer)$_POST['Priority'])) {
		$InputError = 1;
		prnMsg( _('The Contact priority must be an integer.'), 'error');
	} elseif (mb_strlen($_POST['Note']) >200) {
		$InputError = 1;
		prnMsg( _('The contacts notes must be two hundred characters or less long'), 'error');
	} elseif( trim($_POST['Note']) == '' ) {
		$InputError = 1;
		prnMsg( _('The contacts notes may not be empty'), 'error');
	}

	if (isset($Id) and $InputError !=1) {

		$sql = "UPDATE debtortypenotes SET note='" . $_POST['Note'] . "',
											date='" . FormatDateForSQL($_POST['NoteDate']) . "',
											href='" . $_POST['Href'] . "',
											priority='" . $_POST['Priority'] . "'
										WHERE typeid ='".$DebtorType."'
										AND noteid='".$Id."'";
		$msg = _('Customer Group Notes') . ' ' . $DebtorType  . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO debtortypenotes (typeid,
											href,
											note,
											date,
											priority)
									VALUES ('" . $DebtorType. "',
											'" . $_POST['Href'] . "',
											'" . $_POST['Note'] . "',
											'" . FormatDateForSQL($_POST['NoteDate']) . "',
											'" . $_POST['Priority'] . "')";
		$msg = _('The contact group notes record has been added');
	}

	if ($InputError !=1) {
		$result = DB_query($sql);

		echo '<br />';
		prnMsg($msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['NoteID']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$sql="DELETE FROM debtortypenotes
			WHERE noteid='".$Id."'
			AND typeid='".$DebtorType."'";
	$result = DB_query($sql);

	echo '<br />';
	prnMsg( _('The contact group note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);

}

if (!isset($Id)) {
	$SQLname="SELECT typename from debtortype where typeid='".$DebtorType."'";
	$result = DB_query($SQLname);
	$myrow = DB_fetch_array($result);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Search') . '" alt="" />'  . _('Notes for Customer Type').': <b>' .$myrow['typename'] . '</b></p>
		<br />';

	$sql = "SELECT noteid,
					typeid,
					href,
					note,
					date,
					priority
				FROM debtortypenotes
				WHERE typeid='".$DebtorType."'
				ORDER BY date DESC";
	$result = DB_query($sql);
			//echo '<br />' . $sql;

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Note') . '</th>
			<th>' . _('href') . '</th>
			<th>' . _('Priority') . '</th>
		</tr>';

	while ($myrow = DB_fetch_array($result)) {
		printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sId=%s&amp;DebtorType=%s">' .  _('Edit') . '</a></td>
				<td><a href="%sId=%s&amp;DebtorType=%s&amp;delete=1">' .  _('Delete') . '</a></td>
				</tr>',
				ConvertSQLDate($myrow['date']),
				$myrow['note'],
				$myrow['href'],
				$myrow['priority'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['noteid'],
				$myrow['typeid'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['noteid'],
				$myrow['typeid']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?DebtorType=' . $DebtorType . '">' . _('Review all notes for this Customer Type')  . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorType='.$DebtorType.'">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing

		$sql = "SELECT noteid,
					typeid,
					href,
					note,
					date,
					priority
				FROM debtortypenotes
				WHERE noteid=".$Id."
					AND typeid='".$DebtorType."'";

		$result = DB_query($sql);
				//echo '<br />' . $sql;

		$myrow = DB_fetch_array($result);

		$_POST['NoteID'] = $myrow['noteid'];
		$_POST['Note']	= $myrow['note'];
		$_POST['Href']  = $myrow['href'];
		$_POST['NoteDate']  = ConvertSQLDate($myrow['date']);
		$_POST['Priority']  = $myrow['priority'];
		$_POST['typeid']  = $myrow['typeid'];
		echo '<input type="hidden" name="Id" value="'. $Id .'" />';
		echo '<input type="hidden" name="Con_ID" value="' . $_POST['NoteID'] . '" />';
		echo '<input type="hidden" name="DebtorType" value="' . $_POST['typeid'] . '" />';
		echo '<fieldset>
				<legend>', _('Amend Customer Type Note'), '</legend>
				<field>
					<label for="NoteID">' .  _('Note ID').':</label>
					<fieldtext>' . $_POST['NoteID'] . '</fieldtext>
				</field>';
	} else {
		echo '<fieldset>
				<legend>', _('Create New Customer Type Note'), '</legend>';
		$_POST['NoteID'] = '';
		$_POST['Note']  = '';
		$_POST['Href']  = '';
		$_POST['NoteDate']  = Date($_SESSION['DefaultDateFormat']);
		$_POST['Priority']  = '1';
		$_POST['typeid']  = '';
	}

	echo '<field>
			<label for="Note">' . _('Contact Group Note').':</label>
			<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32">' .  $_POST['Note'] . '</textarea>
			<fieldhelp>', _('Write the customer type note here'), '</fieldhelp>
		</field>
		<field>
			<label for="Href">' .  _('Web site').':</label>
			<input type="url" name="Href" value="'. $_POST['Href'].'" size="35" maxlength="100" />
			<fieldhelp>', _('Any website associated with this note'), '</fieldhelp>
		</field>
		<field>
			<label for="NoteDate">' .  _('Date').':</label>
			<input required="required" name="NoteDate" type="date" value="'. FormatDateForSQL($_POST['NoteDate']). '" size="11" maxlength="10" />
			<fieldhelp>', _('The date of this note'), '</fieldhelp>
		</field>
		<field>
			<label for="Priority">' .  _('Priority').':</label>
			<input type="text" class="number" name="Priority" value="'. $_POST['Priority'] .'" size="1" maxlength="3" />
			<fieldhelp>', _('The priority level for this note, between 1 and 9'), '</fieldhelp>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="'. _('Enter Information').'" />
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>