var GREEN = 'rgba(179, 255, 204, 0)';
var AMBER = '#F39720'; //This should really change I dont really like it
var YELLOW = '#e1e37b';
var BLUE = '#62d9f4';
var RED = '#cd2737';

var DEFAULTTEXTCOLOR = '#6f6a57'


function update(panel, id) {

    //update the date and status
    document.getElementById( id + "-date").innerHTML = panel["timestamp"];
    document.getElementById( id + "-status").innerHTML = "status: "+ panel["message"];
    
    //set all elements to default values
    var idBox = document.getElementById( id + "-idBox")
    var aBox = document.getElementById( id + "-alarm")
    aBox.innerHTML = 'A';
    var tBox = document.getElementById( id + "-trouble")
    tBox.innerHTML = 'T';
    var sBox = document.getElementById( id + "-supervisory")
    sBox.innerHTML = 'S';
    var pBox = document.getElementById( id + "-power")
    pBox.innerHTML = 'P';

	idBox.style.backgroundColor = GREEN;


	//idbox // go gray if the last message was > 24h
	//alarm
	switch (panel["alarmzone"]){
	case "1":
		switch (panel["alarmstate"]){
		case "1":
        		aBox.innerHTML = 'A';
        		aBox.style.color = DEFAULTTEXTCOLOR;
        		aBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		aBox.innerHTML = 'A';
        		aBox.style.color = tinycolor(RED).darken(40).toRgbString();
        		aBox.style.backgroundColor = RED;
			break;
		}
		break;
	case "A":
		switch (panel["alarmstate"]){
		case "1":
        		aBox.innerHTML = 'A';
        		aBox.style.color = DEFAULTTEXTCOLOR;
        		aBox.style.backgroundColor = tinycolor(GREEN).setAlpha(.2).toRgbString();
			break;
		case "2":
        		aBox.innerHTML = 'A';
        		aBox.style.color = tinycolor(RED).darken(40).toRgbString();
        		aBox.style.backgroundColor = tinycolor(RED).setAlpha(.2).toRgbString();
			break;
		}
		break;
	}
	//trouble
	switch (panel["troublezone"]){
	case "3":
		switch (panel["troublestate"]){
		case "1":
        		tBox.innerHTML = 'T';
        		tBox.style.color = DEFAULTTEXTCOLOR;
        		tBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		tBox.innerHTML = 'T';
        		tBox.style.color = tinycolor(YELLOW).darken(40).toRgbString();
        		tBox.style.backgroundColor = YELLOW;
			break;
		}
		break;
	case "C":
		switch (panel["troublestate"]){
		case "1":
        		tBox.innerHTML = 'T';
        		tBox.style.color = DEFAULTTEXTCOLOR;
        		tBox.style.backgroundColor = tinycolor(GREEN).setAlpha(.2).toRgbString();
			break;
		case "2":
        		tBox.innerHTML = 'T';
        		tBox.style.color = tinycolor(YELLOW).darken(40).toRgbString();
        		tBox.style.backgroundColor = tinycolor(YELLOW).setAlpha(.2).toRgbString();
			break;
		}
		break;
	}
	//supervisory
	switch (panel["supervisoryzone"]){
	case "2":
		switch (panel["supervisorystate"]){
		case "1":
        		sBox.innerHTML = 'S';
        		sBox.style.color = DEFAULTTEXTCOLOR;
        		sBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		sBox.innerHTML = 'S';
        		sBox.style.color = tinycolor(BLUE).darken(40).toRgbString();
        		sBox.style.backgroundColor = BLUE;
			break;
		}
		break;
	case "B":
		switch (panel["supervisorystate"]){
		case "1":
        		sBox.innerHTML = 'S';
        		sBox.style.color = DEFAULTTEXTCOLOR;
        		sBox.style.backgroundColor = tinycolor(GREEN).setAlpha(.2).toRgbString();
			break;
		case "2":
        		sBox.innerHTML = 'S';
        		sBox.style.color = tinycolor(BLUE).darken(40).toRgbString();
        		sBox.style.backgroundColor = tinycolor(BLUE).setAlpha(.2).toRgbString();
			break;
		}
		break;
	}
	//power
	switch (panel["powerzone"]){
	case "4":
		switch (panel["powerstate"]){
		case "1":
        		pBox.innerHTML = 'P';
        		pBox.style.color = DEFAULTTEXTCOLOR;
        		pBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		pBox.innerHTML = 'P';
        		pBox.style.color = tinycolor(AMBER).darken(40).toRgbString();
        		pBox.style.backgroundColor = AMBER;
			break;
		}
		break;
	}
}

