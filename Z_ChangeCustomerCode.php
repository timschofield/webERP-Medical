<?php
/* This script is an utility to change a customer code. */

include ('includes/session.php');
$Title = _('UTILITY PAGE To Changes A Customer Code In All Tables');// Screen identificator.
$ViewTopic = 'SpecialUtilities'; // Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangeCustomerCode'; // Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/customer.png" title="' .
	_('Change A Customer Code') . '" /> ' .// Icon title.
	_('Change A Customer Code') . '</p>';// Page title.

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$Result=DB_query("SELECT debtorno FROM debtorsmaster WHERE debtorno='" . $_POST['OldDebtorNo'] . "'");
	if (DB_num_rows($Result)==0){
		prnMsg ('<br /><br />' . _('The customer code') . ': ' . $_POST['OldDebtorNo'] . ' ' . _('does not currently exist as a customer code in the system'),'error');
		include('includes/footer.php');
		exit;
	}


	if ($_POST['NewDebtorNo']==''){
		prnMsg(_('The new customer code to change the old code to must be entered as well'),'error');
		include('includes/footer.php');
		exit;
	}
/*Now check that the new code doesn't already exist */
	$Result=DB_query("SELECT debtorno FROM debtorsmaster WHERE debtorno='" . $_POST['NewDebtorNo'] . "'");
	if (DB_num_rows($Result)!=0){
		prnMsg(_('The replacement customer code') .': ' . $_POST['NewDebtorNo'] . ' ' . _('already exists as a customer code in the system') . ' - ' . _('a unique customer code must be entered for the new code'),'error');
		include('includes/footer.php');
		exit;
	}

	DB_Txn_Begin();

	prnMsg(_('Inserting the new debtors master record'),'info');
	$SQL = "INSERT INTO debtorsmaster (`debtorno`,
									`name`,
									`address1`,
									`address2`,
									`address3`,
									`address4`,
									`currcode`,
									`salestype`,
									`clientsince`,
									`holdreason`,
									`paymentterms`,
									`discount`,
									`discountcode`,
									`pymtdiscount`,
									`lastpaid`,
									`lastpaiddate`,
									`creditlimit`,
									`invaddrbranch`,
									`ediinvoices`,
									`ediorders`,
									`edireference`,
									`editransport`,
									`ediaddress`,
									`ediserveruser`,
									`ediserverpwd`,
									`typeid`)
					SELECT '" . $_POST['NewDebtorNo'] . "',
									`name`,
									`address1`,
									`address2`,
									`address3`,
									`address4`,
									`currcode`,
									`salestype`,
									`clientsince`,
									`holdreason`,
									`paymentterms`,
									`discount`,
									`discountcode`,
									`pymtdiscount`,
									`lastpaid`,
									`lastpaiddate`,
									`creditlimit`,
									`invaddrbranch`,
									`ediinvoices`,
									`ediorders`,
									`edireference`,
									`editransport`,
									`ediaddress`,
									`ediserveruser`,
									`ediserverpwd`,
									`typeid`
					FROM debtorsmaster
					WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$DbgMsg =_('The SQL that failed was');
	$ErrMsg = _('The SQL to insert the new debtors master record failed') . ', ' . _('the SQL statement was');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Inserting new customer branch records'),'info');
	$SQL = "INSERT INTO custbranch ( `branchcode`,
									`debtorno`,
									`brname`,
									`braddress1`,
									`braddress2`,
									`braddress3`,
									`braddress4`,
									`braddress5`,
									`braddress6`,
									`estdeliverydays`,
									`area`,
									`salesman`,
									`fwddate`,
									`phoneno`,
									`faxno`,
									`contactname`,
									`email`,
									`defaultlocation`,
									`taxgroupid`,
									`disabletrans`,
									`brpostaddr1`,
									`brpostaddr2`,
									`brpostaddr3`,
									`brpostaddr4`,
									`brpostaddr5`,
									`brpostaddr6`,
									`defaultshipvia`,
									`specialinstructions`,
									`custbranchcode`)
							SELECT branchcode,
								'" . $_POST['NewDebtorNo'] . "',
									`brname`,
									`braddress1`,
									`braddress2`,
									`braddress3`,
									`braddress4`,
									`braddress5`,
									`braddress6`,
									`estdeliverydays`,
									`area`,
									`salesman`,
									`fwddate`,
									`phoneno`,
									`faxno`,
									`contactname`,
									`email`,
									`defaultlocation`,
									`taxgroupid`,
									`disabletrans`,
									`brpostaddr1`,
									`brpostaddr2`,
									`brpostaddr3`,
									`brpostaddr4`,
									`brpostaddr5`,
									`brpostaddr6`,
									`defaultshipvia`,
									'',
									`custbranchcode`
								FROM custbranch
								WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to insert new customer branch records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Changing debtor transaction records'),'info');

	$SQL = "UPDATE debtortrans SET debtorNo='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update debtor transaction records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing sales analysis records'),'info');

	$SQL = "UPDATE salesanalysis SET cust='" . $_POST['NewDebtorNo'] . "' WHERE cust='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	prnMsg (_('Changing order delivery differences records'),'info');
	$SQL = "UPDATE orderdeliverydifferenceslog SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update order delivery differences records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Changing pricing records'),'info');
	$SQL = "UPDATE prices SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update the pricing records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing sales orders records'),'info');
	$SQL = "UPDATE salesorders SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update the sales order header records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	prnMsg( _('Changing stock movement records'),'info');
	$SQL = "UPDATE stockmoves SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update the sales order header records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing user default customer records'),'info');
	$SQL = "UPDATE www_users SET customerid='" . $_POST['NewDebtorNo'] . "' WHERE customerid='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update the user records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing the customer code in contract header records'),'info');
	$SQL = "UPDATE contracts SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update contract header records failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	$Result = DB_IgnoreForeignKeys();

	prnMsg(_('Deleting the old customer branch records from the CustBranch table'),'info');
	$SQL = "DELETE FROM custbranch WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to delete the old CustBranch records for the old debtor record failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Deleting the customer code from the DebtorsMaster table'),'info');
	$SQL = "DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to delete the old debtor record failed');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);


	DB_Txn_Commit();
	$Result = DB_ReinstateForeignKeys();

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', _('Customer to Change'), '</legend>
	<field>
		<label>' . _('Existing Debtor Code') . ':</label>
		<input type="text" name="OldDebtorNo" size="20" maxlength="20" />
	</field>
	<field>
		<label> ' . _('New Debtor Code') . ':</label>
		<input type="text" name="NewDebtorNo" size="20" maxlength="20" /><
	</field>
	</fieldset>

	<div class="centre">
		<input type="submit" name="ProcessCustomerChange" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>
