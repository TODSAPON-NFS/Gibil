
/*-----------GraySlider------------------*/
var GrayLevel = 50;
slider = document.getElementById("graySlider");


var grayDiv = document.getElementById("grayVal");
grayDiv.innerHTML = GrayLevel;

//function is called when slider value changes
slider.addEventListener("change", function() { 
  GrayLevel = slider.value;  
  grayDiv.innerHTML = GrayLevel;
})


//if you want it real-time, you can do this: 
setInterval(function() {
  GrayLevel = slider.value;
  grayDiv.innerHTML = GrayLevel;
}, 100)
/*-----------/GraySlider------------------*/

/*-----------DarkSlider------------------*/
var DarkLevel = 10;
darkSlider = document.getElementById("darkSlider");


var darkDiv = document.getElementById("darkVal");
darkDiv.innerHTML = DarkLevel;

//function is called when darkSlider value changes
darkSlider.addEventListener("mod", function() { 
  DarkLevel = darkSlider.value;  
  darkDiv.innerHTML = DarkLevel;
})


//if you want it real-time, you can do this: 
setInterval(function() {
  DarkLevel = darkSlider.value;
  darkDiv.innerHTML = DarkLevel;
}, 100)
/*-----------/GraySlider------------------*/

//end slider TODO end delete


/*-------TIME BUTTONS---------------------*/

var MIN = 60
var HOUR = MIN * 60;
var DAY = HOUR * 24;
var WEEK = DAY * 7;

var seperatorString = ""
var timeSeparator;

setRecentTime("day"); //default

function setRecentTime(breakPoint){
	switch (breakPoint){
	case "min":
		timeSeparator = MIN;
		seperatorString = "Minute";
		break;
	case "hour":
		timeSeparator = HOUR;
		seperatorString = "Hour";
		break;
	case "day":
		seperatorString = "Day";
		timeSeparator = DAY;
		break;
	case "week":
		seperatorString = "Week";
		timeSeparator = WEEK;
		break;
	default://day default
		seperatorString = "Day";
		timeSeparator = DAY;
		break;
	}
}
