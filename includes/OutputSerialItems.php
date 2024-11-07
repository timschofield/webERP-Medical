<?php
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php

*/

//we start with a batch or serial no header and need to display something for verification...

include ('includes/Add_SerialItemsOut.php');

global $tableheader;
/* Link to clear the list and start from scratch */
$EditLink =  '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?EditControlled=true&StockID=' . $LineItem->StockID .
	'&LineNo=' . $LineNo .'">' .  _('Edit'). '</a> | ';
$RemoveLink = '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?DELETEALL=YES&StockID=' . $LineItem->StockID .
	'&LineNo=' . $LineNo .'">' .  _('Remove All'). '</a><br /></div>';
$sql="SELECT perishable
		FROM stockmaster
		WHERE stockid='".$StockID."'";
$result=DB_query($sql);
$myrow=DB_fetch_array($result);
$Perishable=$myrow['perishable'];
if ($LineItem->Serialised==1){
	$tableheader .= '<tr>
						<th>' .  _('Serial No') . '</th>
					</tr>';
	$listtableheader=$tableheader;
} else if ($LineItem->Serialised==0 and $Perishable==1){
	$tableheader = '<tr>
						<th>' .  _('Batch/Roll/Bundle'). ' #</th>
						<th>' .  _('Available'). '</th>
						<th>' .  _('Quantity'). '</th>
						<th>' .  _('Expiry Date'). '</th>
					</tr>';
	$listtableheader = '<tr>
							<th>' .  _('Batch/Roll/Bundle'). ' #</th>
							<th>' .  _('Quantity'). '</th>
							<th>' .  _('Expiry Date'). '</th>
						</tr>';
} else {
	$tableheader = '<tr>
				<th>' .  _('Batch/Roll/Bundle'). ' #</th>
				<th>' .  _('Quantity'). '</th>
				</tr>';
	$listtableheader=$tableheader;
}

echo $EditLink . $RemoveLink;

if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

/*Display the batches already entered with quantities if not serialised */

echo '<table class="selection">
		<tr>
			<td valign="top"><table class="selection">';
echo $listtableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

foreach ($LineItem->SerialItems as $Bundle){

	if ($RowCounter == 10){
		echo $listtableheader;
		$RowCounter =0;
	} else {
		$RowCounter++;
	}

	echo '<tr class="striped_row">
			<td>' . $Bundle->BundleRef . '</td>';

	if ($LineItem->Serialised==0 and $Perishable==0){
		echo '<td class="number">' . locale_number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</td>';
	} else if ($LineItem->Serialised==0 and $Perishable==1){
		echo '<td class="number">' . locale_number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</td>';
		echo '<td class="number">' . $Bundle->ExpiryDate . '</td>';
	}

	echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $Bundle->BundleRef . '&StockID=' . $LineItem->StockID . '&LineNo=' . $LineNo .'">' .  _('Delete'). '</a></td></tr>';

	$TotalQuantity += $Bundle->BundleQty;
}


/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo '<tr><td class="number"><b>' .  _('Total Quantity'). ': ' . locale_number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td></tr>';
} else {
	echo '<tr>
			<td class="number"><b>' .  _('Total Quantity'). ':</b></td>
			<td class="number"><b>' . locale_number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td>
		</tr>';
}

/*Close off old table */
echo '</table></td><td valign="top">';

/*Start a new table for the Serial/Batch ref input  in one column (as a sub table
then the multi select box for selection of existing bundle/serial nos for dispatch if applicable*/
//echo '<TABLE><TR><TD valign=TOP>';
$TransferQuantity=$TotalQuantity;
/*in the first column add a table for the input of newies */
echo '<table class="selection">';
echo $tableheader;


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" name="Ga6uF5Wa" method="post">
      <input type="hidden" name="LineNo" value="' . $LineNo . '" />
      <input type="hidden" name="StockID" value="' . $StockID . '" />
      <input type="hidden" name="EntryType" value="KEYED" />';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if ( isset($_GET['EditControlled']) ) {
	$EditControlled = isset($_GET['EditControlled'])?$_GET['EditControlled']:false;
} elseif ( isset($_POST['EditControlled']) ){
	$EditControlled = isset($_POST['EditControlled'])?$_POST['EditControlled']:false;
} else {
	$EditControlled=false;
}
$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

