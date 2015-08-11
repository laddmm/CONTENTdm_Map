<?php

/* 
Map made with the CONTENTdm API and the Javascript Leaflet library

Designed by Marcus Ladd (laddmm@MiamiOH.edu), Miami University Libraries. Last updated August 11, 2015.

Please note this requires Leaflet and the markercluster customization for Leaflet which are avaiable from the Leaflet GitHub at https://github.com/Leaflet

This creates the public map directly from the API. If you have access to an SQL table, I would strong recommend the alternative version that uses apiquery.php to build an SQL table and then the map.php file to create the map. The loading time directly querying the CONTENTdm API in this version is significantly slower.

Please note this requires coordinates in locfield in the format xx.xxxxyyx, -yy.yyyyyy.

To set up the map, configure the following variables:
*/

$contentserver = "contentserver"; /* Replace contentserver with the URL of your CONTENTdm server and port e.g. http://contentserver.lib.miamioh.edu */

$publicserver = "publicserver"; /* Replace publicserver with the URL of the public collection e.g. http://contentdm.lib.miamioh.edu */

$collectionname = "collectionname"; /* Replace collectionname with the name of your collection's alias in CONTENTdm */

$locfield = "locfield"; /* Change locfield to the alias of the field in CONTENTdm which holds the address you want to use in the map. Coordinates are necessary in this version. To use the version with Google Maps, you will need to set up the SQL table with apiquery.php and use the standard map.php option. */

$pagetitle = "pagetitle"; /* Replace pagetitle with the title of your map */

$startingcoords = "39.833333, -98.583333"; /* This is the location where you want to center the map when it loads. If you are unsure of what to chose, 39.833333, -98.583333 is the geographic center of the contiguous United States. */

$startingzoom = "8"; /* This is the starting zoom. 8 creates a map about 400 miles wide. The range is 0-20 with 0 showing the entire world and 20 the maximum zoom. */

/* No edits are required past this point */
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

		var map = L.map('map').setView([40.60144, -82.68311], 8);

		var markers = L.markerClusterGroup();

		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
				'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' ,	id: 'examples.map-i86knfo3'
		}).addTo(map);
<? 

function ctest($ccheck) { if (preg_match('/[A-Za-z]/', $ccheck)) return true; } 

$i = 1;

while ($i < 3000) {

	$metadata_url = $contentserver . "/dmwebservices/index.php?q=dmGetItemInfo/" . $collectionname . "/" . $i . "/json";

	$data = file_get_contents($metadata_url);

	if (!empty($data)) {
	
		$array = json_decode($data, true);
		
		$title = $array["title"];
				
		$id = $array["dmrecord"];
		
		$url = $publicserver . "/cdm/compoundobject/collection/" . $collectionname . "/id/" . $i;

		$thumb = $publicserver . "/utils/getthumbnail/collection/" . $collectionname . "/id/" . $i;

		$location = $array[$locfield];

		if (!empty($location)) {
		
			if (!ctest($location)) {
		
			echo "markers.addLayer(new L.marker([$location]).bindPopup(\"<div style=\\\"width: 190px; max-height: 200px;\\\"> <a href=\\\"$url\\\" target=\\\"_blank\\\"><div style=\\\"float: center;\\\"><img class=\\\"thumbnail\\\" src=\\\"$thumb\\\"></div><p style=\\\"text-align: center;\\\"> $title</p></a></div>\"));\n";
			 }
		}
		
		
	}

$i = $i+1;

}


?>

		map.addLayer(markers);

		var popup = L.popup();
	</script>
</body>
</html>
