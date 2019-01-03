<?php
// Set this $loggingEnabled to true if you want to enable logging; else set to false
$loggingEnabled = true;
// Set this $logFile to your target logfile; ensure right permissions
$logFile = "/var/log/vzloggermw/vzloggermw.log";
// Set this $openhabUrl to match your openhab host
$openhabUrl = "openhab.example.com";
// Set this $openhabPort to match your openhab API port
$openhabPort = 8080;

try
{
	$rawData = file_get_contents("php://input");
	$jsArray = json_decode($rawData);
	$reqestUriArray = explode("/", $_SERVER["REQUEST_URI"]);
	$itemName = $reqestUriArray[1];
	
	$data = $jsArray[0][1];
	
	$ch = curl_init(); 
	$curlUrl = sprintf("http://%s:%s/rest/items/%s/state", $openhabUrl, $openhabPort, $itemName);
	curl_setopt($ch, CURLOPT_URL, $curlUrl);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', 'Accept: application/json', 'Content-Length: ' . strlen($data)));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$responseCode = null;
	$response  = curl_exec($ch);
	$curlInfo = null;
	if(!curl_errno($ch))
	{
    	$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	}
	else
	{
		$response = curl_errno($ch);
	}
	curl_close($ch);
	if ($loggingEnabled)
	{
		$fp = fopen($logFile, 'a');
		fwrite($fp, sprintf("%s %s %s %s %s %s\n", date(DATE_RFC3339_EXTENDED), $itemName, $data, $curlUrl, $responseCode, $response));
		fclose($fp);
	}
}
catch (Exception $ex)
{
	print($ex->getMessage());
}
finally
{
	print("success");
}
?>
