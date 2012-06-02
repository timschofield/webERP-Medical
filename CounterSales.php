<?php
/* $Id: CounterSales.php 4469 2011-01-15 02:28:37Z daintree $*/

include('includes/DefineCartClass.php');

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc $PageSecurity now comes from session.inc (and gets read in by GetConfig.php*/

include('includes/session.inc');

$title = _('Counter Sales');

include('includes/header.inc');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');
include('includes/ItemSearch.php');

if (empty($_GET['identifier'])) {
	$identifier=date('U');
	$_POST['PartSearch']=True;
} else {
	$identifier=$_GET['identifier'];
}

if (isset($_SESSION['Items'.$identifier])){
	//update the Items object variable with the data posted from the form
	$_SESSION['Items'.$identifier]->CustRef = _('Cash Sale');
	$_SESSION['Items'.$identifier]->Comments = _('Cash sale on') . ' ' . date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['OrderItems'])){
	foreach ($_POST as $key => $value) {
		if (mb_strstr($key,'StockID')) {
			$Index=substr($key,7);
			$StockID=$value;
			$Quantity=filter_number_input($_POST['Quantity'.$Index]);
			$_POST['Units'.$StockID]=$_POST['Units'.$Index];
			if (isset($_POST['Batch'.$Index])) {
				$Batch=$_POST['Batch'.$Index];
				$sql="SELECT quantity
						FROM stockserialitems
						WHERE stockid='".$StockID."'
							AND serialno='".$Batch."'";
				$BatchQuantityResult=DB_query($sql, $db);
				$BatchQuantityRow=DB_fetch_array($BatchQuantityResult);
				if (!isset($NewItemArray[$StockID])) {
					if ($BatchQuantityRow['quantity']<$Quantity and $Quantity>0) {
						prnMsg( _('Batch number').' '.$Batch.' '.
							_('of item number').' '.$StockID.' '.
								_('has insufficient items remaining in it to complete this sale') , 'info');
					} else {
						$NewItemArray[$StockID]['Quantity'] = $Quantity;
						$NewItemArray[$StockID]['Batch']['Number'][] = $Batch;
						$NewItemArray[$StockID]['Batch']['Quantity'][] = $Quantity;
					}
				} else {
					if ($BatchQuantityRow['quantity']<$Quantity+$NewItemArray[$StockID]['Quantity'] and $NewItemArray[$StockID]['Quantity']>0) {
						prnMsg( _('Batch number').' '.$Batch.' '.
							_('of item number').' '.$StockID.' '.
								_('has insufficient items remaining in it to complete this sale'), 'info');
					} else {
						$NewItemArray[$StockID]['Quantity'] += $Quantity;
						$NewItemArray[$StockID]['Batch']['Number'][] = $Batch;
						$NewItemArray[$StockID]['Batch']['Quantity'][] = $Quantity;
					}
				}
			} else {
				$NewItemArray[$StockID]['Quantity'] = $Quantity;
			}
		}
	}
}

if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}

if (isset($_GET['NewOrder'])){
	/*New order entry - clear any existing order details from the Items object and initiate a newy*/
	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset ($_SESSION['Items'.$identifier]);
	}
}


if (!isset($_SESSION['Items'.$identifier])){
	/* It must be a new order being created $_SESSION['Items'.$identifier] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

	$_SESSION['ExistingOrder'] = 0;
	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['PrintedPackingSlip'] = 0; /*Of course 'cos the order ain't even started !!*/
	/*Get the default customer-branch combo from the user's default location record */
	$sql = "SELECT cashsalecustomer,
					cashsalebranch,
					locationname,
					taxprovinceid
				 FROM locations
				 WHERE loccode='" . $_SESSION['UserStockLocation'] ."'";
	$result = DB_query($sql,$db);
	if (DB_num_rows($result)==0) {
		prnMsg(_('Your user account does not have a valid default inventory location set up. Please see the system administrator to modify your user account.'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow = DB_fetch_array($result); //get the only row returned

		if ($myrow['cashsalecustomer']=='' or $myrow['cashsalebranch']==''){
			prnMsg(_('To use this script it is first necessary to define a cash sales customer for the location that is your default location.').' '.
			 _('This should be setup by modifying the appropriate Customer/Branch details.'),'error');
			include('includes/footer.inc');
			exit;
		}

		if (isset($_GET['DebtorNo'])) {
			$CashSaleCustomer[0]=$_GET['DebtorNo'];
			$CashSaleCustomer[1]=$_GET['BranchNo'];
		} else {
			$CashSaleCustomer[0]=$myrow['cashsalecustomer'];
			$CashSaleCustomer[1]=$myrow['cashsalebranch'];
		}

		$_SESSION['Items'.$identifier]->Branch  = $CashSaleCustomer[1];
		$_SESSION['Items'.$identifier]->DebtorNo = $CashSaleCustomer[0];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];
		$_SESSION['Items'.$identifier]->Location = $_SESSION['UserStockLocation'];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];

		// Now check to ensure this account exists and set defaults */
		$sql = "SELECT debtorsmaster.name,
						holdreasons.dissallowinvoices,
						debtorsmaster.salestype,
						salestypes.sales_type,
						debtorsmaster.currcode,
						debtorsmaster.customerpoline,
						paymentterms.terms
					FROM debtorsmaster,
						holdreasons,
						salestypes,
						paymentterms
					WHERE debtorsmaster.salestype=salestypes.typeabbrev
						AND debtorsmaster.holdreason=holdreasons.reasoncode
						AND debtorsmaster.paymentterms=paymentterms.termsindicator
						AND debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

		$ErrMsg = _('The details of the customer selected') . ': ' .  $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
		// echo $sql;
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

		$myrow = DB_fetch_array($result);
//		$SalesType=$myrow['salestype'];

		if ($myrow['dissallowinvoices'] != 1){
			if ($myrow['dissallowinvoices']==2){
				prnMsg($myrow['name'] . ' ' . _('Although this account is defined as the cash sale account for the location.  The account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'),'warn');
			}

			$_SESSION['RequireCustomerSelection']=0;
			$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
			// the sales type is the price list to be used for this sale
			$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
			$_SESSION['Items'.$identifier]->SalesTypeName = $myrow['sales_type'];
			$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
			$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
			$_SESSION['Items'.$identifier]->PaymentTerms = $myrow['terms'];

			/* now get the branch defaults from the customer branches table CustBranch. */

			$sql = "SELECT custbranch.brname,
							custbranch.braddress1,
							custbranch.defaultshipvia,
							custbranch.deliverblind,
							custbranch.specialinstructions,
							custbranch.estdeliverydays,
							custbranch.salesman,
							custbranch.taxgroupid,
							custbranch.defaultshipvia
						FROM custbranch
						WHERE custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'
							AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

			$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('cannot be retrieved because');
			$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

			if (DB_num_rows($result)==0){

				prnMsg(_('The branch details for branch code') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('against customer code') . ': ' . $_POST['Select'] . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

				if ($debug==1){
					echo '<br />' . _('The SQL that failed to get the branch details was') . ':<br />' . $sql;
				}
				include('includes/footer.inc');
				exit;
			}
			// add echo
			$myrow = DB_fetch_array($result);

			$_SESSION['Items'.$identifier]->DeliverTo = '';
			$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['braddress1'];
			$_SESSION['Items'.$identifier]->ShipVia = $myrow['defaultshipvia'];
			$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
			$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow['specialinstructions'];
			$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
			$_SESSION['Items'.$identifier]->TaxGroup = $myrow['taxgroupid'];

			if ($_SESSION['Items'.$identifier]->SpecialInstructions) {
				prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
			}

			if ($_SESSION['CheckCreditLimits'] > 0) {  /*Check credit limits is 1 for warn and 2 for prohibit sales */
				$_SESSION['Items'.$identifier]->CreditAvailable = GetCreditAvailable($_SESSION['Items'.$identifier]->DebtorNo,$db);

				if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(_('The') . ' ' . $myrow['brname'] . ' ' . _('account is currently at or over their credit limit'),'warn');
				} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(_('No more orders can be placed by') . ' ' . $myrow[0] . ' ' . _(' their account is currently at or over their credit limit'),'warn');
					include('includes/footer.inc');
					exit;
				}
			}

		} else {
			prnMsg($myrow['brname'] . ' ' . _('Although the account is defined as the cash sale account for the location  the account is currently on hold. Please contact the credit control personnel to discuss'),'warn');
		}

	}
} // end if its a new sale to be set up ...

