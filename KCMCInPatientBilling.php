<?php
include('includes/session.inc');
$title = _('Billing For All Drugs and Services');
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

if (isset($_GET['Delete'])) {
	$_SESSION['Items']['Value']-=$_SESSION['Items'][$_GET['Delete']]['Quantity']*$_SESSION['Items'][$_GET['Delete']]['Price'];
	unset($_SESSION['Items'][$_GET['Delete']]);
	$_POST['Patient'] = $_GET['Patient']. ' ' . $_GET['Branch'];
	$Patient[0] = $_GET['Patient'];
	$Patient[1] = $_GET['Branch'];
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

if (isset($_POST['ChangeItem'])) {
	$Patient[0]=$_POST['PatientNo'];
	$Patient[1]=$_POST['BranchNo'];
}

if (isset($_POST['ChangeItem']) and $_POST['StockID']!='') {
	$Patient[0]=$_POST['PatientNo'];
	$Patient[1]=$_POST['BranchNo'];
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
	$sql="SELECT materialcost+labourcost+overheadcost as standardcost
				FROM stockmaster
				WHERE stockid='".$_POST['StockID']."'";
	$CostResult=DB_query($sql, $db);
	$CostRow=DB_fetch_array($CostResult);
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['StandardCost']=$CostRow['standardcost'];
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['StockID']=$_POST['StockID'];
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['Quantity']=filter_number_input($_POST['Quantity']);
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['Price']=$Price;
	$_SESSION['Items']['Value']+=$Price*filter_number_input($_POST['Quantity']);
	$_SESSION['Items']['Lines']++;
	unset($_POST['StockType']);
} else if (isset($_POST['ChangeItem']) and $_POST['StockID']=='' and isset($_POST['AddDoctorFee'])) {
	$_SESSION['Items']['Value']+=filter_currency_input($_POST['DoctorsFee']);
} else if (isset($_POST['ChangeItem']) and $_POST['StockID']=='' and !isset($_POST['AddDoctorFee'])) {
	$_SESSION['Items']['Value']-=filter_currency_input($_POST['DoctorsFee']);
}
if (isset($_POST['Dispensary'])) {
	$_SESSION['Items']['Dispensary']=$_POST['Dispensary'];
} else {
	$_SESSION['Items']['Dispensary']=$_SESSION['UserStockLocation'];
}

if (isset($_POST['UpdateItems'])) {
	$_SESSION['Items'][$_SESSION['Items']['Lines']]['StockType']=$_POST['StockType'];
	$Patient[0]=$_POST['PatientNo'];
	$Patient[1]=$_POST['BranchNo'];
}

if (isset($_POST['SubmitCash']) or isset($_POST['SubmitInsurance'])) {

	$InputError=0;

	if ((!isset($_POST['Dispensary']) or $_POST['Dispensary']=='')) {
		$InputError=1;
		$msg[]=_('You must select a location where the drugs are to be dispensed from');
	}

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
			$_POST['ChangeItem']='Yes';
			$Patient[0]=$_POST['PatientNo'];
			$Patient[1]=$_POST['BranchNo'];
		}
	} else {
		$ExRate=1;
		DB_Txn_Begin($db);
		/*First off create the sales order
		* entries in the database
		*/

		$sql="SELECT area
				FROM custbranch
				WHERE branchcode='" . $Patient[1] . "'
					AND debtorno='" . $Patient[0] . "'";
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_array($result);
		$Area=$myrow['area'];

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
												'" . $Patient[0] . "',
												'" . $Patient[1] . "',
												'" . DB_escape_string($_POST['Comments']) ."',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												'1',
												'" . $_SESSION['Items']['Dispensary'] . "',
												'" . $_SESSION['Items']['Dispensary'] ."',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												0
											)";

		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

		for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
			if (isset($_SESSION['Items'][$i]['StockID'])) {
				$LineItemSQL = "INSERT INTO salesorderdetails ( orderlineno,
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
																'" . _('Sales order for inpatient transaction') . "',
																'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
																'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
																'" . $_SESSION['Items'][$i]['Quantity'] . "',
																1
															)";
				$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
				$Ins_LineItemResult = DB_query($LineItemSQL,$db,$ErrMsg,$DbgMsg,true);
			}
			if ($_SESSION['Care2xDatabase']!='None') {
				$SQL="UPDATE ".$_SESSION['Care2xDatabase'].".care_encounter_prescription SET bill_number='".$OrderNo."'
							WHERE nr='".$_SESSION['Items'][$i]['Care2x']."'";
				$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
				$UpdateCare2xResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}
		$InvoiceNo = GetNextTransNo(10, $db);
		$PeriodNo = GetPeriod(Date($_POST['AdmissionDate']), $db);

		if (isset($_POST['Doctor']) and $_POST['Doctor']!='') {
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
														'" . filter_currency_input($_POST['DoctorsFee']) . "',
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
												'FEE',
												'" . filter_currency_input($_POST['DoctorsFee']) . "',
												'1',
												'0',
												'" . _('Doctors fee for inpatient transaction') . "',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
												'1',
												1
											)";
			$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
			$Ins_LineItemResult = DB_query($LineItemSQL,$db,$ErrMsg,$DbgMsg,true);

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
					'FEE',
					 10,
					'" . $InvoiceNo . "',
					'" . $_SESSION['Items']['Dispensary'] . "',
					'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
					'" . $Patient[0] . "',
					'" . $Patient[1] . "',
					'" . $PeriodNo . "',
					'" . _('Doctors Fee Patient transactions for Patient number').' '.$_POST['PatientNo'] . "',
					'1',
					'" . filter_currency_input($_POST['DoctorsFee']) . "',
					1,
					0
				)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for'). ' '. $_POST['StockID'] . ' ' .
			_('could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

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
				'" . $Patient[0] . "',
				'" . $Patient[1] . "',
				'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
				'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
				'" . $PeriodNo . "',
				'" . $OrderNo . "',
				'" . $_SESSION['Items']['Value'] . "',
				'0',
				'1',
				'" . _('Invoice of Patient transactions for Patient number').' '.$_POST['PatientNo'] . "',
				'" . $_POST['InsuranceRef'] . "',
				'1',
				'" . filter_currency_input($_POST['Received']) . "'
			)";

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
						'" . $_SESSION['Items']['Dispensary'] . "',
						'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
						'" . $Patient[0] . "',
						'" . $Patient[1] . "',
						'" . $PeriodNo . "',
						'" . _('Invoice of Patient transactions for Patient number').' '.$_POST['PatientNo'] . "',
						'" . -$_SESSION['Items'][$i]['Quantity'] . "',
						'" . $_SESSION['Items'][$i]['Price'] . "',
						1,
						0
					)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for'). ' '. $_POST['StockID'] . ' ' .
				_('could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

				if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $_SESSION['Items'][$i]['StandardCost'] !=0){

				/*first the cost of sales entry*/

					$SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
												VALUES (
													10,
													'" . $InvoiceNo . "',
													'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
													'" . $PeriodNo . "',
													'" . GetCOGSGLAccount($Area, $_SESSION['Items'][$i]['StockID'], $_POST['PriceList'], $db) . "',
													'" . $_POST['PatientNo'] . " - " . $_SESSION['Items'][$i]['StockID'] . " x " . $_SESSION['Items'][$i]['Quantity'] . " @ " . $_SESSION['Items'][$i]['StandardCost'] . "',
													'" . filter_currency_input($_SESSION['Items'][$i]['StandardCost'] * $_SESSION['Items'][$i]['Quantity']) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/*now the stock entry*/
					$StockGLCode = GetStockGLCode($_SESSION['Items'][$i]['StockID'],$db);

					$SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount )
												VALUES (
													10,
													'" . $InvoiceNo . "',
													'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
													'" . $PeriodNo . "',
													'" . $StockGLCode['stockact'] . "',
													'" . $_POST['PatientNo'] . " - " . $_SESSION['Items'][$i]['StockID'] . " x " . $_SESSION['Items'][$i]['Quantity'] . " @ " . $_SESSION['Items'][$i]['StandardCost'] . "',
													'" . filter_currency_input(-$_SESSION['Items'][$i]['StandardCost'] * $_SESSION['Items'][$i]['Quantity']) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /* end of if GL and stock integrated and standard cost !=0 */
		/*Insert Sales Analysis records */

				$SQL="SELECT COUNT(*),
							salesanalysis.stockid,
							salesanalysis.stkcategory,
							salesanalysis.cust,
							salesanalysis.custbranch,
							salesanalysis.area,
							salesanalysis.periodno,
							salesanalysis.typeabbrev,
							salesanalysis.salesperson
						FROM salesanalysis,
							custbranch,
							stockmaster
						WHERE salesanalysis.stkcategory=stockmaster.categoryid
							AND salesanalysis.stockid=stockmaster.stockid
							AND salesanalysis.cust=custbranch.debtorno
							AND salesanalysis.custbranch=custbranch.branchcode
							AND salesanalysis.area=custbranch.area
							AND salesanalysis.salesperson=custbranch.salesman
							AND salesanalysis.typeabbrev ='" . $_POST['PriceList'] . "'
							AND salesanalysis.periodno='" . $PeriodNo . "'
							AND salesanalysis.cust " . LIKE . " '" . $_POST['PatientNo'] . "'
							AND salesanalysis.custbranch " . LIKE . " '" . $_POST['BranchNo'] . "'
							AND salesanalysis.stockid " . LIKE . " '" . $_SESSION['Items'][$i]['StockID'] . "'
							AND salesanalysis.budgetoractual=1
						GROUP BY salesanalysis.stockid,
								salesanalysis.stkcategory,
								salesanalysis.cust,
								salesanalysis.custbranch,
								salesanalysis.area,
								salesanalysis.periodno,
								salesanalysis.typeabbrev,
								salesanalysis.salesperson";

				$ErrMsg = _('The count of existing Sales analysis records could not run because');
				$DbgMsg = _('SQL to count the no of sales analysis records');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$myrow = DB_fetch_array($Result);

				if ($myrow[0]>0){  /*Update the existing record that already exists */

					$SQL = "UPDATE salesanalysis
								SET amt=amt+" . filter_currency_input($_SESSION['Items'][$i]['Price'] * $_SESSION['Items'][$i]['Quantity'] / $ExRate) . ",
									cost=cost+" . filter_currency_input($_SESSION['Items'][$i]['StandardCost'] * $_SESSION['Items'][$i]['Quantity']) . ",
									qty=qty +" . $_SESSION['Items'][$i]['Quantity'] . ",
									disc=disc+" . filter_currency_input(0 * $_SESSION['Items'][$i]['Price'] * $_SESSION['Items'][$i]['Quantity'] / $ExRate) . "
								WHERE salesanalysis.area='" . $myrow[5] . "'
									AND salesanalysis.salesperson='" . $myrow['salesperson'] . "'
									AND typeabbrev ='" . $_POST['PriceList'] . "'
									AND periodno = '" . $PeriodNo . "'
									AND cust " . LIKE . " '" . $Patient[0] . "'
									AND custbranch " . LIKE . " '" . $Patient[1] . "'
									AND stockid " . LIKE . " '" . $_SESSION['Items'][$i]['StockID'] . "'
									AND salesanalysis.stkcategory ='" . $myrow['stkcategory'] . "'
									AND budgetoractual=1";

				} else { /* insert a new sales analysis record */

					$SQL = "INSERT INTO salesanalysis (	typeabbrev,
														periodno,
														amt,
														cost,
														cust,
														custbranch,
														qty,
														disc,
														stockid,
														area,
														budgetoractual,
														salesperson,
														stkcategory	)
													SELECT
														'" . $_POST['PriceList'] . "',
														'" . $PeriodNo . "',
														'" . filter_currency_input($_SESSION['Items'][$i]['Price'] * $_SESSION['Items'][$i]['Quantity'] / $ExRate) . "',
														'" . filter_currency_input($_SESSION['Items'][$i]['StandardCost'] * $_SESSION['Items'][$i]['Quantity']) . "',
														'" . $Patient[0] . "',
														'" . $Patient[1] . "',
														'" . $_SESSION['Items'][$i]['Quantity'] . "',
														'" . filter_currency_input(0 * $_SESSION['Items'][$i]['Price'] * $_SESSION['Items'][$i]['Quantity'] / $ExRate) . "',
														'" . $_SESSION['Items'][$i]['StockID'] . "',
														custbranch.area,
														1,
														custbranch.salesman,
														stockmaster.categoryid
													FROM stockmaster,
														custbranch
													WHERE stockmaster.stockid = '" . $_SESSION['Items'][$i]['StockID'] . "'
														AND custbranch.debtorno = '" . $Patient[0] . "'
														AND custbranch.branchcode='" . $Patient[1] . "'";
				}
				$BaseStockID=$_SESSION['Items'][$i]['StockID'];

				$ErrMsg = _('Sales analysis record could not be added or updated because');
				$DbgMsg = _('The following SQL to insert the sales analysis record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}
		$SQL="SELECT salestype
				FROM debtorsmaster
				WHERE debtorno='".$_POST['PatientNo']."'";
		$Result=DB_query($SQL, $db);
		$myrow=DB_fetch_array($Result);
		$SalesGLAccounts = GetSalesGLAccount('AN', $BaseStockID, $myrow['salestype'], $db);
		$SQL = "INSERT INTO gltrans (type,
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
									'" . _('Invoice of Patient Transactions for Patient number').' '.$Patient[0] . "',
									'" . -$_SESSION['Items']['Value'] . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									defaulttag,
									narrative,
									amount )
								VALUES (10,
									'" . $InvoiceNo . "',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
									'" . $_SESSION['DefaultTag'] . "',
									'" . _('Invoice of Patient Transactions for Patient number').' '.$Patient[0] . "',
									'" . $_SESSION['Items']['Value'] . "'
								)";

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
										'" . _('Payment of Patient Transactions for Patient number').' '.$Patient[0] . "',
										'" . ($_SESSION['Items']['Value']) . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
			$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
			if ($_POST['Received']!=0) {
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
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
										'" . _('Payment of Patient Transactions for Patient number').' '.$Patient[0] . "',
										'" . -($_SESSION['Items']['Value']) . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
			$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
			if ($_POST['Received']!=0) {
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

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
									'" . _('Patient Transactions for Patient').' '.$Patient[0] . "',
									'1',
									'1',
									'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
									'2',
									'" . filter_currency_input($_POST['Received']) . "',
									'" . $_SESSION['CompanyRecord']['currencydefault'] . "',
									'" . $_SESSION['UserID'] . "'
								)";

			$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
			$ErrMsg = _('Cannot insert a bank transaction');
			if ($_POST['Received']!=0) {
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

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
											'" . $Patient[0] . "',
											'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
											'" . FormatDateForSQL($_POST['AdmissionDate']) . "',
											'" . $PeriodNo . "',
											'" . $InvoiceNo . "',
											'1',
											'" . -filter_currency_input($_POST['Received']) . "',
											'" . -filter_currency_input($_POST['Received']) . "',
											'" . _('Payment of Patient Transactions for Patient number').' '.$Patient[0] . "'
										)";

			prnMsg( _('The transaction has been successfully posted'), 'success');
			echo '<br /><div class="centre"><a href="'.$_SERVER['PHP_SELF'].'?New=True">'._('Enter another receipt').'</a>';
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			if ($_POST['Received']!=0) {
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

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

if (isset($Patient) or isset($_POST['ChangeItem'])) {
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
	$sql="SELECT sum(ovamount+ovgst) as balance
				FROM debtortrans
				WHERE debtorno='".$Patient[0]."'";
	$result=DB_query($sql, $db);
	$mybalancerow=DB_fetch_array($result);
	$Balance=$mybalancerow['balance'];
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/DoctorFemale.png" title="' . _('Search') . '" alt="" />' . $title . '</p>';

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="PriceList" value="'.$mydebtorrow['salestype'].'" />';
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
	$sql = "SELECT loccode,
				locationname
			FROM locations";

	$ErrMsg = _('The locations could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the locations was');
	$LocationResults = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo '<tr><td>' . _('Drugs issued from') . ':</td><td><select name="Dispensary">';

	if (DB_num_rows($LocationResults)==0){
		echo '</select></td></tr></table><p>';
		prnMsg( _('Locations have not yet been defined. You must first') . ' <a href="' . $rootpath . '/Locations.php">' . _('define the locations') . '</a> ' ,'warn');
		include('includes/footer.inc');
		exit;
	} else {
		echo '<option value=""></option>';
		while ($myrow=DB_fetch_array($LocationResults)){
		/*list the bank account names */
			if (isset($_POST['Dispensary']) and $_POST['Dispensary']==$myrow['loccode']){
				echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '<tr><td>'._('Filter Item List').':</td><td><input type="search" name="SearchString" maxlength="30" size="21" value="" onKeyUp="FilterArray(StockID,SearchString.value,StockID2)" /></td></tr>';

	echo '<input type="submit" name="UpdateItems" style="visibility: hidden" value=" " />';
	echo '<tr>
			<td>' . _('Type of Item:') . '</td>';

	for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
//		ShowStockTypes($_SESSION['Items'][$i]['StockType']);
		if (isset($_SESSION['Items'][$i])) {
			$sql="SELECT stocktype
					FROM stockmaster
					LEFT JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockid='".$_SESSION['Items'][$i]['StockID']."'";
			$result=DB_query($sql, $db);
			$myrow=DB_fetch_array($result);
			$sql="SELECT stockid,
						description,
						categorydescription
					FROM stockmaster
					LEFT JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockcategory.stocktype='".$myrow['stocktype']."'
					ORDER BY description";

			$result=DB_query($sql, $db);
			$myrow=DB_fetch_array($result);
			echo '<td>' . $myrow['categorydescription'] . '</td>';

			DB_data_seek($result,0);
			if (isset($_POST['StockID'])) {
				$StockID=$_POST['StockID'];
			} else {
				$StockID='';
			}
			while ($myrow=DB_fetch_array($result)) {
				if ($myrow['stockid']==$_SESSION['Items'][$i]['StockID']) {
					echo '<td>' .$myrow['description'] . '</td>';
				}
			}
			echo '<td>' . _('Quantity') . ' - ';
			echo '&nbsp;' . $_SESSION['Items'][$i]['Quantity'];
			echo '&nbsp;@&nbsp;'.number_format($_SESSION['Items'][$i]['Price'],0).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td>';
			if ($_SESSION['CanViewPrices']==1){
				echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?Delete=' . $i . '&Patient='.$Patient[0].'&Branch='.$Patient[1].'">' . _('Delete') . '</a></td></tr>';
			} else {
				echo '<td>' . _('Delete') . '</td></tr>';
			}
			DB_data_seek($result,0);
			echo '<tr><td>';
		}
	}
	if (!isset($_POST['StockType'])) {
		ShowStockTypes('');
		$sql="SELECT stockid,
				description
		FROM stockmaster
		LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
		ORDER BY description";
	} else {
		ShowStockTypes($_POST['StockType']);
		$sql="SELECT stockid,
				description
		FROM stockmaster
		LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='".$_POST['StockType']."'
		ORDER BY description";
	}
	$result=DB_query($sql, $db);
	echo '</td><td><select name="StockID">';
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($result)) {
		echo '<option value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
	}
	echo '</select></td>';
	echo '<td>&nbsp;' . _('Quantity') . ' - ';
	echo '<select name="Quantity" onChange="ReloadForm(ChangeItem)">';
	echo '<option value=""></option>';
	for ($j=0; $j<100; $j++) {
		echo '<option value="'.$j.'">'.$j.'</option>';
	}
	echo '</select></td></tr>';
	DB_data_seek($result,0);

	echo '<select name="StockID2" style="visibility: hidden">';
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($result)) {
		echo '<option value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
	}
	echo '</select>';
	DB_data_seek($result,0);

	echo '<input type="submit" name="ChangeItem" style="visibility: hidden" value=" " />';

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
			if (isset($_POST['Doctor']) and $_POST['Doctor']==$myrow['supplierid']) {
				echo '<option selected="selected" value="'.$myrow['supplierid'].'">'.$myrow['supplierid']. ' - ' . $myrow['suppname'].'</option>';
			} else {
				echo '<option value="'.$myrow['supplierid'].'">'.$myrow['supplierid']. ' - ' . $myrow['suppname'].'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><td>';
		if (isset($_POST['DoctorsFee'])) {
			echo _('Doctors Fee') . ':</td><td><input type="text" class="number" size="10" name="DoctorsFee" value="' . locale_money_format(filter_currency_input($_POST['DoctorsFee']), $_SESSION['CompanyRecord']['currencydefault']) .'" />';
		} else {
			echo _('Doctors Fee') . ':</td><td><input type="text" class="number" size="10" name="DoctorsFee" value="0.00" />';
		}
		if (isset($_POST['AddDoctorFee'])) {
			echo '<input type="checkbox" checked="checked" name="AddDoctorFee" value="Add Doctors fee to balance" onChange="ReloadForm(ChangeItem)" />' . _('Add Doctors fee to balance') . '</td></tr>';
		} else {
			echo '<input type="checkbox" name="AddDoctorFee" value="Add Doctors fee to balance" onChange="ReloadForm(ChangeItem)" />' . _('Add Doctors fee to balance') . '</td></tr>';
		}
	}

	echo '<tr><td>'._('Balance on Account').'</td>';
	echo '<td>'.number_format($Balance, 0).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td></tr>';
	echo '<tr><td>'._('Payment Fee').'</td>';
	echo '<td>'.number_format($_SESSION['Items']['Value'], 0).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td></tr>';
	echo '<input type="hidden" name="Price" value="'.$_SESSION['Items']['Value'].'" />';

	if ($Patient[1]=='CASH') {
		if (!isset($Received)) {
			$Received=$_SESSION['Items']['Value'];
		}
		echo '<tr><td>'._('Amount Received').'</td>';
		if (($Received+$Balance)<0) {
			echo '<td><input type="text" class="number" size="10" name="Received" value="'.locale_money_format(0,$_SESSION['CompanyRecord']['currencydefault']).'" /></td></tr>';
		} else {
			echo '<td><input type="text" class="number" size="10" name="Received" value="'.locale_money_format($Received+$Balance,$_SESSION['CompanyRecord']['currencydefault']).'" /></td></tr>';
		}

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
		echo '<td colspan="2"><input type="text" size="50" name="Comments" value="" /></td></tr>';
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