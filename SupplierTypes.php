<?php


include('includes/session.php');
$Title = _('Supplier Types') . ' / ' . _('Maintenance');
$ViewTopic = 'Setup';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Supplier Types')
	. '" alt="" />' . _('Supplier Type Setup') . '</p>
	<div class="page_help_text">' . _('Add/edit/delete Supplier Types') . '</div>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TypeName']) >100) {
		$InputError = 1;
		prnMsg(_('The supplier type name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'SupplierType';
		$i++;
	}

	if (mb_strlen(trim($_POST['TypeName']))==0) {
		$InputError = 1;
		prnMsg(_('The supplier type name description must contain at least one character'),'error');
		$Errors[$i] = 'SupplierType';
		$i++;
	}

	$CheckSQL = "SELECT count(*)
		     FROM suppliertype
		     WHERE typename = '" . $_POST['TypeName'] . "'";
	$CheckResult=DB_query($CheckSQL);
	$CheckRow=DB_fetch_row($CheckResult);
	if ($CheckRow[0]>0 and !isset($_POST['Edit'])) {
		$InputError = 1;
		prnMsg(_('You already have a supplier type called').' '.$_POST['TypeName'],'error');
		$Errors[$i] = 'SupplierName';
		$i++;
	}

	if (isset($_POST['Edit']) AND $InputError !=1) {

		$SQL = "UPDATE suppliertype
			SET typename = '" . $_POST['TypeName'] . "'
			WHERE typeid = '" . $SelectedType . "'";

		prnMsg(_('The supplier type') . ' ' . $SelectedType . ' ' .  _('has been updated'),'success');
	} elseif ($InputError !=1){
		// Add new record on submit

		$SQL = "INSERT INTO suppliertype
					(typename)
				VALUES ('" . $_POST['TypeName'] . "')";


		$msg = _('Supplier type') . ' ' . $_POST['TypeName'] .  ' ' . _('has been created');
		$CheckSQL = "SELECT count(typeid) FROM suppliertype";
		$Result = DB_query($CheckSQL);
		$row = DB_fetch_row($Result);
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);


	// Fetch the default supplier type
		$SQL = "SELECT confvalue
					FROM config
					WHERE confname='DefaultSupplierType'";
		$Result = DB_query($SQL);
		$SupplierTypeRow = DB_fetch_row($Result);
		$DefaultSupplierType = $SupplierTypeRow[0];

	// Does it exist
		$CheckSQL = "SELECT count(*)
			     FROM suppliertype
			     WHERE typeid = '" . $DefaultSupplierType . "'";
		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

	// If it doesnt then update config with newly created one.
		if ($CheckRow[0] == 0) {
			$SQL = "UPDATE config
					SET confvalue='" . $_POST['TypeID'] . "'
					WHERE confname='DefaultSupplierType'";
			$Result = DB_query($SQL);
			$_SESSION['DefaultSupplierType'] = $_POST['TypeID'];
		}

		unset($SelectedType);
		unset($_POST['TypeID']);
		unset($_POST['TypeName']);
	}

} elseif ( isset($_GET['delete']) ) {

	$SQL = "SELECT COUNT(*) FROM suppliers WHERE supptype='" . $SelectedType . "'";

	$ErrMsg = _('The number of suppliers using this Type record could not be retrieved because');
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg (_('Cannot delete this type because suppliers are currently set up to use this type') . '<br />' .
			_('There are') . ' ' . $MyRow[0] . ' ' . _('suppliers with this type code'));
	} else {

		$SQL="DELETE FROM suppliertype WHERE typeid='" . $SelectedType . "'";
		$ErrMsg = _('The Type record could not be deleted because');
		$Result = DB_query($SQL,$ErrMsg);
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

	$SQL = "SELECT typeid, typename FROM suppliertype";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<thead>
			<tr>
		<th class="ascending" >' . _('Type ID') . '</th>
		<th class="ascending" >' . _('Type Name') . '</th>
			</tr>
		</thead>
		<tbody>';

while ($MyRow = DB_fetch_row($Result)) {

	printf('<tr class="striped_row">
			<td>%s</td>
			<td>%s</td>
			<td><a href="%sSelectedType=%s">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedType=%s&amp;delete=yes" onclick="return confirm(\'' .
				_('Are you sure you wish to delete this Supplier Type?') . '\');">' . _('Delete') . '</a></td>
		</tr>',
		$MyRow[0],
		$MyRow[1],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$MyRow[0],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$MyRow[0]);
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre">
			<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Types Defined') . '</a></p>
		</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>'; //Main table

	// The user wish to EDIT an existing type
	if ( isset($SelectedType) AND $SelectedType!='' ) {

		$SQL = "SELECT typeid,
			       typename
		        FROM suppliertype
		        WHERE typeid='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['TypeID'] = $MyRow['typeid'];
		$_POST['TypeName']  = $MyRow['typename'];

		echo '<input type="hidden" name="Edit" value="' . $SelectedType . '" />';
		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />';
		echo '<input type="hidden" name="TypeID" value="' . $_POST['TypeID'] . '" />';

		// We dont allow the user to change an existing type code

		echo '<legend>', _('Edit Supplier Type'), '</legend>
				<field>
					<label for="TypeID">' ._('Type ID') . ': </label>
					<fieldtext>' . $_POST['TypeID'] . '</fieldtext>
				</field>';
	} else {
		echo '<legend>', _('Create Supplier Type'), '</legend>';
	}
	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName']='';
	}
	echo '<field>
			<label for="TypeName">' . _('Type Name') . ':</label>
			<input type="text"  required="true" pattern="(?!^\s+$)[^<>+-]{1,100}" title="" name="TypeName" placeholder="'._('less than 100 characters').'" value="' . $_POST['TypeName'] . '" />
			<fieldhelp>'._('The input should not be over 100 characters and contains illegal characters') . ' ' . '" \' - &amp; or a space'.'</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Accept') . '" />
		</div>';

	echo '</form>';

} // end if user wish to delete

include('includes/footer.php');
?>