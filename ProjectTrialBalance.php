<?php

$PageSecurity=1;
include('includes/session.inc');
$title = _('Balances for Project');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Payment Entry') . '" alt="" />' . ' ' . $title . '</p>';

if (isset($_POST['SelectedProject'])) {
	$sql="SELECT typetabcode,currency FROM pctabs WHERE tabcode='".$_POST['SelectedProject']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	$_POST['SelectedTab']=$myrow['typetabcode'];
	$Currency=$myrow['currency'];
}

if (isset($_POST['SelectedType'])){
	$SelectedType = strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = strtoupper($_GET['SelectedType']);
} else {
	$SelectedType='';
}

if (!isset($_GET['delete']) and (ContainsIllegalCharacters($SelectedType) OR strpos($SelectedType,' ')>0)){
	$InputError = 1;
	prnMsg(_('The petty cash tab type contain any of the following characters " \' - & or a space'),'error');
}

if (isset($_POST['SelectedTab'])){
	$SelectedTab = strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])){
	$SelectedTab = strtoupper($_GET['SelectedTab']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedTab);
	unset($SelectedType);
}

$Errors = array();
$InputError=0;
$i=0;
if (isset($_POST['process'])) {

	if ($_POST['SelectedTab'] == '') {
		$InputError=1;
		echo prnMsg(_('You have not selected a tab to maintain the expenses on'),'error');
		echo '<br />';
		$Errors[$i] = 'TabName';
		$i++;
		unset($SelectedTab);
		unset($_POST['SelectedTab']);
	}
}

if (isset($_POST['submit'])) {

	if ($_POST['SelectedExpense']=='') {
		$InputError=1;
		echo prnMsg(_('You have not selected an expense to add to this tab'),'error');
		$Errors[$i] = 'TabName';
		$i++;
	}

	if ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM pctabexpenses
			     WHERE typetabcode= '" .  $_POST['SelectedTab'] . "'
				 AND codeexpense = '" .  $_POST['SelectedExpense'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] >0) {
			$InputError = 1;
			prnMsg( _('The Expense') . ' ' . $_POST['codeexpense'] . ' ' ._('already exists in this Type of Tab'),'error');
		} else {
			// Add new record on submit
			$sql = "INSERT INTO pctabexpenses (typetabcode,
												codeexpense)
										VALUES ('" . $_POST['SelectedTab'] . "',
												'" . $_POST['SelectedExpense'] . "')";

			$msg = _('Expense code:') . ' ' . $_POST['SelectedExpense'].' '._('for Type of Tab:') .' '. $_POST['SelectedTab'] .  ' ' . _('has been created');
			$checkSql = "SELECT count(typetabcode)
							FROM pctypetabs";
			$result = DB_query($checkSql, $db);
			$row = DB_fetch_row($result);
		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		unset($_POST['SelectedExpense']);
	}

} elseif ( isset($_GET['delete']) ) {
	$sql="DELETE FROM pctabexpenses
		WHERE typetabcode='".$SelectedTab."'
		AND codeexpense='".$SelectedType."'";
	$ErrMsg = _('The Tab Type record could not be deleted because');
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg(_('Expense code').' '. $SelectedType .' '. _('for type of tab').' '. $SelectedTab .' '. _('has been deleted') ,'success');
	unset ($SelectedType);
	unset($_GET['delete']);
}

if (!isset($SelectedTab)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">'; //Main table

	echo '<tr><td>' . _('Select Project Tab') . ':</td><td><select name="SelectedProject">';

	DB_free_result($result);
	$SQL = "SELECT tabcode
			FROM pctabs";

	$result = DB_query($SQL,$db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($SelectedTab) and $myrow['tabcode']==$SelectedTab) {
			echo '<option selected="True" value="' . $myrow['tabcode'] . '">' . $myrow['tabcode'] . '</option>';
		} else {
			echo '<option value="' . $myrow['tabcode'] . '">' . $myrow['tabcode'] . '</option>';
		}

	} //end while loop

	echo '</select></td></tr>';

	   	echo '</table>'; // close table in first column
   	echo '</td></tr></table>'; // close main table

	echo '<br /><div class="centre">
			<input type="submit" name=process value="' . _('Accept') . '" />
			<input type="submit" name=Cancel value="' . _('Cancel') . '" /></div>';

	echo '</form>';

} else {

	echo '<p><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Select another project') . '</a></div></p>';

	$sql = "SELECT pctabexpenses.codeexpense, pcexpenses.description
			FROM pctabexpenses,pcexpenses
			WHERE pctabexpenses.codeexpense=pcexpenses.codeexpense
				AND pctabexpenses.typetabcode='".$SelectedTab."'
			ORDER BY pctabexpenses.codeexpense ASC";

	$result = DB_query($sql,$db);

	echo '<br /><table class="selection">';
	echo '<tr><th colspan="3"><font size="2" color="#616161">' . _('Project balances for ') . ' ' .$_POST['SelectedProject']. '</font></th></tr>';
	echo '<tr>
		<th>' . _('Expense Code') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Balance') . ' (' . $Currency . ')</th>
	</tr>';

	$k=0; //row colour counter

	$BalanceSQL="SELECT sum(amount) as balance FROM pcashdetails WHERE tabcode='".$_POST['SelectedProject']."' AND codeexpense='ASSIGNCASH'";
	$BalanceResult=DB_query($BalanceSQL, $db);
	$BalanceRow=DB_fetch_array($BalanceResult);
	printf('<td>CASH</td>
			<td>Assigned Cash</td>
			<td class="number">%s</td>
		</tr>',
		locale_money_format($BalanceRow['balance'], $Currency));

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$BalanceSQL="SELECT sum(amount) as balance FROM pcashdetails WHERE tabcode='".$_POST['SelectedProject']."' AND codeexpense='".$myrow[0]."'";
		$BalanceResult=DB_query($BalanceSQL, $db);
		$BalanceRow=DB_fetch_array($BalanceResult);

		if (!isset($BalanceRow['balance'])) {
			$BalanceRow['balance']=0;
		}
		printf('<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>',
			$myrow[0],
			$myrow[1],
			locale_money_format($BalanceRow['balance'], $Currency));
		$k++;
	}
	//END WHILE LIST LOOP
	echo '</table><br />';
}

include('includes/footer.inc');
?>