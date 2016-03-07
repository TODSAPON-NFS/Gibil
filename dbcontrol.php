<?php


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
