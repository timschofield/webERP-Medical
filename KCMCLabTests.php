<?php
$PageSecurity=1;
include('includes/session.inc');
$title = _('Billing For Laboratory Tests');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');

if (isset($_GET['New']) or isset($_POST['Cancel'])) {
	unset($_POST['SubmitCash']);
	unset($_POST['Patient']);
	unset($_SESSION['Items']);
	$_SESSION['Items']['Lines']=0;
	$_SESSION['Items']['Value']=0;
}

if (isset($_POST['ChangeItem'])) {
	$sql="SELECT price
				FROM prices
				WHERE stockid='".$_POST['StockID']."'
				AND typeabbrev='".$_POST['PriceList']."'
				AND '".date('Y-m-d')."' between startdate and enddate";
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
											'" . Date("Y-m-d") . "',
											'1',
											'" . $_SESSION['UserStockLocation'] . "',
											'" . $_SESSION['UserStockLocation'] ."',
											'" . Date('Y-m-d') . "',
											'" . Date('Y-m-d') . "',
											0
										)";

		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

		for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
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
													'" . _('Sales order for laboratory test transaction') . "',
													'" . Date('Y-m-d') . "',
													'" . Date('Y-m-d') . "',
													'1',
													1
												)";
			$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
			$Ins_LineItemResult = DB_query($LineItemSQL,$db,$ErrMsg,$DbgMsg,true);
		}
		$InvoiceNo = GetNextTransNo(10, $db);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
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
				'" . date('Y-m-d H-i-s') . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $OrderNo . "',
				'" . $_SESSION['Items']['Value'] . "',
				'0',
				'1',
				'" . _('Invoice for Laboratory test of Patient number').' '.$_POST['PatientNo'] . "',
				'" . $_POST['InsuranceRef'] . "',
				'1',
				'" . $_POST['Receipt'] . "')";

		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
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
						'" . date('Y-m-d H-i-s') . "',
						'" . $_POST['PatientNo'] . "',
						'" . $_POST['BranchNo'] . "',
						'" . $PeriodNo . "',
						'" . _('Invoice for Laboratory test of Patient number').' '.$_POST['PatientNo'] . "',
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
									'" . date('Y-m-d') . "',
									'" . $PeriodNo . "',
									'" . $SalesGLAccounts['salesglcode'] . "',
									'" . $_SESSION['DefaultTag'] . "',
									'" . _('Invoice for Laboratory test of Patient number').' '.$_POST['PatientNo'] . "',
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
									'" . date('Y-m-d') . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
									'" . $_SESSION['DefaultTag'] . "',
									'" . _('Invoice for Laboratory test of Patient number').' '.$_POST['PatientNo'] . "',
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
										'" . date('Y-m-d H-i-s') . "',
										'" . $PeriodNo . "',
										'" . $_POST['BankAccount'] . "',
										'" . $_SESSION['DefaultTag'] . "',
										'" . _('Payment of Laboratory test for Patient number').' '.$_POST['PatientNo'] . "',
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
										'" . date('Y-m-d H-i-s') . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
										'" . $_SESSION['DefaultTag'] . "',
										'" . _('Payment of Laboratory test for Patient number').' '.$_POST['PatientNo'] . "',
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
									currcode)
								VALUES (12,
									'" . $ReceiptNumber . "',
									'" . $_POST['BankAccount'] . "',
									'" . _('Payment of Laboratory test for Patient number').' '.$_POST['PatientNo'] . "',
									'1',
									'1',
									'" . date('Y-m-d H-i-s') . "',
									'2',
									'" . ($_SESSION['Items']['Value']) . "',
			'" . $_SESSION['CompanyRecord']['currencydefault'] . "')";

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
											'" . date('Y-m-d H-i-s') . "',
											'" . date('Y-m-d H-i-s') . "',
											'" . $PeriodNo . "',
											'" . $InvoiceNo . "',
											'1',
											'" . -$_SESSION['Items']['Value'] . "',
											'" . -$_POST['Receipt'] . "',
											'" . _('Payment of Laboratory test for Patient number').' '.$_POST['PatientNo'] . "'
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

