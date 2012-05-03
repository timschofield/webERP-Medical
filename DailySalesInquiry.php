<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Daily Sales Inquiry');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Daily Sales') . '" alt="" />' . ' ' . _('Daily Sales') . '</p>';
echo '<div class="page_help_text">' . _('Select the month to show daily sales for') . '</div>
	<br />';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['MonthToShow'])){
	$_POST['MonthToShow'] = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
	$Result = DB_query("SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['MonthToShow'] . "'",$db);
	$myrow = DB_fetch_array($Result);
	$EndDateSQL = $myrow['lastdate_in_period'];
}

echo '<table class="selection">
	<tr>
		<td>' . _('Month to Show') . ':</td>
		<td><select tabindex="1" name="MonthToShow" onChange="ReloadForm(ShowResults)">';

$PeriodsResult = DB_query("SELECT periodno, lastdate_in_period FROM periods",$db);

while ($PeriodRow = DB_fetch_array($PeriodsResult)){
	if ($_POST['MonthToShow']==$PeriodRow['periodno']) {
		echo '<option selected="selected" value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
		$EndDateSQL = $PeriodRow['lastdate_in_period'];
	} else {
		echo '<option value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
	}
}
echo '</select></td>';

echo '<td>' . _('Sales Type') . ':</td>';

if (!isset($_POST['StockType'])) {
	$_POST['StockType']='';
}

ShowStockTypes($_POST['StockType']);
echo '<input type="submit" name="UpdateItems" style="visibility:hidden" value="Not Seen" />';
echo '</tr>
	</table>
	<br />
	<div class="centre">
		<button tabindex="4" type="submit" name="ShowResults">' . _('Show Daily Sales For The Selected Month') . '</button>
	</div>
	</form>
	<br />';
/*Now get and display the sales data returned */
if (strpos($EndDateSQL,'/')) {
	$Date_Array = explode('/',$EndDateSQL);
} elseif (strpos ($EndDateSQL,'-')) {
	$Date_Array = explode('-',$EndDateSQL);
} elseif (strpos ($EndDateSQL,'.')) {
	$Date_Array = explode('.',$EndDateSQL);
}

if (strlen($Date_Array[2])>4) {
	$Date_Array[2]= substr($Date_Array[2],0,2);
}

$StartDateSQL =  date('Y-m-d', mktime(0,0,0, (int)$Date_Array[1],1,(int)$Date_Array[0]));

$sql = "SELECT 	trandate,
				SUM(price*(1-discountpercent)* (-qty)) as salesvalue,
				SUM((standardcost * -qty)) as cost
			FROM stockmoves
				INNER JOIN custbranch
				ON stockmoves.debtorno=custbranch.debtorno
					AND stockmoves.branchcode=custbranch.branchcode
				INNER JOIN stockmaster
				ON stockmoves.stockid=stockmaster.stockid
				INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			WHERE (stockmoves.type=10 or stockmoves.type=11)
			AND show_on_inv_crds =1
			AND trandate>='" . $StartDateSQL . "'
			AND trandate<='" . $EndDateSQL . "'";

if ($_POST['StockType']!='') {
	$sql .= " AND stockcategory.stocktype='" . $_POST['StockType'] . "'";
}

$sql .= " GROUP BY stockmoves.trandate ORDER BY stockmoves.trandate";
$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg($db);
$SalesResult = DB_query($sql, $db,$ErrMsg);

echo '<table cellpadding="2" class="selection">';
echo '<tr><th colspan="7"><font color="navy" size="3">' . _('Sales For The Month Of') . ' ' .
		MonthAndYearFromSQLDate($StartDateSQL) . '</font></th></tr>';

if ($_POST['StockType']!='') {
	echo '<tr><th colspan="7"><font color="navy" size="2">' . _('For sales of type') . ' ' .
		GetStockType($_POST['StockType']) . '</font></th></tr>';
} else {
	echo '<tr><th colspan="7"><font color="navy" size="2">' . _('For all sales') . '</font></th></tr>';
}

