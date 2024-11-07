<?php
// PcReportTab.php
// .

include ('includes/session.php');
use Dompdf\Dompdf;

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};
$ViewTopic = 'PettyCash';
$BookMark = 'PcReportTab';
$Title = _('Petty Cash Management Report');

include ('includes/SQL_CommonFunctions.inc');

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$SQLFromDate = FormatDateForSQL($_POST['FromDate']);
	$SQLToDate = FormatDateForSQL($_POST['ToDate']);

	$SQLTabs = "SELECT tabcode,
						usercode,
						typetabcode,
						currency,
						tablimit,
						assigner,
						authorizer,
						authorizerexpenses,
						glaccountassignment,
						glaccountpcash,
						defaulttag,
						taxgroupid
			FROM pctabs
			WHERE tabcode = '" . $SelectedTabs . "'";

	$TabResult = DB_query($SQLTabs,
						 _('No Petty Cash Tabs were returned by the SQL because'),
						 _('The SQL that failed was:'));

	$Tabs = DB_fetch_array($TabResult);

	$SQLDecimalPlaces = "SELECT decimalplaces
					FROM currencies,pctabs
					WHERE currencies.currabrev = pctabs.currency
						AND tabcode='" . $SelectedTabs . "'";
	$Result = DB_query($SQLDecimalPlaces);
	$MyRow = DB_fetch_array($Result);
	$CurrDecimalPlaces = $MyRow['decimalplaces'];


	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$CurrencySQL = "SELECT currency FROM currencies WHERE currabrev='" . $Tabs['currency'] . "'";
	$CurrencyResult = DB_query($CurrencySQL);
	$CurrencyRow = DB_fetch_array($CurrencyResult);

	$UserSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['usercode'] . "'";
	$UserResult = DB_query($UserSQL);
	$UserRow = DB_fetch_array($UserResult);

	$AssignerSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['assigner'] . "'";
	$AssignerResult = DB_query($AssignerSQL);
	$AssignerRow = DB_fetch_array($AssignerResult);

	$AuthoriserSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['authorizer'] . "'";
	$AuthoriserResult = DB_query($AuthoriserSQL);
	$AuthoriserRow = DB_fetch_array($AuthoriserResult);

	$AuthExpSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['authorizerexpenses'] . "'";
	$AuthExpResult = DB_query($AuthExpSQL);
	$AuthExpRow = DB_fetch_array($AuthExpResult);

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP http://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . _('Tab Code') . ': ' . $SelectedTabs . '<br />
					' . _('User') . ': ' . $Tabs['usercode'] . ' - ' . $UserRow['realname'] . '<br />
					' . _('Currency') . ': ' . $Tabs['currency'] . ' - ' . $CurrencyRow['currency'] . '<br />
					' . _('Cash Assigner') . ': ' . $Tabs['assigner'] . ' - ' . $AssignerRow['realname'] . '<br />
					' . _('Authoriser - Cash') . ': ' . $Tabs['authorizer'] . ' - ' . $AuthoriserRow['realname'] . '<br />
					' . _('Authoriser - Expenses') . ': ' . $Tabs['authorizerexpenses'] . ' - ' . $AuthExpRow['realname'] . '<br />
					' . _('Date Range') . ': ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '<br />
				</div>
				<table>';

	$SQLBalance = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode = '" . $SelectedTabs . "'
			AND date < '" . $SQLFromDate . "'";

	$TabBalance = DB_query($SQLBalance);

	$Balance = DB_fetch_array($TabBalance);

	if( !isset($Balance['0'])){
		$Balance['0'] = 0;
	}

	$HTML .= '<tr><td>' . _('Balance before ') . '' . $_POST['FromDate'] . ':</td>
				<td></td>
				<td>' . locale_number_format($Balance['0'],$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $Tabs['currency'] . '</td>
			</tr>';

	$SQLBalanceNotAut = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode = '" . $SelectedTabs . "'
			AND authorized = '0000-00-00'
			AND date < '" . $SQLFromDate . "'";

	$TabBalanceNotAut = DB_query($SQLBalanceNotAut);

	$BalanceNotAut = DB_fetch_array($TabBalanceNotAut);

	if( !isset($BalanceNotAut['0'])){
		$BalanceNotAut['0'] = 0;
	}

	$HTML .= '<tr><td>' . _('Total not authorised before ') . '' . $_POST['FromDate'] . ':</td>
			  <td></td>
			  <td>' . '' . locale_number_format($BalanceNotAut['0'],$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $Tabs['currency'] . '</td>
		  </tr>';


	$HTML .=  '</table>';

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	$SQL = "SELECT counterindex,
					tabcode,
					tag,
					date,
					codeexpense,
					amount,
					authorized,
					posted,
					purpose,
					notes
			FROM pcashdetails
			WHERE tabcode = '" . $SelectedTabs . "'
				AND date >= '" . $SQLFromDate . "'
				AND date <= '" . $SQLToDate . "'
			ORDER BY date, counterindex Asc";

	$TabDetail = DB_query($SQL,
						_('No Petty Cash movements for this tab were returned by the SQL because'),
						_('The SQL that failed was:'));

	$HTML .=  '<table class="selection">
			<thead>
				<tr>
					<th class="ascending">' . _('Date of Expense') . '</th>
					<th class="ascending">' . _('Expense Code') . '</th>
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
			</tbody>';

	while ($MyRow = DB_fetch_array($TabDetail)) {

		$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
		$TagResult = DB_query($TagSQL);
		$TagRow = DB_fetch_array($TagResult);
		if ($MyRow['tag'] == 0) {
			$TagRow['tagdescription'] = _('None');
		}
		$TagTo = $MyRow['tag'];
		$TagDescription = $TagTo . ' - ' . $TagRow['tagdescription'];

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

		$SQLDes = "SELECT description
					FROM pcexpenses
					WHERE codeexpense = '" . $MyRow['codeexpense'] . "'";

		$ResultDes = DB_query($SQLDes);
		$Description=DB_fetch_array($ResultDes);
		if (!isset($Description[0])) {
				$ExpenseCodeDes = 'ASSIGNCASH';
		} else {
				$ExpenseCodeDes = $MyRow['codeexpense'] . ' - ' . $Description[0];
		}

		$HTML .=  '<tr class="striped_row">
				<td>' . ConvertSQLDate($MyRow['date']) . '</td>
				<td>' . $ExpenseCodeDes . '</td>
				<td class="number">' . locale_number_format($MyRow['amount'], $CurrDecimalPlaces) . '</td>
				<td class="number">' . $TaxesTaxAmount . '</td>
				<td>' . $TaxesDescription . '</td>
				<td>' . $TagDescription . '</td>
				<td>' . $MyRow['purpose'] . '</td>
				<td>' . $MyRow['notes'] . '</td>
				<td>' . $ReceiptText . '</td>
				<td>' . $AuthorisedDate . '</td>
			</tr>';
	}

	$SQLAmount="SELECT sum(amount)
				FROM pcashdetails
				WHERE tabcode = '" . $SelectedTabs . "'
				AND date <= '" . $SQLToDate . "'";

	$ResultAmount = DB_query($SQLAmount);
	$Amount = DB_fetch_array($ResultAmount);

	if (!isset($Amount[0])) {
		$Amount[0] = 0;
	}

	$HTML .= '</tbody>
		<tfoot>
			<tr class="total_row">
				<td colspan="2" class="number">' . _('Balance at') . ' ' .$_POST['ToDate'] . ':</td>
				<td class="number">' . locale_number_format($Amount[0],$_SESSION['CompanyRecord']['decimalplaces']) . ' </td>
				<td>' . $Tabs['currency'] . '</td>
				<td colspan="6"></td>
			</tr>
		</tfoot>';


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
		$Title = _('Petty Cash Management Report');
		include ('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('Payment Entry'). '" alt="" />' . ' ' . $Title . '
			</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

    echo '</form>';
} else {
	include  ('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('Payment Entry')
	. '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (!isset($_POST['FromDate'])){
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}

	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	/*Show a form to allow input of criteria for Tabs to show */
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="SelectedTabs">' . _('Petty Cash Tab') . ':</label>
				<select name="SelectedTabs">';

	$SQL = "SELECT tabcode
				FROM pctabs
				WHERE ( authorizer = '" . $_SESSION['UserID'] .
					"' OR usercode = '" . $_SESSION['UserID'].
					"' OR assigner = '" . $_SESSION['UserID'] . "' )
				ORDER BY tabcode";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectedTabs']) and $MyRow['tabcode'] == $_POST['SelectedTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop get type of tab

	DB_free_result($Result);


	echo '</select>
		</field>
		<field>
			<label for="FromDate">', _('From Date'), ':</label>
			<input tabindex="2" type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
		</field>
		<field>
			<label for="FromDate">', _('To Date'), ':</label>
			<input tabindex="3" type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . _('Show HTML') . '" />
		</div>
	</form>';
	include('includes/footer.php');

}

?>