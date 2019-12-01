<?php
require_once('include.php');
use Globe\Connect\Sms;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// map entity names to tables
$entityToTable = array(
    "subscriber" => "opt_in",
    "conv" => "conversations", 
    "dict" => "dictionary"
);

// map entity names to key names
$tableToKey = array(
    "opt_in" => "subscriberNumber",
    "conversations" => "subscriberNumber",
    "dictionary" => "keyword",
);

$env = getenv("APPLICATION_ENV");

function returnJson($arr){
    echo json_encode($arr);
    die();
}

function initializeDb(){
    
    // Palitan niyo na lang muna manual
    $servername = "";
    $username = "";
    $password = "";
    $dbname = "";
    global $env;
    
    switch($env){
        case "development":
            $servername = "localhost";
            $username = "lean1";
            $password = "lean1";
            $dbname = "omeghed";
            break;
        default:
            $servername = "localhost";
            $username = "root";
            $password = "Elekid123!@-";
            $dbname = "is238";
    }

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        $arr = array(
            "success"=>false,
            "data"=>"Connection failed: ".$conn->connect_error,
        );
        returnJson($arr);
    }
    
    return $conn;
}

function getEntity($entity, $id){
    
    // Reference global variables
    global $entityToTable;
    global $tableToKey;
    
    // Build SQL statement
    $sql = "SELECT * FROM ";    
   
    // check if entity has corresponding table. Else, return error.
    if(empty($entity) || !isset($entityToTable[$entity])){
        return array(
            "success"=>false,
            "data"=>"Invalid entity: ".$entity
        );        
    }
    // Add table name to parameters
    $tableName = $entityToTable[$entity];    
    $sql.=" ".$tableName;
    
    try{
        // Initialize DB
        $conn = initializeDb();    
        $sortsql = "";
        switch($tableName){
            case "opt_in":
                $sortsql.=" ORDER BY latestIncoming DESC";
                break;
            case "conversations":
                $sortsql.=" ORDER BY dateTime ASC";
                break;
        }

        // If ID is provided, add to SQL and bind
        if(!empty($id)){
            $sql.=" WHERE ".$tableToKey[$tableName]." = ? ".$sortsql;        
            $stmt = $conn->prepare($sql);    
            $stmt->bind_param("s",$id);
        }else{            
            $sql.=$sortsql;
            $stmt = $conn->prepare($sql);    
        }

        $stmt->execute();
        $ret = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return array(
            "success"=> true,
            "data"=>$ret,
        );
    } catch (Exception $ex) {
        return array(
            "success"=>false,
            "data"=>"Error: ".str($ex),
        );
    }
}

function updateDict($keyword, $message, $newKey){   
    
    $keyword = trim($keyword);
    $newKey = trim($newKey);
    // Build SQL statement
    // Default: keyword stays the same    
    if($keyword==$newKey){
        $sql = "UPDATE dictionary SET message='".$message."' WHERE keyword = ?";
    }
    // Else, update keyword as well
    else{
        $sql = "UPDATE dictionary SET keyword='".$newKey."', message='".$message."' WHERE keyword = ?";
    }
    
    try{
        // Initialize DB
        $conn = initializeDb();    
        $stmt = $conn->prepare($sql);    
        $stmt->bind_param("s",$keyword);        
        $stmt->execute();        
        
        return array(
            "success"=> true,
            "data"=>"Update successful",
        );
    } catch (Exception $ex) {
        return array(
            "success"=>false,
            "data"=>"Error on update: ".str($ex),
        );
        
    }
}

function addToDict($keyword, $message){   
    
    $keyword = trim($keyword);
    
    // Build SQL statement
    $sql = "INSERT INTO dictionary VALUES (?,?)";
    
    try{
        // Initialize DB
        $conn = initializeDb();    
        $stmt = $conn->prepare($sql);    
        $stmt->bind_param("ss",$keyword, $message);
        $stmt->execute();        
        
        return array(
            "success"=> true,
            "data"=>"Create successful",
        );
    } catch (Exception $ex) {
        return array(
            "success"=>false,
            "data"=>"Error on update: ".str($ex),
        );
        
    }
}

function addToConv($subscriberNumber, $message){   
    // Build SQL statement
    $sql = "INSERT INTO conversations "
            . "(subscriberNumber,destinationAddress, message, senderAddress, isMO) "
            . "VALUES (?,?,?,'tel:21580567',0)";
    
    $sql_update = "UPDATE opt_in SET latestIncoming=now() WHERE subscriberNumber=?";

    try{
        // Initialize DB
        $conn = initializeDb();    
        $stmt = $conn->prepare($sql);    
        $stmt->bind_param("sss",$subscriberNumber,$subscriberNumber,$message);
        $stmt->execute();        
        
        $stmt2 = $conn->prepare(($sql_update));
        $stmt2->bind_param("s",$subscriberNumber);
        $stmt2->execute();
        
        return array(
            "success"=> true,
            "data"=>"Create successful",
        );
    } catch (Exception $ex) {
        return array(
            "success"=>false,
            "data"=>"Error on update: ".str($ex),
        );
        
    }
}

