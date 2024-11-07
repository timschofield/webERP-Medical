<?php
// CompanyPreferences.php
// Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.

include('includes/session.php');
$ViewTopic= 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
$Title = _('Company Preferences');
include('includes/header.php');

if (isset($Errors)) {
	unset($Errors);
}

//initialise no input errors assumed initially before we test
$InputError = 0;
$Errors = array();
$i=1;

if (isset($_POST['submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['CoyName']) > 50 OR mb_strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		$i++;
	}

	if (mb_strlen($_POST['Email'])>0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}

	if ($InputError !=1){

		$sql = "UPDATE companies SET coyname='" . $_POST['CoyName'] . "',
									companynumber = '" . $_POST['CompanyNumber'] . "',
									gstno='" . $_POST['GSTNo'] . "',
									regoffice1='" . $_POST['RegOffice1'] . "',
									regoffice2='" . $_POST['RegOffice2'] . "',
									regoffice3='" . $_POST['RegOffice3'] . "',
									regoffice4='" . $_POST['RegOffice4'] . "',
									regoffice5='" . $_POST['RegOffice5'] . "',
									regoffice6='" . $_POST['RegOffice6'] . "',
									telephone='" . $_POST['Telephone'] . "',
									fax='" . $_POST['Fax'] . "',
									email='" . $_POST['Email'] . "',
									currencydefault='" . $_POST['CurrencyDefault'] . "',
									debtorsact='" . $_POST['DebtorsAct'] . "',
									pytdiscountact='" . $_POST['PytDiscountAct'] . "',
									creditorsact='" . $_POST['CreditorsAct'] . "',
									payrollact='" . $_POST['PayrollAct'] . "',
									grnact='" . $_POST['GRNAct'] . "',
									commissionsact='" . $_POST['CommAct'] . "',
									exchangediffact='" . $_POST['ExchangeDiffAct'] . "',
									purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
									retainedearnings='" . $_POST['RetainedEarnings'] . "',
									gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
									gllink_creditors='" . $_POST['GLLink_Creditors'] . "',
									gllink_stock='" . $_POST['GLLink_Stock'] ."',
									freightact='" . $_POST['FreightAct'] . "'
								WHERE coycode=1";

			$ErrMsg =  _('The company preferences could not be updated because');
			$result = DB_query($sql,$ErrMsg);
			prnMsg( _('Company preferences updated'),'success');

			/* Alter the exchange rates in the currencies table */

			/* Get default currency rate */
			$sql="SELECT rate from currencies WHERE currabrev='" . $_POST['CurrencyDefault'] . "'";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
			$NewCurrencyRate=$myrow[0];

			/* Set new rates */
			$sql="UPDATE currencies SET rate=rate/" . $NewCurrencyRate;
			$ErrMsg =  _('Could not update the currency rates');
			$result = DB_query($sql,$ErrMsg);

			/* End of update currencies */

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include('includes/GetConfig.php');
			$ForceConfigReload = False;

	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $Title . '</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', _('Company Profile Settings'), '</legend>';

if ($InputError != 1) {
	$sql = "SELECT coyname,
					gstno,
					companynumber,
					regoffice1,
					regoffice2,
					regoffice3,
					regoffice4,
					regoffice5,
					regoffice6,
					telephone,
					fax,
					email,
					currencydefault,
					debtorsact,
					pytdiscountact,
					creditorsact,
					payrollact,
					grnact,
					commissionsact,
					exchangediffact,
					purchasesexchangediffact,
					retainedearnings,
					gllink_debtors,
					gllink_creditors,
					gllink_stock,
					freightact
				FROM companies
				WHERE coycode=1";

	$ErrMsg =  _('The company preferences could not be retrieved because');
	$result = DB_query($sql,$ErrMsg);


	$myrow = DB_fetch_array($result);

	$_POST['CoyName'] = $myrow['coyname'];
	$_POST['GSTNo'] = $myrow['gstno'];
	$_POST['CompanyNumber']  = $myrow['companynumber'];
	$_POST['RegOffice1']  = $myrow['regoffice1'];
	$_POST['RegOffice2']  = $myrow['regoffice2'];
	$_POST['RegOffice3']  = $myrow['regoffice3'];
	$_POST['RegOffice4']  = $myrow['regoffice4'];
	$_POST['RegOffice5']  = $myrow['regoffice5'];
	$_POST['RegOffice6']  = $myrow['regoffice6'];
	$_POST['Telephone']  = $myrow['telephone'];
	$_POST['Fax']  = $myrow['fax'];
	$_POST['Email']  = $myrow['email'];
	$_POST['CurrencyDefault']  = $myrow['currencydefault'];
	$_POST['DebtorsAct']  = $myrow['debtorsact'];
	$_POST['PytDiscountAct']  = $myrow['pytdiscountact'];
	$_POST['CreditorsAct']  = $myrow['creditorsact'];
	$_POST['PayrollAct']  = $myrow['payrollact'];
	$_POST['GRNAct'] = $myrow['grnact'];
	$_POST['CommAct'] = $myrow['commissionsact'];
	$_POST['ExchangeDiffAct']  = $myrow['exchangediffact'];
	$_POST['PurchasesExchangeDiffAct']  = $myrow['purchasesexchangediffact'];
	$_POST['RetainedEarnings'] = $myrow['retainedearnings'];
	$_POST['GLLink_Debtors'] = $myrow['gllink_debtors'];
	$_POST['GLLink_Creditors'] = $myrow['gllink_creditors'];
	$_POST['GLLink_Stock'] = $myrow['gllink_stock'];
	$_POST['FreightAct'] = $myrow['freightact'];
}

echo '<field>
		<label for="CoyName">' . _('Name') . ' (' . _('to appear on reports') . '):</label>
		<input '.(in_array('CoyName',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="1" type="text" autofocus="autofocus" required="required" name="CoyName" value="' . stripslashes($_POST['CoyName']) . '"  pattern="?!^ +$"  title="" size="52" maxlength="50" />
		<fieldhelp>' . _('Enter the name of the business. This will appear on all reports and at the top of each screen. ') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="CoyNumber">' . _('Official Company Number') . ':</label>
		<input '.(in_array('CoyNumber',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="2" type="text" name="CompanyNumber" value="' . $_POST['CompanyNumber'] . '" size="22" maxlength="20" />
	</field>';

echo '<field>
		<label for="TaxRef">' . _('Tax Authority Reference') . ':</label>
		<input '.(in_array('TaxRef',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="3" type="text" name="GSTNo" value="' . $_POST['GSTNo'] . '" size="22" maxlength="20" />
	</field>';

echo '<field>
		<label for="RegOffice1">' . _('Address Line 1') . ':</label>
		<input '.(in_array('RegOffice1',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="4" type="text" name="RegOffice1" title="" required="required" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice1']) . '" />
		<fieldhelp>' . _('Enter the first line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="RegOffice2">' . _('Address Line 2') . ':</label>
		<input '.(in_array('RegOffice2',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="5" type="text" name="RegOffice2" title="" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice2']) . '" />
		<fieldhelp>' . _('Enter the second line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="RegOffice3">' . _('Address Line 3') . ':</label>
		<input '.(in_array('RegOffice3',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="6" type="text" name="RegOffice3" title="" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice3']) . '" />
		<fieldhelp>' . _('Enter the third line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="RegOffice4">' . _('Address Line 4') . ':</label>
		<input '.(in_array('RegOffice4',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="7" type="text" name="RegOffice4" title="" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice4']) . '" />
		<fieldhelp>' . _('Enter the fourth line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
