<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html lang="en" xml:lang="en">
<head></head
<body>
<?php
//set POST variables
$url = 'http://domain.com/get-post.php';
$fields = array(
	'username'=>urlencode(''),
	'password'=>urlencode(''),
	'btnSubmit'=>urlencode('Login')
);

//url-ify the data for the POST
$fields_string='';
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
$fields_string=rtrim($fields_string,'&');

//Login to website
$login = "https://www.schooldirectoryupdate.com/desktop/login.php";
$ch = curl_init($login);
curl_setopt($ch,CURLOPT_POST,count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

$page = curl_exec($ch);
if(curl_errno($ch)){ echo 'Curl error: ' . curl_error($ch); }
else{ echo($page); }
$info = curl_getinfo($ch);
print_r($info);
if($info['http_code']==302){ echo('Location: '.$info['redirect_url']); }

//Read list of web addresses
$listfile = 'C:\wamp\www\readin.txt';
$listhandle = fopen($listfile, "r");
/*
//while(!feof($listhandle))
//{
	$url = fgets($listhandle);
	echo $url;
	echo '<br/>';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	echo '<br/>';
	print_r(curl_exec($ch));
	if(curl_errno($ch)){ echo 'Curl error: ' . curl_error($ch); }
//}*/
curl_close($ch);
fclose($listhandle);
?>
</body>
</html>