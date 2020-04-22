if you want to put downloadbox containers' web pages within iframe in panel, you can use this.  

you need to copy paste header and header sidebar modifications to related php files.  

i assumed you already did set up downloadbox from my github repo with nginx reverse proxy.  

i also added nginx reverse proxy config with ssl on port 443.  

then put all php files into panel's admin folder.   

disable plex public connection with iptables, only reverse proxy will work with login auth by nginx.  

iptables -I INPUT -p tcp -s 0.0.0.0/0 --dport 32400 -j DROP  
iptables -I INPUT -p tcp -s 127.0.0.1 --dport 32400 -j ACCEPT  

you must enable login authentication in radarr/sonarr... it is a public server, you can't leave them unprotected, don't be stupid.  
