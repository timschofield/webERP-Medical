<?php
include ('includes/session.php');
$Title = _('Billing For Other Medical Services');
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/GetSalesTransGLCodes.php');
include ('includes/CustomerSearch.php');

if (!isset($_POST['BankAccount']) or isset($_POST['Cancel'])) {
	unset($_POST['SubmitCash']);
	unset($_POST['Patient']);
	unset($_SESSION['Items']);
	$_SESSION['Items']['Lines'] = 0;
	$_SESSION['Items']['Value'] = 0;
}

if (isset($_POST['BankAccount'])) {
	$SQL = "SELECT currencies.decimalplaces
				FROM bankaccounts
				INNER JOIN currencies
					ON currencies.currabrev=bankaccounts.currcode
				WHERE bankaccounts.accountcode='" . $_POST['BankAccount'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$DecimalPlaces = $MyRow['decimalplaces'];
} else {
	$SQL = "SELECT decimalplaces
				FROM currencies
				WHERE currabrev='" . $_SESSION['CompanyRecord']['currencydefault'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$DecimalPlaces = $MyRow['decimalplaces'];
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

if (isset($_GET['Delete'])) {
	$_SESSION['Items']['Value']-= $_SESSION['Items'][$_GET['Delete']]['Quantity'] * $_SESSION['Items'][$_GET['Delete']]['Price'];
	unset($_SESSION['Items'][$_GET['Delete']]);
	$_POST['Patient'] = $_GET['Patient'] . ' ' . $_GET['Branch'];
	$Patient[0] = $_GET['Patient'];
	$Patient[1] = $_GET['Branch'];
}

if (isset($_POST['ChangeItem'])) {
	$Patient[0] = $_POST['PatientNo'];
	$Patient[1] = $_POST['BranchNo'];
}

if (isset($_POST['ChangeItem']) and $_POST['StockID'] != '') {
	$Patient[0] = $_POST['PatientNo'];
	$Patient[1] = $_POST['BranchNo'];
	$SQL = "SELECT price
				FROM prices
				WHERE stockid='" . $_POST['StockID'] . "'
				AND typeabbrev='" . $_POST['PriceList'] . "'
				AND '" . FormatDateForSQL($_POST['AdmissionDate']) . "' between startdate and enddate";
	$PriceResult = DB_query($SQL);
	if (DB_num_rows($PriceResult) == 0) {
		$Price = 0;
	} else {
		$MyRow = DB_fetch_array($PriceResult);
		$Price = $MyRow['price'];
	}
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['StockID'] = $_POST['StockID'];
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['Quantity'] = $_POST['Quantity'];
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['Price'] = $Price;
	$_SESSION['Items']['Value']+= $Price * $_POST['Quantity'];
	$_SESSION['Items']['Lines']++;
} else if (isset($_POST['ChangeItem']) and $_POST['StockID'] == '' and isset($_POST['AddDoctorFee'])) {
	$_SESSION['Items']['Value']+= filter_number_format($_POST['DoctorsFee']);
} else if (isset($_POST['ChangeItem']) and $_POST['StockID'] == '' and !isset($_POST['AddDoctorFee'])) {
	$_SESSION['Items']['Value']-= filter_number_format($_POST['DoctorsFee']);
}

if (isset($_POST['SubmitCash']) or isset($_POST['SubmitInsurance'])) {

	$InputError = 0;

	if ((!isset($_POST['BankAccount']) or $_POST['BankAccount'] == '') and !isset($_POST['SubmitInsurance'])) {
		$InputError = 1;
		$msg[] = _('You must select a cash collection point');
	}

	if ($_SESSION['Items']['Lines'] == 0) {
		$InputError = 1;
		$msg[] = _('You must select a service to bill');
	}

	if ($InputError == 1) {
		foreach ($msg as $message) {
			prnMsg($message, 'info');
			$_POST['ChangeItem'] = 'Yes';
			$Patient[0] = $_POST['PatientNo'];
			$Patient[1] = $_POST['BranchNo'];
		}
	} else {

		DB_Txn_Begin();
		/*First off create the sales order
		 * entries in the database
		*/
		$OrderNo = GetNextTransNo(30);

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
											'" . DB_escape_string($_POST['Comments']) . "',
											'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
											'1',
											'" . $_SESSION['UserStockLocation'] . "',
											'" . $_SESSION['UserStockLocation'] . "',
											'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
											'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
											0
										)";

		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL, $ErrMsg);

		for ($i = 0;$i < $_SESSION['Items']['Lines'];$i++) {
			if (isset($_SESSION['Items'][$i]['StockID'])) {
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
													'" . $i . "',
													'" . $OrderNo . "',
													'" . $_SESSION['Items'][$i]['StockID'] . "',
													'" . $_SESSION['Items'][$i]['Price'] . "',
													'" . $_SESSION['Items'][$i]['Quantity'] . "',
													'0',
													'" . _('Sales order for other medical services transaction') . "',
													'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
													'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
													'1',
													1
												)";
				$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
				$Ins_LineItemResult = DB_query($LineItemSQL, $ErrMsg, $DbgMsg, true);
				$SQL = "UPDATE care_encounter_prescription SET bill_number='" . $OrderNo . "'
							WHERE nr='" . $_SESSION['Items'][$i]['Care2x'] . "'";
				$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
				$UpdateCare2xResult = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
		}

		if (isset($_POST['Doctor'])) {
			$SuppInvoiceNumber = GetNextTransNo(20);
			$DoctorsInvoiceSQL = "INSERT INTO supptrans (transno,
														type,
														supplierno,
														suppreference,
														trandate,
														duedate,
														inputdate,
														settled,
														rate,
														ovamount,
														ovgst,
														diffonexch,
														alloc,
														transtext,
														hold,
														id)
													VALUES (
														'" . $SuppInvoiceNumber . "',
														'20',
														'" . $_POST['Doctor'] . "',
														'',
														'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
														'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
														'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
														'0',
														'1',
														'" . $_POST['DoctorsFee'] . "',
														'0',
														'0',
														'0',
														'" . $_POST['Doctor'] . ' ' . _('fee') . ' ' . _('for patient') . ' ' . $_POST['PatientNo'] . "',
														'0',
														''
													)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The doctors transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the doctors transaction record was used');
			$Result = DB_query($DoctorsInvoiceSQL, $ErrMsg, $DbgMsg, true);
		}

		$InvoiceNo = GetNextTransNo(10);
		$PeriodNo = GetPeriod($_POST['AdmissionDate']);
		if (isset($_POST['SubmitInsurance'])) {
			$_POST['Received'] = 0;
		} else {
			$_POST['InsuranceRef'] = '';
		}
		$SQL = "INSERT INTO debtortrans (
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
				'" . $InvoiceNo . "',
				10,
				'" . $_POST['PatientNo'] . "',
				'" . $_POST['BranchNo'] . "',
				'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
				'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
				'" . $PeriodNo . "',
				'" . $OrderNo . "',
				'" . $_SESSION['Items']['Value'] . "',
				'0',
				'1',
				'" . _('Invoice of Other Medical Services for Patient number') . ' ' . $_POST['PatientNo'] . "',
				'" . $_POST['InsuranceRef'] . "',
				'1',
				'" . $_POST['Received'] . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		for ($i = 0;$i < $_SESSION['Items']['Lines'];$i++) {
			if (isset($_SESSION['Items'][$i]['StockID'])) {
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
						'" . $_SESSION['Items'][$i]['StockID'] . "',
						 10,
						'" . $InvoiceNo . "',
						'" . $_SESSION['UserStockLocation'] . "',
						'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
						'" . $_POST['PatientNo'] . "',
						'" . $_POST['BranchNo'] . "',
						'" . $PeriodNo . "',
						'" . _('Invoice of Other Medical Service for Patient number') . ' ' . $_POST['PatientNo'] . "',
						'" . -$_SESSION['Items'][$i]['Quantity'] . "',
						'" . $_SESSION['Items'][$i]['Price'] . "',
						1,
						0
					)";
				$BaseStockID = $_SESSION['Items'][$i]['StockID'];

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for') . ' ' . $_POST['StockID'] . ' ' . _('could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
		}
		$SQL = "SELECT salestype
				FROM debtorsmaster
				WHERE debtorno='" . $_POST['PatientNo'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$SalesGLAccounts = GetSalesGLAccount('AN', $BaseStockID, $MyRow['salestype']);
		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									tag,
									narrative,
									amount)
							VALUES ( 10,
									'" . $InvoiceNo . "',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'" . $PeriodNo . "',
									'" . $SalesGLAccounts['salesglcode'] . "',
									'" . $_SESSION['DefaultTag'] . "',
									'" . _('Invoice of Other Medical Service for Patient number') . ' ' . $_POST['PatientNo'] . "',
									'" . -$_SESSION['Items']['Value'] . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									tag,
									narrative,
									amount )
								VALUES (10,
									'" . $InvoiceNo . "',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
									'" . $_SESSION['DefaultTag'] . "',
									'" . _('Invoice of Other Medical Service for Patient number') . ' ' . $_POST['PatientNo'] . "',
									'" . $_SESSION['Items']['Value'] . "'
								)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		if (isset($_POST['SubmitCash'])) {
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
										'" . _('Payment of Other Medical Service for Patient number') . ' ' . $_POST['PatientNo'] . "',
										'" . ($_SESSION['Items']['Value']) . "'
									)";
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
										'" . _('Payment of Other Medical Service for Patient number') . ' ' . $_POST['PatientNo'] . "',
										'" . -($_SESSION['Items']['Value']) . "'
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
									'" . _('Other Medical Service for Patient') . ' ' . $_POST['PatientNo'] . "',
									'1',
									'1',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'2',
									'" . ($_SESSION['Items']['Value']) . "',
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
											'" . $InvoiceNo . "',
											'1',
											'" . -$_SESSION['Items']['Value'] . "',
											'" . -$_POST['Received'] . "',
											'" . _('Payment of Other Medical Service for Patient number') . ' ' . $_POST['PatientNo'] . "'
										)";

			prnMsg(_('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?New=True">' . _('Enter another receipt') . '</a>';
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			DB_Txn_Commit();
			echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PDFPatientReceipt.php?FromTransNo=' . $InvoiceNo . '&amp;InvOrCredit=Invoice&amp;PrintPDF=True">';
			include ('includes/footer.php');
			$_SESSION['DefaultCashPoint'] = $_POST['BankAccount'];
			exit;
		} elseif (isset($_POST['SubmitInsurance'])) {
			prnMsg(_('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?New=True">' . _('Enter another receipt') . '</a>';
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
	$Care2xSQL = "SELECT care_encounter_prescription.article_item_number,
						care_encounter_prescription.nr,
						partcode,
						total_dosage,
						prescribe_date
					FROM care_encounter_prescription
					LEFT JOIN care_tz_drugsandservices
						ON care_encounter_prescription.article_item_number=care_tz_drugsandservices.item_id
					LEFT JOIN stockmaster
						ON care_tz_drugsandservices.partcode=stockmaster.stockid
					LEFT JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					LEFT JOIN care_encounter
						ON care_encounter.encounter_nr=care_encounter_prescription.encounter_nr
					WHERE care_encounter.pid='" . $Patient[0] . "'
						AND stockcategory.stocktype='S'
						AND care_encounter_prescription.bill_number=''";
	$Care2xResult = DB_query($Care2xSQL);
	$i = 0;
	while ($MyCare2xRow = DB_fetch_array($Care2xResult)) {
		$PriceSQL = "SELECT price
					FROM prices
					WHERE stockid='" . $MyCare2xRow['partcode'] . "'
					AND typeabbrev='" . $mydebtorrow['salestype'] . "'
					AND '" . $MyCare2xRow['prescribe_date'] . "' between startdate and enddate";
		$PriceResult = DB_query($PriceSQL);
		if (DB_num_rows($PriceResult) == 0) {
			$Price = 0;
		} else {
			$MyRow = DB_fetch_array($PriceResult);
			$Price = $MyRow['price'];
		}
		$_SESSION['Items'][$i]['StockID'] = $MyCare2xRow['partcode'];
		$_SESSION['Items'][$i]['Quantity'] = $MyCare2xRow['total_dosage'];
		$_SESSION['Items'][$i]['Price'] = $Price;
		$_SESSION['Items'][$i]['Care2x'] = $MyCare2xRow['nr'];
		$_SESSION['Items']['Value']+= $Price * $MyCare2xRow['total_dosage'];
		$_SESSION['Items']['Lines']++;
		$i++;
	}
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/Stethoscope.png" title="' . _('Search') . '" alt="" />' . $Title . '</p>';

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="PriceList" value="' . $mydebtorrow['salestype'] . '" />';
	echo '<input type="hidden" name="PatientNo" value="' . $Patient[0] . '" />';
	echo '<input type="hidden" name="BranchNo" value="' . $Patient[1] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="3"><font size="3" color="navy">' . $mydebtorrow['name'] . '</font><font size="2" color="navy"> - ' . $mydebtorrow['phoneno'] . '</font></th>
			<th style="text-align: right"><a href="KCMCEditPatientDetails.php?PatientNumber=' . $Patient[0] . '&BranchCode=' . $Patient[1] . '" target="_blank">
					<img width="15px" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/user.png" alt="Patient Details" /></a>
			</th>
		</tr>';
	echo '<tr><td>' . _('Date of Admission') . ':</td>
		<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="AdmissionDate" maxlength="10" size="11" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td></tr>';
	echo '<tr><td>' . _('Type of Service') . ':</td>';
	$SQL = "SELECT stockid,
				description
			FROM stockmaster
			LEFT JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='S'";

	$Result = DB_query($SQL);
	if (isset($_POST['StockID'])) {
		$StockID = $_POST['StockID'];
	} else {
		$StockID = '';
	}

	for ($i = 0;$i < $_SESSION['Items']['Lines'];$i++) {
		if (isset($_SESSION['Items'][$i])) {
			while ($MyRow = DB_fetch_array($Result)) {
				if ($MyRow['stockid'] == $_SESSION['Items'][$i]['StockID']) {
					echo '<td>' . $MyRow['description'] . '</td>';
				}
			}
			echo '<td>&nbsp;' . _('Quantity') . ' - ';
			echo '&nbsp;' . $_SESSION['Items'][$i]['Quantity'];
			echo '&nbsp;@&nbsp;' . number_format($_SESSION['Items'][$i]['Price'], 0) . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>';
			if ($_SESSION['CanAmendBill'] == 1) {
				echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?Delete=' . $i . '&Patient=' . $Patient[0] . '&Branch=' . $Patient[1] . '">' . _('Delete') . '</a></td></tr>';
			} else {
				echo '<td>' . _('Delete') . '</td></tr>';
			}
			DB_data_seek($Result, 0);
			echo '<tr><td>';
		}
	}
	echo '<td><select name="StockID">';
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . ' - ' . $MyRow['description'] . '</option>';
	}
	echo '</select></td>';
	echo '<td>&nbsp;' . _('Quantity') . ' - ';
	echo '<select name="Quantity" onChange="ReloadForm(ChangeItem)">';
	echo '<option value=""></option>';
	for ($j = 0;$j < 100;$j++) {
		echo '<option value="' . $j . '">' . $j . '</option>';
	}
	echo '</select></td></tr>';
	DB_data_seek($Result, 0);

	echo '<input type="submit" name="ChangeItem" style="display:none" value=" " />';
	echo '<tr><td>' . _('Payment Fee') . '</td>';
	echo '<td>' . number_format($_SESSION['Items']['Value'], 0) . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td></tr>';
	echo '<input type="hidden" name="Price" value="' . $_SESSION['Items']['Value'] . '" />';

	$SQL = "SELECT supplierid,
					suppname
				FROM suppliers
				LEFT JOIN suppliertype
					ON suppliertype.typeid=suppliers.supptype
				WHERE suppliertype.typename='Doctors'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		echo '<tr><td>' . _('Doctors Name') . ':</td>';
		echo '<td><select name="Doctor">';
		echo '<option value="">' . _('Select a doctor from list') . '</option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['Doctor']) and $_POST['Doctor'] == $MyRow['supplierid']) {
				echo '<option selected="selected" value="' . $MyRow['supplierid'] . '">' . $MyRow['supplierid'] . ' - ' . $MyRow['suppname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['supplierid'] . '">' . $MyRow['supplierid'] . ' - ' . $MyRow['suppname'] . '</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><td>';
		if (isset($_POST['DoctorsFee']) and $_POST['DoctorsFee'] != '') {
			echo _('Doctors Fee') . ':</td><td><input type="text" class="number" size="10" name="DoctorsFee" value="' . locale_number_format(filter_number_format($_POST['DoctorsFee']), $_SESSION['CompanyRecord']['currencydefault']) . '" />';
		} else {
			echo _('Doctors Fee') . ':</td><td><input type="text" class="number" size="10" name="DoctorsFee" value="" />';
		}
		if (isset($_POST['AddDoctorFee'])) {
			echo '<input type="checkbox" checked="checked" name="AddDoctorFee" value="Add Doctors fee to balance" onChange="ReloadForm(ChangeItem)" />' . _('Add Doctors fee to balance') . '</td></tr>';
		} else {
			echo '<input type="checkbox" name="AddDoctorFee" value="Add Doctors fee to balance" onChange="ReloadForm(ChangeItem)" />' . _('Add Doctors fee to balance') . '</td></tr>';
		}
	}

	if ($Patient[1] == 'CASH') {
		if (!isset($Received)) {
			$Received = $_SESSION['Items']['Value'];
		}
		echo '<tr><td>' . _('Amount Received') . '</td>';
		echo '<td><input type="text" class="number" size="10" name="Received" value="' . number_format($Received, 0, '.', '') . '" /></td></tr>';

		$SQL = "SELECT bankaccountname,
					bankaccounts.accountcode,
					bankaccounts.currcode
			FROM bankaccounts
			INNER JOIN bankaccountusers
				ON bankaccounts.accountcode=bankaccountusers.accountcode
			WHERE pettycash=1
				AND bankaccountusers.userid='" . $_SESSION['UserID'] . "'";

		$ErrMsg = _('The bank accounts could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the bank accounts was');
		$AccountsResults = DB_query($SQL, $ErrMsg, $DbgMsg);

		echo '<tr><td>' . _('Received into') . ':</td><td><select name="BankAccount">';

		if (DB_num_rows($AccountsResults) == 0) {
			echo '</select></td></tr></table><p>';
			prnMsg(_('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'), 'warn');
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
			echo '</select></td></tr>';
		}
		echo '<tr><td>' . _('Comments') . '</td>';
		echo '<td><input type="text" size="50" name="Comments" value="" /></td></tr>';
		echo '</table>';
		echo '<input type="submit" name="SubmitCash" value="' . _('Make Payment') . '" />';
	} else {
		echo '<tr><td>' . _('Insurance Reference') . '</td>';
		echo '<td><input type="text" size="10" name="InsuranceRef" value="" /></td></tr>';
		echo '<tr><td>' . _('Comments') . '</td>';
		echo '<td><input type="text" size="50" name="Comments" value="" /></td></tr>';
		echo '</table>';
		echo '<input type="submit" name="SubmitInsurance" value="' . _('Process Invoice') . '" />';
	}
	echo '<input type="submit" name="Cancel" value="' . _('Cancel Transaction') . '" />';
	echo '</form>';
}

include ('includes/footer.php');
?>