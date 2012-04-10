<?php
class NCYM{
var $agent = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.151 Safari/535.19";
var $cookies ="/cookies_yahoo_messenger.cookie";
var $xmlDoc;
var $login ="http://us.m.yahoo.com/w/bp-messenger/messenger/";
var $logout = "http://mlogin.yahoo.com/w/login/logout";
var $user = "user"; // user@yahoo.co.id / user@ymail.com / user@rocketmail.com
var $pass = "password";

function &getInstance(){
	static $instance;
	if(!$instance){
		$instance = new NCYM;
	}
	return $instance;
}

function getCurl($url,$post=""){
  	$curl = curl_init();
  	curl_setopt($curl, CURLOPT_URL,$url);
  	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	if($post != ""){
		curl_setopt($curl, CURLOPT_POST, 1);	
		curl_setopt($curl, CURLOPT_POSTFIELDS,$post);
	}	
  	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_USERAGENT,$this->agent);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  2); 
  	curl_setopt($curl, CURLOPT_COOKIEFILE, getcwd() . $this->cookies);
  	curl_setopt($curl, CURLOPT_COOKIEJAR, getcwd() . $this->cookies);
  	$curlData = curl_exec($curl);
  	curl_close($curl);
 	if(empty($curlData)):
		return false;
	endif;
	return $curlData;
}

function setXML($data){
  $this->xmlDoc = new DOMDocument();
  if(!$this->xmlDoc->loadHTML($data)){
  	return false;
  }
  foreach ($this->xmlDoc->getElementsByTagName("input") as $input) {
	$this->xmlDoc->input[$input->getAttribute("name")] = $input->getAttribute("value");
  }
  return true;
}
 

function getInput($name){
	if($this->xmlDoc->input[$name]){
		return $this->xmlDoc->input[$name];
	}else{
		return false;
	}	
}

function getAction(){
	return $this->xmlDoc->getElementsByTagName("form")->item(0)->getAttribute("action");
}
function send($to,$msg=""){
 	$data = $this->getCurl($this->login);
	$this->setXML($data);
	$urllogin = $this->getAction();
	$post = "_authurl=auth&_done=".$this->getInput('_done')."&_sig=&_src=&_ts=".$this->getInput('_ts'). "&_crumb=".$this->getInput('_crumb')."&_pc=&_send_userhash=0&_appdata=&_partner_ts&_is_ysid=0&_page=secure&_next=nonssl&id=". $this->user."&password=".$this->pass."&__submit=Sign+in";
	
	$data = $this->getCurl($urllogin,$post);
	$urlmessenger = $data;
  	$urlmessenger = substr($urlmessenger, strpos($urlmessenger, "<a href=\"/w/bp-messenger/sendmessage") + 9);
  	$urlmessenger = substr($urlmessenger, 0, strpos($urlmessenger, "\""));
  	$urlmessenger = str_replace("&amp;", "&", $urlmessenger);
  	$urlmessenger = "http://us.m.yahoo.com" . $urlmessenger;
	
	$data = $this->getCurl($urlmessenger);
	$this->setXML($data);
	$urlpost = "http://us.m.yahoo.com" .$this->getAction();
	$post = "id=" . $to . "&message=" . $msg . "&__submit=Send";
	if($this->getCurl($urlpost,$post)){
		return true;
	}else{
		return false;
	}	
}
function logout(){
	$this->getCurl($this->logout);
}

}
?>

<?php //example 
$ym =& NCYM::getInstance();
if($ym->send("someone@yahoo.co.id","Hello World!")){
print "Your Chat Sent...";
}
//or
if($ym->send("+62878xxxxxx","Hello World!")){
print "Your SMS Sent...";
}
$ym->logout();
?>