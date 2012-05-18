<?php
include('includes/session.inc');
$title = _('In Patient Deposit Payments');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');
include('includes/CustomerSearch.php');

if (isset($_GET['New']) or isset($_POST['Cancel'])) {
	unset($_POST['SubmitCash']);
	unset($_POST['Patient']);
}

if (!isset($_POST['Search']) and !isset($_POST['Next']) and !isset($_POST['Previous']) and !isset($_POST['Go1']) and !isset($_POST['Go2']) and isset($_POST['JustSelectedACustomer']) and empty($_POST['Patient'])){
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i=0; $i< count($_POST); $i++){ //loop through the returned customers
		if(isset($_POST['SubmitCustomerSelection'.$i])){
			break;
		}
	}
	if ($i==count($_POST)){
		prnMsg(_('Unable to identify the selected customer'),'error');
	} else {
		$Patient[0] = $_POST['SelectedCustomer'.$i];
		$Patient[1] = $_POST['SelectedBranch'.$i];
		unset($_POST['Search']);
	}
}

if (isset($_POST['SubmitCash'])) {

	$InputError=0;

	if ((!isset($_POST['BankAccount']) or $_POST['BankAccount']=='') and !isset($_POST['SubmitInsurance'])) {
		$InputError=1;
		$msg[]=_('You must select a cash collection point');
	}

	if (!isset($_POST['Received']) or $_POST['Received']==0) {
		$InputError=1;
		$msg[]=_('You must enter an amount for the deposit');
	}

	if ($InputError==1) {
		$Patient[0] = $_POST['PatientNo'];
		$Patient[1] = $_POST['BranchNo'];
		foreach($msg as $message) {
			prnMsg( $message, 'info');
		}
	} else {

		DB_Txn_Begin($db);
		/*First off create the sales order
		* entries in the database
		*/

		if (isset($_POST['SubmitCash'])) {
			$PeriodNo = GetPeriod($_POST['AdmissionDate'], $db);
			$ReceiptNumber = GetNextTransNo(12,$db);
			$SQL="INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										defaulttag,
										narrative,
										amount)
									VALUES (12,
										'" . $ReceiptNumber . "',
										'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
										'" . $PeriodNo . "',
										'" . $_POST['BankAccount'] . "',
										'" . $_SESSION['DefaultTag'] . "',
										'" . _('Deposit for Patient number').' '.$_POST['PatientNo'] . "',
										'" . ($_POST['Received']) . "')";
			$DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
			$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			/* Now Credit Debtors account with receipt */
			$SQL="INSERT INTO gltrans ( type,
										typeno,
										trandate,
										periodno,
										account,
										defaulttag,
										narrative,
										amount)
									VALUES (12,
										'" . $ReceiptNumber . "',
										'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
										'" . $_SESSION['DefaultTag'] . "',
										'" . _('Deposit for Patient number').' '.$_POST['PatientNo'] . "',
										'" . -($_POST['Received']) . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
			$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$SQL="INSERT INTO banktrans (type,
									transno,
									bankact,
									ref,
									exrate,
									functionalexrate,
									transdate,
									banktranstype,
									amount,
									currcode,
									userid)
								VALUES (12,
									'" . $ReceiptNumber . "',
									'" . $_POST['BankAccount'] . "',
									'" . _('Deposit for Patient').' '.$_POST['PatientNo'] . "',
									'1',
									'1',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'2',
									'" . ($_POST['Received']) . "',
									'" . $_SESSION['CompanyRecord']['currencydefault'] . "',
									'" . $_SESSION['UserID'] . "'
								)";

			$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
			$ErrMsg = _('Cannot insert a bank transaction');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$SQL="INSERT INTO debtortrans (transno,
											type,
											debtorno,
											trandate,
											inputdate,
											prd,
											reference,
											rate,
											ovamount,
											alloc,
											invtext)
										VALUES ('" . $ReceiptNumber . "',
											12,
											'" . $_POST['PatientNo'] . "',
											'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
											'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
											'" . $PeriodNo . "',
											'" . $ReceiptNumber . "',
											'1',
											'" . -$_POST['Received'] . "',
											'0',
											'" . _('Deposit for Patient number').' '.$_POST['PatientNo'] . "'
										)";

			prnMsg( _('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="'.$_SERVER['PHP_SELF'].'?New=True">'._('Enter another receipt').'</a>';
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			DB_Txn_Commit($db);
			echo '<meta http-equiv="Refresh" content="0; url='.$rootpath.'/PDFReceipt.php?FromTransNo='.$ReceiptNumber.'&amp;InvOrCredit=Receipt&amp;PrintPDF=True&amp;Amount='.$_POST['Received'].'">';
			include('includes/footer.inc');
			$_SESSION['DefaultCashPoint']=$_POST['BankAccount'];
			exit;
		} elseif (isset($_POST['SubmitInsurance'])) {
			prnMsg( _('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="'.$_SERVER['PHP_SELF'].'?New=True">'._('Enter another deposit').'</a>';
			DB_Txn_Commit($db);
			include('includes/footer.inc');
			exit;
		}
	}
}

if (!isset($Patient)) {
	ShowCustomerSearchFields($rootpath, $theme, $db);
}

if (isset($_POST['Search']) OR isset($_POST['Go1']) OR isset($_POST['Go2']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {

	$PatientResult = CustomerSearchSQL($db);
	if (DB_num_rows($PatientResult) == 0) {
		prnMsg(_('No patient records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search

if (isset($PatientResult)) {
	ShowReturnedCustomers($PatientResult);
}

if (isset($Patient)) {
	$sql="SELECT name,
				clientsince,
				salestype,
				phoneno
				FROM debtorsmaster
				LEFT JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				WHERE debtorsmaster.debtorno='".$Patient[0]."'";
	$result=DB_query($sql, $db);
	$mydebtorrow=DB_fetch_array($result);
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/Hospital.png" title="' . _('Search') . '" alt="" />' . $title . '</p>';

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="PatientNo" value="'.$Patient[0].'" />';
	echo '<input type="hidden" name="BranchNo" value="'.$Patient[1].'" />';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="5" class="header">'.$mydebtorrow['name'].' - '.$mydebtorrow['phoneno'].'</th>
			<th style="text-align: right"><a href="KCMCEditPatientDetails.php?PatientNumber='.$Patient[0].'&BranchCode='.$Patient[1].'" target="_blank">
					<img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/user.png" alt="Patient Details" /></a>
			</th>
		</tr>';
	echo '<tr><td>'._('Date of Admission').':</td>
		<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="AdmissionDate" maxlength="10" size="11" value="' .
					 date($_SESSION['DefaultDateFormat']) . '" /></td></tr>';

	if ($Patient[1]=='CASH') {
		echo '<tr><td>'._('Amount Received').'</td>';
		echo '<td><input type="text" class="number" size="10" name="Received" value="0.00" /></td></tr>';

		$sql = "SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
			FROM bankaccounts,
				chartmaster
			WHERE bankaccounts.accountcode=chartmaster.accountcode
				AND pettycash=1";

		$ErrMsg = _('The bank accounts could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the bank accounts was');
		$AccountsResults = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		echo '<tr><td>' . _('Received into') . ':</td><td><select name="BankAccount">';

		if (DB_num_rows($AccountsResults)==0){
			echo '</select></td></tr></table><p>';
			prnMsg( _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'),'warn');
			include('includes/footer.inc');
			exit;
		} else {
			echo '<option value=""></option>';
			while ($myrow=DB_fetch_array($AccountsResults)){
			/*list the bank account names */
				if (isset($_SESSION['DefaultCashPoint']) and $_SESSION['DefaultCashPoint']==$myrow['accountcode']){
					echo '<option selected value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
				} else {
					echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
				}
			}
			echo '</select></td></tr>';
		}
		echo '<tr><td>'._('Comments').'</td>';
		echo '<td><input type="text" size="50" name="Comments" value="" /></td></tr>';
		echo '<tr><td colspan="2" style="text-align: left"><button type="submit" style="text-align:left" name="SubmitCash"><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/tick.png" />'._('Process Payment').'</button>';
	}
	echo '<button type="submit" name="Cancel" value=""><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/cross.png" />'._('Cancel Transaction').'</button></td></tr>';
	echo '</table>';
	echo '</form>';
}

include('includes/footer.inc');
?>