<?php
//	PDFPriceList.php
//	Script to print a price list by inventory category.

/*
Output column sizes:
	* stockmaster.stockid, varchar(20), len = 20chr
	* stockmaster.description, varchar(50), len = 50chr
	* prices.startdate, date, len = 10chr
	* prices.enddate, date/'No End Date', len = 12chr
	* custbranch.brname, varchar(40), len = 40chr
	* Gross Profit, calculated, len = 8chr
	* prices.price, decimal(20,4), len = 20chr + 4spaces
Please note that addTextWrap() YPos is a font-size-height further down than addText() and other functions. Use addText() instead of addTextWrap() to print left aligned elements.
All coordinates are measured from the lower left corner of the sheet to the top left corner of the element.
*/
include('includes/session.php');
if (isset($_POST['EffectiveDate'])){$_POST['EffectiveDate'] = ConvertSQLDate($_POST['EffectiveDate']);};
use Dompdf\Dompdf;

// Merges gets into posts:
if (isset($_GET['ShowObsolete'])) {// Option to show obsolete items.
	$_POST['ShowObsolete'] = $_GET['ShowObsolete'];
}
if (isset($_GET['ItemOrder'])) {// Option to select the order of the items in the report.
	$_POST['ItemOrder'] = $_GET['ItemOrder'];
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$WhereCurrency = '';
	if ($_POST['Currency'] != "All") {
		$WhereCurrency = " AND prices.currabrev = '" . $_POST['Currency'] ."' ";// Query element to select a currency.
	}
	// Option to show obsolete items:
	$ShowObsolete = ' AND `stockmaster`.`discontinued` != 1 ';// Query element to exclude obsolete items.
	if (isset($_POST['ShowObsolete'])) {
		$ShowObsolete = '';// Cleans the query element to exclude obsolete items.
	}
	// Option to select the order of the items in the report:
	$ItemOrder = 'stockmaster.stockid';// Query element to sort by currency, item_stock_category, and item_code.
	if ($_POST['ItemOrder'] == 'Description') {
		$ItemOrder = 'stockmaster.description';// Query element to sort by currency, item_stock_category, and item_description.
	}

	$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
	$SalesTypeResult = DB_query($SQL);
	$SalesTypeRow = DB_fetch_row($SalesTypeResult);
	$SalesTypeName = $SalesTypeRow[0];

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {

		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}
	$HTML .= '<meta name="author" content="WebERP " . $Version">
				<meta name="Creator" content="webERP http://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . _('Prices By Inventory Category') . '<br />
					' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . _('Price List') . ' - ' . $_POST['SalesType'] . ' - ' . $SalesTypeName . '<br />
					' . _('Effective as at') . ' - ' . $_POST['EffectiveDate'] . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . _('Item Code') . '</th>
							<th>' . _('Item Description') . '</th>
							<th colspan="2">' . _('Effective Date Range') . '</th>';

	if ($_POST['CustomerSpecials']=='Customer Special Prices Only') {
		$HTML .= '<th>' .  _('Branch') . '</th>';
	}
	if ($_POST['ShowGPPercentages']=='Yes') {
		$HTML .= '<th>' . _('Gross Profit') . '</th>';
	}

	$HTML .= '<th>' . _('Price') . '</th>
			</tr>
		</thead>
	<tbody>';

	$HTML .= '<tr>
				<td colspan="4">*' . _('Prices excluding tax') . '</td>
			</tr>';

	/*Now figure out the inventory data to report for the category range under review */
	if ($_POST['CustomerSpecials']==_('Customer Special Prices Only')) {

		if ($_SESSION['CustomerID']=='') {
			$Title = _('Special price List - No Customer Selected');
			$ViewTopic = 'SalesTypes';// Filename in ManualContents.php's TOC.
			$BookMark = 'PDFPriceList';// Anchor's id in the manual's html document.
			include('includes/header.php');
			echo '<br />';
			prnMsg( _('The customer must first be selected from the select customer link') . '. ' . _('Re-run the price list once the customer has been selected') );
			echo '<br /><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Back') . '</a>';
			include('includes/footer.php');
			exit;
		}
		if (!Is_Date($_POST['EffectiveDate'])) {
			$Title = _('Special price List - No Customer Selected');
			$ViewTopic = 'SalesTypes';// Filename in ManualContents.php's TOC.
			$BookMark = 'PDFPriceList';// Anchor's id in the manual's html document.
			include('includes/header.php');
			prnMsg(_('The effective date must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
			echo '<br /><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Back') . '</a>';
			include('includes/footer.php');
			exit;
		}

		$SQL = "SELECT
					debtorsmaster.name,
					debtorsmaster.salestype
				FROM debtorsmaster
				WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
		$CustNameResult = DB_query($SQL);
		$CustNameRow = DB_fetch_row($CustNameResult);
		$CustomerName = $CustNameRow[0];
		$SalesType = $CustNameRow[1];
		$SQL = "SELECT
					prices.typeabbrev,
					prices.stockid,
					stockmaster.description,
					stockmaster.longdescription,
					prices.currabrev,
					prices.startdate,
					prices.enddate,
					prices.price,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					prices.debtorno,
					prices.branchcode,
					custbranch.brname,
					currencies.decimalplaces
				FROM stockmaster
					INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN prices ON stockmaster.stockid=prices.stockid
					INNER JOIN currencies ON prices.currabrev=currencies.currabrev
					LEFT JOIN custbranch ON prices.debtorno=custbranch.debtorno AND prices.branchcode=custbranch.branchcode
				WHERE prices.typeabbrev = '" . $SalesType . "'
					AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
					AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
					AND (prices.enddate='0000-00-00' OR prices.enddate >'" . FormatDateForSQL($_POST['EffectiveDate']) . "')" .
					$WhereCurrency .
					$ShowObsolete . "
				ORDER BY
					prices.currabrev,
					stockcategory.categorydescription," .
					$ItemOrder;

	} else { /* the sales type list only */

		$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];

		$SQL = "SELECT
					prices.typeabbrev,
					prices.stockid,
					prices.startdate,
					prices.enddate,
					stockmaster.description,
					stockmaster.longdescription,
					prices.currabrev,
					prices.price,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost as standardcost,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					currencies.decimalplaces
				FROM stockmaster
					INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN prices ON stockmaster.stockid=prices.stockid
					INNER JOIN currencies ON prices.currabrev=currencies.currabrev
				WHERE stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					AND prices.typeabbrev='" . $_POST['SalesType'] . "'
					AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
					AND (prices.enddate='0000-00-00' OR prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "')" .
					$WhereCurrency .
					$ShowObsolete . "
					AND prices.debtorno LIKE '%%'
				ORDER BY
					prices.currabrev,
					stockcategory.categorydescription," .
					$ItemOrder;
	}
	$PricesResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Price List') . ' - ' . _('Problem Report....');
		include('includes/header.php');
		prnMsg( _('The Price List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu'). '</a>';
		if ($debug==1) {
			prnMsg(_('For debugging purposes the SQL used was:') . $SQL,'error');
		}
		include('includes/footer.php');
		exit;
	}
	if (DB_num_rows($PricesResult)==0) {
		$Title = _('Print Price List Error');
		include('includes/header.php');
		prnMsg(_('There were no price details to print out for the customer or category specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Back') . '</a>';
		include('includes/footer.php');
		exit;
	}

	$CurrCode ='';
	$Category = '';
	$CatTot_Val=0;

	require_once('includes/CurrenciesArray.php');// To get the currency name from the currency code.

	while ($PriceList = DB_fetch_array($PricesResult)) {

		if ($Category != $PriceList['categoryid']) {
			$HTML .= '<tr>
						<th colspan="6">' . $PriceList['categoryid'] . ' - ' . $PriceList['categorydescription'] . '</th>
					</tr>';
			$Category = $PriceList['categoryid'];
		}

		if ($CurrCode != $PriceList['currabrev']) {
			$HTML .= '<tr>
						<th colspan="6">' . $PriceList['currabrev'] . ' - ' . _($CurrencyName[$PriceList['currabrev']]) . '</th>
					</tr>';
			$CurrCode = $PriceList['currabrev'];
		}

		$FontSize = 8;

		if ($PriceList['enddate']!='0000-00-00') {
			$DisplayEndDate = ConvertSQLDate($PriceList['enddate']);
		} else {
			$DisplayEndDate = _('No End Date');
		}

		$HTML .= '<tr>
					<td>' . $PriceList['stockid'] . '</td>
					<td>' . $PriceList['description'] . '</td>
					<td>' . ConvertSQLDate($PriceList['startdate']) . '</td>
					<td>' . $DisplayEndDate . '</td>';

		if ($_POST['CustomerSpecials']=='Customer Special Prices Only') {
			/*Need to show to which branch the price relates */
			if ($PriceList['branchcode']!='') {
				$HTML .= '<td>' . $PriceList['brname'] . '</td>';;
			} else {
				$HTML .= '<td>' . _('All') . '</td>';;
			}

		} elseif ($_POST['CustomerSpecials']=='Full Description') {
			$YPos -= $FontSize;

			// Prints item image:
			$SupportedImgExt = array('png','jpg','jpeg');
            $glob = (glob($_SESSION['part_pics_dir'] . '/' . $PriceList['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
			$imagefile = reset($glob);
			$YPosImage = $YPos;// Initializes the image bottom $YPos.
			if (file_exists($imagefile) ) {
				if ($YPos-36 < $Bottom_Margin) {// If the image bottom reaches the bottom margin, do PageHeader().
					PageHeader();
				}
				$LeftOvers = $pdf->Image($imagefile,$Left_Margin+3, $Page_Height-$YPos, 36, 36);
				$YPosImage = $YPos-36;// Stores the $YPos of the image bottom (see bottom).
			}
			// Prints stockmaster.longdescription:
			$XPos = $Left_Margin+80;// Takes out this calculation from the loop.
			$Width = $Page_Width-$Left_Margin-$Right_Margin-$XPos;// Takes out this calculation from the loop.
			$FontSize2 = $FontSize*0.80;// Font size and line height of Full Description section.
			PrintDetail($pdf,$PriceList['longdescription'],$Bottom_Margin,$XPos,$YPos,$Width,$FontSize2,'PageHeader',null, 'j', 0, $fill);

			// Assigns to $YPos the lowest $YPos value between the image and the description:
			$YPos = min($YPosImage, $YPos);
			$YPos -= $FontSize;// Jumps additional line after the image and the description.
		}
		// Shows gross profit percentage:
		if ($_POST['ShowGPPercentages']=='Yes') {
			$DisplayGPPercent = '-';
			if ($PriceList['price']!=0) {
				$DisplayGPPercent = locale_number_format((($PriceList['price']-$PriceList['standardcost'])*100/$PriceList['price']), 2) . '%';
			}
			$HTML .= '<td class="number">' . $DisplayGPPercent . '</td>';
		}

		// Displays unit price:
		$HTML .= '<td class="number">' . locale_number_format($PriceList['price'],$PriceList['decimalplaces']) . '</td></tr>';

	} /*end inventory valn while loop */

	// Warns if obsolete items are included:
	if (isset($_POST['ShowObsolete'])) {
		$HTML .= '<tr>
					<td colspan="4">' . _('* Obsolete items included.') . '</td>
				</tr>';
	}

	$FontSize = 10;
	$FileName = $_SESSION['DatabaseName'] . '_' . _('Price_List') . '_' . date('Y-m-d') . '.pdf';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('Prices By Inventory Category');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Prices By Inventory Category') . '" alt="" />' . ' ' . _('Prices By Inventory Category') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}


} else { /*The option to print PDF was not hit */
	$Title = _('Price Listing');
	$ViewTopic = 'SalesTypes';
	$BookMark = 'PDFPriceList';
	include('includes/header.php');

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/customer.png" title="', // Icon image.
		_('Price List'), '" /> ', // Icon title.
		_('Print a price list by inventory category'), '</p>';// Page title.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="Categories">', _('Select Inventory Categories'), ':</label>
			<select autofocus="autofocus" id="Categories" minlength="1" multiple="multiple" name="Categories[]" required="required">';
	$SQL = "SELECT categoryid, categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		echo '<option' ;
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo ' selected="selected"';
		}
		echo ' value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SalesType">', _('For Sales Type/Price List'), ':</label>
			<select name="SalesType">';
	$sql = "SELECT sales_type, typeabbrev FROM salestypes";
	$SalesTypesResult=DB_query($sql);

	while ($myrow=DB_fetch_array($SalesTypesResult)) {
		echo '<option value="', $myrow['typeabbrev'], '">', $myrow['sales_type'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Currency">', _('For Currency'), ':</label>
			<select name="Currency">';
	$sql = "SELECT currabrev, currency FROM currencies ORDER BY currency";
	$CurrencyResult=DB_query($sql);
	echo '<option selected="selected" value="All">', _('All'), '</option>';
	while ($myrow=DB_fetch_array($CurrencyResult)) {
		echo '<option value="', $myrow['currabrev'], '">', $myrow['currency'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowGPPercentages">', _('Show Gross Profit %'), ':</label>
			<select name="ShowGPPercentages">
				<option selected="selected" value="No">', _('Prices Only'), '</option>
				<option value="Yes">', _('Show GP % too'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="CustomerSpecials">', _('Price Listing Type'), ':</label>
			<select name="CustomerSpecials">
				<option selected="selected" value="Sales Type Prices">', _('Default Sales Type Prices'), '</option>
				<option value="Customer Special Prices Only">', _('Customer Special Prices Only'), '</option>
				<option value="Full Description">', _('Full Description'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="EffectiveDate">', _('Effective As At'), ':</label>
			<input required="required" maxlength="10" size="11" type="date" name="EffectiveDate" value="', Date('Y-m-d'), '" />
		</field>';

	// Option to show obsolete items:
	if (isset($_POST['ShowObsolete'])) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowObsolete">', _('Show obsolete items'), ':</label>
			<input',$Checked, ' id="ShowObsolete" name="ShowObsolete" type="checkbox" />
			<fieldhelp>', _('Check this box to show the obsolete items'), '</fieldhelp>
		</field>';

	// Option to select the order of the items in the report:
	echo '<fieldset>
			<legend>', _('Sort items by'), ':</legend>
		<field>
	 		<label>', _('Currency, category and code'), '</label>
	 		<input checked="checked" id="ItemOrder" name="ItemOrder" type="radio" value="Code" />
		</field>
		<field>
			<label>', _('Currency, category and description'), '</label>
			<input name="ItemOrder" type="radio" value="Description" />
		</field>
		</fieldset>',

		'</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('PDF Price List') . '" />
				<input type="submit" name="View" title="View" value="' . _('View Price List') . '" />
			</div>
	</form>';

	include('includes/footer.php');
} /*end of else not PrintPDF */
// END: Procedure division -----------------------------------------------------

?>