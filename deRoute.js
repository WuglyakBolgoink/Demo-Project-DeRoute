var geocoder; // fuer Koordinaten
		
var directionDisplay;
var directionsService = new google.maps.DirectionsService();
		
var map;  // angezeigte karte beim Start
var KML_Layer;
var KML_Layer_BING;
var prev_KML_Layer;
var status=false;
var cor_lng;
var cor_lat;
	
var default_loc="munich";
var def_punktA=default_loc;
var def_punktB="dachau";	
var nav = [];
var markPoint;

var polylineOptionsActual = new google.maps.Polyline({
	strokecolor: '#e3e3e3',
    strokeopacity: 0.3,
    strokeweight: 1
});

/*
	Mün: Lat:48.1366069   Lng:11.5770851
    Ber: Lat:52.519171    Lng:13.4060912
*/

	 
function initialize() {
 
	var request = $.ajax({
		async: false,
		url: "getJSONkor.php",
  		type: "GET",
		data: {loc : default_loc},
  		dataType: "json"
	});
	
	request.done(function(msg) {
		//console.log(msg);
  		cor_lat=msg.results[0].geometry.location.lat;
  		cor_lng=msg.results[0].geometry.location.lng;        	
	});

	request.fail(function(jqXHR, textStatus) {
  		alert( "Request failed: " + textStatus );
  		return;
	});
		
	directionsDisplay = new google.maps.DirectionsRenderer();
	
	geocoder = new google.maps.Geocoder();
		//variable "munich" - start Position mit koordinaten ( latitude und longitude)
		//console.log("variable2 "+default_loc+" - start Position mit koordinaten ( latitude("+cor_lat+") und longitude("+cor_lng+"))");
    var munich = new google.maps.LatLng(cor_lat, cor_lng);
    var mapOptions = {
    	zoom:11,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: munich
    };
    
    map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
    
    directionsDisplay.setMap(map);    
    directionsDisplay.setPanel(document.getElementById("map_description"));
} // end initialize()
      
      
      
      
      
