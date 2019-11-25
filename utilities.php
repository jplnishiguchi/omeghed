<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

/* KEVIN DITO KA TUMINGIN ANDITO YUNG MGA PARAMETER
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
    
    // Build SQL statement
    $sql = "SELECT * FROM ";    
    $params = array(&$types);
   
    // check if entity has corresponding table. Else, return error.
    if(empty($entity) || !isset($entityToTable[$entity])){
        $arr = array(
            "success"=>false,
            "payload"=>"Invalid entity: ".$entity
        );
        returnJson($arr);
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

        returnJson(array(
            "success"=> true,
            "payload"=>$ret,
        ));
    } catch (Exception $ex) {
        $arr = array(
            "success"=>false,
            "payload"=>"Error: ".str($ex),
        );
        returnJson($arr);
    }
    
}