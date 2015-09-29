<html>
<head>
    <link href='styles/resets.css' rel='stylesheet' type='text/css' >
    <link href='styles/styles.css' rel='stylesheet' type='text/css' >
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'> <!-- Add this to the head of your html before the styles-->
</head>

 <body onload="updateListener()"> 
    <div class="wrapper">  <!--add this after the body tag but still so it sorounds all the content on the page -->

<?php

function buildContainer($category, $zone, $panel, $date, $status) {
        echo "<div class=\"eventBox\"id=",$zone+$panel,"-box>\n";
        echo "<div class=\"content\" id=",$zone+$panel,">\n";
        //status box
        echo "<div class=idBox id=",$zone+$panel,"-idBox style=\"background-color: #BCF1AB\">",$zone+$panel,"</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxA style=\"background-color: #BCF1AB;\">A</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxT style=\"background-color: #BCF1AB;\">T</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxS style=\"background-color: #BCF1AB;\">S</div>";
        echo "  <div class=\"statusBox\" id=",$zone+$panel,"-statusboxP style=\"background-color: #BCF1AB;\">P</div>";
        echo "<div class=label id=",$zone+$panel,"-status> status: ",$status, "</div>";
        echo "<div class=label id=",$zone+$panel,"-date> time: ",$date, "</div>";
        echo "</div>\n";
        echo "</div>\n";
}
    include 'sqlFunctions.php';
    
    $panels = getPanels();
// initalize the top tne panel display
    

	// initalize the main viewing platfom
    $zone = "";
    echo "<div class=\"acordian\" id=\"acordian\">";
    echo "<h2>Recent</h2>";
    echo "<div class = \"recent clearfix\" id=\"recent\">";
    echo "</div>";
    for ($i=0; $i<count($panels); $i++){
        if ($panels[$i]["zone"] != $zone) {
            if( $zone != ""){
                echo "</div>"; //zone div
            }
            $zone = $panels[$i]["zone"];
            echo "<h2>",$zone,"</h2>";
            echo "<div class=\"",$panels[$i]["zone"]," clearfix\">";
        }
        buildContainer($panels[$i]["category"],$panels[$i]["zone"],$panels[$i]["panel"],$panels[$i]["timestamp"],$panels[$i]["status"]);
    }
    echo "</div>"; //zone div
    echo "</div>"; //acordian div


?>


        <div class="clear"></div>
    </div> <!-- wrapper-->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="scripts/rpc.js"></script>
<script type="text/javascript" src="scripts/ui.js"></script>
</body>


</html>


