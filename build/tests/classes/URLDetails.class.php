<?php

class URLDetails {

	private $SessionID;
	private $URL;
	private $PostArray;
	private $FormDetails;
	public $xml;
	public $Links;

	function __construct($SessionID) {
		$this->SessionID = $SessionID;
		$this->xml = new DOMDocument();
		$this->PostArray=array();
		if (!file_exists('/tmp/'.$this->SessionID))
			mkdir('/tmp/'.$this->SessionID);
	}

	function __destruct() {
//		return $this->rrmdir('/tmp/'.$this->SessionID);
		return true;
	}

	public function GetURL() {
		return $this->URL;
	}

	public function SetURL($aURL) {
		$this->URL = $aURL;
		return true;
	}

	public function GetPostArray() {
		return $this->PostArray;
	}

	public function SetPostArray($aPostArray) {
		$this->PostArray = $aPostArray;
		return true;
	}

	private function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." and $object != "..") {
					if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		} else {
			return 1;
		}
		return 0;
	}

	public function Save($name) {
		$fh = fopen('/tmp/'.$this->SessionID.'/'.$name, 'w');
		fwrite($fh, serialize($this));
		fclose($fh);
	}

	public function Load($name) {
	}

	private function GetTextDetails() {
		$Texts=array();
		$result=$this->xml->getElementsByTagName('input');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			if ($result->item($i)->getAttribute('type')=='text') {
				for ($j=0; $j<$result->item($i)->attributes->length; $j++) {
					$name = $result->item($i)->attributes->item($j)->name;
					$Texts['text'][$k][$name]=(string)$result->item($i)->attributes->getNamedItem($name)->nodeValue;
				}
				if (!isset($Texts['text'][$k]['maxlength'])) {
					error_log('**Warning** '.$Texts['text'][$k]['name'].' in '.$this->GetURL().' has no maxlength attribute set.'."\n\n", 3, '/home/tim/weberp'.date('Ymd').'.log');
				}
				$k++;
			}
		}
		return $Texts;
	}

	private function GetSubmitDetails() {
		$Submits=array();
		$result=$this->xml->getElementsByTagName('input');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			if ($result->item($i)->getAttribute('type')=='submit') {
				for ($j=0; $j<$result->item($i)->attributes->length; $j++) {
					$name = $result->item($i)->attributes->item($j)->name;
					$Submits['submit'][$k][$name]=(string)$result->item($i)->attributes->getNamedItem($name)->nodeValue;
				}
				$k++;
			}
		}
		return $Submits;
	}

	private function GetRadioDetails() {
		$Radios=array();
		$result=$this->xml->getElementsByTagName('input');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			if ($result->item($i)->getAttribute('type')=='radio') {
				for ($j=0; $j<$result->item($i)->attributes->length; $j++) {
					$name = $result->item($i)->attributes->item($j)->name;
					$Radios['radio'][$k][$name]=(string)$result->item($i)->attributes->getNamedItem($name)->nodeValue;
				}
				$k++;
			}
		}
		return $Radios;
	}

	private function GetCheckBoxDetails() {
		$CheckBoxs=array();
		$result=$this->xml->getElementsByTagName('input');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			if ($result->item($i)->getAttribute('type')=='checkbox') {
				for ($j=0; $j<$result->item($i)->attributes->length; $j++) {
					$name = $result->item($i)->attributes->item($j)->name;
					$CheckBoxs['checkbox'][$k][$name]=(string)$result->item($i)->attributes->getNamedItem($name)->nodeValue;
				}
				$k++;
			}
		}
		return $CheckBoxs;
	}

	private function GetHiddenDetails() {
		$Hiddens=array();
		$result=$this->xml->getElementsByTagName('input');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			if ($result->item($i)->getAttribute('type')=='hidden') {
				for ($j=0; $j<$result->item($i)->attributes->length; $j++) {
					$name = $result->item($i)->attributes->item($j)->name;
					$Hiddens['hidden'][$k][$name]=(string)$result->item($i)->attributes->getNamedItem($name)->nodeValue;
				}
				$k++;
			}
		}
		return $Hiddens;
	}

	private function GetPasswordDetails() {
		$Passwords=array();
		$result=$this->xml->getElementsByTagName('input');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			if ($result->item($i)->getAttribute('type')=='password') {
				for ($j=0; $j<$result->item($i)->attributes->length; $j++) {
					$name = $result->item($i)->attributes->item($j)->name;
					$Passwords['password'][$k][$name]=(string)$result->item($i)->attributes->getNamedItem($name)->nodeValue;
				}
				$k++;
			}
		}
		return $Passwords;
	}

	private function GetSelectDetails() {
		$Selects=array();
		$result=$this->xml->getElementsByTagName('select');
		for ($i=0; $i<$result->length; $i++) {
			$SelectName=$result->item($i)->getAttribute('name');
			$result1=$result->item($i)->getElementsByTagName('option');
			for ($j=0; $j<$result1->length; $j++) {
				for ($k=0; $k<$result1->item($j)->attributes->length; $k++) {
					$name = $result1->item($j)->attributes->item($k)->name;
					$Selects['select'][$SelectName]['options'][$j][$name]=(string)$result1->item($j)->attributes->getNamedItem($name)->nodeValue;
				}
			}
		}
		return $Selects;
	}

	public function GetHREFDetails() {
		$Links=array();
		$result=$this->xml->getElementsByTagName('a');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			for ($j=0; $j<$result->item($i)->attributes->length; $j++) {
				$name = $result->item($i)->attributes->item($j)->name;
				$Links[$k][$name]=(string)$result->item($i)->attributes->getNamedItem($name)->nodeValue;
				$Links[$k]['value']=$result->item($i)->nodeValue;
			}
			$k++;
		}
		return $Links;
	}

	public function GetFormAction() {
		$Action='';
		$Passwords=array();
		$result=$this->xml->getElementsByTagName('form');
		$k=0;
		for ($i=0; $i<$result->length; $i++) {
			$Action=(string)$result->item($i)->attributes->getNamedItem('action')->nodeValue;
		}
		return $Action;
	}

	public function GetFormDetails() {
		$this->FormDetails['Texts']=$this->GetTextDetails();
		$this->FormDetails['Passwords']=$this->GetPasswordDetails();
		$this->FormDetails['Selects']=$this->GetSelectDetails();
		$this->FormDetails['Submits']=$this->GetSubmitDetails();
		$this->FormDetails['Hiddens']=$this->GetHiddenDetails();
		$this->FormDetails['Action']=$this->GetFormAction();
		return $this->FormDetails;
	}

	private function ValidateHTML($html) {
		$Validator = new XhtmlValidator();
		$result=$Validator->validate($html);
		if($Validator->validate($html) === false){
			error_log('**Error**'.'There are errors in the XHTML of page '.$this->GetURL()."\n", 3, '/home/tim/weberp'.date('Ymd').'.log');
			$Validator->logErrors();
		}
		return $result;
	}

	private function ValidateLinks($ServerPath, $ch) {
		for ($i=0; $i<sizeOf($this->Links); $i++) {
			if ($this->Links[$i]['href']!='/trunk/Logout.php') {
				curl_setopt($ch,CURLOPT_URL,$ServerPath.$this->Links[$i]['href']);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
				curl_setopt($ch,CURLOPT_COOKIEJAR,'/tmp/'.$this->SessionID.'/curl.txt');
				$result = curl_exec($ch);
				$response = curl_getinfo( $ch );
				if ($response['http_code']!=200) {
					error_log('**Warning**'.$i.' '.$this->Links[$i]['href'].' '.$response['http_code']."\n", 3, '/home/tim/weberp'.date('Ymd').'.log');
				}
			}
		}
	}

	public function FetchPage($RootPath, $ServerPath, $ch) {
		//url-ify the data for the POST
		$fields_string='';
		foreach($this->GetPostArray() as $key=>$value) {
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string,'&');

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$this->GetURL());
		curl_setopt($ch,CURLOPT_POST,count($this->GetPostArray()));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
		curl_setopt($ch,CURLOPT_COOKIEJAR,'/tmp/'.$this->SessionID.'/curl.txt');

		//execute post
		$result[0] = curl_exec($ch);

		$this->xml->loadHTML($result[0]);
		$answer = $this->ValidateHTML($result[0]);

		$this->Links=$this->GetHREFDetails();
		$this->ValidateLinks($ServerPath, $ch);

		$result[1] = $this->GetHREFDetails();
		$result[2] = $this->GetFormDetails();

		return $result;

	}

}

?>