<?php

$output = array();

$loc=$_GET["loc"];
//$loc = "munich";

$url = "http://maps.google.com/maps/api/geocode/json?address=".$loc."&sensor=false";

$output = file_get_contents($url);

//echo jsondecode($output);
echo $output;
?>