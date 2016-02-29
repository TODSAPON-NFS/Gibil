var GREEN = '#BCF1AB'; //This should really change I dont really like it
var RED = '#FA0012'; //This should really change I dont really like it
var YELLOW = '#EFB77A';
var BLUE = '#08D3E1';
var AMBER = '#FFFF88';

var GrayLevel = 50;
//Javascript User interface functions

function update(panel) {
    
    //update the date and status
    document.getElementById( panel["account"] + "-date").innerHTML = "time: "+ panel["timestamp"];
    document.getElementById( panel["account"] + "-status").innerHTML = "status: "+ panel["message"];
    
    //set all elements to default values
    var aBox = document.getElementById( panel["account"] + "-alarm")
    aBox.innerHTML = 'A';
    var tBox = document.getElementById( panel["account"] + "-trouble")
    tBox.innerHTML = 'T';
    var sBox = document.getElementById( panel["account"] + "-supervisory")
    sBox.innerHTML = 'S';
    var pBox = document.getElementById( panel["account"] + "-power")
    pBox.innerHTML = 'P';
	//alarm
	switch (panel["alarmzone"]){
	case "1":
		switch (panel["alarmstate"]){
		case "1":
        		aBox.innerHTML = 'A';
        		aBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		aBox.innerHTML = 'A';
        		aBox.style.backgroundColor = RED;
			break;
		}
		break;
	case "A":
		switch (panel["alarmstate"]){
		case "1":
        		aBox.innerHTML = 'A';
        		aBox.style.backgroundColor = tinycolor(GREEN).desaturate(GrayLevel).toHexString();
			break;
		case "2":
        		aBox.innerHTML = 'A';
        		aBox.style.backgroundColor = tinycolor(RED).desaturate(GrayLevel).toHexString();
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
        		tBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		tBox.innerHTML = 'T';
        		tBox.style.backgroundColor = YELLOW;
			break;
		}
		break;
	case "C":
		switch (panel["troublestate"]){
		case "1":
        		tBox.innerHTML = 'T';
        		tBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		tBox.innerHTML = 'T';
        		tBox.style.backgroundColor = tinycolor(YELLOW).desaturate(GrayLevel).toHexString();
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
        		sBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		sBox.innerHTML = 'S';
        		sBox.style.backgroundColor = BLUE;
			break;
		}
		break;
	case "B":
		switch (panel["supervisorystate"]){
		case "1":
        		sBox.innerHTML = 'S';
        		sBox.style.backgroundColor = tinycolor(GREEN).desaturate(GrayLevel).toHexString();
			break;
		case "2":
        		sBox.innerHTML = 'S';
        		sBox.style.backgroundColor = tinycolor(YELLOW).desaturate(GrayLevel).toHexString();
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
        		pBox.style.backgroundColor = GREEN;
			break;
		case "2":
        		pBox.innerHTML = 'P';
        		pBox.style.backgroundColor = AMBER;
			break;
		}
		break;
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
		clone.id = original.id + "-recent";

	original.clone(false).find("*[id]").andSelf().each(function() { $(this).attr("id", $(this).attr("id") + "_cloned"); });
		
		//collect status
		var alarmStatus = document.getElementById(panel["account"] + "-alarm").style.backgroundColor;
		var tamperStatus = document.getElementById(panel["account"] + "-trouble").style.backgroundColor;
		var supervisorStatus = document.getElementById(panel["account"] + "-supervisory").style.backgroundColor;
		var powerStatus = document.getElementById(panel["account"] + "-power").style.backgroundColor;
			
		
		//discriminate catagory based on age of the update
		var eventTime = new Date(panel["timestamp"]);
		var breakPointTime = new Date();

		//TODO 15 second diff ( eventually 24h )
		breakPointTime.setSeconds(breakPointTime.getSeconds() - 15);
		
		//remove panels that have been set to green
		if ( alarmStatus == GREEN && tamperStatus == GREEN && supervisorStatus == GREEN && powerStatus == GREEN) {
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
		}
	}
}

function blueTheme() {
/*
	//LOGO missing

	//LOGO Header missing

	//Background
	var body = document.querySelector("#body");
	body.classList.add("bluebody")
	
	//wrapper or tab border color
	var wrapper = document.querySelector(".wrapper");
	wrapper.classList.add("bluewrapper")

	//tab-content-background color
	var tab = document.querySelectorAll(".tab-content");
	for(var i =0; i< tab.length;i++){
		tab[i].style.backgroundColor = "#042029";
	}

	//tab-links
	var tablinks = document.querySelectorAll(".tab-links");
	for(var i =0; i< tablinks.length;i++){
		tablinks[i].style.backgroundColor = "#042029";
		tablinks[i].style.color = "#9EA69D";
	}
	
*/

}

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

