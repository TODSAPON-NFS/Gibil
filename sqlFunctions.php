<?php

global $connection;
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

function queryPanels(){
    global $connection;
    if(!$result = $connection->query("Select * FROM Event")) {
			die('There was an error running the query [' .$db->error . ']');
		} else {
	}
	return $result;
}

function queryAllPanels(){
    global $connection;
    if(!$result = $connection->query(
	'SELECT * 
	 FROM Event 
	 WHERE (panel, zone, timestamp) IN
	 ( 
		SELECT panel, zone, max(timestamp)
		FROM Event
		GROUP BY zone, panel
	)')) {
			die('There was an error running the query [' .$db->error . ']');
		} else {
	}
	return $result;
}



function arrayFromSQLResult($res){
    $myArray = array();
    while ($row = $res->fetch_array(MYSQLI_ASSOC)){
        $myArray[] = $row;
    }
    return $myArray;
}

function getPanels(){
    construct();
    $res = queryPanels();
    $panelArray = arrayFromSQLResult($res);
    $res->close();
    return $panelArray;
}
?>
