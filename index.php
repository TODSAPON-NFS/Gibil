<?php
$pass = $_POST['pass'];

if($pass == "admin")
{
        include "home.php" ;
 
}
else
{
    if(isset($_POST))
    { ?>
	<head>
	    <link href='styles/resets.css' rel='stylesheet' type='text/css' >
	    <link href='styles/styles.css' rel='stylesheet' type='text/css' >
	    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'> <!-- Add this to the head of your html before the styles-->

	</head>
	<body>
	<div id=lock class=lock align=center>
	    <img src="styles/lock.png" alt="lock" style="width:304px;height:228px;">
            <form method="POST" action="index.php">
            <input type="password" name="pass"></input><br/>
            <input type="submit" name="submit" value="Go"></input>
            </form>
	</div>
	</body>
    <?php }
}
?>
