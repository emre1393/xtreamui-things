if you want to put downloadbox containers' web pages within iframe in panel, you can use this.  

you need to copy paste header and header sidebar modifications to related php files.  

i assumed you already did set up downloadbox from my github repo with nginx reverse proxy.  

i also added nginx reverse proxy config with ssl on port 443.  

then put all php files into panel's admin folder.   

(optional) disable public connections to the container, setup your dlbox and bind localhost ip to ports within docker-compose.yml,  you need to down and up docker-compose again.  
set reverse proxy to work with login auth by nginx.  
example:  
    ports:
      - "127.0.0.1:5111:8112"


you must enable login authentication in radarr/sonarr... it is a public server, you can't leave them unprotected.  



if you want to use nginx reverse proxy for dlbox items, you need to use webroot challange obtain ssl certificate.  


wget https://github.com/emre1393/xtreamui-things/raw/master/downloadbox-in-admin-panel/dlbox_nginx.conf -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dlbox_nginx.conf  
wget https://ssl-config.mozilla.org/ffdhe4096.txt -O /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dhparam.pem  
sed -i 's|yourdomain.com|YOURREALDOMAINHERE|g' /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dlbox_nginx.conf  
sed -i '$i'"$(echo 'include dlbox_nginx.conf;')" /home/xtreamcodes/iptv_xtream_codes/nginx/conf/nginx.conf  
chown xtreamcodes:xtreamcodes -R /home/xtreamcodes  


-Create a common ACME-challenge directory (for Let's Encrypt):  

mkdir -p /var/www/_letsencrypt  
chown xtreamcodes /var/www/_letsencrypt  

--Certbot procedure  

Install Certbot  
sudo apt-get update  
sudo apt-get install software-properties-common  
sudo add-apt-repository universe  
sudo add-apt-repository ppa:certbot/certbot -y  
sudo apt-get update  
sudo apt-get install certbot  

1.Comment out SSL related directives in configuration:  

sed -i -r 's/(listen .*443)/\1;#/g; s/(ssl_(certificate|certificate_key|trusted_certificate) )/#;#\1/g' /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dlbox_nginx.conf  

2.Reload NGINX:  

sudo /home/xtreamcodes/iptv_xtream_codes/nginx/sbin/nginx -s reload  

3.Obtain certificate (replace "yourdomain.com"):  

sudo certbot certonly --webroot -d yourdomain.com --email info@yourdomain.com -w /var/www/_letsencrypt -n --agree-tos --force-renewal  

4.Uncomment SSL related directives in configuration:  

sed -i -r 's/#?;#//g' /home/xtreamcodes/iptv_xtream_codes/nginx/conf/dlbox_nginx.conf  

sudo /home/xtreamcodes/iptv_xtream_codes/nginx/sbin/nginx -s reload  