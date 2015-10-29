<html>
<head>
    <link href='styles/resets.css' rel='stylesheet' type='text/css' >
    <link href='styles/styles.css' rel='stylesheet' type='text/css' >
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'> <!-- Add this to the head of your html before the styles-->

    <button onclick="blueTheme()">Blue</button>
    <button onclick="blackTheme()">Black</button>
    <button onclick="whiteTheme()">White</button>
</head>

 <body onload="updateListener()" id="body"> 
    <div class="wrapper">  <!--add this after the body tag but still so it sorounds all the content on the page -->

<?php
//error reporting
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);


//buildContainer constructs a panel container based on the category ( status id ) of the panel, its zone (4000 ... 5000 etc), it's timestamp and written status 
function buildContainer($category, $zone, $panel, $date, $status) {
        echo "<div class=\"eventBox\"id=",$zone+$panel,"-box>\n";
        echo "<div class=\"content\" id=",$zone+$panel,">\n";
        //status box
        echo "<div class=idBox id=",$zone+$panel,"-idBox >",$zone+$panel,"</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxA >A</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxT >T</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxS >S</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxP >P</div>";
        echo "<div class=label id=",$zone+$panel,"-status> status: ",$status, "</div>";
        echo "<div class=label id=",$zone+$panel,"-date> time: ",$date, "</div>";
        echo "</div>\n";
        echo "</div>\n";
}

function initalizePanels() {
    include 'sqlFunctions.php';
    $panels = getPanels();
    $zone = "";
    for ($i=0; $i<count($panels); $i++){
        if ($panels[$i]["zone"] != $zone) {
            if( $zone != ""){
                echo "</div>"; //zone div
            }
            $zone = $panels[$i]["zone"];
            echo "<br><h1>",$zone,"</h1><br>";
            echo "<div class=\"",$panels[$i]["zone"]," clearfix\">";
        }
        buildContainer($panels[$i]["category"],$panels[$i]["zone"],$panels[$i]["panel"],$panels[$i]["timestamp"],$panels[$i]["status"]);
    }
    echo "</div>"; //final zone div closure
}
?>


	<div class="tabs">

		<ul class="tab-links">
			<li class="active"><a href="#tabrecent">Recent</a></li>

			<li><a href="#taboverview">Overview</a></li>
		</ul>

		<div class="tab-content">
			
			<div id="tabrecent" class="tab active">

			   <div class="acordian" id="acordian recent">
			   <br><h1> < 24 </h1><br>
				   <div class = "recent clearfix" id="new"></div> <!-- clarfix -->
			   <br><h1> > 24 </h1><br>
				   <div class = "recent clearfix" id="old"></div> <!-- clarfix -->
			   </div> <!-- acordian -->
			</div> <!-- tabrecent -->
			
			<div id="taboverview" class="tab">
			   <div class="acordian" id="acordian overview">
				<?php
					initalizePanels();
				?>
			
			   </div> <!-- acordian div -->
			</div> <!-- overview tab div -->
		</div> <!-- tabcontent div-->
	</div> <!-- tab container div-->

        <div class="clear"></div>
    </div> <!-- wrapper-->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="scripts/rpc.js"></script>
<script type="text/javascript" src="scripts/ui.js"></script>
</body>


</html>


