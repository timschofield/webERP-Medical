<?php
// MRPCreateDemands.php - Create mrpdemands based on sales order history

include('includes/session.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};
if (isset($_POST['DistDate'])){$_POST['DistDate'] = ConvertSQLDate($_POST['DistDate']);};

$Title = _('MRP Create Demands');
$ViewTopic= 'MRP';
$BookMark = 'MRP_MasterSchedule';
include('includes/header.php');

if (isset($_POST['submit'])) {
   // Create mrpdemands based on sales order history

	$InputError=0;

	if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
		$msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
		$InputError=1;
		unset($_POST['FromDate']);
	}
	if (isset($_POST['ToDate']) AND !Is_Date($_POST['ToDate'])){
		$msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
		$InputError=1;
		unset($_POST['ToDate']);
	}
	if (isset($_POST['FromDate']) and isset($_POST['ToDate']) and
		 Date1GreaterThanDate2($_POST['FromDate'], $_POST['ToDate'])){
			$msg = _('The date to must be after the date from');
			$InputError=1;
			unset($_POST['ToDate']);
			unset($_POST['FromoDate']);
	}
	if (isset($_POST['DistDate']) AND !Is_Date($_POST['DistDate'])){
		$msg = _('The distribution start date must be specified in the format') . ' ' .  $_SESSION['DefaultDateFormat'];
		$InputError=1;
		unset($_POST['DistDate']);
	}
	if (!is_numeric(filter_number_format($_POST['ExcludeQuantity']))){
		$msg = _('The quantity below which no demand will be created must be numeric');
		$InputError=1;
	}
	if (!is_numeric(filter_number_format($_POST['Multiplier']))){
		$msg = _('The multiplier is expected to be a positive number');
		$InputError=1;
	}

	if ($InputError==1){
		prnMsg($msg,'error');
	}

	$WhereLocation = " ";
	if ($_POST['Location']!='All') {
		$WhereLocation = " AND salesorders.fromstkloc ='" . $_POST['Location'] . "' ";
	}

	$sql= "SELECT salesorderdetails.stkcode,
				  SUM(salesorderdetails.quantity) AS totqty,
				  SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced,
				  SUM(salesorderdetails.quantity * salesorderdetails.unitprice ) AS totextqty
			FROM salesorders INNER JOIN salesorderdetails
				 ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			INNER JOIN stockmaster
				 ON salesorderdetails.stkcode = stockmaster.stockid
			WHERE orddate >='" . FormatDateForSQL($_POST['FromDate']) ."'
			AND orddate <='" . FormatDateForSQL($_POST['ToDate']) .  "'
			" . $WhereLocation . "
			AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
			AND stockmaster.discontinued = 0
			AND salesorders.quotation=0
			GROUP BY salesorderdetails.stkcode";
	//echo "<br />$sql<br />";
	$result = DB_query($sql);
	// To get the quantity per period, get the whole number amount of the total quantity divided
	// by the number of periods and also get the remainder from that calculation. Put the whole
	// number quantity into each entry of the periodqty array, and add 1 to the periodqty array
	// until the remainder number is used up. Then create an mrpdemands records for everything
	// in the array

	if (filter_number_format($_POST['Multiplier']) < 1) {
		$Multiplier = 1;
	} else {
		$Multiplier = filter_number_format($_POST['Multiplier']);
	}

	if ($_POST['ExcludeQuantity'] < 1) {
		$ExcludeQty = 1;
	} else {
		$ExcludeQty = filter_number_format($_POST['ExcludeQuantity']);
	}

	if ($_POST['ExcludeAmount'] < 1) {
		$ExcludeAmount = 0;
	} else {
		$ExcludeAmount = filter_number_format($_POST['ExcludeAmount']);
	}

	// Create array of dates based on DistDate and adding either weeks or months
	$FormatedDistdate = FormatDateForSQL($_POST['DistDate']);
	if (mb_strpos($FormatedDistdate,"/")) {
		list($yyyy,$mm,$dd) = explode("/",$FormatedDistdate);
	} else if (mb_strpos($FormatedDistdate,"-")) {
		list($yyyy,$mm,$dd) = explode("-",$FormatedDistdate);
	} else if (mb_strpos($FormatedDistdate,".")) {
		list($yyyy,$mm,$dd) = explode(".",$FormatedDistdate);
	}

	$datearray[0] = $FormatedDistdate;
	// Set first date to valid manufacturing date
	$calendarsql = "SELECT COUNT(*),cal2.calendardate
					  FROM mrpcalendar
						LEFT JOIN mrpcalendar as cal2
						  ON mrpcalendar.daynumber = cal2.daynumber
					  WHERE mrpcalendar.calendardate = '".$datearray[0]."'
						AND cal2.manufacturingflag='1'
						GROUP BY cal2.calendardate";
	$resultdate = DB_query($calendarsql);
	$myrowdate=DB_fetch_array($resultdate);
	// If find date based on manufacturing calendar, change date in array
	if ($myrowdate[0] != 0){
		$datearray[0] = $myrowdate[1];
	}

	$date = date('Y-m-d',mktime(0,0,0,$mm,$dd,$yyyy));
	for ($i = 1; $i <= ( $_POST['PeriodNumber'] - 1); $i++) {
		if ($_POST['Period'] == 'weekly') {
			$date = strtotime(date('Y-m-d', strtotime($date)) . ' + 1 week');
		} else {
			$date = strtotime(date('Y-m-d', strtotime($date)) . ' + 1 month');
		}
		$datearray[$i] = date('Y-m-d',$date);
		// Following sql finds daynumber for the calculated date and finds
		// a valid manufacturing date for the daynumber. There is only one valid manufacturing date
		// for each daynumber, but there could be several non-manufacturing dates for the
		// same daynumber. MRPCalendar.php maintains the manufacturing calendar.
		$calendarsql = "SELECT COUNT(*),cal2.calendardate
						  FROM mrpcalendar
							LEFT JOIN mrpcalendar as cal2
							  ON mrpcalendar.daynumber = cal2.daynumber
						  WHERE mrpcalendar.calendardate = '".$datearray[$i]."'
							AND cal2.manufacturingflag='1'
							GROUP BY cal2.calendardate";
		$resultdate = DB_query($calendarsql);
		$myrowdate=DB_fetch_array($resultdate);
		// If find date based on manufacturing calendar, change date in array
		if ($myrowdate[0] != 0){
			$datearray[$i] = $myrowdate[1];
		}
		$date = date('Y-m-d',$date);
	}

	$TotalRecords = 0;
	while ($myrow = DB_fetch_array($result)) {
		if (($myrow['totqty'] >= $ExcludeQty) AND ($myrow['totextqty'] >= $ExcludeAmount)) {
			unset($PeriodQty);
			$PeriodQty[] = ' ';
			$TotalQty = $myrow['totqtyinvoiced'] * $Multiplier;
			$WholeNumber = floor($TotalQty / $_POST['PeriodNumber']);
			$Remainder = ($TotalQty % $_POST['PeriodNumber']);
			if ($WholeNumber > 0) {
				for ($i = 0; $i <= ($_POST['PeriodNumber'] - 1); $i++) {
					$PeriodQty[$i] = $WholeNumber;
				}
			}
			if ($Remainder > 0) {
				for ($i = 0; $i <= ($Remainder - 1); $i++) {
					$PeriodQty[$i] += 1;
				}
			}

			$i = 0;
			foreach ($PeriodQty as $demandqty) {
					$sql = "INSERT INTO mrpdemands (stockid,
									mrpdemandtype,
									quantity,
									duedate)
								VALUES ('" . $myrow['stkcode'] . "',
									'" . $_POST['MRPDemandtype'] . "',
									'" . $demandqty . "',
									'" . $datearray[$i] . "')";
					$insertresult = DB_query($sql);
					$i++;
					$TotalRecords++;

			} // end of foreach for INSERT
		} // end of if that checks exludeqty, ExcludeAmount

	} //end while loop

	prnMsg( $TotalRecords . ' ' . _('records have been created'),'success');

} // end if submit has been pressed

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
	_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<field>
			<label for="MRPDemandtype">' . _('Demand Type') . ':</label>
			<select name="MRPDemandtype">';
