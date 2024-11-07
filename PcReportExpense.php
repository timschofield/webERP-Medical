<?php

include ('includes/session.php');
use Dompdf\Dompdf;
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};
$Title = _('Petty Cash Expense Management Report');
/* webERP manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'PcReportExpense';

include ('includes/SQL_CommonFunctions.inc');

if (isset($_POST['SelectedExpense'])){
	$SelectedExpense = mb_strtoupper($_POST['SelectedExpense']);
} elseif (isset($_GET['SelectedExpense'])){
	$SelectedExpense = mb_strtoupper($_GET['SelectedExpense']);
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}
	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP http://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . _('Expense Code') . ': ' . _('Expense Code') . '<br />
					' . _('Date Range') . ': ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '<br />
				</div>
				<table>';

	$SQL = "SELECT pcashdetails.counterindex,
					pcashdetails.tabcode,
					pcashdetails.tag,
					pcashdetails.date,
					pcashdetails.codeexpense,
					pcashdetails.amount,
					pcashdetails.authorized,
					pcashdetails.posted,
					pcashdetails.purpose,
					pcashdetails.notes,
					pctabs.currency,
					currencies.decimalplaces
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND pcashdetails.codeexpense='".$SelectedExpense."'
				AND pcashdetails.date >='" . $SQL_FromDate . "'
				AND pcashdetails.date <= '" . $SQL_ToDate . "'
				AND (pctabs.authorizer='" . $_SESSION['UserID'] .
					"' OR pctabs.usercode ='" . $_SESSION['UserID'].
					"' OR pctabs.assigner ='" . $_SESSION['UserID'] . "')
			ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";

	$Result = DB_query($SQL,
						_('No Petty Cash movements for this expense code were returned by the SQL because'),
						_('The SQL that failed was:'));

	$HTML .= '<table class="selection">
			<thead>
				<tr>
					<th class="ascending">' . _('Date of Expense') . '</th>
					<th class="ascending">' . _('Tab') . '</th>
					<th>' . _('Currency') . '</th>
					<th class="ascending">' . _('Gross Amount') . '</th>
					<th>' . _('Tax') . '</th>
					<th>' . _('Tax Group') . '</th>
					<th>' . _('Tag') . '</th>
					<th>' . _('Business Purpose') . '</th>
					<th>' . _('Notes') . '</th>
					<th>' . _('Receipt Attachment') . '</th>
					<th class="ascending">' . _('Date Authorised') . '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		$TaxesDescription = '';
		$TaxesTaxAmount = '';
		$TaxSQL = "SELECT counterindex,
							pccashdetail,
							calculationorder,
							description,
							taxauthid,
							purchtaxglaccount,
							taxontax,
							taxrate,
							amount
						FROM pcashdetailtaxes
						WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$TaxResult = DB_query($TaxSQL);
		while ($MyTaxRow = DB_fetch_array($TaxResult)) {
			$TaxesDescription .= $MyTaxRow['description'] . '<br />';
			$TaxesTaxAmount .= locale_number_format($MyTaxRow['amount'], $CurrDecimalPlaces) . '<br />';
		}
		$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
		$TagResult = DB_query($TagSQL);
		$TagRow = DB_fetch_array($TagResult);
		if ($MyRow['tag'] == 0) {
			$TagRow['tagdescription'] = _('None');
		}
		$TagTo = $MyRow['tag'];
		$TagDescription = $TagTo . ' - ' . $TagRow['tagdescription'];

		//Generate download link for expense receipt, or show text if no receipt file is found.
		$ReceiptSupportedExt = array('png','jpg','jpeg','pdf','doc','docx','xls','xlsx'); //Supported file extensions
		$ReceiptDir = $PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/expenses_receipts/'; //Receipts upload directory
		$ReceiptSQL = "SELECT hashfile,
								extension
								FROM pcreceipts
								WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$ReceiptResult = DB_query($ReceiptSQL);
		$ReceiptRow = DB_fetch_array($ReceiptResult);
		if (DB_num_rows($ReceiptResult) > 0) { //If receipt exists in database
			$ReceiptHash = $ReceiptRow['hashfile'];
			$ReceiptExt = $ReceiptRow['extension'];
			$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt;
			$ReceiptPath = $ReceiptDir . $ReceiptFileName;
			$ReceiptText = '<a href="' . $ReceiptPath . '" download="ExpenseReceipt-' . mb_strtolower($SelectedTabs) . '-[' . $MyRow['date'] . ']-[' . $MyRow['counterindex'] . ']">' . _('Download attachment') . '</a>';
		} else {
			$ReceiptText = _('No attachment');
		}

		if ($MyRow['authorized'] == '0000-00-00') {
			$AuthorisedDate = _('Unauthorised');
		} else {
			$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
		}

		$HTML .= '<tr class="striped_row">
			<td>' . ConvertSQLDate($MyRow['date']) . '</td>
			<td>' . $MyRow['tabcode'] . '</td>
			<td>' . $MyRow['currency'] . '</td>
			<td class="number">' . locale_number_format($MyRow['amount'], $CurrDecimalPlaces) . '</td>
			<td class="number">' . $TaxesTaxAmount . '</td>
			<td>'. $TaxesDescription . '</td>
			<td>'. $TagDescription . '</td>
			<td>'. $MyRow['purpose'] . '</td>
			<td>'. $MyRow['notes'] . '</td>
			<td>'. $ReceiptText . '</td>
			<td>'. $AuthorisedDate . '</td>
		</tr>';
	} //end of looping


	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('Petty Cash Expense Management Report');
		include ('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('PC Expense Report'). '" alt="" />' . ' ' . $Title . '
			</p>';
		echo $HTML;
		include ('includes/footer.php');
	}
} else {
	include  ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('PC Expense Report'). '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (!isset($_POST['FromDate'])){
		$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}

	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	/*Show a form to allow input of criteria for Expenses to show */
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="SelectedExpense">' . _('Expense Code') . ':</label>
			<select name="SelectedExpense">';

	$SQL = "SELECT DISTINCT(pctabexpenses.codeexpense)
			FROM pctabs, pctabexpenses
			WHERE pctabexpenses.typetabcode = pctabs.typetabcode
				AND ( pctabs.authorizer='" . $_SESSION['UserID'] .
					"' OR pctabs.usercode ='" . $_SESSION['UserID'].
					"' OR pctabs.assigner ='" . $_SESSION['UserID'] . "' )
			ORDER BY pctabexpenses.codeexpense";

	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense']==$_POST['SelectedExpense']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['codeexpense'] . '">' . $MyRow['codeexpense'] . '</option>';

	} //end while loop get type of tab

	DB_free_result($Result);


	echo '</select>
		</field>
		<field>
			<label for="FromDate">' . _('From Date') . ':</label>
			<input tabindex="2" type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
		</field>
		<field>
			<label for="ToDate">' . _('To Date') . ':' . '</label>
			<input tabindex="3" type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . _('Show HTML') . '" />
		</div>
	</form>';

}

include('includes/footer.php');

?>