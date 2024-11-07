<?php


include('includes/session.php');
$Title = _('Credit Status Code Maintenance');
$ViewTopic = 'CreditStatus';
$BookMark = 'CreditStatus';
include('includes/header.php');

if (isset($_GET['SelectedReason'])){
	$SelectedReason = $_GET['SelectedReason'];
} elseif(isset($_POST['SelectedReason'])){
	$SelectedReason = $_POST['SelectedReason'];
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
	</p>
	<br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible

	$SQL="SELECT count(reasoncode)
			FROM holdreasons WHERE reasoncode='".$_POST['ReasonCode']."'";
	$Result=DB_query($SQL);
	$MyRow=DB_fetch_row($Result);

	if ($MyRow[0]!=0 and !isset($SelectedReason)) {
		$InputError = 1;
		prnMsg( _('The credit status code already exists in the database'),'error');
		$Errors[$i] = 'ReasonCode';
		$i++;
	}
	if (!is_numeric($_POST['ReasonCode'])) {
		$InputError = 1;
		prnMsg(_('The status code name must be an integer'),'error');
		$Errors[$i] = 'ReasonCode';
		$i++;
	}
	if (mb_strlen($_POST['ReasonDescription']) > 30) {
		$InputError = 1;
		prnMsg(_('The credit status description must be thirty characters or less long'),'error');
	}
	if (mb_strlen($_POST['ReasonDescription']) == 0) {
		$InputError = 1;
		prnMsg(_('The credit status description must be entered'),'error');
		$Errors[$i] = 'ReasonDescription';
		$i++;
	}

	$msg='';

	if (isset($SelectedReason) AND $InputError !=1) {

		/*SelectedReason could also exist if submit had not been clicked this code would not run in this case cos submit is false of course	see the delete code below*/

		if (isset($_POST['DisallowInvoices']) and $_POST['DisallowInvoices']=='on'){
			$SQL = "UPDATE holdreasons SET
							reasondescription='" . $_POST['ReasonDescription'] . "',
							dissallowinvoices=1
							WHERE reasoncode = '".$SelectedReason."'";
		} else {
			$SQL = "UPDATE holdreasons SET
							reasondescription='" . $_POST['ReasonDescription'] . "',
							dissallowinvoices=0
							WHERE reasoncode = '".$SelectedReason."'";
		}
		$msg = _('The credit status record has been updated');

	} else if ($InputError !=1) {

	/*Selected Reason is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new status code form */

		if (isset($_POST['DisallowInvoices']) AND $_POST['DisallowInvoices']=='on'){

			$SQL = "INSERT INTO holdreasons (reasoncode,
											reasondescription,
											dissallowinvoices)
									VALUES ('" .$_POST['ReasonCode'] . "',
											'".$_POST['ReasonDescription'] . "',
											1)";
		} else {
			$SQL = "INSERT INTO holdreasons (reasoncode,
											reasondescription,
											dissallowinvoices)
									VALUES ('" . $_POST['ReasonCode'] . "',
											'" . $_POST['ReasonDescription'] ."',
											0)";
		}

		$msg = _('A new credit status record has been inserted');
	}
	//run the SQL from either of the above possibilites
	$Result = DB_query($SQL);
	if ($msg != '') {
		prnMsg($msg,'success');
	}
	unset ($SelectedReason);
	unset ($_POST['ReasonCode']);
	unset ($_POST['ReasonDescription']);
	unset ($_POST['submit']);
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$SQL= "SELECT COUNT(*)
			FROM debtorsmaster
			WHERE debtorsmaster.holdreason='".$SelectedReason."'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg( _('Cannot delete this credit status code because customer accounts have been created referring to it'),'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('customer accounts that refer to this credit status code');
	}  else {
		//only delete if used in neither customer or supplier accounts

		$SQL="DELETE FROM holdreasons WHERE reasoncode='" . $SelectedReason . "'";
		$Result = DB_query($SQL);
		prnMsg(_('This credit status code has been deleted'),'success');
	}
	//end if status code used in customer or supplier accounts
	unset ($_GET['delete']);
	unset ($SelectedReason);

}

