<?php


include('includes/session.php');
$ViewTopic = 'AccountsPayable';
$BookMark = 'AgedCreditors';

if (isset($_POST['PrintPDF'])
	and isset($_POST['FromCriteria'])
	and mb_strlen($_POST['FromCriteria'])>=1
	and isset($_POST['ToCriteria'])
	and mb_strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Aged Supplier Listing'));
	$pdf->addInfo('Subject',_('Aged Suppliers'));
	$FontSize=12;
	$PageNumber=0;
	$line_height=12;

	  /*Now figure out the aged analysis for the Supplier range under review */

	if ($_POST['All_Or_Overdues']=='All'){
		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						currencies.currency,
						currencies.decimalplaces AS currdecimalplaces,
						paymentterms.terms,
						SUM(supptrans.ovamount + supptrans.ovgst  - supptrans.alloc) as balance,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS due,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue1,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue2
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN currencies
				ON suppliers.currcode = currencies.currabrev
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				WHERE suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
				AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
				AND  suppliers.currcode ='" . $_POST['Currency'] . "'
				GROUP BY suppliers.supplierid,
						suppliers.suppname,
						currencies.currency,
						paymentterms.terms,
						paymentterms.daysbeforedue,
						paymentterms.dayinfollowingmonth
				HAVING ROUND(ABS(SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc)), currencies.decimalplaces) > 0";

	} else {

		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						currencies.currency,
						currencies.decimalplaces AS currdecimalplaces,
						paymentterms.terms,
						SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
							CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS due,
						Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue1,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue2
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN currencies
				ON suppliers.currcode = currencies.currabrev
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				WHERE suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
				AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
				AND suppliers.currcode ='" . $_POST['Currency'] . "'
				GROUP BY suppliers.supplierid,
					suppliers.suppname,
					currencies.currency,
					paymentterms.terms,
					paymentterms.daysbeforedue,
					paymentterms.dayinfollowingmonth
				HAVING SUM(IF (paymentterms.daysbeforedue > 0,
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END,
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END)) > 0";

	}

	$SupplierResult = DB_query($SQL,'','',False,False); /*dont trap errors */

	if (DB_error_no() !=0) {
		$Title = _('Aged Supplier Account Analysis') . ' - ' . _('Problem Report') ;
		include('includes/header.php');
		prnMsg(_('The Supplier details could not be retrieved by the SQL because') .  ' ' . DB_error_msg(),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	include ('includes/PDFAgedSuppliersPageHeader.inc');
	$TotBal = 0;
	$TotDue = 0;
	$TotCurr = 0;
	$TotOD1 = 0;
	$TotOD2 = 0;
	$CurrDecimalPlaces =0;

	$ListCount = DB_num_rows($SupplierResult); // UldisN

	while ($AgedAnalysis = DB_fetch_array($SupplierResult)){

		$CurrDecimalPlaces = $AgedAnalysis['currdecimalplaces'];

		$DisplayDue = locale_number_format($AgedAnalysis['due']-$AgedAnalysis['overdue1'],$CurrDecimalPlaces);
		$DisplayCurrent = locale_number_format($AgedAnalysis['balance']-$AgedAnalysis['due'],$CurrDecimalPlaces);
		$DisplayBalance = locale_number_format($AgedAnalysis['balance'],$CurrDecimalPlaces);
		$DisplayOverdue1 = locale_number_format($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2'],$CurrDecimalPlaces);
		$DisplayOverdue2 = locale_number_format($AgedAnalysis['overdue2'],$CurrDecimalPlaces);

		$TotBal += $AgedAnalysis['balance'];
		$TotDue += ($AgedAnalysis['due']-$AgedAnalysis['overdue1']);
		$TotCurr += ($AgedAnalysis['balance']-$AgedAnalysis['due']);
		$TotOD1 += ($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2']);
		$TotOD2 += $AgedAnalysis['overdue2'];

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$AgedAnalysis['supplierid'] . ' - ' . $AgedAnalysis['suppname'],'left');
		$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
		$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
		$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
		$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

		$YPos -=$line_height;
		if ($YPos < $Bottom_Margin + $line_height){
			  include('includes/PDFAgedSuppliersPageHeader.inc');
		}

		if ($_POST['DetailedReport']=='Yes'){

		   $FontSize=6;
		   /*draw a line under the Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);

		   $sql = "SELECT systypes.typename,
							supptrans.suppreference,
							supptrans.trandate,
							(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) as balance,
							CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							END AS due,
							CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	   AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate), paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							END AS overdue1,
							CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							END AS overdue2
						FROM suppliers
						LEFT JOIN paymentterms
							ON suppliers.paymentterms = paymentterms.termsindicator
						LEFT JOIN supptrans
							ON suppliers.supplierid = supptrans.supplierno
						LEFT JOIN systypes
							ON systypes.typeid = supptrans.type
						WHERE ABS(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) >0.009
							AND supptrans.settled = 0
							AND supptrans.supplierno = '" . $AgedAnalysis["supplierid"] . "'";

			$DetailResult = DB_query($sql,'','',False,False); /*dont trap errors - trapped below*/
			if (DB_error_no() !=0) {
			$Title = _('Aged Supplier Account Analysis - Problem Report');
			include('includes/header.php');
			prnMsg(_('The details of outstanding transactions for Supplier') . ' - ' . $AgedAnalysis['supplierid'] . ' ' . _('could not be retrieved because') . ' - ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
			   echo '<br />' . _('The SQL that failed was') . '<br />' . $sql;
			}
			include('includes/footer.php');
			exit;
			}

			while ($DetailTrans = DB_fetch_array($DetailResult)){

				$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,60,$FontSize,$DetailTrans['typename'],'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,50,$FontSize,$DetailTrans['suppreference'],'left');
				$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+105,$YPos,70,$FontSize,$DisplayTranDate,'left');

				$DisplayDue = locale_number_format($DetailTrans['due']-$DetailTrans['overdue1'],$CurrDecimalPlaces);
				$DisplayCurrent = locale_number_format($DetailTrans['balance']-$DetailTrans['due'],$CurrDecimalPlaces);
				$DisplayBalance = locale_number_format($DetailTrans['balance'],$CurrDecimalPlaces);
				$DisplayOverdue1 = locale_number_format($DetailTrans['overdue1']-$DetailTrans['overdue2'],$CurrDecimalPlaces);
				$DisplayOverdue2 = locale_number_format($DetailTrans['overdue2'],$CurrDecimalPlaces);

				$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
				$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
				$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
				$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
				$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

				$YPos -=$line_height;
				if ($YPos < $Bottom_Margin + $line_height){
				$PageNumber++;
				include('includes/PDFAgedSuppliersPageHeader.inc');
				$FontSize=6;
				}
			} /*end while there are detail transactions to show */
			/*draw a line under the detailed transactions before the next Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);
		   $FontSize=8;
		} /*Its a detailed report */
	} /*end Supplier aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include('includes/PDFAgedSuppliersPageHeader.inc');
	} elseif ($_POST['DetailedReport']=='Yes') {
		//dont do a line if the totals have to go on a new page
		$pdf->line($Page_Width-$Right_Margin, $YPos+10 ,220, $YPos+10);
	}

	$DisplayTotBalance = locale_number_format($TotBal,$CurrDecimalPlaces);
	$DisplayTotDue = locale_number_format($TotDue,$CurrDecimalPlaces);
	$DisplayTotCurrent = locale_number_format($TotCurr,$CurrDecimalPlaces);
	$DisplayTotOverdue1 = locale_number_format($TotOD1,$CurrDecimalPlaces);
	$DisplayTotOverdue2 = locale_number_format($TotOD2,$CurrDecimalPlaces);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');
	$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayTotCurrent,'right');
	$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayTotDue,'right');
	$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayTotOverdue1,'right');
	$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayTotOverdue2,'right');

	$YPos -=$line_height;
	$pdf->line($Page_Width-$Right_Margin, $YPos ,220, $YPos);

	if ($ListCount == 0) {
		$Title = _('Aged Supplier Analysis');
		include('includes/header.php');
		prnMsg(_('There are no results so the PDF is empty'));
		include('includes/footer.php');
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_AgedSuppliers_' . date('Y-m-d').'.pdf');
	}
	$pdf->__destruct();
} else { /*The option to print PDF was not hit */

	$Title = _('Aged Supplier Analysis');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	if (!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>', _('Select Report Criteria'), '</legend>';

		echo '<field>
				<label for="FromCriteria">' . _('From Supplier Code') . ':</label>
				<input tabindex="1" type="text" required="required"  autofocus="autofocus" maxlength="6" size="7" name="FromCriteria" value="1" title+"" />
				<fieldhelp>' . _('Enter the first supplier code alphabetially to include in the report') . '</fieldhelp>
			</field>';

		echo '<field>
				<label for="ToCriteria">' . _('To Supplier Code') . ':</label>
				<input tabindex="2" type="text" maxlength="6" size="7" name="ToCriteria" required="required" value="zzzzzz" title="" />
				<fieldhelp>' . _('Enter the last supplier code alphabetically to include in the report') . '</fieldhelp>
			</field>';

		echo '<field>
				<label for="All_Or_Overdues">' . _('All balances or overdues only') . ':' . '</label>
				<select tabindex="3" name="All_Or_Overdues">
					<option selected="selected" value="All">' . _('All suppliers with balances') . '</option>
					<option value="OverduesOnly">' . _('Overdue accounts only') . '</option>
				</select>
				<fieldhelp>', _('Show all account balances, or just show accounts with overdue balances'), '</fieldhelp>
			</field>';

		echo '<field>
				<label for="Currency">' . _('For suppliers trading in') . ':' . '</label>
				<select tabindex="4" name="Currency">';

		$sql = "SELECT currency, currabrev FROM currencies";
		$result=DB_query($sql);

		while ($myrow=DB_fetch_array($result)){
			if ($myrow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']){
				echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			} else {
				echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			}
		}
		echo '</select>
			<fieldhelp>', _('Select the supplier currency, and just show suppliers trading in that currency'), '</fieldhelp>
		</field>';

		echo '<field>
				<label for="DetailedReport">' . _('Summary or Detailed Report') . ':' . '</label>
				<select tabindex="5" name="DetailedReport">
					<option selected="selected" value="No">' . _('Summary Report')  . '</option>
					<option value="Yes">' . _('Detailed Report')  . '</option>
				</select>
				<fieldhelp>', _('The report can be shown as a summary report, or a detailed report'), '</fieldhelp>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input tabindex="6" type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
            </div>
		</form>';
	}
	include('includes/footer.php');
} /*end of else not PrintPDF */

?>
