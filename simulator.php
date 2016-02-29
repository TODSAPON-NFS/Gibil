<?php

$connection = NULL;
$categories = 8;
//probability that the alarm will cause an error // used to normalize the events to create a more realistic enviornment
$alarmProb = 2;
$accountsPerGroup = 100;
$groups = 4;
$groupGap = 1000;
$startingAccount = 4000;
$stats = [
    "alarm" => [
	"zone" => ["1","A"],
	"state" => ["1","2"]],
    "supervisory" => [
	"zone" => ["2","B"],
	"state" => ["1","2"]],
    "trouble" => [
	"zone" => ["3","C"],
	"state" => ["1","2"]],
    "power" => [
	"zone" => ["4"],
	"state" => ["1","2"]],
];

function construct(){
        $server = '127.0.0.1';
	$user = 'root';
	$pass = 'iwicbV15';
        $dbname = 'Gibil';
        
        global $connection;
		$connection = new mysqli($server, $user, $pass, $dbname);
		if (!mysqli_connect_errno()) {
        }

        if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
        }
}

function initPanels(){
	global $alarmProb;
	global $accountsPerGroup;
	global $groups;
	global $groupGap;
	global $startingAccount;
    	global $panels;
    	global $stats;
    	for($i = 0; $i < $groups;$i++){
        for($j=0; $j< $accountsPerGroup;$j++){
		//attempt to update existing database, if the events do not exist then insert them
		echo $i * $groupGap + $startingAccount + $j;
		if(insertEvent(($i * $groupGap + $startingAccount + $j),
			$stats["alarm"]["zone"][0], $stats["alarm"]["state"][0],
			$stats["supervisory"]["zone"][0], $stats["supervisory"]["state"][0],
			$stats["trouble"]["zone"][0], $stats["trouble"]["state"][0],
			$stats["power"]["zone"][0], $stats["power"]["state"][0],
			date(DATE_RFC2822),"Simulated") != 0) {

				updateEvent(($i * $groupGap + $startingAccount + $j),
				$stats["alarm"]["zone"][0], $stats["alarm"]["state"][0],
				$stats["supervisory"]["zone"][0], $stats["supervisory"]["state"][0],
				$stats["trouble"]["zone"][0], $stats["trouble"]["state"][0],
				$stats["power"]["zone"][0], $stats["power"]["state"][0],
				date(DATE_RFC2822),"Simulated");
			}
        }
    }
}


function insertEvent($account, $az, $as, $sz, $ss, $tz, $ts, $pz, $ps, $timestamp,$message){
    global $connection;
	
$stmt = $connection->prepare("INSERT INTO Event (account,alarmzone,alarmstate,supervisoryzone,supervisorystate,troublezone,troublestate,powerzone,powerstate,timestamp,message) Values (?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issssssssss", $account, $az, $as, $sz, $ss, $tz, $ts, $pz, $ps, $timestamp,$message);
    $stmt->execute();
    if($stmt->error) {
        printf("<b>Error: %s. </b>\n", $stmt->error);
        return $stmt->error;
    } else {
        return 0;
    }
}
function updateEvent($account, $az, $as, $sz, $ss, $tz, $ts, $pz, $ps, $timestamp,$message){
    global $connection;
	
$stmt = $connection->prepare("update Event set alarmzone=?, alarmstate=?, supervisoryzone=?, supervisorystate=?, troublezone=?, troublestate=?, powerzone=?, powerstate=?, timestamp=?, message=? where account=?");
    $stmt->bind_param("ssssssssssi", $az, $as, $sz, $ss, $tz, $ts, $pz, $ps, $timestamp,$message,$account);
    $stmt->execute();
    if($stmt->error) {
        printf("<b>Error: %s. </b>\n", $stmt->error);
        return $stmt->error;
    } else {
        return 0;
    }
}

//alarm randomly generates alarm
function alarm(){
	global $alarmProb;
	$alarm = rand(0,$alarmProb);
	if($alarm == 0 ) {
		return 1;
	} 
	return 0;
}

function simulateEvents(){
	global $alarmProb;
	global $accountsPerGroup;
	global $groups;
	global $groupGap;
	global $startingAccount;
    	global $panels;
    	global $stats;
	while( true ) {
		$newEvents = rand(0,5);
		for ($i =0;$i < $newEvents; $i++){
			$az = $stats["alarm"]["zone"][alarm()];
			$as = $stats["alarm"]["state"][alarm()];
			$sz = $stats["supervisory"]["zone"][alarm()];
			$ss = $stats["supervisory"]["state"][alarm()];
			$tz = $stats["trouble"]["zone"][alarm()];
			$ts = $stats["trouble"]["state"][alarm()];
			$pz = $stats["power"]["zone"][0];
			$ps = $stats["power"]["state"][alarm()];
			$account = $startingAccount + (rand(0, $groups) * $groupGap) + rand(0, $accountsPerGroup);
			updateEvent($account, $az, $as, $sz, $ss, $tz, $ts, $pz, $ps, date(DATE_RFC2822),"Simulated");
		}
	}
}
			
construct();
initPanels();
simulateEvents();
?>
