<?php
/* $Revision: 1.6 $ */
/* $Id$*/

include('includes/session.inc');
$title = _('Supplier Types') . ' / ' . _('Maintenance');
include('includes/header.inc');

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Supplier Types') . '" alt="" />' . _('Supplier Type Setup') . '</p>';
echo '<div class="page_help_text">' . _('Add/edit/delete Supplier Types') . '</div>';

if (isset($_POST['Submit']) or isset($_POST['Update'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['typename']) >100) {
		$InputError = 1;
		echo prnMsg(_('The supplier type name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'SupplierType';
		$i++;
	}

	if (mb_strlen($_POST['typename'])==0) {
		$InputError = 1;
		echo prnMsg(_('The supplier type name description must contain at least one character'),'error');
		$Errors[$i] = 'SupplierType';
		$i++;
	}

	$CheckSQL = "SELECT typename
		     FROM suppliertype
		     WHERE typename = '" . $_POST['typename'] . "'";
	$CheckResult=DB_query($CheckSQL, $db);

	if (!isset($_POST['Update']) and DB_num_rows($CheckResult)>0) {
		$InputError = 1;
		echo prnMsg(_('You already have a supplier type called').' '.$_POST['typename'],'error');
		$Errors[$i] = 'SupplierName';
		$i++;
	}

	if (isset($_POST['Update']) and $InputError !=1) {

		$sql = "UPDATE suppliertype
			SET typename = '" . $_POST['typename'] . "'
			WHERE typeid = '" . $SelectedType . "'";

		$msg = _('The supplier type') . ' ' . $SelectedType . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		// Add new record on submit

		$sql = "INSERT INTO suppliertype
					(typename)
				VALUES ('" . $_POST['typename'] . "')";

		$msg = _('Supplier type') . ' ' . $_POST['typename'] .  ' ' . _('has been created');

	}


	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);


	// Fetch the default price list.
		$sql = "SELECT confvalue
					FROM config
					WHERE confname='DefaultSupplierType'";
		$result = DB_query($sql,$db);
		$SupplierTypeRow = DB_fetch_array($result);
		$DefaultSupplierType = $SupplierTypeRow['confvalue'];

	// Does it exist
		$CheckSQL = "SELECT typeid
			     FROM suppliertype
			     WHERE typeid = '" . $DefaultSupplierType . "'";
		$CheckResult = DB_query($CheckSQL,$db);

	// If it doesnt then update config with newly created one.
		if (DB_num_rows($CheckResult)>0) {
			$sql = "UPDATE config
					SET confvalue='" . $_POST['typeid'] . "'
					WHERE confname='DefaultSupplierType'";
			$result = DB_query($sql,$db);
			$_SESSION['DefaultSupplierType'] = $_POST['typeid'];
		}

		prnMsg($msg,'success');

		unset($SelectedType);
		unset($_POST['typeid']);
		unset($_POST['typename']);
	}

} elseif ( isset($_GET['Delete']) ) {

	$sql = "SELECT supplierid FROM suppliers WHERE supptype='" . $SelectedType . "'";

	$ErrMsg = _('The number of suppliers using this Type record could not be retrieved because');
	$result = DB_query($sql,$db,$ErrMsg);

	if (DB_num_rows($result)>0) {
		prnMsg (_('Cannot delete this type because suppliers are currently set up to use this type') . '<br />' .
			_('There are') . ' ' . $myrow[0] . ' ' . _('suppliers with this type code'));
	} else {

		$sql="DELETE FROM suppliertype WHERE typeid='" . $SelectedType . "'";
		$ErrMsg = _('The Type record could not be deleted because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('Supplier type') . $SelectedType  . ' ' . _('has been deleted') ,'success');

		unset ($SelectedType);
		unset($_GET['delete']);

	}
}

if (!isset($SelectedType)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will
 *  exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then
 * none of the above are true and the list of sales types will be displayed with links to delete or edit each. These will call
 * the same page again and allow update/input or deletion of the records
 */

	$sql = "SELECT typeid, typename FROM suppliertype";
	$result = DB_query($sql,$db);

	echo '<table class="selection">';
	echo '<br />';
	echo '<tr><th colspan="4" class="header">' . _('Supplier Types') . '</th></tr>';
	echo '<tr>
			<th>' . _('Type ID') . '</th>
			<th>' . _('Type Name') . '</th>
		</tr>';

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	printf('
		<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedType=%s&Edit=Yes">' . _('Edit') . '</td>
		<td><a href="%sSelectedType=%s&Delete=Yes" onclick=\'return confirm(
			"' . _('Are you sure you wish to delete this Supplier Type?') . '");\'>' . _('Delete') . '</td>
		</tr>',
		$myrow['typeid'],
		$myrow['typename'],
		htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .'?' , $myrow['typeid'],
		htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .'?' , $myrow['typeid']);
	}
	//END WHILE LIST LOOP
	echo '</table><br />';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre"><p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Types Defined') . '</a></div></p>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">'; //Main table
	echo '<tr><th colspan="2" class="header">' . _('Supplier Type Details') . '</th></tr>';
	// The user wish to EDIT an existing type
	if ( isset($_GET['Edit']) ) {

		$sql = "SELECT typeid,
			       typename
		        FROM suppliertype
		        WHERE typeid='" . $SelectedType . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['typeid'] = $myrow['typeid'];
		$_POST['typename']  = $myrow['typename'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />';
		echo '<input type="hidden" name="typeid" value="' . $SelectedType . '" />';

		// We dont allow the user to change an existing type code

		echo '<tr><td>' . _('Type ID') . ': </td><td>' . $SelectedType . '</td></tr>';

		if (!isset($_POST['typename'])) {
			$_POST['typename']='';
		}
		echo '<tr><td>' . _('Type Name') . ':</td><td><input type="text" name="typename" value="' . $_POST['typename'] . '" /></td></tr>';

		echo '</table><br />'; // close main table

		echo '<div class="centre"><button type="submit" name="Update">' . _('Update') . '</button></div><br />';

	} else {

		if (!isset($_POST['typename'])) {
			$_POST['typename']='';
		}
		echo '<tr><td>' . _('Type Name') . ':</td><td><input type="text" name="typename" value="' . $_POST['typename'] . '" /></td></tr>';

		echo '</table><br />'; // close main table

		echo '<div class="centre"><button type="submit" name="Submit">' . _('Insert') . '</button></div><br />';
	}

	echo '</form>';

} // end if user wish to delete


include('includes/footer.inc');
?>