$(document).ready(function() {

	
	 
	 //initialise a map
      initialize();

   
        
	 
	 
	 
	 $("#but_calc_route").click(function(){
	 if (map) {
	 	delete(map);
		initialize();
	}
	 	//tests
	 	var pA=$("#start").val();
	 	var pB=$("#end").val();
	 	//console.log(pA+"-"+pB);
	 	//test1: falls Punkt A oder B leer ist
	 	if ((pA=="")||(pB=="")){
	 		alert("Fehler!!! PunktA oder PunktB wurde nicht eingegeben!");
	 		return;
	 	}
		//------------------------------------------------------------------------------------
		//test2: getKor für PunktA
		var pA_lng;
		var pA_lat;
	 	
	 	var request = $.ajax({
			async: false,
			url: "getJSONkor.php",
  			type: "GET",
			data: {loc : pA},
	  		dataType: "json"
		});
	
		request.done(function(msg) {
			//console.log(msg);
			if (msg.status=="OK"){
		  		pA_lat=msg.results[0].geometry.location.lat;
  				pA_lng=msg.results[0].geometry.location.lng;
  			}else {
  				alert("Koordinaten für PunktA '"+pA+"' wurden nicht gefungen! Bitte geben Sie andere Adresse!");
				return;
  			}        	
		});

		request.fail(function(jqXHR, textStatus) {
  			alert( "Request failed: " + textStatus );
  			return;
		});
		
		//------------------------------------------------------------------------------------
	 	//test2: getKor für PunktB
		var pB_lng;
		var pB_lat;		
	 	
	 	var request = $.ajax({
			async: false,
			url: "getJSONkor.php",
  			type: "GET",
			data: {loc : pB},
	  		dataType: "json"
		});
	
		request.done(function(msg) {
			//console.log(msg);
			if (msg.status=="OK"){
		  		pB_lat=msg.results[0].geometry.location.lat;
  				pB_lng=msg.results[0].geometry.location.lng;
  			}else {
  				alert("Koordinaten für PunktB '"+pB+"' wurden nicht gefungen! Bitte geben Sie andere Adresse!");
				return;
  			}        	
		});

		request.fail(function(jqXHR, textStatus) {
  			alert( "Request failed: " + textStatus );
  			return;
		});
		
		//------------------------------------------------------------------------------------	 	
	 	//test3: Route_Anbieter
	 	var stat_g=$("#google").is(':checked');
	 	var stat_b=$("#bing").is(':checked');	 	
	 	var stat_o=$("#yours").is(':checked');
 		
 		//falls eine von Route-Anbieter gewählt wurde
 		if(stat_g || stat_b || stat_o){
 			var selectedMode=$("#mode").val();
//------- 			
 			//falls GOOGLE 
 			if (stat_g){
 			//http://maps.googleapis.com/maps/api/directions/json?origin=munich&destination=dachau&sensor=false
 			
 						// vorbereiten REQUEST
        var google_request = {
	        origin:pA,
            destination:pB,
			// Note that Javascript allows us to access the constant
            // using square brackets and a string value as its
            // "property."
            travelMode: google.maps.TravelMode[selectedMode],
			/*
					UnitSystem.METRIC specifies usage of the metric system. Distances are shown using kilometers.
					UnitSystem.IMPERIAL specifies usage of the Imperial (English) system. Distances are shown using miles.
			*/
            unitSystem: google.maps.UnitSystem.METRIC
        };
		
		// Antwortbearbeitung
        directionsService.route(google_request, function(response, status) {
          if (status == google.maps.DirectionsStatus.OK) {
           directionsDisplay.setDirections(response);
           
            directionsDisplayActual = new google.maps.DirectionsRenderer({suppressMarkers: true, polylineOptions: polylineOptionsActual});
            //directionsDisplayActual = new google.maps.DirectionsRenderer({polylineOptions: polylineOptionsActual});
            directionsDisplayActual.setMap(map);
            directionsDisplayActual.setDirections(response);
           
			//console.log(response.routes[0]);
			//console.log(response.routes[0].legs[0].distance.text);
			//console.log(response.routes[0].legs[0].duration.text);
			$("#sG").html("Distance:"+response.routes[0].legs[0].distance.text+"   gesamte Zeit:"+response.routes[0].legs[0].duration.text);
			
          }
		  else {
				var errText='';
				switch (status) {
				  case "ZERO_RESULTS":
					errText="indicates that the geocode was successful but returned no results. This may occur if the geocode was passed a non-existent address or a latng in a remote location.";
					break
				  case "OVER_QUERY_LIMIT":
					errText="indicates that you are over your quota.";
					break
				  case "REQUEST_DENIED":
					errText="indicates that your request was denied for some reason. ";
					break
					case "INVALID_REQUEST":
					errText="generally indicates that the query (address or latLng) is missing.";
					break
				  default:
					errText="unknown status code!";
					break
				}
				alert("Geocode was not successful for the following reason: " + status+" "+errText);
			}
        });		
 			}//end google
//-------- 		
//falls bing
 			if (stat_b){
 		
 			
 			var global_time_BING=0;
 			var global_length_BING=0;
 				        $.ajax({
			async: false,
			dataType: "text",
			url: "getroute.php",
			data: { 
				from: pA,
				to: pB,
				map: "bing"
			},
			success: function(data){
				var geoXml = new geoXML3.parser({map: map, afterParse:function(doc) {
				  // Geodata handling goes here, using JSON properties of the doc object
			      for (var i = 0; i < doc[0].placemarks.length; i++) {

			//console.log(doc[0].placemarks[i]);
			doc[0].placemarks[i].marker.setMap(null);
    			  }

				}});
				   geoXml.parse("http://cyberkatze.net46.net/sweng/DeRoute/bing.kml");
		       // KML_Layer_BING = new google.maps.KmlLayer("http://cyberkatze.net46.net/sweng/DeRoute/bing.kml");
				//	KML_Layer_BING.setMap(map);
				
				 			
 			$.get("http://cyberkatze.net46.net/sweng/DeRoute/bing.kml", function(data){

            html = "<ul class='bing_descr'>";

            //loop through placemarks tags
            $(data).find("Folder").find("Placemark").each(function(index, value){
            		//console.log($(this));
                //get coordinates and place name
                coords = $(this).find("coordinates").text();
                place = $(this).find("name").text();
                descr = $(this).find("description").text();
                
                global_time_BING += parseFloat($(this).find("time").text().replace(",", "."));
                global_length_BING += parseFloat($(this).find("dist").text().replace(",", "."));
                
                //store as JSON
                c = coords.split(",");
                nav.push({
                    "place": place,
                    "lat": c[0],
                    "lng": c[1],
                    "descr": descr
                });
                //output as a navigation
                html += "<li>"+(index+1)+". "+ place + "</li>";
            })
             html += "</ul>";
            //output as a navigation
            $("#tabs-2").html(html);

$("#sB").html("Distance:"+global_length_BING.toFixed(2)+"km   gesamte Zeit:"+global_time_BING.toFixed(0)+"Min");
			

            //bind clicks on your navigation to scroll to a placemark
            $("#tabs-2 li").bind("click", function(){

                panToPoint = new google.maps.LatLng(nav[$(this).index()].lng, nav[$(this).index()].lat)

                map.panTo(panToPoint);
                
                
                // generate Info-Window
                var infowindow = new google.maps.InfoWindow({
    				content: nav[$(this).index()].descr
				});

//delete marker				
if (markPoint) {
	markPoint.setMap(null);
}

//generate marker
 markPoint = new google.maps.Marker({
    position: panToPoint,
    map: map,
    title: nav[$(this).index()].place
});


//show InfoWindow for Marker
  infowindow.open(map,markPoint);
     
            })

        });
        
 			
				
			}
			});
		
 			
 			}//end bing
 			
 			//falls yours
 			if (stat_o){
 			
		     	var KML_Layer = new google.maps.KmlLayer('http://www.yournavigation.org/api/1.0/gosmore.php?format=kml&flat='+pA_lat+'&flon='+pA_lng+'&tlat='+pB_lat+'&tlon='+pB_lng+'&v=motorcar&fast=1&layer=mapnik&instructions=1');
				KML_Layer.setMap(map);
 			}
 		
 		
 		
 		}//sonst Fehler Meldung!
 		else{
 			alert("Fehler!!! Kein Route System wurde gewählt!");
 			return;
 		}
	 	
	 	
	 });//end click();
	  
	 

});//end jQuery.document.ready()