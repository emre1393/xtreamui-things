this nginx.conf file is prepared to use with certbot ssl certificates

challange method is standalone, it will use 80 port.

sudo certbot certonly --standalone --preferred-challenges http -d  yourdomain.com 


also use wget command to get dhparam from mozilla

wget --no-check-certificate "https://ssl-config.mozilla.org/ffdhe4096.txt" -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dhparam.pem



for main server,

wget "https://github.com/emre1393/xtreamui-things/raw/master/nginx/conf/nginx.conf" -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/nginx.conf

since r22d, developer added isp api, it is an experimentle api. uncomment the "include nginx_isp_api.conf;" in nginx.conf file.
but with this you need to use certbot webroot challenge method.

wget "https://github.com/emre1393/xtreamui-things/raw/master/nginx/conf/nginx_isp_api.conf" -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/nginx_isp_api.conf


mkdir -p /var/www/_letsencrypt
chown xtreamcodes /var/www/_letsencrypt

sudo certbot certonly --webroot --preferred-challenges http -w /var/www/_letsencrypt -d yourdomain.com --email info@yourdomain.com -n --agree-tos --force-renewal



also enable https streaming in panel with adding server id to settings > use_https data like this with a mysql querry.

UPDATE `xtream_iptvpro`.`settings` set `use_https` = '["1","2","3",....,"xyz"]' where `id` = 1;





for lb servers, if you want to use nginx reverse proxy for dlbox items, you need to use webroot challange obtain ssl certificate.

wget "https://github.com/emre1393/xtreamui-things/raw/master/nginx/conf/nginx_for_lb.conf" -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/nginx.conf
wget "https://github.com/emre1393/xtreamui-things/raw/master/downloadbox-in-admin-panel/dlbox_nginx.conf" -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dlbox_nginx.conf

-Create a common ACME-challenge directory (for Let's Encrypt):
mkdir -p /var/www/_letsencrypt
chown xtreamcodes /var/www/_letsencrypt

sudo certbot certonly --webroot -d yourdomain.com --email info@yourdomain.com -w /var/www/_letsencrypt -n --agree-tos --force-renewal
