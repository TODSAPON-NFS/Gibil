//Javascript User interface functions
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
