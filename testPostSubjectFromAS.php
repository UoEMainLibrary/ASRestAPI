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
$username = 'admin';
$password = 'admin';

//start session
//start_session();
$session_id = loginToAS($username, $password);
//$_SESSION['TOKEN']=$session_id;

print_r($session_id);
echo '***************';
createSubject($session_id);

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
// POST DATA USING SESSION ID

function createRepository($session_id)
{
    $data = array(
        'repo_code' => 'testnow1',
        'name' => 'Test Now 1'
    );

    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'X-ArchivesSpace-Session: '.$session_id
    );

    $service_url = 'http://localhost:8089/repositories';
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


function createSubject($session_id)
{
    //example from CRC database
    //id	term	use_for	source	other	ext_id	notes	created_for	created_by	created_on	last_edited	last_edited_by	suppress
    //1	Landscape		lcsh				NAHSTE	Rachael Third	0000-00-00 00:00:00	2009-08-19 13:30:52	Grant Buttars	NULL


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
    $term = new Term();
    $term->term = "Oranges";
    $term->term_type = "topical";
    $term->vocabulary = "/vocabularies/1";

    $data = new Data();
    $data->title = "Oranges";
    $data->vocabulary  = "/vocabularies/1";
    $data->external_ids =array();
    $data->source = "lcsh";
    $data->authority_id = "lcsh";
    $data->created_by = "Claire Knowles";
    $data->last_modified_by = "John Smith";
    $data->user_mtime = "2009-08-19 13:30:52";
    $data->terms = array($term);

    //echo json_encode($data);

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



