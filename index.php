<html>
<head>

    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'> <!-- Add this to the head of your html before the styles-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
</head>

 <body onload="updateListener()"> 
    <div class="wrapper">  <!--add this after the body tag but still so it sorounds all the content on the page -->


<frameset cols="15%", *
<h2></h2>


<style>
	body {
	    font-family: 'Ubuntu', sans-serif;
	    padding: 30px;
	    background-color: #3C3C3C;
	}

	.wrapper {
	    background: #ebebeb;
	    border-radius: 5px;
	    padding: 10px;
	}

	h1 {
	    margin-top: 0;
	}

     .eventBox {
	    width: 200px;
        height: 22px;
	    float: left;
	    padding: 2px;
	    margin: 3px auto;
        position: relative;
      }

	 .eventBox .content {
		background: #FFF;
	    border: 1px solid #BDBDBD;
	    border-radius: 5px;
	    box-shadow: 0px 0px 4px 0px #B7B7B7;
	    margin: 3px auto;
	    padding: 2px;
	    width: 200px;
        height: 22px;
        overflow: hidden;
        position: absolute;
	}

	.statusBox {
	    width: 30px;
	    height: 15px;
        position: relative;
        float :left;
	    border-radius: 5px;
	    margin-bottom: 15px;
	    border: 1px solid #E2E2E2;
    }
    
    .idBox {
	    width: 70px;
	    height: 15px;
        position: relative;
        float :left;
	    border-radius: 5px;
	    margin-bottom: 15px;
        border: 1px solid #E2E2E2;
}

	.clear {
	    clear: both;
	}
</style>

<?php

function buildContainer($category, $zone, $panel, $date, $status) {
        echo "<div class=\"eventBox\"id=",$zone,"-",$panel,"-box>\n";
        echo "<div class=\"content\" id=",$zone,"-",$panel,">\n";
        //status box
        echo "<div class=idBox id=",$zone,"-",$panel,"-idBox style=\"background-color: #BCF1AB\">",$zone,"-",$panel,"</div>";
        echo "  <div class=\"statusBox\" id=",$zone,"-",$panel,"-statusboxA style=\"background-color: #BCF1AB;\">A</div>";
        echo "  <div class=\"statusBox\" id=",$zone,"-",$panel,"-statusboxT style=\"background-color: #BCF1AB;\">T</div>";
        echo "  <div class=\"statusBox\" id=",$zone,"-",$panel,"-statusboxS style=\"background-color: #BCF1AB;\">S</div>";
        echo "  <div class=\"statusBox\" id=",$zone,"-",$panel,"-statusboxP style=\"background-color: #BCF1AB;\">P</div>";
        echo "<div class=label id=",$zone,"-",$panel,"-status> status: ",$status, "</div>";
        echo "<div class=label id=",$zone,"-",$panel,"-date> time: ",$date, "</div>";
        echo "</div>\n";
        echo "</div>\n";
}
    include 'sqlFunctions.php';
    
    $panels = getPanels();
    $zone = "";
    for ($i=0; $i<count($panels); $i++){
        /*if ($panels[$i]["zone"] != $zone) {
            $zone = $panels[$i]["zone"];
            echo "<HR width=\"75%\">";
            echo "<h2>",$zone,"</h2>";
            echo "<HR width=\"75%\">";
        }*/
        buildContainer($panels[$i]["category"],$panels[$i]["zone"],$panels[$i]["panel"],$panels[$i]["timestamp"],$panels[$i]["status"]);
    }

