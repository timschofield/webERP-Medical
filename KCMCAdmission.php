<?php

include('includes/session.inc');
$title = _('Billing Patient Registrations');
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

if (isset($_POST['SubmitCash']) or isset($_POST['SubmitInsurance'])) {

	$InputError=0;

	if ((!isset($_POST['BankAccount']) or $_POST['BankAccount']=='') and !isset($_POST['SubmitInsurance'])) {
		$InputError=1;
		$msg[]=_('You must select a cash collection point');
	}

	if (!isset($_POST['StockID']) or $_POST['StockID']=='') {
		$InputError=1;
		$msg[]=_('You must select an item to bill');
	}

	if ($InputError==1) {
		foreach($msg as $message) {
			prnMsg( $message, 'info');
		}
	} else {

		DB_Txn_Begin($db);
		/*First off create the sales order
		* entries in the database
		*/
		$OrderNo = GetNextTransNo(30, $db);

		$HeaderSQL = "INSERT INTO salesorders (	orderno,
												debtorno,
												branchcode,
												comments,
												orddate,
												shipvia,
												deliverto,
												fromstkloc,
												deliverydate,
												confirmeddate,
												deliverblind)
											VALUES (
												'" . $OrderNo . "',
												'" . $_POST['PatientNo'] . "',
												'" . $_POST['BranchNo'] . "',
												'" . DB_escape_string($_POST['Comments']) ."',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												'1',
												'" . $_SESSION['UserStockLocation'] . "',
												'" . $_SESSION['UserStockLocation'] ."',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												0
											)";

		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

		$LineItemSQL = "INSERT INTO salesorderdetails (orderlineno,
													orderno,
													stkcode,
													unitprice,
													quantity,
													discountpercent,
													narrative,
													itemdue,
													actualdispatchdate,
													qtyinvoiced,
													completed)
												VALUES (
													'0',
													'" . $OrderNo . "',
													'" . $_POST['StockID'] . "',
													'" . $_POST['Price'] . "',
													'1',
													'0',
													'" . _('Sales order for patient admission transaction') . "',
													'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
													'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
													'1',
													1
												)";
		$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
		$Ins_LineItemResult = DB_query($LineItemSQL,$db,$ErrMsg,$DbgMsg,true);

		$InvoiceNo = GetNextTransNo(10, $db);
		$PeriodNo = GetPeriod($_POST['AdmissionDate'], $db);
		if (isset($_POST['SubmitInsurance'])) {
			$_POST['Received']=0;
		} else {
			$_POST['InsuranceRef']='';
		}
		$sql = "INSERT INTO debtortrans (
				transno,
				type,
				debtorno,
				branchcode,
				trandate,
				inputdate,
				prd,
				order_,
				ovamount,
				ovgst,
				rate,
				invtext,
				reference,
				shipvia,
				alloc )
			VALUES (
				'". $InvoiceNo . "',
				10,
				'" . $_POST['PatientNo'] . "',
				'" . $_POST['BranchNo'] . "',
				'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
				'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
				'" . $PeriodNo . "',
				'" . $OrderNo . "',
				'" . $_POST['Price'] . "',
				'0',
				'1',
				'" . _('Invoice for admission of Patient number').' '.$_POST['PatientNo'] . "',
				'" . $_POST['InsuranceRef'] . "',
				'1',
				'" . $_POST['Received'] . "')";

		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		$SQL = "INSERT INTO stockmoves (
						stockid,
						type,
						transno,
						loccode,
						trandate,
						debtorno,
						branchcode,
						prd,
						reference,
						qty,
						price,
						show_on_inv_crds,
						newqoh
					) VALUES (
						'" . $_POST['StockID'] . "',
						 10,
						'" . $InvoiceNo . "',
						'" . $_SESSION['UserStockLocation'] . "',
						'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
						'" . $_POST['PatientNo'] . "',
						'" . $_POST['BranchNo'] . "',
						'" . $PeriodNo . "',
						'" . _('Invoice for admission of Patient number').' '.$_POST['PatientNo'] . "',
						-1,
						'" . $_POST['Price'] . "',
						1,
						0
					)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for'). ' '. $_POST['StockID'] . ' ' .
				_('could not be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$SQL="SELECT salestype
				FROM debtorsmaster
				WHERE debtorno='".$_POST['PatientNo']."'";
		$Result=DB_query($SQL, $db);
		$myrow=DB_fetch_array($Result);
		$SalesGLAccounts = GetSalesGLAccount('AN', $_POST['StockID'], $myrow['salestype'], $db);
		$SQL = "INSERT INTO gltrans (	type,
									typeno,
									trandate,
									periodno,
									account,
									defaulttag,
									narrative,
									amount)
							VALUES ( 10,
									'" . $InvoiceNo . "',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'" . $PeriodNo . "',
									'" . $SalesGLAccounts['salesglcode'] . "',
									'" . $_SESSION['DefaultTag'] . "',
									'" . _('Invoice for admission of Patient number').' '.$_POST['PatientNo'] . "',
									'" . -$_POST['Price'] . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$SQL = "INSERT INTO gltrans (	type,
									typeno,
									trandate,
									periodno,
									account,
									defaulttag,
									narrative,
									amount )
								VALUES ( 10,
									'" . $InvoiceNo . "',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
									'" . $_SESSION['DefaultTag'] . "',
									'" . _('Invoice for admission of Patient number').' '.$_POST['PatientNo'] . "',
									'" . $_POST['Price'] . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		if (isset($_POST['SubmitCash'])) {
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
										'" . _('Payment of invoice for admission of Patient number').' '.$_POST['PatientNo'] . "',
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
										'" . _('Payment of invoice for admission of Patient number').' '.$_POST['PatientNo'] . "',
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
									'" . _('Admission of Patient').' '.$_POST['PatientNo'] . "',
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
											'" . $InvoiceNo . "',
											'1',
											'" . -$_POST['Received'] . "',
											'" . -$_POST['Received'] . "',
											'" . _('Payment of invoice for admission of Patient number').' '.$_POST['PatientNo'] . "'
										)";

			prnMsg( _('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="'.$_SERVER['PHP_SELF'].'?New=True">'._('Enter another receipt').'</a>';
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			DB_Txn_Commit($db);
			echo '<meta http-equiv="Refresh" content="0; url='.$rootpath.'/PDFReceipt.php?FromTransNo='.$InvoiceNo.'&amp;InvOrCredit=Invoice&amp;PrintPDF=True">';
			include('includes/footer.inc');
			$_SESSION['DefaultCashPoint']=$_POST['BankAccount'];
			exit;
		} elseif (isset($_POST['SubmitInsurance'])) {
			prnMsg( _('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="'.$_SERVER['PHP_SELF'].'?New=True">'._('Enter another receipt').'</a>';
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
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/PatientFile.png" title="' . _('Search') . '" alt="" />' . $title . '</p>';

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Patient" value="'.$_POST['Patient'].'" />';
	echo '<input type="hidden" name="PatientNo" value="'.$Patient[0].'" />';
	echo '<input type="hidden" name="BranchNo" value="'.$Patient[1].'" />';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="3" class="header">'.$mydebtorrow['name'].' - '.$mydebtorrow['phoneno'].'</th>
			<th style="text-align: right"><a href="KCMCEditPatientDetails.php?PatientNumber='.$Patient[0].'&BranchCode='.$Patient[1].'" target="_blank">
					<img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/user.png" alt="Patient Details" /></a>
			</th>
		</tr>';
	echo '<tr><td>'._('Date of Registration').':</td>
		<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="AdmissionDate" maxlength="10" size="11" value="' .
					 date($_SESSION['DefaultDateFormat']) . '" /></td></tr>';
	echo '<tr><td>'._('Type of payment').':</td>';
	$sql="SELECT stockid,
				description
			FROM stockmaster
			LEFT JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='R'";

	$result=DB_query($sql, $db);
	if (isset($_POST['StockID'])) {
		$StockID=$_POST['StockID'];
	} else {
		$StockID='';
	}
	echo '<td><select name="StockID" onChange="ReloadForm(ChangeItem)">';
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($result)) {
		if ($myrow['stockid']==$StockID) {
			echo '<option selected value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
		} else {
			echo '<option value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<input type="submit" name="ChangeItem" style="visibility: hidden" value=" " />';
	$sql="SELECT price
				FROM prices
				WHERE stockid='".$StockID."'
				AND typeabbrev='".$mydebtorrow['salestype']."'
				AND '".FormatDateForSQL($_POST['AdmissionDate'])."' between startdate and enddate";
	$result=DB_query($sql,$db);
	if (DB_num_rows($result)==0) {
		$Price=0;
	} else {
		$myrow=DB_fetch_array($result);
		$Price=$myrow['price'];
	}
	echo '<tr><td>'._('Payment Fee').'</td>';
	echo '<td>'.number_format($Price, 0).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td></tr>';
	echo '<input type="hidden" name="Price" value="'.$Price.'" />';

	if ($Patient[1]=='CASH') {
		if (!isset($Received)) {
			$Received=$Price;
		}
		echo '<tr><td>'._('Amount Received').'</td>';
		echo '<td><input type="text" class="number" size="10" name="Received" value="'.number_format($Received,0,'.','').'" /></td></tr>';

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
		echo '<tr><td colspan="2" style="text-align: left"><button type="submit" style="text-align:left" name="SubmitCash"><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/tick.png" />'._('Make Payment').'</button>';
	} else {
		echo '<tr><td>'._('Insurance Reference').'</td>';
		echo '<td><input type="text" size="10" name="InsuranceRef" value="" /></td></tr>';
		echo '<tr><td>'._('Comments').'</td>';
		echo '<td><input type="text" size="50" name="Comments" value="" /></td></tr>';
		echo '<tr><td colspan="2" style="text-align: left"><button type="submit" style="text-align:left" name="SubmitInsurance"><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/tick.png" />'._('Process Invoice').'</button>';
	}
	echo '<button type="submit" name="Cancel" value=""><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/cross.png" />'._('Cancel Transaction').'</button></td></tr>';
	echo '</table>';
	echo '</form>';
}

include('includes/footer.inc');
?>