<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cknowles
 * Date: 08/01/2014
 * Time: 14:22
 * To change this template use File | Settings | File Templates.
 */


//$url = "http://localhost:8089/";
//$response = file_get_contents($url);
//echo $response;

//Example using CURL for Get

//next example will recieve all messages for specific conversation
//$service_url = 'http://example.com/api/conversations/[CONV_CODE]/messages&apikey=[API_KEY]';
$service_url = 'http://localhost:8089/terms?q=t';
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$curl_response = curl_exec($curl);
if ($curl_response === false) {
    $info = curl_getinfo($curl);
    curl_close($curl);
    die('error occurred during curl exec. Additional info: ' . var_export($info));
}
curl_close($curl);
$decoded = json_decode($curl_response);
if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
    die('error occurred: ' . $decoded->response->errormessage);
}
echo 'response ok!';
print_r($decoded);