</field>';

echo '<field>
		<label for="RegOffice5">' . _('Address Line 5') . ':</label>
		<input '.(in_array('RegOffice5',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="8" type="text" name="RegOffice5" size="22" maxlength="20" value="' . stripslashes($_POST['RegOffice5']) . '" />
	</field>';

echo '<field>
		<label for="RegOffice6">' . _('Address Line 6') . ':</label>
		<input '.(in_array('RegOffice6',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="9" type="text" name="RegOffice6" size="17" maxlength="15" value="' . stripslashes($_POST['RegOffice6']) . '" />
	</field>';

echo '<field>
		<label for="Telephone">' . _('Telephone Number') . ':</label>
		<input ', (in_array('Telephone',$Errors) ?  'class="inputerror"' : '' ), ' maxlength="25" name="Telephone" required="required" size="26" tabindex="10" type="tel" title="" value="', $_POST['Telephone'], '" />
		<fieldhelp>', _('Enter the main telephone number of the company registered office. This will appear on invoices and statements.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Fax">' . _('Facsimile Number') . ':</label>
		<input ', (in_array('Fax',$Errors) ?  'class="inputerror"' : '' ), ' maxlength="25" name="Fax" size="26" tabindex="11" type="tel" value="', $_POST['Fax'], '" />
	</field>';

echo '<field>
		<label for="Email">' . _('Email Address') . ':</label>
		<input '.(in_array('Email',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="12" type="email" name="Email" title="" required="required" placeholder="accounts@example.com" size="50" maxlength="55" value="' . $_POST['Email'] . '" />
		<fieldhelp>' . _('Enter the main company email address. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';


$result=DB_query("SELECT currabrev, currency FROM currencies");
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.

echo '<field>
		<label for="CurrencyDefault">', _('Home Currency'), ':</label>
		<select id="CurrencyDefault" name="CurrencyDefault" tabindex="13" >';

while ($myrow = DB_fetch_array($result)) {
	if ($_POST['CurrencyDefault']==$myrow['currabrev']){
		echo '<option selected="selected" value="'. $myrow['currabrev'] . '">' . $CurrencyName[$myrow['currabrev']] . '</option>';
	} else {
		echo '<option value="' . $myrow['currabrev'] . '">' . $CurrencyName[$myrow['currabrev']] . '</option>';
	}
} //end while loop

DB_free_result($result);

echo '</select>
	</field>';

