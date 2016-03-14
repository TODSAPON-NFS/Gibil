<?php
include "php_serial.class.php";
include "dbcontrol.php";
include "controlclasses.php";
define ("ACK", chr(6));
define ("NAK", chr(21));
define ("XON", chr(17));
define ("XOFF", chr(19));
define ("SHORTMESSAGELEN", 2);
define ("LONGMESSAGELEN", 22);

//Timing intervals for periodic tasks// TODO set better ones
define ("POLLINGTIME", 5);
define ("UPDATETIME", 15);
define ("FIRMWARETIME", 15);
define ("LOGFILETIME",15);

//timing intervals for event driven tasks
define ("TROUBLESHOOTTIME", 15);
define ("EMAILTIME", 30);
define ("EMAILFREQUENCY", 60*60*24);

define ("LOGDIR","/home/gibil/Gibil/logs/");



//-----------------------------------------------------------------------//
/*			Start Execution Here				 */
//-----------------------------------------------------------------------//

//database
$db = NULL;

//event queue holds events to be put in the DB
$eventQueue = new SplQueue();
//message queue holds messages to be send to the AEU
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
$taskTimer = [
	"poll" => time() - POLLINGTIME,
	"update" => time() - UPDATETIME, 
	"firmware" => time() - FIRMWARETIME,
	"log" => time() - LOGFILETIME,
	"email" => time() - EMAILFREQUENCY,
	"lastmessage" => time(), 
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

$logfilename;
$logfile;




setLogFile();

//signal handling
declare(ticks=1); // PHP internal, make signal handling work
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGINT, "sig_handler");



while(true){
	global $taskTimer;
	if(messageHandle()){
		//do if a message was received
		$taskTimer["lastmessage"] = time();
	} else {
		//non message received tasks
		insertEvents();
		taskMannage();
		sendMessage();
	}
	
	
}

// If you want to change the configuration, the device must be closed
$serial->deviceClose();


	
/* genMessages checks the message timer to see if any periodic messages such as polling, or update are due to be sent.
if so a message object is generated, and added to the message queue for sending to the aeu*/
function taskMannage(){
	global $serial;
	global $taskTimer;
	global $messageQueue;
	global $PRODUCTION;
	global $lastMessageTime;
	//Poll only if no polling is occuring and no messages were received
	$time = time();

	//periodic
	if ($time - $taskTimer["poll"] > POLLINGTIME){
		$message = getNextPollingRange();
		$messageQueue->enqueue($message);
		$taskTimer["poll"] = $time + POLLINGTIME;
	} 
	if ($time - $taskTimer["update"] > UPDATETIME){
		$message = new Message("u1\r","Sending Update ON");
		$messageQueue->enqueue($message);
		$taskTimer["update"] = $time;
	} 
	if ($time - $taskTimer["firmware"] > FIRMWARETIME){
		$message = new Message("f\r","Sending firmware info request");
		$messageQueue->enqueue($message);
		$taskTimer["firmware"] = $time;
	}
 
	if ($time - $taskTimer["log"] > LOGFILETIME){
		setLogFile();
		$taskTimer["log"] = $time;
	}

	//event driven
	if ($time - $taskTimer["lastmessage"] > TROUBLESHOOTTIME){
		$message = new Message(XON,"Troubleshooting with XON");
		$messageQueue->enqueue($message);
	} 
	if (
		$time - $taskTimer["lastmessage"] > EMAILTIME &&
		$time - $taskTimer["email"] > EMAILFREQUENCY
	){
		sendEmail();
		$taskTimer["email"] = $time;
	} 

}

