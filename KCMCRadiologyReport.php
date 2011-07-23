<?php

/* $Id$*/
$PageSecurity=1;
include ('includes/session.inc');
$title = _('Financial Report for Radiology Department');

include('includes/pchart/pChart/pData.class');
include('includes/pchart/pChart/pChart.class');

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

$sql="SELECT salestypes.sales_type,
				debtortrans.branchcode,
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
			WHERE stockcategory.stocktype='X'
				AND stockmoves.trandate='".FormatDateForSQL($_POST['ReportDate'])."'
			GROUP BY stockmoves.stockid
			ORDER BY stockmoves.trandate DESC";
$result=DB_query($sql, $db);

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

$LastType='General';
$SubTotalQuantity=0;
$SubTotalValue=0;
$TotalQuantity=0;
$TotalValue=0;
while($myrow=DB_fetch_array($result)) {
	if ($myrow['sales_type'] != $LastType) {
		echo '<tr><th colspan="5"></th></tr>';
		echo '<tr><td colspan="2"></td>';
		echo '<td>' . _('Total For') . ' ' . $myrow['sales_type'].'</td>';
		echo '<td class="number">' . $SubTotalQuantity . '</td>';
		echo '<td class="number">' . number_format($SubTotalValue, $_SESSION['Currencies'][$_SESSION['CompanyRecord']['currencydefault']]['DecimalPlaces']) . '</td></tr>';
		$TotalQuantity+=$SubTotalQuantity;
		$TotalValue+=$SubTotalValue;
		$SubTotalQuantity=0;
		$SubTotalValue=0;
		$LastDate=$myrow['trandate'];
		echo '<tr><th colspan="5"></th></tr>';
		$Days++;
	}
	echo '<tr>
			<td>' . $myrow['sales_type'] . '</td>
			<td>' . $myrow['branchcode'] . '</td>
			<td>' . $myrow['stockid'] . '</td>
			<td>' . $myrow['description'] . '</td>
			<td class="number">' . $myrow['quantity'] . '</td>
			<td class="number">' . number_format($myrow['value'], $_SESSION['Currencies'][$_SESSION['CompanyRecord']['currencydefault']]['DecimalPlaces']) . '</td>
		</tr>';
	$SubTotalQuantity+=$myrow['quantity'];
	$SubTotalValue+=$myrow['value'];
}
echo '</td></tr></table>';

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
echo '</td><td width="33%" style="text-align: left;vertical-align: top;">';
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
	if (ConvertSQLDate($myrow['trandate']) < $_POST['ReportDate'] and $Days<6) {
		if ($myrow['trandate'] != $LastDate) {
			echo '<tr><th colspan="5"></th></tr>';
			echo '<tr><td colspan="2"></td>';
			echo '<td>' . _('Total For') . ' ' . $DayNames[DayOfWeekFromSQLDate($myrow['trandate'])].' '.DateAdd(ConvertSQLDate($myrow['trandate']),'d',1).'</td>';
			echo '<td class="number">' . $SubTotalQuantity . '</td>';
			echo '<td class="number">' . number_format($SubTotalValue, $_SESSION['Currencies'][$_SESSION['CompanyRecord']['currencydefault']]['DecimalPlaces']) . '</td></tr>';
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
				<td class="number">' . number_format($myrow['value'], $_SESSION['Currencies'][$_SESSION['CompanyRecord']['currencydefault']]['DecimalPlaces']) . '</td>
			</tr>';
		$SubTotalQuantity+=$myrow['quantity'];
		$SubTotalValue+=$myrow['value'];
	}
}
echo '<tr><th colspan="5"></th></tr>';
echo '<tr><th colspan="5"></th></tr>';
echo '<tr><td colspan="2"></td>';
echo '<td>' . _('Total For') . ' ' . $DayNames[DayOfWeekFromSQLDate(DateAdd($LastDate, 'd', -1))].' '.DateAdd(ConvertSQLDate($LastDate),'d',0).'</td>';
echo '<td class="number">' . $SubTotalQuantity . '</td>';
echo '<td class="number">' . number_format($SubTotalValue, $_SESSION['Currencies'][$_SESSION['CompanyRecord']['currencydefault']]['DecimalPlaces']) . '</td></tr>';
echo '<tr><th colspan="5"></th></tr>';

echo '<tr><th colspan="5"></th></tr>';
echo '<tr><td colspan="2"></td>';
echo '<td><b>' . ('Total') . '</b></td>';
echo '<td class="number"><b>' . $TotalQuantity . '</b></td>';
echo '<td class="number"><b>' . number_format($TotalValue, $_SESSION['Currencies'][$_SESSION['CompanyRecord']['currencydefault']]['DecimalPlaces']) . '</b></td></tr>';
echo '<tr><th colspan="5"></th></tr>';

echo '</table>';
echo '</td>';
echo '<td width="33%" style="text-align: left;vertical-align: top;">';
echo '<table width="100%" class="selection">';

$sql = "SELECT stockmoves.stockid,
				stockmaster.description,
				sum(-qty*price) AS value
			FROM stockmoves
			LEFT JOIN stockmaster
			ON stockmaster.stockid=stockmoves.stockid
			LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockcategory.stocktype='X'
				AND stockmoves.trandate='".FormatDateForSQL($_POST['ReportDate'])."'
			GROUP BY stockmoves.stockid";
$result = DB_query($sql, $db);

while ($myrow=DB_fetch_array($result)) {
	$Point1[]=$myrow['value'];
	$Point2[]=$myrow['stockid'];
}
// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($Point1,"Serie1");
$DataSet->AddPoint($Point2,"Serie2");
$DataSet->AddAllSeries();
$DataSet->SetAbsciseLabelSerie("Serie2");

 // Initialise the graph
$Test = new pChart(420,250);
$Test->drawFilledRoundedRectangle(7,7,413,243,5,240,240,240);
$Test->drawRoundedRectangle(5,5,415,245,5,230,230,230);
$Test->createColorGradientPalette(195,204,56,223,110,41,5);

// Draw the pie chart
$Test->setFontProperties("includes/pchart/Fonts/tahoma.ttf",8);
$Test->AntialiasQuality = 0;
$Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),180,130,110,PIE_PERCENTAGE_LABEL,FALSE,50,20,5);
$Test->drawPieLegend(330,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);

// Write the title
$Test->setFontProperties("includes/pchart/Fonts/MankSans.ttf",10);
$Test->drawTitle(10,20,"Sales per month",100,100,100);

$Test->Render("example10.png");

echo '<tr><th colspan="5"><font color="navy" size="2">';
echo _('Income for the previous week');
echo '</font></th></tr>';
echo '<td>';
echo '<img src=example10.png />';
echo '</td></tr></table>';
echo '</td></table>';


include('includes/footer.inc');

?>