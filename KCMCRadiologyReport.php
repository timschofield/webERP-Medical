<?php

/* $Id$*/
$PageSecurity=1;
include ('includes/session.inc');
$title = _('Financial Report for Radiology Department');

include('includes/header.inc');

if (!isset($_POST['ReportDate'])) {
	$_POST['ReportDate']=date($_SESSION['DefaultDateFormat']);
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p>';

echo '<table width="98%" class="selection">';

echo '<tr><th colspan="3"><font color="navy" size="4">' . _('Financial report for the radiology department on') . ':</font>
		<input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" onchange="ReloadForm(submit)" name="ReportDate" maxlength="10" size="11" value="' .
					 $_POST['ReportDate'] . '" /><input type="submit" name="submit" value="Refresh" /></th></tr>';

$sql = "SELECT stockmoves.trandate,
				stockmoves.stockid,
				stockmaster.description,
				sum(-qty) AS quantity,
				sum(-qty*price) AS value
			FROM stockmoves
			LEFT JOIN stockmaster
			ON stockmaster.stockid=stockmoves.stockid
			LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='X'
			GROUP BY stockmoves.trandate,
					stockmoves.stockid
			ORDER BY stockmoves.trandate DESC";
$result = DB_query($sql, $db);
echo '<tr><td width="33%" style="text-align: left;">';
echo '<table width="100%" class="selection">';


echo '<tr><th colspan="5"><font color="navy" size="2">';
echo _('Income for the previous week');
echo '</font></th></tr>';

$LastDate=FormatDateForSQL(DateAdd($_POST['ReportDate'], 'd', -1));
$SubTotalQuantity=0;
$SubTotalValue=0;
$TotalQuantity=0;
$TotalValue=0;
$Days=0;
echo '<tr>
		<th>' . _('Date') . '</th>
		<th>' . _('Test ID') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Total Quantity') . '</th>
		<th>' . _('Total Income') . '</th>
	</tr>';

while($myrow=DB_fetch_array($result)) {
	if (ConvertSQLDate($myrow['trandate']) < $_POST['ReportDate'] and $Days<=7) {
		if ($myrow['trandate'] != $LastDate) {
			echo '<tr><th colspan="5"></th></tr>';
			echo '<tr><td colspan="2"></td>';
			echo '<td>' . _('Total For') . ' ' . $DayNames[DayOfWeekFromSQLDate($myrow['trandate'])].'</td>';
			echo '<td class="number">' . $SubTotalQuantity . '</td>';
			echo '<td class="number">' . number_format($SubTotalValue, $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
			$TotalQuantity+=$SubTotalQuantity;
			$TotalValue+=$SubTotalValue;
			$SubTotalQuantity=0;
			$SubTotalValue=0;
			$LastDate=$myrow['trandate'];
			echo '<tr><th colspan="5"></th></tr>';
			$Days++;
		}
		echo '<tr>
				<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
				<td>' . $myrow['stockid'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td class="number">' . $myrow['quantity'] . '</td>
				<td class="number">' . number_format($myrow['value'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
		$SubTotalQuantity+=$myrow['quantity'];
		$SubTotalValue+=$myrow['value'];
	}
}
echo '<tr><th colspan="5"></th></tr>';
echo '<tr><th colspan="5"></th></tr>';
echo '<tr><td colspan="2"></td>';
echo '<td>' . _('Total For') . ' ' . $DayNames[DayOfWeekFromSQLDate(DateAdd($LastDate, 'd', -1))].'</td>';
echo '<td class="number">' . $SubTotalQuantity . '</td>';
echo '<td class="number">' . number_format($SubTotalValue, $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
echo '<tr><th colspan="5"></th></tr>';

echo '<tr><th colspan="5"></th></tr>';
echo '<tr><td colspan="2"></td>';
echo '<td><b>' . ('Total') . '</b></td>';
echo '<td class="number"><b>' . $TotalQuantity . '</b></td>';
echo '<td class="number"><b>' . number_format($TotalValue, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td></tr>';
echo '<tr><th colspan="5"></th></tr>';

echo '</table>';
echo '</td><td width="33%" style="text-align: left;vertical-align: top;">';
echo '<table width="100%" class="selection">';
echo '<tr><th colspan="5"><font color="navy" size="2">';
echo _('Income for') . ' ' . $_POST['ReportDate'];
echo '</font></th></tr>';
echo '</td></tr></table>';
echo '<td></td></tr>';
echo '</table>';


include('includes/footer.inc');

?>