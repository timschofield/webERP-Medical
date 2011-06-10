<?php
$PageSecurity=1;
include('includes/session.inc');
$title = _('Monthly Insurance Company Billing');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['Process'])) {
	foreach ($_POST as $key => $value) {
		if (substr($key, 0, 2)=='ID') {
			if (isset($_POST['include'.substr($key, 2)])) {
				$InvoiceID[substr($key, 2)]=$_POST['include'.substr($key, 2)];
			} else {
				$InvoiceID[substr($key, 2)]='off';
			}
		}
	}

	if (!isset($InvoiceID)) {
		echo '<br />';
		prnMsg( _('There are no invoices for this company in this period'), 'info');
		echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Return to selection screen') . '</a></div>';
		include('includes/footer.inc');
		exit;
	}

	$MainInvoiceNo = GetNextTransNo(10, $db);
	DB_Txn_Begin($db);
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
											'" . $_POST['Company'] . "',
											'" . $_POST['Company'] . "',
											'" . _('Sales order for insurance company monthly bill') . "',
											'" . Date("Y-m-d") . "',
											'1',
											'" . $_SESSION['UserStockLocation'] . "',
											'" . $_SESSION['UserStockLocation'] ."',
											'" . Date('Y-m-d') . "',
											'" . Date('Y-m-d') . "',
											0
										)";

	$ErrMsg = _('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg,'',true);
	$OrderLine=0;
	foreach ($InvoiceID as $ID => $Post) {

		if ($Post=='on') {
			// Credit original
			$sql = "SELECT transno,
					debtorno,
					branchcode,
					trandate,
					order_,
					ovamount,
					invtext,
					reference
				FROM debtortrans
				WHERE id='".$ID."'";
			$result=DB_query($sql, $db);
			$myrow=DB_fetch_array($result);

			$CreditNo = GetNextTransNo(11, $db);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
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
				'". $CreditNo . "',
				11,
				'" . $myrow['debtorno'] . "',
				'" . $myrow['branchcode'] . "',
				'" . $myrow['trandate'] . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $myrow['order_'] . "',
				'" . -$myrow['ovamount'] . "',
				'0',
				'1',
				'" . _('Transfer Invoice number').' '.$myrow['transno'] . ' ' . _('to').' '.$myrow['branchcode']. "',
				'" . $myrow['reference'] . "',
				'1',
				'" . -$myrow['ovamount'] . "')";

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
						'" . $_POST['ID'.$ID] . "',
						 11,
						'" . $CreditNo . "',
						'" . $_SESSION['UserStockLocation'] . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $myrow['debtorno'] . "',
						'" . $myrow['branchcode'] . "',
						'" . $PeriodNo . "',
						'" . _('Transfer Invoice number').' '.$myrow['transno'] . ' ' . _('to').' '.$myrow['branchcode']. "',
						1,
						'" . $myrow['ovamount'] . "',
						1,
						0
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for'). ' '. $_POST['StockID'] . ' ' .
				_('could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$sql="UPDATE debtortrans
					SET alloc='".$myrow['ovamount']."'
					WHERE id='".$ID."'";
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

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
													completed)
												VALUES (
													'" . $OrderLine."',
													'" . $OrderNo . "',
													'" . $_POST['ID'.$ID] . "',
													'" . $myrow['ovamount'] . "',
													'1',
													'0',
													'" . _('Sales order for insurance company monthly bill') . "',
													'" . Date('Y-m-d') . "',
													'" . Date('Y-m-d') . "',
													'1',
													1
												)";
			$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
			$Ins_LineItemResult = DB_query($LineItemSQL,$db,$ErrMsg,$DbgMsg,true);

			if ($OrderLine==0) {
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
					'". $MainInvoiceNo . "',
					10,
					'" . $myrow['branchcode'] . "',
					'" . $myrow['branchcode'] . "',
					'" . $myrow['trandate'] . "',
					'" . date('Y-m-d H-i-s') . "',
					'" . $PeriodNo . "',
					'" . $OrderNo . "',
					'" . $myrow['ovamount'] . "',
					'0',
					'1',
					'" . _('Transfer Invoice number').' '.$myrow['transno'] . ' ' . _('to').' '.$myrow['branchcode']. "',
					'" . $myrow['reference'] . "',
					'1',
					'0')";
				} else {
					$sql = "UPDATE debtortrans
								SET ovamount=ovamount+'".$myrow['ovamount']."'
							WHERE transno='".  $MainInvoiceNo . "'
							AND type=10";
			}

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

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
						'" . $_POST['ID'.$ID] . "',
						 10,
						'" . $MainInvoiceNo . "',
						'" . $_SESSION['UserStockLocation'] . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $myrow['debtorno'] . "',
						'" . $myrow['branchcode'] . "',
						'" . $PeriodNo . "',
						'" . _('Transfer Invoice number').' '.$myrow['transno'] . ' ' . _('to').' '.$myrow['branchcode']. "',
						-1,
						'" . $myrow['ovamount'] . "',
						1,
						0
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for'). ' '. $_POST['StockID'] . ' ' .
				_('could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
	}
	DB_Txn_Commit($db);
}

