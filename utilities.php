<?php
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

function returnJson($arr){
    echo json_encode($arr);
    die();
}

function initializeDb(){
    
    // Palitan niyo na lang muna manual
    $servername = "localhost";
    $username = "lean1";
    $password = "lean1";
    $dbname = "omeghed";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        $arr = array(
            "success"=>false,
            "payload"=>"Connection failed: ".$conn->connect_error,
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
            "payload"=>"Invalid entity: ".$entity
        );        
    }
    // Add table name to parameters
    $tableName = $entityToTable[$entity];    
    $sql.=" ".$tableName;
    
    try{
        // Initialize DB
        $conn = initializeDb();    

        // If ID is provided, add to SQL and bind
        if(!empty($id)){
            $sql.=" WHERE ".$tableToKey[$tableName]." = ?";        
            $stmt = $conn->prepare($sql);    
            $stmt->bind_param("s",$id);
        }else{
            $stmt = $conn->prepare($sql);    
        }

        $stmt->execute();
        $ret = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array(
            "success"=> true,
            "payload"=>$ret,
        );
    } catch (Exception $ex) {
        return array(
            "success"=>false,
            "payload"=>"Error: ".str($ex),
        );
        
    }
}

function updateDict($keyword, $message){   
    // Build SQL statement
    $sql = "UPDATE dictionary SET message='".$message."' WHERE keyword = ?";
    
    try{
        // Initialize DB
        $conn = initializeDb();    
        $stmt = $conn->prepare($sql);    
        $stmt->bind_param("s",$keyword);        
        $stmt->execute();        
        
        return array(
            "success"=> true,
            "payload"=>"Update successful",
        );
    } catch (Exception $ex) {
        return array(
            "success"=>false,
            "payload"=>"Error on update: ".str($ex),
        );
        
    }
}

function addToDict($keyword, $message){   
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
            "payload"=>"Create successful",
        );
    } catch (Exception $ex) {
        return array(
            "success"=>false,
            "payload"=>"Error on update: ".str($ex),
        );
        
    }
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
    
    returnJson(getEntity($entity, $id));
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
            "payload"=>"No entity provided",
        ));
    }
    else if(!in_array($entity, array("dict","conversations"))){
        returnJson(array(
            "success"=>false,
            "payload"=>"Not allowed for entity: ".$entity
        ));
    }
    $result = getEntity($entity, $id)['payload'];
    
    // If record exists, then it's an update request
    // Update requests are only allowed for dictionary entries
    if(count($result)>0){
        
        // Check if update is for dictionary
        if(!in_array($entity, array("dict"))){
            returnJson(array(
                "success"=>false,
                "payload"=>"Not allowed for entity: ".$entity,
            ));
        }
        // Check if message is provided
        else if(!isset($_POST['message'])){
            returnJson(array(
                "success"=>false,
                "payload"=>"No provided message",
            ));
        }                
        
        $message = isset($_POST['message']) ? $_POST['message'] : NULL;
        returnJson(updateDict($id, $message));
    }
    // Else, if record does not exist, it's a create request
    // Create requests are only allowed for dictionary and conversations entities
    else{
        // Check if update is for dictionary or conversations
        if(!in_array($entity, array("dict","conv"))){
            returnJson(array(
                "success"=>false,
                "payload"=>"Not allowed for entity: ".$entity,
            ));
        }
        
        switch($entity){
            case "dict":
                if(!isset($_POST['message'])){
                    returnJson(array(
                        "success"=>false,
                        "payload"=>"No provided message",
                    ));
                }
                
                returnJson(addToDict($id, $_POST['message']));
                break;
            case "conv":
                break;
        }
    }
}