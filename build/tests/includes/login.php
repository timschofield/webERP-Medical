<?php

function webERPLogIn($ch, $TestSessionID, $RootPath, $ServerPath, $Company, $UserName, $Password) {

	$LoginScreenDetails = new URLDetails($TestSessionID);
	$LoginScreenDetails->SetURL($RootPath.'index.php');
	$LoginScreenDetails->SetPostArray(array());

	$LoginScreenDetails->FetchPage($RootPath, $ServerPath, $ch);

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
	$IndexScreenDetails->SetURL($RootPath.'index.php');
	$IndexScreenDetails->SetPostArray($PostArray);

	$IndexPage=$IndexScreenDetails->FetchPage($RootPath, $ServerPath, $ch);


	return $IndexPage;
}

?>