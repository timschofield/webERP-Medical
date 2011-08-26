#!/usr/bin/php5
<?php

include('includes/config.php');

include('includes/login.php');
include('includes/SelectModule.php');
include('includes/SelectMenuOption.php');
include('includes/FillForm.php');

include('includes/validators/XhtmlValidator.php');
include('classes/URLDetails.class.php');

include('data/suppliers.data');

//Set up the session ID
//open connection
$ch = curl_init();
$TestSessionID = sha1(uniqid(mt_rand(), true));

$IndexPage=webERPLogIn($ch, $TestSessionID, $RootPath, $ServerPath, $CompanyName, $UserName, $Password);
$APPage=FindModule($ch, $RootPath, $ServerPath, $TestSessionID, $IndexPage, 'Payables');
$SupplierPage=ChooseMenuOption($ch, $RootPath, $ServerPath, $TestSessionID, $APPage, 'Add Supplier');
$PostData=FillFormWithRandomData($SupplierPage[2]);

$SupplierInsertPage = new URLDetails($TestSessionID);
$SupplierInsertPage->SetURL($ServerPath.$SupplierPage[2]['Action']);
$SupplierInsertPage->SetPostArray($PostData);

$Page=$SupplierInsertPage->FetchPage($RootPath, $ServerPath, $ch);
if (!strstr($Page[0], 'success')) {
	$InputDump = print_r($PostData, true);
	error_log('**Error**'.' The supplier does not seem to have been inserted correctly using the following data:'."\n", 3, '/home/tim/weberp'.date('Ymd').'.log');
	error_log($InputDump."\n\n", 3, '/home/tim/weberp'.date('Ymd').'.log');
}
curl_close($ch);

?>