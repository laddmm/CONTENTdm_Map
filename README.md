# CONTENTdm_Map
Uses the CONTENTdm API to Create an SQL table for a Leaflet-based map

SET UP
1. Download the Leaflet Javascript library and the Leaflet.markercluster plugin
2. Select which version of the API-map script you will be using. It is *highly* recommended you use the version that creates an SQL table.
3. Place the files in the same directory as the Leaflet library and plugin.

VERSION 1: Using an SQL Table
1. Running apiquery.php will update an SQL table. I recommend you set this up with a cron job if possible.
2. map.php will be the public map display
3. This version will check for any non-numeric coordinates in the location and use the Google Maps API to create coordinates. Please note that the Google Maps API allows a maximum of 2,500 queries per 24 hours and can be buggy.

VERSION 2: All in one
1. allinonemap.php will query the CONTENTdm API and directly create the map. Please note this version is significantly slower and requires exact coordinates in the format xx.xxxxx, -yy.yyyyyy in the location field.

If you need help, please feel free to contact me at laddmm -at- MiamiOH.edu
