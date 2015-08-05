# CONTENTdm_Map
Uses the CONTENTdm API to Create an SQL table for a Leaflet-based map

Note: this requires both the Leaflet Javascript library and the Leaflet.markercluster plugin.

Instructions for customizing each script are found at the top of each file. Place these two files in the same directory as the Leaflet library and plugin. Running apiquery.php (recommend using a cron job) will update the SQL table which map.php then uses to create the public map display.
