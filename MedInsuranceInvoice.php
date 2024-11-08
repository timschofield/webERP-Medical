<?php
include ('includes/session.php');
$Title = _('Monthly Insurance Company Billing');
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

if (isset($_POST['Process'])) {
	foreach ($_POST as $key => $value) {
		if (substr($key, 0, 2) == 'ID') {
			if (isset($_POST['include' . substr($key, 2) ])) {
				$InvoiceID[substr($key, 2) ] = $_POST['include' . substr($key, 2) ];
			} else {
				$InvoiceID[substr($key, 2) ] = 'off';
			}
		}
	}

	if (!isset($InvoiceID)) {
		echo '<br />';
		prnMsg(_('There are no invoices for this company in this period'), 'info');
		echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Return to selection screen') . '</a></div>';
		include ('includes/footer.php');
		exit;
	}

	$MainInvoiceNo = GetNextTransNo(10);
	DB_Txn_Begin();
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
											'" . $_POST['Company'] . "',
											'" . $_POST['Company'] . "',
											'" . _('Sales order for insurance company monthly bill') . "',
											'" . Date("Y-m-d") . "',
											'1',
											'" . $_SESSION['UserStockLocation'] . "',
											'" . $_SESSION['UserStockLocation'] . "',
											'" . Date('Y-m-d') . "',
											'" . Date('Y-m-d') . "',
											0
										)";

	$ErrMsg = _('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL, $ErrMsg, '', true);
	$OrderLine = 0;
	foreach ($InvoiceID as $ID => $Post) {

		if ($Post == 'on') {
			// Credit original
			$SQL = "SELECT transno,
					debtortrans.debtorno,
					debtortrans.branchcode,
					trandate,
					order_,
					ovamount,
					invtext,
					reference,
					custbranch.salesman
				FROM debtortrans
				LEFT JOIN custbranch
				ON debtortrans.debtorno=custbranch.debtorno
				AND custbranch.branchcode='" . $_POST['Company'] . "'
				WHERE id='" . $ID . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			$CreditNo = GetNextTransNo(11);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
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
				'" . $CreditNo . "',
				11,
				'" . $MyRow['debtorno'] . "',
				'" . $MyRow['branchcode'] . "',
				'" . $MyRow['trandate'] . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $MyRow['order_'] . "',
				'" . -$MyRow['ovamount'] . "',
				'0',
				'1',
				'" . _('Transfer Invoice number') . ' ' . $MyRow['transno'] . ' ' . _('to') . ' ' . $MyRow['branchcode'] . "',
				'" . $MyRow['reference'] . "',
				'1',
				'" . -$MyRow['ovamount'] . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

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
						'" . $_POST['ID' . $ID] . "',
						 11,
						'" . $CreditNo . "',
						'" . $_SESSION['UserStockLocation'] . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $MyRow['debtorno'] . "',
						'" . $MyRow['branchcode'] . "',
						'" . $PeriodNo . "',
						'" . _('Transfer Invoice number') . ' ' . $MyRow['transno'] . ' ' . _('to') . ' ' . $MyRow['branchcode'] . "',
						1,
						'" . $MyRow['ovamount'] . "',
						1,
						0
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for') . ' ' . $_POST['StockID'] . ' ' . _('could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "UPDATE debtortrans
					SET alloc='" . $MyRow['ovamount'] . "'
					WHERE id='" . $ID . "'";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			//Then add line to Company Invoice
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
													completed,
													patient,
													employer)
												VALUES (
													'" . $OrderLine . "',
													'" . $OrderNo . "',
													'" . $_POST['ID' . $ID] . "',
													'" . $MyRow['ovamount'] . "',
													'1',
													'0',
													'" . _('Sales order for insurance company monthly bill') . "',
													'" . Date('Y-m-d') . "',
													'" . Date('Y-m-d') . "',
													'1',
													1,
													'" . $MyRow['debtorno'] . "',
													'" . $MyRow['salesman'] . "'
												)";
			$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
			$Ins_LineItemResult = DB_query($LineItemSQL, $ErrMsg, $DbgMsg, true);

			if ($OrderLine == 0) {
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
					'" . $MainInvoiceNo . "',
					10,
					'" . $MyRow['branchcode'] . "',
					'" . $MyRow['branchcode'] . "',
					'" . $MyRow['trandate'] . "',
					'" . date('Y-m-d H-i-s') . "',
					'" . $PeriodNo . "',
					'" . $OrderNo . "',
					'" . $MyRow['ovamount'] . "',
					'0',
					'1',
					'" . _('Transfer Invoice number') . ' ' . $MyRow['transno'] . ' ' . _('to') . ' ' . $MyRow['branchcode'] . "',
					'" . $MyRow['reference'] . "',
					'1',
					'0')";
			} else {
				$SQL = "UPDATE debtortrans
								SET ovamount=ovamount+'" . $MyRow['ovamount'] . "'
							WHERE transno='" . $MainInvoiceNo . "'
							AND type=10";
			}

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$OrderLine++;
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
						'" . $_POST['ID' . $ID] . "',
						 10,
						'" . $MainInvoiceNo . "',
						'" . $_SESSION['UserStockLocation'] . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $MyRow['debtorno'] . "',
						'" . $MyRow['branchcode'] . "',
						'" . $PeriodNo . "',
						'" . _('Transfer Invoice number') . ' ' . $MyRow['transno'] . ' ' . _('to') . ' ' . $MyRow['branchcode'] . "',
						-1,
						'" . $MyRow['ovamount'] . "',
						1,
						0
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for') . ' ' . $_POST['StockID'] . ' ' . _('could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
	}
	DB_Txn_Commit();
	prnMsg(_('The insurance company invoice for') . ' ' . $_POST['Company'] . ' ' . _('Has been succesfuly raised'), 'success');
	echo '<br /><div class="centre"><a href="PrintInsuranceInvoice.php?FromTransNo=' . $MainInvoiceNo . '&InvOrCredit=Invoice&PrintPDF=True">' . _('Print the invoice') . '</a></div><br />';
	include ('includes/footer.php');
	exit;
}

