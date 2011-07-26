#!/usr/bin/php5
<?php

include('includes/config.php');
include('includes/login.php');

include('includes/validators/XhtmlValidator.php');
include('classes/URLDetails.class.php');

//Set up the session ID
//open connection
$ch = curl_init();

$IndexPage=webERPLogIn($ch, $RootPath, $CompanyName, $UserName, $Password);

curl_close($ch);

/*
exec('cat /tmp/'.$TestSessionID.'/login', $output);
echo $output[0];

*/
?>