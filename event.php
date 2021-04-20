<?php
//https://domain.com/event.php?s={advertiser id}



//get the advertiser id
if(!empty($_GET['s'])){
  $s = $_GET['s'];
}else{
  $s = 'none';
}


//postback the conversion event to facebook
$appId = '2820616208187342';
$appSecret = '545ed44d69949e8294d36009211e7acf';
$appAccessToken = getAppAccessToken($appId, $appSecret);
$mobileAdvId = $s;
postEvent($appId, $appAccessToken, $mobileAdvId);



//get app access token from fb
function getAppAccessToken($appId, $appSecret){
  $url = "https://graph.facebook.com/oauth/access_token?client_id={$appId}&client_secret={$appSecret}&grant_type=client_credentials";
  $ch = curl_init($url); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $resultArr = json_decode($result, true);
  //exit("url: {$httpCode} {$url}<br>result: {$result}<br>");
  if ($resultArr['error']){
    //exit("getAppAccessToken error: " . print_r($result, true));
	file_put_contents($logFile, "{$click_date}, {$url}, getAppAccessToken error: {$result}\r\n\r\n", FILE_APPEND);
	exit();
  }else{
    return $resultArr['access_token'];
  }
}


//post conversion event to fb
function postEvent($appId, $appAccessToken, $mobileAdvId){
  $url = "https://graph.facebook.com/{$appId}/activities";
  $postArr = array('event' => 'CUSTOM_APP_EVENTS', 'advertiser_id' => $mobileAdvId, 'advertiser_tracking_enabled' => '1', 'application_tracking_enabled' => '1', 'custom_events' => '[{"_eventName":"fb_mobile_purchase","_valueToSum":10,"fb_currency":"USD",}]', $appAccessToken);
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postArr);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $resultArr = json_decode($result, true);
  //exit("url: {$httpCode} {$url}<br>postArr: <pre>" . print_r($postArr, true) . "</pre><br>resultArr: <pre>" . print_r($resultArr, true) . "</pre><br>");
  if ($resultArr['error']){
    //exit("postEvent error: " . print_r($result, true));
	file_put_contents('postback.txt', "{$click_date}, {$url}, postEvent error: {$result}\r\n\r\n", FILE_APPEND);
	exit();
  }else{
    //file_put_contents('postback.txt', "{$click_date}, {$url}, postEvent success: " . print_r($postArr, true) . " {$result}\r\n\r\n", FILE_APPEND);
	return $resultArr['success'] . ' success';
  }
}

?>