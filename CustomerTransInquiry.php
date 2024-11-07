<?php
// CustomerTransInquiry.php

include('includes/session.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};

$Title = _('Customer Transactions Inquiry');
$ViewTopic = 'ARInquiries';
$BookMark = 'ARTransInquiry';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Transaction Inquiry') . '" alt="" />' . ' ' . _('Transaction Inquiry') . '
	</p>';
echo '<div class="page_help_text">' . _('Choose which type of transaction to report on.') . '</div>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="TransType">' . _('Type') . ':</label>
				<select tabindex="1" name="TransType"> ';

$sql = "SELECT typeid,
				typename
		FROM systypes
		WHERE typeid >= 10
		AND typeid <= 14";

$resultTypes = DB_query($sql);

echo '<option value="All">' . _('All') . '</option>';
while($myrow=DB_fetch_array($resultTypes)) {
	echo '<option';
	if(isset($_POST['TransType'])) {
		if($myrow['typeid'] == $_POST['TransType']) {
		     echo ' selected="selected"' ;
		}
	}
	echo ' value="' . $myrow['typeid'] . '">' . _($myrow['typename']) . '</option>';
}
echo '</select>
	</field>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<field>
		<label for="FromDate">' . _('From') . ':</label>
		<input type="date" maxlength="10" name="FromDate" required="required" size="11" tabindex="2" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
	</field>
	<field>
		<label for="ToDate">' . _('To') . ':</label>
		<input type="date" maxlength="10" name="ToDate" required="required" size="11" tabindex="3" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input name="ShowResults" tabindex="4" type="submit" value="' . _('Show transactions') . '" />
    </div>
	</form>';

if (isset($_POST['ShowResults']) && $_POST['TransType'] != ''){
   $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
   $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
   $sql = "SELECT transno,
		   		trandate,
				debtortrans.debtorno,
				branchcode,
				reference,
				invtext,
				order_,
				debtortrans.rate,
				ovamount+ovgst+ovfreight+ovdiscount as totalamt,
				currcode,
				typename,
				decimalplaces AS currdecimalplaces
			FROM debtortrans
			INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN currencies ON debtorsmaster.currcode=currencies.currabrev
			INNER JOIN systypes ON debtortrans.type = systypes.typeid
			WHERE ";

   $sql = $sql . "trandate >='" . $SQL_FromDate . "' AND trandate <= '" . $SQL_ToDate . "'";
	if  ($_POST['TransType']!='All')  {
		$sql .= " AND type = '" . $_POST['TransType']."'";
	}
	$sql .=  " ORDER BY id";

   $ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg();
   $DbgMsg =  _('The SQL that failed was');
   $TransResult = DB_query($sql,$ErrMsg,$DbgMsg);

   echo '<br />
		<table class="selection">';

   $TableHeader = '<tr>
					<th>' . _('Type') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Date') . '</th>
					<th>' . _('Customer') . '</th>
					<th>' . _('Branch') . '</th>
					<th>' . _('Reference') . '</th>
					<th>' . _('Comments') . '</th>
					<th>' . _('Order') . '</th>
					<th>' . _('Ex Rate') . '</th>
					<th>' . _('Amount') . '</th>
					<th>' . _('Currency') . '</th>
				</tr>';
	echo $TableHeader;

	$RowCounter = 1;

	while ($myrow=DB_fetch_array($TransResult)) {

		$format_base = '<tr class="striped_row">
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td style="width:200px">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>';

		if ($_POST['TransType']==10){ /* invoices */

			printf($format_base .
					'<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice"><img src="%s" title="' . _('Click to preview the invoice') . '" /></a></td>
					</tr>',
					_($myrow['typename']),
					$myrow['transno'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['debtorno'],
					$myrow['branchcode'],
					$myrow['reference'],
					$myrow['invtext'],
					$myrow['order_'],
					locale_number_format($myrow['rate'],6),
					locale_number_format($myrow['totalamt'],$myrow['currdecimalplaces']),
					$myrow['currcode'],
					$RootPath,
					$myrow['transno'],
					$RootPath.'/css/'.$Theme.'/images/preview.png');

		} elseif($_POST['TransType']==11) { /* credit notes */
			printf($format_base .
					'<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Credit"><img src="%s" title="' . _('Click to preview the credit') . '" /></a></td>
					</tr>',
					_($myrow['typename']),
					$myrow['transno'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['debtorno'],
					$myrow['branchcode'],
					$myrow['reference'],
					$myrow['invtext'],
					$myrow['order_'],
					locale_number_format($myrow['rate'],6),
					locale_number_format($myrow['totalamt'],$myrow['currdecimalplaces']),
					$myrow['currcode'],
					$RootPath,
					$myrow['transno'],
					$RootPath.'/css/'.$Theme.'/images/preview.png');
		} else {  /* otherwise */
			printf($format_base . '</tr>',
					_($myrow['typename']),
					$myrow['transno'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['debtorno'],
					$myrow['branchcode'],
					$myrow['reference'],
					$myrow['invtext'],
					$myrow['order_'],
					locale_number_format($myrow['rate'],6),
					locale_number_format($myrow['totalamt'],$myrow['currdecimalplaces']),
					$myrow['currcode']);
		}

	}
	//end of while loop

 echo '</table>';
}

include('includes/footer.php');

?>
