#!/bin/bash
echo load shape file from wms
curl "http://services.clarity-h2020.eu:8080/geoserver/clarity/wfs?request=GetFeature&typeNames=clarity:city&outputFormat=SHAPE-ZIP" --output ./data.zip --silent
echo unzip shape file
unzip -q data 
echo copy shape to postgis container
docker cp city.cst csis-postgis:/
docker cp city.dbf csis-postgis:/
docker cp city.prj csis-postgis:/
docker cp city.shp csis-postgis:/
docker cp city.shx csis-postgis:/
echo create import script
docker exec csis-postgis bash -c "/usr/bin/shp2pgsql -d -W CP1252 /city.shp raw.cities > /cities.sql"
echo execute import script
docker exec csis-postgis bash -c "/usr/bin/psql -h localhost -p 5432 -U postgres -d drupal -f /cities.sql" 
echo publish cities
docker exec csis-postgis bash -c "/usr/bin/psql -h localhost -p 5432 -U postgres -d drupal -c \"select publish_cities();\"" 
echo drush cr
docker exec csis-drupal bash -c "drush cr"
echo clean up
docker exec csis-postgis bash -c "rm city.cst city.dbf city.prj city.shp city.shx cities.sql"
rm city.cst
rm city.dbf
rm city.prj
rm city.shp
rm city.shx
rm wfsrequest.txt
rm data.zip
