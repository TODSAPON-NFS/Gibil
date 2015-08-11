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

function getPanels(){
    global $connection;
    if(!$result = $connection->query("Select * FROM Event")) {
			die('There was an error running the query [' .$db->error . ']');
		} else {
	}
		return $result;
}

construct()
$res = getPanels()
$schema = array('category','zone','panel','timestamp','status')
while($row = $result->fetch_assoc()){
    echo ":"
    for($i=0;$i<count($schema);$i++)
    {
        echo $row[$schema[$i]]."-";
    }
    echo ":"
}
?>
