<?php

/* $Id$ */

$PageSecurity=1;

include('includes/session.inc');

$title = _('Create or Modify Insurance Company Details');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Customer') .
	'" alt="" />' . ' ' . $title . '</p>';

if (isset($Errors)) {
	unset($Errors);
}

if (isset($_GET['Debtor'])) {
	$_POST['DebtorNo']=$_GET['Debtor'];
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['DebtorNo'] = strtoupper($_POST['DebtorNo']);

	$sql="SELECT COUNT(debtorno) FROM debtorsmaster WHERE debtorno='".$_POST['DebtorNo']."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0 and isset($_POST['New'])) {
		$InputError = 1;
		prnMsg( _('The company number already exists in the database'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif (strlen($_POST['CustName']) > 40 OR strlen($_POST['CustName'])==0) {
		$InputError = 1;
		prnMsg( _('The company name must be entered and be forty characters or less long'),'error');
		$Errors[$i] = 'CustName';
		$i++;
	} elseif (strlen($_POST['DebtorNo']) ==0) {
		$InputError = 1;
		prnMsg( _('The debtor code cannot be empty'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif ((ContainsIllegalCharacters($_POST['DebtorNo']) OR strpos($_POST['DebtorNo'], ' '))) {
		$InputError = 1;
		prnMsg( _('The customer code cannot contain any of the illefal characters') ,'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif (strlen($_POST['Address1']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address1';
		$i++;
	} elseif (strlen($_POST['Address2']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address2';
		$i++;
	} elseif (strlen($_POST['Address3']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address3';
		$i++;
	} elseif (strlen($_POST['Address4']) >50) {
		$InputError = 1;
		prnMsg( _('The Line 4 of the address must be fifty characters or less long'),'error');
		$Errors[$i] = 'Address4';
		$i++;
	} elseif (strlen($_POST['Address5']) >20) {
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address5';
		$i++;
	} elseif (strlen($_POST['Address6']) >15) {
		$InputError = 1;
		prnMsg( _('The Line 6 of the address must be fifteen characters or less long'),'error');
		$Errors[$i] = 'Address6';
		$i++;
	}
	elseif (strlen($_POST['Phone']) >25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Telephone';
		$i++;
	}
	elseif (strlen($_POST['Fax']) >25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Fax';
		$i++;
	}
	elseif (strlen($_POST['Email']) >55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}
	elseif (strlen($_POST['Email'])>0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}

	if ($InputError !=1){

		$sql="SELECT typeid FROM debtortype WHERE typename='Insurance'";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$InsuranceTypeID=$myrow['typeid'];

		if (!isset($_POST['New'])) {

			$sql = "SELECT count(id)
					  FROM debtortrans
					where debtorno = '" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_array($result);

			if ($myrow[0] == 0) {
			  $sql = "UPDATE debtorsmaster SET
					name='" . $_POST['CustName'] . "',
					address1='" . $_POST['Address1'] . "',
					address2='" . $_POST['Address2'] . "',
					address3='" . $_POST['Address3'] ."',
					address4='" . $_POST['Address4'] . "',
					address5='" . $_POST['Address5'] . "',
					address6='" . $_POST['Address6'] . "',
					currcode='" . $_POST['CurrCode'] . "',
					paymentterms='" . $_POST['PaymentTerms'] . "',
					taxref='" . $_POST['TaxRef'] . "',
					typeid='".$InsuranceTypeID."'
				  WHERE debtorno = '" . $_POST['DebtorNo'] . "'";
			} else {

			  $currsql = "SELECT currcode
					  		FROM debtorsmaster
							where debtorno = '" . $_POST['DebtorNo'] . "'";
			  $currresult = DB_query($currsql,$db);
			  $currrow = DB_fetch_array($currresult);
			  $OldCurrency = $currrow[0];

			  $sql = "UPDATE debtorsmaster SET
					name='" . $_POST['CustName'] . "',
					address1='" . $_POST['Address1'] . "',
					address2='" . $_POST['Address2'] . "',
					address3='" . $_POST['Address3'] ."',
					address4='" . $_POST['Address4'] . "',
					address5='" . $_POST['Address5'] . "',
					address6='" . $_POST['Address6'] . "',
					taxref='" . $_POST['TaxRef'] . "',
					typeid='".$InsuranceTypeID."'
				  WHERE debtorno = '" . $_POST['DebtorNo'] . "'";

			  if ($OldCurrency != $_POST['CurrCode']) {
			  	prnMsg( _('The currency code cannot be updated as there are already transactions for this customer'),'info');
			  }
			}

			$ErrMsg = _('The customer could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Customer updated'),'success');
			echo '<br />';

		} else { //it is a new customer
			/* set the DebtorNo if $AutoDebtorNo in config.php has been set to
			something greater 0 */

			$sql = "INSERT INTO debtorsmaster (
							debtorno,
							name,
							address1,
							address2,
							address3,
							address4,
							address5,
							address6,
							currcode,
							holdreason,
							salestype,
							paymentterms,
							taxref,
							typeid)
				VALUES ('" . $_POST['DebtorNo'] ."',
					'" . $_POST['CustName'] ."',
					'" . $_POST['Address1'] ."',
					'" . $_POST['Address2'] ."',
					'" . $_POST['Address3'] . "',
					'" . $_POST['Address4'] . "',
					'" . $_POST['Address5'] . "',
					'" . $_POST['Address6'] . "',
					'" . $_POST['CurrCode'] . "',
					'1',
					'GE',
					'" . $_POST['PaymentTerms'] . "',
					'" . $_POST['TaxRef'] . "',
					'" . $InsuranceTypeID . "'
					)";

			$ErrMsg = _('This company could not be added because');
			$result = DB_query($sql,$db,$ErrMsg);

			$BranchCode = substr($_POST['DebtorNo'],0,4);

			$sql = "INSERT INTO custbranch (
							branchcode,
							debtorno,
							brname,
							braddress1,
							braddress2,
							braddress3,
							braddress4,
							braddress5,
							braddress6,
							area,
							salesman,
							taxgroupid,
							defaultlocation,
							defaultshipvia)
						VALUES (
							'" . $BranchCode . "',
							'" . $_POST['DebtorNo'] ."',
							'" . $_POST['CustName'] ."',
							'" . $_POST['Address1'] ."',
							'" . $_POST['Address2'] ."',
							'" . $_POST['Address3'] . "',
							'" . $_POST['Address4'] . "',
							'" . $_POST['Address5'] . "',
							'" . $_POST['Address6'] . "',
							'" . $_POST['Area'] . "',
							'" . $_POST['Salesman'] . "',
							'" . $_POST['TaxGroup'] . "',
							'" . $_SESSION['UserStockLocation'] . "',
							'1'
						)";
			$ErrMsg = _('This company could not be added because');
			$result = DB_query($sql,$db,$ErrMsg);

			prnMsg( _('The Insurance Company has been successfully created'), 'success');

			include('includes/footer.inc');
			exit;
		}
	} else {
		prnMsg( _('Validation failed') . '. ' . _('No updates or deletes took place'),'error');
	}

} elseif (isset($_POST['delete'])) {

//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['DebtorNo'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('This company cannot be deleted because there are transactions that refer to it'),'warn');
		echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions against this company');

	} else {
		$sql= "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete the company record because orders have been created against it'),'warn');
			echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('orders against this company');
		} else {
			$sql= "SELECT COUNT(*) FROM salesanalysis WHERE cust='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg( _('Cannot delete this company record because sales analysis records exist for it'),'warn');
				echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records against this company');
			} else {
				$sql= "SELECT COUNT(*) FROM custbranch WHERE debtorno='" . $_POST['DebtorNo'] . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this company because there are branch records set up against it'),'warn');
					echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('branch records relating to this company');
				}
			}
		}

	}
	if ($CancelDelete==0) { //ie not cancelled the delete as a result of above tests
		$sql="DELETE FROM custcontacts WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		$sql="DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		prnMsg( _('company') . ' ' . $_POST['DebtorNo'] . ' ' . _('has been deleted - together with all the associated contacts') . ' !','success');
		include('includes/footer.inc');
		unset($_SESSION['CustomerID']);
		exit;
	} //end if Delete Customer
}

if(isset($reset)){
	unset($_POST['CustName']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['Phone']);
	unset($_POST['Fax']);
	unset($_POST['Email']);
	unset($_POST['HoldReason']);
	unset($_POST['PaymentTerms']);
	unset($_POST['Discount']);
	unset($_POST['DiscountCode']);
	unset($_POST['PymtDiscount']);
	unset($_POST['CreditLimit']);
// Leave Sales Type set so as to faciltate fast customer setup
//	unset($_POST['SalesType']);
	unset($_POST['DebtorNo']);
	unset($_POST['InvAddrBranch']);
	unset($_POST['TaxRef']);
	unset($_POST['CustomerPOLine']);
// Leave Type ID set so as to faciltate fast customer setup
//	unset($_POST['typeid']);
}

/*DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}
if (isset($_POST['ID'])){
	$ID = $_POST['ID'];
} elseif (isset($_GET['ID'])){
	$ID = $_GET['ID'];
} else {
	$ID='';
}
if (isset($_POST['ws'])){
	$ws = $_POST['ws'];
} elseif (isset($_GET['ws'])){
	$ws = $_GET['ws'];
}
if (isset($_POST['Edit'])){
	$Edit = $_POST['Edit'];
} elseif (isset($_GET['Edit'])){
	$Edit = $_GET['Edit'];
} else {
	$Edit='';
}

if (isset($_POST['Add'])){
	$Add = $_POST['Add'];
} elseif (isset($_GET['Add'])){
	$Add = $_GET['Add'];
}

/*If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field*/

/* First check that all the necessary items have been setup */

$SetupErrors=0; //Count errors
$sql="SELECT COUNT(typeabbrev)
				FROM salestypes";
$result=DB_query($sql, $db);
$myrow=DB_fetch_row($result);
if ($myrow[0]==0) {
	prnMsg( _('In order to create a new customer you must first set up at least one sales type/price list').'<br />'.
		_('Click').' '.'<a target="_blank" href="' . $rootpath . '/SalesTypes.php">' . _('here').' ' . '</a>'._('to set up your price lists'),'warning').'<br />';
	$SetupErrors += 1;
}
$sql="SELECT COUNT(typeid)
			FROM debtortype";
$result=DB_query($sql, $db);
$myrow=DB_fetch_row($result);
if ($myrow[0]==0) {
	prnMsg( _('In order to create a new customer you must first set up at least one customer type').'<br />'.
		_('Click').' '.'<a target="_blank" href="' . $rootpath . '/CustomerTypes.php">' . _('here').' ' . '</a>'._('to set up your customer types'),'warning');
	$SetupErrors += 1;
}

if ($SetupErrors>0) {
	echo '<br /><div class=centre><a href="'.$_SERVER['PHP_SELF'] .'" >'._('Click here to continue').'</a></div>';
	include('includes/footer.inc');
	exit;
}

$sql="SELECT debtorno,
			name,
			address1,
			address2,
			address3,
			address4,
			address5,
			address6,
			currencies.currency
			FROM debtorsmaster
			LEFT JOIN debtortype
				ON debtorsmaster.typeid=debtortype.typeid
			LEFT JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtortype.typename='Insurance'";
$result=DB_query($sql, $db);

echo '<table class="selection">
		<tr>
			<th>' . _('Company No') . '</th>
			<th>' . _('Name') . '</th>
			<th>' . _('Address1') . '</th>
			<th>' . _('Address2') . '</th>
			<th>' . _('Address3') . '</th>
			<th>' . _('Address4') . '</th>
			<th>' . _('Address5') . '</th>
			<th>' . _('Address6') . '</th>
			<th>' . _('Currency') . '</th>
		</tr>';

while ($myrow=DB_fetch_array($result)) {
	echo '<tr>
			<td>' . $myrow['debtorno'] . '</td>
			<td>' . $myrow['name'] . '</td>
			<td>' . $myrow['address1'] . '</td>
			<td>' . $myrow['address2'] . '</td>
			<td>' . $myrow['address3'] . '</td>
			<td>' . $myrow['address4'] . '</td>
			<td>' . $myrow['address5'] . '</td>
			<td>' . $myrow['address6'] . '</td>
			<td>' . $myrow['currency'] . '</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?Debtor='.$myrow['debtorno'].'&Edit=True">' . _('Edit') . '</a></td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?Debtor='.$myrow['debtorno'].'&Delete=True">' . _('Delete') . '</a></td>
		</tr>';
}
echo '</table><br />';

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection" cellspacing="4"><tr><td valign="top"><table class="selection">';

if (!isset($DebtorNo)) {
	echo '<tr><td>' . _('Company Code') . ':</td><td><input tabindex="1" type="text" name="DebtorNo" size="11" maxlength="10"></td></tr>';
} else {
	echo '<tr><td>' . _('Company Code') . ':</td><td>' . $DebtorNo . '</td></tr>';
	$sql = "SELECT debtorsmaster.debtorno,
					name,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					currencies.currency,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.email,
					taxref
				FROM debtorsmaster
				LEFT JOIN custbranch
					ON debtorsmaster.debtorno=custbranch.debtorno
				LEFT JOIN currencies
					ON debtorsmaster.currcode=currencies.currabrev
				WHERE debtorsmaster.debtorno='".$DebtorNo."'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['CustName'] = $myrow['name'];
	$_POST['Phone'] = $myrow['phoneno'];
	$_POST['Facsimile'] = $myrow['faxno'];
	$_POST['Address1'] = $myrow['address1'];
	$_POST['Address2'] = $myrow['address2'];
	$_POST['Address3'] = $myrow['address3'];
	$_POST['Address4'] = $myrow['address4'];
	$_POST['Address5'] = $myrow['address5'];
	$_POST['Address6'] = $myrow['address6'];
	$_POST['Email'] = $myrow['email'];
	$_POST['TaxRef'] = $myrow['taxref'];
}

echo '<tr><td>' . _('Company Name') . ':</td>
	<td><input tabindex=2 type="Text" name="CustName" size=42 maxlength=40 value="'.$_POST['CustName'].'"></td></tr>';
echo '<tr><td>' . _('Telephone') . ':</td>
	<td><input tabindex=2 type="Text" name="Phone" size=30 maxlength=40 value="'.$_POST['Phone'].'"></td></tr>';
echo '<tr><td>' . _('Facsimile') . ':</td>
	<td><input tabindex=2 type="Text" name="Fax" size=30 maxlength=40 value="'.$_POST['Facsimile'].'"></td></tr>';
echo '<tr><td>' . _('Email Address') . ':</td>
	<td><input tabindex=2 type="Text" name="Email" size=30 maxlength=40 value="'.$_POST['Email'].'"></td></tr>';
echo '<tr><td>' . _('Address Line 1') . ':</td>
	<td><input tabindex=3 type="Text" name="Address1" size=42 maxlength=40 value="'.$_POST['Address1'].'"></td></tr>';
echo '<tr><td>' . _('Address Line 2') . ':</td>
	<td><input tabindex=4 type="Text" name="Address2" size=42 maxlength=40 value="'.$_POST['Address2'].'"></td></tr>';
echo '<tr><td>' . _('Address Line 3') . ':</td>
	<td><input tabindex=5 type="Text" name="Address3" size=42 maxlength=40 value="'.$_POST['Address3'].'"></td></tr>';
echo '<tr><td>' . _('Address Line 4') . ':</td>
	<td><input tabindex=6 type="Text" name="Address4" size=42 maxlength=40 value="'.$_POST['Address4'].'"></td></tr>';
echo '<tr><td>' . _('Address Line 5') . ':</td>
	<td><input tabindex=7 type="Text" name="Address5" size=22 maxlength=20 value="'.$_POST['Address5'].'"></td></tr>';
echo '<tr><td>' . _('Address Line 6') . ':</td>
	<td><input tabindex=8 type="Text" name="Address6" size=17 maxlength=15 value="'.$_POST['Address6'].'"></td></tr>';
echo '</table></td><td valign="top"><table class="selection">';

echo '<tr><td>' . _('Tax Reference') . ':</td>
	<td><input tabindex=15 type="Text" name="TaxRef" size=22 maxlength=20 value="'.$_POST['TaxRef'].'"></td></tr>';

$result=DB_query("SELECT terms, termsindicator FROM paymentterms",$db);
if (DB_num_rows($result)==0){
	$DataError =1;
	echo '<tr><td colspan=2>' . prnMsg(_('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
} else {

	echo '<tr><td>' . _('Payment Terms') . ':</td>
		<td><select tabindex=15 name="PaymentTerms">';

	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
	} //end while loop
	DB_data_seek($result,0);

	echo '</select></td></tr>';
}
$result=DB_query("SELECT currency, currabrev FROM currencies",$db);
if (DB_num_rows($result)==0){
	$DataError =1;
	echo '<tr><td colspan=2>' . prnMsg(_('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
} else {
	if (!isset($_POST['CurrCode'])){
		$CurrResult = DB_query("SELECT currencydefault FROM companies WHERE coycode=1",$db);
		$myrow = DB_fetch_row($CurrResult);
		$_POST['CurrCode'] = $myrow[0];
	}
	echo '<tr><td>' . _('Customer Currency') . ':</td><td><select tabindex=17 name="CurrCode">';
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode']==$myrow['currabrev']){
			echo '<option selected value='. $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
		} else {
			echo '<option value='. $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result,0);

	echo '</select></td></tr>';
}

$sql = "SELECT areacode, areadescription FROM areas";
$result = DB_query($sql,$db);
if (DB_num_rows($result)==0){
	echo '</table>';
	prnMsg(_('There are no areas defined as yet') . ' - ' . _('customer branches must be allocated to an area') . '. ' . _('Please use the link below to define at least one sales area'),'error');
	echo '<br /><a href="'.$rootpath.'/Areas.php?">'._('Define Sales Areas').'</a>';
	include('includes/footer.inc');
	exit;
}

echo '<tr><td>'._('Sales Area').':</td>';
echo '<td><select tabindex=14 name="Area">';
while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['Area']) and $myrow['areacode']==$_POST['Area']) {
		echo '<option selected value="'.$myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
	} else {
		echo '<option value="'.$myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
	}
} //end while loop
echo '</select></td></tr>';

