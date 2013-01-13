<?php
//TODO: HTTP_GET prüfen, ob ein oder mehrere Eingabeparameter leer sind
//TODO: add parsing for OpenStreetMap
$output = array();

$loc=$_GET["map"];
$from=$_GET["from"];
$to=$_GET["to"];

$status=array();
$status['success']=true;
$status['error']='';

if (empty($loc)||empty($from)||empty($to)){
	$status['success']=false;
	$status['error']='Empty parameter!';
	print json_encode($status);
}
elseif ($map="bing") {
	$url="http://dev.virtualearth.net/REST/V1/Routes/Driving?o=json";
	$url.="&wp.0=".$from."&wp.1=".$to;
	$url.="&optmz=distance&rpo=Points&key=AnoITCW6guOxL5ZWxceu3R-KP2Il23xCViRGpZt34q1SZbSByAxVeauO1C__fKKv";
	
	$oJSON=GetJsonFromURL($url);
	
	if ($oJSON==NULL){
		$status['success']=false;
		$status['error']="JSON Object not found";
		print json_encode($status);
	}
	else {
		$co=$oJSON->resourceSets[0]->resources[0]->routePath->line->coordinates;	
		$points=$oJSON->resourceSets[0]->resources[0]->routeLegs[0]->itineraryItems;	
		create_KML_File($co,$points);
	}
}

function GetJsonFromURL($url){
    $response = file_get_contents($url);
    return json_decode($response);
}


/**
 * 
 * @input: $co -> polyline-Coordinaten
 * @input: $points -> Descriptions Coordinaten
 * @return: nichts, bildet KML-File in Root-directory 
 */
function create_KML_File($co,$points){
	$kml='<?xml version="1.0" encoding="UTF-8"?>
	<kml xmlns="http://earth.google.com/kml/2.0">
	  <Document>
	    <name>BING Paths kml</name>
	    <distance>0</distance>
	    <traveltime>0</traveltime>
	    <description>Examples of paths. Note that the tessellate tag is by default
	      set to 0. If you want to create tessellated lines, they must be authored
	      (or edited) directly in KML.</description>
	    <Style id="redLineGreenPoly">
	      <LineStyle>
	        <color>7f0000ff</color>
	         <width>7</width>
	      </LineStyle>
	      <PolyStyle>
	        <color>7f00ff00</color>
	      </PolyStyle>
	    </Style>    
	    <Folder>
	    	<name>Tessellated</name>
	    	<visibility>0</visibility>
	    	<description>If the "tessellate" tag has a value of 1, the line will contour to the underlying terrain</description>
	      	'.loadMarkerPoints($points).'
	   </Folder>
	      	<Placemark>
	      		<styleUrl>#redLineGreenPoly</styleUrl>
	      		<LineString>
	        		<extrude>1</extrude>
	        		<tessellate>1</tessellate>
	        		<altitudeMode>relativeToGround</altitudeMode>
	        		<coordinates>'.loadCo($co).'</coordinates>
	      		</LineString>
	      	</Placemark>
	  </Document>
	</kml>';
	
	$file = fopen("bing.kml", "w") or die ( "Fehler!!! Kann nicht File oeffnen!" );
	
	fwrite($file, $kml);
	fclose($file);
}

function loadMarkerPoints($points){
	$out="";
	foreach($points as $po){
		$dist=$po->travelDistance;
		$time=$po->travelDuration / 60;
		$name=$po->details[0]->names[0];
		$title=$po->instruction->text;
		$lat=$po->maneuverPoint->coordinates[0];
		$lng=$po->maneuverPoint->coordinates[1];

		$muster="<Placemark>
	    	<name>".$title." (".$dist."km, ".number_format($time,2)."Min)</name>
	    	<time>".number_format($time,2)."</time>
	    	<dist>".$dist."</dist>
		    <description>".$name."</description>
	    	<Point>
	      		<coordinates>".$lng.",".$lat.",0</coordinates>
			</Point>
		</Placemark>\n";
	
		$out.=$muster;
	}
	return $out;
}

function loadCo($cos){
	$out="";
	foreach($cos as $co){
		// Format: <longitude>,<latitude>,<altitude>
		// Trennung durch Leerzeichen
		$latlng="";
		foreach($co as $key=>$val){
			$latlng.=$val.',';
		}
		$latlng=explode(',',$latlng);
		// "_" <- keine Hoehe fuer Koordinates, Leerzeichen fuer naechste Koordinate
		$out.= $latlng[1].",".$latlng[0]."\n";
	}
	return trim($out);
}



?>

