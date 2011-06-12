<?php
/* $Revision: 1.11 $ */
/* $Id$*/
//$PageSecurity = 11;

include('includes/session.inc');

$result = DB_query("SELECT debtorsmaster.name,
							debtorsmaster.currcode,
							debtorsmaster.salestype
						 FROM
							debtorsmaster
						 WHERE
							debtorsmaster.debtorno='" . $_SESSION['CustomerID'] . "'",$db);
$myrow = DB_fetch_row($result);

$title = _('Special Prices for') . ' '. $myrow[0];

include('includes/header.inc');

if (isset($_GET['Item'])){
	$Item = $_GET['Item'];
}elseif (isset($_POST['Item'])){
	$Item = $_POST['Item'];
}

if (!isset($Item) OR !isset($_SESSION['CustomerID']) OR $_SESSION['CustomerID']==""){

	prnMsg( _('A customer must be selected from the customer selection screen') . ', '
		. _('then an item must be selected before this page is called') . '. '
			. _('The product selection page should call this page with a valid product code'),'info');
	echo '<br />';
	include('includes/footer.inc');
	exit;
}

$result = DB_query("SELECT debtorsmaster.name,
							debtorsmaster.currcode,
							debtorsmaster.salestype
						 FROM
							debtorsmaster
						 WHERE
							debtorsmaster.debtorno='" . $_SESSION['CustomerID'] . "'",$db);
$myrow = DB_fetch_row($result);
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . _('Special Customer Prices').'</p>';
echo '<p class="page_title_text"><font color=BLUE><b>' . $myrow[0] . ' ' . _('in') . ' ' . $myrow[1] . '<br />' . ' ' . _('for') . ' ';

$CurrCode = $myrow[1];
$SalesType = $myrow[2];

$result = DB_query("SELECT stockmaster.description,
							stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='" . $Item . "'",$db);

$myrow = DB_fetch_row($result);
if (DB_num_rows($result)==0){
	prnMsg( _('The part code entered does not exist in the database') . '. ' . _('Only valid parts can have prices entered against them'),'error');
	$InputError=1;
}
if ($myrow[1]=='K'){
	prnMsg(_('The part selected is a kit set item') .', ' . _('these items explode into their components when selected on an order') . ', ' . _('prices must be set up for the components and no price can be set for the whole kit'),'error');
	exit;
}

echo $Item . ' - ' . $myrow[0] . '</b></font></p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_double((double) trim($_POST['Price'])) OR $_POST['Price']=="") {
		$InputError = 1;
		$msg = _('The price entered must be numeric');
	}

	if ($_POST['Branch'] !=''){
		$sql = "SELECT custbranch.branchcode
						FROM custbranch
						WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'
						AND custbranch.branchcode='" . $_POST['Branch'] . "'";

		$result = DB_query($sql,$db);
		if (DB_num_rows($result) ==0){
			$InputError =1;
			$msg = _('The branch code entered is not currently defined');
		}
	}

	if (! Is_Date($_POST['StartDate'])){
		$InputError =1;
		$msg = _('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	}
	if (! Is_Date($_POST['EndDate']) AND $_POST['EndDate']!=''){ //EndDate can also be blank for default prices
		$InputError =1;
		$msg = _('The date this price is be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	}
	if (Date1GreaterThanDate2($_POST['StartDate'],$_POST['EndDate']) AND $_POST['EndDate']!=''){
		$InputError =1;
		$msg = _('The end date is expected to be after the start date, enter an end date after the start date for this price');
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['EndDate']) AND $_POST['EndDate']!=''){
		$InputError =1;
		$msg = _('The end date is expected to be after today. There is no point entering a new price where the effective date is before today!');
	}

	if ((isset($_POST['Editing']) and $_POST['Editing']=='Yes') AND strlen($Item)>1 AND $InputError !=1) {

		//editing an existing price

		$sql = "UPDATE prices SET typeabbrev='" . $SalesType . "',
		                          currabrev='" . $CurrCode . "',
								  price='" . $_POST['Price'] . "',
								  units='" . $_POST['Units'] . "',
								  conversionfactor='" . $_POST['ConversionFactor'] . "',
								  branchcode='" . $_POST['Branch'] . "',
								  startdate='" . FormatDateForSQL($_POST['StartDate']) . "',
								  enddate='" . FormatDateForSQL($_POST['EndDate']) . "'
				WHERE prices.stockid='" . $Item . "'
				AND prices.typeabbrev='" . $SalesType . "'
				AND prices.currabrev='" . $CurrCode . "'
				AND prices.startdate='" . $_POST['OldStartDate'] . "'
				AND prices.enddate='" . $_POST['OldEndDate'] . "'
				AND prices.debtorno='" . $_SESSION['CustomerID'] . "'";

		$msg = _('Price Updated');
	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */
		$sql = "INSERT INTO prices (stockid,
		                            typeabbrev,
									currabrev,
									debtorno,
									price,
									units,
									conversionfactor,
									branchcode,
									startdate,
									enddate)
							valueS ('".$Item."',
								'".$SalesType."',
								'".$CurrCode."',
								'" . $_SESSION['CustomerID'] . "',
								'" . $_POST['Price'] . "',
								'" . $_POST['Units'] . "',
								'" . $_POST['ConversionFactor'] . "',
								'" . $_POST['Branch'] . "',
								'" . FormatDateForSQL($_POST['StartDate']) . "',
								'" . FormatDateForSQL($_POST['EndDate']) . "'
							)";
		$msg = _('Price added') . '.';
	}
	//run the SQL from either of the above possibilites
	if ($InputError!=1){
		$result = DB_query($sql,$db,'','',false,false);
		if (DB_error_no($db)!=0){
		   If ($msg==_('Price Updated')){
				$msg = _('The price could not be updated because') . ' - ' . DB_error_msg($db);
			} else {
				$msg = _('The price could not be added because') . ' - ' . DB_error_msg($db);
			}
		}else {
			ReSequenceEffectiveDates ($Item, $SalesType, $CurrCode, $_SESSION['CustomerID'], $db);
			unset($_POST['EndDate']);
			unset($_POST['StartDate']);
			unset($_POST['Price']);
			unset($_POST['Units']);
			unset($_POST['ConversionFactor']);
		}
	}

	prnMsg($msg);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM prices
			WHERE prices.stockid = '". $Item ."'
			AND prices.typeabbrev='". $SalesType ."'
			AND prices.currabrev ='". $CurrCode ."'
			AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
			AND prices.branchcode='" . $_GET['Branch'] . "'
			AND prices.startdate='" . $_GET['StartDate'] . "'
			AND prices.enddate='" . $_GET['EndDate'] . "'";

	$result = DB_query($sql,$db);
	prnMsg( _('This price has been deleted') . '!','success');
}


//Always do this stuff
//Show the normal prices in the currency of this customer

$sql = "SELECT prices.price,
				prices.units,
				prices.conversionfactor,
				prices.currabrev,
               prices.typeabbrev,
               prices.startdate,
               prices.enddate
		FROM prices
		WHERE  prices.stockid='" . $Item . "'
		AND prices.typeabbrev='". $SalesType ."'
		AND prices.currabrev ='". $CurrCode ."'
		AND prices.debtorno=''
		ORDER BY currabrev,
						typeabbrev,
						startdate";

$ErrMsg = _('Could not retrieve the normal prices set up because');
$DbgMsg = _('The SQL used to retrieve these records was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

echo '<table><tr><td valign=top>';
echo '<table class=selection>';

if (DB_num_rows($result) == 0) {
	echo '<tr><td>' . _('There are no default prices set up for this part in this currency') . '</td></tr>';
} else {
	echo '<tr><th colspan=6><font color="navy" size="2">' . _('Normal Price') . '</font></th></tr>';
	echo '<tr><th>' . _('Price') . '</th>
		<th>'. _('UOM'). '</th>
		<th>'. _('Conversion') . '<br />'.
			_('Factor') . '</th>
		<th>' . _('Start Date') . '</th>
		<th>' . _('End Date') . '</th></tr>';
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['enddate']=='0000-00-00'){
			$EndDateDisplay = _('No End Date');
		} else {
			$EndDateDisplay = ConvertSQLDate($myrow['enddate']);
		}
		echo '<tr class="EvenTableRows">
						<td class=number>'.number_format($myrow['price'],2).'</td>
						<td>'.$myrow['units'].'</td>
						<td class=number>'.$myrow['conversionfactor'].'</td>
						<td class=date>'.ConvertSQLDate($myrow['startdate']).'</td>
						<td class=date>'.$EndDateDisplay.'</td></tr>';
	}
}

echo '</table></td><tr></tr></tr><tr><td valign=top>';

//now get the prices for the customer selected

$sql = "SELECT prices.price,
               prices.branchcode,
			   custbranch.brname,
			   prices.units,
			   prices.conversionfactor,
			   prices.startdate,
			   prices.enddate
		FROM prices
		LEFT JOIN custbranch ON prices.branchcode= custbranch.branchcode
		WHERE prices.typeabbrev = '".$SalesType."'
		AND prices.stockid='".$Item."'
		AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
		AND prices.currabrev='".$CurrCode."'
		AND (custbranch.debtorno='" . $_SESSION['CustomerID'] . "' OR
						custbranch.debtorno IS NULL)
		ORDER BY prices.branchcode,
							prices.startdate";

$ErrMsg = _('Could not retrieve the special prices set up because');
$DbgMsg = _('The SQL used to retrieve these records was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

echo '<table class=selection>';
echo '<tr><th colspan=8><font color="navy" size="2">' . _('Special Prices') . '</font></th></tr>';

if (DB_num_rows($result) == 0) {
	echo '<tr><td>' . _('There are no special prices set up for this part') . '</td></tr>';
} else {
/*THERE IS ALREADY A spl price setup */
	echo '<tr><th>' . _('Special Price') .
		'</th><th>' . _('Branch') . '</th>
		<th>'. _('UOM'). '</th>
		<th>'. _('Conversion') . '<br />'.
			_('Factor') . '</th>
		<th>' . _('Start Date') . '</th>
		<th>' . _('End Date') . '</th></tr>';

	while ($myrow = DB_fetch_array($result)) {

		if ($myrow['branchcode']==''){
			$Branch = _('All Branches');
		} else {
			$Branch = $myrow['brname'];
		}
		if ($myrow['enddate']=='0000-00-00'){
			$EndDateDisplay = _('No End Date');
		} else {
			$EndDateDisplay = ConvertSQLDate($myrow['enddate']);
		}
		echo '<tr bgcolor="#CCCCCC">
		<td class=number>'.number_format($myrow['price'],2).'</td>
		<td>'.$Branch.'</td>
		<td>'.$myrow['units'].'</td>
		<td class=number>'.$myrow['conversionfactor'].'</td>
		<td>'.ConvertSQLDate($myrow['startdate']).'</td>
		<td>'.$EndDateDisplay.'</td>
 		<td><a href="'.$_SERVER['PHP_SELF'].'?Item='.$Item.'&Price='.$myrow['price'].'&Branch='.$myrow['branchcode'].'&StartDate='.$myrow['startdate'].'&EndDate='.$myrow['enddate'].'&Edit=1">' . _('Edit') . '</td>
		<td><a href="'.$_SERVER['PHP_SELF'].'?Item='.$Item.'&Branch='.$myrow['branchcode'].'&StartDate='.$myrow['startdate'].'&EndDate='.$myrow['enddate'].'&delete=yes">' . _('Delete') . '</td></tr>';
	}
//END WHILE LIST LOOP
}

echo '</table></tr></table><p>';

echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type=hidden name="Item" value="' . $Item . '">';

if (isset($_GET['Edit']) and $_GET['Edit']==1){
	$sql = "SELECT currencies.currency,
				salestypes.sales_type,
				prices.debtorno,
				prices.branchcode,
				prices.units,
				prices.price,
				prices.conversionfactor,
				prices.stockid,
				prices.typeabbrev,
				prices.currabrev,
				prices.startdate,
				prices.enddate
			FROM prices,
				salestypes,
				currencies
			WHERE prices.currabrev=currencies.currabrev
				AND prices.typeabbrev = salestypes.typeabbrev
				AND prices.stockid='".$Item."'
				AND prices.debtorno='".$_SESSION['CustomerID']."'
				AND prices.branchcode='".$_GET['Branch']."'
				AND prices.startdate='". $_GET['StartDate'] . "'
				AND prices.enddate='". $_GET['EndDate'] . "'
			ORDER BY prices.currabrev,
				prices.typeabbrev,
				prices.startdate";

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	echo '<input type=hidden name="Editing" value="Yes">';
	echo '<input type=hidden name="OldTypeAbbrev" value="' . $myrow['typeabbrev'] .'">';
	echo '<input type=hidden name="OldCurrAbrev" value="' . $myrow['currabrev'] . '">';
	echo '<input type=hidden name="OldStartDate" value="' . $myrow['startdate'] . '">';
	echo '<input type=hidden name="OldEndDate" value="' . $myrow['enddate'] . '">';
	$_POST['Branch']=$myrow['branchcode'];
	$_POST['CurrAbrev'] = $myrow['currabrev'];
	$_POST['TypeAbbrev'] = $myrow['typeabbrev'];
	$_POST['Price'] = $myrow['price'];
	$_POST['Units'] = $myrow['units'];
	$_POST['ConversionFactor'] = $myrow['conversionfactor'];
	$_POST['StartDate'] = ConvertSQLDate($myrow['startdate']);
	if ($_GET['EndDate']=='' OR $_GET['EndDate']=='0000-00-00'){
		$_POST['EndDate'] = '';
	} else {
		$_POST['EndDate'] = ConvertSQLDate($myrow['enddate']);
	}
}
if (!isset($_POST['Branch'])) {
	$_POST['Branch']='';
}
if (!isset($_POST['Price'])) {
	$_POST['Price']=0;
}

if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['EndDate'])){
	$_POST['EndDate'] = DateAdd(Date($_SESSION['DefaultDateFormat']), 'y', 1);
}

$sql = "SELECT custbranch.branchcode,
					custbranch.brname,
					currencies.currency
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					LEFT JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
					WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'";

$result = DB_query($sql, $db);
echo '<table class=selection>';
echo '<tr><td>' . _('Branch') . ':</td>';
echo '<td><select name="Branch">';
while ($myrow=DB_fetch_array($result)) {
	$CustomerCurrency=$myrow['currency'];
	if ($myrow['branchcode']==$_POST['branch']) {
		echo '<option selected value='.$myrow['branchcode'].'>'.$myrow['brname'].'</option>';
	} else {
		echo '<option value='.$myrow['branchcode'].'>'.$myrow['brname'].'</option>';
	}
}
echo '</td></tr>';
echo '<tr><td>' . _('Currency') .':</td><td>' . $CustomerCurrency.'</td></tr>';
echo '<tr><td>' . _('Price Effective From Date') . ':</td>
	                         <td><input type="Text" name="StartDate" class=date alt='.$_SESSION['DefaultDateFormat'].
	                         ' size=11 maxlength=10 value=' . $_POST['StartDate'] . '></td></tr>';
echo '<tr><td>' . _('Price Effective To Date') . ':</td>
	                         <td><input type="Text" name="EndDate" class=date alt='.$_SESSION['DefaultDateFormat'].
	                         ' size=11 maxlength=10 value=' . $_POST['EndDate'] . '></td></tr>';
echo '<tr><td>' . _('Unit of Measure') . ':</td>';
echo '<td><select name="Units">';
$sql = "SELECT unitname FROM unitsofmeasure";
$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result)) {
	if ($_POST['Units'] == $myrow['unitname']) {
		echo '<option selected value="' . $myrow['unitname'] . '">' . $myrow['unitname'] . '</option>';
	} else if ($DefaultUOM == $myrow['unitname'] and ($_POST['Units'] != $myrow['unitname'])) {
		echo '<option selected value="' . $myrow['unitname'] . '">' . $myrow['unitname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['unitname'] . '">' . $myrow['unitname'] . '</option>';
	}
}
echo '</td></tr>';
echo '<input type=hidden name=Item value='.$Item.'>';

echo '</select></td></tr>';

echo '<tr><td>'. _('Conversion Factor') . '<br />'._('to stock units').'</td>';
echo '<td><input type="text" class=number name="ConversionFactor" size=8 maxlength=8 value="';
if(isset($_POST['ConversionFactor'])) {
	echo $_POST['ConversionFactor'];
} else {
	echo '1';
}
echo '">';

echo '<tr><td>' . _('Price') . ':</td>
	          <td><input type="Text" class=number name="Price" size=11 maxlength=10 value=' . $_POST['Price'] . '></td>
				</tr></table>';


echo '<br /><div class="centre"><input type="Submit" name="submit" value="' . _('Enter Information') . '"></div>';

echo '</form>';
include('includes/footer.inc');

function ReSequenceEffectiveDates ($Item, $PriceList, $CurrAbbrev, $CustomerID, $db) {

	/*This is quite complicated - the idea is that prices set up should be unique and there is no way two prices could be returned as valid - when getting a price in includes/GetPrice.inc the logic is to first look for a price of the salestype/currency within the effective start and end dates - then if not get the price with a start date prior but a blank end date (the default price). We would not want two prices where the effective dates fall between an existing price so it is necessary to update enddates of prices  - with me - I am just hanging on here myself
	 Prices with no end date are default prices and need to be ignored in this resquence*/

	$SQL = "SELECT branchcode,
						startdate,
						enddate
						FROM prices
						WHERE debtorno='" . $CustomerID . "'
						AND stockid='" . $Item . "'
						AND currabrev='" . $CurrAbbrev . "'
						AND typeabbrev='" . $PriceList . "'
						AND enddate<>''
						ORDER BY
						branchcode,
						startdate,
						enddate";

	$result = DB_query($SQL,$db);

	unset($BranchCode);

	while ($myrow = DB_fetch_array($result)){
		if (!isset($BranchCode)){
			unset($NextDefaultStartDate); //a price with a blank end date
			unset($NextStartDate);
			unset($EndDate);
			unset($StartDate);
			$BranchCode = $myrow['branchcode'];
		}
		if (isset($NextStartDate)){
			if (Date1GreaterThanDate2(ConvertSQLDate($myrow['startdate']),$NextStartDate)){
				$NextStartDate = ConvertSQLDate($myrow['startdate']);
				if (Date1GreaterThanDate2(ConvertSQLDate($EndDate),ConvertSQLDate($myrow['startdate']))) {
					/*Need to make the end date the new start date less 1 day */
					$SQL = "UPDATE prices SET enddate = '" . FormatDateForSQL(DateAdd($NextStartDate,'d',-1))  . "'
										WHERE stockid ='" .$Item . "'
										AND currabrev='" . $CurrAbbrev . "'
										AND typeabbrev='" . $PriceList . "'
										AND startdate ='" . $StartDate . "'
										AND enddate = '" . $EndDate . "'
										AND debtorno ='" . $CustomerID . "'";
						$UpdateResult = DB_query($SQL,$db);
				}
			} //end of if startdate  after NextStartDate - we have a new NextStartDate
		} //end of if set NextStartDate
			else {
				$NextStartDate = ConvertSQLDate($myrow['startdate']);
		}
		$StartDate = $myrow['startdate'];
		$EndDate = $myrow['enddate'];
	}
}
?>