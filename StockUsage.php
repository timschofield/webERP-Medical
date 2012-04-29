<?php

/* $Id$*/


include('includes/session.inc');

$title = _('Stock Usage');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if (isset($_POST['ShowGraphUsage'])) {
	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/SuppInvGRNs.php">';
	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/StockUsageGraph.php?StockLocation=' . $_POST['StockLocation']  . '&StockID=' . $StockID . '">';
	echo '<p>' . _('You should automatically be forwarded to the usage graph') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			'<a href="' . $rootpath . '/StockUsageGraph.php?StockLocation=' . $_POST['StockLocation'] .'&StockID=' . $StockID . '">' . _('click here') . '</a> ' . _('to continue') . '.</p><br />';
	exit;
}

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $title . '</p>';

$result = DB_query("SELECT description,
							units,
							mbflag,
							decimalplaces
						FROM stockmaster
						WHERE stockid='".$StockID."'",$db);
$myrow = DB_fetch_array($result);

$DecimalPlaces = $myrow['decimalplaces'];

echo '<table class="selection">';

$Its_A_KitSet_Assembly_Or_Dummy =False;
if (($myrow['mbflag']=='K') OR ($myrow['mbflag']=='A') OR ($myrow['mbflag']=='D')) {
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo '<font color="#616162" size="3"><b>' . $StockID - $myrow['description'] . '</b></font>';

	echo '<br />' . _('The selected item is a dummy or assembly or kit-set item and cannot have a stock holding') . '. ' . _('Please select a different item');

	$StockID = '';
} else {
	echo '<tr><th class="header">' . _('Item') . ' :<b> ' . $StockID . ' - ' . $myrow['description'] . ' </b>  (' . _('in units of') . ' :<b> ' . $myrow['units'] . ')</b></th></tr>';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post"><tr><td>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Stock Code') . ':<input type="text" name="StockID" size="21" maxlength="20" value="' . $StockID . '" />';

echo _('From Stock Location') . ':<select name="StockLocation">';

$sql = "SELECT loccode, locationname FROM locations";
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<option selected="True" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected="True" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option Vvlue="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}
if (isset($_POST['StockLocation'])){
	if ('All'== $_POST['StockLocation']){
	     echo '<option selected="True" value="All">' . _('All Locations') . '</option>';
	} else {
	     echo '<option value="All">' . _('All Locations') . '</option>';
	}
}
echo '</select>';

echo '<button type="submit" name="ShowUsage">' . _('Show Stock Usage') . '</button>';
echo '<button type="submit" name="ShowGraphUsage">' . _('Show Graph Of Stock Usage') . '</button></td></tr></table><br />';

/* $_SESSION['NumberOfPeriodsOfStockUsage']  is defined in config.php as a user definable variable
config.php is loaded by header.inc */

/*HideMovt ==1 if the movement was only created for the purpose of a transaction but is not a physical movement eg. A price credit will create a movement record for the purposes of display on a credit note
but there is no physical stock movement - it makes sense honest ??? */

$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);

if (isset($_POST['ShowUsage'])){
	if($_POST['StockLocation']=='All'){
		$sql = "SELECT periods.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN (stockmoves.type=10 Or stockmoves.type=11 OR stockmoves.type=28)
												AND stockmoves.hidemovt=0
												AND stockmoves.stockid = '" . $StockID . "'
									THEN -stockmoves.qty ELSE 0 END) AS qtyused
			FROM periods LEFT JOIN stockmoves
				ON periods.periodno=stockmoves.prd
			WHERE periods.periodno <='" . $CurrentPeriod . "'
			GROUP BY periods.periodno,
				periods.lastdate_in_period
			ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];
	} else {
		$sql = "SELECT periods.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN (stockmoves.type=10 Or stockmoves.type=11 OR stockmoves.type=28)
												AND stockmoves.hidemovt=0
												AND stockmoves.stockid = '" . $StockID . "'
												AND stockmoves.loccode='" . $_POST['StockLocation'] . "'
									THEN -stockmoves.qty ELSE 0 END) AS qtyused
			FROM periods LEFT JOIN stockmoves
				ON periods.periodno=stockmoves.prd
			WHERE periods.periodno <='" . $CurrentPeriod . "'
			GROUP BY periods.periodno,
				periods.lastdate_in_period
			ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];

	}
	$MovtsResult = DB_query($sql, $db);
	if (DB_error_no($db) !=0) {
		echo _('The stock usage for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
		if ($debug==1){
		echo '<br />' . _('The SQL that failed was') . $sql;
		}
		exit;
	}

	echo '</div><table cellpadding="2" class="selection">';
	$tableheader = '<tr><th>' . _('Month') . '</th><th>' . _('Usage') . '</th></tr>';
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter

	$TotalUsage = 0;
	$PeriodsCounter =0;

	while ($myrow=DB_fetch_array($MovtsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$DisplayDate = MonthAndYearFromSQLDate($myrow['lastdate_in_period']);

		$TotalUsage += $myrow['qtyused'];
		$PeriodsCounter++;
		printf('<td>%s</td><td class="number">%s</td></tr>', $DisplayDate, locale_number_format($myrow['qtyused'],$DecimalPlaces));

	//end of page full new headings if
	}
	//end of while loop

	if ($TotalUsage>0 and $PeriodsCounter>0){
	echo '<tr><th colspan="2">' . _('Average Usage per month is') . ' ' . locale_number_format($TotalUsage/$PeriodsCounter,$DecimalPlaces);
	echo '</th></tr>';
	}
	echo '</table>';
} /* end if Show Usage is clicked */

echo '<br /><div class="centre">';
echo '<a href="' . $rootpath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status') .'</a>';
echo '<br /><a href="' . $rootpath . '/StockMovements.php?StockID=' . $StockID . '&StockLocation=' . $_POST['StockLocation'] . '">' . _('Show Stock Movements') . '</a>';
echo '<br /><a href="' . $rootpath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '&StockLocation=' . $_POST['StockLocation'] . '">' . _('Search Outstanding Sales Orders') . '</a>';
echo '<br /><a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a>';
echo '<br /><a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</a>';

echo '</form></div>';
include('includes/footer.inc');

?>