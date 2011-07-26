<?php

class URLDetails {

	private $SessionID;
	private $URL;
	private $PostArray;
	public $xml;

	function __construct($SessionID) {
		$this->SessionID = $SessionID;
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
				if ($object != "." && $object != "..") {
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

	private function GetTextDetails($xml) {
		$Texts=array();
		$result=$xml->xpath('//input');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				if ($key=='type' and $value=='text') {
					foreach ($result[$i]->attributes() as $key=>$value) {
						$Texts['text'][$j][$key]=(string)$value[0];
					}
					$j++;
				}
			}
		}
		return $Texts;
	}

	private function GetSubmitDetails($xml) {
		$Submits=array();
		$result=$xml->xpath('//input');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				if ($key=='type' and $value=='submit') {
					foreach ($result[$i]->attributes() as $key=>$value) {
						$Submits['submit'][$j][$key]=(string)$value[0];
					}
					$j++;
				}
			}
		}
		return $Submits;
	}

	private function GetRadioDetails($xml) {
		$Radios=array();
		$result=$xml->xpath('//input');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				if ($key=='type' and $value=='radio') {
					foreach ($result[$i]->attributes() as $key=>$value) {
						$Radios['radio'][$j][$key]=(string)$value[0];
					}
					$j++;
				}
			}
		}
		return $Radios;
	}

	private function GetCheckBoxDetails($xml) {
		$CheckBoxs=array();
		$result=$xml->xpath('//input');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				if ($key=='type' and $value=='checkbox') {
					foreach ($result[$i]->attributes() as $key=>$value) {
						$CheckBoxs['checkbox'][$j][$key]=(string)$value[0];
					}
					$j++;
				}
			}
		}
		return $CheckBoxs;
	}

	private function GetHiddenDetails($xml) {
		$Hiddens=array();
		$result=$xml->xpath('//input');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				if ($key=='type' and $value=='hidden') {
					foreach ($result[$i]->attributes() as $key=>$value) {
						$Hiddens['hidden'][$j][$key]=(string)$value[0];
					}
					$j++;
				}
			}
		}
		return $Hiddens;
	}

	private function GetHREFDetails($xml) {
		$Links=array();
		print_r($xml);
		$result=$xml->xpath('//title');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				echo $key.' '.$value."\n";
				foreach ($result[$i]->attributes() as $key=>$value) {
					$Links['a'][$j][$key]=(string)$value[0];
				}
			}
		}
		return $Links;
	}

	private function GetPasswordDetails($xml) {
		$Passwords=array();
		$result=$xml->xpath('//input');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				if ($key=='type' and $value=='password') {
					foreach ($result[$i]->attributes() as $key=>$value) {
						$Passwords['password'][$j][$key]=(string)$value[0];
					}
					$j++;
				}
			}
		}
		return $Passwords;
	}

	private function GetSelectDetails($xml) {
		$Selects=array();
		$result=$xml->xpath('//select');
		$j = 0;
		for ($i=0; $i<sizeOf($result); $i++) {
			foreach ($result[$i]->attributes() as $key=>$value) {
				$result1=$value->xpath('//option');
				$name=(string)$value;
				for ($k=0; $k<sizeOf($result1); $k++) {
				foreach ($result1[$j]->attributes() as $key=>$value) {
					$Selects['select'][$name]['options'][$j][$key]=(string)$value;
				}
				$j++;
				}
			}
		}
		return $Selects;
	}

	public function GetFormDetails() {
		$this->FormDetails['Texts']=$this->GetTextDetails($this->xml);
		$this->FormDetails['Passwords']=$this->GetPasswordDetails($this->xml);
		$this->FormDetails['Selects']=$this->GetSelectDetails($this->xml);
		$this->FormDetails['Submits']=$this->GetSubmitDetails($this->xml);
		$this->FormDetails['Hiddens']=$this->GetHiddenDetails($this->xml);
	}

	private function ValidateHTML($html) {
		$Validator = new XhtmlValidator();
		$result=$Validator->validate($html);
		return $result;
	}

	private function ValidateLink($html) {
	}

	public function FetchPage($RootPath, $ch) {
		//url-ify the data for the POST
		$fields_string='';
		foreach($this->GetPostArray() as $key=>$value) {
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string,'&');

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$RootPath.$this->GetURL());
		curl_setopt($ch,CURLOPT_POST,count($this->GetPostArray()));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,True);
		curl_setopt($ch,CURLOPT_COOKIEJAR,'/tmp/'.$this->SessionID.'/curl.txt');

		//execute post
		$result = curl_exec($ch);

		$this->xml = simplexml_load_string($result);
		$answer = $this->ValidateHTML($result);
		$this->GetHREFDetails($this->xml);
		$this->GetFormDetails();

	}

}

?>