<?php
include ('includes/session.php');
$Title = _('In Patient Deposit Payments');
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/GetSalesTransGLCodes.php');
include ('includes/CustomerSearch.php');

if (isset($_GET['New']) or isset($_POST['Cancel'])) {
	unset($_POST['SubmitCash']);
	unset($_POST['Patient']);
}

if (!isset($_POST['Search']) and !isset($_POST['Next']) and !isset($_POST['Previous']) and !isset($_POST['Go1']) and !isset($_POST['Go2']) and isset($_POST['JustSelectedACustomer']) and empty($_POST['Patient'])) {
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i = 0;$i < count($_POST);$i++) { //loop through the returned customers
		if (isset($_POST['SubmitCustomerSelection' . $i])) {
			break;
		}
	}
	if ($i == count($_POST)) {
		prnMsg(_('Unable to identify the selected customer'), 'error');
	} else {
		$Patient[0] = $_POST['SelectedCustomer' . $i];
		$Patient[1] = $_POST['SelectedBranch' . $i];
		unset($_POST['Search']);
	}
}

if (isset($_POST['SubmitCash'])) {

	$InputError = 0;

	if ((!isset($_POST['BankAccount']) or $_POST['BankAccount'] == '') and !isset($_POST['SubmitInsurance'])) {
		$InputError = 1;
		$msg[] = _('You must select a cash collection point');
	}

	if (!isset($_POST['Received']) or $_POST['Received'] == 0) {
		$InputError = 1;
		$msg[] = _('You must enter an amount for the deposit');
	}

	if ($InputError == 1) {
		$Patient[0] = $_POST['PatientNo'];
		$Patient[1] = $_POST['BranchNo'];
		foreach ($msg as $message) {
			prnMsg($message, 'info');
		}
	} else {

		DB_Txn_Begin();
		/*First off create the sales order
		 * entries in the database
		*/

		if (isset($_POST['SubmitCash'])) {
			$PeriodNo = GetPeriod($_POST['AdmissionDate']);
			$ReceiptNumber = GetNextTransNo(12);
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										tag,
										narrative,
										amount)
									VALUES (12,
										'" . $ReceiptNumber . "',
										'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
										'" . $PeriodNo . "',
										'" . $_POST['BankAccount'] . "',
										'" . $_SESSION['DefaultTag'] . "',
										'" . _('Deposit for Patient number') . ' ' . $_POST['PatientNo'] . "',
										'" . ($_POST['Received']) . "')";
			$DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
			$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			/* Now Credit Debtors account with receipt */
			$SQL = "INSERT INTO gltrans ( type,
										typeno,
										trandate,
										periodno,
										account,
										tag,
										narrative,
										amount)
									VALUES (12,
										'" . $ReceiptNumber . "',
										'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
										'" . $_SESSION['DefaultTag'] . "',
										'" . _('Deposit for Patient number') . ' ' . $_POST['PatientNo'] . "',
										'" . -($_POST['Received']) . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
			$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO banktrans (type,
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
									'" . _('Deposit for Patient') . ' ' . $_POST['PatientNo'] . "',
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
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO debtortrans (transno,
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
											'" . _('Deposit for Patient number') . ' ' . $_POST['PatientNo'] . "'
										)";

			prnMsg(_('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?New=True">' . _('Enter another deposit') . '</a>';
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			DB_Txn_Commit();
			echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PDFPatientReceipt.php?FromTransNo=' . $ReceiptNumber . '&amp;InvOrCredit=Receipt&amp;PrintPDF=True&amp;Amount=' . $_POST['Received'] . '">';
			include ('includes/footer.php');
			$_SESSION['DefaultCashPoint'] = $_POST['BankAccount'];
			exit;
		} elseif (isset($_POST['SubmitInsurance'])) {
			prnMsg(_('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?New=True">' . _('Enter another deposit') . '</a>';
			DB_Txn_Commit();
			include ('includes/footer.php');
			exit;
		}
	}
}

if (!isset($Patient)) {
	ShowCustomerSearchFields($RootPath, $_SESSION['Theme']);
}

if (isset($_POST['Search']) or isset($_POST['Go1']) or isset($_POST['Go2']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

	$PatientResult = CustomerSearchSQL();
	if (DB_num_rows($PatientResult) == 0) {
		prnMsg(_('No patient records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search
if (isset($PatientResult)) {
	ShowReturnedCustomers($PatientResult);
}

if (isset($Patient)) {
	$SQL = "SELECT name,
				clientsince,
				salestype,
				phoneno
				FROM debtorsmaster
				LEFT JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				WHERE debtorsmaster.debtorno='" . $Patient[0] . "'";
	$Result = DB_query($SQL);
	$mydebtorrow = DB_fetch_array($Result);
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/Hospital.png" title="' . _('Search') . '" alt="" />' . $Title . '</p>';

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="PatientNo" value="' . $Patient[0] . '" />';
	echo '<input type="hidden" name="BranchNo" value="' . $Patient[1] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="5" class="header">' . $mydebtorrow['name'] . ' - ' . $mydebtorrow['phoneno'] . '</th>
			<th style="text-align: right"><a href="KCMCEditPatientDetails.php?PatientNumber=' . $Patient[0] . '&BranchCode=' . $Patient[1] . '" target="_blank">
					<img width="15px" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/user.png" alt="Patient Details" /></a>
			</th>
		</tr>';
	echo '<tr>
			<td>' . _('Date of Admission') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="AdmissionDate" maxlength="10" size="11" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>';

	if ($Patient[1] == 'CASH') {
		echo '<tr>
				<td>' . _('Amount Received') . '</td>
				<td><input type="text" class="number" size="10" name="Received" value="0.00" /></td>
			</tr>';

		$SQL = "SELECT bankaccountname,
						bankaccounts.accountcode,
						bankaccounts.currcode
					FROM bankaccounts
					INNER JOIN chartmaster
						ON bankaccounts.accountcode=chartmaster.accountcode
					INNER JOIN bankaccountusers
						ON bankaccounts.accountcode=bankaccountusers.accountcode
					WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] . "'
						AND pettycash=1
					ORDER BY bankaccountname";

		$ErrMsg = _('The bank accounts could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the bank accounts was');
		$AccountsResults = DB_query($SQL, $ErrMsg, $DbgMsg);

		echo '<tr>
				<td>' . _('Received into') . ':</td>
				<td><select name="BankAccount">';

		if (DB_num_rows($AccountsResults) == 0) {
			echo '</select>
						</td>
					</tr>
				</table>';
			prnMsg(_('A Cash Account has not yet been defined for this user. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'), 'warn');
			include ('includes/footer.php');
			exit;
		} else {
			echo '<option value=""></option>';
			while ($MyRow = DB_fetch_array($AccountsResults)) {
				/*list the bank account names */
				if (isset($_SESSION['DefaultCashPoint']) and $_SESSION['DefaultCashPoint'] == $MyRow['accountcode']) {
					echo '<option selected value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
				} else {
					echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
				}
			}
			echo '</select>
						</td>
					</tr>';
		}
		echo '<tr>
				<td>' . _('Comments') . '</td>
				<td><input type="text" size="50" name="Comments" value="" /></td>
			</tr>';
		echo '</table>';
		echo '<div class="centre">
				<input type="submit" name="SubmitCash" value="' . _('Process Payment') . '" />
			</div>';
	}
	echo '<input type="submit" name="Cancel" value="' . _('Cancel Transaction') . '" />';
	echo '</form>';
}

include ('includes/footer.php');
?>