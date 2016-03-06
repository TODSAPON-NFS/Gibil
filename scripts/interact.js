/*-------TIME BUTTONS---------------------*/

var MIN = 60
var HOUR = MIN * 60;
var DAY = HOUR * 24;
var WEEK = DAY * 7;

var seperatorString = "";
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
	//update the display based on local panels
	if ( localPanels != null){
		updateRecent(localPanels);
	}
}
