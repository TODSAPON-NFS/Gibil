<?php

$db = NULL;
$categories = 8;
//probability that the alarm will cause an error // used to normalize the events to create a more realistic enviornment
$alarmProb = 5; //probability 1 / alarmProm
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

function construct(){
        $server = '127.0.0.1';
	$user = 'root';
	$pass = 'iwicbV15';
        $dbname = 'Gibil';
        
        global $db;
		$db = new mysqli($server, $user, $pass, $dbname);
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
		$panel = new Panel($i * $groupGap + $startingAccount + $j);
		if (insertPanelDB($panel) != 0){
			updatePanelDB($panel);
		}
        }
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
			$panel = new Panel($startingAccount + (rand(0, $groups) * $groupGap) + rand(0, $accountsPerGroup));
			$panel->az = $stats["alarm"]["zone"][alarm()];
			$panel->as = $stats["alarm"]["state"][alarm()];
			$panel->at = date(DATE_RFC2822);
			$panel->sz = $stats["supervisory"]["zone"][alarm()];
			$panel->ss = $stats["supervisory"]["state"][alarm()];
			$panel->st = date(DATE_RFC2822);
			$panel->tz = $stats["trouble"]["zone"][alarm()];
			$panel->ts = $stats["trouble"]["state"][alarm()];
			$panel->tt = date(DATE_RFC2822);
			$panel->pz = $stats["power"]["zone"][0];
			$panel->ps = $stats["power"]["state"][alarm()];
			$panel->pt = date(DATE_RFC2822);
			$panel->timestamp = date(DATE_RFC2822);
			$account = $startingAccount + (rand(0, $groups) * $groupGap) + rand(0, $accountsPerGroup);
			updatePanelDB($panel);
		}
	}
}
			
construct();
initPanels();
simulateEvents();
?>