if (isset($_POST['CancelOrder'])) {


	unset($_SESSION['Items'.$identifier]->LineItems);
	$_SESSION['Items'.$identifier]->ItemsOrdered = 0;
	unset($_SESSION['Items'.$identifier]);
	$_SESSION['Items'.$identifier] = new cart;

	echo '<br /><br />';
	prnMsg(_('This sale has been cancelled as requested'),'success');
	echo '<br /><br /><a href="' .htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Start a new Counter Sale') . '</a>';
	include('includes/footer.inc');
	exit;

} else { /*Not cancelling the order */

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Counter Sales') . '" alt="" />' . ' ';

	echo _('Counter Sale') . ' - ' . $_SESSION['Items'.$identifier]->LocationName . ' (' . _('all amounts in') . ' ' . $_SESSION['Items'.$identifier]->DefaultCurrency . ')<br />';

	echo _('Customer') . ' - ' . $_SESSION['Items'.$identifier]->CustomerName;
	echo '</p>';
}

/* Always do the stuff below */

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

//Get The exchange rate used for GPPercent calculations on adding or amending items
if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']){
	$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'",$db);
	if (DB_num_rows($ExRateResult)>0){
		$ExRateRow = DB_fetch_row($ExRateResult);
		$ExRate = $ExRateRow[0];
	} else {
		$ExRate =1;
	}
} else {
	$ExRate = 1;
}

if ($ExRate==0) {
	$ExRate=1;
}

/*Process Quick Entry */
/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
 if (isset($_POST['OrderItems'])
		OR isset($_POST['QuickEntry'])
		OR isset($_POST['Recalculate'])){

	/* get the item details from the database and hold them in the cart object */

	/*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
	$Discount = 0;

	$i=1;
	while ($i<=$_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i]!='') {
		$QuickEntryCode = 'part_' . $i;
		$QuickEntryQty = 'qty_' . $i;
		$QuickEntryPOLine = 'poline_' . $i;
		$QuickEntryItemDue = 'ItemDue_' . $i;

		$i++;

		if (isset($_POST[$QuickEntryCode])) {
			$NewItem = strtoupper($_POST[$QuickEntryCode]);
		}
		if (isset($_POST[$QuickEntryQty])) {
			$NewItemQty = $_POST[$QuickEntryQty];
		}
		if (isset($_POST[$QuickEntryItemDue])) {
			$NewItemDue = $_POST[$QuickEntryItemDue];
		} else {
			$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
		}
		if (isset($_POST[$QuickEntryPOLine])) {
			$NewPOLine = $_POST[$QuickEntryPOLine];
		} else {
			$NewPOLine = 0;
		}

		if (!isset($NewItem)){
			unset($NewItem);
			break;	/* break out of the loop if nothing in the quick entry fields*/
		}

		if(!Is_Date($NewItemDue)) {
			prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
			//Attempt to default the due date to something sensible?
			$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
		}
		/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$sql = "SELECT stockmaster.mbflag,
						stockmaster.controlled
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";

		$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
		$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
		$KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);


		if (DB_num_rows($KitResult)==0){
			prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'),'warn');
		} elseif ($myrow=DB_fetch_array($KitResult)){
			if ($myrow['mbflag']=='K'){	/*It is a kit set item */
				$sql = "SELECT bom.component,
								bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
								AND bom.effectiveto > '" . Date('Y-m-d') . "'
								AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg =  _('Could not retrieve kitset components from the database because') . ' ';
				$KitResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult,$db)) {
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					$NewPOLine = 0;
					include('includes/SelectOrderItems_IntoCart.inc');
				}

			} else if ($myrow['mbflag']=='G'){
				prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
			} else { /*Its not a kit set item*/
				include('includes/SelectOrderItems_IntoCart.inc');
			}
		}
	 }
	 unset($NewItem);
} /* end of if quick entry */

 /*Now do non-quick entry delete/edits/adds */

if ((isset($_SESSION['Items'.$identifier])) OR isset($NewItem)) {

	if (isset($_GET['Delete'])){
		$_SESSION['Items'.$identifier]->remove_from_cart($_GET['Delete']);  /*Don't do any DB updates*/
	}

	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

		if (isset($_POST['Quantity_' . $OrderLine->LineNumber])){

			$Quantity = filter_number_input($_POST['Quantity_' . $OrderLine->LineNumber]);

			if (abs($OrderLine->Price - $_POST['Price_' . $OrderLine->LineNumber])>0.01){
				$Price = filter_number_input($_POST['Price_' . $OrderLine->LineNumber]);
				$_POST['GPPercent_' . $OrderLine->LineNumber] = (($Price*(1-($_POST['Discount_' . $OrderLine->LineNumber]/100))) - $OrderLine->StandardCost*$ExRate)/($Price *(1-$_POST['Discount_' . $OrderLine->LineNumber])/100);
			} else if (abs($OrderLine->GPPercent - $_POST['GPPercent_' . $OrderLine->LineNumber])>=0.001) {
				//then do a recalculation of the price at this new GP Percentage
				$Price = ($OrderLine->StandardCost*$ExRate)/(1 -(($_POST['GPPercent_' . $OrderLine->LineNumber] + $_POST['Discount_' . $OrderLine->LineNumber])/100));
			} else {
				$Price = filter_number_input($_POST['Price_' . $OrderLine->LineNumber]);
			}
			$DiscountPercentage = filter_number_input($_POST['Discount_' . $OrderLine->LineNumber]);
			if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
				$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
			} else {
				$Narrative = '';
			}

			if (!isset($OrderLine->DiscountPercent)) {
				$OrderLine->DiscountPercent = 0;
			}

			if ($Quantity<0 or $Price <0 or $DiscountPercentage >100 or $DiscountPercentage <0){
				prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'warn');
			} else if ($OrderLine->Quantity !=$Quantity
						or $OrderLine->Price != $Price
						or abs($OrderLine->DiscountPercent -$DiscountPercentage/100) >0.001
						or $OrderLine->Narrative != $Narrative
						or $OrderLine->ItemDue != $_POST['ItemDue_' . $OrderLine->LineNumber]
						or $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

				$_SESSION['Items'.$identifier]->update_cart_item($OrderLine->LineNumber,
																$Quantity,
																$Price,
																$OrderLine->Units,
																$OrderLine->ConversionFactor,
																($DiscountPercentage/100),
																0,
																$Narrative,
																'No', /*Update DB */
																$_POST['ItemDue_' . $OrderLine->LineNumber],
																$_POST['POLine_' . $OrderLine->LineNumber],
																filter_number_input($_POST['GPPercent_' . $OrderLine->LineNumber])
																);
			}
		} //page not called from itself - POST variables not set
	}
}

if (isset($_POST['Recalculate'])) {
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$NewItem=$OrderLine->StockID;
		$sql = "SELECT stockmaster.mbflag,
						stockmaster.controlled
					FROM stockmaster
					WHERE stockmaster.stockid='". $OrderLine->StockID."'";

		$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
		$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
		$KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
		if ($myrow=DB_fetch_array($KitResult)){
			if ($myrow['mbflag']=='K'){	/*It is a kit set item */
				$sql = "SELECT bom.component,
								bom.quantity
							FROM bom
							WHERE bom.parent='" . $OrderLine->StockID. "'
								AND bom.effectiveto > '" . Date('Y-m-d') . "'
								AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve kitset components from the database because');
				$KitResult = DB_query($sql,$db,$ErrMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult,$db)){
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					$NewPOLine = 0;
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
				}

			} else { /*Its not a kit set item*/
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;
				$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
			}
		}
		unset($NewItem);
	} /* end of if its a new item */
}