function sendToMobile($subscriberNumber, $message){
    
    $sql = "SELECT accessToken FROM opt_in WHERE subscriberNumber=?";    
    
    $conn = initializeDb();    
    $stmt = $conn->prepare($sql);    
    $stmt->bind_param("s",$subscriberNumber);
    $stmt->execute();
    $ret = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $accesstoken = $ret['0']['accessToken'];
    $sms = new Sms('0567', $accesstoken);
    $sms->setReceiverAddress($subscriberNumber);
    $sms->setMessage($message);
    $sms->setClientCorrelator('12345');
    $sms->sendMessage();

}

/* 
 * RETRIEVE RECORDS
 * 
 * /utilities.php?entity=subscriber
 * /utilities.php?entity=subscriber&id=<subscriber number>
 * 
 * /utilities.php?entity=conv
 * /utilities.php?entity=conv&id=<subscriber number>
 * 
 * /utilities.php?entity=dict
 * /utilities.php?entity=dict&id=<keyword>
 * 
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $entity = isset($_GET['entity']) ? $_GET['entity'] : NULL;
    $id = isset($_GET['id']) ? $_GET['id'] : NULL;
    
    $arr = getEntity($entity, $id);
    if($entity=="subscriber"){        
        foreach($arr['data'] as $x=>$sub){
            $latestMsg = getEntity("conv", $sub['subscriberNumber'])['data'];
            $latestMsg = array_pop($latestMsg)['message'];
            $arr['data'][$x]['latestMsg'] = $latestMsg;
        }
        
    }
    
    returnJson($arr);
}

/*
 * ADD RECORDS
 * 
 * /utilities.php
 *      entity      dict
 *      id          <keyword>
 *      message     <message>
 * 
 */
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity = isset($_POST['entity']) ? $_POST['entity'] : NULL;
    $id = isset($_POST['id']) ? $_POST['id'] : NULL;
   
    if(empty($entity)){
        returnJson(array(
            "success"=>false,
            "data"=>"No entity provided",
        ));
    }
    else if(!in_array($entity, array("dict","conv"))){
        returnJson(array(
            "success"=>false,
            "data"=>"Not allowed for entity: ".$entity
        ));
    }
    $result = getEntity($entity, $id)['data'];
    
    // If record exists, then it's an update request
    // Update requests are only allowed for dictionary entries
    if(count($result)>0 && $entity!="conv"){
        
        // Check if update is for dictionary
        if(!in_array($entity, array("dict"))){
            returnJson(array(
                "success"=>false,
                "data"=>"Not allowed for entity: ".$entity,
            ));
        }
        // Check if message is provided
        else if(!isset($_POST['message'])){
            returnJson(array(
                "success"=>false,
                "data"=>"No provided message",
            ));
        }                
        
        $message = isset($_POST['message']) ? $_POST['message'] : NULL;
        $newKey = isset($_POST['newKey']) ? $_POST['newKey'] : NULL;
        returnJson(updateDict($id, $message, $newKey));
    }
    // Else, if record does not exist, it's a create request
    // Create requests are only allowed for dictionary and conversations entities
    else{
        // Check if update is for dictionary or conversations
        if(!in_array($entity, array("dict","conv"))){
            returnJson(array(
                "success"=>false,
                "data"=>"Not allowed for entity: ".$entity,
            ));
        }
        
        switch($entity){
            case "dict":
                if(!isset($_POST['message'])){
                    returnJson(array(
                        "success"=>false,
                        "data"=>"No provided message",
                    ));
                }
                
                returnJson(addToDict($id, $_POST['message']));
                break;
            case "conv":
                try{
                    sendToMobile($id, $_POST['message']);
                    returnJson(addToConv($id, $_POST['message']));
                } catch (Exception $ex) {
                    return array(
                        "success"=>false,
                        "data"=>"Error: ".str($ex),
                    );
                }
                break;
        }
    }
}

// Delete dictionary entry
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $entity = isset($_GET['entity']) ? $_GET['entity'] : NULL;
    $id = isset($_GET['id']) ? $_GET['id'] : NULL;
    
    if(empty($entity) || empty($id)){
        returnJson(array(
            "success"=>false,
            "data"=>"No entity or ID provided",
        ));
    }
    else if(!in_array($entity, array("dict"))){
        returnJson(array(
            "success"=>false,
            "data"=>"Not allowed for entity: ".$entity
        ));
    }
    
     $sql = "DELETE FROM dictionary WHERE keyword=?";
    
    try{
        // Initialize DB
        $conn = initializeDb();    
        $stmt = $conn->prepare($sql);    
        $stmt->bind_param("s",$id);
        $stmt->execute();        
        
        returnJson(array(
            "success"=> true,
            "data"=>"Delete successful",
        ));
    } catch (Exception $ex) {
        returnJson(array(
            "success"=>false,
            "data"=>"Error on update: ".str($ex),
        ));
        
    }
}