<?php
include "php_serial.class.php";
define ("ACK", chr(6));
define ("NAK", chr(21));
define ("SHORTMESSAGELEN", 2);
define ("LONGMESSAGELEN", 22);
define ("POLLINGTIME", 5 * 60);
define ("POLLINGWAIT", 30);

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
	"7000" => time() - POLIINGTIME + 180,
];
//True if polling is happening
$POLLING = 0;


while(true){
	messageHandle();
	
	//Poll only if no polling is occuring
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





//$serial->sendMessage("r4001/100\r",1);
//$serial->sendMessage("e1\r");
//$serial->sendMessage("f\r");
//$serial->sendMessage(ACK);

$serial->sendMessage("r4001/100\r",1);

/*
$clockReset="cw010101000000AAAAA";
echo genchecksum($clockReset);
$clockReset = $clockReset . genchecksum($clockReset) . "\r";
echo $clockReset;
echo strlen($clockReset);
$serial->sendMessage($clockReset);
*/
while ($read = $serial->readPort()){
	echo $read;
	$hex = strToHex($read);
	checksum($hex);
	//echo $hex;
	echo strlen($read) + "\n";
	echo $read;
	printf("%d\n",$read);
	//$serial->sendMessage("cr\r");
	//sleep(.5);
	$serial->sendMessage(ACK);
}



// If you want to change the configuration, the device must be closed
$serial->deviceClose();


function messageHandle(){
	global $serial;
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
					//$event = parseEvent($read);
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
			return;
		default:
			echo "Bad message length: ". strlen($read) . " for command " . $read . "\n";
	}
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
	for ($i=0;$i<strlen($hex);$i = $i+2){
		echo $hex[$i] . $hex[$i +1] ." ";
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
?>
