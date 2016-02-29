<?php
include "php_serial.class.php";
define ("ACK", chr(6));
define ("NAK", chr(21));
define ("SHORTMESSAGELEN", 2);
define ("LONGMESSAGELEN", 22);
define ("POLLINGTIME", 5 * 60);
define ("POLLINGWAIT", 30);


class Event {
	public $account;
	public $zone;
	public $status;
	public $timestamp;
	
	function String() {
		return sprintf("[Account :%s, Zone :%s, Status :%s, Timestamp %s]",$this->account,$this->zone,$this->status,$this->timestamp->format(DateTime::RFC2822));
	}
}

class Panel {
	public $account;	//account number
	public $az;		//alarm zone
	public $as;		//alarm status
	public $at;		//alarm timestamp
	public $sz;		//supervisory zone
	public $ss;		//supervisory status
	public $st;		//supervisory timestamp
	public $tz;		//trouble zone
	public $ts;		//trouble status
	public $tt;		//trouble timestamp
	public $pz;		//power zone
	public $ps;		//power status
	public $pt;		//power timestamp
	public $timestamp;	//most recent timestamp
	public $message;	//message for aux usage
	
	function String() {
		return sprintf("[account :%s az :%s as :%s at :%s sz :%s ss :%s st : %s tz :%s ts :%s tt :%s pz :%s ps :%s pt %s timestamp :%s message %s]",$this->account, $this->az, $this->as, $this->at, $this->sz, $this->ss, $this->st, $this->tz, $this->ts, $this->tt, $this->pz, $this->ps, $this->pt, $this->timestamp,$this->message);
	}

	function getZoneTimestamp($zone){
		switch ($zone){
			case "1":
			case "A":
				return $this->at;
			case "2":
			case "B":
				return $this->st;
			case "3":
			case "C":
				return $this->tt;
			case "4":
			case "D":
				return $this->pt;
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
		$this->az = "1";
		$this->as = "1";
		$this->at = $stamp;
		$this->sz = "2";
		$this->ss = "1";
		$this->st = $stamp;
		$this->tz = "3";
		$this->ts = "1";
		$this->tt = $stamp;
		$this->pz = "4";
		$this->ps = "1";
		$this->pt = $stamp;
		$this->timestamp = $stamp;
		$this->message = "NEW PANEL";
		//no timestamps in the default
		return $this;
	}

}

//-----------------------------------------------------------------------//
/*			Start Execution Here				 */
//-----------------------------------------------------------------------//

$db = NULL;
$eventQueue = new SplQueue();

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
$POLL = [
	"4000" => time() - POLLINGTIME, //inital
	"5000" => time() - POLLINGTIME + 60,
	"6000" => time() - POLLINGTIME + 120,
	"7000" => time() - POLLINGTIME + 180,
];
//True if polling is happening
$POLLING = 0;


while(true){
	if(messageHandle()){
		//do if a message was received
	} else {
		//non message received tasks
		pollingRoutine();
		insertEvents();
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
*/



// If you want to change the configuration, the device must be closed
$serial->deviceClose();


function pollingRoutine(){
	global $serial;
	global $POLL;
	global $POLLING;
	//Poll only if no polling is occuring and no messages were received
	$time = time();
	if ($time - $POLLING > POLLINGWAIT){
		$didPoll = false;
		if ($time - $POLL["4000"] > POLLINGTIME){
			echo "Polling 4000!\n";
			$serial->sendMessage("r4001/100\r");
			$POLL["4000"] = $time;
			$didPoll = true;
		} else if ($time - $POLL["5000"] > POLLINGTIME) {
			echo "Polling 5000!\n";
			$serial->sendMessage("r5001/100\r");
			$POLL["5000"] = $time;
			$didPoll = true;
		} else if ($time - $POLL["6000"] > POLLINGTIME) {
			echo "Polling 6000!\n";
			$serial->sendMessage("r6001/100\r");
			$POLL["6000"] = $time;
			$didPoll = true;
		} else if ($time - $POLL["7000"] > POLLINGTIME) {
			echo "Polling 7000!\n";
			$serial->sendMessage("r7001/100\r");
			$POLL["7000"] = $time;
			$didPoll = true;
		}
		if ($didPoll) {
			echo "Polling OFF!\n";
			$POLLING = $time;
		}
	}

}

function messageHandle(){
	global $serial;
	global $eventQueue;
	$read = $serial->readPort();

	switch (strlen($read)) {
		case LONGMESSAGELEN:
			//nack if the checksum is bad
			if (!checksum(strToHex($read))){
				echo "bad checksum";
				$serial->sendMessage(NAK);
				return;
			}
			switch($read[0]){
				case "M": 
				case "U":
				case "E":
					$event = parseEventSimulator($read);
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


//TODO remove later this is a testing function
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

function checksum($hex){
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

	//extract checksum
	$sum = 0;
	$index = 0;
	for ($i=strlen($hex)-3;$i>strlen($hex)-7;$i--){
		//echo "checksum: " . $sum . " - " . $hex[$i] . "\n";
		$sum = $sum + (hexdec($hex[$i]) << ($index * 4) );
		$index++;
	}
	if ( $csum != $sum ){
		//echo "bad checksum: " . $sum . " =/= " .$csum."\n";
		return false;
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
		if ($panel->timestamp == ""){
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
	switch ($event->zone){
		case "1":
		case "A":
			$panel->az = $event->zone;
			$panel->as = $event->status;
			$panel->at = $panel->timestamp;
			break;
		case "2":
		case "B":
			$panel->sz = $event->zone;
			$panel->ss = $event->status;
			$panel->st = $panel->timestamp;
			break;
		case "3":
		case "C":
			$panel->tz = $event->zone;
			$panel->ts = $event->status;
			$panel->tt = $panel->timestamp;
			break;
		case "4":
			$panel->pz = $event->zone;
			$panel->ps = $event->status;
			$panel->pt = $panel->timestamp;
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
	$panel = new Panel();
	$stmt->bind_result($panel->account, $panel->az, $panel->as, $panel->at, $panel->sz, $panel->ss, $panel->st, $panel->tz, $panel->ts, $panel->tt ,$panel->pz, $panel->ps, $panel->pt, $panel->timestamp,$panel->message);
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
	
$stmt = $db->prepare("INSERT INTO Event (account,alarmzone,alarmstate,alarmtimestamp,supervisoryzone,supervisorystate,supervisorytimestamp,troublezone,troublestate,troubletimestamp,powerzone,powerstate,powertimestamp,timestamp,message) Values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issssssssssssss", $panel->account, $panel->az, $panel->as, $panel->at, $panel->sz, $panel->ss, $panel->st, $panel->tz, $panel->ts, $panel->tt, $panel->pz, $panel->ps, $panel->pt, $panel->timestamp,$panel->message);
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
	
$stmt = $db->prepare("update Event set alarmzone=?, alarmstate=?, alarmtimestamp=?, supervisoryzone=?, supervisorystate=?, supervisorytimestamp=?, troublezone=?, troublestate=?, troubletimestamp=?, powerzone=?, powerstate=?, powertimestamp=?, timestamp=?, message=? where account=?");
    $stmt->bind_param("ssssssssssssssi", $panel->az, $panel->as, $panel->at, $panel->sz, $panel->ss, $panel->st, $panel->tz, $panel->ts, $panel->tt, $panel->pz, $panel->ps, $panel->pt, $panel->timestamp,$panel->message,$panel->account);
    $stmt->execute();
    if($stmt->error) {
        printf("<b>Error: %s. </b>\n", $stmt->error);
        return $stmt->error;
    } else {
        return 0;
    }
}
?>
