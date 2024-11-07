<?php

include('includes/DefineContractClass.php');
include('includes/session.php');
if (isset($_POST['RequiredDate'])){$_POST['RequiredDate'] = ConvertSQLDate($_POST['RequiredDate']);};

if (isset($_GET['ModifyContractNo'])) {
	$Title = _('Modify Contract') . ' ' . $_GET['ModifyContractNo'];
} else {
	$Title = _('Contract Entry');
}

if (isset($_GET['CustomerID'])) {
	$_POST['SelectedCustomer']=$_GET['CustomerID'];
}

foreach ($_POST as $FormVariableName=>$FormVariableValue) {
	if (mb_substr($FormVariableName, 0, 6)=='Submit') {
		$Index = mb_substr($FormVariableName, 6);
		$_POST['SelectedCustomer']=$_POST['SelectedCustomer'.$Index];
		$_POST['SelectedBranch']=$_POST['SelectedBranch'.$Index];
	}
}
$ViewTopic= 'Contracts';
$BookMark = 'CreateContract';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

/*If the page is called is called without an identifier being set then
 * it must be either a new contract, or the start of a modification of an
 * existing contract, and so we must create a new identifier.
 *
 * The identifier only needs to be unique for this php session, so a
 * unix timestamp will be sufficient.
 */

