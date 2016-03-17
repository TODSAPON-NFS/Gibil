<?php
/*
	dbcontroller.php is the controller for the mysql database, the controler
	is composed of a number of queries that leverage the mysqli API

	@author Stewart Grant
	@version 1.0.0
	@modified March 14 2016
*/	

/*
	queryEvent queries the database for panels based on an account name
	a panel object is retured filled with values from the DB, if the Panel
	does not exist in the DB a default panel is returned.
*/
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

/*
	connectDB is the first call made to start a connection with the mysql
 	database. the assumption is that the db is local, named gibil, and has
	root permisions set.
*/

function connectDB(){
        $server = '127.0.0.1';
	$user = 'root';
	$pass = 'iwicbV15';
        $dbname = 'Gibil';
        
        global $db;
		$db = new mysqli($server, $user, $pass, $dbname);
		if (!mysqli_connect_errno()) {
			echo "[db connected]\n";
        }

        if (mysqli_connect_errno()) {
			printf("[Connect failed: %s]\n", mysqli_connect_error());
			exit();
        }
}

/*
	insertPanelDB inserts a panel object into its corresponding place in the
	db. This method returns an error if the panel is allready in the DB and 0 
	upon success
	@return error if panel exists 0 otherwise
*/
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

/*
	UpdatePanelDB updates a panel in the DB based on a panel object.
	if the panel does not exist in the DB an error is thrown.
	0 is returned on success
	@return error if panel exists, 0 on success
*/
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
