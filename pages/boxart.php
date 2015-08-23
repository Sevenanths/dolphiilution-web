<?php
header("Cache-Control: private, max-age=10800, pre-check=10800");
header("Pragma: private");
header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
header("Content-Type: image/png");

if (isset($_GET['id']))
{
	$id = $_GET['id'];

	$region = $id[3];
	
	$prefix = "EN";
	switch ($region)
	{
		case "P":
			$prefix = "EN";
		break;
		case "E":
			$prefix = "US";
		break;
		case "J":
			$prefix = "JA";
		break;
		case "K":
			$prefix = "KO";
		break;
	}

	$handle = curl_init("http://art.gametdb.com/wii/coverfullHQ/$prefix/$id.png");
	curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
	
	/* Get the HTML or whatever is linked in $url. */
	$response = curl_exec($handle);
	
	/* Check for 404 (file not found). */
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	if($httpCode == 404)
	{
	    
	}
	else
	{
		echo $response;
	}
	
	curl_close($handle);
}
?>