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

//next example will receive all messages for specific conversation
//$service_url = 'http://example.com/api/conversations/[CONV_CODE]/messages&apikey=[API_KEY]';

//not sure if needed
$username = 'xxxx';
$password = 'xxxx';

//start session
//start_session();
$session_id = loginToAS($username, $password);
//$_SESSION['TOKEN']=$session_id;

print_r($session_id);
getClass($session_id);

class Data {
    public $publish = "1";
}


function loginToAS($username, $password)
{

    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json'
    );

    $service_url = 'http://localhost:8089/users/admin/login?password=admin';
    $curl = curl_init($service_url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_URL, $service_url);
    curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);


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
    echo 'LOGIN: response ok!';
    //echo print_r($curl_response);
    //echo '====================';
    //echo print_r($decoded);

    //get session out of response to use in every call
    $session_id = $decoded->session;

    //echo print_r($session_id);
    return $session_id;
}

function getClass($session_id)
{
    $repo_id = 2;
    $class_type = 'resources';

    $headers = array(
        //'Accept: application/json',
        //'Content-Type: application/json',
        'X-ArchivesSpace-Session: '.$session_id
    );

    $service_url = 'http://localhost:8089/repositories/' .$repo_id. '/' .$class_type. '?all_ids=true';
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($curl, CURLOPT_URL, $service_url);
    $curl_response = curl_exec($curl);

    if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('error occurred during curl exec get class. Additional info: ' . var_export($info));
    }
    curl_close($curl);
    $decoded = json_decode($curl_response);
    echo $curl_response;
    foreach($decoded as $value) {
        echo $value . ' ';
        publish($session_id, $repo_id, $class_type, $value);
    }
    //publish($session_id, $repo_id, $class_type, 419);
    if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
        die('error occurred get list: ' . $decoded->response->errormessage);
    }
    else
    {
        echo 'response ok!';
    }

    //print_r($decoded);
}

function publish($session_id, $repo_id , $class_type, $class_id)
{
    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'X-ArchivesSpace-Session: '.$session_id
    );

    //todo don't update if note type is acqinfo or processinfo
    //todo  does not publish all sub parts :(

    $service_urlupdate = 'http://localhost:8089/repositories/' .$repo_id. '/' .$class_type. '/'.$class_id .'/publish';
    $curl = curl_init($service_urlupdate);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_URL, $service_urlupdate);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $curl_response = curl_exec($curl);
    if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('error occurred during curl exec update. Additional info: ' . var_export($info));
    }
    curl_close($curl);
    $decoded = json_decode($curl_response, true);
    if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
        die('error occurred in update :' . $decoded->response->errormessage);
    }
    else{
        echo 'response ok!';
    }

    echo print_r($curl_response);
    //echo '====================';
    //echo print_r($decoded);
}
