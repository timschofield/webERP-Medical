#!/usr/bin/php5
<?php

include('includes/config.php');

include('includes/login.php');
include('includes/SelectModule.php');
include('includes/SelectMenuOption.php');

include('includes/validators/XhtmlValidator.php');
include('classes/URLDetails.class.php');

//Set up the session ID
//open connection
$ch = curl_init();
$TestSessionID = sha1(uniqid(mt_rand(), true));

$IndexPage=webERPLogIn($ch, $TestSessionID, $RootPath, $ServerPath, $CompanyName, $UserName, $Password);
$APPage=FindModule($ch, $RootPath, $ServerPath, $TestSessionID, $IndexPage, 'Payables');
$SupplierPage=ChooseMenuOption($ch, $RootPath, $ServerPath, $TestSessionID, $APPage, 'Add Supplier');
//echo $SupplierPage[0];
curl_close($ch);

?>