if (!isset($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (isset($_GET['NewContract']) AND isset($_SESSION['Contract'.$identifier])){
	unset($_SESSION['Contract'.$identifier]);
	$_SESSION['ExistingContract'] = 0;
}

if (isset($_GET['NewContract']) AND isset($_GET['SelectedCustomer'])) {
	/*
	* initialize a new contract
	*/
	$_SESSION['ExistingContract']=0;
	unset($_SESSION['Contract'.$identifier]->ContractBOM);
	unset($_SESSION['Contract'.$identifier]->ContractReqts);
	unset($_SESSION['Contract'.$identifier]);
	/* initialize new class object */
	$_SESSION['Contract'.$identifier] = new Contract;

	$_POST['SelectedCustomer'] = $_GET['SelectedCustomer'];

	/*The customer is checked for credit and the Contract Object populated
	 * using the usual logic of when a customer is selected
	 * */
}

if (isset($_SESSION['Contract'.$identifier]) AND
			(isset($_POST['EnterContractBOM'])
				OR isset($_POST['EnterContractRequirements']))){
	/**  Ensure session variables updated */

	$_SESSION['Contract'.$identifier]->ContractRef=$_POST['ContractRef'];
	$_SESSION['Contract'.$identifier]->ContractDescription=$_POST['ContractDescription'];
	$_SESSION['Contract'.$identifier]->CategoryID = $_POST['CategoryID'];
	$_SESSION['Contract'.$identifier]->LocCode = $_POST['LocCode'];
	$_SESSION['Contract'.$identifier]->RequiredDate = $_POST['RequiredDate'];
	$_SESSION['Contract'.$identifier]->Margin = filter_number_format($_POST['Margin']);
	$_SESSION['Contract'.$identifier]->CustomerRef = $_POST['CustomerRef'];
	$_SESSION['Contract'.$identifier]->ExRate = filter_number_format($_POST['ExRate']);
	$_SESSION['Contract'.$identifier]->DefaultWorkCentre = $_POST['DefaultWorkCentre'];


/*User hit the button to enter line items -
  then meta refresh to Contract_Items.php*/
	$InputError = false;
	if(mb_strlen($_SESSION['Contract'.$identifier]->ContractRef)<5){
		prnMsg(_('The contract reference must be entered (and be longer than 5 characters) before the requirements of the contract can be setup'),'warn');
		$InputError = true;
	}

	if (isset($_POST['EnterContractBOM']) AND !$InputError){
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/ContractBOM.php?identifier='.$identifier. '" />';
		echo '<br />';
		prnMsg(_('You should automatically be forwarded to the entry of the Contract line items page') . '. ' .
		_('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/ContractBOM.php?identifier='.$identifier . '">' . _('click here') . '</a> ' . _('to continue'),'info');
		include('includes/footer.php');
		exit;
	}
	if (isset($_POST['EnterContractRequirements']) AND !$InputError){
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/ContractOtherReqts.php?identifier='.$identifier. '" />';
		echo '<br />';
		prnMsg(_('You should automatically be forwarded to the entry of the Contract requirements page') . '. ' .
		_('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
		'<a href="' . $RootPath . '/ContractOtherReqts.php?identifier=' . $identifier . '">' . _('click here') . '</a> ' . _('to continue'),'info');
		include('includes/footer.php');
		exit;
	}
} /* end of if going to contract BOM or contract requriements */

echo '<a href="'. $RootPath . '/SelectContract.php">' .  _('Back to Contract Selection'). '</a><br />';

$SupportedImgExt = array('png','jpg','jpeg');

//attempting to upload the drawing image file
if (isset($_FILES['Drawing']) AND $_FILES['Drawing']['name'] !='' AND $_SESSION['Contract'.$identifier]->ContractRef!='') {

	$Result = $_FILES['Drawing']['error'];
	$ImgExt = pathinfo($_FILES['Drawing']['name'], PATHINFO_EXTENSION);

 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . $_SESSION['Contract'.$identifier]->ContractRef . '.' . $ImgExt;

	//But check for the worst
	if (!in_array ($ImgExt, $SupportedImgExt)) {
		prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['Drawing']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['Drawing']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( _('Only graphics files can be uploaded'),'warn');
		 	$UploadTheFile ='No';
	}
	foreach ($SupportedImgExt as $ext) {
		$file = $_SESSION['part_pics_dir'] . '/' . $_SESSION['Contract'.$identifier]->ContractRef . '.' . $ext;
		if (file_exists ($file) ) {
			$Result = unlink($file);
			if (!$Result){
				prnMsg(_('The existing image could not be removed'),'error');
				$UploadTheFile ='No';
			}
		}
	}

	if ($UploadTheFile=='Yes'){
		$Result  =  move_uploaded_file($_FILES['Drawing']['tmp_name'], $filename);
		$message = ($Result)?_('File url') . '<a href="' . $filename . '">' .  $filename . '</a>' : _('Something is wrong with uploading the file');
	}
}


/*The page can be called with ModifyContractRef=x where x is a contract
 * reference. The page then looks up the details of contract x and allows
 * these details to be modified */

if (isset($_GET['ModifyContractRef'])){

	if (isset($_SESSION['Contract'.$identifier])){
		unset ($_SESSION['Contract'.$identifier]->ContractBOM);
		unset ($_SESSION['Contract'.$identifier]->ContractReqts);
		unset ($_SESSION['Contract'.$identifier]);
	}

	$_SESSION['ExistingContract']=$_GET['ModifyContractRef'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Contract'.$identifier] = new Contract;

	/*read in all the guff from the selected contract into the contract Class variable  */
	$ContractRef = $_GET['ModifyContractRef'];
	include('includes/Contract_Readin.php');

}// its an existing contract to readin

if (isset($_POST['CancelContract'])) {
/*The cancel button on the header screen - to delete the contract */
	$OK_to_delete = true;	 //assume this in the first instance
	if(!isset($_SESSION['ExistingContract']) OR $_SESSION['ExistingContract']!=0) {
		/* need to check that not already ordered by the customer - status = 100  */
		if($_SESSION['Contract'.$identifier]->Status==2){
			$OK_to_delete = false;
			prnMsg( _('The contract has already been ordered by the customer the order must also be deleted first before the contract can be deleted'),'warn');
		}
	}

	if ($OK_to_delete==true){
		$SQL = "DELETE FROM contractbom WHERE contractref='" . $_SESSION['Contract'.$identifier]->ContractRef . "'";
		$ErrMsg = _('The contract bill of materials could not be deleted because');
		$DelResult=DB_query($SQL,$ErrMsg);
		$SQL = "DELETE FROM contractreqts WHERE contractref='" . $_SESSION['Contract'.$identifier]->ContractRef . "'";
		$ErrMsg = _('The contract requirements could not be deleted because');
		$DelResult=DB_query($SQL,$ErrMsg);
		$SQL= "DELETE FROM contracts WHERE contractref='" . $_SESSION['Contract'.$identifier]->ContractRef . "'";
		$ErrMsg = _('The contract could not be deleted because');
		$DelResult=DB_query($SQL,$ErrMsg);

		if ($_SESSION['Contract'.$identifier]->Status==1){
			$SQL = "DELETE FROM salesorderdetails WHERE orderno='" . $_SESSION['Contract'.$identifier]->OrderNo . "'";
			$ErrMsg = _('The quotation lines for the contract could not be deleted because');
			$DelResult=DB_query($SQL,$ErrMsg);
			$SQL = "DELETE FROM salesorders WHERE orderno='" . $_SESSION['Contract'.$identifier]->OrderNo . "'";
			$ErrMsg = _('The quotation for the contract could not be deleted because');
			$DelResult=DB_query($SQL,$ErrMsg);
		}
		prnMsg( _('Contract').' '.$_SESSION['Contract'.$identifier]->ContractRef.' '._('has been cancelled'), 'success');
		unset($_SESSION['ExistingContract']);
		unset($_SESSION['Contract'.$identifier]->ContractBOM);
		unset($_SESSION['Contract'.$identifier]->ContractReqts);
		unset($_SESSION['Contract'.$identifier]);
	}
}

if (!isset($_SESSION['Contract'.$identifier])){
	/* It must be a new contract being created
	 * $_SESSION['Contract'.$identifier] would be set up from the order modification
	 * code above if a modification to an existing contract. Also
	 * $ExistingContract would be set to the ContractRef
	 * */
		$_SESSION['ExistingContract']= 0;
		$_SESSION['Contract'.$identifier] = new Contract;

		if ($_SESSION['Contract'.$identifier]->DebtorNo==''
				OR !isset($_SESSION['Contract'.$identifier]->DebtorNo)){

/* a session variable will have to maintain if a supplier
 * has been selected for the order or not the session
 * variable CustomerID holds the supplier code already
 * as determined from user id /password entry  */
			$_SESSION['RequireCustomerSelection'] = 1;
		} else {
			$_SESSION['RequireCustomerSelection'] = 0;
		}
}

if (isset($_POST['CommitContract']) OR isset($_POST['CreateQuotation'])){
	/*This is the bit where the contract object is commited to the database after a bit of error checking */

	//First update the session['Contract'.$identifier] variable with all inputs from the form

	$InputError = False; //assume no errors on input then test for errors
	if (mb_strlen($_POST['ContractRef']) < 2){
		prnMsg(_('The contract reference is expected to be more than 2 characters long. Please alter the contract reference before proceeding.'),'error');
		$InputError = true;
	}
	if(ContainsIllegalCharacters($_POST['ContractRef'])){
		prnMsg(_('The contract reference cannot contain any spaces, slashes, or inverted commas. Please alter the contract reference before proceeding.'),'error');
		$InputError = true;
	}

	//The contractRef cannot be the same as an existing stockid or contractref
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['ContractRef'] . "'");
	if (DB_num_rows($Result)==1 AND $_SESSION['Contract'.$identifier]->Status ==0){
		prnMsg(_('The contract reference cannot be the same as a previously created stock item. Please modify the contract reference before continuing'),'error');
		$InputError=true;
	}
	if (mb_strlen($_POST['ContractDescription'])<10){
		prnMsg(_('The contract description is expected to be more than 10 characters long. Please alter the contract description in full before proceeding.'),'error');
		$InputError = true;
	}
	if (! Is_Date($_POST['RequiredDate'])){
		prnMsg (_('The date the contract is required to be completed by must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$InputError =true;
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['RequiredDate']) AND $_POST['RequiredDate']!=''){
		prnMsg(_('The date that the contract is to be completed by is expected to be a date in the future. Make the required date a date after today before proceeding.'),'error');
		$InputError =true;
	}

	if (!$InputError) {
		$_SESSION['Contract'.$identifier]->ContractRef=$_POST['ContractRef'];
		$_SESSION['Contract'.$identifier]->ContractDescription=$_POST['ContractDescription'];
		$_SESSION['Contract'.$identifier]->CategoryID = $_POST['CategoryID'];
		$_SESSION['Contract'.$identifier]->LocCode = $_POST['LocCode'];
		$_SESSION['Contract'.$identifier]->RequiredDate = $_POST['RequiredDate'];
		$_SESSION['Contract'.$identifier]->Margin = filter_number_format($_POST['Margin']);
		$_SESSION['Contract'.$identifier]->Status = $_POST['Status'];
		$_SESSION['Contract'.$identifier]->CustomerRef = $_POST['CustomerRef'];
		$_SESSION['Contract'.$identifier]->ExRate = filter_number_format($_POST['ExRate']);

		/*Get the first work centre for the users location - until we set this up properly */
		$Result = DB_query("SELECT code FROM workcentres WHERE location='" . $_SESSION['Contract'.$identifier]->LocCode ."'");
		if (DB_num_rows($Result)>0){
			$WorkCentreRow = DB_fetch_row($Result);
			$WorkCentre = $WorkCentreRow[0];
		} else { //need to add a default work centre for the location
			$Result = DB_query("INSERT INTO workcentres (code,
														location,
														description,
														overheadrecoveryact)
											VALUES ('" . $_SESSION['Contract'.$identifier]->LocCode . "',
													'" . $_SESSION['Contract'.$identifier]->LocCode . "',
													'" . _('Default for') . ' ' . $_SESSION['Contract'.$identifier]->LocCode . "',
													'1')");
			$WorkCentre = $_SESSION['Contract'.$identifier]->LocCode;
		}
		/*The above is a bit of a hack to get a default workcentre for a location based on the users default location*/
	}

	$SQL = "SELECT contractref,
					debtorno,
					branchcode,
					categoryid,
					loccode,
					requireddate,
					margin,
					customerref,
					exrate,
					status
			FROM contracts
			WHERE contractref='" . $_POST['ContractRef'] . "'";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==1){ // then we have an existing contract with this contractref
		$ExistingContract = DB_fetch_array($Result);
		if ($ExistingContract['debtorno'] != $_SESSION['Contract'.$identifier]->DebtorNo){
			prnMsg(_('The contract reference cannot be the same as a previously created contract for another customer. Please modify the contract reference before continuing'),'error');
			$InputError=true;
		}

		if($ExistingContract['status']<=1 AND ! $InputError){
			//then we can accept any changes at all do an update on the whole lot
			$SQL = "UPDATE contracts SET categoryid = '" . $_POST['CategoryID'] ."',
										requireddate = '" . FormatDateForSQL($_POST['RequiredDate']) . "',
										loccode='" . $_POST['LocCode'] . "',
										margin = '" . filter_number_format($_POST['Margin']) . "',
										customerref = '" . $_POST['CustomerRef'] . "',
										exrate = '" . filter_number_format($_POST['ExRate']) . "'
							WHERE contractref ='" . $_POST['ContractRef'] . "'";
			$ErrMsg = _('Cannot update the contract because');
			$Result = DB_query($SQL,$ErrMsg);
			/* also need to update the items on the contract BOM  - delete the existing contract BOM then add these items*/
			$Result = DB_query("DELETE FROM contractbom WHERE contractref='" .$_POST['ContractRef'] . "'");
			$ErrMsg = _('Could not add a component to the contract bill of material');
			foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component){
				$SQL = "INSERT INTO contractbom (contractref,
												stockid,
												workcentreadded,
												quantity)
											VALUES ( '" . $_POST['ContractRef'] . "',
												'" . $Component->StockID . "',
												'" . $WorkCentre . "',
												'" . $Component->Quantity . "')";
				$Result = DB_query($SQL,$ErrMsg);
			}

			/*also need to update the items on the contract requirements  - delete the existing database entries then add these */
			$Result = DB_query("DELETE FROM contractreqts WHERE contractref='" .$_POST['ContractRef'] . "'");
			$ErrMsg = _('Could not add a requirement to the contract requirements');
			foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement){
				$SQL = "INSERT INTO contractreqts (contractref,
													requirement,
													costperunit,
													quantity)
												VALUES (
													'" . $_POST['ContractRef'] . "',
													'" . $Requirement->Requirement . "',
													'" . $Requirement->CostPerUnit . "',
													'" . $Requirement->Quantity . "')";
				$Result = DB_query($SQL,$ErrMsg);
			}

			prnMsg(_('The changes to the contract have been committed to the database'),'success');
		}
		if ($ExistingContract['status']==1 AND ! $InputError){
			//then the quotation will need to be updated with the revised contract cost if necessary
			$ContractBOMCost =0;
			foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component) {
				$ContractBOMCost += ($Component->ItemCost *  $Component->Quantity);
			}
			$ContractReqtsCost=0;
			foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement) {
				$ContractReqtsCost += ($Requirement->CostPerUnit * $Requirement->Quantity);
			}
			$ContractCost = $ContractReqtsCost+$ContractBOMCost;
			$ContractPrice = ($ContractBOMCost+$ContractReqtsCost)/((100-$_SESSION['Contract'.$identifier]->Margin)/100);

			$SQL = "UPDATE stockmaster SET description='" . $_SESSION['Contract'.$identifier]->ContractDescription . "',
											longdescription='" . $_SESSION['Contract'.$identifier]->ContractDescription . "',
											categoryid = '" . $_SESSION['Contract'.$identifier]->CategoryID . "',
											materialcost= '" . $ContractCost . "'
										WHERE stockid ='" . $_SESSION['Contract'.$identifier]->ContractRef."'";
			$ErrMsg =  _('The contract item could not be updated because');
			$DbgMsg = _('The SQL that was used to update the contract item failed was');
			$InsertNewItemResult = DB_query($SQL, $ErrMsg, $DbgMsg);

			//update the quotation
			$SQL = "UPDATE salesorderdetails
						SET unitprice = '" . $ContractPrice* $_SESSION['Contract'.$identifier]->ExRate . "'
						WHERE stkcode='" .  $_SESSION['Contract'.$identifier]->ContractRef . "'
						AND orderno='" .  $_SESSION['Contract'.$identifier]->OrderNo . "'";
			$ErrMsg = _('The contract quotation could not be updated because');
			$DbgMsg = _('The SQL that failed to update the quotation was');
			$UpdQuoteResult = DB_query($SQL,$ErrMsg,$DbgMsg);
			prnMsg(_('The contract quotation has been updated based on the new contract cost and margin'),'success');
			echo '<br /><a href="' .$RootPath . '/SelectSalesOrder.php?OrderNumber=' .  $_SESSION['Contract'.$identifier]->OrderNo . '&amp;Quotations=Quotes_Only">' . _('Go to Quotation') . ' ' .  $_SESSION['Contract'.$identifier]->OrderNo . '</a>';

		}
		if ($ExistingContract['status'] == 0 AND $_POST['Status']==1){
			/*we are updating the status on the contract to a quotation so we need to
			 * add a new item for the contract into the stockmaster
			 * add a salesorder header and detail as a quotation for the item
			 */


		}
	} elseif (!$InputError) { /*Its a new contract - so insert */

		$SQL = "INSERT INTO contracts ( contractref,
										debtorno,
										branchcode,
										contractdescription,
										categoryid,
										loccode,
										requireddate,
										margin,
										customerref,
										exrate)
					VALUES ('" . $_POST['ContractRef'] . "',
							'" . $_SESSION['Contract'.$identifier]->DebtorNo  . "',
							'" . $_SESSION['Contract'.$identifier]->BranchCode . "',
							'" . $_POST['ContractDescription'] . "',
							'" . $_POST['CategoryID'] . "',
							'" . $_POST['LocCode'] . "',
							'" . FormatDateForSQL($_POST['RequiredDate']) . "',
							'" . filter_number_format($_POST['Margin']) . "',
							'" . $_POST['CustomerRef'] . "',
							'". filter_number_format($_POST['ExRate']) ."')";

		$ErrMsg = _('The new contract could not be added because');
		$Result = DB_query($SQL,$ErrMsg);

		/*Also need to add the reqts and contracbom*/
		$ErrMsg = _('Could not add a component to the contract bill of material');
		foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component){
			$SQL = "INSERT INTO contractbom (contractref,
											stockid,
											workcentreadded,
											quantity)
							VALUES ('" . $_POST['ContractRef'] . "',
									'" . $Component->StockID . "',
									'" . $WorkCentre . "',
									'" . $Component->Quantity . "')";
			$Result = DB_query($SQL,$ErrMsg);
		}

		$ErrMsg = _('Could not add a requirement to the contract requirements');
		foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement){
			$SQL = "INSERT INTO contractreqts (contractref,
												requirement,
												costperunit,
												quantity)
							VALUES ( '" . $_POST['ContractRef'] . "',
									'" . $Requirement->Requirement . "',
									'" . $Requirement->CostPerUnit . "',
									'" . $Requirement->Quantity . "')";
			$Result = DB_query($SQL,$ErrMsg);
		}
		prnMsg(_('The new contract has been added to the database'),'success');

	} //end of adding a new contract
}//end of commital to database

