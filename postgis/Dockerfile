# adds the shp2pgsql tool to the postgis/postgis:10-3.0 image
FROM postgis/postgis:10-3.0

RUN apt-get update
RUN apt-get --assume-yes install postgis
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/*

