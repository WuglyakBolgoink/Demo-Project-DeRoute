<?php
	$output = array();
	//$loc = "munich";
	$loc=$_GET["loc"];
	$url = "http://maps.google.com/maps/api/geocode/json?address=".$loc."&sensor=false";
	$output = file_get_contents($url);
	echo $output;
?>