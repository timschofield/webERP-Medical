<?php
$PageSecurity=1;

if (!isset($PathPrefix)) {
	$PathPrefix=$_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']) . '/../';
	$rootpath = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
}

include($PathPrefix . 'config.php');
require_once($PathPrefix . 'includes/session.inc');
if (isset($_GET['Category'])) {
	$Category=$_GET['Category'];
} else {
	$Category='%';
}
if (isset($_GET['identifier'])) {
	$identifier=$_GET['identifier'];
}
if (isset($_GET['Code'])) {
	$Code=$_GET['Code'];
} else {
	$Code='%';
}
if (isset($_GET['Description'])) {
	$Description=$_GET['Description'];
} else {
	$Description='%';
}

if (isset($_GET['MaxItems'])) {
	$MaxItems=$_GET['MaxItems'];
} else {
	$MaxItems=10;
}

function __autoload($Cart) {
	global $PathPrefix;
    include $PathPrefix . 'includes/DefineCartClass.php';
}

$db = mysqli_connect($host , $dbuser, $dbpassword,$_SESSION['DatabaseName'], $mysqlport);
$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.units,
				stockmaster.perishable,
				stockmaster.controlled,
				stockmaster.decimalplaces
			FROM stockmaster
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			WHERE ".$_SESSION['StockTypesSQL']."
				".$_SESSION['MBFlagSQL']."
				AND stockmaster.discontinued=0
				AND stockmaster.categoryid like '".$Category."'
				AND stockmaster.description like '%".$Description."%'
				AND stockmaster.stockid like '%".$Code."%'
			ORDER BY stockmaster.stockid
			LIMIT 0,".($MaxItems+1);
$SearchResult = mysqli_query($db, $SQL);

echo '<input type="hidden" name="identifier" value="' . $identifier . '" />';
echo '<table class="selection" width="98%" id="txtHint">
		<tr>
			<th>' . _('Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Units') . '</th>
			<th>' . _('On Hand') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('Price') . '</th>
			<th>' . _('Batch No') . '</th>
			<th>' . _('Batch Qty') . '</th>
			<th>' . _('Expiry Date') . '</th>
		</tr>';
$k=0;
$i=0;

while ($myrow=DB_fetch_array($SearchResult)) {

	$PricesSQL="SELECT price,
						units,
						conversionfactor,
						decimalplaces
					FROM prices
					WHERE stockid='".$myrow['stockid']."'
						AND typeabbrev='".$_SESSION['Items'.$identifier]->DefaultSalesType."'
						AND currabrev='".$_SESSION['Items'.$identifier]->DefaultCurrency."'
						AND '".date('Y-m-d')."' BETWEEN startdate and enddate";
	$PricesResult = DB_query($PricesSQL, $db);
	if (DB_num_rows($PricesResult)>0) {
		$PricesRow = DB_fetch_array($PricesResult);
		$myrow['price']=$PricesRow['price'];
		$myrow['units']=$PricesRow['units'];
		$myrow['conversionfactor']=$PricesRow['conversionfactor'];
	} else {
		$myrow['conversionfactor']=1;
		$myrow['price']=0;
	}

	// Find the quantity in stock at location
	$QOHSql = "SELECT sum(quantity) AS QOH
							FROM locstock
							WHERE locstock.stockid='" .$myrow['stockid'] . "'
								AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";
	$QOHResult =  DB_query($QOHSql,$db);
	$QOHRow = DB_fetch_array($QOHResult);
	$QOH = $QOHRow['QOH']/$myrow['conversionfactor'];

	$sql = "SELECT serialno,
					expirationdate,
					quantity
				FROM stockserialitems
				WHERE stockid='" . $myrow['stockid'] ."'
					AND loccode='" . $_SESSION['Items'.$identifier]->Location . "'";
	$ErrMsg = _('The batch details cannot be found');
	$BatchResult = DB_query($sql,$db,$ErrMsg);

	if ($myrow['controlled']==0) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<input type="hidden" name="StockID'.$i.'" value="'.$myrow['stockid'].'" />';
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td><font size="1"><input class="number" type="text" size="15" name="Quantity'.$i.'" value="0" /></font></td>
				<td class="number">%s</td>
			</tr>',
				$myrow['stockid'],
				$myrow['description'],
				$myrow['units'],
				locale_number_format($QOH, $myrow['decimalplaces']),
				locale_number_format($myrow['price'], 4));
		echo '<input type="hidden" name="Units' . $i . '" value="' . $myrow['units'] . '" />';
		$i++;
	} else {
		$LastStockID='';
		while ($BatchRow=DB_fetch_array($BatchResult)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			if ($LastStockID!=$myrow['stockid']) {
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td><font size="1"><input class="number" type="text" size="15" name="Quantity'.$i.'" value="0" /></font></td>
						<td class="number">%s</td>',
						$myrow['stockid'],
						$myrow['description'],
						$myrow['units'],
						locale_number_format($QOH, $myrow['decimalplaces']),
						locale_number_format($myrow['price'], 4));
			} else {
				echo '<td colspan="4"></td>';
				echo '<td><font size="1"><input class="number" type="text" size="15" name="Quantity'.$i.'" value="0" /></font></td>';
				echo '<td class="number">' . locale_number_format($myrow['price'], 4) . '</td>';
			}
			echo '<input type="hidden" name="Batch'.$i.'" value="'.$BatchRow['serialno'].'" />';
			echo '<input type="hidden" name="StockID'.$i.'" value="'.$myrow['stockid'].'" />';
			if ($myrow['perishable']==1) {
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
					</tr>',
						$BatchRow['serialno'],
						locale_number_format($BatchRow['quantity']/$myrow['conversionfactor'], $myrow['decimalplaces']),
						ConvertSQLDate($BatchRow['expirationdate']));
			} else {
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number"></td>
					</tr>',
						$BatchRow['serialno'],
						locale_number_format($BatchRow['quantity']/$myrow['conversionfactor'], $myrow['decimalplaces']),
						'');
			}
			$LastStockID=$myrow['stockid'];
			echo '<input type="hidden" name="Units' . $i . '" value="' . $myrow['units'] . '" />';
			$i++;
		}
	}
	#end of page full new headings if
}
#end of while loop
echo '<tr>
		<th colspan="9"><button type="submit" name="OrderItems">'._('Add to Sale').'</button></th>
	</tr>';
echo '</table>';

?>