//change id recursively  
  function changeId(nodes, n){
   for (var i=0;i<nodes.length;i=i+1){
     if (nodes[i].childNodes){
           changeId(nodes[i].childNodes,n);
     }
      nodes[i].id =  String(n) + nodes[i].id;
   }
  }

//update recent updates the DIV acordian recent with sections based on recent events.
//Precondition : The panes passed to update recent are in sorted order
function updateRecent(panels) {
	var newEvents = document.getElementById("new");
	var oldEvents = document.getElementById("old");
	for (i=panels.length -1 ;i>=0;i--) {
		var panel = panels[i];
		var original = document.getElementById(panel["account"] + "-box");
		
		//clone the panel being examined
		var clone = original.cloneNode(true);
		clone.id = "recent-" + original.id;
		//rename children
		children = clone.childNodes;
		changeId(children,"recent-");


		//var clone = $("#"+panel["account"] + "-box").clone(false).find("*[id]").andSelf().each(function() { $(this).attr("id", $(this).attr("id") + "_cloned"); });		
		
		//collect status
		var alarmStatus = document.getElementById(panel["account"] + "-alarm").style.backgroundColor;
		var tamperStatus = document.getElementById(panel["account"] + "-trouble").style.backgroundColor;
		var supervisorStatus = document.getElementById(panel["account"] + "-supervisory").style.backgroundColor;
		var powerStatus = document.getElementById(panel["account"] + "-power").style.backgroundColor;
			
		
		//discriminate catagory based on age of the update
		var eventTime = new Date(panel["timestamp"]);
		var breakPointTime = new Date();

		//TODO 15 second diff ( eventually 24h )
		breakPointTime.setSeconds(breakPointTime.getSeconds() - timeSeparator);
		//update event display
		document.getElementById("since").innerHTML = "Events in the last : " + seperatorString;
		document.getElementById("after").innerHTML = "Outstanding Since : "+ breakPointTime.toString() ;
		
		//remove panels that have been set to green
		var green = GREEN;
		if ( alarmStatus == green && tamperStatus == green && supervisorStatus == green && powerStatus == green) {
			newlyOkPanel = document.getElementById(clone.id);
			//if the panel was in the new or old list remove it
			if(newlyOkPanel != null){
				newlyOkPanel.parentNode.removeChild(newlyOkPanel);
			}

		} else {
			//most recent events, occuring before the time delimiter
			if ( eventTime > breakPointTime ) {
				oldPanel = document.getElementById(clone.id);
				if(oldPanel == null){
					//panel status has changed from green to some sort of alarm
					newEvents.insertBefore(clone,newEvents.firstChild);
				} else {
					if ( oldPanel.parentNode.id == "new") {
						//do nothing the node is in the right place
					} else {
						//it was in the old panel box
						oldEvents.removeChild(oldPanel);
						newEvents.insertBefore(clone,newEvents.firstChild);
					}
				}
						
			} else {
				oldPanel = document.getElementById(clone.id);
				if(oldPanel == null){
					//This can only happen on startup or due to error
					//Error the panel is not in the recent section but has an old timestamp ( has the server not updated in 24h?)
					oldEvents.insertBefore(clone,oldEvents.firstChild);
					//alert("Error panel alarmed for >24 hours and not reported, check server!!");
				} else {
					if ( oldPanel.parentNode.id == "new") {
						newEvents.removeChild(oldPanel);
						oldEvents.insertBefore(clone,oldEvents.firstChild);
					} else {
						//do nothing the node is in the right place
					}
				}
			}
		update(panel,"recent-" + panel["account"]);
		}
	}//end for
}//end updaterecent


$(document).ready(function() {

	$('.tabs .tab-links a').on('click', function(e) {
		var currentAttrValue = $(this).attr('href');

		//show hide tabs
		$('.tabs ' + currentAttrValue).show().siblings().hide();

		//change curent tab to active

		$(this).parent('li').addClass('active').siblings().removeClass('active');
		e.preventDefaults();
	});

});

$(document).ready(function() {

$(".content").click(
function() {
  if ( $(this).css('height') <= '22px' ) {
	  $(this).animate({
	    height: '100px' //adds 250px
	    }, 'fast' //sets animation speed to slow
	  );
	  $(this).css({
	      zIndex: 100
	  });
 }
 
else {
	  $(this).animate({
	    height: '22px' //substracts 250px
	    }, 'fast'
	  );
	  $(this).css({
	      zIndex: 1
	  });
	}
 });
	
});