if(isset($_POST['CreateQuotation']) AND !$InputError){
//Create a quotation for the contract as entered
//First need to create the item in stockmaster

//calculate the item's contract cost
	$ContractBOMCost =0;
	foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component) {
		$ContractBOMCost += ($Component->ItemCost *  $Component->Quantity);
	}
	$ContractReqtsCost=0;
	foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement) {
		$ContractReqtsCost += ($Requirement->CostPerUnit * $Requirement->Quantity);
	}
	$ContractCost = $ContractReqtsCost+$ContractBOMCost;
	$ContractPrice = ($ContractBOMCost+$ContractReqtsCost)/((100-$_SESSION['Contract'.$identifier]->Margin)/100);

//Check if the item exists already
	$SQL = "SELECT stockid FROM stockmaster WHERE stockid='" . $_SESSION['Contract'.$identifier]->ContractRef."'";
	$ErrMsg =  _('The item could not be retrieved because');
	$DbgMsg = _('The SQL that was used to find the item failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	if (DB_num_rows($Result)==0) { //then the item doesn't currently exist so add it

		$SQL = "INSERT INTO stockmaster (stockid,
										description,
										longdescription,
										categoryid,
										mbflag,
										taxcatid,
										materialcost)
							VALUES ('" . $_SESSION['Contract'.$identifier]->ContractRef."',
									'" . $_SESSION['Contract'.$identifier]->ContractDescription . "',
									'" . $_SESSION['Contract'.$identifier]->ContractDescription . "',
									'" . $_SESSION['Contract'.$identifier]->CategoryID . "',
									'M',
									'" . $_SESSION['DefaultTaxCategory'] . "',
									'" . $ContractCost . "')";
		$ErrMsg =  _('The new contract item could not be added because');
		$DbgMsg = _('The SQL that was used to insert the contract item failed was');
		$InsertNewItemResult = DB_query($SQL, $ErrMsg, $DbgMsg);
		$SQL = "INSERT INTO locstock (loccode,
										stockid)
						SELECT locations.loccode,
								'" . $_SESSION['Contract'.$identifier]->ContractRef . "'
						FROM locations";

		$ErrMsg =  _('The locations for the item') . ' ' . $_SESSION['Contract'.$identifier]->ContractRef . ' ' . _('could not be added because');
		$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
		$InsLocnsResult = DB_query($SQL,$ErrMsg,$DbgMsg);
	}
	//now add the quotation for the item

	//first need to get some more details from the customer/branch record
	$SQL = "SELECT debtorsmaster.salestype,
					custbranch.defaultshipvia,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.phoneno,
					custbranch.email,
					custbranch.defaultlocation
				FROM debtorsmaster INNER JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				WHERE debtorsmaster.debtorno='" . $_SESSION['Contract'.$identifier]->DebtorNo  . "'
				AND custbranch.branchcode='" . $_SESSION['Contract'.$identifier]->BranchCode . "'";
	$ErrMsg =  _('The customer and branch details could not be retrieved because');
	$DbgMsg = _('The SQL that was used to find the customer and branch details failed was');
	$CustomerDetailsResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	$CustomerDetailsRow = DB_fetch_array($CustomerDetailsResult);

	//start a DB transaction
	DB_Txn_Begin();
	$OrderNo = GetNextTransNo(30);
	$HeaderSQL = "INSERT INTO salesorders (	orderno,
											debtorno,
											branchcode,
											customerref,
											orddate,
											ordertype,
											shipvia,
											deliverto,
											deladd1,
											deladd2,
											deladd3,
											deladd4,
											deladd5,
											deladd6,
											contactphone,
											contactemail,
											fromstkloc,
											deliverydate,
											quotedate,
											quotation)
										VALUES (
											'". $OrderNo . "',
											'" . $_SESSION['Contract'.$identifier]->DebtorNo  . "',
											'" . $_SESSION['Contract'.$identifier]->BranchCode . "',
											'". $_SESSION['Contract'.$identifier]->CustomerRef ."',
											'" . Date('Y-m-d H:i') . "',
											'" . $CustomerDetailsRow['salestype'] . "',
											'" . $CustomerDetailsRow['defaultshipvia'] ."',
											'". $CustomerDetailsRow['brname'] . "',
											'" . $CustomerDetailsRow['braddress1'] . "',
											'" . $CustomerDetailsRow['braddress2'] . "',
											'" . $CustomerDetailsRow['braddress3'] . "',
											'" . $CustomerDetailsRow['braddress4'] . "',
											'" . $CustomerDetailsRow['braddress5'] . "',
											'" . $CustomerDetailsRow['braddress6'] . "',
											'" . $CustomerDetailsRow['phoneno'] . "',
											'" . $CustomerDetailsRow['email'] . "',
											'" . $_SESSION['Contract'.$identifier]->LocCode ."',
											'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->RequiredDate) . "',
											'" . Date('Y-m-d') . "',
											'1' )";

	$ErrMsg = _('The quotation cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$ErrMsg,true);
	$LineItemSQL = "INSERT INTO salesorderdetails ( orderlineno,
													orderno,
													stkcode,
													unitprice,
													quantity,
													poline,
													itemdue)
										VALUES ('0',
												'" . $OrderNo . "',
												'" . $_SESSION['Contract'.$identifier]->ContractRef . "',
												'" . ($ContractPrice * $_SESSION['Contract'.$identifier]->ExRate) . "',
												'1',
												'" . $_SESSION['Contract'.$identifier]->CustomerRef . "',
												'" . FormatDateForSQL($_SESSION['Contract'.$identifier]->RequiredDate) . "')";
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('Unable to add the quotation line');
	$Ins_LineItemResult = DB_query($LineItemSQL,$ErrMsg,$DbgMsg,true);
	 //end of adding the quotation to salesorders/details

	//make the status of the contract 1 - to indicate that it is now quoted
	$SQL = "UPDATE contracts SET orderno='" . $OrderNo . "',
								status='" . 1 . "'
						WHERE contractref='" . DB_escape_string($_SESSION['Contract'.$identifier]->ContractRef) . "'";
	$ErrMsg = _('Unable to update the contract status and order number because');
	$UpdContractResult = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	DB_Txn_Commit();
	$_SESSION['Contract'.$identifier]->Status=1;
	$_SESSION['Contract'.$identifier]->OrderNo=$OrderNo;
	prnMsg(_('The contract has been made into quotation number') . ' ' . $OrderNo,'info');
	echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?OrderNumber=' . $OrderNo . '&amp;Quotations=Quotes_Only">' . _('Go to quotation number:') . ' ' . $OrderNo . '</a>';

} //end of if making a quotation

