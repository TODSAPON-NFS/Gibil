<?php

$connection = NULL;
$categories = 8;
$zones = [
        0 => "north",
        1 => "south",
        2 => "east",
        3 => "west",
];
$panels = 50;
$stats = [
    0 => "alive",
    1 => "dead",
    2 => "thinking",
    3 => "sleeping",
    4 => "zombie"];

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
    global $zones;
    global $panels;
    global $stats;
    for($i = 0; $i < sizeof($zones);$i++){
        for($j=0; $j< $panels;$j++){
            insertEvent(0,$zones[$i],$j,date(DATE_RFC2822),$stats[0]);
        }
    }
}

function simulateEvents(){
    global $categories;
    global $zones;
    global $panels;
    global $stats;
    while( true ) {
        $newEvents = rand(0,5);
        for ($i =0;$i < $newEvents; $i++){
            $category = rand(0, $categories);
            $zone = $zones[ rand(0, sizeof($zones) - 1) ];
            $panel = rand(0, $panels);
            $status = $stats[rand(0, sizeof($stats) -1 )];
            updateEvent($category,$zone,$panel,date(DATE_RFC2822),$status);
        }
        sleep(1);
    }
}

function insertEvent($category, $zone, $panel, $timestamp, $status){
    global $connection;
    $stmt = $connection->prepare("INSERT INTO Event (category,zone,panel,timestamp,status) Values (?,?,?,?,?)");
    $stmt->bind_param("isiss", $category, $zone, $panel, $timestamp, $status);
    $stmt->execute();
    if($stmt->error) {
        printf("<b>Error: %s. </b>\n", $stmt->error);
        return $stmt->error;
    } else {
        return 0;
    }
}

function updateEvent($category, $zone, $panel, $timestamp, $status){
    global $connection;
    $stmt = $connection->prepare("update Event set category=?, status=?, timestamp=? where zone=? and panel=?");
    $stmt->bind_param("isssi",$category,$status,$timestamp,$zone,$panel);
    $stmt->execute();
    if($stmt->error) {
        printf("<b>Error: %s. </b>\n", $stmt->error);
        return $stmt->error;
    } else {
        return 0;
    }
}

construct();
initPanels();
simulateEvents();
?>