$sql = "SELECT mrpdemandtype,
				description
		FROM mrpdemandtypes";
$result = DB_query($sql);
while ($myrow = DB_fetch_array($result)) {
	 echo '<option value="' . $myrow['mrpdemandtype'] . '">' . $myrow['mrpdemandtype'] . ' - ' .$myrow['description'] . '</option>';
} //end while loop
echo '</select>
	</field>';

echo '<field>
		<label for="Categories">' . _('Inventory Categories') . ':</label>
		<select autofocus="autofocus" required="required" minlength="1" name="Categories[] "multiple="multiple">';
	$SQL = 'SELECT categoryid, categorydescription
			FROM stockcategory
			ORDER BY categorydescription';
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

echo '<field>
		<label for="Location">' . _('Inventory Location') . ':</label>
		<select name="Location">';
echo '<option selected="selected" value="All">' . _('All Locations') . '</option>';

$result= DB_query("SELECT locations.loccode,
						   locationname
					FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1");
while ($myrow=DB_fetch_array($result)){
	echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
}
echo '</select>
	</field>';
if (!isset($_POST['FromDate'])) {
	$_POST['FromDate']=date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['ToDate'])) {
	$_POST['ToDate']=date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['DistDate'])) {
	$_POST['DistDate']=date($_SESSION['DefaultDateFormat']);
}
echo '<field>
		<label for="FromDate">' . _('From Sales Date') . ':</label>
		<input type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
	</field>
	<field>
		<label for="ToDate">'. _('To Sales Date') . ':</label>
		<input type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
	</field>
	<field>
		<label for="DistDate">' . _('Start Date For Distribution') . ':</label>
		<input type="date" name="DistDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['DistDate']) . '" />
	</field>
	<field>
		<label for="Period">' . _('Distribution Period') . ':</label>
		<select name="Period">
			<option selected="selected" value="weekly">' . _('Weekly') . '</option>
			<option value="monthly">' . _('Monthly')  . '</option>
		</select>
	</field>
	<field>
		<label for="PeriodNumber">' . _('Number of Periods') .':</label>
		<input type ="text" class="number" name="PeriodNumber" size="4" value="1" />
	</field>
	<field>
		<label for="ExcludeQuantity">' . _('Exclude Total Quantity Less Than') . ':</label>
		<input type ="text" class="number" name="ExcludeQuantity" size="4" value="1" />
    </field>
	<field>
		<label for="ExcludeAmount">' . _('Exclude Total Dollars Less Than') . ':</label>
		<input type ="text" class="number" name="ExcludeAmount" size="8" value="0" />
	</field>
	<field>
		<label for="Multiplier">' . _('Multiplier') .':</label>
		<input type="text" class="number" name="Multiplier" size="2" value="1" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Submit') .  '" />
	</div>';
echo '</form>';

include('includes/footer.php');
?>