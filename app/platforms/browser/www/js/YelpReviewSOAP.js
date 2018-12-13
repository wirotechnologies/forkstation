
	  function YelpRequestSOAP(YelpID,Callback) {
                var wsUrl = "http://backservices.forkstation.com/Services/Gateway.php?action=getYelpReviews&data-id="+YelpID;
				
launchSOAP(wsUrl,Callback);    
		}



        function processError(request, status, error) {
			if (window.location.href.indexOf("-es")!=-1){
            alertcreate("Error Interno",request.responseText + " " + status,"Aceptar");}
			else{
			alertcreate("Internal Error",request.responseText + " " + status,"Accept");}
			}
          

function launchSOAP(SOAPUrl,Callback){
	//$.get(SOAPUrl, "jsonp",  function(data) {
   //OKFunction(data);
//});
$.ajax({
   type: 'GET',
    url: SOAPUrl,
    contentType: "text; charset=\"utf-8\"",
    success: function(json) {
		Callback(json);
    },
    error: processError
});
}