//Javascript for preforming remote procedure calls

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
function updateError(errorText) {
    alert("Update Error!" + errorText)
}
// handles the response, adds the html
function updatePanels(responseText) {
    var panels = JSON.parse(responseText);
    //sort panels by date for most recent events
    panels.sort(function(a,b){
	return new Date(b["timestamp"]) - new Date (a["timestamp"])
    });
    for (i =0;i< panels.length;i++){
        update(panels[i]["panel"],panels[i]["zone"],panels[i]["category"],panels[i]["timestamp"],panels[i]["status"]);
    }
    updateRecent(panels)	
    //alert(panels[0]["panel"] + panels[0]["timestamp"] + panels[1]["panel"] + panels[1]["timestamp"])

    //alert(panels[0]["panel"] + panels[0]["timestamp"] + panels[1]["panel"] + panels[1]["timestamp"])
    
        

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
