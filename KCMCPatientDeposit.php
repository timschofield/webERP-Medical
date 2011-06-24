<?php
$PageSecurity=1;
include('includes/session.inc');
$title = _('In Patient Deposit Payments');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');

if (isset($_GET['New']) or isset($_POST['Cancel'])) {
	unset($_POST['SubmitCash']);
	unset($_POST['Patient']);
}

if (isset($_POST['SubmitCash'])) {

	$InputError=0;

	if ((!isset($_POST['BankAccount']) or $_POST['BankAccount']=='') and !isset($_POST['SubmitInsurance'])) {
		$InputError=1;
		$msg[]=_('You must select a cash collection point');
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

		if (isset($_POST['SubmitCash'])) {
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
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
										'" . date('Y-m-d H-i-s') . "',
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
									currcode)
								VALUES (12,
									'" . $ReceiptNumber . "',
									'" . $_POST['BankAccount'] . "',
									'" . _('Deposit for Patient number').' '.$_POST['PatientNo'] . "',
									'1',
									'1',
									'" . date('Y-m-d H-i-s') . "',
									'2',
									'" . ($_POST['Received']) . "',
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
			echo '<meta http-equiv="Refresh" content="0; url='.$rootpath.'/PDFReceipt.php?FromTransNo='.$ReceiptNumber.'&amp;InvOrCredit=Receipt&amp;PrintPDF=True">';
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
			WHERE debtorsmaster.typeid = debtortype.typeid
				AND custbranch.branchcode='CASH'";
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
			AND debtorsmaster.typeid = debtortype.typeid
			AND custbranch.branchcode='CASH'";
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
			AND debtorsmaster.typeid = debtortype.typeid
			AND custbranch.branchcode='CASH'";
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
			AND debtorsmaster.typeid = debtortype.typeid
			AND custbranch.branchcode='CASH'";
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
			AND debtorsmaster.typeid = debtortype.typeid
			AND custbranch.branchcode='CASH'";
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
	echo '<input type="hidden" name="Patient" value="'.$_POST['Patient'].'" />';
	echo '<input type="hidden" name="PatientNo" value="'.$Patient[0].'" />';
	echo '<input type="hidden" name="BranchNo" value="'.$Patient[1].'" />';
	echo '<table class="selection">';
	echo '<tr><th colspan="2"><font size="3" color="navy">'._('Patient ID').' - '.$Patient[0].'</font></th></tr>';
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
		echo '</table><br />';
		echo '<div class="centre"><input type="submit" name="SubmitCash" value="Process Deposit" /></div>';
	}
	echo '<br /><div class="centre"><input type="submit" name="Cancel" value="Cancel This Payment" /></div>';

	echo '</form>';
}

include('includes/footer.inc');
?>