<html>
<head>
    <link href='styles/resets.css' rel='stylesheet' type='text/css' >
    <link href='styles/styles.css' rel='stylesheet' type='text/css' >
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'> <!-- Add this to the head of your html before the styles-->

	<h2>Grayout Level</h2>
	<div id='graySlideContainer' width='700' style="padding-bottom:50px">
		<input type='range' min='0' max='100' value='50' id='graySlider' style='float:left'></input>
		<div id='grayVal' style='float:left'></div>
	</div>
	<h2>Darkness Level</h2>
	<div id='darkSlideContainer' width='700' style="padding-bottom:50px">
		<input type='range' min='0' max='100' value='10' id='darkSlider' style='float:left'></input>
		<div id='darkVal' style='float:left'></div>
	</div>
</head>

 <body onload="updateListener()" id="body"> 
    <div class="wrapper">  <!--add this after the body tag but still so it sorounds all the content on the page -->

<?php
//error reporting
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);


function buildContainer($account) {
	$accountn = $account["account"];
        echo "<div class=\"eventBox\"id=",$accountn,"-box>\n";
        echo "<div class=\"content\" id=",$accountn,">\n";
        //status box
        echo "<div class=idBox id=",$accountn,"-idBox >",$accountn,"</div>";
        echo "  <div class=\"statusBox\" id=",$accountn,"-alarm >A</div>";
        echo "  <div class=\"statusBox\" id=",$accountn,"-trouble >T</div>";
        echo "  <div class=\"statusBox\" id=",$accountn,"-supervisory >S</div>";
        echo "  <div class=\"statusBox\" id=",$accountn,"-power >P</div>";
        echo "<div class=label id=",$accountn,"-status> status: ",$account["message"], "</div>";
        echo "<div class=label id=",$accountn,"-date> time: ",$account["timestamp"], "</div>";
        echo "</div>\n";
        echo "</div>\n";
}

function initalizeAccounts() {
    include 'sqlFunctions.php';
    $accounts = getAccounts();
    $prevRange = 0;
    for ($i=0; $i<count($accounts); $i++){
	$account = $accounts[$i];
	$range = floor($account["account"] / 1000) * 1000;
	//start a new div for each grouping of accounts by the thousands
        if ($range > $prevRange) {
            if( $prevRange != 0){
                echo "</div>"; //account group div
            }
            echo "<br><h1>",$range,"</h1><br>";
            echo "<div class=\"",$range," clearfix\">";
        }
        buildContainer($account);
	$prevRange = $range;
    }
    echo "</div>"; //final account div closure
}

?>


	<div class="tabs">

		<ul class="tab-links">
			<li class="active"><a href="#tabrecent">Recent</a></li>

			<li><a href="#taboverview">Overview</a></li>
		</ul>

		<div class="tab-content">
			
			<div id="tabrecent" class="tab active">
				<div id="buttonContainer">
					<button onclick="setRecentTime('min')">Min</button> 
					<button onclick="setRecentTime('hour')">Hour</button> 
					<button onclick="setRecentTime('day')">Day</button> 
					<button onclick="setRecentTime('week')">Week</button> 
				</div>

			   <div class="acordian" id="acordian recent" >
			   <h1 id="since"></h1>
				   <div class = "recent clearfix" id="new"></div> <!-- clarfix -->
			   <h1 id="after"></h1>
				   <div class = "recent clearfix" id="old"></div> <!-- clarfix -->
			   </div> <!-- acordian -->
			</div> <!-- tabrecent -->
			
			<div id="taboverview" class="tab">
			   <div class="acordian" id="acordian overview">
				<?php
					initalizeAccounts();
				?>
			
			   </div> <!-- acordian div -->
			</div> <!-- overview tab div -->
		</div> <!-- tabcontent div-->
	</div> <!-- tab container div-->

        <div class="clear"></div>
    </div> <!-- wrapper-->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="scripts/rpc.js"></script>
<script type="text/javascript" src="scripts/interact.js"></script>
<script type="text/javascript" src="scripts/ui.js"></script>
<script type='text/javascript' src='scripts/tinycolor.js'></script>
</body>


</html>


