<?php
/* $Id$ */

include ('includes/session.php');

$Title = _('Create or Modify Insurance Company Details');

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/CountriesArray.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($Errors)) {
	unset($Errors);
}

if (isset($_GET['ID'])) {
	$_POST['ID'] = $_GET['ID'];
}

$Errors = array();

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	$InputError = 0;
	if (strlen($_POST['Name']) > 40 or strlen($_POST['Name']) == 0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be forty characters or less long'), 'error');
	} elseif (strlen($_POST['Address1']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'), 'error');
	} elseif (strlen($_POST['Address2']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'), 'error');
	} elseif (strlen($_POST['Address3']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'), 'error');
	} elseif (strlen($_POST['Address4']) > 50) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be fifty characters or less long'), 'error');
	} elseif (strlen($_POST['Address5']) > 20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'), 'error');
	} elseif (strlen($_POST['Address6']) > 15) {
		$InputError = 1;
		prnMsg(_('The Line 6 of the address must be fifteen characters or less long'), 'error');
	} elseif (strlen($_POST['Phone']) > 25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'), 'error');
	} elseif (strlen($_POST['Fax']) > 25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'), 'error');
	} elseif (strlen($_POST['Email']) > 55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'), 'error');
	} elseif (strlen($_POST['Email']) > 0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'), 'error');
	}

	if ($InputError != 1) {

		if (!isset($_POST['New'])) {
			$SQL = "UPDATE insuranceco SET
					name='" . $_POST['Name'] . "',
					address1='" . $_POST['Address1'] . "',
					address2='" . $_POST['Address2'] . "',
					address3='" . $_POST['Address3'] . "',
					address4='" . $_POST['Address4'] . "',
					address5='" . $_POST['Address5'] . "',
					address6='" . $_POST['Address6'] . "',
					currcode='" . $_POST['CurrCode'] . "',
					paymentterms='" . $_POST['PaymentTerms'] . "',
					insurancetype='" . $_POST['InsuranceType'] . "',
					billingfrequency='" . $_POST['BillingFrequency'] . "',
					phoneno='" . $_POST['Phone'] . "',
					faxno='" . $_POST['Fax'] . "',
					contactname='" . $_POST['ContactName'] . "',
					email='" . $_POST['Email'] . "'
				  WHERE id = '" . $_POST['ID'] . "'";

			$ErrMsg = _('The company could not be updated because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(_('Company updated'), 'success');
		} else { //it is a new customer
			$_POST['ID'] = GetNextTransNo(520);

			$SQL = "INSERT INTO insuranceco (id,
											name,
											address1,
											address2,
											address3,
											address4,
											address5,
											address6,
											currcode,
											paymentterms,
											insurancetype,
											billingfrequency,
											phoneno,
											faxno,
											contactname,
											email
										) VALUES (
											'" . $_POST['ID'] . "',
											'" . $_POST['Name'] . "',
											'" . $_POST['Address1'] . "',
											'" . $_POST['Address2'] . "',
											'" . $_POST['Address3'] . "',
											'" . $_POST['Address4'] . "',
											'" . $_POST['Address5'] . "',
											'" . $_POST['Address6'] . "',
											'" . $_POST['CurrCode'] . "',
											'" . $_POST['PaymentTerms'] . "',
											'" . $_POST['InsuranceType'] . "',
											'" . $_POST['BillingFrequency'] . "',
											'" . $_POST['Phone'] . "',
											'" . $_POST['Fax'] . "',
											'" . $_POST['ContactName'] . "',
											'" . $_POST['Email'] . "'
										)";

			$ErrMsg = _('This company could not be added because');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(_('The Insurance Company has been successfully created'), 'success');

			include ('includes/footer.php');
			exit;
		}
	} else {
		prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
	}
	unset($_POST['ID']);
	unset($_POST['Name']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['CurrCode']);
	unset($_POST['PaymentTerms']);
	unset($_POST['InsuranceType']);
	unset($_POST['BillingFrequency']);
	unset($_POST['Phone']);
	unset($_POST['Fax']);
	unset($_POST['ContactName']);
	unset($_POST['Email']);
} elseif (isset($_POST['delete'])) {

	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	$SQL = "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['ID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('This company cannot be deleted because there are transactions that refer to it'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('transactions against this company');

	} else {
		$SQL = "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST['ID'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete the company record because orders have been created against it'), 'warn');
			echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('orders against this company');
		} else {
			$SQL = "SELECT COUNT(*) FROM salesanalysis WHERE cust='" . $_POST['ID'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this company record because sales analysis records exist for it'), 'warn');
				echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('sales analysis records against this company');
			} else {
				$SQL = "SELECT COUNT(*) FROM custbranch WHERE debtorno='" . $_POST['ID'] . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this company because there are branch records set up against it'), 'warn');
					echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('branch records relating to this company');
				}
			}
		}

	}
	if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
		$SQL = "DELETE FROM custcontacts WHERE debtorno='" . $_POST['ID'] . "'";
		$Result = DB_query($SQL);
		$SQL = "DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['ID'] . "'";
		$Result = DB_query($SQL);
		prnMsg(_('company') . ' ' . $_POST['DebtorNo'] . ' ' . _('has been deleted - together with all the associated contacts') . ' !', 'success');
		include ('includes/footer.php');
		unset($_SESSION['CustomerID']);
		exit;
	} //end if Delete Customer
	
}