if (!isset($SelectedReason)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReason will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of status codes will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT reasoncode, reasondescription, dissallowinvoices FROM holdreasons";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<tr>
			<th>' .  _('Status Code')  . '</th>
			<th>' .  _('Description')  . '</th>
			<th>' .  _('Disallow Invoices')  . '</th>
        </tr>';

	while ($MyRow=DB_fetch_array($Result)) {

		if ($MyRow['dissallowinvoices']==0) {
			$DissallowText = _('Invoice OK');
		} else {
			$DissallowText = '<b>' .  _('NO INVOICING')  . '</b>';
		}

		printf('<tr class="striped_row">
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%s?SelectedReason=%s">' . _('Edit') . '</a></td>
			<td><a href="%s?SelectedReason=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this credit status record?') . '\');">' .  _('Delete')  . '</a></td>
			</tr>',
			$MyRow['reasoncode'],
			$MyRow['reasondescription'],
			$DissallowText,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$MyRow['reasoncode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$MyRow['reasoncode']);

	} //END WHILE LIST LOOP
	echo '</table>';

} //end of ifs and buts!

if (isset($SelectedReason)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show Defined Credit Status Codes') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedReason) and ($InputError!=1)) {
		//editing an existing status code

		$SQL = "SELECT reasoncode,
					reasondescription,
					dissallowinvoices
				FROM holdreasons
				WHERE reasoncode='".$SelectedReason."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['ReasonCode'] = $MyRow['reasoncode'];
		$_POST['ReasonDescription']  = $MyRow['reasondescription'];
		$_POST['DisallowInvoices']  = $MyRow['dissallowinvoices'];

		echo '<input type="hidden" name="SelectedReason" value="' . $SelectedReason . '" />';
		echo '<input type="hidden" name="ReasonCode" value="' . $_POST['ReasonCode'] . '" />';
		echo '<fieldset>
				<legend>', _('Edit Credit Status'), '</legend>
				<field>
					<label for="ReasonCode">' .  _('Status Code') .':</label>
					<fieldtext>' . $_POST['ReasonCode'] . '</fieldtext>
				</field>';

	} else { //end of if $SelectedReason only do the else when a new record is being entered
		if (!isset($_POST['ReasonCode'])) {
			$_POST['ReasonCode'] = '';
		}
		echo '<fieldset>
				<legend>', _('Create Credit Status'), '</legend>
				<field>
					<label for="ReasonCode">' .  _('Status Code') .':</label>
					<input ' . (in_array('ReasonCode',$Errors) ? 'class="integer inputerror"' : 'class="integer"' ) . ' tabindex="1" type="text" name="ReasonCode" required="required" value="'. $_POST['ReasonCode'] .'" size="3" maxlength="2" />
				</field>';
	}

	if (!isset($_POST['ReasonDescription'])) {
		$_POST['ReasonDescription'] = '';
	}
	echo '<field>
			<label for="ReasonDescription">' .  _('Description') .':</label>
			<input ' . (in_array('ReasonDescription',$Errors) ? 'class="inputerror"' : '' ) .
			 ' tabindex="2" type="text" name="ReasonDescription" required="required" value="'. $_POST['ReasonDescription'] .'" size="28" maxlength="30" />
		</field>
		<field>
			<label for="DisallowInvoices">' .  _('Disallow Invoices') . '</label>';
	if (isset($_POST['DisallowInvoices']) and $_POST['DisallowInvoices']==1) {
		echo '<input tabindex="3" type="checkbox" checked="checked" name="DisallowInvoices" />
			</field>';
	} else {
		echo '<input tabindex="3" type="checkbox" name="DisallowInvoices" />
			</field>';
	}
	echo '</fieldset>
			<div class="centre">
				<input tabindex="4" type="submit" name="submit" value="' . _('Enter Information') . '" />
            </div>
			</form>';
} //end if record deleted no point displaying form to add record
include('includes/footer.php');
?>