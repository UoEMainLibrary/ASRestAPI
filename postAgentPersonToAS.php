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

class Agent {
    public $title = "";
    //one of "agent_person", "agent_corporate_entity", "agent_software", "agent_family", "user"
    public $agent_type = "";
    public $agent_contacts = "";
    public $linked_agent_roles = "";
    public $external_documents = "";
    public $notes = "";

    public $type = "";
    public $vocabulary = "";
    public $external_ids = "";
    public $source = "";
    public $authority_id = "";
    public $created_by = "";
    //'created_on' => ,
    public $last_modified_by = "";
    public $user_mtime = "";
    public $names= "";
    public $related_agents= "";

}

class AgentContacts{
    public $name = "";
    public $jsonmodel_type = "agent_contact";
}


class Name {
    public $type = "";
    public $primary_name = ""; //surname
    public $rest_of_name = ""; //forenames
    public $prefix = ""; //Mrs, Mr etc
    public $title = ""; //Sir etc
    public $authority_id = "";
    public $dates = "";
    public $source = "";
    public $sort_name_auto_generate = "";
    public $name_order = "inverted";
}


class Note {
    public $label = "";
    public $jsonmodel_type = "note_bioghist";
    public $subnotes = "";
}

class SubNote {
    public $content = "";
    public $jsonmodel_type = "note_text";
    public $publish = false;
}

//not sure if needed
$username = 'admin';
$password = 'admin';
$filename = "/Users/cknowles/Desktop/CRCSubjectCSV/cms_auth_pers.csv";

//start session
//start_session();
$session_id = loginToAS($username, $password);
//$_SESSION['TOKEN']=$session_id;

print_r($session_id);
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
        if(count($line_as_arr) == 19)
        {

            //ignore lines set to delete/suppress
            if ($line_as_arr[18] != 'y')
            {

                createAgent($line_as_arr, $session_id);
            }
        }
        else{
            echo("Error processing line =".$line);
        }

    }
    fclose($file_handle);

}

function createAgent($line_as_arr, $session_id)
{
    //0   1         2       3           4           5    6                 7            8           9       10        11
    //id  persterm	normal	family_name	given_name	date terms_of_address  description	variant_of	use_for	source	lang_code

    //12    13          14          15          16          17              18
    //notes	created_for	created_by	created_on	last_edited	last_edited_by	suppress
    $name = new Name();
    $name->authority_id = "per_".$line_as_arr[0];
    $name->dates = $line_as_arr[5];
    $name->qualifier = $line_as_arr[7];
    if (!empty($line_as_arr[3]))
    {
        $name->primary_name = $line_as_arr[3];
        $name->rest_of_name = $line_as_arr[4];
    }
    else{
        $name->primary_name = $line_as_arr[4];
    }
    $name->title = $line_as_arr[6];

    //rules are required when source is blank
    $name->source= "local";

    $name->sort_name_auto_generate = TRUE;

    $name->type= $line_as_arr[3];

    $notes = array();

    if (!empty($line_as_arr[8]))
    {
        $note = new Note();
        $note->label = "Variant Of";

        $subnote = new SubNote();
        $subnote->content = $line_as_arr[8];

        $note->subnotes = array($subnote);
        $notes[] = $note;
    }

    if (!empty($line_as_arr[9]))
    {
        $note = new Note();
        $note->label = "Use For";

        $subnote = new SubNote();
        $subnote->content = $line_as_arr[9];

        $note->subnotes = array($subnote);
        $notes[] = $note;
    }

    if (!empty($line_as_arr[10]))
    {
        $note = new Note();
        $note->label = "Source";

        $subnote = new SubNote();
        $subnote->content = $line_as_arr[10];

        $note->subnotes = array($subnote);
        $notes[] = $note;
    }

    if (!empty($line_as_arr[12]))
    {
        $note = new Note();
        $note->label = "Notes";

        $subnote = new SubNote();
        $subnote->content = $line_as_arr[12];

        $note->subnotes = array($subnote);
        $notes[] = $note;
    }

    if (!empty($line_as_arr[13]))
    {
        $note = new Note();
        $note->label = "Created For";

        $subnote = new SubNote();
        $subnote->content = $line_as_arr[13];

        $note->subnotes = array($subnote);
        $notes[] = $note;
    }

    $data = new Agent();
    $data->agent_type = "agent_person";

    if (count($notes) > 0)
    {
        $data->notes = $notes;
    }

    //link to terms 1-2-1 for these
    $data->names = array($name);
    //echo '=======********==========';
    //echo json_encode($data);
    //echo '====================';


    // is this needed?
    //$agent_contact = new AgentContacts();
    //$agent_contact->name= $line_as_arr[3];

    //$data->agent_contacts = array($agent_contact);

    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'X-ArchivesSpace-Session: '.$session_id
    );

    $service_url = 'http://localhost:8089/agents/people';
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