$result=DB_query("SELECT accountcode,
						accountname
					FROM chartmaster INNER JOIN accountgroups
					ON chartmaster.group_=accountgroups.groupname
					WHERE accountgroups.pandl=0
					ORDER BY chartmaster.accountcode");

echo '<field>
		<label>' . _('Debtors Control GL Account') . ':</label>
		<select tabindex="14" title="" name="DebtorsAct">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['DebtorsAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select>
	<fieldhelp>' . _('Select the general ledger account to be used for posting the local currency value of all customer transactions to. This account will always represent the total amount owed by customers to the business. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Creditors Control GL Account') . ':</label>
		<select tabindex="15" title="" name="CreditorsAct">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['CreditorsAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="' . $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select>
	<fieldhelp>' . _('Select the general ledger account to be used for posting the local currency value of all supplier transactions to. This account will always represent the total amount owed by the business to suppliers. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Payroll Net Pay Clearing GL Account') . ':</label>
		<select tabindex="16" name="PayrollAct">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PayrollAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select>
	</field>';

echo '<field>
		<label>' . _('Goods Received Clearing GL Account') . ':</label>
		<select title="" tabindex="17" name="GRNAct">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['GRNAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);
echo '</select>
	<fieldhelp>' . _('Select the general ledger account to be used for posting the cost of goods received pending the entry of supplier invoices for the goods. This account will represent the value of goods received yet to be invoiced by suppliers. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
	</field>';

echo '<field>
		<label>', _('Sales Commission Accruals Account'), ':</label>';
echo '<label>
		<select name="CommAct">';
while ($myrow = DB_fetch_row($result)) {
	if ($_POST['CommAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop
DB_data_seek($result,0);
echo '</select>
	</field>';

echo '<field>
		<label>' . _('Retained Earning Clearing GL Account') . ':</label>
		<select title="" tabindex="18" name="RetainedEarnings">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['RetainedEarnings']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_free_result($result);

echo '</select>
	<fieldhelp>' . _('Select the general ledger account to be used for clearing profit and loss accounts to that represents the accumulated retained profits of the business. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Freight Re-charged GL Account') . ':</label>
		<select tabindex="19" name="FreightAct">';

$result=DB_query("SELECT accountcode,
						accountname
					FROM chartmaster INNER JOIN accountgroups
					ON chartmaster.group_=accountgroups.groupname
					WHERE accountgroups.pandl=1
					ORDER BY chartmaster.accountcode");

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['FreightAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select>
	</field>';

echo '<field>
		<label>' . _('Sales Exchange Variances GL Account') . ':</label>
		<select title="" tabindex="20" name="ExchangeDiffAct">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['ExchangeDiffAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select>
	<fieldhelp>' . _('Select the general ledger account to be used for posting accounts receivable exchange rate differences to - where the exchange rate on sales invocies is different to the exchange rate of currency receipts from customers, the exchange rate is calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Purchases Exchange Variances GL Account') . ':</label>
		<select tabindex="21" title="" name="PurchasesExchangeDiffAct">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PurchasesExchangeDiffAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option  value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select>
	<fieldhelp>' . _('Select the general ledger account to be used for posting the exchange differences on the accounts payable transactions to. Supplier invoices entered at one currency and paid in the supplier currency at a different exchange rate have the differences calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Payment Discount GL Account') . ':</label>
		<select title="" tabindex="22" name="PytDiscountAct">';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PytDiscountAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select>
	<fieldhelp>' . _('Select the general ledger account to be used for posting the value of payment discounts given to customers at the time of entering a receipt. Only profit and loss general ledger accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Create GL entries for AR transactions') . ':</label>
		<select title="" tabindex="23" name="GLLink_Debtors">';

if ($_POST['GLLink_Debtors']==0){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes'). '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes'). '</option>';
	echo '<option value="0">' . _('No'). '</option>';
}

echo '</select>
	<fieldhelp>' . _('Select yes to ensure that webERP creates general ledger journals for all accounts receivable transactions. webERP will maintain the debtors control account (selected above) to ensure it should always balance to the list of customer balances in local currency.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Create GL entries for AP transactions') . ':</label>
		<select title="" tabindex="24" name="GLLink_Creditors">';

if ($_POST['GLLink_Creditors']==0){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}

echo '</select>
	<fieldhelp>' . _('Select yes to ensure that webERP creates general ledger journals for all accounts payable transactions. webERP will maintain the creditors control account (selected above) to ensure it should always balance to the list of supplier balances in local currency.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . _('Create GL entries for stock transactions')  . ':</label>
		<select title="" tabindex="25" name="GLLink_Stock">';

if ($_POST['GLLink_Stock']=='0'){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}

echo '</select>
	<fieldhelp>' . _('Select yes to ensure that webERP creates general ledger journals for all inventory transactions. webERP will maintain the stock control accounts (selected under the inventory categories set up) to ensure they balance. Only balance sheet general ledger accounts can be selected.') . '</fieldhelp>
</field>';


echo '</fieldset>
	<div class="centre">
		<input tabindex="26" type="submit" name="submit" value="' . _('Update') . '" />
	</div>';
echo '</form>';

include('includes/footer.php');
?>