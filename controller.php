<?php
include "php_serial.class.php";
define ("ACK", chr(6));
define ("NAK", chr(21));
define ("SHORTMESSAGELEN", 2);
define ("LONGMESSAGELEN", 22);
define ("POLLINGTIME", 5);
define ("UPDATETIME", 15);



class Message {
	public $message;
	public $echolog;

	function Message ($m, $l) {
		$this->message = $m;
		$this->echolog = $l;
		return $this;
	}
}

class Event {
	public $account;
	public $zone;
	public $status;
	public $timestamp;
	public $message;
	
	function String() {
		return sprintf("[Account :%s, Zone :%s, Status :%s, Timestamp %s, Message %s]",$this->account,$this->zone,$this->status,$this->timestamp->format(DateTime::RFC2822), $this->message);
	}
}

class Panel {
	public $account;	//account number
	public $as;		//alarm status
	public $at;		//alarm timestamp
	public $aws;		//alarm wiring status
	public $awt;		//alarm wiring timestamp
	public $ss;		//supervisory status
	public $st;		//supervisory timestamp
	public $sws;		//supervisory wiring status
	public $swt;		//supervisory wiring timestamp
	public $ts;		//trouble status
	public $tt;		//trouble timestamp
	public $tws;		//trouble wiring status
	public $twt;		//trouble wiring timestamp
	public $ps;		//power status
	public $pt;		//power timestamp
	public $pws;		//power wiring status
	public $pwt;		//power wiring timestamp
	public $timestamp;	//most recent timestamp
	public $message;	//message for aux usage
	
	function String() {
		return sprintf(
"account :%s
as  :%s \tat  :%s 
aws :%s \tawt :%s
ss  :%s \tst  :%s 
sws :%s \tswt :%s
ts  :%s \ttt  :%s 
tws :%s \ttwt :%s
ps  :%s \tpt  :%s 
pws :%s \tpwt :%s
timestamp :%s message %s",
$this->account, 
$this->as, $this->at, 
$this->aws, $this->awt, 
$this->ss, $this->st, 
$this->sws, $this->swt, 
$this->ts, $this->tt, 
$this->tws, $this->twt, 
$this->ps, $this->pt, 
$this->pws, $this->pwt, 
$this->timestamp,$this->message);
	}

	function getZoneTimestamp($zone){
		switch ($zone){
			case "1":
				return $this->at;
			case "2":
				return $this->st;
			case "3":
				return $this->tt;
			case "4":
				return $this->pt;
			case "A":
				return $this->awt;
			case "B":
				return $this->swt;
			case "C":
				return $this->twt;
			case "D":
				return $this->pwt;
		}
		return "";
	}

	//construct a new panel in the off state
	function Panel($account){
		//Set the default time to UNIX EPOCH
		$epochTime = new DateTime();
		$epochTime->setTimestamp(0);
		$stamp = $epochTime->format(DateTime::RFC2822);
		
		$this->account = $account;
		$this->as = "1";
		$this->at = $stamp;
		$this->ss = "1";
		$this->st = $stamp;
		$this->ts = "1";
		$this->tt = $stamp;
		$this->ps = "1";
		$this->pt = $stamp;
		$this->aws = "1";
		$this->awt = $stamp;
		$this->sws = "1";
		$this->swt = $stamp;
		$this->tws = "1";
		$this->twt = $stamp;
		$this->pws = "1";
		$this->pwt = $stamp;
		$this->timestamp = $stamp;
		$this->message = "";
		//no timestamps in the default
		return $this;
	}

}

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

// We can change the baud rate
$serial->confBaudRate(9600);


// Then we need to open it
$serial->deviceOpen();
sleep(1);


//GLOBAL CONTROL VARIABLES
//True if Accounts should be polled
$messageTimer = [
	"poll" => time() - POLLINGTIME,
	"update" => time() - POLLINGTIME, 
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


	


//$serial->sendMessage("r4001/100\r",1);
//$serial->sendMessage("e1\r");
//$serial->sendMessage("f\r");
//$serial->sendMessage(ACK);

//$serial->sendMessage("r4001/100\r",1);
/*
$clockReset="cw010101000000AAAAA";
echo genchecksum($clockReset);
$clockReset = $clockReset . genchecksum($clockReset) . "\r";
echo $clockReset;
echo strlen($clockReset);
$serial->sendMessage($clockReset);
$read = $serial->readPort();
echo $read;
*/




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
		$message = new Message("u1\r","Sending Update ON\n");
		$messageQueue->enqueue($message);
		$messageTimer["update"] = $time;
		$didPoll = true;
	}

}

