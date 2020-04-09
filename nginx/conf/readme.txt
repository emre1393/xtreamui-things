this nginx.conf file is prepared to use with certbot ssl certificates

challange method is standalone, it will use 80 port.

sudo certbot certonly --standalone --preferred-challenges http -d  yourdomain.com 


also use wget command to get dhparam from mozilla

wget --no-check-certificate "https://ssl-config.mozilla.org/ffdhe4096.txt" -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dhparam.pem


since r22d, developer added isp api, it is an experimentle api. uncomment the "include nginx_isp_api.conf;" in nginx.conf file.
but with this you need to use certbot webroot challenge method.

mkdir -p /var/www/_letsencrypt
chown xtreamcodes /var/www/_letsencrypt

sudo certbot certonly --webroot --preferred-challenges http -w /var/www/_letsencrypt -d yourdomain.com --email info@yourdomain.com -n --agree-tos --force-renewal