if (isset($reset)) {
	unset($_POST['Name']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['Phone']);
	unset($_POST['Fax']);
	unset($_POST['Email']);
	unset($_POST['PaymentTerms']);
	unset($_POST['InsuranceType']);
	unset($_POST['CurrCode']);
	unset($_POST['ContactName']);
	unset($_POST['BillingFrequency']);
}

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';

if (!isset($_POST['ID'])) {
	$_POST['Name'] = '';
	$_POST['Phone'] = '';
	$_POST['Fax'] = '';
	$_POST['Address1'] = '';
	$_POST['Address2'] = '';
	$_POST['Address3'] = '';
	$_POST['Address4'] = '';
	$_POST['Address5'] = '';
	$_POST['Address6'] = '';
	$_POST['Email'] = '';
	$_POST['BillingFrequency'] = 0;
	$_POST['ContactName'] = '';
	$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
} else {
	echo '<input type="hidden" name="ID" value="' . $_POST['ID'] . '" />';
	$SQL = "SELECT name,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					currcode,
					phoneno,
					faxno,
					email,
					insurancetype,
					contactname,
					billingfrequency,
					paymentterms
				FROM insuranceco
				LEFT JOIN currencies
					ON insuranceco.currcode=currencies.currabrev
				WHERE id='" . $_POST['ID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Name'] = $MyRow['name'];
	$_POST['Phone'] = $MyRow['phoneno'];
	$_POST['Fax'] = $MyRow['faxno'];
	$_POST['Address1'] = $MyRow['address1'];
	$_POST['Address2'] = $MyRow['address2'];
	$_POST['Address3'] = $MyRow['address3'];
	$_POST['Address4'] = $MyRow['address4'];
	$_POST['Address5'] = $MyRow['address5'];
	$_POST['Address6'] = $MyRow['address6'];
	$_POST['Email'] = $MyRow['email'];
	$_POST['BillingFrequency'] = $MyRow['billingfrequency'];
	$_POST['ContactName'] = $MyRow['contactname'];
	$_POST['CurrCode'] = $MyRow['currcode'];
	$_POST['PaymentTerms'] = $MyRow['paymentterms'];
	$_POST['InsuranceType'] = $MyRow['insurancetype'];
}

echo '<tr>
		<td>' . _('Company Name') . ':</td>
		<td><input type="text" name="Name" size="42" maxlength="40" value="' . $_POST['Name'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Telephone') . ':</td>
		<td><input type="tel" name="Phone" size="30" maxlength="40" value="' . $_POST['Phone'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Facsimile') . ':</td>
		<td><input type="tel" name="Fax" size="30" maxlength="40" value="' . $_POST['Fax'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Email Address') . ':</td>
		<td><input type="email" name="Email" size="30" maxlength="40" value="' . $_POST['Email'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Address Line 1') . ':</td>
		<td><input type="text" name="Address1" size="42" maxlength="40" value="' . $_POST['Address1'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Address Line 2') . ':</td>
		<td><input type="text" name="Address2" size="42" maxlength="40" value="' . $_POST['Address2'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Address Line 3') . ':</td>
		<td><input type="text" name="Address3" size="42" maxlength="40" value="' . $_POST['Address3'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Address Line 4') . ':</td>
		<td><input type="text" name="Address4" size="42" maxlength="40" value="' . $_POST['Address4'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Address Line 5') . ':</td>
		<td><input type="text" name="Address5" size="22" maxlength="20" value="' . $_POST['Address5'] . '"></td>
	</tr>';
echo '<tr>
		<td>' . _('Country') . ':</td>
		<td><select minlength="0" name="Address6">';
foreach ($CountriesArray as $CountryEntry => $CountryName) {
	if (isset($_POST['Address6']) and (strtoupper($_POST['Address6']) == strtoupper($CountryName))) {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
	} elseif (!isset($_POST['Address6']) and $CountryName == "") {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
	} else {
		echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
	}
} //$CountriesArray as $CountryEntry => $CountryName
echo '</select>
			</td>
		</tr>';

echo '<tr>
		<td>' . _('Contact Name') . ':</td>
		<td><input type="text" name="ContactName" size="22" maxlength="30" value="' . $_POST['ContactName'] . '"></td>
	</tr>';

// Show Sales Type drop down list
$TypeResult = DB_query("SELECT typeid, typename FROM insurancetypes");
if (DB_num_rows($TypeResult) == 0) {
	$DataError = 1;
	echo '<tr>
			<td>' . _('Insurance Type') . ':</td>
			<td>' . _('No insurance company types defined') . '</td><td><a class="ButtonLink" href="InsuranceCompanyTypes.php?" target="_parent">Setup Types</a></td>
		</tr>';
	echo '';
} else {
	echo '<tr>
			<td>' . _('Insurance Type') . ':</td>
			<td><select name="InsuranceType">';
	echo '<option value=""></option>';
	while ($MyTypeRow = DB_fetch_array($TypeResult)) {
		if (isset($_POST['InsuranceType']) and $_POST['InsuranceType'] == $MyTypeRow['typeid']) {
			echo '<option selected="selected" value="' . $MyTypeRow['typeid'] . '">' . $MyTypeRow['typename'] . '</option>';
		} else {
			echo '<option value="' . $MyTypeRow['typeid'] . '">' . $MyTypeRow['typename'] . '</option>';
		}
	} //end while loopre
	echo '</select>
				</td>
			</tr>';
}

