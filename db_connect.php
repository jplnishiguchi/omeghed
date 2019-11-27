<?php

//$errorlog = 'error_log.txt';
	
$connection = mysqli_connect('localhost','root','Elekid123!@-');
if (!$connection){
	//file_put_contents($errorlog, mysqli_error($connection) . PHP_EOL, FILE_APPEND);
	die("Database Connection Failed".mysqli_error($connection));
}

$select_db = mysqli_select_db($connection, 'is238');
if (!$select_db){
	//file_put_contents($errorlog, mysqli_error($connection) . PHP_EOL, FILE_APPEND);
	die("Database Selection Failed".mysqli_error($select_db));
}

/*$dbname = 'is238';
$dbuser = 'root';
$dbpass = 'Elekid123!@-';
$dbhost = 'localhost';

$link = mysqli_connect($dbhost,$dbuser,$dbpass) or die("Unable to Connect to '$dbhost'");
mysqli_select_db($link, $dbname) or die("Could not open the db '$dbname'");

$test_query = "SHOW TABLES FROM $dbname";
$result = mysqli_query($link, $test_query);

$tblCnt = 0;
while($tbl = mysqli_fetch_array($result)) {
  $tblCnt++;
  #echo $tbl[0]."<br />\n";
}

if (!$tblCnt) {
  echo "There are no tables<br />\n";
} else {
  echo "There are $tblCnt tables<br />\n";
} */

?>