if (!isset($_POST['Patient'])) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Patients').'</p>';
	echo '<table cellpadding=3 colspan=4 class=selection>';
	echo '<tr><td colspan=2>' . _('Enter a partial Name') . ':</td><td>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="Text" name="Keywords" value="' . $_POST['Keywords'] . '" size=20 maxlength=25>';
	} else {
		echo '<input type="Text" name="Keywords" size=20 maxlength=25>';
	}
	echo '</td><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Code') . ':</td><td>';
	if (isset($_POST['CustCode'])) {
		echo '<input type="Text" name="CustCode" value="' . $_POST['CustCode'] . '" size=15 maxlength=18>';
	} else {
		echo '<input type="Text" name="CustCode" size=15 maxlength=18>';
	}
	echo '</td></tr><tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Phone Number') . ':</td><td>';
	if (isset($_POST['CustPhone'])) {
		echo '<input type="Text" name="CustPhone" value="' . $_POST['CustPhone'] . '" size=15 maxlength=18>';
	} else {
		echo '<input type="Text" name="CustPhone" size=15 maxlength=18>';
	}
	echo '</td>';
	echo '<td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter part of the Address') . ':</td><td>';
	if (isset($_POST['CustAdd'])) {
		echo '<input type="Text" name="CustAdd" value="' . $_POST['CustAdd'] . '" size=20 maxlength=25>';
	} else {
		echo '<input type="Text" name="CustAdd" size=20 maxlength=25>';
	}
	echo '</td></tr>';

	echo '</td></tr></table><br />';
	echo '<div class="centre"><input type=submit name="Search" value="' . _('Search Now') . '"></div></form>';
}

if (isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND (($_POST['CustCode']) OR ($_POST['CustPhone']))) {
		$msg = _('Search Result: Customer Name has been used in search') . '<br>';
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
	}
	if ($_POST['CustCode'] AND $_POST['CustPhone'] == "" AND isset($_POST['CustType']) AND $_POST['Keywords'] == "") {
		$msg = _('Search Result: Customer Code has been used in search') . '<br>';
	}
	if (($_POST['CustPhone'])) {
		$msg = _('Search Result: Customer Phone has been used in search') . '<br>';
	}
	if (($_POST['CustAdd'])) {
		$msg = _('Search Result: Customer Address has been used in search') . '<br>';
	}
	if ($_POST['CustPhone'] == "" AND $_POST['CustCode'] == "" AND $_POST['Keywords'] == "" AND $_POST['CustAdd'] == "") {
		$msg = _('Search Result: Customer Type has been used in search') . '<br>';
	}
	if (($_POST['Keywords'] == "") AND ($_POST['CustCode'] == "") AND ($_POST['CustPhone'] == "") AND ($_POST['CustAdd'] == "")) {
		$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
								debtorsmaster.address2,
								debtorsmaster.address3,
								debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.typeid = debtortype.typeid";
	} else {
		if (strlen($_POST['Keywords']) > 0) {
			//using the customer name
			$_POST['Keywords'] = strtoupper(trim($_POST['Keywords']));
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.name " . LIKE . " '$SearchString'
			AND debtorsmaster.typeid = debtortype.typeid";
		} elseif (strlen($_POST['CustCode']) > 0) {
			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid";
		} elseif (strlen($_POST['CustPhone']) > 0) {
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid";
			// Added an option to search by address. I tried having it search address1, address2, address3, and address4, but my knowledge of MYSQL is limited.  This will work okay if you select the CSV Format then you can search though the address1 field. I would like to extend this to all 4 address fields. Gilles Deacur

		} elseif (strlen($_POST['CustAdd']) > 0) {
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE CONCAT_WS(debtorsmaster.address1,debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4) " . LIKE . " '%" . $_POST['CustAdd'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid";
			// End added search feature. Gilles Deacur

		}
	} //one of keywords or custcode or custphone was more than a zero length string
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL.= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL.= ' ORDER BY debtorsmaster.name';
	$ErrMsg = _('The searched patient records requested cannot be retrieved because');

	$result = DB_query($SQL, $db, $ErrMsg);
	if (DB_num_rows($result) == 0) {
		prnMsg(_('No patient records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search

if (isset($result)) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
	if ($ListPageMax > 1) {
		echo '<p><div class=centre>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset1">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
			} else {
				echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
				<input type=submit name="Go1" value="' . _('Go') . '">
				<input type=submit name="Previous" value="' . _('Previous') . '">
				<input type=submit name="Next" value="' . _('Next') . '">';
		echo '</div>';
	}
	echo '<br /><table cellpadding=2 colspan=7 class=selection>';
	$TableHeader = '<tr>
				<th>' . _('Code') . '</th>
				<th>' . _('Patient Name') . '</th>
				<th>' . _('Phone') . '</th>
			</tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($result) <> 0) {
		if (!isset($_POST['CSV'])) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td><font size=1><input type=submit name="Patient" value="' . $myrow['debtorno'].' '.$myrow['branchcode'] . '"></font></td>
				<td><font size=1>' . $myrow['name'] . '</font></td>
				<td><font size=1>' . $myrow['phoneno'] . '</font></td></tr>';
			$j++;
			if ($j == 11 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $TableHeader;
			}
			$RowIndex++;
			//end of page full new headings if

		}
		//end of while loop
		echo '</table></form>';
	}
}

