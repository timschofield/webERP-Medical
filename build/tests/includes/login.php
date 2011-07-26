<?php

function webERPLogIn($ch, $RootPath, $Company, $UserName, $Password) {
	$TestSessionID = sha1(uniqid(mt_rand(), true));

	$LoginScreenDetails = new URLDetails($TestSessionID);
	$LoginScreenDetails->SetURL('index.php');
	$LoginScreenDetails->SetPostArray(array());

	$LoginScreenDetails->FetchPage($RootPath, $ch);

	for ($i=0; $i<sizeOf($LoginScreenDetails->FormDetails['Selects']['select']['CompanyNameField']['options']); $i++) {
		if ($LoginScreenDetails->FormDetails['Selects']['select']['CompanyNameField']['options'][$i]['label']==$Company) {
			$PostArray['CompanyNameField']=$LoginScreenDetails->FormDetails['Selects']['select']['CompanyNameField']['options'][$i]['label'];
			break;
		}
	}

	for ($i=0; $i<sizeOf($LoginScreenDetails->FormDetails['Texts']['text']); $i++) {
		if ($LoginScreenDetails->FormDetails['Texts']['text'][$i]['name']=='UserNameEntryField') {
			$PostArray['UserNameEntryField']=$UserName;
			break;
		}
	}

	for ($i=0; $i<sizeOf($LoginScreenDetails->FormDetails['Passwords']['password']); $i++) {
		if ($LoginScreenDetails->FormDetails['Passwords']['password'][$i]['name']=='Password') {
			$PostArray['Password']=$Password;
			break;
		}
	}

	for ($i=0; $i<sizeOf($LoginScreenDetails->FormDetails['Hiddens']['hidden']); $i++) {
		if ($LoginScreenDetails->FormDetails['Hiddens']['hidden'][$i]['name']=='FormID') {
			$PostArray['FormID']=$LoginScreenDetails->FormDetails['Hiddens']['hidden'][$i]['value'];
			break;
		}
	}

	for ($i=0; $i<sizeOf($LoginScreenDetails->FormDetails['Submits']['submit']); $i++) {
		if ($LoginScreenDetails->FormDetails['Submits']['submit'][$i]['name']=='SubmitUser') {
			$PostArray['SubmitUser']='Login';
			break;
		}
	}

	$IndexScreenDetails = new URLDetails($TestSessionID);
	$IndexScreenDetails->SetURL('index.php');
	$IndexScreenDetails->SetPostArray($PostArray);

	$IndexScreenDetails->FetchPage($RootPath, $ch);

	return $IndexScreenDetails;
}

?>