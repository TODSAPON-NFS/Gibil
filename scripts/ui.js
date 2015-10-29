var GREEN = 'rgb(188, 241, 171)' //This should really change I dont really like it

//Javascript User interface functions
function update(panel, zone, category, date, stat) {
    var panelID = parseInt(zone,10) + parseInt(panel,10)
    
    //update the date and status
    document.getElementById( panelID + "-date").innerHTML = "time: "+ date;
    document.getElementById( panelID + "-status").innerHTML = "status: "+ stat;
    
    //set all elements to default values
    var aBox = document.getElementById( panelID + "-statusboxA")
    aBox.style.backgroundColor = GREEN;
    aBox.innerHTML = 'A';
    var tBox = document.getElementById( panelID + "-statusboxT")
    tBox.style.backgroundColor = GREEN;
    tBox.innerHTML = 'T';
    var sBox = document.getElementById( panelID + "-statusboxS")
    sBox.style.backgroundColor = GREEN;
    sBox.innerHTML = 'S';
    var pBox = document.getElementById( panelID + "-statusboxP")
    pBox.style.backgroundColor = GREEN;
    pBox.innerHTML = 'P';

    //multiplex based on category ( alarm status ) and set colors and text accordingly
    if ( category == 0 ) {
        aBox.style.backgroundColor = '#FA0012';
        aBox.innerHTML = 'A1';
    } else if ( category == 1 ) {
        aBox.style.backgroundColor = '#FA0012';
        aBox.innerHTML = 'A2';
    } else if ( category == 2 ) {
        aBox.style.backgroundColor = '#EFB77A';
        aBox.innerHTML = 'A1';
    } else if ( category == 3 ) {
        aBox.style.backgroundColor = '#EFB77A';
        aBox.innerHTML = 'A2';
    } else if ( category == 4 ) {
        tBox.style.backgroundColor = '#EFB77A';
        tBox.innerHTML = 'T';
    } else if ( category == 5 ) {
        sBox.style.backgroundColor = '#08D3E1';
    } else if ( category == 6 ) {
        pBox.style.backgroundColor = '#FFFF88';
        pBox.innerHTML = 'F';
    } else if ( category == 7 ) {
        tBox.style.backgroundColor = '#FFFF88';
        tBox.innerHTML = 'T';
    }
}


//update recent updates the DIV acordian recent with sections based on recent events.
//Precondition : The panes passed to update recent are in sorted order
function updateRecent(panels) {
	var newEvents = document.getElementById("new");
	var oldEvents = document.getElementById("old");
	for (i=panels.length -1 ;i>=0;i--) {
    		var panelID = parseInt(panels[i]["zone"],10) + parseInt(panels[i]["panel"],10)
		var original = document.getElementById(panelID + "-box");
		
		//clone the panel being examined
		var clone = original.cloneNode(true);
		clone.id = original.id + "-recent";
		
		//collect status
		var alarmStatus = document.getElementById(panelID + "-statusboxA").style.backgroundColor;
		var tamperStatus = document.getElementById(panelID + "-statusboxT").style.backgroundColor;
		var supervisorStatus = document.getElementById(panelID + "-statusboxS").style.backgroundColor;
		var powerStatus = document.getElementById(panelID + "-statusboxP").style.backgroundColor;
			
		
		//discriminate catagory based on age of the update
		var eventTime = new Date(panels[i]["timestamp"]);
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

