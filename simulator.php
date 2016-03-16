<?php

include "dbcontrol.php";
include "controlclasses.php";

$db = NULL;
$categories = 8;
//probability that the alarm will cause an error // used to normalize the events to create a more realistic enviornment
$alarmProb = 10; //probability 1 / alarmProm
$accountsPerGroup = 100;
$groups = 4;
$groupGap = 1000;
$startingAccount = 4001;

$newPerRun = 8;
$sleepTime = 5;


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



//alarm randomly generates alarm
function alarm(){
	global $alarmProb;
	$alarm = rand(0,$alarmProb);
	if($alarm == 0 ) {
		return 2;
	} 
	return 1;
}

function simulateEvents(){
	global $alarmProb;
	global $accountsPerGroup;
	global $groups;
	global $groupGap;
	global $startingAccount;
    	global $panels;
    	global $stats;
	global $newPerRun;
	global $sleepTime;
	while( true ) {
		$newEvents = rand(0,$newPerRun);
			
		for ($i =0;$i < $newEvents; $i++){
			$panel = new Panel($startingAccount + (rand(0, $groups) * $groupGap) + rand(0, $accountsPerGroup));
			if ($panel->account%2 == 0){
				continue;
			}
			$panel->as= alarm(); 
			$panel->at= date(DATE_RFC2822); 
			$panel->aws= alarm(); 
			$panel->awt= date(DATE_RFC2822); 
			$panel->ss= alarm(); 
			$panel->st= date(DATE_RFC2822); 
			$panel->sws= alarm(); 
			$panel->swt= date(DATE_RFC2822); 
			$panel->ts= alarm(); 
			$panel->tt= date(DATE_RFC2822);
			$panel->tws= alarm(); 
			$panel->twt= date(DATE_RFC2822);
			$panel->ps= alarm(); 
			$panel->pt= date(DATE_RFC2822); 
			$panel->pws= alarm(); 
			$panel->pwt= date(DATE_RFC2822); 
			$panel->timestamp = date(DATE_RFC2822);
			$panel->message = "Simulated";
			updatePanelDB($panel);
		}
		sleep($sleepTime);
	}
}

connectDB();			
initPanels();
simulateEvents();
?>
