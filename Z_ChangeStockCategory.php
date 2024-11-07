<?php
/* This script is an utility to change a stock category code. */

include ('includes/session.php');
$Title = _('UTILITY PAGE Change A Stock Category');// Screen identificator.
$ViewTopic = 'SpecialUtilities'; // Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangeStockCategory'; // Anchor's id in the manual's html document
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/inventory.png" title="' .
	_('Change A Stock Category Code') . '" /> ' .// Icon title.
	_('Change A Stock Category Code') . '</p>';// Page title.

include ('includes/SQL_CommonFunctions.inc');

if (isset($_POST['ProcessStockChange'])) {
	$_POST['NewStockCategory'] = mb_strtoupper($_POST['NewStockCategory']);

	/*First check the stock code exists */
	$Result = DB_query("SELECT categoryid FROM stockcategory WHERE categoryid='" . $_POST['OldStockCategory'] . "'");

	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The stock Category') . ': ' . $_POST['OldStockCategory'] . ' ' . _('does not currently exist as a stock category in the system'), 'error');
		include ('includes/footer.php');
		exit;
	}

	if (ContainsIllegalCharacters($_POST['NewStockCategory'])) {
		prnMsg(_('The new stock category to change the old code to contains illegal characters - no changes will be made'), 'error');
		include ('includes/footer.php');
		exit;
	}

	if ($_POST['NewStockCategory'] == '') {
		prnMsg(_('The new stock category to change the old code to must be entered as well'), 'error');
		include ('includes/footer.php');
		exit;
	}

	/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT categoryid FROM stockcategory WHERE categoryid='" . $_POST['NewStockCategory'] . "'");

	if (DB_num_rows($Result) != 0) {
		echo '<br /><br />';
		prnMsg(_('The replacement stock category') . ': ' . $_POST['NewStockCategory'] . ' ' . _('already exists as a stock category in the system') . ' - ' . _('a unique stock category must be entered for the new stock category'), 'error');
		include ('includes/footer.php');
		exit;
	}
    DB_Txn_Begin();
	echo '<br />' . _('Adding the new stock Category record');
	$SQL = "INSERT INTO stockcategory (categoryid,
					categorydescription,
					stocktype,
					stockact,
					adjglact,
					issueglact,
					purchpricevaract,
					materialuseagevarac,
					defaulttaxcatid,
					wipact)
			SELECT '" . $_POST['NewStockCategory'] . "',
				categorydescription,
					stocktype,
					stockact,
					adjglact,
					issueglact,
					purchpricevaract,
					materialuseagevarac,
					defaulttaxcatid,
					wipact
			FROM stockcategory
			WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$DbgMsg = _('The SQL statement that failed was');
	$ErrMsg = _('The SQL to insert the new stock category record failed');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	echo '<br />' . _('Changing stock properties');
	$SQL = "UPDATE stockcatproperties SET categoryid='" . $_POST['NewStockCategory'] . "' WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to update stock properties records failed');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	echo '<br />' . _('Changing stock master records');
	$SQL = "UPDATE stockmaster SET categoryid='" . $_POST['NewStockCategory'] . "' WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to update stock master transaction records failed');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	echo '<br />' . _('Changing sales analysis records');
	$SQL = "UPDATE salesanalysis SET stkcategory='" . $_POST['NewStockCategory'] . "' WHERE stkcategory='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');

	echo '<br />' . _('Changing internal stock category roles records');
	$SQL = "UPDATE internalstockcatrole SET categoryid='" . $_POST['NewStockCategory'] . "' WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to update internal stock category role records failed');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');

	$SQL = 'SET FOREIGN_KEY_CHECKS=1';
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	DB_Txn_Commit();
	echo '<br />' . _('Deleting the old stock category record');
	$SQL = "DELETE FROM stockcategory WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to delete the old stock category record failed');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo ' ... ' . _('completed');
	echo '<p>' . _('Stock Category') . ': ' . $_POST['OldStockCategory'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewStockCategory'];
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', _('Category To Change'), '</legend>
		<field>
			<label>' . _('Existing Inventory Category Code') . ':</label>
			<td><input type="text" data-type="no-illegal-chars" name="OldStockCategory"  title="' . _('Enter up to six alphanumeric characters or underscore as a code for this stock category') . '" size="7" maxlength="6" /></td>
		</field>
		<field>
			<label>' . _('New Inventory Category Code') . ':</label
			<td><input type="text" data-type="no-illegal-chars"  title="' . _('Enter up to six alphanumeric characters or underscore as a code for this stock category') . '" name="NewStockCategory" size="7" maxlength="6" /></td>
		</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ProcessStockChange" value="' . _('Process') . '" />
	</div>
	</form>';
include ('includes/footer.php');
?>