?>


        <div class="clear"></div>

        <script>
        function update(panel, zone, category, date, stat) {
            document.getElementById( zone + "-" + panel + "-date").innerHTML = "time: "+ date;
            document.getElementById( zone + "-" + panel + "-status").innerHTML = "status: "+ stat;
            
            document.getElementById( zone + "-" + panel + "-statusboxA").style.backgroundColor = '#BCF1AB';
            document.getElementById( zone + "-" + panel + "-statusboxA").innerHTML = 'A';
            document.getElementById( zone + "-" + panel + "-statusboxT").style.backgroundColor = '#BCF1AB';
            document.getElementById( zone + "-" + panel + "-statusboxT").innerHTML = 'T';
            document.getElementById( zone + "-" + panel + "-statusboxS").style.backgroundColor = '#BCF1AB';
            document.getElementById( zone + "-" + panel + "-statusboxS").innerHTML = 'S';
            document.getElementById( zone + "-" + panel + "-statusboxP").style.backgroundColor = '#BCF1AB';
            document.getElementById( zone + "-" + panel + "-statusboxP").innerHTML = 'P';

            if ( category == 0 ) {
                document.getElementById( zone + "-" + panel + "-statusboxA").style.backgroundColor = '#FA0012';
                document.getElementById( zone + "-" + panel + "-statusboxA").innerHTML = 'A1';
            } else if ( category == 1 ) {
                document.getElementById( zone + "-" + panel + "-statusboxA").style.backgroundColor = '#FA0012';
                document.getElementById( zone + "-" + panel + "-statusboxA").innerHTML = 'A2';
            } else if ( category == 2 ) {
                document.getElementById( zone + "-" + panel + "-statusboxA").style.backgroundColor = '#EFB77A';
                document.getElementById( zone + "-" + panel + "-statusboxA").innerHTML = 'A1';
            } else if ( category == 3 ) {
                document.getElementById( zone + "-" + panel + "-statusboxA").style.backgroundColor = '#EFB77A';
                document.getElementById( zone + "-" + panel + "-statusboxA").innerHTML = 'A2';
            } else if ( category == 4 ) {
                document.getElementById( zone + "-" + panel + "-statusboxT").style.backgroundColor = '#EFB77A';
                document.getElementById( zone + "-" + panel + "-statusboxT").innerHTML = 'T';
            } else if ( category == 5 ) {
                document.getElementById( zone + "-" + panel + "-statusboxS").style.backgroundColor = '#08D3E1';
            } else if ( category == 6 ) {
                document.getElementById( zone + "-" + panel + "-statusboxP").style.backgroundColor = '#FFFF88';
                document.getElementById( zone + "-" + panel + "-statusboxP").innerHTML = 'F';
            } else if ( category == 7 ) {
                document.getElementById( zone + "-" + panel + "-statusboxT").style.backgroundColor = '#FFFF88';
                document.getElementById( zone + "-" + panel + "-statusboxT").innerHTML = 'T';
            }
        }
    

     $(document).ready(function() {

      $(".content").hover(
        //on mouseover
        function() {
          $(this).animate({
            height: '+=70' //adds 250px
            }, 'fast' //sets animation speed to slow
          );
          $(this).css({
              zIndex: 100
          });
        },
        //on mouseout
        function() {
          $(this).animate({
            height: '-=70px' //substracts 250px
            }, 'fast'
          );
          $(this).css({
              zIndex: 1
          });
        }
      );

    });
        
    function updateListener(){
            getUpdate()
            window.setTimeout(updateListener, 1000)
        }

            
        function getUpdate() {
          getRequest(
              'server.php', // URL for the PHP file
               updatePanels,  // handle successful request
               updateError    // handle error
          );
          return false;
        }  
        // handles drawing an error message
        function updateError() {
            alert("Update Error!")
        }
        // handles the response, adds the html
        function updatePanels(responseText) {
            var panels = JSON.parse(responseText);
            for (i =0;i< panels.length;i++){
                update(panels[i]["panel"],panels[i]["zone"],panels[i]["category"],panels[i]["timestamp"],panels[i]["status"]);
            }
                

        }
        // helper function for cross-browser request object
        function getRequest(url, success, error) {
            var req = false;
            try{
                // most browsers
                req = new XMLHttpRequest();
            } catch (e){
                // IE
                try{
                    req = new ActiveXObject("Msxml2.XMLHTTP");
                } catch(e) {
                    // try an older version
                    try{
                        req = new ActiveXObject("Microsoft.XMLHTTP");
                    } catch(e) {
                        return false;
                    }
                }
            }
            if (!req) return false;
            if (typeof success != 'function') success = function () {};
            if (typeof error!= 'function') error = function () {};
            req.onreadystatechange = function(){
                if(req.readyState == 4) {
                    return req.status === 200 ? 
                        success(req.responseText) : error(req.status);
                }
            }
            req.open("GET", url, true);
            req.send(null);
            return req;
        }

    
        </script>
    </div> <!-- wrapper-->
</body>


</html>


