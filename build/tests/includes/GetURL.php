<?php

//url-ify the data for the POST
$fields_string='';
foreach($fields as $key=>$value) {
	$fields_string .= $key.'='.$value.'&';
}
rtrim($fields_string,'&');

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_POST,count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
curl_setopt($ch,CURLOPT_COOKIEJAR,'/tmp/curl.txt');

//execute post
$result = curl_exec($ch);

?>