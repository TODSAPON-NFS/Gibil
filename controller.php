<?php
include "php_serial.class.php";
include "dbcontrol.php";
include "controlclasses.php";
define ("ACK", chr(6));
define ("NAK", chr(21));
define ("SHORTMESSAGELEN", 2);
define ("LONGMESSAGELEN", 22);
define ("POLLINGTIME", 5);
define ("UPDATETIME", 15);
define ("FIRMWARETIME", 15);
define ("EMAILTIME", 15);



//-----------------------------------------------------------------------//
/*			Start Execution Here				 */
//-----------------------------------------------------------------------//

$db = NULL;
$eventQueue = new SplQueue();
$messageQueue = new SplQueue();

// Let's start the class
$serial = new phpSerial;

// First we must specify the device. This works on both linux and windows (if
// your linux serial device is /dev/ttyS0 for COM1, etc)
$serial->deviceSet("/dev/ttyS0");

//set cts/rts
$serial->confFlowControl("rts/cts");

// We can change the baud rate
$serial->confBaudRate(9600);


// Then we need to open it
$serial->deviceOpen();
sleep(1);


//GLOBAL CONTROL VARIABLES
//True if Accounts should be polled
$messageTimer = [
	"poll" => time() - POLLINGTIME,
	"update" => time() - UPDATETIME, 
	"firmware" => time() - FIRMWARETIME,
	"email" => time() - EMAILTIME,
];

//True if working with the real AEU
$PRODUCTION = true;


//used to generate polling requests
$currentGroup = 1;
$currentAccount = 1;

$pollingRange = 5;
$accounts = 100;
$baseAccount = 4000;
$groups = 4;
$groupsGap = 1000;

/*

//mail functionality		
echo "[SENDING EMAIL]\n";
$msg = "Hello Howard,
This is an email test from the web server

cheers,
-Stewart\n";
$headers = "From: ubcfirealarmwebserver@test.com" . "\r\n" ."CC: somebodyelse@example.com";
$msg = wordwrap($msg,70);
$sent = mail("stewbertgrant@gmail.com","web server email test",$msg,$headers);
if ($sent === false){
	echo "[Email not sent]\n";
}
*/

while(true){
	if(messageHandle()){
		//do if a message was received
	} else {
		//non message received tasks
		insertEvents();
		genMessages();
		sendMessage();
	}
	
	
}


// If you want to change the configuration, the device must be closed
$serial->deviceClose();


	
/* genMessages checks the message timer to see if any periodic messages such as polling, or update are due to be sent.
if so a message object is generated, and added to the message queue for sending to the aeu*/
function genMessages(){
	global $serial;
	global $messageTimer;
	global $messageQueue;
	//Poll only if no polling is occuring and no messages were received
	$time = time();

	if ($time - $messageTimer["poll"] > POLLINGTIME){
		$message = getNextPollingRange();
		$messageQueue->enqueue($message);
		$messageTimer["poll"] = $time + POLLINGTIME;
	} else if ($time - $messageTimer["update"] > UPDATETIME){
		$message = new Message("u1\r","[Sending Update ON]\n");
		$messageQueue->enqueue($message);
		$messageTimer["update"] = $time;
	} else if ($time - $messageTimer["firmware"] > FIRMWARETIME){
		$message = new Message("f\r","[Sending firmware info request]\n");
		$messageQueue->enqueue($message);
		$messageTimer["firmware"] = $time;
	} else if ($time - $messageTimer["email"] > EMAILTIME){
		$messageTimer["email"] = $time;
	}

}

//send a message if one is in the queue
function sendMessage(){
	global $serial;
	global $messageQueue;
	$sent = true;

	if(!$messageQueue->isEmpty()){
		$message = $messageQueue->dequeue();
		echo $message->echolog;
		$sent = $serial->sendMessage($message->message);
	}
	if (!$sent){
		echo "MESSAGE NOT SENT";
	}
}

function getNextPollingRange(){
	global $currentGroup;
	global $currentAccount;

	global $pollingRange;
	global $accounts;
	global $baseAccount;
	global $groups;
	global $groupsGap;

	$account = $baseAccount + (($currentGroup - 1) * $groupsGap) + $currentAccount;
	$pollingMessage = sprintf("r%d/%d\r",$account,$pollingRange);
	$echoMessage = sprintf("[Polling [%d,%d]]\n",$account,$account+$pollingRange);
	$m = new Message($pollingMessage,$echoMessage);
	
	//update the status of the polling range
	$currentAccount += $pollingRange;

	if ($currentAccount > $accounts){
		$currentAccount = 1;
		$currentGroup++;
	}

	if ($currentGroup > $groups){
		$currentGroup = 1;
	}

	return $m;
}