$sql = "SELECT salesmanname, salesmancode FROM salesman";
$result = DB_query($sql,$db);
echo '<tr><td>'._('Salesperson').':</td>';
echo '<td><select tabindex=13 name="Salesman">';

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['Salesman']) and $myrow['salesmancode']==$_POST['Salesman']) {
		echo '<option selected value="'.$myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
	} else {
		echo '<option value="'.$myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
	}
} //end while loop
echo '</select></td></tr>';

echo '<tr><td>'._('Tax Group').':</td>';
echo '<td><select tabindex=19 name="TaxGroup">';

DB_data_seek($result,0);

$sql = "SELECT taxgroupid, taxgroupdescription FROM taxgroups";
$result = DB_query($sql,$db);

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['TaxGroup']) and $myrow['taxgroupid']==$_POST['TaxGroup']) {
		echo '<option selected value="'. $myrow['taxgroupid'] . '">' . $myrow['taxgroupdescription'] . '</option>';
	} else {
		echo '<option value="'. $myrow['taxgroupid'] . '">' . $myrow['taxgroupdescription'] . '</option>';
	}

} //end while loop

echo '</select></td></tr>';

echo'</table></td></tr></table>';

echo '<tr><td colspan=2>';

if (isset($DebtorNo)) {
	$sql = "SELECT * FROM custcontacts where debtorno='".$DebtorNo."' ORDER BY contid";
	$result = DB_query($sql,$db);

	echo '<table class=selection>';
	if (isset($_GET['Modify'])) {
		echo '<tr>
				<th>' . _('Name') . '</th>
				<th>' . _('Role') . '</th>
				<th>' . _('Phone Number') . '</th>
				<th>' . _('Notes') . '</th>
			</tr>';
	} else {
		echo '<tr>
				<th>' . _('Name') . '</th>
				<th>' . _('Role') . '</th>
				<th>' . _('Phone Number') . '</th>
				<th>' . _('Notes') . '</th>
				<th>' . _('Edit') . '</th>
				<th colspan=2><input type="Submit" name="addcontact" value="Add Contact"></th>
			</tr>';
	}
	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}

		if (isset($_GET['Modify'])) {
			printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>',
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$myrow[5],
				$myrow[0],
				$myrow[1],
				$myrow[1]);
		} else {
			printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="AddCustomerContacts.php?Id=%s&DebtorNo=%s">'. _('Edit'). '</a></td>
				<td><a href="%sID=%s&DebtorNo=%s&delete=1">'. _('Delete'). '</a></td>
				</tr>',
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$myrow[5],
				$myrow[0],
				$myrow[1],
				$_SERVER['PHP_SELF'] . '?',
				$myrow[0],
				$myrow[1]);
		}
	}//END WHILE LIST LOOP
	echo '</table>';
	//	echo "<input type='Submit' name='addcontact' value='" . _('ADD Contact') . "'>";
	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?DebtorNo="'.$DebtorNo.'"&ID='.$ID.'&Edit'.$Edit.'>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (isset($Edit) and $Edit!='') {
		$SQLcustcontacts="SELECT * from custcontacts
							WHERE debtorno='".$DebtorNo."'
							and contid='".$ID."'";
		$resultcc = DB_query($SQLcustcontacts,$db);
		$myrowcc = DB_fetch_array($resultcc);
		$_POST['custname']=$myrowcc['contactname'];
		$_POST['role']=$myrowcc['role'];
		$_POST['phoneno']=$myrowcc['phoneno'];
		$_POST['notes']=$myrowcc['notes'];
		echo '<table class=selection>';
		echo '<tr>
				<td>' . _('Name') . '</td><td><input type=text name="custname" value="'.$_POST['custname'].'"></td></tr><tr>
				<td>' . _('Role') . '</td><td><input type=text name="role" value="'.$_POST['role'].'"></td></tr><tr>
				<td>' . _('Phone no') . '</td><td><input type="text" name="phoneno" value="'.$_POST['phoneno'].'"></td></tr><tr>
				<td>' . _('Notes') . '</td><td><textarea name="notes">'.$_POST['notes'].'</textarea></td></tr>
				<tr><td colspan=2><input type=submit name=update value=update></td></tr></table>';

	}
	if (isset($_POST['update'])) {

		$SQLupdatecc="UPDATE custcontacts
						SET contactname='".$_POST['custname']."',
						role='".$_POST['role']."',
						phoneno='".$_POST['phoneno']."',
						notes='".DB_escape_string($_POST['notes'])."'
						Where debtorno='".$DebtorNo."'
						and contid='".$Edit."'";
		$resultupcc = DB_query($SQLupdatecc,$db);
		echo '<br />'.$SQLupdatecc;
		echo '<meta http-equiv="Refresh" content="0; url="' . $_SERVER['PHP_SELF'] . '?DebtorNo='.$DebtorNo.'&ID='.$ID.'">';
	}
	if (isset($_GET['delete'])) {
		$SQl="DELETE FROM custcontacts where debtorno='".$DebtorNo."'
				and contid='".$ID."'";
		$resultupcc = DB_query($SQl,$db);

		echo '<meta http-equiv="Refresh" content="0; url=' . $_SERVER['PHP_SELF'] . '?DebtorNo='.$DebtorNo.'">';
		echo '<br />'.$SQl;
		prnmsg('Contact Deleted','success');
	}


	echo'</td></tr></table>';
 // end of main ifs
}
if (!isset($DebtorNo)) {
	echo '<br /><div class="centre"><input type="Submit" name="submit" value="' . _('Add New Company') .
		'">&nbsp;<input type=submit name="reset" value="' . _('Reset') . '"></div>';
} else {
	echo '<br /><div class="centre"><input type="Submit" name="submit" value="' . _('Update Company') . '">';
	echo '&nbsp;<input type="Submit" name="delete" value="' . _('Delete Company') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">';
}
if(isset($_POST['addcontact']) AND (isset($_POST['addcontact'])!=''))
{
	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/AddCustomerContacts.php?DebtorNo=' .$DebtorNo.'">';
}
echo '</div></form>';

include('includes/footer.inc');
?>