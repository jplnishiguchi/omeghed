<?php
require_once(__DIR__ . '/include.php');

use Globe\Connect\Sms;

//$sms = new Sms('0123', 'MQWvyMejeJG9w-rsAUPlzs7XvGIKLnarjFpLr5NCY5k');
$sms = new Sms('0567', 'vRAPHZPExy7EUSbmzSJL-y4PJwRYfjHSC9A8uX0pHgc');


// send message
//$sms->setReceiverAddress('9173186699');
//$sms->setMessage('test2');
//$sms->setClientCorrelator('12345');
//echo $sms->sendMessage();

$sms->setReceiverAddress('9273289628');
$sms->setMessage('ako ang iyong konsyensya awoooo');
$sms->setClientCorrelator('12345');
echo $sms->sendMessage();


// send binary message
//$sms->setReceiverAddress('[address]');
//$sms->setUserDataHeader('[header]');
//$sms->setDataEncodingScheme('[scheme]');
//$sms->setMessage('[message]');
//echo $sms->sendBinaryMessage();

?>