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
    public $scope_note;
}

class Term {
    public $term_type = "";
    public $term = "";
    public $vocabulary = "";
}


//not sure if needed
$username = 'admin';
$password = 'admin';
$filename = "/Users/cknowles/Documents/CRCSubjectCSV/updated/cms_auth_geog.csv";

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
    echo "LOGIN: response ok!\n";
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
        echo(count($line_as_arr));
        if(count($line_as_arr) == 24)
        {
            if ($line_as_arr[23] != 'y')
            {
                echo("create subject");
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
    //ignore lines set to delete/suppress
    //example from CRC database - column headings below

    //0   1     2         3   4         5       6      7      8         9           10       11
    //id  term	island	city  territory	county	state country continent	part_order	alt_form alt_form_lang

    //12      13       14     15    16      17    18          19         20          21         22              23
    //use_for locator source other	ext_id	notes created_for created_by created_on	last_edited	last_edited_by	suppress

    //if [1] is not empty then spilt on brackets and commas, add last entry first as a term?
    $terms = array();
    $data = new Data();

    echo($line_as_arr);
    //do this for all items 1 to 8, reverse order so country comes first
    for ($x=8; $x>=1; $x--)
    {
        $entry = $line_as_arr[$x];
        if (!empty($entry))
        {
            if (!strpbrk($entry, ",("))
            {

                $term = new Term();
                $term->term = trim($entry);
                $term->term_type = "geographic";
                $term->vocabulary = "/vocabularies/1";
                $terms[] = $term;
            }
            else{
                $places = preg_split("/[(,]+/", $entry, -1, PREG_SPLIT_NO_EMPTY);
                //echo $places;
                //loop through places, flipped order so country comes first
                foreach (array_reverse($places) as $place)
                {
                    echo "place " . $place . "\n";
                    //ToDo strip ) and , from strings
                    $term = new Term();
                    $term->term = trim($place, ") ");
                    $term->term_type = "geographic";
                    $term->vocabulary = "/vocabularies/1";
                    $terms[] = $term;
                }
            }
        }
    }

    //add notes
    $notes = "";

    if (!empty($line_as_arr[10])){
        $notes = $notes . "Alt form = " . $line_as_arr[10] . ",";
    }
    if (!empty($line_as_arr[11])) {
        $notes = $notes . "Alt form lang = " . $line_as_arr[11]. ",";
    }
    if (!empty($line_as_arr[12])) {
        $notes = $notes . "Use For = " . $line_as_arr[12]. ",";
    }
    if (!empty($line_as_arr[13])){
        $notes = $notes . "Locator = " . $line_as_arr[13]. ",";
    }
    if (!empty($line_as_arr[15])) {
        $notes = $notes . "Other = " . $line_as_arr[15]. ",";
    }
    if (!empty($line_as_arr[17])) {
        $notes = $notes . "Notes = " . $line_as_arr[17]. ",";
    }
    if (!empty($line_as_arr[16])){
        $notes = $notes . "External Id = " . $line_as_arr[16] . ",";
    }
    if (!empty($line_as_arr[18])) {
        $notes = $notes . "Created For = " . $line_as_arr[18]. ",";
    }

    if (strlen($notes) > 0){
        $data->scope_note = trim($notes, ",");
    }

    //$data->title = $line_as_arr[1];
    $data->vocabulary  = "/vocabularies/1";
    $data->external_ids =array();
    $data->source = $line_as_arr[14];
    $data->authority_id = "geo_".$line_as_arr[0];

    //Change to an array of terms
    $data->terms = $terms;

    echo json_encode($data). "\n";

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