if (isset($_POST['SearchCustomers'])){

	if (($_POST['CustKeywords']!='') AND (($_POST['CustCode']!='') OR ($_POST['CustPhone']!=''))) {
		prnMsg( _('Customer Branch Name keywords have been used in preference to the Customer Branch Code or Branch Phone Number entered'), 'warn');
	}
	if (($_POST['CustCode']!='') AND ($_POST['CustPhone']!='')) {
		prnMsg(_('Customer Branch Code has been used in preference to the Customer Branch Phone Number entered'), 'warn');
	}
	if (mb_strlen($_POST['CustKeywords'])>0) {
	//insert wildcard characters in spaces
		$_POST['CustKeywords'] = mb_strtoupper(trim($_POST['CustKeywords']));
		$SearchString = '%' . str_replace(' ', '%', $_POST['CustKeywords']) . '%';

		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					WHERE custbranch.brname " . LIKE . " '$SearchString'
						AND custbranch.disabletrans=0
					ORDER BY custbranch.debtorno, custbranch.branchcode";

	} elseif (mb_strlen($_POST['CustCode'])>0){

		$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));

		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					WHERE custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%'
						AND custbranch.disabletrans=0
					ORDER BY custbranch.debtorno";

	} elseif (mb_strlen($_POST['CustPhone'])>0){
		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
						AND custbranch.disabletrans=0
					ORDER BY custbranch.debtorno";
	} else {
		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					WHERE custbranch.disabletrans=0
					ORDER BY custbranch.debtorno";
	}

	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	$Result_CustSelect = DB_query($SQL,$ErrMsg);

	if (DB_num_rows($Result_CustSelect)==0){
		prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'),'info');
	}
} /*one of keywords or custcode was more than a zero length string */

