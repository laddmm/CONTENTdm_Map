<?php

/* 
Map made with the CONTENTdm API and the Javascript Leaflet library

Designed by Marcus Ladd (laddmm@MiamiOH.edu), Miami University Libraries. Last updated August 5, 2015.

Please note this requires Leaflet and the markercluster customization for Leaflet which are avaiable from the Leaflet GitHub at https://github.com/Leaflet

This creates the public map using the table created by apiquery.php. To set up the map, configure the following variables:
*/

$sqlhost = "sqlhost"; /* Replace sqlhost with the location of your SQL table */

$dbnameconfig = "dbnameconfig"; /* Replace dbname with the name of your SQL database */

$dbtable = "dbtable"; /* Replace dbtable with the name of your SQL table */

$sqluser = "sqluser"; /* Replace sqluser with the user name for your SQL table */

$sqlpassword = "sqlpassword"; /* Replace sqlpassword with the password for your SQL table */

$pagetitle = "pagetitle"; /* Replace pagetitle with the title of your map */

$startingcoords = "39.833333, -98.583333"; /* This is the location where you want to center the map when it loads. If you are unsure of what to chose, 39.833333, -98.583333 is the geographic center of the contiguous United States. */

$startingzoom = "8"; /* This is the starting zoom. 8 creates a map about 400 miles wide. The range is 0-20 with 0 showing the entire world and 20 the maximum zoom. */

/* END OF CUSTOMIZATION, NO FURTHER EDITS ARE REQUIRED */

//SQL set up

$dsn = 'mysql:host=' . $sqlhost . ';dbname=' . $dbnameconfig . ';charset=utf8';

$pdo = new PDO($dsn, $sqluser, $sqlpassword);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

//Done with SQL set up

?>

<!DOCTYPE html>
<html>
<head>
<title><? echo $pagetitle; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="leaflet.css" />
  <link rel="stylesheet" href="MarkerCluster.css" />
  <link rel="stylesheet" href="MarkerCluster.Default.css" />
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" />

  <script src="leaflet.js"></script>
  <script src="leaflet-providers.js"></script>
  <script src="leaflet.markercluster.js"></script>

  <script src="//code.jquery.com/jquery.js"></script>

  <style>
  #map {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    position: absolute;
    width: 100%;
    height: 100%;
  }

.thumbnail {float: center; max-height: 150px; width: auto; display: block; margin-left: auto; margin-right: auto;}
  </style>

</head>
<body>
	<div id="map"></div>
	<script>

		var map = L.map('map').setView([<? echo $startingcoords; ?>], <? echo $startingzoom; ?>);

		var markers = L.markerClusterGroup();

		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
				'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' ,	id: 'examples.map-i86knfo3'
		}).addTo(map);

		<?
			$mapstm = "SELECT title,id,url,thumb,coords FROM " . $dbtable;
			$sth = $pdo->prepare($mapstm);

			$sth->execute();

			$result = $sth->fetchAll();

			foreach ($result as $x) {
				$title = addslashes($x["title"]);
				$url = $x["url"];
				$thumb = $x["thumb"];
				$coords = $x["coords"];

				echo "markers.addLayer(new L.marker([$coords]).bindPopup(\"<div style=\\\"width: 190px; max-height: 200px;\\\"> <a href=\\\"$url\\\" target=\\\"_blank\\\"><div style=\\\"float: center;\\\"><img class=\\\"thumbnail\\\" src=\\\"$thumb\\\"></div><p style=\\\"text-align: center;\\\"> $title</p></a></div>\"));\n";			}
		?>

		map.addLayer(markers);

		var popup = L.popup();
	</script>
</body>
</html>
