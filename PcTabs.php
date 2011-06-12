<?php
/* $Id$ */

//$PageSecurity = 15;

include('includes/session.inc');
$title = _('Maintenance Of Petty Cash Tabs');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Payment Entry')
	. '" alt="" />' . ' ' . $title . '</p>';

if (isset($_POST['SelectedTab'])){
	$SelectedTab = strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])){
	$SelectedTab = strtoupper($_GET['SelectedTab']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedTab);
	unset($_POST['tabcode'] );
	unset($_POST['SelectUser'] );
	unset($_POST['SelectTabs']);
	unset($_POST['SelectCurrency']);
	unset($_POST['tablimit']);
	unset($_POST['SelectAssigner']);
	unset($_POST['SelectAuthorizer']);
	unset($_POST['glaccountcash']);
	unset($_POST['glaccountpcashtab']);
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

	if ($_POST['tabcode']=='' OR $_POST['tabcode']==' ' OR $_POST['tabcode']=='  ') {
		$InputError = 1;
		prnMsg('<br />' . _('The Tab code cannot be an empty string or spaces'),'error');
		$Errors[$i] = 'TabCode';
		$i++;
	} elseif (strlen($_POST['tabcode']) >20) {
		$InputError = 1;
		echo prnMsg(_('The Tab code must be twenty characters or less long'),'error');
		$Errors[$i] = 'TabCode';
		$i++;
	} elseif (($_POST['SelectUser'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a User for this tab'),'error');
		$Errors[$i] = 'UserName';
		$i++;
	} elseif (($_POST['SelectTabs'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a type of tab from the list'),'error');
		$Errors[$i] = 'TabType';
		$i++;
	} elseif (($_POST['SelectAssigner'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a User to assign cash to this tab'),'error');
		$Errors[$i] = 'AssignerName';
		$i++;
	} elseif (($_POST['SelectAuthorizer'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a User to authorise this tab'),'error');
		$Errors[$i] = 'AuthorizerName';
		$i++;
	} elseif (($_POST['glaccountcash'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a General ledger code for the cash to be assigned from'),'error');
		$Errors[$i] = 'GLCash';
		$i++;
	} elseif (($_POST['glaccountpcashtab'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a General ledger code for this petty cash tab'),'error');
		$Errors[$i] = 'GLTab';
		$i++;
	}

	if (isset($SelectedTab) AND $InputError !=1) {

		$sql = "UPDATE pctabs
			SET usercode = '" . $_POST['SelectUser'] . "',
			typetabcode = '" . $_POST['SelectTabs'] . "',
			currency = '" . $_POST['SelectCurrency'] . "',
			tablimit = '" . $_POST['tablimit'] . "',
			assigner = '" . $_POST['SelectAssigner'] . "',
			authorizer = '" . $_POST['SelectAuthorizer'] . "',
			glaccountassignment = '" . $_POST['glaccountcash'] . "',
			glaccountpcash = '" . $_POST['glaccountpcashtab'] . "'
			WHERE tabcode = '".$SelectedTab."'";

		$msg = _('The Petty Cash Tab') . ' ' . $SelectedTab . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
				 FROM pctabs
				 WHERE tabcode = '" . $_POST['tabcode'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Tab ') . $_POST['tabcode'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO pctabs
						(tabcode,
			 			 usercode,
						 typetabcode,
						 currency,
						 tablimit,
						 assigner,
						 authorizer,
						 glaccountassignment,
						 glaccountpcash)
				VALUES ('" . $_POST['tabcode'] . "',
					'" . $_POST['SelectUser'] . "',
					'" . $_POST['SelectTabs'] . "',
					'" . $_POST['SelectCurrency'] . "',
					'" . $_POST['tablimit'] . "',
					'" . $_POST['SelectAssigner'] . "',
					'" . $_POST['SelectAuthorizer'] . "',
					'" . $_POST['glaccountcash'] . "',
					'" . $_POST['glaccountpcashtab'] . "')";

			$msg = _('The Petty Cash Tab') . ' ' . $_POST["tabcode"] .  ' ' . _('has been created');

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');

		unset($SelectedTab);
		unset($_POST['tabcode'] );
		unset($_POST['SelectUser'] );
		unset($_POST['SelectTabs']);
		unset($_POST['SelectCurrency']);
		unset($_POST['tablimit']);
		unset($_POST['SelectAssigner']);
		unset($_POST['SelectAuthorizer']);
		unset($_POST['glaccountcash']);
		unset($_POST['glaccountpcashtab']);


	}

} elseif ( isset($_GET['delete']) ) {

			$sql="DELETE FROM pctabs WHERE tabcode='".$SelectedTab."'";
			$ErrMsg = _('The Tab record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('The Petty Cash Tab') .  ' ' . $SelectedTab  . ' ' . _('has been deleted') ,'success');

			unset ($SelectedTab);
			unset($_GET['delete']);
}

if (!isset($SelectedTab)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTab will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT tabcode,
					usercode,
					pctabs.typetabcode,
					pctypetabs.typetabdescription,
					currency,
					tablimit,
					assigner,
					authorizer,
					glaccountassignment,
					glaccountpcash
				FROM pctabs
				LEFT JOIN pctypetabs
				ON pctabs.typetabcode=pctypetabs.typetabcode
				ORDER BY tabcode";
	$result = DB_query($sql,$db);

	echo '<br /><table class=selection>';
	echo '<tr>
		<th>' . _('Tab Code') . '</th>
		<th>' . _('User Name') . '</th>
		<th>' . _('Type Of Tab') . '</th>
		<th>' . _('Currency') . '</th>
		<th>' . _('Limit') . '</th>
		<th>' . _('Assigner') . '</th>
		<th>' . _('Authorizer') . '</th>
		<th>' . _('GL Account For Cash Assignment') . '</th>
		<th>' . _('GL Account Petty Cash Tab') . '</th>
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

	$sqldes="SELECT accountname
				FROM chartmaster
				WHERE accountcode='". $myrow['glaccountassignment'] . "'";

	$ResultDes = DB_query($sqldes,$db);
	$Description=DB_fetch_array($ResultDes);

	$sqlname="SELECT accountname
				FROM chartmaster
				WHERE accountcode='". $myrow['glaccountpcash'] . "'";

	$ResultName = DB_query($sqlname,$db);
	$DescriptionName=DB_fetch_array($ResultName);


	printf('<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td class=number>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedTab=%s">' . _('Edit') . '</td>
		<td><a href="%sSelectedTab=%s&delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this code and all the description it may have set up?') . '");\'>' . _('Delete') . '</td>
		</tr>',
		$myrow['tabcode'],
		$myrow['usercode'],
		$myrow['typetabdescription'],
		$myrow['currency'],
		number_format($myrow['tablimit'],2),
		$myrow['authorizer'],
		$myrow[6],
		$myrow['glaccountassignment'].' - '.$Description[0],
		$myrow['glaccountpcash'].' - '.$DescriptionName[0],
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow['tabcode'],
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow['tabcode']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedTab)) {

	echo '<p><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Show All Petty Cash Tabs Defined') . '</a></div><p>';
}
if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p><table class=selection>'; //Main table


	if ( isset($SelectedTab) AND $SelectedTab!='' ) {

		$sql = "SELECT * FROM pctabs
				WHERE tabcode='".$SelectedTab."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['tabcode'] = $myrow['tabcode'];
		$_POST['SelectUser']  = $myrow['usercode'];
		$_POST['SelectTabs']  = $myrow['typetabcode'];
		$_POST['SelectCurrency']  = $myrow['currency'];
		$_POST['tablimit']  = $myrow['tablimit'];
		$_POST['SelectAssigner']  = $myrow['assigner'];
		$_POST['SelectAuthorizer']  = $myrow['authorizer'];
		$_POST['glaccountcash']  = $myrow['glaccountassignment'];
		$_POST['glaccountpcashtab']  = $myrow['glaccountpcash'];


		echo '<input type=hidden name="SelectedTab" value="' . $SelectedTab . '">';
		echo '<input type=hidden name="tabcode" value="' . $_POST['tabcode']. '">';
		echo '<table class=selection> <tr><td>' . _('Tabcode') . ':</td><td>';

		// We dont allow the user to change an existing tab code

		echo $_POST['tabcode'] . '</td></tr>';

	} else 	{

		// This is a new tab so the user may volunteer a tab code
		if (isset($_POST['tabcode'])) {
			echo '<table class=selection><tr><td>' . _('Tabcode') . ':</td><td><input type="text" value="'.$_POST['tabcode'].'"
				' . (in_array('TypeTabCode',$Errors) ? 'class="inputerror"' : '' ) .' name="tabcode"></td></tr>';
		} else {
			echo '<table class=selection><tr><td>' . _('Tabcode') . ':</td><td><input type="text"
				' . (in_array('TypeTabCode',$Errors) ? 'class="inputerror"' : '' ) .' name="tabcode"></td></tr>';
		}
	}

	echo '<tr><td>' . _('User Name') . ':</td><td><select name="SelectUser">';

	$SQL = "SELECT userid,
					realname
		FROM www_users
		ORDER BY userid";

	$UsersResult = DB_query($SQL,$db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($UsersResult)) {
		if (isset($_POST['SelectUser']) and $myrow['userid']==$_POST['SelectUser']) {
			echo '<option selected value="' . $myrow['userid'] . '">' . $myrow['userid'].' - '.$myrow['realname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['userid'] . '">' . $myrow['userid'].' - '.$myrow['realname'] . '</option>';
		}

	} //end while loop get authorizer

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Type Of Tab') . ':</td><td><select name="SelectTabs">';

	DB_free_result($result);
	$SQL = "SELECT typetabcode,
					typetabdescription
				FROM pctypetabs
				ORDER BY typetabcode";

	$result = DB_query($SQL,$db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['typetabcode']==$_POST['SelectTabs']) {
			echo '<option selected value="'.$myrow['typetabcode'] . '">' . $myrow['typetabcode'].' - ' . $myrow['typetabdescription'] . '</option>';
		} else {
			echo '<option value="'.$myrow['typetabcode'] . '">' . $myrow['typetabcode'].' - ' . $myrow['typetabdescription'] . '</option>';
		}

	} //end while loop get type of tab

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Currency') . ':</td><td><select name="SelectCurrency">';

	DB_free_result($result);
	$SQL = "SELECT currency,currabrev
		FROM currencies";

	$result = DB_query($SQL,$db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectCurrency']) and $myrow['currabrev']==$_POST['SelectCurrency']) {
			echo '<option selected value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		} else if (!isset($_POST['SelectCurrency']) and ($_SESSION['CompanyRecord']['currencydefault']==$myrow['currabrev'])) {
			echo '<option selected value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		} else {
			echo '<option value=' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		}
	} //end while loop get type of tab

	echo '</select></td></tr>';

	if (!isset($_POST['tablimit'])) {
		$_POST['tablimit']=0;
	}

	echo '<tr><td>' . _('Limit Of Tab') . ':</td><td><input type="text" class="number" name="tablimit" size="12" maxlength="11" value="' . $_POST['tablimit'] . '"></td></tr>';

	echo '<tr><td>' . _('Cash Assigner') . ':</td><td><select name="SelectAssigner">';

	DB_data_seek($UsersResult);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($UsersResult)) {
		if (isset($_POST['SelectAssigner']) and $myrow['userid']==$_POST['SelectAssigner']) {
			echo '<option selected value="' . $myrow['userid'] . '">' . $myrow['userid'].' - '.$myrow['realname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['userid'] . '">' . $myrow['userid'].' - '.$myrow['realname'] . '</option>';
		}

	} //end while loop get authorizer

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Authorizer') . ':</td><td><select name="SelectAuthorizer">';

	DB_data_seek($UsersResult);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($UsersResult)) {
		if (isset($_POST['SelectAuthorizer']) and $myrow['userid']==$_POST['SelectAuthorizer']) {
			echo '<option selected value="' . $myrow['userid'] . '">' . $myrow['userid'].' - '.$myrow['realname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['userid'] . '">' . $myrow['userid'].' - '.$myrow['realname'] . '</option>';
		}

	} //end while loop get authorizer

	echo '</select></td></tr>';

	echo '<tr><td>' . _('GL Account Cash Assignment') . ':</td><td><select name="glaccountcash">';

	DB_free_result($result);
	$SQL = "SELECT chartmaster.accountcode, chartmaster.accountname
		FROM chartmaster, bankaccounts
		WHERE chartmaster.accountcode = bankaccounts.accountcode
		ORDER BY chartmaster.accountcode";

	$result = DB_query($SQL,$db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['glaccountcash']) and $myrow['accountcode']==$_POST['glaccountcash']) {
			echo '<option selected value="'.$myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
		}

	} //end while loop

	echo '</select></td></tr>';

	echo '<tr><td>' . _('GL Account Petty Cash Tab') . ':</td><td><select name="glaccountpcashtab">';

	DB_free_result($result);
	$SQL = "SELECT accountcode, accountname
		FROM chartmaster
		ORDER BY accountcode";

	$result = DB_query($SQL,$db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['glaccountpcashtab']) and $myrow['accountcode']==$_POST['glaccountpcashtab']) {
			echo '<option selected value="'.$myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="'.$myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
		}

	} //end while loop

	echo '</select></td></tr>';
   	echo '</td></tr></table>'; // close main table

	echo '<p><div class="centre"><input type=submit name=submit value="' . _('Accept') . '"><input type=submit name=Cancel value="' . _('Cancel') . '"></div>';

	echo '</form>';

} // end if user wish to delete


include('includes/footer.inc');
?>