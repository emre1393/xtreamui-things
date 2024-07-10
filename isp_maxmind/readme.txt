--install requirements:

sudo apt update
sudo apt install libmaxminddb0 libmaxminddb-dev mmdb-bin


--import asn_database.sql into xtream_iptvpro database.

mysql xtream_iptvpro < asn_database.sql


--then place api.php, GeoIP2-ISP.mmdb, geoip2.phar files into isp folder. you can enable isp api in panel settings and serve to localhost with an nginx server{} block.

note:asn_database.sql is from 2021, don't expect high accuracy. you can disable is_server in api.php if it is not good enough.
note-2: you need to find maxmind isp db from somewhere else or buy it from maxmind. 