//send a message if one is in the queue
function sendMessage(){
	global $serial;
	global $messageQueue;

	if(!$messageQueue->isEmpty()){
		$message = $messageQueue->dequeue();
		echo $message->echolog;
		$serial->sendMessage($message->message);
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
	$echoMessage = sprintf("Polling [%d,%d]\n",$account,$account+$pollingRange);
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
			echo $read;
			//nack if the checksum is bad
			if (!checksum($read)){
				echo "bad checksum";
				$serial->sendMessage(ACK);
				return;
			}
			switch($read[0]){
				case "M": 
				case "U":
				case "E":
					if ($PRODUCTION == true ) {
						$event = parseEventProduction($read);
					} else if ($PRODUCTION == false) {
						$event = parseEventSimulator($read);
					}
					//TODO in the end no events should be null
					if ($event != null ){
						$eventQueue->enqueue($event);
						echo $event->String()."\n";
					}
					break;
				//clock read
				case "C":
					echo "Clock Read: " . $read . "\n";
					break;
				case "F":
					echo "Firmware Read: ". $read . "\n";
					break;
				default:
					echo "Unknown Long Command: " . $read . "\n";
			}
			$serial->sendMessage(ACK);
			break;
		case SHORTMESSAGELEN:
			switch ($read[0]){
				case "Y":
					echo "Accepted Command\n";
					break;
				case "t":
					echo "Timeout\n";
					//TODO try command again a few times
					break;
				case "?":
					echo "Bad Command\n";
					//TODO try again but potentially ditch command
					break;
				default:
					echo "Unknown Short Response: ". $read . "\n";
			}
			$serial->sendMessage(ACK);
			break;
		case 0:
			return false;
		default:
			//TODO check how many bad messages have happened recently and give up at some point if it breaks
			echo "Bad message length: ". strlen($read) . " for command " . $read . "\n";
			//TODO stop throwing away bad events $serial->sendMessage(NAK);
			$serial->sendMessage(ACK);
	}
	//assume messages were received
	return true;
}


function parseEventProduction($message){
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
	$event->zone = substr($message,17,1);
	$event->status = substr($message,18,1);
	$event->message = $message;
	return $event;

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
	/*if ($csum < (2 << 12)) {
		return "0". dechex($csum);
	}
	return dechex($csum);
	*/

}

	/*The message structure is assumed by the checksum method to be
		
		II (MM x18) CS CS CR
	
	in the production server the checksum is only the lowest two ascii chars of the checksum
	in the simulator the checksum is two 8bit hex codes*/
function checksum($message){

	global $PRODUCTION;
	
	$hex = strToHex($message);
	for ($i = 0;$i<strlen($hex); $i++){
		echo " " . $hex[$i];
	}
	if (strlen($hex) != LONGMESSAGELEN * 2) {
		echo "inproper length: " . strlen($hex) . "\n";
		return false;
	}
	//calculate checksum from data
	$csum = 0;
	for ($i=2;$i<strlen($hex)-7;$i=$i+2){
		$csum = $csum + (hexdec($hex[$i]) << 4) + hexdec($hex[$i+1]);
		//echo "checksum: " . $csum . " - " . $hex[$i] . "\n";
	}
	echo "checksum" . dechex($csum) . "\n";

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
		echo "first ". $first. " second ". $second ."\n";
		$sum = hexdec($second) + (hexdec($first) << 4);
		echo dechex($csum);
		$csum = $csum % 128;

	}
	if ( $csum != $sum ){
		echo "bad checksum: " . $sum . " =/= " .$csum."\n";
		//return false;
		return true;
	}
	//echo "good checksum!";
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
	if ($db == null) {
		//TODO handle error cases
		connectDB();
	}
	//assuming db is no longer nil
	while(!$eventQueue->isEmpty()){
		$event = $eventQueue->dequeue();
		$panel = queryEvent($event);
		echo $panel->String() . "\n";
		//TODO make the panel timestamp real
		//If the panel has no timestamp it is not in the DB yet
		if ($panel->message == ""){
			//addToDB
			$panel = new Panel($event->account);
			$panel = updatePanel($panel,$event);
			insertPanelDB($panel);
			echo "New Panel";
		}
		//The event is newer than what is in the DB so add it 
		else if ($event->timestamp->getTimestamp() > date_create($panel->getZoneTimestamp($event->zone))->getTimestamp()){
			$panel = updatePanel($panel,$event);
			updatePanelDB($panel);
			//updateDB
			echo "New Event";
		}
		//The event timestamp <= whats in the DB its old and is ignored 
		else {
			echo "OLD EVENT";
		}
	}
	
}