if (!isset($_POST['Submit'])) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' .
		_('Search') . '" alt="" />' . ' ' . _('Select Insurance Company').'</p>';

	echo '<table class="selection">';
	$sql="SELECT distinct
				branchcode,
				brname
			FROM custbranch
			WHERE branchcode<>'CASH'";
	$ErrMsg = _('The companies could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the companies was');
	$BranchResults = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo '<tr><td>' . _('Select Company') . ':</td><td><select name="Company">';

	if (DB_num_rows($BranchResults)==0){
		echo '</select></td></tr></table><p>';
		include('includes/footer.inc');
		exit;
	} else {
		echo '<option value=""></option>';
		while ($myrow=DB_fetch_array($BranchResults)){
		/*list the bank account names */
			if (isset($_POST['Company']) and $_POST['Company']==$myrow['branchcode']){
				echo '<option selected value="' . $myrow['branchcode'] . '">' . $myrow['brname'] . '</option>';
			} else {
				echo '<option value="' . $myrow['branchcode'] . '">' . $myrow['brname'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '<tr><td>'._('Select the month end date').':</td><td><select name="Month">';

	$periodno=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='".$periodno . "'";
	$result = DB_query($sql,$db);
	$myrow=DB_fetch_array($result, $db);
	$lastdate_in_period=$myrow[0];

	$sql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($Periods,$db)){
		if( $myrow['periodno']== $periodno){
			echo '<option selected value=' . $myrow['periodno'] . '>' . ConvertSQLDate($lastdate_in_period) . '</option>';
		} else {
			echo '<option value=' . $myrow['periodno'] . '>' . ConvertSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
	}

	echo '</select></td></tr></table><br />';
	echo '<div class="centre"><input type="submit" name="Submit" value="' . _('Show Invoices').'" /></div>';
	echo '</form>';
} else {

	if ($_POST['Company']=='') {
		echo '<br />';
		prnMsg( _('You must select a company from the drop down list'), 'info');
		echo '<br /><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Return to selection screen') . '</a></div>';
		include('includes/footer.inc');
		exit;
	}

	$sql="SELECT debtorno
			FROM debtorsmaster
			WHERE debtorno='".$_POST['Company']."'";
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)==0) {
		prnMsg($_POST['Company'].' '. _('is not set up as a customer in webERP and so an invoice cannot be created for them'), 'warn');
		include('includes/footer.inc');
		exit;
	}

	$sql="SELECT debtortrans.id,
				debtortrans.debtorno,
				stockmoves.stockid,
				stockmaster.description,
				debtorsmaster.name,
				debtortrans.trandate,
				debtortrans.invtext,
				debtortrans.ovamount,
				debtortrans.reference
			FROM debtortrans
			LEFT JOIN stockmoves
			ON debtortrans.type=stockmoves.type
				AND debtortrans.transno=stockmoves.transno
			LEFT JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			LEFT JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			WHERE debtortrans.branchcode='".$_POST['Company']."'
			AND debtortrans.prd='".$_POST['Month']."'
			AND debtortrans.alloc=0
			AND debtortrans.debtorno<>'".$_POST['Company']."'";
	$result=DB_query($sql, $db);
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/transactions.png" title="' .
		_('Search') . '" alt="" />' . ' ' . _('Invoices for transfer to').' '.$_POST['Company'].'</p>';

	echo '<table class="selection">';
	echo '<tr><th>'._('Patient ID').'</th>';
	echo '<th>'._('Patient Name').'</th>';
	echo '<th>'._('Form Number').'</th>';
	echo '<th>'._('Treatment Date').'</th>';
	echo '<th>'._('Item Code').'</th>';
	echo '<th>'._('Item').'</th>';
	echo '<th>'._('Comments').'</th>';
	echo '<th>'._('Amount').'</th>';
	echo '<th>'._('Include').'</th></tr>';

	while ($myrow=DB_fetch_array($result)) {
		echo '<tr><td>' . $myrow['debtorno'] . '</td>';
		echo '<td>' . $myrow['name'] . '</td>';
		echo '<td>' . $myrow['reference'] . '</td>';
		echo '<td>' . ConvertSQLDate($myrow['trandate']) . '</td>';
		echo '<td>' . $myrow['stockid'] . '</td>';
		echo '<td>' . $myrow['description'] . '</td>';
		echo '<td>' . $myrow['invtext'] . '</td>';
		echo '<td class="number">' . number_format($myrow['ovamount'],0) .' '.$_SESSION['CompanyRecord']['currencydefault']. '</td>';
		echo '<td><input type="checkbox" name="include'.$myrow['id'].'" checked="True" /></td></tr>';
		echo '<input type="hidden" name="ID'.$myrow['id'].'" value="'.$myrow['stockid'].'" />';
	}
	echo '<input type="hidden" name="Company" value="'.$_POST['Company'].'" />';
	echo '</table><br />';
	echo '<div class="centre"><input type="submit" name="Process" value="' . _('Process Invoice') . '" /></div>';
	echo '</form>';
}

include('includes/footer.inc');

?>