if (isset($NewItem)){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart
Now figure out if the item is a kit set - the field MBFlag='K'
* controlled items and ghost/phantom items cannot be selected because the SQL to show items to select doesn't show 'em
* */
	$sql = "SELECT stockmaster.mbflag,
					stockmaster.taxcatid
		   		FROM stockmaster
				WHERE stockmaster.stockid='". $NewItem ."'";

	$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

	$KitResult = DB_query($sql, $db,$ErrMsg);

	$NewItemQty = 1; /*By Default */
	$Discount = 0; /*By default - can change later or discount category override */

	if ($myrow=DB_fetch_array($KitResult)){
	   	if ($myrow['mbflag']=='K'){	/*It is a kit set item */
			$sql = "SELECT bom.component,
							bom.quantity
						FROM bom
						WHERE bom.parent='" . $NewItem . "'
							AND bom.effectiveto > '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

			$ErrMsg = _('Could not retrieve kitset components from the database because');
			$KitResult = DB_query($sql,$db,$ErrMsg);

			$ParentQty = $NewItemQty;
			while ($KitParts = DB_fetch_array($KitResult,$db)){
				$NewItem = $KitParts['component'];
				$NewItemQty = $KitParts['quantity'] * $ParentQty;
				$NewPOLine = 0;
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				include('includes/SelectOrderItems_IntoCart.inc');
				$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
			}

		} else { /*Its not a kit set item*/
			$NewItemDue = date($_SESSION['DefaultDateFormat']);
			$NewPOLine = 0;

			include('includes/SelectOrderItems_IntoCart.inc');
			$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
		}

	} /* end of if its a new item */

} /*end of if its a new item */

if (isset($NewItemArray) and isset($_POST['OrderItems'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
	foreach($NewItemArray as $NewItem => $NewItemArray) {
		$NewItemQty=$NewItemArray['Quantity'];
		if($NewItemQty > 0)	{
			$sql = "SELECT stockmaster.mbflag
							FROM stockmaster
							WHERE stockmaster.stockid='". $NewItem ."'";

			$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

			$KitResult = DB_query($sql, $db,$ErrMsg);

			//$NewItemQty = 1; /*By Default */
			$Discount = 0; /*By default - can change later or discount category override */

			if ($myrow=DB_fetch_array($KitResult)){
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
									bom.quantity
								FROM bom
								WHERE bom.parent='" . $NewItem . "'
									AND bom.effectiveto > '" . Date('Y-m-d') . "'
									AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

					$ErrMsg = _('Could not retrieve kitset components from the database because');
					$KitResult = DB_query($sql,$db,$ErrMsg);

					$ParentQty = $NewItemQty;
					while ($KitParts = DB_fetch_array($KitResult,$db)){
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
						include('includes/SelectOrderItems_IntoCart.inc');
						$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
					}

				} else { /*Its not a kit set item*/
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$NewPOLine = 0;
					include('includes/SelectOrderItems_IntoCart.inc');
					if ($_SESSION['Items'.$identifier]->LineCounter>0 and
							$_SESSION['Items'.$identifier]->LineItems[$_SESSION['Items'.$identifier]->LineCounter - 1]->Controlled==1) {
						$_SESSION['Items'.$identifier]->LineItems[$_SESSION['Items'.$identifier]->LineCounter - 1]->SerialItems=$NewItemArray['Batch'];
					}
					if ($_SESSION['Items'.$identifier]->LineCounter>0) {
						$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
					}
				}
			} /* end of if its a new item */
		} /*end of if its a new item */
	}
}


/* Run through each line of the order and work out the appropriate discount from the discount matrix */
$DiscCatsDone = array();
$counter =0;
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

	if ($OrderLine->DiscCat !="" AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
		$DiscCatsDone[]=$OrderLine->DiscCat;
		$QuantityOfDiscCat = 0;

		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine_2) {
			/* add up total quantity of all lines of this DiscCat */
			if ($OrderLine_2->DiscCat==$OrderLine->DiscCat){
				$QuantityOfDiscCat += $OrderLine_2->Quantity;
			}
		}
		$result = DB_query("SELECT MAX(discountrate) AS discount
								FROM discountmatrix
								WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
									AND discountcategory ='" . $OrderLine->DiscCat . "'
									AND quantitybreak <='" . $QuantityOfDiscCat . "'",$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]==NULL){
			$DiscountMatrixRate = 0;
		} else {
			$DiscountMatrixRate = $myrow[0];
		}
		if ($myrow[0]!=0){ /* need to update the lines affected */
			foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine_2) {
				/* add up total quantity of all lines of this DiscCat */
				if ($OrderLine_2->DiscCat==$OrderLine->DiscCat){
					$_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
					$_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->GPPercent = (($_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->Price*(1-$DiscountMatrixRate)) - $_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->StandardCost*$ExRate)/($_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->Price *(1-$DiscountMatrixRate)/100);
				}
			}
		}
	}
} /* end of discount matrix lookup code */

