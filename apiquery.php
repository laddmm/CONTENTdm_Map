<?php

/* 
Map made with the CONTENTdm API and the Javascript Leaflet library

Designed by Marcus Ladd (laddmm@MiamiOH.edu), Miami University Libraries. Last updated August 5, 2015.

Please note this requires Leaflet and the markercluster customization for Leaflet which are avaiable from the Leaflet GitHub at https://github.com/Leaflet

This creates the table which will be used by map.php. To set up your table, configure the following variables before running the script:
*/

$sqlhost = "sqlhost"; /* Replace sqlhost with the location of your SQL table */

$dbnameconfig = "dbname"; /* Replace dbname with the name of your SQL database */

$dbtable = "dbtable"; /* Replace dbtable with the name of your SQL table */

$sqluser = "sqluser"; /* Replace sqluser with the user name for your SQL table */

$sqlpassword = "sqlpassword"; /* Replace sqlpassword with the password for your SQL table */

$contentserver = "contentserver"; /* Replace contentserver with the URL of your CONTENTdm server and port e.g. http://contentserver.lib.miamioh.edu - N.B. there is no slash at the end */

$publicserver = "publicserver"; /* Replace publicserver with the URL of the public collection e.g. http://contentdm.lib.miamioh.edu - N.B. there is no slash at the end */

$collectionname = "collectionname"; /* Replace collectionname with the name of your collection's alias in CONTENTdm */

$locfield = "locfield"; /* Change locfield to the alias of the field in CONTENTdm which holds the address you want to use in the map. Coordinates are ideal but the script is able to query the Google Maps API to get coordinates from a street address. */

$genlocfield = "genlocfield"; /* The collection this map is based on has two location fields - one that is a subject heading of the state and city (e.g. Ohio--Oxford), the other is a more accurate street address or coordinates. "genlocfield" can be set to a more general back-up location (e.g. Ohio--Oxford). If this is not being used, delete lines 156-164*/

/* END OF VARIABLE CONFIGURATION */

/* Prepping the connection to the SQL database */

$dsn = 'mysql:host=' . $sqlhost . ';dbname=' . $dbnameconfig . ';charset=utf8';

$pdo = new PDO($dsn, $sqluser, $sqlpassword);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

/* Done with setting up the SQL connection */

/* This function queries the Google Maps API to convert street addresses into coordinates */

function getCoordinates($gaddress){
 
$gaddress = str_replace(" ", "+", $gaddress); // replace all the white space with "+" sign to match with google search pattern
 
$gurl = "http://maps.google.com/maps/api/geocode/json?sensor=false&region=us&address=$gaddress";
 
$gresponse = file_get_contents($gurl);
 
$gjson = json_decode($gresponse,TRUE); //generate array object from the response from the web
 
return ($gjson['results'][0]['geometry']['location']['lat'].",".$gjson['results'][0]['geometry']['location']['lng']);
 
}

$id = array();

$newid = array();

$i = 1;

/* Done with Google Maps API set up */

/* This queries the CONTENTdm API and gets the CONTENTdm ID number for each record and stores it in the array $id. It is configured to query up to 10000 entries, but this can be changed in line 57 */

while ($i < 10000) {

	$metadata_url = $contentserver . "/dmwebservices/index.php?q=dmQuery/" . $collectionname . "/0/dmrecord/dmrecord/1024/" . $i . "/0/0/0/0/0/json";

	$data = file_get_contents($metadata_url);

	$array = json_decode($data, true);

	$records = ($array["records"]);

	foreach ($records as $y) {
		$id[] = $y["dmrecord"];
		}

    $i = $i + 1024;
    } //Closes out while
		
/* Done with creating $id array */

/* This uses the $id array to compare the SQL table to the CONTENTdm collection. If an id is found in the CONTENTdm collection that is not in the SQL table, it is added to $newid. */

$idstm = "SELECT id FROM " . $dbtable;

$sth = $pdo->prepare($idstm);
$sth->execute();

$return = $sth->fetchAll(PDO::FETCH_COLUMN, 0);

foreach ($id as $x) {

if (!in_array($x, $return)) {
	$newid[] = $x;
	echo $x . " was marked for upload.\n";
	}

}

/* This checks to see if there is anything new to upload. If $newid is not empty, we have things to upload. */

if (!empty($newid)) {


//This takes each entry in $newid and adds it to the SQL table with the rest of the necessary metadata

foreach ($newid as $item) {

		$metadata_url = $contentserver . "/dmwebservices/index.php?q=dmGetItemInfo/" . $collectionname . "/" . $item . "/json";

		$data = file_get_contents($metadata_url);

		$array = json_decode($data, true);
		
		$title= $array["title"];
		
		$url = $publicserver . "/cdm/compoundobject/collection/" . $collectionname . "/id/" . $item;

		$thumb = $publicserver . "/utils/getthumbnail/collection/" . $collectionname . "/id/" . $item;

		$location = $array[$locfield];

/* The collection this map is based on has two location fields - one that is a subject heading of the state and city (e.g. Ohio--Oxford), the other is a more accurate street address or coordinates. Delete this section if double location fields are not needed. */		
		
		$loc2 = $array[$genlocfield];

		if (empty($location)) {
			$location = $loc2;
 			}
			
/* End of double location field customization */			

		if (!empty($location)){ /* If the item does not have a location, apiquery will ignore it. Otherwise, it will add the necessary information to the table. */
	
		$gcoords = getCoordinates($location);

		$addspace = strpos($gcoords, ',');

		$coords = substr_replace($gcoords, ', ', $addspace, 1);

		$upload = array('title' => $title, 'id' => $item, 'url' => $url, 'thumb' => $thumb, 'coords' => $coords);
				
		$sql = "INSERT INTO " . $dbtable . " (title,id,url,thumb,coords) VALUES (:title, :id, :url, :thumb, :coords)";
		$q = $pdo->prepare($sql);
		$q->execute($upload);
		echo $item . " was successfully added!\n";

		} // Closes out if(!empty($location)
		} // Closes out foreach
	
// Closes out if(!empty
}

else { echo "Nothing new to add.\n";}

/* The Google Maps API is buggy and doesn't always get everything. This checks for anything it missed the previous time. If the coords field in the SQL database is empty, it will delete the entry. */

echo "Checking for errors...\n";

$iderrors = "SELECT id FROM " . $dbtable . " WHERE coords=', '";

$idsql = $pdo->prepare($iderrors);
$idsql->execute();

$idresult = $idsql->fetchAll(PDO::FETCH_COLUMN);

function print_coords_errors ($val){
	echo "Error(s) found in:\n";
	foreach ($val as $x){
	echo $x . "\n";}
	echo "Marked for deletion.\n";
	}

if (!empty($idresult)) {	
print_coords_errors ($idresult);

$fixcoords = "DELETE FROM " . $dbtable . " WHERE coords=', '";

$sqlstm = $pdo->prepare($fixcoords);
$sqlstm->execute();

echo "Errors deleted!\n";
}

else {echo "No errors found!\n";}

/* Done with deleting blank coordinates */

$pdo = null;

echo "All done!\n";

?>
