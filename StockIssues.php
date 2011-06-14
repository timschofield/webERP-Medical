<?php

/* $Revision: 1.4 $ */

include('includes/DefineStockAdjustment.php');
include('includes/DefineSerialItems.php');

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Stock Issues');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


if (isset($_POST['CheckCode'])) {

	if (strlen($_POST['StockText'])>0) {
		$sql="SELECT stockid, description from stockmaster where description like '%".$_POST['StockText']."%'";
	} else if (strlen($_POST['StockCode'])>0) {
		$sql="SELECT stockid, description from stockmaster where stockid like '%".$_POST['StockCode']."%'";
	} else {
		$sql="SELECT stockid, description from stockmaster";
	}
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	echo '<table><tr><th>'._('Stock Code').'</th><th>'._('Stock Description').'</th></tr>';
	while ($myrow = DB_fetch_row($result)) {
		echo '<tr><td>'.$myrow[0].'</td><td>'.$myrow[1].'</td><td><a href="StockIssues.php?StockID='.$myrow[0].'">Issue</a></tr>';
	}
	echo '</table>';
}

if (isset($_GET['NewIssue'])){
     unset($_SESSION['Adjustment']);
     $_SESSION['Adjustment'] = new StockAdjustment;
}

if (!isset($_SESSION['Adjustment'])){
     $_SESSION['Adjustment'] = new StockAdjustment;
}

$NewAdjustment = false;

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' .
	_('Stock Issues') . '" alt="" />' . ' ' . _('Stock Issues') . '</p>';

if (isset($_GET['StockID'])){
	$_SESSION['Adjustment']->StockID = trim(strtoupper($_GET['StockID']));
	$NewAdjustment = true;
} elseif (isset($_POST['StockID'])){
	if ($_POST['StockID'] != $_SESSION['Adjustment']->StockID){
		$NewAdjustment = true;
		$_SESSION['Adjustment']->StockID = trim(strtoupper($_POST['StockID']));
	}

	$_SESSION['Adjustment']->Narrative = $_POST['Narrative'];
	$_SESSION['Adjustment']->StockLocation = $_POST['StockLocation'];
	if ($_POST['Quantity']=='' or !is_numeric($_POST['Quantity'])){
		$_POST['Quantity']=0;
	}
	$_SESSION['Adjustment']->Quantity = $_POST['Quantity'];
}

if ($NewAdjustment){

	$sql ="SELECT description,
				units,
				mbflag,
				materialcost+labourcost+overheadcost as standardcost,
				controlled,
				serialised,
				decimalplaces
			FROM stockmaster
			WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'";
	$ErrMsg = _('Unable to load StockMaster info for part'). ':' . $_SESSION['Adjustment']->StockID;
	$result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_row($result);

	if (DB_num_rows($result)==0){
                prnMsg( _('Unable to locate Stock Code').' '.$_SESSION['Adjustment']->StockID, 'error' );
				unset($_SESSION['Adjustment']);
	} elseif (DB_num_rows($result)>0){

		$_SESSION['Adjustment']->ItemDescription = $myrow[0];
		$_SESSION['Adjustment']->PartUnit = $myrow[1];
		$_SESSION['Adjustment']->StandardCost = $myrow[3];
		$_SESSION['Adjustment']->Controlled = $myrow[4];
		$_SESSION['Adjustment']->Serialised = $myrow[5];
		$_SESSION['Adjustment']->DecimalPlaces = $myrow[6];
		$_SESSION['Adjustment']->SerialItems = array();

		if ($myrow[2]=='D' OR $myrow[2]=='A' OR $myrow[2]=='K'){
			prnMsg( _('The part entered is either or a dummy part or an assembly or kit-set part') . '. ' . _('These parts are not physical parts and no stock holding is maintained for them') . '. ' . _('Stock adjustments are therefore not possible'),'error');
			echo '<HR>';
			echo '<a href="'. $rootpath .'/StockAdjustments.php?' . SID .'">'. _('Enter another adjustment'). '</a>';
			unset ($_SESSION['Adjustment']);
			include ('includes/footer.inc');
			exit;
		}
	}
}

