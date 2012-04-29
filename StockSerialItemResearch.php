<?php
/* $Id$*/

include('includes/session.inc');
$title = _('Serial Item Research');
include('includes/header.inc');

echo '<p Class="page_title_text"> <img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') .'"  /> <b>' . $title. '</b> </p>';


//validate the submission
if (isset($_POST['serialno'])) {
	$SN = trim($_POST['serialno']);
} elseif(isset($_GET['serialno'])) {
	$SN = trim($_GET['serialno']);
} else {
	$SN = '';
}
$SN = $SN;


echo '<form name=SNRESEARCH method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .'">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection"><tr><td>' . _('Serial Number') .': </td><td><input id="serialno" name="serialno" size="21" maxlength="20" value="'. $SN . '" /></td> &nbsp;
<td><button type="submit" name="submit" />' . _('Submit') . '</button></td></tr></table><br />
</form>';

echo '<script>
document.getElementById("serialno").focus();
</script>';


if ($SN!='') {
	//the point here is to allow a semi fuzzy search, but still keep someone from killing the db server
	if (mb_strstr($SN,'%')){
		while(mb_strstr($SN,'%%'))	{
			$SN = str_replace('%%','%',$SN);
		}
		if (mb_strlen($SN) < 11){
			$SN = str_replace('%','',$SN);
			prnMsg('You can not use LIKE with short numbers. It has been removed.','warn');
		}
	}
	$SQL = "SELECT stockserialitems.serialno,
					stockserialitems.stockid,
					stockserialitems.quantity AS CurInvQty,
					stockserialmoves.moveqty,
					stockmoves.type,
					systypes.typename,
					stockmoves.transno,
					stockmoves.loccode,
					locations.locationname,
					stockmoves.trandate,
					stockmoves.debtorno,
					stockmoves.branchcode,
					stockmoves.reference,
					stockmoves.qty AS TotalMoveQty,
					stockmaster.decimalplaces
			FROM stockserialitems
			INNER JOIN stockserialmoves
				ON stockserialitems.serialno = stockserialmoves.serialno
				AND stockserialitems.stockid=stockserialmoves.stockid
			INNER JOIN stockmoves
				ON stockserialmoves.stockmoveno = stockmoves.stkmoveno
				AND stockserialitems.loccode=stockmoves.loccode
			INNER JOIN systypes
				ON stockmoves.type=systypes.typeid
			INNER JOIN locations
				ON stockmoves.loccode = locations.loccode
			INNER JOIN stockmaster
				ON stockmaster.stockid=stockserialitems.stockid
			WHERE stockserialitems.serialno LIKE '$SN'
			ORDER BY stkmoveno";

	$result = DB_query($SQL,$db);

	if (DB_num_rows($result) == 0){
		prnMsg( _('No History found for Serial Number'). ': <b>'.$SN.'</b>' , 'warn');
	} else {
		echo '<h4>'. _('Details for Serial Item').': <b>'.$SN.'</b><br />'. _('Length').'='.mb_strlen($SN).'</h4>';
		echo '<table class="selection">';
		echo '<tr>
				<th>' . _('StockID') . '</th>
				<th>' . _('CurInvQty') . '</th>
				<th>' . _('Move Qty') . '</th>
				<th>' . _('Move Type') . '</th>
				<th>' . _('Trans #') . '</th>
				<th>' . _('Location') . '</th>
				<th>' . _('Date') . '</th>
				<th>' . _('DebtorNo') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Move Ref') . '</th>
				<th>' . _('Total Move Qty') . '</th>
			</tr>';
		while ($myrow=DB_fetch_array($result)) {
			printf('<tr>
				<td>%s<br />%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s (%s)</td>
				<td class="number">%s</td>
				<td>%s - %s</td>
				<td>%s &nbsp;</td>
				<td>%s &nbsp;</td>
				<td>%s &nbsp;</td>
				<td>%s &nbsp;</td>
				<td class="number">%s</td>
				</tr>',
				$myrow['stockid'],
				$myrow['serialno'],
				locale_number_Format($myrow['CurInvQty'], $myrow['decimalplaces']),
				locale_number_Format($myrow['moveqty'], $myrow['decimalplaces']),
				$myrow['typename'], $myrow['type'],
				$myrow['transno'],
				$myrow['loccode'], $myrow['locationname'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['debtorno'],
				$myrow['branchcode'],
				$myrow['reference'],
				locale_number_Format($myrow['TotalMoveQty'], $myrow['decimalplaces'])
			);
		} //END WHILE LIST LOOP
		echo '</table>';
	} // ELSE THERE WHERE ROWS
}//END OF POST IS SET
echo '</div>';

include('includes/footer.inc');
?>