if (count($_SESSION['Items'.$identifier]->LineItems)>0 and !isset($_POST['ProcessSale']) and !isset($_POST['PartSearch'])){ /*only show order lines if there are any */
/*
// *************************************************************************
//   T H I S   W H E R E   T H E   S A L E  I S   D I S P L A Y E D
// *************************************************************************
*/

	echo '<br /><table width="90%" cellpadding="2" class="selection">
		<tr>
			<th>' . _('Item Code') . '</th>
			<th>' . _('Item Description') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('QOH') . '</th>
			<th>' . _('Unit') . '</th>
			<th>' . _('Price') . '</th>
			<th>' . _('Discount') . '</th>
			<th>' . _('GP %') . '</th>
			<th>' . _('Net') . '</th>
			<th>' . _('Tax') . '</th>
			<th>' . _('Total') . '<br />' . _('Incl Tax') . '</th>
		</tr>';

	$_SESSION['Items'.$identifier]->total = 0;
	$_SESSION['Items'.$identifier]->totalVolume = 0;
	$_SESSION['Items'.$identifier]->totalWeight = 0;
	$TaxTotals = array();
	$TaxGLCodes = array();
	$TaxTotal =0;
	$k =0;  //row colour counter
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

		$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		$QtyOrdered = $OrderLine->Quantity;
		$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

		if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M')) {
			/*There is a stock deficiency in the stock location selected */
			$RowStarter = '<tr bgcolor="#EEAABB">';
		} elseif ($k==1){
			$RowStarter = '<tr class="OddTableRows">';
			$k=0;
		} else {
			$RowStarter = '<tr class="EvenTableRows">';
			$k=1;
		}

		echo $RowStarter;
		echo '<input type="hidden" name="POLine_' .	 $OrderLine->LineNumber . '" value="" />';
		echo '<input type="hidden" name="ItemDue_' .	 $OrderLine->LineNumber . '" value="'.$OrderLine->ItemDue.'" />';

		echo '<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?identifier='.$identifier . '&amp;StockID=' . $OrderLine->StockID . '&amp;DebtorNo=' . $_SESSION['Items'.$identifier]->DebtorNo . '">' . $OrderLine->StockID . '</a></td>
			<td>' . $OrderLine->ItemDescription . '</td>';

		echo '<td><input class="number" tabindex="2" type="text" name="Quantity_' . ($OrderLine->LineNumber) . '" size="6" maxlength="6" value="' . locale_number_format($OrderLine->Quantity,$OrderLine->DecimalPlaces) . '" /></td>';

		echo '<td class="number">' . locale_number_format($OrderLine->QOHatLoc/$OrderLine->ConversionFactor,$OrderLine->DecimalPlaces) . '</td>
				<td>' . $OrderLine->Units . '</td>';

		if ($_SESSION['CanViewPrices']==1) {
			echo '<td><input class="number" type="text" name="Price_' . $OrderLine->LineNumber . '" size="16" maxlength="16" value="' . locale_number_format($OrderLine->Price,4) . '" /></td>';
		} else {
			echo '<input type="hidden" name="Price_' . $OrderLine->LineNumber . '" value="' . locale_number_format($OrderLine->Price,4) . '" />';
			echo '<td class="number">' . locale_number_format($OrderLine->Price,4) . '</td>';
		}

		if ($_SESSION['CanViewPrices']==1) {
			echo '<td><input class="number" type="text" name="Discount_' . $OrderLine->LineNumber . '" size="5" maxlength="4" value="' . locale_number_format($OrderLine->DiscountPercent * 100,2) . '" /></td>
					<td><input class="number" type="text" name="GPPercent_' . $OrderLine->LineNumber . '" size="8" maxlength="8" value="' . locale_number_format($OrderLine->GPPercent,4) . '" /></td>';
		} else {
			echo '<input type="hidden" name="Discount_' . $OrderLine->LineNumber . '" value="' . locale_number_format($OrderLine->DiscountPercent * 100,2) . '" />
					<input type="hidden" name="GPPercent_' . $OrderLine->LineNumber . '" value="' . locale_number_format($OrderLine->GPPercent,4) . '" />';
			echo '<td class="number">' . locale_number_format($OrderLine->DiscountPercent * 100,2) . '</td>
					<td class="number">' . locale_number_format($OrderLine->GPPercent,4) . '%</td>';
		}
		echo '<td class="number">' . locale_money_format($SubTotal,$_SESSION['Items'.$identifier]->DefaultCurrency) . '</td>';
		$LineDueDate = $OrderLine->ItemDue;
		if (!Is_Date($OrderLine->ItemDue)){
			$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
			$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
		}
		$i=0; // initialise the number of taxes iterated through
		$TaxLineTotal =0; //initialise tax total for the line

		foreach ($OrderLine->Taxes AS $Tax) {
			if (empty($TaxTotals[$Tax->TaxAuthID])) {
				$TaxTotals[$Tax->TaxAuthID]=0;
			}
			if ($Tax->TaxOnTax ==1){
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
				$TaxLineTotal += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
			} else {
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $SubTotal);
				$TaxLineTotal += ($Tax->TaxRate * $SubTotal);
			}
			$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
		}

		$TaxTotal += $TaxLineTotal;
		$_SESSION['Items'.$identifier]->TaxTotals=$TaxTotals;
		$_SESSION['Items'.$identifier]->TaxGLCodes=$TaxGLCodes;
		echo '<td class="number">' . locale_money_format($TaxLineTotal ,$_SESSION['Items'.$identifier]->DefaultCurrency) . '</td>';
		echo '<td class="number">' . locale_money_format($SubTotal + $TaxLineTotal ,$_SESSION['Items'.$identifier]->DefaultCurrency) . '</td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier='.$identifier . '&amp;Delete=' . $OrderLine->LineNumber . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . _('Delete') . '</a></td></tr>';

		if ($_SESSION['AllowOrderLineItemNarrative'] == 1){
			echo $RowStarter;
			echo '<td valign="top" colspan="11">' . _('Narrative') . ':<textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="100" rows="1">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea><br /></td></tr>';
		} else {
			echo '<input type="hidden" name="Narrative" value="" />';
		}

		$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $SubTotal;
		$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
		$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;

	} /* end of loop around items */

	echo '<tr class="EvenTableRows">
			<td colspan="8" class="number"><b>' . _('Total') . '</b></td>
			<td class="number">' . locale_money_format(($_SESSION['Items'.$identifier]->total),$_SESSION['Items'.$identifier]->DefaultCurrency) . '</td>
			<td class="number">' . locale_money_format($TaxTotal,$_SESSION['Items'.$identifier]->DefaultCurrency) . '</td>
			<td class="number">' . locale_money_format(($_SESSION['Items'.$identifier]->total+$TaxTotal),$_SESSION['Items'.$identifier]->DefaultCurrency) . '</td>
		</tr>
		</table>';
	echo '<input type="hidden" name="TaxTotal" value="'.$TaxTotal.'" />';
	echo '<br /><table><tr><td>';
	//nested table
	echo '<table class="selection">
			<tr>
				<td>'. _('Picked Up By') .':</td>
				<td><input type="text" size="25" maxlength="25" name="DeliverTo" value="' . stripslashes($_SESSION['Items'.$identifier]->DeliverTo) . '" /></td>
			</tr>';
	echo '<tr>
			<td>'. _('Contact Phone Number') .':</td>
			<td><input type="text" size="25" maxlength="25" name="PhoneNo" value="' . stripslashes($_SESSION['Items'.$identifier]->PhoneNo) . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Contact Email') . ':</td>
			<td><input type="text" size="25" maxlength="30" name="Email" value="' . stripslashes($_SESSION['Items'.$identifier]->Email) . '" /></td>
		</tr>';

	echo '<tr>
			<td>'. _('Customer Reference') .':</td>
			<td><input type="text" size="25" maxlength="25" name="CustRef" value="' . stripcslashes($_SESSION['Items'.$identifier]->CustRef) . '" /></td>
		</tr>';

	echo '<tr>
			<td>'. _('Comments') .':</td>
			<td><textarea name="Comments" cols="23" rows="5">' . stripcslashes($_SESSION['Items'.$identifier]->Comments) .'</textarea></td>
		</tr>';
	echo '</table>'; //end the sub table in the first column of master table
	echo '</td><th style="vertical-align: top;border-width: 0px;">'; //for the master table
	echo '<table class="selection">'; // a new nested table in the second column of master table
	//now the payment stuff in this column
	$PaymentMethodsResult = DB_query("SELECT paymentid, paymentname FROM paymentmethods",$db);

	$_POST['PaymentMethod']='Cash';

	echo '<tr>
			<td>' . _('Payment Type') . ':</td>
			<td><select name="PaymentMethod">';
	while ($PaymentMethodRow = DB_fetch_array($PaymentMethodsResult)){
		if (isset($_POST['PaymentMethod']) and $_POST['PaymentMethod']	== $PaymentMethodRow['paymentname']){
			echo '<option selected="True" value="' . $PaymentMethodRow['paymentid'] . '">' . $PaymentMethodRow['paymentname'] . '</option>';
		} else {
			echo '<option value="' . $PaymentMethodRow['paymentid'] . '">' . $PaymentMethodRow['paymentname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	$BankAccountsResult = DB_query("SELECT bankaccountname, accountcode FROM bankaccounts",$db);

	echo '<tr>
			<td>' . _('Banked to') . ':</td>
			<td><select name="BankAccount">';
	while ($BankAccountsRow = DB_fetch_array($BankAccountsResult)){
		if (isset($_POST['BankAccount']) and $_POST['BankAccount']	== $BankAccountsRow['accountcode']){
			echo '<option selected="True" value="' . $BankAccountsRow['accountcode'] . '">' . $BankAccountsRow['bankaccountname'] . '</option>';
		} else {
			echo '<option value="' . $BankAccountsRow['accountcode'] . '">' . $BankAccountsRow['bankaccountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	$_POST['AmountPaid'] =($_SESSION['Items'.$identifier]->total+$TaxTotal);

	echo '<tr><td>' . _('Amount Paid') . ':</td><td><input type="text" class="number" name="AmountPaid" maxlength="12" size="12" value="' . locale_money_format(round($_POST['AmountPaid'],2),$_SESSION['Items'.$identifier]->DefaultCurrency) . '" /></td></tr>';

	echo '</table>'; //end the sub table in the second column of master table
	echo '</th></tr></table>';	//end of column/row/master table
	echo '<br /><div class="centre">
				<button type="submit" name="Recalculate">' . _('Re-Calculate') . '</button>
				<button type="submit" name="ProcessSale">' . _('Process The Sale') . '</button>
				<button type="submit" name="PartSearch">' . _('Add more items') . '</button>
				<button type="submit" name="CancelOrder" onclick="return confirm(\'' . _('Are you sure you wish to cancel this sale?') . '\');">' . _('Cancel Sale') . '</button>
			</div>';

} # end of if lines

if (isset($_SESSION['Items'.$identifier]) and $_SESSION['Items'.$identifier]->ItemsOrdered==0) {
	$_POST['PartSearch']='Yes';
}

/* **********************************
 * Invoice Processing Here
 * **********************************
 * */
if (isset($_POST['ProcessSale'])){

	$InputError = false; //always assume the best
	//but check for the worst
	if ($_SESSION['Items'.$identifier]->LineCounter == 0){
		prnMsg(_('There are no lines on this sale. Please enter lines to invoice first'),'error');
		$InputError = true;
	}
	if (abs(filter_currency_input($_POST['AmountPaid'])-($_SESSION['Items'.$identifier]->total+filter_currency_input($_POST['TaxTotal'])))>=0.01) {
		prnMsg(_('The amount entered as payment does not equal the amount of the invoice. Please ensure the customer has paid the correct amount and re-enter'),'error');
		$InputError = true;
	}

	if ($_SESSION['ProhibitNegativeStock']==1){ // checks for negative stock after processing invoice
	//sadly this check does not combine quantities occuring twice on and order and each line is considered individually :-(
		$NegativesFound = false;
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			$SQL = "SELECT stockmaster.description,
							locstock.quantity,
							stockmaster.mbflag
						FROM locstock
						INNER JOIN stockmaster
							ON stockmaster.stockid=locstock.stockid
						WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
							AND locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'";

			$ErrMsg = _('Could not retrieve the quantity left at the location once this order is invoiced (for the purposes of checking that stock will not go negative because)');
			$Result = DB_query($SQL,$db,$ErrMsg);
			$CheckNegRow = DB_fetch_array($Result);
			if ($CheckNegRow['mbflag']=='B' OR $CheckNegRow['mbflag']=='M'){
				if ($CheckNegRow['quantity'] < ($OrderLine->Quantity*$OrderLine->ConversionFactor)){
					prnMsg( _('Invoicing the selected order would result in negative stock. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
					$NegativesFound = true;
				}
			} else if ($CheckNegRow['mbflag']=='A') {

				/*Now look for assembly components that would go negative */
				$SQL = "SELECT bom.component,
							   stockmaster.description,
							   locstock.quantity-(" . $OrderLine->Quantity  . "*bom.quantity) AS qtyleft
							FROM bom
							INNER JOIN locstock
								ON bom.component=locstock.stockid
							INNER JOIN stockmaster
								ON stockmaster.stockid=bom.component
							WHERE bom.parent='" . $OrderLine->StockID . "'
								AND locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'
								AND effectiveafter <'" . Date('Y-m-d') . "'
								AND effectiveto >='" . Date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve the component quantity left at the location once the assembly item on this order is invoiced (for the purposes of checking that stock will not go negative because)');
				$Result = DB_query($SQL,$db,$ErrMsg);
				while ($NegRow = DB_fetch_array($Result)){
					if ($NegRow['qtyleft']<0){
						prnMsg(_('Invoicing the selected order would result in negative stock for a component of an assembly item on the order. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$NegRow['component'] . ' ' . $NegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
						$NegativesFound = true;
					} // end if negative would result
				} //loop around the components of an assembly item
			}//end if its an assembly item - check component stock

		} //end of loop around items on the order for negative check

		if ($NegativesFound){
			prnMsg(_('The parameter to prohibit negative stock is set and invoicing this sale would result in negative stock. No futher processing can be performed. Alter the sale first changing quantities or deleting lines which do not have sufficient stock.'),'error');
			$InputError = true;
		}

	}//end of testing for negative stocks


	if ($InputError == false) { //all good so let's get on with the processing

	/* Now Get the area where the sale is to from the branches table */

		$SQL = "SELECT 	area,
						defaultshipvia
					FROM custbranch
					WHERE custbranch.debtorno ='". $_SESSION['Items'.$identifier]->DebtorNo . "'
						AND custbranch.branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "'";

		$ErrMsg = _('We were unable to load the area where the sale is to from the custbranch table');
		$Result = DB_query($SQL,$db, $ErrMsg);
		$myrow = DB_fetch_row($Result);
		$Area = $myrow[0];
		$DefaultShipVia = $myrow[1];
		DB_free_result($Result);

	/*company record read in on login with info on GL Links and debtors GL account*/

		if ($_SESSION['CompanyRecord']==0){
			/*The company data and preferences could not be retrieved for some reason */
			prnMsg( _('The company information and preferences could not be retrieved. See your system administrator'), 'error');
			include('includes/footer.inc');
			exit;
		}

	// *************************************************************************
	//   S T A R T   O F   I N V O I C E   S Q L   P R O C E S S I N G
	// *************************************************************************

	/*First add the order to the database - it only exists in the session currently! */
		$OrderNo = GetNextTransNo(30, $db);

		$HeaderSQL = "INSERT INTO salesorders (	orderno,
												debtorno,
												branchcode,
												customerref,
												comments,
												orddate,
												ordertype,
												shipvia,
												deliverto,
												deladd1,
												contactphone,
												contactemail,
												fromstkloc,
												deliverydate,
												confirmeddate,
												deliverblind)
											VALUES (
												'" . $OrderNo . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . $_SESSION['Items'.$identifier]->Branch . "',
												'" . DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
												'" . DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
												'" . Date('Y-m-d H:i') . "',
												'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
												'" . $_SESSION['Items'.$identifier]->ShipVia . "',
												'" . DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
												'" . _('Counter Sale') . "',
												'" . $_SESSION['Items'.$identifier]->PhoneNo . "',
												'" . $_SESSION['Items'.$identifier]->Email . "',
												'" . $_SESSION['Items'.$identifier]->Location ."',
												'" . Date('Y-m-d') . "',
												'" . Date('Y-m-d') . "',
												0)";

		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

		$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (orderlineno,
																orderno,
																stkcode,
																unitprice,
																quantity,
																discountpercent,
																narrative,
																itemdue,
																actualdispatchdate,
																qtyinvoiced,
																completed)
															VALUES (";

		$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
		foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

			$LineItemsSQL = $StartOf_LineItemsSQL . "
											'" . $StockItem->LineNumber . "',
											'" . $OrderNo . "',
											'" . $StockItem->StockID . "',
											'" . $StockItem->Price . "',
											'" . $StockItem->Quantity . "',
											'" . floatval($StockItem->DiscountPercent) . "',
											'" . DB_escape_string($StockItem->Narrative) . "',
											'" . Date('Y-m-d') . "',
											'" . Date('Y-m-d') . "',
											'" . $StockItem->Quantity . "',
											1)";
			$ErrMsg = _('Unable to add the sales order line');
			$Ins_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);

			/*Now check to see if the item is manufactured
			 * 			and AutoCreateWOs is on
			 * 			and it is a real order (not just a quotation)*/

			if ($StockItem->MBflag=='M'
				and $_SESSION['AutoCreateWOs']==1){ //oh yeah its all on!

				//now get the data required to test to see if we need to make a new WO
				$QOHResult = DB_query("SELECT SUM(quantity) FROM locstock WHERE stockid='" . $StockItem->StockID . "'",$db);
				$QOHRow = DB_fetch_row($QOHResult);
				$QOH = $QOHRow[0];

				$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
									FROM salesorderdetails
									WHERE salesorderdetails.stkcode = '" . $StockItem->StockID . "'
									AND salesorderdetails.completed = 0";
				$DemandResult = DB_query($SQL,$db);
				$DemandRow = DB_fetch_row($DemandResult);
				$QuantityDemand = $DemandRow[0];

				$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
								FROM salesorderdetails,
									bom,
									stockmaster
								WHERE salesorderdetails.stkcode=bom.parent
								AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
								AND bom.component='" . $StockItem->StockID . "'
								AND stockmaster.stockid=bom.parent
								AND salesorderdetails.completed=0";
				$AssemblyDemandResult = DB_query($SQL,$db);
				$AssemblyDemandRow = DB_fetch_row($AssemblyDemandResult);
				$QuantityAssemblyDemand = $AssemblyDemandRow[0];

				$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
								FROM purchorderdetails INNER JOIN purchorders
								ON purchorderdetails.orderno = purchorders.orderno
								WHERE purchorderdetails.itemcode = '" . $StockItem->StockID . "'
								AND purchorderdetails.completed = 0
								AND purchorders.status<>'Rejected'
								AND purchorders.status<>'Pending'
								AND purchorders.status<>'Completed'";
				$PurchOrdersResult = DB_query($SQL,$db);
				$PurchOrdersRow = DB_fetch_row($PurchOrdersResult);
				$QuantityPurchOrders = $PurchOrdersRow[0];

				$SQL = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) as qtyonorder
								FROM woitems INNER JOIN workorders
								ON woitems.wo=workorders.wo
								WHERE woitems.stockid = '" . $StockItem->StockID . "'
								AND woitems.qtyreqd > woitems.qtyrecd
								AND workorders.closed = 0";
				$WorkOrdersResult = DB_query($SQL,$db);
				$WorkOrdersRow = DB_fetch_row($WorkOrdersResult);
				$QuantityWorkOrders = $WorkOrdersRow[0];

				//Now we have the data - do we need to make any more?
				$ShortfallQuantity = $QOH-$QuantityDemand-$QuantityAssemblyDemand+$QuantityPurchOrders+$QuantityWorkOrders;

				if ($ShortfallQuantity < 0) { //then we need to make a work order
					//How many should the work order be for??
					if ($ShortfallQuantity + $StockItem->EOQ < 0){
						$WOQuantity = -$ShortfallQuantity;
					} else {
						$WOQuantity = $StockItem->EOQ;
					}

					$WONo = GetNextTransNo(40,$db);
					$ErrMsg = _('Unable to insert a new work order for the sales order item');
					$InsWOResult = DB_query("INSERT INTO workorders (wo,
													 loccode,
													 requiredby,
													 startdate)
									 VALUES ('" . $WONo . "',
											'" . $_SESSION['DefaultFactoryLocation'] . "',
											'" . Date('Y-m-d') . "',
											'" . Date('Y-m-d'). "')",
											$db,$ErrMsg,$DbgMsg,true);
					//Need to get the latest BOM to roll up cost
					$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
																	FROM stockmaster INNER JOIN bom
																	ON stockmaster.stockid=bom.component
																	WHERE bom.parent='" . $StockItem->StockID . "'
																	AND bom.loccode='" . $_SESSION['DefaultFactoryLocation'] . "'",
																$db);
					$CostRow = DB_fetch_row($CostResult);
					if (is_null($CostRow[0]) OR $CostRow[0]==0){
						$Cost =0;
						prnMsg(_('In automatically creating a work order for') . ' ' . $StockItem->StockID . ' ' . _('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
					} else {
						$Cost = $CostRow[0];
					}

					// insert parent item info
					$sql = "INSERT INTO woitems (wo,
												 stockid,
												 qtyreqd,
												 stdcost)
									 VALUES ('" . $WONo . "',
											 '" . $StockItem->StockID . "',
											 '" . $WOQuantity . "',
											 '" . $Cost . "')";
					$ErrMsg = _('The work order item could not be added');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

					//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
					WoRealRequirements($db, $WONo, $_SESSION['DefaultFactoryLocation'], $StockItem->StockID);

					$FactoryManagerEmail = _('A new work order has been created for') .
										":\n" . $StockItem->StockID . ' - ' . $StockItem->ItemDescription . ' x ' . $WOQuantity . ' ' . $StockItem->Units .
										"\n" . _('These are for') . ' ' . $_SESSION['Items'.$identifier]->CustomerName . ' ' . _('there order ref') . ': '  . $_SESSION['Items'.$identifier]->CustRef . ' ' ._('our order number') . ': ' . $OrderNo;

					if ($StockItem->Serialised AND $StockItem->NextSerialNo>0){
						//then we must create the serial numbers for the new WO also
						$FactoryManagerEmail .= "\n" . _('The following serial numbers have been reserved for this work order') . ':';

						for ($i=0;$i<$WOQuantity;$i++){

							$result = DB_query("SELECT serialno FROM stockserialitems
													WHERE serialno='" . ($StockItem->NextSerialNo + $i) . "'
													AND stockid='" . $StockItem->StockID ."'",$db);
							if (DB_num_rows($result)!=0){
								$WOQuantity++;
								prnMsg(($StockItem->NextSerialNo + $i) . ': ' . _('This automatically generated serial number already exists - it cannot be added to the work order'),'error');
							} else {
								$sql = "INSERT INTO woserialnos (wo,
																	stockid,
																	serialno)
														VALUES ('" . $WONo . "',
																'" . $StockItem->StockID . "',
																'" . ($StockItem->NextSerialNo + $i)	 . "')";
								$ErrMsg = _('The serial number for the work order item could not be added');
								$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
								$FactoryManagerEmail .= "\n" . ($StockItem->NextSerialNo + $i);
							}
						} //end loop around creation of woserialnos
						$NewNextSerialNo = ($StockItem->NextSerialNo + $WOQuantity +1);
						$ErrMsg = _('Could not update the new next serial number for the item');
						$UpdateSQL="UPDATE stockmaster SET nextserialno='" . $NewNextSerialNo . "' WHERE stockid='" . $StockItem->StockID . "'";
						$UpdateNextSerialNoResult = DB_query($UpdateSQL,$db,$ErrMsg,$DbgMsg,true);
					} // end if the item is serialised and nextserialno is set

					$EmailSubject = _('New Work Order Number') . ' ' . $WONo . ' ' . _('for') . ' ' . $StockItem->StockID . ' x ' . $WOQuantity;
					//Send email to the Factory Manager
					mail($_SESSION['FactoryManagerEmail'],$EmailSubject,$FactoryManagerEmail);
				} //end if with this sales order there is a shortfall of stock - need to create the WO
			}//end if auto create WOs in on
		} /* end inserted line items into sales order details */

		$result = DB_Txn_Commit($db);

		prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('has been entered'),'success');

	/* End of insertion of new sales order */

	/*Now Get the next invoice number - GetNextTransNo() function in SQL_CommonFunctions
	 * GetPeriod() in includes/DateFunctions.inc */

		$InvoiceNo = GetNextTransNo(10, $db);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

	/*Start an SQL transaction */

		DB_Txn_Begin($db);

		$DefaultDispatchDate = Date('Y-m-d');

	/*Update order header for invoice charged on */
		$SQL = "UPDATE salesorders SET comments = CONCAT(comments,'" . ' ' . _('Invoice') . ': ' . "','" . $InvoiceNo . "') WHERE orderno= '" . $OrderNo."'";

		$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order header could not be updated with the invoice number');
		$DbgMsg = _('The following SQL to update the sales order was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	/*Now insert the DebtorTrans */

		$SQL = "INSERT INTO debtortrans (transno,
										type,
										debtorno,
										branchcode,
										trandate,
										inputdate,
										prd,
										reference,
										tpe,
										order_,
										ovamount,
										ovgst,
										rate,
										invtext,
										shipvia,
										alloc,
										settled)
									VALUES (
										'". $InvoiceNo . "',
										10,
										'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
										'" . $_SESSION['Items'.$identifier]->Branch . "',
										'" . $DefaultDispatchDate . "',
										'" . date('Y-m-d H-i-s') . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['Items'.$identifier]->CustRef  . "',
										'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										'" . $OrderNo . "',
										'" . filter_currency_input($_SESSION['Items'.$identifier]->total) . "',
										'" . filter_currency_input($_POST['TaxTotal']) . "',
										'" . $ExRate . "',
										'" . $_SESSION['Items'.$identifier]->Comments . "',
										'" . $_SESSION['Items'.$identifier]->ShipVia . "',
										'" . filter_number_input($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal']) . "',
										'1')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
	 	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

	/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['Items'.$identifier]->TaxTotals AS $TaxAuthID => $TaxAmount) {

			$SQL = "INSERT INTO debtortranstaxes (debtortransid,
												taxauthid,
												taxamount)
											VALUES (
												'" . $DebtorTransID . "',
												'" . $TaxAuthID . "',
												'" . filter_currency_input($TaxAmount/$ExRate) . "')";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		}

		//Loop around each item on the sale and process each in turn
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			 /* Update location stock records if not a dummy stock item
			 need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'",$db);
			$myrow = DB_fetch_row($Result);
			$MBFlag = $myrow[0];
			if ($MBFlag=='B' OR $MBFlag=='M') {
				$Assembly = False;

				/* Need to get the current location quantity
				will need it later for the stock movement */
				$SQL="SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $OrderLine->StockID . "'
								AND loccode= '" . $_SESSION['Items'.$identifier]->Location . "'";
				$ErrMsg = _('WARNING') . ': ' . _('Could not retrieve current location stock');
				$Result = DB_query($SQL, $db, $ErrMsg);

				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$SQL = "UPDATE locstock
							SET quantity = locstock.quantity-" . filter_number_input($OrderLine->Quantity*$OrderLine->ConversionFactor) . "
							WHERE locstock.stockid = '" . $OrderLine->StockID . "'
							AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the location stock record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			} else if ($MBFlag=='A'){ /* its an assembly */
				/*Need to get the BOM for this part and make
				stock moves for the components then update the Location stock balances */
				$Assembly=True;
				$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				$SQL = "SELECT bom.component,
						bom.quantity,
						stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
						FROM bom,
							stockmaster
						WHERE bom.component=stockmaster.stockid
						AND bom.parent='" . $OrderLine->StockID . "'
						AND bom.effectiveto > '" . Date('Y-m-d') . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
				$DbgMsg = _('The SQL that failed was');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				while ($AssParts = DB_fetch_array($AssResult,$db)){

					$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
					/* Need to get the current location quantity
					will need it later for the stock movement */
					$SQL="SELECT locstock.quantity
									FROM locstock
									WHERE locstock.stockid='" . $AssParts['component'] . "'
									AND loccode= '" . $_SESSION['Items'.$identifier]->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Can not retrieve assembly components location stock quantities because ');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					if (DB_num_rows($Result)==1){
						$LocQtyRow = DB_fetch_row($Result);
						$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
					}
					if (empty($AssParts['standard'])) {
						$AssParts['standard']=0;
					}
					$SQL = "INSERT INTO stockmoves (stockid,
													type,
													transno,
													loccode,
													trandate,
													debtorno,
													branchcode,
													prd,
													reference,
													qty,
													standardcost,
													show_on_inv_crds,
													newqoh
												) VALUES (
													'" . $AssParts['component'] . "',
													10,
													'" . $InvoiceNo . "',
													'" . $_SESSION['Items'.$identifier]->Location . "',
													'" . $DefaultDispatchDate . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
													'" . $_SESSION['Items'.$identifier]->Branch . "',
													'" . $PeriodNo . "',
													'" . _('Assembly') . ': ' . $OrderLine->StockID . ' ' . _('Order') . ': ' . $OrderNo . "',
													'-" . filter_number_input($AssParts['quantity'] * $OrderLine->Quantity) . "',
													'" . $AssParts['standard'] . "',
													0,
													newqoh-" . filter_number_input($AssParts['quantity'] * $OrderLine->Quantity) . "
												)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of'). ' '. $OrderLine->StockID . ' ' . _('could not be inserted because');
					$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


					$SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . filter_number_input($AssParts['quantity'] * $OrderLine->Quantity) . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
					$DbgMsg = _('The following SQL to update the locations stock record for the component was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /* end of assembly explosion and updates */

				/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
				$OrderLine->StandardCost = $StandardCost;
			} /* end of its an assembly */

			// Insert stock movements - with unit cost
			$LocalCurrencyPrice = ($OrderLine->Price / $ExRate);

			if (empty($OrderLine->StandardCost)) {
				$OrderLine->StandardCost=0;
			}
			if ($MBFlag=='B' OR $MBFlag=='M'){
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												newqoh,
												narrative
											) VALUES (
												'" . $OrderLine->StockID . "',
												10,
												'" . $InvoiceNo . "',
												'" . $_SESSION['Items'.$identifier]->Location . "',
												'" . $DefaultDispatchDate . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . $_SESSION['Items'.$identifier]->Branch . "',
												'" . filter_number_input($LocalCurrencyPrice) . "',
												'" . $PeriodNo . "',
												'" . $OrderNo . "',
												'-" . filter_number_input($OrderLine->Quantity*$OrderLine->ConversionFactor) . "',
												'" . $OrderLine->DiscountPercent . "',
												'" . $OrderLine->StandardCost . "',
												'" . filter_number_input(($QtyOnHandPrior - $OrderLine->Quantity)*$OrderLine->ConversionFactor) . "',
												'" . DB_escape_string($OrderLine->Narrative) . "' )";
			} else {
			// its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
				if (empty($OrderLine->StandardCost)) {
					$OrderLine->StandardCost = 0;
				}
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												narrative
											) VALUES ('" . $OrderLine->StockID . "',
												10,
												'" . $InvoiceNo . "',
												'" . $_SESSION['Items'.$identifier]->Location . "',
												'" . $DefaultDispatchDate . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . $_SESSION['Items'.$identifier]->Branch . "',
												'" . filter_number_input($LocalCurrencyPrice) . "',
												'" . $PeriodNo . "',
												'" . $OrderNo . "',
												'-" . filter_number_input($OrderLine->Quantity*$OrderLine->ConversionFactor) . "',
												'" . $OrderLine->DiscountPercent . "',
												'" . $OrderLine->StandardCost . "',
												'" . DB_escape_string($OrderLine->Narrative) . "')";
			}
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

			if (isset($OrderLine->SerialItems)) {
				foreach ($OrderLine->SerialItems['Number'] as $i => $SerialItemNumber) {
					$Batch[$SerialItemNumber]=$OrderLine->SerialItems['Quantity'][$i];
					$SQL="UPDATE stockserialitems
							SET quantity=quantity-".filter_number_input($OrderLine->SerialItems['Quantity'][$i]*$OrderLine->ConversionFactor)."
							WHERE stockid='".$OrderLine->StockID."'
								AND serialno='".$SerialItemNumber."'";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Batch numbers could not be updated');
					$DbgMsg = _('The following SQL to update the stock batch record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					$SQL="INSERT INTO stockserialmoves (stockmoveno,
														stockid,
														serialno,
														moveqty
													) VALUES (
														'" . $StkMoveNo . "',
														'" . $OrderLine->StockID . "',
														'" . $SerialItemNumber . "',
														'-" . filter_number_input($OrderLine->SerialItems['Quantity'][$i]*$OrderLine->ConversionFactor) . "'
													)";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Batch numbers could not be updated');
					$DbgMsg = _('The following SQL to insert the stock batch movement was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			}

		/*Insert the taxes that applied to this line */
			foreach ($OrderLine->Taxes as $Tax) {

				$SQL = "INSERT INTO stockmovestaxes (stkmoveno,
													taxauthid,
													taxrate,
													taxcalculationorder,
													taxontax
												) VALUES (
													'" . $StkMoveNo . "',
													'" . $Tax->TaxAuthID . "',
													'" . $Tax->TaxRate . "',
													'" . $Tax->TaxCalculationOrder . "',
													'" . $Tax->TaxOnTax . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Taxes and rates applicable to this invoice line item could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} //end for each tax for the line

		/*Insert Sales Analysis records */

			$SQL="SELECT COUNT(*),
					salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson
				FROM salesanalysis,
					custbranch,
					stockmaster
				WHERE salesanalysis.stkcategory=stockmaster.categoryid
				AND salesanalysis.stockid=stockmaster.stockid
				AND salesanalysis.cust=custbranch.debtorno
				AND salesanalysis.custbranch=custbranch.branchcode
				AND salesanalysis.area=custbranch.area
				AND salesanalysis.salesperson=custbranch.salesman
				AND salesanalysis.typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
				AND salesanalysis.periodno='" . $PeriodNo . "'
				AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
				AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
				AND salesanalysis.stockid " . LIKE . " '" . $OrderLine->StockID . "'
				AND salesanalysis.budgetoractual=1
				GROUP BY salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson";

			$ErrMsg = _('The count of existing Sales analysis records could not run because');
			$DbgMsg = _('SQL to count the no of sales analysis records');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE salesanalysis
							SET amt=amt+" . filter_currency_input($OrderLine->Price * $OrderLine->Quantity / $ExRate) . ",
								cost=cost+" . filter_currency_input($OrderLine->StandardCost * $OrderLine->Quantity) . ",
								qty=qty +" . $OrderLine->Quantity . ",
								disc=disc+" . filter_currency_input($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->Quantity / $ExRate) . "
							WHERE salesanalysis.area='" . $myrow[5] . "'
								AND salesanalysis.salesperson='" . $myrow[8] . "'
								AND typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
								AND periodno = '" . $PeriodNo . "'
								AND cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
								AND custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
								AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
								AND salesanalysis.stkcategory ='" . $myrow[2] . "'
								AND budgetoractual=1";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT INTO salesanalysis (	typeabbrev,
													periodno,
													amt,
													cost,
													cust,
													custbranch,
													qty,
													disc,
													stockid,
													area,
													budgetoractual,
													salesperson,
													stkcategory	)
												SELECT
													'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
													'" . $PeriodNo . "',
													'" . filter_currency_input($OrderLine->Price * $OrderLine->Quantity / $ExRate) . "',
													'" . filter_currency_input($OrderLine->StandardCost * $OrderLine->Quantity) . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
													'" . $_SESSION['Items'.$identifier]->Branch . "',
													'" . $OrderLine->Quantity . "',
													'" . filter_currency_input($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->Quantity / $ExRate) . "',
													'" . $OrderLine->StockID . "',
													custbranch.area,
													1,
													custbranch.salesman,
													stockmaster.categoryid
												FROM stockmaster,
													custbranch
												WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
													AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
													AND custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'";
			}

			$ErrMsg = _('Sales analysis record could not be added or updated because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0){

		/*first the cost of sales entry*/

				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES ( 10,
												'" . $InvoiceNo . "',
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db) . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "',
												'" . filter_currency_input($OrderLine->StandardCost * $OrderLine->Quantity) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*now the stock entry*/
				$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);

				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount )
										VALUES ( 10,
											'" . $InvoiceNo . "',
											'" . $DefaultDispatchDate . "',
											'" . $PeriodNo . "',
											'" . $StockGLCode['stockact'] . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "',
											'" . filter_currency_input(-$OrderLine->StandardCost * $OrderLine->Quantity) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){

		//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);

				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount
											)
										VALUES ( 10,
											'" . $InvoiceNo . "',
											'" . $DefaultDispatchDate . "',
											'" . $PeriodNo . "',
											'" . $SalesGLAccounts['salesglcode'] . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . "',
											'" . filter_currency_input(-$OrderLine->Price * $OrderLine->Quantity/$ExRate) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales GL posting could not be inserted because');
				$DbgMsg = '<br />' ._('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				if ($OrderLine->DiscountPercent !=0){

					$SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount
												)
												VALUES ( 10,
													'" . $InvoiceNo . "',
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $SalesGLAccounts['discountglcode'] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
													'" . filter_currency_input($OrderLine->Price * $OrderLine->Quantity * $OrderLine->DiscountPercent/$ExRate) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */
		} /*end of OrderLine loop */

		if ($_SESSION['CompanyRecord']['gllink_debtors']==1){

	/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
			if (($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal']) !=0) {
				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount	)
											VALUES ( 10,
												'" . $InvoiceNo . "',
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . filter_currency_input((filter_currency_input($_SESSION['Items'.$identifier]->total) + filter_currency_input($_POST['TaxTotal']))/$ExRate) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}


			foreach ( $_SESSION['Items'.$identifier]->TaxTotals as $TaxAuthID => $TaxAmount){
				if ($TaxAmount !=0 ){
					$SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount	)
												VALUES ( 10,
													'" . $InvoiceNo . "',
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $_SESSION['Items'.$identifier]->TaxGLCodes[$TaxAuthID] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
													'" . filter_currency_input(-$TaxAmount/$ExRate) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			}
			/*Also if GL is linked to debtors need to process the debit to bank and credit to debtors for the payment */
			/*Need to figure out the cross rate between customer currency and bank account currency */

			if ($_POST['AmountPaid']!=0){
				$ReceiptNumber = GetNextTransNo(12,$db);
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $_POST['BankAccount'] . "',
						'" . $_SESSION['Items'.$identifier]->LocationName . ' ' . _('Counter Sale') . ' ' . $InvoiceNo . "',
						'" . filter_currency_input(filter_currency_input($_POST['AmountPaid'])/$ExRate) . "')";
				$DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/* Now Credit Debtors account with receipt */
				$SQL="INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount)
				VALUES (12,
					'" . $ReceiptNumber . "',
					'" . $DefaultDispatchDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
					'" . $_SESSION['Items'.$identifier]->LocationName . ' ' . _('Counter Sale') . ' ' . $InvoiceNo . "',
					'-" . filter_currency_input(filter_currency_input($_POST['AmountPaid'])/$ExRate) . "')";
				$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
				$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			}//amount paid we not zero
		} /*end of if Sales and GL integrated */
		if ($_POST['AmountPaid']!=0){
			if (!isset($ReceiptNumber)){
				$ReceiptNumber = GetNextTransNo(12,$db);
			}
			//Now need to add the receipt banktrans record
			//First get the account currency that it has been banked into
			$result = DB_query("SELECT rate FROM currencies
													INNER JOIN bankaccounts ON currencies.currabrev=bankaccounts.currcode
													WHERE bankaccounts.accountcode='" . $_POST['BankAccount'] . "'",$db);
			$myrow = DB_fetch_row($result);
			$BankAccountExRate = $myrow[0];

			/*
			 * Some interesting exchange rate conversion going on here
			 * Say :
			 * The business's functional currency is NZD
			 * Customer location counter sales are in AUD - 1 NZD = 0.80 AUD
			 * Banking money into a USD account - 1 NZD = 0.68 USD
			 *
			 * Customer sale is for $100 AUD
			 * GL entries  conver the AUD 100 to NZD  - 100 AUD / 0.80 = $125 NZD
			 * Banktrans entries convert the AUD 100 to USD using 100/0.8 * 0.68
			*/

			//insert the banktrans record in the currency of the bank account

			$SQL="INSERT INTO banktrans (type,
										transno,
										bankact,
										ref,
										exrate,
										functionalexrate,
										transdate,
										banktranstype,
										amount,
										currcode)
									VALUES (
										12,
										'" . $ReceiptNumber . "',
										'" . $_POST['BankAccount'] . "',
										'" . $_SESSION['Items'.$identifier]->LocationName . ' ' . _('Counter Sale') . ' ' . $InvoiceNo . "',
										'" . $ExRate . "',
										'" . $BankAccountExRate . "',
										'" . $DefaultDispatchDate . "',
										'" . $_POST['PaymentMethod'] . "',
										'" . filter_currency_input(filter_currency_input($_POST['AmountPaid']) * $BankAccountExRate) . "',
										'" . $_SESSION['Items'.$identifier]->DefaultCurrency . "')";

			$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
			$ErrMsg = _('Cannot insert a bank transaction');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//insert a new debtortrans for the receipt

			$SQL = "INSERT INTO debtortrans (transno,
											type,
											debtorno,
											trandate,
											inputdate,
											prd,
											reference,
											rate,
											ovamount,
											alloc,
											invtext,
											settled)
										VALUES (
											'" . $ReceiptNumber . "',
											12,
											'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
											'" . $DefaultDispatchDate . "',
											'" . date('Y-m-d H-i-s') . "',
											'" . $PeriodNo . "',
											'" . $InvoiceNo . "',
											'" . $ExRate . "',
											'-" . filter_currency_input($_POST['AmountPaid']) . "',
											'-" . filter_currency_input($_POST['AmountPaid']) . "',
											'" . $_SESSION['Items'.$identifier]->LocationName . ' ' . _('Counter Sale') ."',
											'1')";

			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$ReceiptDebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

			$SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . $DefaultDispatchDate . "',
											lastpaid='" . filter_currency_input($_POST['AmountPaid']) . "'
									WHERE debtorsmaster.debtorno='" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

			$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
			$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//and finally add the allocation record between receipt and invoice

			$SQL = "INSERT INTO custallocns (	amt,
												datealloc,
												transid_allocfrom,
												transid_allocto )
									VALUES  ('" . filter_currency_input($_POST['AmountPaid']) . "',
											'" . $DefaultDispatchDate . "',
											 '" . $ReceiptDebtorTransID . "',
											 '" . $DebtorTransID . "')";

			$DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
			$ErrMsg = _('Cannot insert the customer allocation of the receipt to the invoice because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} //end if $_POST['AmountPaid']!= 0

		DB_Txn_Commit($db);
	// *************************************************************************
	//   E N D   O F   I N V O I C E   S Q L   P R O C E S S I N G
	// *************************************************************************

		unset($_SESSION['Items'.$identifier]->LineItems);
		unset($_SESSION['Items'.$identifier]);

		echo prnMsg( _('Invoice number'). ' '. $InvoiceNo .' '. _('processed'), 'success');

		echo '<br /><div class="centre">';

		if ($_SESSION['InvoicePortraitFormat']==0){
			echo '<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="'.$rootpath.'/PDFReceipt.php?FromTransNo='.$InvoiceNo.'&amp;InvOrCredit=Invoice&amp;PrintPDF=True">'. _('Print this receipt'). '</a><br /><br />';
		} else {
			echo '<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="'.$rootpath.'/PDFReceipt.php?FromTransNo='.$InvoiceNo.'&amp;InvOrCredit=Invoice&amp;PrintPDF=True">'. _('Print this receipt'). '</a><br /><br />';
		}
		echo '<br /><a href="' .htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Start a new Counter Sale') . '</a></div>';

	}
	// There were input errors so don't process nuffin
} else {
	//pretend the user never tried to commit the sale
	unset($_POST['ProcessSale']);
}
/*******************************
 * end of Invoice Processing
 * *****************************
*/

// This code needs sorting out, but until then :
$ImageSource = _('No Image');

/* Now show the stock item selection search stuff below */
if (isset($_POST['PartSearch'])){
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Items') . '</p>';
	echo '<div class="page_help_text">' . _('Search for Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
	ShowItemSearchFields($rootpath, $theme, $db, $identifier, array('A', 'K', 'M', 'B', 'D'), array('F', 'D'), 'Search');
}

echo '<br /></form>';
include('includes/footer.inc');
?>