$Result = DB_query("SELECT terms, termsindicator FROM paymentterms");
if (DB_num_rows($Result) == 0) {
	$DataError = 1;
	echo '<tr>
			<td>' . _('Payment Terms') . ':</td>
			<td>' . _('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first') . '</td>
		</tr>';
} else {

	echo '<tr>
			<td>' . _('Payment Terms') . ':</td>
			<td><select name="PaymentTerms">';

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['termsindicator'] == $_POST['PaymentTerms']) {
			echo '<option selected="selected" value="' . $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
		}
	} //end while loop
	DB_data_seek($Result, 0);

	echo '</select>
				</td>
			</tr>';
}

echo '<tr>
		<td>' . _('Billing Frequency') . '</td>
		<td><select name="BillingFrequency">';

if ($_POST['BillingFrequency'] == 0) {
	echo '<option value="0" selected="selected">' . _('Weekly') . '</option>';
} else {
	echo '<option value="0">' . _('Weekly') . '</option>';
}
if ($_POST['BillingFrequency'] == 1) {
	echo '<option value="1" selected="selected">' . _('Monthly') . '</option>';
} else {
	echo '<option value="1">' . _('Monthly') . '</option>';
}
if ($_POST['BillingFrequency'] == 2) {
	echo '<option value="2" selected="selected">' . _('Quarterly') . '</option>';
} else {
	echo '<option value="2">' . _('Quarterly') . '</option>';
}
if ($_POST['BillingFrequency'] == 3) {
	echo '<option value="3" selected="selected">' . _('Annualy') . '</option>';
} else {
	echo '<option value="3">' . _('Annualy') . '</option>';
}

echo '</select>
			</td>
		</tr>';
$Result = DB_query("SELECT currency, currabrev FROM currencies");
if (DB_num_rows($Result) == 0) {
	$DataError = 1;
	echo '<tr>
			<td>' . _('Default Currency') . ':</td>
			<td>' . _('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first') . '</td>
		</tr>';
} else {
	if (!isset($_POST['CurrCode'])) {
		$CurrResult = DB_query("SELECT currencydefault FROM companies WHERE coycode=1");
		$MyRow = DB_fetch_row($CurrResult);
		$_POST['CurrCode'] = $MyRow[0];
	}
	echo '<tr>
			<td>' . _('Customer Currency') . ':</td>
			<td><select name="CurrCode">';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($_POST['CurrCode'] == $MyRow['currabrev']) {
			echo '<option selected value=' . $MyRow['currabrev'] . '>' . $MyRow['currency'] . '</option>';
		} else {
			echo '<option value=' . $MyRow['currabrev'] . '>' . $MyRow['currency'] . '</option>';
		}
	} //end while loop
	DB_data_seek($Result, 0);

	echo '</select>
				</td>
			</tr>';
}

echo '</table>';

echo '<div class="centre">';
if (!isset($_POST['ID'])) {
	echo '<input type="submit" name="submit" value="' . _('Add New Company') . '" />
			<input type="reset" name="reset" value="' . _('Reset') . '" />';
	echo '<input type="hidden" name="New" value="True" />';
} else {
	echo '<input type="submit" name="submit" value="' . _('Update Company') . '" />';
	echo '<input type="reset" name="delete" value="' . _('Delete Company') . '" />';
}

echo '</div>';
echo '</form>';

if (!isset($_GET['Edit'])) {
	$SQL = "SELECT id,
				name,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				currencies.currency
			FROM insuranceco
			LEFT JOIN insurancetypes
				ON insuranceco.insurancetype=insurancetypes.typeid
			LEFT JOIN currencies
				ON insuranceco.currcode=currencies.currabrev";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<table>
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

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr>
					<td>' . $MyRow['id'] . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td>' . $MyRow['address1'] . '</td>
					<td>' . $MyRow['address2'] . '</td>
					<td>' . $MyRow['address3'] . '</td>
					<td>' . $MyRow['address4'] . '</td>
					<td>' . $MyRow['address5'] . '</td>
					<td>' . $MyRow['address6'] . '</td>
					<td>' . $MyRow['currency'] . '</td>
					<td><a href="' . $_SERVER['PHP_SELF'] . '?ID=' . urlencode($MyRow['id']) . '&Edit=True">' . _('Edit') . '</a></td>
					<td><a href="' . $_SERVER['PHP_SELF'] . '?ID=' . urlencode($MyRow['id']) . '&Delete=True">' . _('Delete') . '</a></td>
				</tr>';
		}
		echo '</table>';
	}
}

include ('includes/footer.php');
?>