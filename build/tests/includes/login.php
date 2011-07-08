<?php

$url=$RootPath.'index.php';
$fields=array();
include('GetURL.php');

$FormID=substr(strstr($result, 'FormID" value="', false),15,40);

$fields['FormID']=$FormID;
$fields['CompanyNameField']=$CompanyName;
$fields['UserNameEntryField']=$UserName;
$fields['Password']=$Password;

include('GetURL.php');

?>