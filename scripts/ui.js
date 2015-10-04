var GREEN = 'rgb(188, 241, 171)' //This should really change I dont really like it

//Javascript User interface functions
function update(panel, zone, category, date, stat) {
    var panelID = parseInt(zone,10) + parseInt(panel,10)
    document.getElementById( panelID + "-date").innerHTML = "time: "+ date;
    document.getElementById( panelID + "-status").innerHTML = "status: "+ stat;
    
    document.getElementById( panelID + "-statusboxA").style.backgroundColor = GREEN;
    document.getElementById( panelID + "-statusboxA").innerHTML = 'A';
    document.getElementById( panelID + "-statusboxT").style.backgroundColor = GREEN;
    document.getElementById( panelID + "-statusboxT").innerHTML = 'T';
    document.getElementById( panelID + "-statusboxS").style.backgroundColor = GREEN;
    document.getElementById( panelID + "-statusboxS").innerHTML = 'S';
    document.getElementById( panelID + "-statusboxP").style.backgroundColor = GREEN;
    document.getElementById( panelID + "-statusboxP").innerHTML = 'P';

    if ( category == 0 ) {
        document.getElementById( panelID + "-statusboxA").style.backgroundColor = '#FA0012';
        document.getElementById( panelID + "-statusboxA").innerHTML = 'A1';
    } else if ( category == 1 ) {
        document.getElementById( panelID + "-statusboxA").style.backgroundColor = '#FA0012';
        document.getElementById( panelID + "-statusboxA").innerHTML = 'A2';
    } else if ( category == 2 ) {
        document.getElementById( panelID + "-statusboxA").style.backgroundColor = '#EFB77A';
        document.getElementById( panelID + "-statusboxA").innerHTML = 'A1';
    } else if ( category == 3 ) {
        document.getElementById( panelID + "-statusboxA").style.backgroundColor = '#EFB77A';
        document.getElementById( panelID + "-statusboxA").innerHTML = 'A2';
    } else if ( category == 4 ) {
        document.getElementById( panelID + "-statusboxT").style.backgroundColor = '#EFB77A';
        document.getElementById( panelID + "-statusboxT").innerHTML = 'T';
    } else if ( category == 5 ) {
        document.getElementById( panelID + "-statusboxS").style.backgroundColor = '#08D3E1';
    } else if ( category == 6 ) {
        document.getElementById( panelID + "-statusboxP").style.backgroundColor = '#FFFF88';
        document.getElementById( panelID + "-statusboxP").innerHTML = 'F';
    } else if ( category == 7 ) {
        document.getElementById( panelID + "-statusboxT").style.backgroundColor = '#FFFF88';
        document.getElementById( panelID + "-statusboxT").innerHTML = 'T';
    }
}

function updateRecent(panels) {
	var recent = document.getElementById("recent");
	while(recent.hasChildNodes()) {
		recent.removeChild(recent.lastChild);
	}
	for (i=0;i< 50;i++) {
    		var panelID = parseInt(panels[i]["zone"],10) + parseInt(panels[i]["panel"],10)
		var original = document.getElementById(panelID + "-box");
		
		//collect status
		var alarmStatus = document.getElementById(panelID + "-statusboxA").style.backgroundColor
		var tamperStatus = document.getElementById(panelID + "-statusboxT").style.backgroundColor
		var supervisorStatus = document.getElementById(panelID + "-statusboxS").style.backgroundColor
		var powerStatus = document.getElementById(panelID + "-statusboxP").style.backgroundColor
		if ( alarmStatus != GREEN || tamperStatus != GREEN || supervisorStatus != GREEN || powerStatus != GREEN) {
			//alert( alarmStatus + " " + tamperStatus + " " + supervisorStatus + " " + powerStatus + GREEN)
			var clone = original.cloneNode(true);
			clone.id = original.id + "-recent";
			recent.appendChild(clone);
		}
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
