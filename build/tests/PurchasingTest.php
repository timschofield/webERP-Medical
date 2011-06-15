#!/usr/bin/php5
<?php

include('includes/config.php');

//open connection
$ch = curl_init();

include('includes/login.php');
$ModuleName='Purchases';
include('includes/SelectModule.php');
$OptionName='Add Purchase Order';
include('includes/SelectMenuOption.php');

$CodeOrName=rand(1,2);

if ($CodeOrName==1) {
	$fields['Keywords']='z';
	$fields['SuppCode']='';
} else if ($CodeOrName==2) {
	$fields['Keywords']='';
	$fields['SuppCode']='z';
}

$fields['SearchSuppliers']='Search Now';

include('includes/GetURL.php');
echo $result;
//close connection
curl_close($ch);

?>