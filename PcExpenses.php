<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Maintenance Of Petty Cash Of Expenses');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Payment Entry') . '" alt="" />' . ' ' . $title . '</p>';

if (isset($_POST['SelectedExpense'])){
	$SelectedExpense = mb_strtoupper($_POST['SelectedExpense']);
} elseif (isset($_GET['SelectedExpense'])){
	$SelectedExpense = mb_strtoupper($_GET['SelectedExpense']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedExpense);
	unset($_POST['codeexpense']);
	unset($_POST['description']);
	unset($_POST['glaccount']);
	unset($_POST['Tag']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if ($_POST['codeexpense']=='' OR $_POST['codeexpense']==' ' OR $_POST['codeexpense']=='  ') {
		$InputError = 1;
		prnMsg(_('The Expense type code cannot be an empty string or spaces'),'error');
		echo '<br />';
		$Errors[$i] = 'PcExpenses';
		$i++;
	} elseif (mb_strlen($_POST['codeexpense']) >20) {
		$InputError = 1;
		prnMsg(_('The Expense code must be twenty characters or less long'),'error');
		echo '<br />';
		$Errors[$i] = 'PcExpenses';
		$i++;
	} elseif (ContainsIllegalCharacters($_POST['codeexpense'])) {
		$InputError = 1;
		prnMsg(_('The Expense code cannot contain any of the following characters " \' - &'),'error');
		echo '<br />';
		$Errors[$i] = 'PcExpenses';
		$i++;
	} elseif (ContainsIllegalCharacters($_POST['description'])) {
		$InputError = 1;
		prnMsg(_('The Expense description cannot contain any of the following characters " \' - &'),'error');
		echo '<br />';
		$Errors[$i] = 'TypeTabCode';
		$i++;
	} elseif (mb_strlen($_POST['description']) >50) {
		$InputError = 1;
		echo prnMsg(_('The tab code must be Fifty characters or less long'),'error');
		echo '<br />';
		$Errors[$i] = 'TypeTabCode';
		$i++;
	} elseif (mb_strlen($_POST['description'])==0) {
		$InputError = 1;
		echo prnMsg(_('The tab code description must be entered'),'error');
		echo '<br />';
		$Errors[$i] = 'TypeTabCode';
		$i++;
	} elseif ($_POST['glaccount']=='') {
		$InputError = 1;
		echo prnMsg(_('A general ledger code must be selected for this expense'),'error');
		echo '<br />';
		$Errors[$i] = 'GLCode';
		$i++;
	}
	if (isset($SelectedExpense) AND $InputError !=1) {

		$sql = "UPDATE pcexpenses
				SET description = '" . $_POST['description'] . "',
					glaccount = '" . $_POST['glaccount'] . "',
					tag = '" . $_POST['Tag'] . "'
			WHERE codeexpense = '" . $SelectedExpense . "'";

		$msg = _('The Expenses type') . ' ' . $SelectedExpense . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
				 FROM pcexpenses
				 WHERE codeexpense = '" . $_POST['codeexpense'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Expense type ') . $_POST['codeexpense'] . _(' already exists'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO pcexpenses
						(codeexpense,
			 			 description,
			 			 glaccount,
			 			 tag)
					VALUES ('" . $_POST['codeexpense'] . "',
						'" . $_POST['description'] . "',
						'" . $_POST['glaccount'] . "',
						'" . $_POST['Tag'] . "')";

			$msg = _('Expense ') . ' ' . $_POST['codeexpense'] .  ' ' . _('has been created');
			$checkSql = "SELECT count(codeexpense)
							FROM pcexpenses";
			$result = DB_query($checkSql, $db);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		echo '<br />';
		unset($SelectedExpense);
		unset($_POST['codeexpense']);
		unset($_POST['description']);
		unset($_POST['glaccount']);
		unset($_POST['Tag']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'PcTabExpenses'

	$sql= "SELECT COUNT(*)
		   FROM pctabexpenses
		   WHERE codeexpense='$SelectedExpense'";

	$ErrMsg = _('The number of type of tabs using this expense code could not be retrieved');
	$result = DB_query($sql,$db,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this petty cash expense because it is used in some tab types') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('tab types using this expense code'),'error');

	} else {

			$sql="DELETE FROM pcexpenses
				WHERE codeexpense='$SelectedExpense'";
			$ErrMsg = _('The expense type record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('Expense type') .  ' ' . $SelectedExpense  . ' ' . _('has been deleted') ,'success');
			echo '<br />';
			unset ($SelectedExpense);
			unset($_GET['delete']);


	} //end if tab type used in transactions
}

if (!isset($SelectedExpense)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedExpense will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT codeexpense,
					description,
					glaccount,
					tag
				FROM pcexpenses";
	$result = DB_query($sql,$db);

	echo '<table class="selection">';
	echo '<tr>
		<th>' . _('Code Of Expense') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Account Code') . '</th>
		<th>' . _('Account Description') . '</th>
		<th>' . _('Tag') . '</th>
	</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$sqldesc="SELECT accountname
					FROM chartmaster
					WHERE accountcode='". $myrow[2] . "'";

		$ResultDes = DB_query($sqldesc,$db);
		$Description=DB_fetch_array($ResultDes);

		$SqlDescTag="SELECT tagdescription
					FROM tags
					WHERE tagref='". $myrow[3] . "'";

		$ResultDesTag = DB_query($SqlDescTag,$db);
		$DescriptionTag=DB_fetch_array($ResultDesTag);


		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sSelectedExpense=%s">' . _('Edit') . '</td>
				<td><a href="%sSelectedExpense=%s&delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this expense code and all the details it may have set up?') . '\');">' . _('Delete') . '</td>
				</tr>',
				$myrow[0],
				$myrow[1],
				$myrow[2],
				$Description['accountname'],
				$DescriptionTag['tagdescription'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow[0],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedExpense)) {

	echo '<p><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Petty Cash Expenses Defined') . '</a></div></p>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><table class="selection">'; //Main table

	// The user wish to EDIT an existing type
	if ( isset($SelectedExpense) AND $SelectedExpense!='' )
	{

		$sql = "SELECT codeexpense,
				   description,
				   glaccount,
				   tag
				FROM pcexpenses
				WHERE codeexpense='" . $SelectedExpense . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['codeexpense'] = $myrow['codeexpense'];
		$_POST['description']  = $myrow['description'];
		$_POST['glaccount']  = $myrow['glaccount'];
		$_POST['Tag']  = $myrow['tag'];

		echo '<input type="hidden" name="SelectedExpense" value="' . $SelectedExpense . '" />';
		echo '<input type="hidden" name="codeexpense" value="' . $_POST['codeexpense']. '" />';
		echo '<table class="selection"> <tr><td>' . _('Code Of Expense') . ':</td><td>';

		// We dont allow the user to change an existing type code

		echo $_POST['codeexpense'] . '</td></tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code
		if (isset($_POST['codeexpense'])) {
			echo '<table class="selection"><tr><td>' . _('Code Of Expense') . ':</td>
					<td><input type="text" value="'.$_POST['codeexpense'].'" ' . (in_array('SalesType',$Errors) ? 'class="inputerror"' : '' ) .' name="codeexpense" /></td></tr>';
		} else {
			echo '<table class="selection"><tr><td>' . _('Code Of Expense') . ':</td>
					<td><input type="text" ' . (in_array('SalesType',$Errors) ? 'class="inputerror"' : '' ) .' name="codeexpense" /></td></tr>';
		}
	}

	if (!isset($_POST['description'])) {
		$_POST['description']='';
	}
	echo '<tr><td>' . _('Description') . ':</td><td><input type="text" name="description" size="50" maxlength="49" value="' . $_POST['description'] . '" /></td></tr>';

	echo '<tr>
			<td>' . _('Account Code') . ':</td>
			<td><select name="glaccount">';

	DB_free_result($result);
	$SQL = "SELECT accountcode,
				accountname
			FROM chartmaster
			ORDER BY accountcode";
	$result = DB_query($SQL,$db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['glaccount']) and $myrow['accountcode']==$_POST['glaccount']) {
			echo '<option selected="True" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
		}
	} //end while loop

	echo '</select></td></tr>';

	//Select the tag
	DB_free_result($result);
	echo '<tr>
			<td>' . _('Tag') . ':</td>
			<td><select name="Tag">';

	$SQL = "SELECT tagref,
					tagdescription
			FROM tags
			ORDER BY tagref";

	$result=DB_query($SQL,$db);
	echo '<option value="0">0 - ' . _('None') . '</option>';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['Tag']) and $_POST['Tag']==$myrow['tagref']){
			echo '<option selected value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'].'</option>';
		} else {
			echo '<option value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'] . '</option>';
		}
	}
	echo '</select></td>';
	// End select tag

   	echo '</td></tr></table>'; // close main table

	echo '<br /><div class="centre"><button type="submit" name="submit">' . _('Accept') . '</button>
					<button type="submit" name="Cancel">' . _('Cancel') . '</button></div><br />';

	echo '</form>';

} // end if user wish to delete


include('includes/footer.inc');
?>