//send a message if one is in the queue
function sendMessage(){
	global $serial;
	global $messageQueue;
	$sent = true;

	if(!$messageQueue->isEmpty()){
		$message = $messageQueue->dequeue();
		out( $message->echolog);
		$sent = $serial->sendMessage($message->message);
	}
	if (!$sent){
		out("MESSAGE NOT SENT");
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
	$echoMessage = sprintf("Polling [%d,%d]",$account,$account+$pollingRange);
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
				out("bad checksum");
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
						out( $event->String());
					}
					break;
				case "F":
					out( "Firmware Read: ". substr($read,0,21));
					break;
				default:
					out( "Unknown Long Command: " . $read);
			}
			$serial->sendMessage(ACK);
			break;
		case SHORTMESSAGELEN:
			switch ($read[0]){
				case "Y":
					out( "Accepted Command");
					break;
				case "t":
					out( "Timeout");
					//TODO try command again a few times
					break;
				case "?":
					out( "Bad Command");
					//TODO try again but potentially ditch command
					break;
				default:
					out( "Unknown Short Response: ". $read);
			}
			$serial->sendMessage(ACK);
			break;
		case 0:
			return false;
		default:
			//TODO check how many bad messages have happened recently and give up at some point if it breaks
			out("Bad message length: ". strlen($read) . " for command " . substr($read,0,strlen($read)-1));
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
	    out( sprintf("'%s' is not a valid date.", $datestr));
	}	
	$event->account = substr($message,13,4);
	$z = substr($message,17,1);
	$wiretamper = rand(0,1);
	if ($wiretamper == 0){
		switch ($z) {
			case 1:
				$event->zone = "A";
				break;
			case 2:
				$event->zone = "B";
				break;
			case 3:
				$event->zone = "C";
				break;
			case 4:
				$event->zone = "D";
				break;
			default:
				//no other zones are in the db
				return null;
		}
	} else {
		$event->zone = $z;
	}
	$event->status = substr($message,18,1);
	$event->message = $message;
		
	return $event;
}



// etc...

function genchecksum($message){
	$hex = strToHex($message);
	$csum = 0;
	for ($i=0;$i<strlen($hex);$i=$i+2){
		$csum = $csum + (hexdec($hex[$i]) << 4) + hexdec($hex[$i+1]);
		//out( "checksum: " . $csum . " - " . $hex[$i] . "\n");
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
			//out( "checksum: " . $sum . " - " . $hex[$i] . "\n");
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
		out( "bad checksum: " . $sum . " =/= " .$csum."\n");
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
		$panelEvent = $panel->getEvent($event->zone);
		out($panelEvent->String());
		//out( $panel->String() . "\n");
		//TODO make the panel timestamp real
		//If the panel has no message it is not in the DB yet
		if ($panel->message == ""){
			//out("Addiing new panel + ".$panel->account." to DB"); 
			//addToDB
			$panel->Update($event);
			insertPanelDB($panel);
			$newpanels++;
		}
		//The event is newer than what is in the DB so add it 
		else if ($event->timestamp->getTimestamp() >= $panelEvent->timestamp->getTimestamp() && $event->status != $panelEvent->status){
			//out("Updating: ".$panel->account."-".$event->zone);
			$panel->Update($event);
			updatePanelDB($panel);
			//updateDB
			$newevents++;
		}
		//The event timestamp <= whats in the DB its old and is ignored 
		else {
			//out("Old event");
			$oldevents++;
		}
	}
	if ($events > 0){
		out( sprintf("DB Write Stats :: Total Events: %d \tNew Panels: %d\tNew Events :%d\tOld Events: %d",$events,$newpanels,$newevents,$oldevents));
	}
	
}

function setLogFile(){
	global $logfilename;
	global $logfile;
	
	$name = LOGDIR.date("FY").".log";
	if ($logfilename != $name){
		if($logfile != null){
			out("Switching log files to " . $name);
			fclose($logfile);
		}
		$logfilename = $name;
		touch($logfilename);
		chmod($logfilename,0777);
		$logfile = fopen($logfilename, "a+") or die("Unable to open file!");
		if (filesize($logfilename) > 0){
			out("New Execution");
		} else {
			out("New Log " . $logfilename );
		}
	}
	return;
}

function sendEmail(){
	global $taskTimer;
	//mail functionality		
	out( "[SENDING EMAIL]\n");
	$msg = 
"Hi All,
This is an automatic email from the AEU Web Server

The webserver has not receved any messages from the aeu in ". (time() - $taskTimer["lastmessage"]). "s 
and may be unpluged consider plugging it in

cheers,
-AEU Webserver.\n";

	$headers = "From: stewbertgrant@gmail.com" . "\r\n" .
			"CC: davis@dccnet.com , stewbertgrant@gmail.com";
	$msg = wordwrap($msg,70);
	$sent = mail("xerwnexa@sharklasers.com","web server email test",$msg,$headers);
	if ($sent === false){
		out( "[Email not sent]\n");
	}
}

function out($message){
	global $logfile;
	$message = "[". $message ."][" . date("ymdhis") ."]\n";
	echo $message;
	fwrite($logfile,$message);
}


// signal handler function
function sig_handler($signo)
{

     switch ($signo) {
         case SIGTERM:
         case SIGINT:
	     out("[Shutting Down: ". date("F j, Y, g:i a")."]\n");
             exit;
             break;
         default:
             // handle all other signals
     }

}

?>


