<?php


include('includes/session.php');
$Title = _('Top Customer Sales Inquiry');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Sales Inquiry') . '" alt="" />' . ' ' . _('Top Customer Sales Inquiry') . '</p>';
echo '<div class="page_help_text">' . _('Select the parameters for the report') . '</div><br />';

if (!isset($_POST['DateRange'])){
	/* then assume report is for This Month - maybe wrong to do this but hey better than reporting an error?*/
	$_POST['DateRange']='ThisMonth';
}

echo '<form id="form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<fieldset>
		<legend>' . _('Date Selection') . '</legend>
		<field>
		<label for="DateRange">' . _('Custom Range') . ':</label>
		<input type="radio" name="DateRange" value="Custom" ';

if ($_POST['DateRange']=='Custom'){
	echo 'checked="checked"';
}
echo	' onchange="ReloadForm(form1.ShowSales)" />
		</field>
	<field>
		<label for="DateRange">' . _('This Week') . ':</label>
		<input type="radio" name="DateRange" value="ThisWeek" ';
if ($_POST['DateRange']=='ThisWeek'){
	echo 'checked="checked"';
}
echo	' onchange="ReloadForm(form1.ShowSales)" />
		</field>
	<field>
		<label for="DateRange">' . _('This Month') . ':</label>
		<input type="radio" name="DateRange" value="ThisMonth" ';
if ($_POST['DateRange']=='ThisMonth'){
	echo 'checked="checked"';
}
echo	' onchange="ReloadForm(form1.ShowSales)" />
		</field>
	<field>
		<label for="DateRange">' . _('This Quarter') . ':</label>
		<input type="radio" name="DateRange" value="ThisQuarter" ';
if ($_POST['DateRange']=='ThisQuarter'){
	echo 'checked="checked"';
}
echo	' onchange="ReloadForm(form1.ShowSales)" />
		</field>';
if ($_POST['DateRange']=='Custom'){
	if (!isset($_POST['FromDate'])){
		unset($_POST['ShowSales']);
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'],mktime(1,1,1,Date('m')-12,Date('d')+1,Date('Y')));
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}
	echo '<field>
			<label for="FromDate">' . _('Date From') . ':</label>
			<input type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
		</field>';
	echo '<field>
			<label for="ToDate">' . _('Date To') . ':</label>
			<input type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>';
}
echo '</fieldset>
		<fieldset>'; //new sub table to set parameters for order of display


if (!isset($_POST['OrderBy'])){ //default to order by net sales
	$_POST['OrderBy']='NetSales';
}
echo '<legend>' . _('Display') . '</legend>
	<field>
		<label for="OrderBy">' . _('Order By Net Sales') . ':</label>
		<input type="radio" name="OrderBy" value="NetSales" ';
if ($_POST['OrderBy']=='NetSales'){
	echo 'checked="checked"';
}
echo	' />
		</field>';

echo '<field>
		<label for="OrderBy">' . _('Order By Quantity') . ':</label>
		<input type="radio" name="OrderBy" value="Quantity" ';
if ($_POST['OrderBy']=='Quantity'){
	echo 'checked="checked"';
}
if (!isset($_POST['NoToDisplay'])){
	$_POST['NoToDisplay']=20;
}
	echo	' />
				</field>
				<field>
					<label for="NoToDisplay">' . _('Number to Display') . ':</label>
					<input type="text" class="number" name="NoToDisplay" size="4" maxlength="4" value="' . $_POST['NoToDisplay'] .'"  />
				</field>
		</field>
	</fieldset>';


echo '<div class="centre"><input tabindex="4" type="submit" name="ShowSales" value="' . _('Show Sales') . '" /></div>';
echo '</form>';

