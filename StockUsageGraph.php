<?php

include('includes/session.php');
$Result = DB_query("SELECT description FROM stockmaster WHERE stockid='" . trim(mb_strtoupper($_GET['StockID'])) . "'");
$MyRow = DB_fetch_row($Result);

include('includes/phplot/phplot.php');
$graph = new PHPlot(1000, 500);
$graph->SetTitle($MyRow[0] . ' ' . _('Usage'));
$graph->SetXTitle(_('Month'));
$graph->SetYTitle(_('Quantity'));
$graph->SetBackgroundColor("wheat");
$graph->SetTitleColor("blue");
$graph->SetPlotType('bars');
$graph->SetShading(5);
$graph->SetDrawYGrid(TRUE);
$graph->SetMarginsPixels(60, 40, 40, 40);
$graph->SetDataType('text-data');

if($_GET['StockLocation'] == 'All') {
    if (!empty($_SESSION['StockUsageShowZeroWithinPeriodRange'])) {
        $CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));

        $SQL = "SELECT periods.periodno,
                       periods.lastdate_in_period,
                       SUM(CASE WHEN stockmoves.type IN (10, 11, 28)
                                 AND stockmoves.hidemovt = 0
                                 AND stockmoves.stockid = '" . $_GET['StockID'] . "'
                                THEN -stockmoves.qty ELSE 0 END) AS qtyused
                  FROM periods
             LEFT JOIN stockmoves ON periods.periodno = stockmoves.prd
             LEFT JOIN locationusers ON locationusers.loccode = stockmoves.loccode
                                    AND locationusers.userid = '" . $_SESSION['UserID'] . "'
                                    AND locationusers.canview = 1
                 WHERE periods.periodno > '" . ($CurrentPeriod - 24) . "'
              GROUP BY periods.periodno,
                       periods.lastdate_in_period
              ORDER BY periodno ASC
                 LIMIT 24";
    } else {
        $SQL = "SELECT periods.periodno,
                       periods.lastdate_in_period,
                       SUM(-stockmoves.qty) AS qtyused
                  FROM stockmoves
            INNER JOIN periods ON stockmoves.prd = periods.periodno
            INNER JOIN locationusers ON locationusers.loccode = stockmoves.loccode
                                        AND locationusers.userid = '" . $_SESSION['UserID'] . "'
                                        AND locationusers.canview = 1
                 WHERE stockmoves.type IN (10, 11, 28)
                   AND stockmoves.hidemovt = 0
                   AND stockmoves.stockid = '" . trim(mb_strtoupper($_GET['StockID'])) . "'
              GROUP BY periods.periodno,
                       periods.lastdate_in_period
              ORDER BY periodno
                 LIMIT 24";
    }
} else {
    if (!empty($_SESSION['StockUsageShowZeroWithinPeriodRange'])) {
        $CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));

        $SQL = "SELECT periods.periodno,
                       periods.lastdate_in_period,
                       SUM(CASE WHEN stockmoves.type IN (10, 11, 28)
                                 AND stockmoves.hidemovt = 0
                                 AND stockmoves.loccode = '" . $_GET['StockLocation'] . "'
                                 AND stockmoves.stockid = '" . $_GET['StockID'] . "'
                                THEN -stockmoves.qty ELSE 0 END) AS qtyused
                  FROM periods
             LEFT JOIN stockmoves ON periods.periodno = stockmoves.prd
             LEFT JOIN locationusers ON locationusers.loccode = stockmoves.loccode
                                    AND locationusers.userid = '" . $_SESSION['UserID'] . "'
                                    AND locationusers.canview = 1
                 WHERE periods.periodno > '" . ($CurrentPeriod - 24) . "'
              GROUP BY periods.periodno,
                       periods.lastdate_in_period
              ORDER BY periodno ASC
                 LIMIT 24";
    } else {
        $SQL = "SELECT periods.periodno,
                       periods.lastdate_in_period,
                       SUM(-stockmoves.qty) AS qtyused
                  FROM stockmoves
            INNER JOIN periods ON stockmoves.prd = periods.periodno
            INNER JOIN locationusers ON locationusers.loccode = stockmoves.loccode
                                    AND locationusers.userid = '" . $_SESSION['UserID'] . "'
                                    AND locationusers.canview = 1
                 WHERE stockmoves.type IN (10, 11, 28)
                   AND stockmoves.hidemovt = 0
                   AND stockmoves.loccode = '" . $_GET['StockLocation'] . "'
                   AND stockmoves.stockid = '" . trim(mb_strtoupper($_GET['StockID'])) . "'
              GROUP BY periods.periodno,
                       periods.lastdate_in_period
              ORDER BY periodno
                 LIMIT 24";
    }
}

$MovtsResult = DB_query($SQL);

if (DB_error_no() != 0) {
    $Title = _('Stock Usage Graph Problem');
    include ('includes/header.php');
    echo _('The stock usage for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg();
    if ($debug == 1) {
        echo '<br />' . _('The SQL that failed was') . $SQL;
    }
    include('includes/footer.php');
    exit;
}

if (DB_num_rows($MovtsResult) == 0) {
    $Title = _('Stock Usage Graph Problem');
    include ('includes/header.php');
    prnMsg(_('There are no movements of this item from the selected location to graph'),'info');
    include('includes/footer.php');
    exit;
}

$UsageArray = array();
$NumberOfPeriodsUsage = DB_num_rows($MovtsResult);

if ($NumberOfPeriodsUsage != 24) {
    $graph->SetDataColors(
        array("blue"),  //Data Colors
        array("black")  //Border Colors
    );

    for ($i = 1; $i <= $NumberOfPeriodsUsage; $i++) {
        $UsageRow = DB_fetch_array($MovtsResult);
        if (!$UsageRow) {
            break;
        } else {
            $UsageArray[] = array(MonthAndYearFromSQLDate($UsageRow['lastdate_in_period']),$UsageRow['qtyused']);
        }
    }
} else {
    $graph->SetDataColors(
        array("blue","red"),  //Data Colors
        array("black")    //Border Colors
    );

    for ($i = 1; $i <= 12; $i++) {
        $UsageRow = DB_fetch_array($MovtsResult);
        if (!$UsageRow) {
            break;
        }
        $UsageArray[] = array(MonthAndYearFromSQLDate($UsageRow['lastdate_in_period'], true), $UsageRow['qtyused']);
    }

    for ($i = 0; $i <= 11; $i++) {
        $UsageRow = DB_fetch_array($MovtsResult);
        if (!$UsageRow) {
            break;
        }
        $UsageArray[$i][0] = MonthAndYearFromSQLDate($UsageRow['lastdate_in_period'], true);
        $UsageArray[$i][2] = $UsageRow['qtyused'];
    }
}
//$graph->SetDrawXGrid(TRUE);
$graph->SetDataValues($UsageArray);
$graph->SetDataColors(
    array("blue","red"),  //Data Colors
    array("black")    //Border Colors
);

//Draw it
$graph->DrawGraph();
