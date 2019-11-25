<?php

require('db_connect.php');
  
$getfile='redirect_get.txt';
$postfile='redirect_post.txt';
$errorlog = 'error_log.txt';

$date = new DateTime();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // opt-in
        $access_token=$_GET['access_token'];
        $subscriber_number=$_GET['subscriber_number'];

	$sql = "INSERT INTO opt_in (subscriberNumber, accessToken) VALUES ('$subscriber_number', '$access_token')";
	
	if (!mysqli_query($connection,$sql)){
		//echo "Error description:".mysqli_error($connection);
		file_put_contents($errorlog, mysqli_error($connection) . PHP_EOL, FILE_APPEND);
	}
	
        //file_put_contents($getfile, $date->getTimestamp() . PHP_EOL, FILE_APPEND);
        //file_put_contents($getfile, $access_token . PHP_EOL, FILE_APPEND);
        //file_put_contents($getfile, $subscriber_number . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // receive SMS
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        //file_put_contents($postfile, $date->getTimestamp() . PHP_EOL, FILE_APPEND);
        //file_put_contents($postfile, $json . PHP_EOL, FILE_APPEND);

        $subscriber_number = $data['unsubscribed']['subscriber_number'];
        $access_token = $data['unsubscribed']['access_token'];
        $shortcode = $data['unsubscribed']['shortcode'];
        $timestamp = $data['unsubscribed']['timestamp'];
	
	$sql = "DELETE FROM opt_in WHERE subscriberNumber = '$subscriber_number'";
	if (!mysqli_query($connection,$sql)){
		//echo "Error description:".mysqli_error($connection);
		file_put_contents($errorlog, mysqli_error($connection) . PHP_EOL, FILE_APPEND);
	}

        //file_put_contents($postfile, $subscriber_number . PHP_EOL, FILE_APPEND);
        //file_put_contents($postfile, $access_token . PHP_EOL, FILE_APPEND);
        //file_put_contents($postfile, $shortcode . PHP_EOL, FILE_APPEND);
        //file_put_contents($postfile, $timestamp . PHP_EOL, FILE_APPEND);
}

?>
