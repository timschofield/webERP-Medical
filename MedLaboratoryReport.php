<?php
/* $Id$*/
include ('includes/session.php');
$Title = _('Financial Report for Laboratory Department');

include ('includes/header.php');

if (!isset($_POST['ReportDate'])) {
	$_POST['ReportDate'] = date($_SESSION['DefaultDateFormat']);
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/reports.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<table width="98%" class="selection">';

echo '<tr><th colspan="3"><font color="navy" size="4">' . _('Financial Report for the Laboratory Department on') . ':</font>
		<input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" onchange="ReloadForm(submit)" name="ReportDate" maxlength="10" size="11" value="' . $_POST['ReportDate'] . '" /><input type="submit" name="submit" value="Refresh" /></th></tr>';

$SQL = "SELECT decimalplaces FROM currencies WHERE currabrev='" . $_SESSION['CompanyRecord']['currencydefault'] . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$DecimalPlaces = $MyRow['decimalplaces'];

$SQL = "SELECT salestypes.sales_type,
				debtortrans.branchcode,
				debtortrans.trandate,
				stockmoves.stockid,
				stockmaster.description,
				sum(-qty) AS quantity,
				sum(-qty*price) AS value
			FROM stockmoves
			LEFT JOIN debtortrans
			ON stockmoves.type=debtortrans.type
				AND stockmoves.transno=debtortrans.transno
			LEFT JOIN stockmaster
			ON stockmaster.stockid=stockmoves.stockid
			LEFT JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			LEFT JOIN salestypes
			ON salestypes.typeabbrev=debtorsmaster.salestype
			LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='T'
				AND stockmoves.trandate='" . FormatDateForSQL($_POST['ReportDate']) . "'
			GROUP BY stockmoves.stockid
			ORDER BY stockmoves.trandate DESC";
$Result = DB_query($SQL);

echo '<td width="33%" style="text-align: left;vertical-align: top;">';
echo '<table width="100%" class="selection">';
echo '<tr><th colspan="6"><font color="navy" size="2">';
echo _('Income for') . ' ' . $_POST['ReportDate'];
echo '</font></th></tr>';
echo '<tr>
		<th>' . _('Customer') . '<br />' . _('Type') . '</th>
		<th>' . _('Billing') . '<br />' . _('Method') . '</th>
		<th>' . _('Test ID') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Total Quantity') . '</th>
		<th>' . _('Total Income') . '</th>
	</tr>';

$LastType = 'General';
$SubTotalQuantity = 0;
$SubTotalValue = 0;
$TotalQuantity = 0;
$TotalValue = 0;
$Days = 0;
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['sales_type'] != $LastType) {
		echo '<tr>
				<th colspan="5"></th>
			</tr>';
		echo '<tr><td colspan="2"></td>';
		echo '<td>' . _('Total For') . ' ' . $MyRow['sales_type'] . '</td>';
		echo '<td class="number">' . $SubTotalQuantity . '</td>';
		echo '<td class="number">' . locale_number_format($SubTotalValue, $DecimalPlaces) . '</td></tr>';
		$TotalQuantity+= $SubTotalQuantity;
		$TotalValue+= $SubTotalValue;
		$SubTotalQuantity = 0;
		$SubTotalValue = 0;
		$LastDate = $MyRow['trandate'];
		echo '<tr><th colspan="5"></th></tr>';
		$Days++;
	}
	echo '<tr>
			<td>' . $MyRow['sales_type'] . '</td>
			<td>' . $MyRow['branchcode'] . '</td>
			<td>' . $MyRow['stockid'] . '</td>
			<td>' . $MyRow['description'] . '</td>
			<td class="number">' . $MyRow['quantity'] . '</td>
			<td class="number">' . number_format($MyRow['value'], $DecimalPlaces) . '</td>
		</tr>';
	$SubTotalQuantity+= $MyRow['quantity'];
	$SubTotalValue+= $MyRow['value'];
}
echo '</td></tr></table>';