if (isset($_POST['EnterAdjustment']) && $_POST['EnterAdjustment']!= ''){
//israel started here
//israel ended here


	$InputError = false; /*Start by hoping for the best */
	$result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		prnMsg( _('The entered item code does not exist'),'error');
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Adjustment']->Quantity)){
		prnMsg( _('The quantity entered must be numeric'),'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment']->Quantity==0){
		prnMsg( _('The quantity entered cannot be zero') . '. ' . _('There would be no adjustment to make'),'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment']->Controlled==1 AND count($_SESSION['Adjustment']->SerialItems)==0) {
		prnMsg( _('The item entered is a controlled item that requires the detail of the serial numbers or batch references to be adjusted to be entered'),'error');
		$InputError = true;
	} elseif ($_POST['tag']=='') {
		prnMsg( _('You must enter a tag for this transaction'),'error');
		$InputError = true;
	}

	if ($_SESSION['ProhibitNegativeStock']==1){
		$SQL = "SELECT quantity FROM locstock
				WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'
				AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'";
		$CheckNegResult=DB_query($SQL,$db);
		$CheckNegRow = DB_fetch_array($CheckNegResult);
		if ($CheckNegRow['quantity']-$_SESSION['Adjustment']->Quantity <0){
			$InputError=true;
			prnMsg(_('The system parameters are set to prohibit negative stocks. Processing this stock adjustment would result in negative stock at this location. This adjustment will not be processed.'),'warn');
		}
	}

	if (!$InputError) {

/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$AdjustmentNumber = GetNextTransNo(14,$db);
		$PeriodNo = GetPeriod ($_POST['issueddate'], $db);
		//$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
		//$SQLAdjustmentDate=$siku[2].'-'.'0'.$m.'-'.$siku[0];
		$SQL = 'BEGIN';
		$Result = DB_query($SQL,$db);

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_SESSION['Adjustment']->StockID . "'
			AND loccode= '" . $_SESSION['Adjustment']->StockLocation . "'";
		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}


		$SQL = "INSERT INTO stockmoves (
				stockid,
				type,
				transno,
				loccode,
				trandate,
				prd,
				reference,
				qty,
				newqoh,
				tagref)
			VALUES (
				'" . $_SESSION['Adjustment']->StockID . "',
				14,
				" . $AdjustmentNumber . ",
				'" . $_SESSION['Adjustment']->StockLocation . "',
				'" . FormatDateForSQL($_POST['issueddate']) . "',
				" . $PeriodNo . ",
				'" . $_SESSION['Adjustment']->Narrative ."',
				" . -$_SESSION['Adjustment']->Quantity . ",
				" . ($QtyOnHandPrior - $_SESSION['Adjustment']->Quantity) . ",'
				" . $_POST['tag']."'
			)";



		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg =  _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Adjustment']->Controlled ==1){
			foreach($_SESSION['Adjustment']->SerialItems as $Item){
			/*We need to add or update the StockSerialItem record and
			The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not */
				$SQL = "SELECT COUNT(*)
					FROM stockserialitems
					WHERE
					stockid='" . $_SESSION['Adjustment']->StockID . "'
					AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'
					AND serialno='" . $Item->BundleRef . "'";
				$ErrMsg = _('Unable to determine if the serial item exists');
				$Result = DB_query($SQL,$db,$ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0]==1){

					$SQL = "UPDATE stockserialitems SET
						quantity= quantity + " . $Item->BundleQty . "
						WHERE
						stockid='" . $_SESSION['Adjustment']->StockID . "'
						AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'
						AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg =  _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
									loccode,
									serialno,
									quantity)
						VALUES ('" . $_SESSION['Adjustment']->StockID . "',
						'" . $_SESSION['Adjustment']->StockLocation . "',
						'" . $Item->BundleRef . "',
						" . $Item->BundleQty . ")";

					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg =  _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}


				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno,
									stockid,
									serialno,
									moveqty)
						VALUES (" . $StkMoveNo . ",
							'" . $_SESSION['Adjustment']->StockID . "',
							'" . $Item->BundleRef . "',
							" . $Item->BundleQty . ")";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			}/* foreach controlled item in the serialitems array */
		} /*end if the adjustment item is a controlled item */



		$SQL = "UPDATE locstock SET quantity = quantity - " . $_SESSION['Adjustment']->Quantity . "
				WHERE stockid='" . $_SESSION['Adjustment']->StockID . "'
				AND loccode='" . $_SESSION['Adjustment']->StockLocation . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the stock record was used');

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		if ($_SESSION['CompanyRecord']['gllink_stock']==1){

			$StockGLCodes = GetStockGLCode($_SESSION['Adjustment']->StockID,$db);

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							amount,
							narrative,
							tag)
					VALUES (14,
						" .$AdjustmentNumber . ",
						'" . FormatDateForSQL($_POST['issueddate']) . "',
						" . $PeriodNo . ",
						" .  $StockGLCodes['adjglact'] . ",
						" . $_SESSION['Adjustment']->StandardCost * ($_SESSION['Adjustment']->Quantity) . ",
						'" . $_SESSION['Adjustment']->StockID . " x " . $_SESSION['Adjustment']->Quantity . " @ " . $_SESSION['Adjustment']->StandardCost . " " . $_SESSION['Adjustment']->Narrative . "','" .
							$_POST['tag'].	"')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							amount,
							narrative)
					VALUES (14,
						" .$AdjustmentNumber . ",
						'" . FormatDateForSQL($_POST['issueddate']) . "',
						" . $PeriodNo . ",
						" .  $StockGLCodes['stockact'] . ",
						" . $_SESSION['Adjustment']->StandardCost * -$_SESSION['Adjustment']->Quantity . ",
						'" . $_SESSION['Adjustment']->StockID . " x " . $_SESSION['Adjustment']->Quantity . " @ " . $_SESSION['Adjustment']->StandardCost . " " . $_SESSION['Adjustment']->Narrative . "')";

			$Errmsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
		}

		$Result = DB_query('COMMIT',$db);

		prnMsg( _('A stock issue for'). ' ' . $_SESSION['Adjustment']->StockID . ' -  ' . $_SESSION['Adjustment']->ItemDescription . ' '._('has been created from location').' ' . $_SESSION['Adjustment']->StockLocation .' '. _('for a quantity of') . ' ' . $_SESSION['Adjustment']->Quantity,'success');

		echo '<br /><a href="PDFStockIssue.php?TransferNo='.$AdjustmentNumber.'">Print Stock Issue Note</a>';

		unset ($_SESSION['Adjustment']);
	} /* end if there was no input error */

}/* end if the user hit enter the adjustment */


