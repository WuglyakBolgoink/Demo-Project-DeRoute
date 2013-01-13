<?php
/*
	als Eingabeparameter bekommt ein Punkt (String[; lat,lng] )
	als Rückgabewert gibt JSON-Object von Google-Maps API mit geoInformation über den Punkt		
	@input: HTTP_GET-request : loc
	@return: JSON
*/
/*
	TODO: parsing hier machen und als Rüchgabe ein Array ["lat"=>lat,"lng"=>lng] zurück geben 
*/
	$output = array();
	$loc=$_GET["loc"];//$loc = "munich";
	$url = "http://maps.google.com/maps/api/geocode/json?address=".$loc."&sensor=false";
	$output = file_get_contents($url);
	echo $output;
?>