if (!isset($_POST['Submit'])) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Select Insurance Company') . '</p>';

	echo '<table class="selection">';
	$SQL = "SELECT typeid FROM debtortype WHERE typename like '%Insurance%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$InsuranceTypeID = $MyRow['typeid'];

	$SQL = "SELECT debtorno,
				name
				FROM debtorsmaster
				LEFT JOIN debtortype
				ON debtortype.typeid=debtorsmaster.typeid
				WHERE typename like '%Insurance%'
				ORDER BY name";
	$ErrMsg = _('The companies could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the companies was');
	$BranchResults = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<tr><td>' . _('Select Company') . ':</td><td><select name="Company">';

	if (DB_num_rows($BranchResults) == 0) {
		echo '</select></td></tr></table><p>';
		include ('includes/footer.php');
		exit;
	} else {
		echo '<option value=""></option>';
		while ($MyRow = DB_fetch_array($BranchResults)) {
			/*list the bank account names */
			if (isset($_POST['Company']) and $_POST['Company'] == $MyRow['debtorno']) {
				echo '<option selected value="' . $MyRow['debtorno'] . '">' . $MyRow['name'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['debtorno'] . '">' . $MyRow['name'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '<tr><td>' . _('Select the month end date') . ':</td><td><select name="Month">';

	$periodno = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$SQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $periodno . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$lastdate_in_period = $MyRow[0];

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if ($MyRow['periodno'] == $periodno) {
			echo '<option selected value=' . $MyRow['periodno'] . '>' . ConvertSQLDate($lastdate_in_period) . '</option>';
		} else {
			echo '<option value=' . $MyRow['periodno'] . '>' . ConvertSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}

	echo '</select></td></tr></table><br />';
	echo '<div class="centre"><button type="submit" name="Submit">' . _('Show Invoices') . '</button></div><br />';
	echo '</form>';
} else {

	if ($_POST['Company'] == '') {
		echo '<br />';
		prnMsg(_('You must select a company from the drop down list'), 'info');
		echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Return to selection screen') . '</a></div>';
		include ('includes/footer.php');
		exit;
	}

	$SQL = "SELECT debtorno
			FROM debtorsmaster
			WHERE debtorno='" . $_POST['Company'] . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg($_POST['Company'] . ' ' . _('is not set up as a customer in webERP and so an invoice cannot be created for them'), 'warn');
		include ('includes/footer.php');
		exit;
	}

	$SQL = "SELECT debtortrans.id,
				debtortrans.debtorno,
				stockmoves.stockid,
				stockmaster.description,
				debtorsmaster.name,
				debtortrans.trandate,
				debtortrans.invtext,
				-stockmoves.qty*stockmoves.price as ovamount,
				debtortrans.reference
			FROM debtortrans
			LEFT JOIN stockmoves
			ON debtortrans.type=stockmoves.type
				AND debtortrans.transno=stockmoves.transno
			LEFT JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			LEFT JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			WHERE debtortrans.branchcode='" . $_POST['Company'] . "'
			AND debtortrans.prd='" . $_POST['Month'] . "'
			AND debtortrans.alloc=0
			AND debtortrans.debtorno<>'" . $_POST['Company'] . "'";

	$Result = DB_query($SQL);
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Invoices for transfer to') . ' ' . $_POST['Company'] . '</p>';

	echo '<table class="selection">';
	echo '<tr><th>' . _('Patient ID') . '</th>';
	echo '<th>' . _('Patient Name') . '</th>';
	echo '<th>' . _('Form Number') . '</th>';
	echo '<th>' . _('Treatment Date') . '</th>';
	echo '<th>' . _('Item Code') . '</th>';
	echo '<th>' . _('Item') . '</th>';
	echo '<th>' . _('Comments') . '</th>';
	echo '<th>' . _('Amount') . '</th>';
	echo '<th>' . _('Include') . '</th></tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr><td>' . $MyRow['debtorno'] . '</td>';
		echo '<td>' . $MyRow['name'] . '</td>';
		echo '<td>' . $MyRow['reference'] . '</td>';
		echo '<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>';
		echo '<td>' . $MyRow['stockid'] . '</td>';
		echo '<td>' . $MyRow['description'] . '</td>';
		echo '<td>' . $MyRow['invtext'] . '</td>';
		echo '<td class="number">' . number_format($MyRow['ovamount'], 0) . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>';
		echo '<td><input type="checkbox" name="include' . $MyRow['id'] . '" checked="True" /></td></tr>';
		echo '<input type="hidden" name="ID' . $MyRow['id'] . '" value="' . $MyRow['stockid'] . '" />';
	}
	echo '<input type="hidden" name="Company" value="' . $_POST['Company'] . '" />';
	echo '</table><br />';
	echo '<div class="centre"><button type="submit" name="Process">' . _('Process Invoice') . '</button></div><br />';
	echo '</form>';
}

include ('includes/footer.php');

?>