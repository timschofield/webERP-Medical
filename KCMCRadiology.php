<?php
include('includes/session.inc');
$title = _('Billing For Radiology Tests');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');
include('includes/CustomerSearch.php');

if (isset($_GET['New']) or isset($_POST['Cancel'])) {
	unset($_POST['SubmitCash']);
	unset($_POST['Patient']);
	unset($_SESSION['Items']);
	$_SESSION['Items']['Lines']=0;
	$_SESSION['Items']['Value']=0;
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

if (isset($_GET['Delete'])) {
	$_SESSION['Items']['Value']-=$_SESSION['Items'][$_GET['Delete']]['Quantity']*$_SESSION['Items'][$_GET['Delete']]['Price'];
	unset($_SESSION['Items'][$_GET['Delete']]);
	$_POST['Patient'] = $_GET['Patient']. ' ' . $_GET['Branch'];
	$Patient[0] = $_GET['Patient'];
	$Patient[1] = $_GET['Branch'];
}

if (isset($_POST['ChangeItem'])) {
	$sql="SELECT price
				FROM prices
				WHERE stockid='".$_POST['StockID']."'
				AND typeabbrev='".$_POST['PriceList']."'
				AND '".FormatDateForSQL($_POST['AdmissionDate'])."' between startdate and enddate";
	$PriceResult=DB_query($sql,$db);
	if (DB_num_rows($PriceResult)==0) {
		$Price=0;
	} else {
		$myrow=DB_fetch_array($PriceResult);
		$Price=$myrow['price'];
	}
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['StockID']=$_POST['StockID'];
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['Quantity']=1;
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['Price']=$Price;
	$_SESSION['Items']['Value']+=$Price;
	$_SESSION['Items']['Lines']++;
}

if (isset($_POST['SubmitCash']) or isset($_POST['SubmitInsurance'])) {

	$InputError=0;

	if ((!isset($_POST['BankAccount']) or $_POST['BankAccount']=='') and !isset($_POST['SubmitInsurance'])) {
		$InputError=1;
		$msg[]=_('You must select a cash collection point');
	}

	if ($_SESSION['Items']['Lines']==0) {
		$InputError=1;
		$msg[]=_('You must select a test to bill');
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

		for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
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
																'1',
																'0',
																'" . _('Sales order for radiology transaction') . "',
																'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
																'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
																'1',
																1
															)";
				$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
				$Ins_LineItemResult = DB_query($LineItemSQL,$db,$ErrMsg,$DbgMsg,true);
				if ($_SESSION['Care2xDatabase']!='None') {
					$SQL="UPDATE ".$_SESSION['Care2xDatabase'].".care_encounter_prescription SET bill_number='".$OrderNo."'
								WHERE nr='".$_SESSION['Items'][$i]['Care2x']."'";
					$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
					$UpdateCare2xResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			}
		}

		if (isset($_POST['Doctor'])) {
			$SuppInvoiceNumber = GetNextTransNo(20, $db);
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
														'" . $_POST['Doctor'] . ' ' . _('fee'). ' ' . _('for patient') . ' ' . $_POST['PatientNo'] . "',
														'0',
														''
													)";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The doctors transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the doctors transaction record was used');
			$Result = DB_query($DoctorsInvoiceSQL,$db,$ErrMsg,$DbgMsg,true);
		}

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
				'" . $_SESSION['Items']['Value'] . "',
				'0',
				'1',
				'" . _('Invoice for radiology test of Patient number').' '.$_POST['PatientNo'] . "',
				'" . $_POST['InsuranceRef'] . "',
				'1',
				'" . filter_currency_input($_POST['Received']) . "')";

		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
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
						'" . _('Invoice for radiology test of Patient number').' '.$_POST['PatientNo'] . "',
						-1,
						'" . $_SESSION['Items'][$i]['Price'] . "',
						1,
						0
					)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for'). ' '. $_POST['StockID'] . ' ' .
					_('could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}
		$SQL="SELECT salestype
				FROM debtorsmaster
				WHERE debtorno='".$_POST['PatientNo']."'";
		$Result=DB_query($SQL, $db);
		$myrow=DB_fetch_array($Result);
		$SalesGLAccounts = GetSalesGLAccount('AN', $_SESSION['Items'][0]['StockID'], $myrow['salestype'], $db);
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
									'" . _('Invoice for radiology test of Patient number').' '.$_POST['PatientNo'] . "',
									'" . -$_SESSION['Items']['Value'] . "')";

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
									'" . _('Invoice for radiology test of Patient number').' '.$_POST['PatientNo'] . "',
									'" . $_SESSION['Items']['Value'] . "')";

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
										'" . _('Payment of radiology test for Patient number').' '.$_POST['PatientNo'] . "',
										'" . ($_SESSION['Items']['Value']) . "')";
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
										'" . _('Payment of radiology test for Patient number').' '.$_POST['PatientNo'] . "',
										'" . -($_SESSION['Items']['Value']) . "'
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
									'" . _('Radiology test for Patient').' '.$_POST['PatientNo'] . "',
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
											'" . -$_SESSION['Items']['Value'] . "',
											'" . -filter_currency_input($_POST['Received']) . "',
											'" . _('Payment of radiology test for Patient number').' '.$_POST['PatientNo'] . "'
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
				WHERE debtorsmaster.debtorno='".$Patient[0]."'
				AND branchcode='".$Patient[1]."'";
	$result=DB_query($sql, $db);
	$mydebtorrow=DB_fetch_array($result);
	if ($_SESSION['Care2xDatabase']!='None' and $_SESSION['Items']['Lines']==0) {
		$Care2xSQL="SELECT ".$_SESSION['Care2xDatabase'].".care_encounter_prescription.article_item_number,
							".$_SESSION['Care2xDatabase'].".care_encounter_prescription.nr,
							partcode,
							total_dosage,
							prescribe_date
						FROM ".$_SESSION['Care2xDatabase'].".care_encounter_prescription
						LEFT JOIN ".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices
						ON ".$_SESSION['Care2xDatabase'].".care_encounter_prescription.article_item_number=".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices.item_id
						LEFT JOIN stockmaster
						ON ".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices.partcode=stockmaster.stockid
						LEFT JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						LEFT JOIN ".$_SESSION['Care2xDatabase'].".care_encounter
						ON ".$_SESSION['Care2xDatabase'].".care_encounter.encounter_nr=".$_SESSION['Care2xDatabase'].".care_encounter_prescription.encounter_nr
						AND stockcategory.stocktype='X'
						WHERE ".$_SESSION['Care2xDatabase'].".care_encounter.pid='".$Patient[0]."'
						AND ".$_SESSION['Care2xDatabase'].".care_encounter_prescription.bill_number=''";
		$Care2xResult=DB_query($Care2xSQL, $db);
		$i=0;
		while ($MyCare2xRow=DB_fetch_array($Care2xResult)) {
			$PriceSQL="SELECT price
						FROM prices
						WHERE stockid='".$MyCare2xRow['partcode']."'
						AND typeabbrev='".$mydebtorrow['salestype']."'
						AND '".$MyCare2xRow['prescribe_date']."' between startdate and enddate";
			$PriceResult=DB_query($PriceSQL,$db);
			if (DB_num_rows($PriceResult)==0) {
				$Price=0;
			} else {
				$myrow=DB_fetch_array($PriceResult);
				$Price=$myrow['price'];
			}
			$_SESSION['Items'][$i]['StockID']=$MyCare2xRow['partcode'];
			$_SESSION['Items'][$i]['Quantity']=$MyCare2xRow['total_dosage'];
			$_SESSION['Items'][$i]['Price']=$Price;
			$_SESSION['Items'][$i]['Care2x']=$MyCare2xRow['nr'];
			$_SESSION['Items']['Value']+=$Price*$MyCare2xRow['total_dosage'];
			$_SESSION['Items']['Lines']++;
			$i++;
		}
	}
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/XRay.png" title="' . _('Search') . '" alt="" />' . $title . '</p>';

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="PriceList" value="'.$mydebtorrow['salestype'].'" />';
	echo '<input type="hidden" name="PatientNo" value="'.$Patient[0].'" />';
	echo '<input type="hidden" name="BranchNo" value="'.$Patient[1].'" />';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="3" class="header">'.$mydebtorrow['name'].' - '.$mydebtorrow['phoneno'].'</th>
			<th style="text-align: right"><a href="KCMCEditPatientDetails.php?PatientNumber='.$Patient[0].'&BranchCode='.$Patient[1].'" target="_blank">
					<img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/user.png" alt="Patient Details" /></a>
			</th>
		</tr>';
	echo '<tr><td>'._('Date of Test').':</td>
		<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="AdmissionDate" maxlength="10" size="11" value="' .
					 date($_SESSION['DefaultDateFormat']) . '" /></td></tr>';
	echo '<tr><td>'._('Type of radiology test').':</td>';
	$sql="SELECT stockid,
				description
			FROM stockmaster
			LEFT JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='X'";

	$result=DB_query($sql, $db);
	if (isset($_POST['StockID'])) {
		$StockID=$_POST['StockID'];
	} else {
		$StockID='';
	}

	for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
		if (isset($_SESSION['Items'][$i])) {
			while ($myrow=DB_fetch_array($result)) {
				if ($myrow['stockid']==$_SESSION['Items'][$i]['StockID']) {
					echo '<td>' . $myrow['description'] . '</td>';
				}
			}
			echo '<td>&nbsp;@&nbsp;'.locale_money_format($_SESSION['Items'][$i]['Price'],$_SESSION['CompanyRecord']['currencydefault']).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td>';
			echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?Delete=' . $i . '&Patient='.$Patient[0].'&Branch='.$Patient[1].'">' . _('Delete') . '</a></td></tr>';
			DB_data_seek($result,0);
			echo '<tr><td>';
		}
	}
	echo '<td><select name="StockID" onChange="ReloadForm(ChangeItem)">';
	echo '<option value="">'._('Select a test from list').'</option>';
	while ($myrow=DB_fetch_array($result)) {
		echo '<option value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
	}
	echo '</select></td></tr>';
	DB_data_seek($result,0);

	echo '<input type="submit" name="ChangeItem" style="visibility: hidden" value=" " />';
	echo '<tr><td>'._('Payment Fee').'</td>';
	echo '<td>'.locale_money_format($_SESSION['Items']['Value'], $_SESSION['CompanyRecord']['currencydefault']).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td></tr>';
	echo '<input type="hidden" name="Price" value="'.$_SESSION['Items']['Value'].'" />';

	if ($Patient[1]=='CASH') {
		if (!isset($Received)) {
			$Received=$_SESSION['Items']['Value'];
		}
		echo '<tr><td>'._('Amount Received').'</td>';
		echo '<td><input type="text" class="number" size="10" name="Received" value="'.locale_money_format($Received,$_SESSION['CompanyRecord']['currencydefault']).'" /></td></tr>';

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

		$sql="SELECT supplierid,
					suppname
				FROM suppliers
				LEFT JOIN suppliertype
					ON suppliertype.typeid=suppliers.supptype
				WHERE suppliertype.typename='Doctors'";
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)>0) {
			echo '<tr><td>'._('Doctors Name').':</td>';
			echo '<td><select name="Doctor">';
			echo '<option value="">'._('Select a doctor from list').'</option>';
			while ($myrow=DB_fetch_array($result)) {
				echo '<option value="'.$myrow['supplierid'].'">'.$myrow['supplierid']. ' - ' . $myrow['suppname'].'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><td>' . _('Doctors Fee') . ':</td>';
			echo '<td><input type="text" class="number" size="10" name="DoctorsFee" value="0" /></td></tr>';
		}
		echo '<tr><td>'._('Comments').'</td>';
		echo '<td><input type="text" size="50" name="Comments" value="" /></td></tr>';
		echo '<tr><td colspan="2" style="text-align: left"><button type="submit" style="text-align:left" name="SubmitCash"><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/tick.png" />'._('Make Payment').'</button>';
	} else {
		echo '<tr><td>'._('Insurance Reference').'</td>';
		echo '<td><input type="text" size="10" name="InsuranceRef" value="" /></td></tr>';

		$sql="SELECT supplierid,
					suppname
				FROM suppliers
				LEFT JOIN suppliertype
					ON suppliertype.typeid=suppliers.supptype
				WHERE suppliertype.typename='Doctors'";
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)>0) {
			echo '<tr><td>'._('Doctors Name').':</td>';
			echo '<td><select name="Doctor">';
			echo '<option value="">'._('Select a doctor from list').'</option>';
			while ($myrow=DB_fetch_array($result)) {
				echo '<option value="'.$myrow['supplierid'].'">'.$myrow['supplierid']. ' - ' . $myrow['suppname'].'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><td>' . _('Doctors Fee') . ':</td>';
			echo '<td><input type="text" class="number" size="10" name="DoctorsFee" value="0" /></td></tr>';
		}
		echo '<tr><td>'._('Comments').'</td>';
		echo '<td><input type="text" size="50" name="Comments" value="" /></td></tr>';
		echo '<tr><td colspan="2" style="text-align: left"><button type="submit" style="text-align:left" name="SubmitInsurance"><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/tick.png" />'._('Process Invoice').'</button>';
	}
	echo '<button type="submit" name="Cancel" value=""><img width="15px" src="' . $rootpath . '/css/' . $theme . '/images/cross.png" />'._('Cancel Transaction').'</button></td></tr>';

	echo '</table><br />';
	echo '</form>';
}

include('includes/footer.inc');
?>