if (isset($_POST['ShowSales'])){
	$InputError=0; //assume no input errors now test for errors
	if ($_POST['DateRange']=='Custom'){
		if (!Is_Date($_POST['FromDate'])){
			$InputError = 1;
			prnMsg(_('The date entered for the from date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		}
		if (!Is_Date($_POST['ToDate'])){
			$InputError = 1;
			prnMsg(_('The date entered for the to date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		}
		if (Date1GreaterThanDate2($_POST['FromDate'],$_POST['ToDate'])){
			$InputError = 1;
			prnMsg(_('The from date is expected to be a date prior to the to date. Please review the selected date range'),'error');
		}
	}
	switch ($_POST['DateRange']) {
		case 'ThisWeek':
			$FromDate = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'ThisMonth':
			$FromDate = date('Y-m-d',mktime(0,0,0,date('m'),1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'ThisQuarter':
			switch (date('m')) {
				case 1:
				case 2:
				case 3:
					$QuarterStartMonth=1;
					break;
				case 4:
				case 5:
				case 6:
					$QuarterStartMonth=4;
					break;
				case 7:
				case 8:
				case 9:
					$QuarterStartMonth=7;
					break;
				default:
					$QuarterStartMonth=10;
			}
			$FromDate = date('Y-m-d',mktime(0,0,0,$QuarterStartMonth,1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'Custom':
			$FromDate = FormatDateForSQL($_POST['FromDate']);
			$ToDate = FormatDateForSQL($_POST['ToDate']);
	}
	$sql = "SELECT stockmoves.debtorno,
					debtorsmaster.name,
					SUM(CASE WHEN stockmoves.type=10
							OR stockmoves.type=11 THEN
							 -qty
							ELSE 0 END) as salesquantity,
					SUM(CASE WHEN stockmoves.type=10 THEN
							price*(1-discountpercent)* -qty
							ELSE 0 END) as salesvalue,
					SUM(CASE WHEN stockmoves.type=11 THEN
							price*(1-discountpercent)* (-qty)
							ELSE 0 END) as returnvalue,
					SUM(CASE WHEN stockmoves.type=11
								OR stockmoves.type=10 THEN
							price*(1-discountpercent)* (-qty)
							ELSE 0 END) as netsalesvalue,
					SUM((standardcost * -qty)) as cost
			FROM stockmoves
			INNER JOIN debtorsmaster
			ON stockmoves.debtorno=debtorsmaster.debtorno
			WHERE (stockmoves.type=10 or stockmoves.type=11)
			AND show_on_inv_crds =1
			AND trandate>='" . $FromDate . "'
			AND trandate<='" . $ToDate . "'
			GROUP BY stockmoves.debtorno";

	if ($_POST['OrderBy']=='NetSales'){
		$sql .= " ORDER BY netsalesvalue DESC ";
	} else {
		$sql .= " ORDER BY salesquantity DESC ";
	}
	if (is_numeric($_POST['NoToDisplay'])){
		if ($_POST['NoToDisplay'] > 0){
			$sql .= " LIMIT " . $_POST['NoToDisplay'];
		}
	}

	$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg();
	$SalesResult = DB_query($sql,$ErrMsg);


	echo '<table cellpadding="2" class="selection">
			<tr>
				<th>' . _('Rank') . '</th>
				<th>' . _('Customer') . '</th>
				<th>' . _('Sales Value') . '</th>
				<th>' . _('Refunds') . '</th>
				<th>' . _('Net Sales') . '</th>
				<th>' . _('Sales')  . '<br />' . _('Quantity') . '</th>
			</tr>';

	$CumulativeTotalSales = 0;
	$CumulativeTotalRefunds = 0;
	$CumulativeTotalNetSales = 0;
	$CumulativeTotalQuantity = 0;
	$i=1;

	while ($SalesRow=DB_fetch_array($SalesResult)) {

		echo '<tr class="striped_row">
				<td>' . $i . '</td>
				<td>' . $SalesRow['debtorno'] . ' - ' . $SalesRow['name'] . '</td>
				<td class="number">' . locale_number_format($SalesRow['salesvalue'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['returnvalue'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['netsalesvalue'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['salesquantity'],'Variable') . '</td>
			</tr>';
		$i++;

		$CumulativeTotalSales += $SalesRow['salesvalue'];
		$CumulativeTotalRefunds += $SalesRow['returnvalue'];
		$CumulativeTotalNetSales += ($SalesRow['salesvalue']+$SalesRow['returnvalue']);
		$CumulativeTotalQuantity += $SalesRow['salesquantity'];

	} //loop around category sales for the period

	echo '<tr class="striped_row"><td colspan="8"><hr /></td></tr>';
	echo '<tr class="striped_row">';

	echo '<td class="number" colspan="2">' . _('GRAND Total') . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalRefunds,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalNetSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalQuantity,'Variable') . '</td>
		</tr>';

	echo '</table>';

} //end of if user hit show sales
include('includes/footer.php');
?>