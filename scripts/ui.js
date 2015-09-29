//Javascript User interface functions
function update(panel, zone, category, date, stat) {
    var panelID = parseInt(zone,10) + parseInt(panel,10)
    document.getElementById( panelID + "-date").innerHTML = "time: "+ date;
    document.getElementById( panelID + "-status").innerHTML = "status: "+ stat;
    
    document.getElementById( panelID + "-statusboxA").style.backgroundColor = '#BCF1AB';
    document.getElementById( panelID + "-statusboxA").innerHTML = 'A';
    document.getElementById( panelID + "-statusboxT").style.backgroundColor = '#BCF1AB';
    document.getElementById( panelID + "-statusboxT").innerHTML = 'T';
    document.getElementById( panelID + "-statusboxS").style.backgroundColor = '#BCF1AB';
    document.getElementById( panelID + "-statusboxS").innerHTML = 'S';
    document.getElementById( panelID + "-statusboxP").style.backgroundColor = '#BCF1AB';
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

/*
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
*/