function updatePanel($panel, $event) {
	//update timestamp will have to be distributed later
	$panel->timestamp = $event->timestamp->format(DateTime::RFC2822);
	$panel->message = $event->message;
	switch ($event->zone){
		case "1":
			$panel->as = $event->status;
			$panel->at = $panel->timestamp;
			break;
		case "2":
			$panel->ss = $event->status;
			$panel->st = $panel->timestamp;
			break;
		case "3":
			$panel->ts = $event->status;
			$panel->tt = $panel->timestamp;
			break;
		case "4":
			$panel->ps = $event->status;
			$panel->pt = $panel->timestamp;
			break;
		case "A":
			$panel->aws = $event->status;
			$panel->awt = $panel->timestamp;
			break;
		case "B":
			$panel->sws = $event->status;
			$panel->swt = $panel->timestamp;
			break;
		case "C":
			$panel->tws = $event->status;
			$panel->twt = $panel->timestamp;
			break;
		case "D":
			$panel->pws = $event->status;
			$panel->pwt = $panel->timestamp;
			break;
		default:
	}
	return $panel;
}

function queryEvent($event) {
	global $db;
	$stmt = $db->prepare("
	SELECT *
	From Event
	Where account = ?");
	$stmt->bind_param("i", $event->account);
	$panel = new Panel($event->account);
	$stmt->bind_result(
		$panel->account, 
		$panel->as, $panel->at, 
		$panel->aws, $panel->awt, 
		$panel->ss, $panel->st, 
		$panel->sws, $panel->swt, 
		$panel->ts, $panel->tt,
		$panel->tws, $panel->twt,
		$panel->ps, $panel->pt, 
		$panel->pws, $panel->pwt, 
		$panel->timestamp,$panel->message);
	$stmt->execute();
	$result = $stmt->fetch();
	return $panel;
}

function connectDB(){
        $server = '127.0.0.1';
	$user = 'root';
	$pass = 'iwicbV15';
        $dbname = 'Gibil';
        
        global $db;
		$db = new mysqli($server, $user, $pass, $dbname);
		if (!mysqli_connect_errno()) {
			echo "db connected\n";
        }

        if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
        }
}
function insertPanelDB($panel){
    global $db;
	
$stmt = $db->prepare("INSERT INTO Event (account,
alarmstate,alarmtimestamp,
alarmwirestate,alarmwiretimestamp,
supervisorystate,supervisorytimestamp,
supervisorywirestate,supervisorywiretimestamp,
troublestate,troubletimestamp,
troublewirestate,troublewiretimestamp,
powerstate,powertimestamp,
powerwirestate,powerwiretimestamp,
timestamp,message) Values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issssssssssssssssss", $panel->account, 
$panel->as, $panel->at, 
$panel->aws, $panel->awt, 
$panel->ss, $panel->st, 
$panel->sws, $panel->swt, 
$panel->ts, $panel->tt, 
$panel->tws, $panel->twt, 
$panel->ps, $panel->pt, 
$panel->pws, $panel->pwt, 
$panel->timestamp,$panel->message);
    $stmt->execute();
    if($stmt->error) {
        printf("<b>Error: %s. </b>\n", $stmt->error);
        return $stmt->error;
    } else {
        return 0;
    }
}

function updatePanelDB($panel){
    global $db;
	
$stmt = $db->prepare("update Event set 
alarmstate=?, alarmtimestamp=?, 
alarmwirestate=?, alarmwiretimestamp=?, 
supervisorystate=?, supervisorytimestamp=?, 
supervisorywirestate=?, supervisorywiretimestamp=?, 
troublestate=?, troubletimestamp=?, 
troublewirestate=?, troublewiretimestamp=?, 
powerstate=?, powertimestamp=?, 
powerwirestate=?, powerwiretimestamp=?, 
timestamp=?, message=? where account=?");
//var_dump($stmt); //to debug
    $stmt->bind_param("ssssssssssssssssssi", 
$panel->as, $panel->at, 
$panel->aws, $panel->awt, 
$panel->ss, $panel->st, 
$panel->sws, $panel->swt, 
$panel->ts, $panel->tt, 
$panel->tws, $panel->twt, 
$panel->ps, $panel->pt, 
$panel->pws, $panel->pwt, 
$panel->timestamp, $panel->message,$panel->account);
    $stmt->execute();
    if($stmt->error) {
        printf("<b>Error: %s. </b>\n", $stmt->error);
        return $stmt->error;
    } else {
        return 0;
    }
}
?>