$SQL = "SELECT stockmoves.trandate,
				stockmoves.stockid,
				stockmaster.description,
				sum(-qty) AS quantity,
				sum(-qty*price) AS value
			FROM stockmoves
			LEFT JOIN stockmaster
			ON stockmaster.stockid=stockmoves.stockid
			LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='T'
			GROUP BY stockmoves.trandate,
					stockmoves.stockid
			ORDER BY stockmoves.trandate DESC";
$Result = DB_query($SQL);
echo '</td><td width="33%" style="text-align: left;">';
echo '<table width="100%" class="selection">';

echo '<tr><th colspan="5"><font color="navy" size="2">';
echo _('Income for the previous week');
echo '</font></th></tr>';

$LastDate = FormatDateForSQL(DateAdd($_POST['ReportDate'], 'd', -1));
$SubTotalQuantity = 0;
$SubTotalValue = 0;
$TotalQuantity = 0;
$TotalValue = 0;
$Days = 0;
echo '<tr>
		<th>' . _('Date') . '</th>
		<th>' . _('Test ID') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Total Quantity') . '</th>
		<th>' . _('Total Income') . '</th>
	</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	if (ConvertSQLDate($MyRow['trandate']) < $_POST['ReportDate'] and $Days < 6) {
		if ($MyRow['trandate'] != $LastDate) {
			echo '<tr><th colspan="5"></th></tr>';
			echo '<tr><td colspan="2"></td>';
			echo '<td>' . _('Total For') . ' ' . GetWeekDayText(DayOfWeekFromSQLDate(FormatDateForSQL(DateAdd(ConvertSQLDate($MyRow['trandate']), 'd', 1)))) . ' ' . DateAdd(ConvertSQLDate($MyRow['trandate']), 'd', 1) . '</td>';
			echo '<td class="number">' . $SubTotalQuantity . '</td>';
			echo '<td class="number">' . number_format($SubTotalValue, $_SESSION['Currencies'][$_SESSION['CompanyRecord']['currencydefault']]['DecimalPlaces']) . '</td></tr>';
			$TotalQuantity+= $SubTotalQuantity;
			$TotalValue+= $SubTotalValue;
			$SubTotalQuantity = 0;
			$SubTotalValue = 0;
			$LastDate = $MyRow['trandate'];
			echo '<tr><th colspan="5"></th></tr>';
			$Days++;
		}
		echo '<tr>
				<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td class="number">' . $MyRow['quantity'] . '</td>
				<td class="number">' . number_format($MyRow['value'], $DecimalPlaces) . '</td>
			</tr>';
		$SubTotalQuantity+= $MyRow['quantity'];
		$SubTotalValue+= $MyRow['value'];
	}
}

echo '<tr><th colspan="5"></th></tr>';
echo '<tr><th colspan="5"></th></tr>';
echo '<tr><td colspan="2"></td>';
echo '<td>' . _('Total For') . ' ' . GetWeekDayText(DayOfWeekFromSQLDate(FormatDateForSQL(DateAdd(ConvertSQLDate($LastDate), 'd', 0)))) . ' ' . DateAdd(ConvertSQLDate($LastDate), 'd', 0) . '</td>';
echo '<td class="number">' . $SubTotalQuantity . '</td>';
echo '<td class="number">' . number_format($SubTotalValue, $DecimalPlaces) . '</td></tr>';
echo '<tr><th colspan="5"></th></tr>';

echo '<tr><th colspan="5"></th></tr>';
echo '<tr><td colspan="2"></td>';
echo '<td><b>' . ('Total') . '</b></td>';
echo '<td class="number"><b>' . $TotalQuantity . '</b></td>';
echo '<td class="number"><b>' . number_format($TotalValue, $DecimalPlaces) . '</b></td></tr>';
echo '<tr><th colspan="5"></th></tr>';

echo '</table>';
echo '</td>';
echo '<td></td></tr>';
echo '</table>';

include ('includes/footer.php');

?>