echo '<form action="'. $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_SESSION['Adjustment'])) {
	$StockID='';
	$Controlled= 0;
	$Quantity = 0;
} else {
	$StockID = $_SESSION['Adjustment']->StockID;
	$Controlled = $_SESSION['Adjustment']->Controlled;
	$Quantity = $_SESSION['Adjustment']->Quantity;
}
echo '<table class="selection"><tr><td>'. _('Stocks Code'). ':</td><td><input type=text name="StockID" size=21 value="' . $StockID . '" maxlength=20></td></tr><tr><td>'.
 _('Partial Description'). ':</td><td><input type=text name="StockText" size=21 value="' . $_SESSION['Adjustment']->ItemDescription .'">'.
 _('Partial Stock Code'). ':<input type=text name="StockCode" size=21 value="' . $StockID .
  '" maxlength=20> <input type=SUBMIT NAME="CheckCode" VALUE="'._('Check Part').'"></td></TR>';

if (isset($_SESSION['Adjustment']) and strlen($_SESSION['Adjustment']->ItemDescription)>1){
	echo '<tr><td COLSPAN=3><FONT COLOR=BLUE size=3>' . $_SESSION['Adjustment']->ItemDescription . ' ('._('In Units of').' ' . $_SESSION['Adjustment']->PartUnit . ' ) - ' . _('Unit Cost').' = ' . $_SESSION['Adjustment']->StandardCost . '</FONT></td></TR>';
}

