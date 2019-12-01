<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */

$env = getenv("APPLICATION_ENV");
switch($env){
    case "development":
        define('DB_SERVER', 'localhost');
        define('DB_USERNAME', 'lean1');
        define('DB_PASSWORD', 'lean1');
        define('DB_NAME', 'omeghed');
        break;
    default:
        define('DB_SERVER', 'localhost');
        define('DB_USERNAME', 'root');
        define('DB_PASSWORD', 'Elekid123!@-');
        define('DB_NAME', 'is238');

}

 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>