if (isset($_POST['SelectedCustomer'])) {

/* will only be true if page called from customer selection form
 * or set because only one customer record returned from a search
 * so parse the $Select string into debtorno and branch code */


	$_SESSION['Contract'.$identifier]->DebtorNo  = $_POST['SelectedCustomer'];
	$_SESSION['Contract'.$identifier]->BranchCode = $_POST['SelectedBranch'];

	$SQL = "SELECT debtorsmaster.name,
					custbranch.brname,
					debtorsmaster.currcode,
					debtorsmaster.holdreason,
					holdreasons.dissallowinvoices,
					currencies.rate
			FROM debtorsmaster INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			WHERE debtorsmaster.debtorno='" . $_SESSION['Contract'.$identifier]->DebtorNo  . "'
			AND custbranch.branchcode='" . $_SESSION['Contract'.$identifier]->BranchCode . "'" ;

	$ErrMsg = _('The customer record selected') . ': ' . $_SESSION['Contract'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg);
	$MyRow = DB_fetch_array($Result);
	if (DB_num_rows($Result)==0){
		prnMsg(_('The customer details were unable to be retrieved'),'error');
		if ($debug==1){
			prnMsg(_('The SQL used that failed to get the customer details was:') . '<br />' . $SQL,'error');
		}
	} else {
		$_SESSION['Contract'.$identifier]->BranchName = $MyRow['brname'];
		$_SESSION['RequireCustomerSelection'] = 0;
		$_SESSION['Contract'.$identifier]->CustomerName = $MyRow['name'];
		$_SESSION['Contract'.$identifier]->CurrCode = $MyRow['currcode'];
		$_SESSION['Contract'.$identifier]->ExRate = $MyRow['rate'];

		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit contracts */
			$CreditAvailable = GetCreditAvailable($_SESSION['Contract'.$identifier]->DebtorNo);
			if ($_SESSION['CheckCreditLimits']==1 AND $CreditAvailable <=0){
				prnMsg(_('The') . ' ' . $_SESSION['Contract'.$identifier]->CustomerName . ' ' . _('account is currently at or over their credit limit'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $CreditAvailable <=0){
				prnMsg(_('No more orders can be placed by') . ' ' . $MyRow[0] . ' ' . _(' their account is currently at or over their credit limit'),'warn');
				include('includes/footer.php');
				exit;
			}
		}
	} //a customer was retrieved ok
} //end if a customer has just been selected


if (!isset($_SESSION['Contract'.$identifier]->DebtorNo)
		OR $_SESSION['Contract'.$identifier]->DebtorNo=='' ) {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/contract.png" title="' . _('Contract') . '" alt="" />' . ' ' . _('Contract: Select Customer') . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" name="CustomerSelection" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend class="search">', _('Search Criteria'), '</legend>
			<field>
				<label for="CustKeywords">', _('Part of the Customer Branch Name'), ':</label>
				<input type="search" name="CustKeywords" autofocus="autofocus" maxlength="25" />
			</field>
			<h1>', _('OR'), '</h1>
			<field>
				<label for="CustCode">', _('Part of the Customer Branch Code'), ':</label>
				<input type="search" name="CustCode" maxlength="18" />
			</field>
			<h1>', _('OR'), '</h1>
			<field>
				<label for="CustPhone">', _('Part of the Branch Phone Number'), ':</label>
				<input type="search" name="CustPhone" maxlength="18" />
			</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="SearchCustomers" value="', _('Search Now'), '" />
			<input type="reset" name="reset" value="', _('Reset'), '" />
		</div>';

	if (isset($Result_CustSelect)) {

		echo '<br /><table cellpadding="2" class="selection">';

		$TableHeader = '<tr>
							<th>' . _('Customer') . '</th>
							<th>' . _('Branch') . '</th>
							<th>' . _('Contact') . '</th>
							<th>' . _('Phone') . '</th>
							<th>' . _('Fax') . '</th>
						</tr>';
		echo $TableHeader;

		$j = 1;

		$LastCustomer='';
		while ($MyRow=DB_fetch_array($Result_CustSelect)) {
			if ($LastCustomer != $MyRow['name']) {
				echo '<tr class="striped_row"><td>' .  $MyRow['name']  . '</td>';
			} else {
				echo '<tr class="striped_row"><td></td>';
			}
			echo '<td><input type="submit" name="Submit'.$j.'" value="' . $MyRow['brname'] . '" /></td>
					<input type="hidden" name="SelectedCustomer'.$j.'" value="'. $MyRow['debtorno'] . '" />
					<input type="hidden" name="SelectedBranch'.$j.'" value="' . $MyRow['branchcode'] . '" />
					<td>' . $MyRow['contactname']  . '</td>
					<td>' . $MyRow['phoneno'] . '</td>
					<td>' . $MyRow['faxno'] . '</td>
					</tr>';
			$LastCustomer=$MyRow['name'];
			$j++;
//end of page full new headings if
		}
//end of while loop

		echo '</table>';
	}//end if results to show

	echo '</form>';

//end if RequireCustomerSelection
} else { /*A customer is already selected so get into the contract setup proper */

	echo '<form name="ContractEntry" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/contract.png" title="' . _('Contract') . '" alt="" /> ' . $_SESSION['Contract'.$identifier]->CustomerName;

	if ($_SESSION['CompanyRecord']['currencydefault'] != $_SESSION['Contract'.$identifier]->CurrCode){
		echo ' - ' . _('All amounts stated in') . ' ' . $_SESSION['Contract'.$identifier]->CurrCode . '<br />';
	}
	if ($_SESSION['ExistingContract']) {
		echo  _('Modify Contract') . ': ' . $_SESSION['Contract'.$identifier]->ContractRef;
	}
	echo '</p>';

	$SQL = "SELECT code, description FROM workcentres INNER JOIN locationusers ON locationusers.loccode=workcentres.location AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$wcresults = DB_query($SQL);

	if (DB_num_rows($wcresults)==0){
		prnMsg( _('There are no work centres set up yet') . '. ' . _('Please use the link below to set up work centres'),'warn');
		echo '<br /><a href="'.$RootPath.'/WorkCentres.php">' . _('Work Centre Maintenance') . '</a>';
		include('includes/footer.php');
		exit;
	}

	echo '<fieldset>
			<legend>', _('Contract Header'), '</legend>
			<field>
				<label for="ContractRef">', _('Contract Reference'), ':</label>';
	if ($_SESSION['Contract' . $identifier]->Status == 0) {
		/*Then the contract has not become an order yet and we can allow changes to the ContractRef */
		echo '<input type="text" name="ContractRef" size="21" autofocus="autofocus" required="required" maxlength="20" value="', $_SESSION['Contract' . $identifier]->ContractRef, '" />';
	} else {
		/*Just show the contract Ref - dont allow modification */
		echo '<input type="hidden" name="ContractRef" value="', $_SESSION['Contract' . $identifier]->ContractRef, '" />', $_SESSION['Contract' . $identifier]->ContractRef;
	}
	echo '</field>';

	echo '<field>
			<label for="CategoryID">', _('Category'), ':</label>
			<select name="CategoryID">';
	$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
	$ErrMsg = _('The stock categories could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	while ($MyRow = DB_fetch_array($Result)) {
		if (!isset($_SESSION['Contract' . $identifier]->CategoryID) or $MyRow['categoryid'] == $_SESSION['Contract' . $identifier]->CategoryID) {
			echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
		} else {
			echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
		}
	}
	echo '</select>';
	echo '&nbsp;<a target="_blank" href="', $RootPath, '/StockCategories.php">', _('Add or Modify Contract Categories'), '</a>
		</field>';

	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1";
	$ErrMsg = _('The stock locations could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve stock locations and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<field>
			<label for="LocCode">', _('Location'), ':</label>
			<select name="LocCode">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (!isset($_SESSION['Contract' . $identifier]->LocCode) or $MyRow['loccode'] == $_SESSION['Contract' . $identifier]->LocCode) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	}
	echo '</select>
		</field>';

	$SQL = "SELECT  code,
					description
				FROM workcentres
				INNER JOIN locationusers
					ON locationusers.loccode=workcentres.location
					AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$WcResults = DB_query($SQL);
	if (DB_num_rows($WcResults) == 0) {
		prnMsg(_('There are no work centres set up yet') . '. ' . _('Please use the link below to set up work centres'), 'warn');
		echo '<br /><a href="', $RootPath, '/WorkCentres.php">', _('Work Centre Maintenance'), '</a>';
		include ('includes/footer.php');
		exit;
	}
	echo '<field>
			<label for="DefaultWorkCentre">', _('Default Work Centre'), ': </label>
			<select name="DefaultWorkCentre">';

	while ($MyRow = DB_fetch_array($WcResults)) {
		if (isset($_POST['DefaultWorkCentre']) and $MyRow['code'] == $_POST['DefaultWorkCentre']) {
			echo '<option selected="selected" value="', $MyRow['code'], '">', $MyRow['description'], '</option>';
		} else {
			echo '<option value="', $MyRow['code'], '">', $MyRow['description'], '</option>';
		}
	} //end while loop
	echo '</select>
		</field>';

	echo '<field>
			<label for="ContractDescription">', _('Contract Description'), ':</label>
			<textarea name="ContractDescription" required="required" style="rows="8" cols="50">', $_SESSION['Contract' . $identifier]->ContractDescription, '</textarea>
		</field>';

	$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $_SESSION['Contract' . $identifier]->ContractRef . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
	$ImageFile = reset($ImageFileArray);
	echo '<field>
			<label for="Drawing">', _('Drawing File'), ' ', implode(", ", $SupportedImgExt), ' ', _('format only'), ':</label>
			<input type="file" id="Drawing" name="Drawing" value="', $ImageFile, '" />
		</field>';

	if (!isset($_SESSION['Contract' . $identifier]->RequiredDate)) {
		$_SESSION['Contract' . $identifier]->RequiredDate = DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 1);
	}

	echo '<field>
			<label for="RequiredDate">', _('Required Date'), ':</label>
			<input type="date" name="RequiredDate" size="11" value="', FormatDateForSQL($_SESSION['Contract' . $identifier]->RequiredDate), '" />
		</field>';

	echo '<field>
			<label for="CustomerRef">', _('Customer Reference'), ':</label>
			<input type="text" name="CustomerRef" size="21" maxlength="20" value="', $_SESSION['Contract' . $identifier]->CustomerRef, '" />
		</field>';

	if (!isset($_SESSION['Contract' . $identifier]->Margin)) {
		$_SESSION['Contract' . $identifier]->Margin = 50;
	}
	echo '<field>
			<label for="Margin">', _('Gross Profit'), ' %:</label>
			<input class="number" type="text" name="Margin" size="6" required="required" maxlength="6" value="', locale_number_format($_SESSION['Contract' . $identifier]->Margin, 2), '" />
		</field>';

	if ($_SESSION['CompanyRecord']['currencydefault'] != $_SESSION['Contract' . $identifier]->CurrCode) {
		echo '<field>
				<label for="ExRate">', $_SESSION['Contract' . $identifier]->CurrCode, ' ', _('Exchange Rate'), ':</label>
				<input class="number" type="text" name="ExRate" size="10" required="required" maxlength="10" value="', locale_number_format($_SESSION['Contract' . $identifier]->ExRate, 'Variable'), '" />
			</field>';
	} else {
		echo '<input type="hidden" name="ExRate" value="', locale_number_format($_SESSION['Contract' . $identifier]->ExRate, 'Variable'), '" />';
	}

	echo '<field>
			<label for="Status">', _('Contract Status'), ':</label>';

	$StatusText = array();
	$StatusText[0] = _('Setup');
	$StatusText[1] = _('Quote');
	$StatusText[2] = _('Completed');
	echo '<div class="fieldtext">';
	if ($_SESSION['Contract' . $identifier]->Status == 0) {
		echo _('Contract Setup');
	} elseif ($_SESSION['Contract' . $identifier]->Status == 1) {
		echo _('Customer Quoted');
	} elseif ($_SESSION['Contract' . $identifier]->Status == 2) {
		echo _('Order Placed');
	}
	echo '<input type="hidden" name="Status" value="', $_SESSION['Contract' . $identifier]->Status, '" />';
	echo '</div>
		</field>';

	if ($_SESSION['Contract' . $identifier]->Status >= 1) {
		echo '<field>
				<td>' . _('Quotation Reference/Sales Order No') . ':</td>
				<td><a href="' . $RootPath . '/SelectSalesOrder.php?OrderNumber=' . urlencode($_SESSION['Contract' . $identifier]->OrderNo) . '&amp;Quotations=Quotes_Only">' . $_SESSION['Contract' . $identifier]->OrderNo . '</a></td>
			</field>';
	}
	if ($_SESSION['Contract' . $identifier]->Status != 2 and isset($_SESSION['Contract' . $identifier]->WO)) {
		echo '<field>
				<td>' . _('Contract Work Order Ref') . ':</td>
				<td>' . $_SESSION['Contract' . $identifier]->WO . '</td>
			</field>';
	}
	echo '</fieldset>';


	echo '<table>
			<tr>
				<td>
					<table class="selection">
						<tr>
							<th colspan="6">' . _('Stock Items Required') . '</th>
						</tr>';
	$ContractBOMCost = 0;
	if (count($_SESSION['Contract'.$identifier]->ContractBOM)!=0){
		echo '<tr>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Unit') . '</th>
				<th>' . _('Unit Cost') . '</th>
				<th>' . _('Total Cost') . '</th>
			</tr>';

		foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component) {
			echo '<tr>
					<td>' . $Component->StockID . '</td>
					<td>' . $Component->ItemDescription . '</td>
					<td class="number">' . locale_number_format($Component->Quantity, $Component->DecimalPlaces) . '</td>
					<td>' . $Component->UOM . '</td>
					<td class="number">' . locale_number_format($Component->ItemCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format(($Component->ItemCost * $Component->Quantity),$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
			$ContractBOMCost += ($Component->ItemCost *  $Component->Quantity);
		}
		echo '<tr>
				<th colspan="5"><b>' . _('Total stock cost') . '</b></th>
					<th class="number"><b>' . locale_number_format($ContractBOMCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></th>
				</tr>';
	} else { //there are no items set up against this contract
		echo '<tr>
				<td colspan="6"><i>' . _('None Entered') . '</i></td>
			</tr>';
	}
	echo '</table></td>'; //end of contract BOM table
	echo '<td valign="top">
			<table class="selection">
				<tr>
					<th colspan="4">' . _('Other Requirements') . '</th>
				</tr>';
	$ContractReqtsCost = 0;
	if (count($_SESSION['Contract'.$identifier]->ContractReqts)!=0){
		echo '<tr>
				<th>' . _('Requirement') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Unit Cost') . '</th>
				<th>' . _('Total Cost') . '</th>
			</tr>';
		foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement) {
			echo '<tr>
					<td>' . $Requirement->Requirement . '</td>
					<td class="number">' . locale_number_format($Requirement->Quantity,'Variable') . '</td>
					<td class="number">' . locale_number_format($Requirement->CostPerUnit,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format(($Requirement->CostPerUnit * $Requirement->Quantity),$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
			$ContractReqtsCost += ($Requirement->CostPerUnit * $Requirement->Quantity);
		}
		echo '<tr>
				<th colspan="3"><b>' . _('Total other costs') . '</b></th>
				<th class="number"><b>' . locale_number_format($ContractReqtsCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></th>
			</tr>';
	} else { //there are no items set up against this contract
		echo '<tr>
				<td colspan="4"><i>' . _('None Entered') . '</i></td>
			</tr>';
	}
	echo '</table></td></tr></table>';
	echo '<br />';
	echo'<table class="selection">
			<tr>
				<th>' . _('Total Contract Cost') . '</th>
				<th class="number">' . locale_number_format(($ContractBOMCost+$ContractReqtsCost),$_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th>' . _('Contract Price') . '</th>
				<th class="number">' . locale_number_format(($ContractBOMCost+$ContractReqtsCost)/((100-$_SESSION['Contract'.$identifier]->Margin)/100),$_SESSION['CompanyRecord']['decimalplaces']) . '</th>
			</tr>
		</table>';

	echo'<p></p>';
	echo '<div class="centre">
			<input type="submit" name="EnterContractBOM" value="' . _('Enter Items Required') . '" />
			<input type="submit" name="EnterContractRequirements" value="' . _('Enter Other Requirements') .'" />';
	if($_SESSION['Contract'.$identifier]->Status==0){ // not yet quoted
		echo '<input type="submit" name="CommitContract" value="' . _('Commit Changes') .'" />';
	} elseif($_SESSION['Contract'.$identifier]->Status==1){ //quoted but not yet ordered
		echo '<input type="submit" name="CommitContract" value="' . _('Update Quotation') .'" />';
	}
	if($_SESSION['Contract'.$identifier]->Status==0){ //not yet quoted
		echo ' <input type="submit" name="CreateQuotation" value="' . _('Create Quotation') .'" />
			</div>';
	} else {
		echo '</div>';
	}
	if ($_SESSION['Contract'.$identifier]->Status!=2) {
		echo '<div class="centre">
				 <br />
				 <input type="submit" name="CancelContract" value="' . _('Cancel and Delete Contract') . '" />
			  </div>';
	}
	echo '</form>';
} /*end of if customer selected  and entering contract header*/

include('includes/footer.php');
?>