echo '<tr><td>'. _('Issued From Stock At Location').':</td><td><select name="StockLocation"> ';

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_SESSION['Adjustment']->StockLocation)){
		if ($myrow['loccode'] == $_SESSION['Adjustment']->StockLocation){
		     echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
		     echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
}

echo '</SELECT></td></TR>';

//Select the tag to tag to this stock issue
echo '<tr><td>' . _('Select Tag') . ':</td>
	<td><select name="tag">';

$SQL = 'SELECT tagref,
				tagdescription
		FROM tags
		ORDER BY tagref';

$result=DB_query($SQL,$db);
if (DB_num_rows($result)==0){
   echo '</select></td></tr>';
   prnMsg(_('No Tags have been set up yet') . ' - ' . _('payments cannot be analysed against a tag until the tag is set up'),'error');
} else {
	echo '<option selected value=></option>';
	while ($myrow=DB_fetch_array($result)){
	    if ($_POST['tag']==$myrow["tagref"]){
		echo '<option selected value=' . $myrow['tagref'] . '>' .$myrow['tagdescription'];
	    } else {
		echo '<option value=' . $myrow['tagref'] . '>' .$myrow['tagdescription'];
	    }
	}
	echo '</select></td></tr>';
}
	// End select tag

if (!isset($_SESSION['Adjustment']->Narrative)) {
	$_SESSION['Adjustment']->Narrative = '';
}

echo '<tr><td>'. _('Comments On Why').':</td>
	<td><input type=text name="Narrative" size=32 maxlength=30 value="' . $_SESSION['Adjustment']->Narrative . '"></td></tr>';

// israel started coding here

echo '<tr><td>'._('Issued Date').'</td><td><input type=text name=issueddate size=10 class=date alt="'.
		$_SESSION['DefaultDateFormat'] .'"  value="'.Date($_SESSION['DefaultDateFormat']).'">
		</td></tr>';

echo '<tr><td>'._('Issued Quantity').':</td>';

echo '<td>';
if ($Controlled==1){
		if ($_SESSION['Adjustment']->StockLocation != ''){
			echo '<input type="HIDDEN" NAME="Quantity" Value="' . $_SESSION['Adjustment']->Quantity . '">
				'.$_SESSION['Adjustment']->Quantity.' &nbsp; &nbsp; &nbsp; &nbsp;
				[<a href="'.$rootpath.'/StockAdjustmentsControlled.php?AdjType=REMOVE&' . SID . '">'._('Remove').'</a>]
				[<a href="'.$rootpath.'/StockAdjustmentsControlled.php?AdjType=ADD&' . SID . '">'._('Add').'</a>]';
		} else {
			prnMsg( _('Please select a location and press') . ' "' . _('Enter Stock Issue') . '" ' . _('below to enter Controlled Items'), 'info');
		}
} else {
	echo '<input type=text name="Quantity" size=12 maxlength=12 Value="' . $Quantity . '">';
}
echo '</td></tr>';

echo '</table><br /><div class=centre><input type=submit name="EnterAdjustment" value="'. _('Enter Stock Issue'). '" />';

if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation']='';
}


echo '<br /><br /><a href="'. $rootpath. '/StockStatus.php?' . SID . '&StockID='. $StockID . '">'._('Show Stock Status').'</a>';
echo '<br /><a href="'.$rootpath.'/StockMovements.php?' . SID . '&StockID=' . $StockID . '">'._('Show Movements').'</a>';
echo '<br /><a href="'.$rootpath.'/StockUsage.php?' . SID . '&StockID=' . $StockID . '&StockLocation=' . $_POST['StockLocation'] . '">'._('Show Stock Usage').'</a>';
echo '<br /><a href="'.$rootpath.'/SelectSalesOrder.php?' . SID . '&SelectedStockItem='. $StockID .'&StockLocation=' . $_POST['StockLocation'] . '">'. _('Search Outstanding Sales Orders').'</a>';
echo '<br /><a href="'.$rootpath.'/SelectCompletedOrder.php?' . SID . '&SelectedStockItem=' . $StockID .'">'._('Search Completed Sales Orders').'</a></div>';

echo '</form>';
include('includes/footer.inc');
?>