$StartAddingAt = 0;
if ($EditControlled){
	foreach ($LineItem->SerialItems as $Bundle){

		echo '<tr>
				<td valign="top"><input type="text" name="SerialNo'. $StartAddingAt .'" value="'.$Bundle->BundleRef.'" size="21"  maxlength="20" /></td>';

		/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
		into the form for entry of quantities manually */

		if ($LineItem->Serialised==1){
			echo '<input type="hidden" name="Qty' . $StartAddingAt .'" value=1></tr>';
		} else if ($LineItem->Serialised==0 and $Perishable==1) {
			echo '<td><input type="text" class="number" name="Qty' . $StartAddingAt .'" size="11" value="'. locale_number_format($Bundle->BundleQty, $LineItem->DecimalPlaces). '" maxlength="10" /></td></tr>';
		} else {
			echo '<td><input type="text" class="number" name="Qty' . $StartAddingAt .'" size="11"
				value="'. locale_number_format($Bundle->BundleQty, $LineItem->DecimalPlaces). '" maxlength="10" /></td></tr>';
		}

		$StartAddingAt++;
	}
}

if (isset($_SESSION['Transfer']->StockLocationFrom)) {
	$Location=$_SESSION['Transfer']->StockLocationFrom;
} else if (isset($_SESSION['Items']->Location)) {
	$Location=$_SESSION['Items']->Location;
}

$sql="SELECT serialno,
			quantity,
			expirationdate
		FROM stockserialitems
		WHERE stockid='".$StockID."'
		AND loccode='" . $Location . "'";
$result=DB_query($sql);

$RowNumber=0;
while ($myrow=DB_fetch_array($result)){

	echo '<tr>
			<td valign="top">' . $myrow['serialno'] . '<input type="hidden" name="SerialNo'. ($RowNumber) .'" size="21" value="'.$myrow['serialno'].'" maxlength="20" /></td>';

	/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
	into the form for entry of quantities manually */

	if ($LineItem->Serialised==1){
		echo '<input type="hidden" name="Qty' . ($StartAddingAt+$RowNumber) .'" value="1" /></tr>';
	} else if ($LineItem->Serialised==0 and $Perishable==1) {
		if (isset($LineItem->SerialItems[$myrow['serialno']])) {
			echo '<td class="number">' . locale_number_format($myrow['quantity']-$LineItem->SerialItems[$myrow['serialno']]->BundleQty,$LineItem->DecimalPlaces) . '</td>';
		} else {
			echo '<td class="number">' . locale_number_format($myrow['quantity'],$LineItem->DecimalPlaces) . '</td>';
		}
		echo '<td><input type="text" class="number" name="Qty' . ($StartAddingAt+$RowNumber) .'" size="11" value="0" maxlength="10" /></td>';
		echo '<td><input type="hidden" class="date" name="ExpiryDate' . ($StartAddingAt+$RowNumber) .'" size="11" value="'.ConvertSQLDate($myrow['expirationdate']).'" maxlength="10" />' . ConvertSQLDate($myrow['expirationdate']) . '</td></tr>';
	} else {
		echo '<td><input type="text" class="number" name="Qty' . ($StartAddingAt+$RowNumber) .'" size=11  value="'. locale_number_format($myrow['quantity'],$LineItem->DecimalPlaces).'"  maxlength="10" /></td></tr>';
	}
	$RowNumber++;
}
echo '<input type="hidden" name="TotalBundles" value="'.($RowNumber).'">';
echo '</table>';
echo '<br /><div class="centre"><input type="submit" name="AddBatches" value="'. _('Enter'). '" /></div>';
echo '</form>
		</td><td valign="top">';
$ShowExisting=True;
$_POST['EntryType']='Sequential';
if ($ShowExisting){
	include('includes/InputSerialItemsExisting.php');
}
echo '</td>
	</tr>
	</table>
	<script type="text/javascript">
//<![CDATA[
document.Ga6uF5Wa.SerialNo0.focus();
//]]>
</script>'; /*end of nested table */
?>
