<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cknowles - University of Edinburgh
 * Date: 08/01/2014
 * Time: 14:22
 */


//$url = "http://localhost:8089/";
//$response = file_get_contents($url);
//echo $response;

//Example using CURL for Get

class Data {
    public $title = "";
    public $vocabulary = "";
    public $external_ids = "";
    public $source = "";
    public $authority_id = "";
    public $created_by = "";
    //'created_on' => ,
    public $last_modified_by = "";
    public $user_mtime = "";
    public $terms= "";
}

class Term {
    public $term_type = "";
    public $term = "";
    public $vocabulary = "";


}

//not sure if needed
$username = 'xxxx';
$password = 'xxxx';
$filename = "/Users/cknowles/Documents/CRCSubjectCSV/updated/cms_auth_genr.csv";

//start session
//start_session();
$session_id = loginToAS($username, $password);
//$_SESSION['TOKEN']=$session_id;

print_r($session_id);
echo '***************';
readInCSV($filename, $session_id);


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

function readInCSV($csvFile, $session_id)
{
    if(!file_exists($csvFile) || !is_readable($csvFile))
        return FALSE;

    $file_handle = fopen($csvFile, 'r');
    $delimiter = ',';
    $enclosure = '"';
    $escape = '\\';

    while ($line = fgets($file_handle)) {
        $line_as_arr = str_getcsv( $line , $delimiter , $enclosure, $escape);
        echo (count($line_as_arr));
        if(count($line_as_arr) == 13)
        {
            //ignore lines set to delete/suppress
            if ($line_as_arr[12] != 'y')
            {
                //echo($line_as_arr[12]);
                createSubject($line_as_arr, $session_id);
            }
        }
        else{
            echo("Error processing line =".$line);
        }

    }
    fclose($file_handle);

}

function createSubject($line_as_arr, $session_id)
{

    echo 'create subject';
    //example from CRC database
    //0   1     2         3        4       5        6        7             8           9            10            11               12
    //id,"term","use_for","source","other","ext_id","notes","created_for","created_by","created_on","last_edited","last_edited_by","suppress"


    $term = new Term();
    $term->term = $line_as_arr[1];
    $term->term_type = "genre_form";
    $term->vocabulary = "/vocabularies/1";

    $data = new Data();
    $data->title = $line_as_arr[1];
    $data->vocabulary  = "/vocabularies/1";
    $data->external_ids =array();
    $data->source = $line_as_arr[3];
    $data->authority_id = "gen_".$line_as_arr[0];

    //add notes
    $notes = "";

    if (!empty($line_as_arr[5])){
        $notes = $notes . "External Id = " . $line_as_arr[5] . ",";
    }
    if (!empty($line_as_arr[6])) {
        $notes = $notes . "Notes = " . $line_as_arr[6]. ",";
    }
    if (!empty($line_as_arr[7])) {
        $notes = $notes . "Created For = " . $line_as_arr[7]. ",";
    }
    if (!empty($line_as_arr[2])) {
        $notes = $notes . "Use For = " . $line_as_arr[2]. ",";
    }
    if (strlen($notes) > 0){
        $data->scope_note = trim($notes, ",");
    }

    $data->terms = array($term);

    echo json_encode($data);

    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'X-ArchivesSpace-Session: '.$session_id
    );

    $service_url = 'http://localhost:8089/subjects';
    $curl = curl_init($service_url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_URL, $service_url);

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
    echo print_r($curl_response);
    //echo '====================';
    //echo print_r($decoded);

}



