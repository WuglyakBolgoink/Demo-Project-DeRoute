<?php

$output = array();

$loc=$_GET["map"];
$from=$_GET["from"];
$to=$_GET["to"];

//echo $loc." ".$from." ".$to;


$status=array();
$status['success']=true;
$status['error']='';

if (empty($loc)||empty($from)||empty($to)){
	$status['success']=false;
	$status['error']='Empty parameter!';
	print json_encode($status);
}
elseif ($map="bing") {
	$url="http://dev.virtualearth.net/REST/V1/Routes/Driving?o=json&wp.0=".$from."&wp.1=".$to."&optmz=distance&rpo=Points&key=AnoITCW6guOxL5ZWxceu3R-KP2Il23xCViRGpZt34q1SZbSByAxVeauO1C__fKKv";
	
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
//var_dump( $kml);
//die("kaput");
	
	$file = fopen("bing.kml", "w")or die ( "Fehler!!! Kann nicht File oeffnen!" );
	
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


/*
http://www.cicloviaslx.com/get.php?kml=T
http://www.geocodezip.com/geoxml3_test/www_cicloviaslx_comA.html


po4itat zdes
https://developers.google.com/maps/documentation/javascript/layers#KMLLayers
https://developers.google.com/maps/documentation/javascript/layers?hl=en

http://www.geocodezip.com/geoxml3_test/v3_geoxml3_multipleKML_test.html
http://stackoverflow.com/questions/7320701/google-maps-api-adding-multiple-destinations-not-working-google-directions?rq=1
http://stackoverflow.com/questions/7102750/drawing-multiple-route-on-gmap-v3-api-and-also-more-than-10-points-on-map?rq=1




peredelat eto toge dlja GoogleAPI:
http://maps.googleapis.com/maps/api/directions/json?origin=munich&destination=dachau&sensor=false

<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.2">
<Document>
  <name>Editar transportes públicos</name>
  <description><![CDATA[Por favor especifiquem na descrição de cada linha as restrições que existem relativas ao transporte de bicicleta no troço em questão.

Que linhas usar?

- Autocarros 
Cor: Verde (o penúltimo)
Largura: 4 pixels
Opacidade: 50%

- Barcos
Cor: Azul (o penúltimo)
Largura: 5 pixels
Opacidade: 50%

- Comboios
Cor: Roxo (penúltima cor)
Largura: 5 pixels
Opacidade: 50%

- Metros
Cor: Laranja (o do meio)
Largura: 5 pixels
Opacidade: 50%
_]]></description>
  <Style id="style16">
    <LineStyle>
      <color>80006600</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style3">
    <LineStyle>
      <color>80006600</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style9">
    <LineStyle>
      <color>80006600</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style6">
    <LineStyle>
      <color>80006600</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style11">
    <LineStyle>
      <color>80990000</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style12">
    <LineStyle>
      <color>80990000</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style15">
    <LineStyle>
      <color>80990000</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style13">
    <LineStyle>
      <color>80990000</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style4">
    <LineStyle>
      <color>80990000</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style5">
    <LineStyle>
      <color>80990000</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style1">
    <LineStyle>
      <color>80663366</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style7">
    <LineStyle>
      <color>80663366</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style18">
    <LineStyle>
      <color>80663366</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style2">
    <LineStyle>
      <color>80663366</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style14">
    <LineStyle>
      <color>80663366</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style8">
    <LineStyle>
      <color>80663366</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style17">
    <LineStyle>
      <color>80663366</color>
      <width>5</width>
    </LineStyle>
  </Style>
  <Style id="style10">
    <LineStyle>
      <color>80006600</color>
      <width>4</width>
    </LineStyle>
  </Style>
  <Placemark>
    <name>BikeBus 24</name>
    <Snippet>Alcântara (Cç. Tapada) - PontinhaPassa por Monsanto</Snippet>
    <description><![CDATA[<div dir="ltr">Alcântara (Cç. Tapada) - Pontinha</div>]]></description>
    <styleUrl>#style16</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.197434,38.762844,0.000000
        -9.197980,38.759140,0.000000
        -9.199870,38.758739,0.000000
        -9.203170,38.757511,0.000000
        -9.204780,38.756859,0.000000
        -9.204620,38.756290,0.000000
        -9.201330,38.756851,0.000000
        -9.197760,38.754311,0.000000
        -9.197280,38.754230,0.000000
        -9.196800,38.754421,0.000000
        -9.196130,38.753849,0.000000
        -9.197360,38.751862,0.000000
        -9.198070,38.744980,0.000000
        -9.199790,38.745049,0.000000
        -9.199730,38.744888,0.000000
        -9.197900,38.744419,0.000000
        -9.195820,38.744301,0.000000
        -9.195940,38.743698,0.000000
        -9.197470,38.743660,0.000000
        -9.199450,38.742321,0.000000
        -9.200350,38.741829,0.000000
        -9.201580,38.739761,0.000000
        -9.202250,38.738991,0.000000
        -9.202060,38.737110,0.000000
        -9.201280,38.736191,0.000000
        -9.204160,38.729000,0.000000
        -9.204500,38.727520,0.000000
        -9.203800,38.727100,0.000000
        -9.201010,38.727489,0.000000
        -9.199380,38.725979,0.000000
        -9.196840,38.726231,0.000000
        -9.194490,38.726170,0.000000
        -9.193720,38.724651,0.000000
        -9.193980,38.724400,0.000000
        -9.193860,38.724140,0.000000
        -9.193550,38.724098,0.000000
        -9.193420,38.724430,0.000000
        -9.193020,38.724541,0.000000
        -9.190300,38.724758,0.000000
        -9.187640,38.723740,0.000000
        -9.186220,38.722069,0.000000
        -9.184890,38.718620,0.000000
        -9.183090,38.715542,0.000000
        -9.182230,38.714458,0.000000
        -9.179680,38.712860,0.000000
        -9.179480,38.711891,0.000000
        -9.180320,38.710911,0.000000
        -9.180190,38.710041,0.000000
        -9.179380,38.709709,0.000000
        -9.178900,38.707001,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>BikeBus 25</name>
    <description><![CDATA[<div dir="ltr">Estação Oriente - Prior Velho</div>]]></description>
    <styleUrl>#style3</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.097830,38.767620,0.000000
        -9.097350,38.773022,0.000000
        -9.097240,38.773239,0.000000
        -9.097130,38.774799,0.000000
        -9.097010,38.774860,0.000000
        -9.096980,38.775169,0.000000
        -9.097070,38.775280,0.000000
        -9.097440,38.775299,0.000000
        -9.097560,38.775139,0.000000
        -9.099150,38.775131,0.000000
        -9.104850,38.774830,0.000000
        -9.105180,38.774750,0.000000
        -9.105670,38.774780,0.000000
        -9.105800,38.774719,0.000000
        -9.105860,38.774632,0.000000
        -9.105920,38.774399,0.000000
        -9.106280,38.774158,0.000000
        -9.106480,38.774090,0.000000
        -9.107880,38.774399,0.000000
        -9.108700,38.774658,0.000000
        -9.116930,38.779701,0.000000
        -9.119870,38.781399,0.000000
        -9.121430,38.782398,0.000000
        -9.122570,38.782970,0.000000
        -9.123170,38.783119,0.000000
        -9.124160,38.782982,0.000000
        -9.124430,38.783112,0.000000
        -9.124560,38.783291,0.000000
        -9.124700,38.783951,0.000000
        -9.124410,38.784760,0.000000
        -9.123910,38.785900,0.000000
        -9.123760,38.787682,0.000000
        -9.123890,38.788601,0.000000
        -9.125410,38.790070,0.000000
        -9.125300,38.790218,0.000000
        -9.124740,38.790310,0.000000
        -9.123420,38.790070,0.000000
        -9.121830,38.789871,0.000000
        -9.121050,38.790001,0.000000
        -9.121200,38.791142,0.000000
        -9.120798,38.792492,0.000000
        -9.122012,38.792740,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>BikeBus 708</name>
    <Snippet>Martim Moniz - Parque Nações Norte</Snippet>
    <description><![CDATA[<div dir="ltr">Martim Moniz - Parque Nações Norte</div>]]></description>
    <styleUrl>#style9</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.136340,38.715542,0.000000
        -9.136010,38.715481,0.000000
        -9.135460,38.716820,0.000000
        -9.135870,38.717030,0.000000
        -9.133470,38.741741,0.000000
        -9.133030,38.742981,0.000000
        -9.131480,38.744980,0.000000
        -9.130990,38.748569,0.000000
        -9.129510,38.762924,0.000000
        -9.128362,38.762959,0.000000
        -9.124167,38.761963,0.000000
        -9.121324,38.761169,0.000000
        -9.112690,38.758739,0.000000
        -9.111990,38.758919,0.000000
        -9.111570,38.759590,0.000000
        -9.112000,38.760792,0.000000
        -9.112600,38.761902,0.000000
        -9.114320,38.761539,0.000000
        -9.117340,38.762550,0.000000
        -9.117310,38.763031,0.000000
        -9.116630,38.763691,0.000000
        -9.116070,38.765221,0.000000
        -9.115810,38.765301,0.000000
        -9.114630,38.765030,0.000000
        -9.114380,38.765369,0.000000
        -9.113820,38.765511,0.000000
        -9.112630,38.766331,0.000000
        -9.111910,38.766220,0.000000
        -9.111010,38.765701,0.000000
        -9.110670,38.764591,0.000000
        -9.110310,38.764240,0.000000
        -9.109670,38.764221,0.000000
        -9.109510,38.764301,0.000000
        -9.110150,38.767601,0.000000
        -9.110010,38.767960,0.000000
        -9.105570,38.767750,0.000000
        -9.098840,38.767071,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>BikeBus 723</name>
    <Snippet>Desterro - AlgésNão opera aos fins-de-semana e feriados!</Snippet>
    <description><![CDATA[<div dir="ltr">Desterro - Algés</div>]]></description>
    <styleUrl>#style6</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.136490,38.720150,0.000000
        -9.137670,38.720291,0.000000
        -9.137990,38.719872,0.000000
        -9.138680,38.720070,0.000000
        -9.139390,38.721130,0.000000
        -9.139360,38.722290,0.000000
        -9.141530,38.722672,0.000000
        -9.142490,38.723320,0.000000
        -9.146130,38.727180,0.000000
        -9.148650,38.725491,0.000000
        -9.157810,38.724781,0.000000
        -9.168830,38.722721,0.000000
        -9.173280,38.722672,0.000000
        -9.179850,38.724121,0.000000
        -9.181990,38.724731,0.000000
        -9.186820,38.725441,0.000000
        -9.189910,38.725651,0.000000
        -9.190850,38.725632,0.000000
        -9.193870,38.726131,0.000000
        -9.194040,38.726360,0.000000
        -9.194310,38.726372,0.000000
        -9.194450,38.726181,0.000000
        -9.193650,38.724651,0.000000
        -9.193910,38.724400,0.000000
        -9.193960,38.724201,0.000000
        -9.193920,38.724091,0.000000
        -9.193720,38.724072,0.000000
        -9.193750,38.723492,0.000000
        -9.194200,38.723141,0.000000
        -9.196410,38.722961,0.000000
        -9.198390,38.719021,0.000000
        -9.200470,38.716530,0.000000
        -9.200940,38.715900,0.000000
        -9.201200,38.715179,0.000000
        -9.200950,38.713810,0.000000
        -9.200820,38.713760,0.000000
        -9.200810,38.713619,0.000000
        -9.200970,38.713570,0.000000
        -9.201130,38.713680,0.000000
        -9.203090,38.713379,0.000000
        -9.203240,38.713299,0.000000
        -9.202273,38.712204,0.000000
        -9.201865,38.712147,0.000000
        -9.201382,38.712246,0.000000
        -9.200932,38.712471,0.000000
        -9.199805,38.712406,0.000000
        -9.198679,38.711910,0.000000
        -9.198786,38.713779,0.000000
        -9.200814,38.713715,0.000000
        -9.200851,38.713787,0.000000
        -9.201023,38.713768,0.000000
        -9.201136,38.713684,0.000000
        -9.203110,38.713379,0.000000
        -9.203260,38.713299,0.000000
        -9.203410,38.713310,0.000000
        -9.203390,38.713470,0.000000
        -9.204200,38.714371,0.000000
        -9.205860,38.715820,0.000000
        -9.206560,38.716141,0.000000
        -9.208710,38.713840,0.000000
        -9.210550,38.712460,0.000000
        -9.211330,38.711781,0.000000
        -9.213170,38.712391,0.000000
        -9.214710,38.711441,0.000000
        -9.217030,38.702591,0.000000
        -9.216790,38.701630,0.000000
        -9.215790,38.700420,0.000000
        -9.220070,38.697041,0.000000
        -9.220450,38.696442,0.000000
        -9.221670,38.696892,0.000000
        -9.223180,38.697701,0.000000
        -9.225320,38.698391,0.000000
        -9.227430,38.698151,0.000000
        -9.227630,38.698311,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Belém-Porto Brandão-Trafaria</name>
    <Snippet>Permite o transporte grátis de bicicletas até um máximo de 15 por navio (6 no</Snippet>
    <description><![CDATA[Máximo de 15 bicicletas por navio (6 no caso de navios de classe Cacilhense).]]></description>
    <styleUrl>#style11</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.197870,38.694759,0.000000
        -9.206972,38.678116,0.000000
        -9.212980,38.679615,0.000000
        -9.228902,38.677467,0.000000
        -9.230860,38.674831,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Terreiro do Paço-Barreiro</name>
    <Snippet>Permite o transporte grátis de bicicletas até um máximo de 5 por navio, sendo</Snippet>
    <description><![CDATA[Máximo de 5 bicicletas por navio, sendo o limite reduzido para 2 por navio nos seguintes horários:<br>- Às 06h30 e às 09h30, no sentido Sul/Norte e às 17h00 e às 20h00, no sentido Norte/Sul]]></description>
    <styleUrl>#style12</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.080650,38.651920,0.000000
        -9.093804,38.655422,0.000000
        -9.104740,38.666069,0.000000
        -9.132860,38.706558,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Terreiro do Paço-Montijo</name>
    <Snippet>Permite o transporte grátis de bicicletas até um máximo de 3 por navio.Mais i</Snippet>
    <description><![CDATA[Máximo de 3 bicicletas por navio.]]></description>
    <styleUrl>#style15</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.006057,38.699734,0.000000
        -9.008117,38.696785,0.000000
        -9.051805,38.687740,0.000000
        -9.087954,38.687962,0.000000
        -9.118166,38.695732,0.000000
        -9.132607,38.706757,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Cais do Sodré-Seixal</name>
    <Snippet>Permite o transporte grátis de bicicletas até um máximo de 3 por navio.Mais i</Snippet>
    <description><![CDATA[<div dir="ltr">Máximo de 3 bicicletas por navio.</div>]]></description>
    <styleUrl>#style13</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.095460,38.647690,0.000000
        -9.100540,38.656330,0.000000
        -9.111786,38.664200,0.000000
        -9.144959,38.704670,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Cais do Sodré-Cacilhas</name>
    <Snippet>Permite o transporte grátis de bicicletas até um máximo de 3 por navio, excep</Snippet>
    <description><![CDATA[<div dir="ltr">Máximo de 3 bicicletas por navio, excepto nos seguintes horários, em que é proibído:<br>- Às 06h30 e 09h30, no sentido Sul/Norte e às 17h00 e 20h00, no sentido Norte/Sul.</div>]]></description>
    <styleUrl>#style4</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.145592,38.704536,0.000000
        -9.147191,38.688400,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Barco Troia-Setubal</name>
    <description><![CDATA[Transporte pago das 07h00 às 10h00 e das 17h00 às 21h00, grátis nos restantes horários.]]></description>
    <styleUrl>#style5</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -8.889327,38.522808,0.000000
        -8.861861,38.478607,0.000000
        -8.870788,38.473301,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Comboio CP - Linha de Sintra e Azambuja</name>
    <description><![CDATA[]]></description>
    <styleUrl>#style1</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.375470,38.802410,0.000000
        -9.359760,38.800800,0.000000
        -9.342080,38.797531,0.000000
        -9.327140,38.790642,0.000000
        -9.323110,38.784279,0.000000
        -9.305430,38.781601,0.000000
        -9.298910,38.767349,0.000000
        -9.290700,38.756626,0.000000
        -9.288683,38.752811,0.000000
        -9.285350,38.751759,0.000000
        -9.275730,38.748341,0.000000
        -9.268212,38.749599,0.000000
        -9.264920,38.754429,0.000000
        -9.260874,38.759304,0.000000
        -9.255650,38.758450,0.000000
        -9.252334,38.758770,0.000000
        -9.246626,38.762081,0.000000
        -9.236590,38.760658,0.000000
        -9.224660,38.751350,0.000000
        -9.212730,38.746868,0.000000
        -9.204490,38.744781,0.000000
        -9.200280,38.744450,0.000000
        -9.190670,38.744419,0.000000
        -9.185130,38.745090,0.000000
        -9.173110,38.739460,0.000000
        -9.170930,38.738831,0.000000
        -9.167410,38.739601,0.000000
        -9.165180,38.740669,0.000000
        -9.161230,38.740730,0.000000
        -9.156510,38.740700,0.000000
        -9.155180,38.740871,0.000000
        -9.149340,38.744419,0.000000
        -9.145010,38.745190,0.000000
        -9.131960,38.746021,0.000000
        -9.129040,38.745819,0.000000
        -9.122600,38.742271,0.000000
        -9.119810,38.739529,0.000000
        -9.118050,38.738209,0.000000
        -9.114700,38.737209,0.000000
        -9.110630,38.737862,0.000000
        -9.106080,38.741070,0.000000
        -9.103550,38.745350,0.000000
        -9.100720,38.753391,0.000000
        -9.099130,38.770451,0.000000
        -9.099860,38.780560,0.000000
        -9.101230,38.788818,0.000000
        -9.100160,38.794708,0.000000
        -9.095270,38.806011,0.000000
        -9.081060,38.839851,0.000000
        -9.074020,38.850739,0.000000
        -9.070380,38.854919,0.000000
        -9.064750,38.858631,0.000000
        -9.057720,38.867081,0.000000
        -9.051360,38.874439,0.000000
        -9.038450,38.885830,0.000000
        -9.035310,38.889000,0.000000
        -9.028660,38.894611,0.000000
        -9.021280,38.904598,0.000000
        -9.017980,38.911308,0.000000
        -9.014460,38.917858,0.000000
        -9.011920,38.922699,0.000000
        -9.010250,38.928768,0.000000
        -9.008410,38.931641,0.000000
        -9.000720,38.936749,0.000000
        -8.992660,38.945999,0.000000
        -8.984540,38.958881,0.000000
        -8.981030,38.967361,0.000000
        -8.952270,39.007519,0.000000
        -8.949740,39.018860,0.000000
        -8.939440,39.028919,0.000000
        -8.924940,39.038761,0.000000
        -8.892410,39.056622,0.000000
        -8.866140,39.068489,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Comboio CP - Linha de Cascais</name>
    <description><![CDATA[]]></description>
    <styleUrl>#style7</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.417822,38.700592,0.000000
        -9.407094,38.703373,0.000000
        -9.399240,38.703407,0.000000
        -9.394623,38.703068,0.000000
        -9.391898,38.703335,0.000000
        -9.387993,38.702194,0.000000
        -9.385933,38.701340,0.000000
        -9.383851,38.700371,0.000000
        -9.381491,38.697792,0.000000
        -9.377929,38.696651,0.000000
        -9.373251,38.695663,0.000000
        -9.368423,38.694859,0.000000
        -9.363681,38.691376,0.000000
        -9.361943,38.690540,0.000000
        -9.358209,38.690353,0.000000
        -9.349798,38.687290,0.000000
        -9.335636,38.687824,0.000000
        -9.322096,38.688347,0.000000
        -9.317890,38.688263,0.000000
        -9.312376,38.686989,0.000000
        -9.308535,38.686821,0.000000
        -9.305531,38.687408,0.000000
        -9.303449,38.689316,0.000000
        -9.302140,38.691410,0.000000
        -9.299351,38.693840,0.000000
        -9.293793,38.696785,0.000000
        -9.292656,38.697140,0.000000
        -9.286884,38.697239,0.000000
        -9.282228,38.698711,0.000000
        -9.280490,38.699165,0.000000
        -9.273108,38.698711,0.000000
        -9.267164,38.699081,0.000000
        -9.262530,38.699986,0.000000
        -9.259418,38.699699,0.000000
        -9.256521,38.700272,0.000000
        -9.249827,38.699669,0.000000
        -9.230815,38.698627,0.000000
        -9.227918,38.698025,0.000000
        -9.226009,38.697308,0.000000
        -9.220580,38.693924,0.000000
        -9.214057,38.693554,0.000000
        -9.196848,38.696335,0.000000
        -9.184273,38.697773,0.000000
        -9.174167,38.702381,0.000000
        -9.171699,38.703384,0.000000
        -9.164490,38.703533,0.000000
        -9.158524,38.705074,0.000000
        -9.156014,38.705948,0.000000
        -9.144427,38.705948,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Comboio CP - Rossio</name>
    <description><![CDATA[]]></description>
    <styleUrl>#style18</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.169720,38.739071,0.000000
        -9.169720,38.737591,0.000000
        -9.169020,38.735569,0.000000
        -9.167919,38.732445,0.000000
        -9.166975,38.730854,0.000000
        -9.143865,38.715752,0.000000
        -9.141800,38.715031,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Comboio CP - Linha de Sintra (MS Meleças)</name>
    <description><![CDATA[]]></description>
    <styleUrl>#style2</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.305263,38.781876,0.000000
        -9.312043,38.790104,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Comboio Fertagus - Areeiro-Setúbal</name>
    <description><![CDATA[]]></description>
    <styleUrl>#style14</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -8.884590,38.531078,0.000000
        -8.884090,38.532669,0.000000
        -8.882890,38.541672,0.000000
        -8.879710,38.549591,0.000000
        -8.874040,38.555828,0.000000
        -8.870870,38.561539,0.000000
        -8.877390,38.590729,0.000000
        -8.894640,38.614399,0.000000
        -8.905540,38.628761,0.000000
        -8.908550,38.630230,0.000000
        -8.926060,38.629829,0.000000
        -8.943220,38.622650,0.000000
        -8.950180,38.617760,0.000000
        -8.985370,38.603340,0.000000
        -8.997040,38.588379,0.000000
        -9.009140,38.585049,0.000000
        -9.017140,38.584591,0.000000
        -9.031810,38.580898,0.000000
        -9.038920,38.580021,0.000000
        -9.057720,38.588299,0.000000
        -9.072220,38.595581,0.000000
        -9.088660,38.602558,0.000000
        -9.102430,38.610298,0.000000
        -9.113080,38.612720,0.000000
        -9.137670,38.626968,0.000000
        -9.155260,38.638939,0.000000
        -9.160930,38.643059,0.000000
        -9.162990,38.646309,0.000000
        -9.174700,38.650162,0.000000
        -9.178690,38.654190,0.000000
        -9.184190,38.657909,0.000000
        -9.186080,38.659351,0.000000
        -9.185430,38.663071,0.000000
        -9.182470,38.665482,0.000000
        -9.177920,38.666950,0.000000
        -9.174750,38.669128,0.000000
        -9.173800,38.671951,0.000000
        -9.174190,38.674019,0.000000
        -9.179940,38.698910,0.000000
        -9.179590,38.703232,0.000000
        -9.178090,38.708462,0.000000
        -9.176760,38.710541,0.000000
        -9.176880,38.713020,0.000000
        -9.176820,38.715279,0.000000
        -9.175120,38.717892,0.000000
        -9.174570,38.718609,0.000000
        -9.173670,38.720112,0.000000
        -9.173650,38.722271,0.000000
        -9.169910,38.730270,0.000000
        -9.168456,38.732178,0.000000
        -9.168241,38.733379,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Comboio CP - Alcântara</name>
    <description><![CDATA[]]></description>
    <styleUrl>#style8</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.173410,38.706959,0.000000
        -9.172940,38.709019,0.000000
        -9.174056,38.716843,0.000000
        -9.173584,38.718750,0.000000
        -9.173627,38.720139,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>Comboio CP - Linha Sado</name>
    <description><![CDATA[]]></description>
    <styleUrl>#style17</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -8.838508,38.518097,0.000000
        -8.842885,38.513561,0.000000
        -8.858421,38.512691,0.000000
        -8.886187,38.524914,0.000000
        -8.884084,38.531326,0.000000
        -8.882282,38.541531,0.000000
        -8.878849,38.549252,0.000000
        -8.872411,38.556129,0.000000
        -8.869665,38.561195,0.000000
        -8.872283,38.571869,0.000000
        -8.875766,38.591469,0.000000
        -8.904004,38.630100,0.000000
        -8.910956,38.631844,0.000000
        -8.924003,38.631508,0.000000
        -8.958507,38.638950,0.000000
        -8.981509,38.641365,0.000000
        -9.031978,38.653229,0.000000
        -9.046741,38.660133,0.000000
        -9.062190,38.662277,0.000000
        -9.071374,38.660603,0.000000
        -9.077929,38.656029,0.000000
        -9.080204,38.652374,0.000000
      </coordinates>
    </LineString>
  </Placemark>
  <Placemark>
    <name>BikeBus 21</name>
    <Snippet>Saldanha - Moscavide Centro</Snippet>
    <description><![CDATA[<div dir="ltr">Saldanha - Moscavide Centro</div>]]></description>
    <styleUrl>#style10</styleUrl>
    <LineString>
      <tessellate>1</tessellate>
      <coordinates>
        -9.144810,38.733471,0.000000
        -9.145320,38.736031,0.000000
        -9.148280,38.749210,0.000000
        -9.149160,38.751411,0.000000
        -9.139210,38.754662,0.000000
        -9.138260,38.754791,0.000000
        -9.137530,38.753880,0.000000
        -9.137140,38.753811,0.000000
        -9.136760,38.753922,0.000000
        -9.136350,38.754940,0.000000
        -9.130410,38.754379,0.000000
        -9.129420,38.762951,0.000000
        -9.128390,38.762970,0.000000
        -9.124890,38.762131,0.000000
        -9.121500,38.761230,0.000000
        -9.120450,38.760891,0.000000
        -9.120330,38.762192,0.000000
        -9.118890,38.763390,0.000000
        -9.119850,38.766151,0.000000
        -9.118670,38.769051,0.000000
        -9.115910,38.769001,0.000000
        -9.113867,38.769302,0.000000
        -9.112060,38.769680,0.000000
        -9.110960,38.770027,0.000000
        -9.110209,38.770405,0.000000
        -9.109586,38.770901,0.000000
        -9.109597,38.771091,0.000000
        -9.111510,38.774830,0.000000
        -9.110750,38.775620,0.000000
        -9.110770,38.775890,0.000000
        -9.110550,38.775959,0.000000
        -9.108680,38.774719,0.000000
        -9.106750,38.774139,0.000000
        -9.106340,38.774132,0.000000
        -9.105960,38.774391,0.000000
        -9.105860,38.774670,0.000000
        -9.105700,38.774780,0.000000
        -9.104630,38.774830,0.000000
        -9.101960,38.774780,0.000000
        -9.100840,38.774921,0.000000
        -9.101300,38.776161,0.000000
        -9.101700,38.776730,0.000000
        -9.102260,38.776531,0.000000
        -9.102940,38.777802,0.000000
        -9.102120,38.778042,0.000000
        -9.102580,38.779751,0.000000
        -9.103540,38.779442,0.000000
      </coordinates>
    </LineString>
  </Placemark>
</Document>
</kml>

*/
?>