function messageHandle(){
	global $serial;
	global $eventQueue;
	global $PRODUCTION;

	$read = $serial->readPort();

	switch (strlen($read)) {
		case LONGMESSAGELEN:
			//nack if the checksum is bad
			if (!checksum($read)){
				echo "bad checksum";
				$serial->sendMessage(NAK);
				return;
			}
			switch($read[0]){
				case "M": 
				case "U":
				case "E":
					if ($PRODUCTION == true ) {
						$event = new Event($read);
					} else if ($PRODUCTION == false) {
						$event = parseEventSimulator($read);
					}
					//TODO in the end no events should be null
					if ($event != null ){
						$eventQueue->enqueue($event);
						echo $event->String()."\n";
					}
					break;
				case "F":
					echo "[Firmware Read: ". substr($read,0,21) . "]\n";
					break;
				default:
					echo "[Unknown Long Command: " . $read . "]\n";
			}
			$serial->sendMessage(ACK);
			break;
		case SHORTMESSAGELEN:
			switch ($read[0]){
				case "Y":
					echo "[Accepted Command]\n";
					break;
				case "t":
					echo "[Timeout]\n";
					//TODO try command again a few times
					break;
				case "?":
					echo "[Bad Command]\n";
					//TODO try again but potentially ditch command
					break;
				default:
					echo "[Unknown Short Response: ". $read . "]\n";
			}
			$serial->sendMessage(ACK);
			break;
		case 0:
			return false;
		default:
			//TODO check how many bad messages have happened recently and give up at some point if it breaks
			echo "Bad message length: ". strlen($read) . " for command " . $read . "\n";
			//TODO stop throwing away bad events $serial->sendMessage(NAK);
			$serial->sendMessage(NAK);
	}
	//assume messages were received
	return true;
}


//TODO remove later this is a testing function
//TODO Deprecated march 6
function parseEventSimulator($message) {
	$event = new Event();
	$mon = substr($message,1,2);
	$d = substr($message,3,2);
	$y = substr($message,5,2);
	$h = substr($message,7,2);
	$min = substr($message,9,2);
	$s = substr($message,11,2);

	$datestr = "20".$y."-".$d."-".$mon." ".$h.":".$min.":".$s;
	$event->timestamp = \DateTime::createFromFormat('Y-d-m H:i:s',$datestr);
	if (! $event->timestamp) {
	    echo sprintf("'%s' is not a valid date.", $datestr);
	}	
	$event->account = substr($message,13,4);
	$z = substr($message,17,1);
	switch ($z) {
		case 1:
		case 2:
		case 3:
		case 4:
			$event->zone = $z;
			break;
		default:
			//no other zones are in the db
			return null;
	}
	$e = substr($message,18,1);
	switch ($e){
		case 1:
		case 2:
			$event->status = $e;
			break;
		default:
			
			//for testing put event in dead mode
			switch ($event->zone){
				case 1:
					$event->zone = "A";
				case 2:
					$event->zone = "B";
				case 3:
					$event->zone = "C";
				default:
					return null;
			}
	}
	$event->message = $message;
		
	return $event;
}



// etc...

function genchecksum($message){
	$hex = strToHex($message);
	$csum = 0;
	for ($i=0;$i<strlen($hex);$i=$i+2){
		$csum = $csum + (hexdec($hex[$i]) << 4) + hexdec($hex[$i+1]);
		//echo "checksum: " . $csum . " - " . $hex[$i] . "\n";
	}
	return chr($csum >> 8) . chr($csum % 128);

}

	/*The message structure is assumed by the checksum method to be
		
		II (MM x18) CS CS CR
	
	in the production server the checksum is only the lowest two ascii chars of the checksum
	in the simulator the checksum is two 8bit hex codes*/
function checksum($message){

	global $PRODUCTION;
	//calculate checksum from data
	$hex = strToHex($message);
	$csum = 0;
	for ($i=2;$i<strlen($hex)-7;$i=$i+2){
		$csum = $csum + (hexdec($hex[$i]) << 4) + hexdec($hex[$i+1]);
	}

	//extract checksum
	$sum = 0;
	$index = 0;
	
	if($PRODUCTION == false){
		for ($i=strlen($hex)-3;$i>strlen($hex)-7;$i--){
			//echo "checksum: " . $sum . " - " . $hex[$i] . "\n";
			$sum = $sum + (hexdec($hex[$i]) << ($index * 4) );
			$index++;
		}
	} else if ($PRODUCTION == true) {
		$first = substr($message,strlen($message)-3,1);
		$second = substr($message,strlen($message)-2,1);
		$sum = hexdec($second) + (hexdec($first) << 4);
		$csum = $csum % 256;

	}
	if ( $csum != $sum ){
		echo "bad checksum: " . $sum . " =/= " .$csum."\n";
		return false;
	}
	return true;
	
}

function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}

function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

//--------------------------------------------------------------------//
/*			DATABASE FUNCTIONS			     */
//--------------------------------------------------------------------//

function insertEvents() {
	global $eventQueue;
	global $db;

	$events = 0;
	$newpanels = 0;
	$newevents = 0;
	$oldevents = 0;
	if ($db == null) {
		//TODO handle error cases
		connectDB();
	}
	//assuming db is no longer nil
	while(!$eventQueue->isEmpty()){
		$events++;
		$event = $eventQueue->dequeue();
		$panel = queryEvent($event);
		//echo $panel->String() . "\n";
		//TODO make the panel timestamp real
		//If the panel has no message it is not in the DB yet
		if ($panel->message == ""){
			//addToDB
			$panel->Update($event);
			insertPanelDB($panel);
			$newpanels++;
		}
		//The event is newer than what is in the DB so add it 
		else if ($event->timestamp->getTimestamp() > date_create($panel->getZoneTimestamp($event->zone))->getTimestamp()){
			$panel->Update($event);
			updatePanelDB($panel);
			//updateDB
			$newevents++;
		}
		//The event timestamp <= whats in the DB its old and is ignored 
		else {
			$oldevents++;
		}
	}
	if ($events > 0){
		echo sprintf("[DB Write Stats :: Total Events: %d \tNew Panels: %d\tNew Events :%d\tOld Events: %d]\n",$events,$newpanels,$newevents,$oldevents);
	}
	
}

?>