echo '<tr>
	<th width="14.285714286%">' . _('Sunday') . '</th>
	<th width="14.285714286%">' . _('Monday') . '</th>
	<th width="14.285714286%">' . _('Tuesday') . '</th>
	<th width="14.285714286%">' . _('Wednesday') . '</th>
	<th width="14.285714286%">' . _('Thursday') . '</th>
	<th width="14.285714286%">' . _('Friday') . '</th>
	<th width="14.285714286%">' . _('Saturday') . '</th></tr>';

$CumulativeTotalSales = 0;
$CumulativeTotalCost = 0;
$BilledDays = 0;
$DaySalesArray = array();
while ($DaySalesRow=DB_fetch_array($SalesResult)) {

	if ($DaySalesRow['salesvalue'] > 0) {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['Sales'] = $DaySalesRow['salesvalue'];
	} else {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['Sales'] = 0;
	}
	if ($DaySalesRow['salesvalue'] > 0 ) {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['GPPercent'] = ($DaySalesRow['salesvalue']-$DaySalesRow['cost'])/$DaySalesRow['salesvalue'];
	} else {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['GPPercent'] = 0;
	}
	$BilledDays++;
	$CumulativeTotalSales += $DaySalesRow['salesvalue'];
	$CumulativeTotalCost += $DaySalesRow['cost'];
}
//end of while loop
echo '<tr>';
$ColumnCounter = DayOfWeekFromSQLDate($StartDateSQL);
for ($i=0;$i<$ColumnCounter;$i++){
	echo '<td></td>';
}
$DayNumber = 1;
/*Set up day number headings*/
for ($i=$ColumnCounter;$i<=6;$i++){
	   echo '<th>' . $DayNumber . '</th>';
	   $DayNumber++;
}
echo '</tr><tr>';
for ($i=0;$i<$ColumnCounter;$i++){
	echo '<td></td>';
}

$LastDayOfMonth = DayOfMonthFromSQLDate($EndDateSQL);
for ($i=1;$i<=$LastDayOfMonth;$i++){
		$ColumnCounter++;
		if(isset($DaySalesArray[$i])) {
			echo '<td class="number" style="outline: 1px solid gray;">' . locale_money_format($DaySalesArray[$i]['Sales'],$_SESSION['CompanyRecord']['currencydefault']) . '<br />' .  locale_number_format($DaySalesArray[$i]['GPPercent']*100,1) . '%</td>';
		} else {
			echo '<td class="number" style="outline: 1px solid gray;">' . locale_money_format(0,$_SESSION['CompanyRecord']['currencydefault']) . '<br />' .  locale_number_format(0,1) . '%</td>';
		}
		if ($ColumnCounter==7){
			echo '</tr><tr>';
						for ($j=1;$j<=7;$j++){
								   echo '<th>' . $DayNumber. '</th>';
							$DayNumber++;
							if($DayNumber>$LastDayOfMonth){
								   break;
							}
						}
						echo '</tr><tr>';
			$ColumnCounter=0;
		}


}
if ($ColumnCounter!=0) {
	echo '</tr><tr>';
}

if ($CumulativeTotalSales !=0){
	$AverageGPPercent = ($CumulativeTotalSales - $CumulativeTotalCost)*100/$CumulativeTotalSales;
	$AverageDailySales = $CumulativeTotalSales/$BilledDays;
} else {
	$AverageGPPercent = 0;
	$AverageDailySales = 0;
}

echo '<th colspan="7">' . _('Total Sales for month') . ': ' . locale_money_format($CumulativeTotalSales,$_SESSION['CompanyRecord']['currencydefault']) . ' ' . _('GP%') . ': ' . locale_number_format($AverageGPPercent,1) . '% ' . _('Avg Daily Sales') . ': ' . locale_money_format($AverageDailySales,$_SESSION['CompanyRecord']['currencydefault']) . '</th></tr>';

echo '</table>';

include('includes/footer.inc');
?>