if (isset($_POST['Patient'])) {
	$Patient=explode(' ', $_POST['Patient']);
	$sql="SELECT name,
				clientsince,
				salestype
				FROM debtorsmaster
				WHERE debtorno='".$Patient[0]."'";
	$result=DB_query($sql, $db);
	$mydebtorrow=DB_fetch_array($result);
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="'
		. _('Search') . '" alt="" />' . ' ' . $mydebtorrow['name']. ' - '.$Patient[1].'</p>';

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="PriceList" value="'.$mydebtorrow['salestype'].'" />';
	echo '<input type="hidden" name="Patient" value="'.$_POST['Patient'].'" />';
	echo '<input type="hidden" name="PatientNo" value="'.$Patient[0].'" />';
	echo '<input type="hidden" name="BranchNo" value="'.$Patient[1].'" />';
	echo '<table class="selection">';
	echo '<tr><th colspan="2"><font size="3" color="navy">'._('Patient ID').' - '.$Patient[0].'</font></th></tr>';
	echo '<tr><td>'._('Date of Admission').':</td>
		<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="AdmissionDate" maxlength="10" size="11" value="' .
					 date($_SESSION['DefaultDateFormat']) . '" /></td></tr>';
	echo '<tr><td>'._('Type of Laboratory test').':</td>';
	$sql="SELECT stockid,
				description
			FROM stockmaster
			LEFT JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='T'";

	$result=DB_query($sql, $db);
	if (isset($_POST['StockID'])) {
		$StockID=$_POST['StockID'];
	} else {
		$StockID='';
	}

	for ($i=0; $i<$_SESSION['Items']['Lines']; $i++) {
		echo '<td><select name="StockID" onChange="ReloadForm(ChangeItem)">';
		echo '<option value=""></option>';
		while ($myrow=DB_fetch_array($result)) {
			if ($myrow['stockid']==$_SESSION['Items'][$i]['StockID']) {
				echo '<option selected value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
			} else {
				echo '<option value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
			}
		}
		echo '</select>&nbsp;@&nbsp;'.number_format($_SESSION['Items'][$i]['Price'],0).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td></tr>';
		DB_data_seek($result,0);
		echo '<tr><td>';
	}
	echo '<td><select name="StockID" onChange="ReloadForm(ChangeItem)">';
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($result)) {
		echo '<option value="'.$myrow['stockid'].'">'.$myrow['stockid']. ' - ' . $myrow['description'].'</option>';
	}
	echo '</select></td></tr>';
	DB_data_seek($result,0);

	echo '<input type="submit" name="ChangeItem" style="visibility: hidden" value=" " />';
	echo '<tr><td>'._('Payment Fee').'</td>';
	echo '<td>'.number_format($_SESSION['Items']['Value'], 0).' '.$_SESSION['CompanyRecord']['currencydefault'].'</td></tr>';
	echo '<input type="hidden" name="Price" value="'.$_SESSION['Items']['Value'].'" />';

	if ($Patient[1]=='CASH') {
		if (!isset($Received)) {
			$Received=$_SESSION['Items']['Value'];
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
		echo '</table><br />';
		echo '<div class="centre"><input type="submit" name="SubmitCash" value="Make Payment" /></div>';
	} else {
		echo '<tr><td>'._('Insurance Reference').'</td>';
		echo '<td><input type="text" size="10" name="InsuranceRef" value="" /></td></tr>';
		echo '<tr><td>'._('Comments').'</td>';
		echo '<td><input type="text" size="50" name="Comments" value="" /></td></tr>';
		echo '</table><br />';
		echo '<div class="centre"><input type="submit" name="SubmitInsurance" value="Process Invoice" /></div>';
	}
	echo '<br /><div class="centre"><input type="submit" name="Cancel" value="Cancel This Payment" /></div>';

	echo '</form>';
}

include('includes/footer.inc');
?>