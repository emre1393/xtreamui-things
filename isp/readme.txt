source: https://xtream-ui.com/forum/viewtopic.php?f=7&t=7220  

/home/xtreamcodes/iptv_xtream_codes/isp/api.php  

put it into isp folder  

then add isp api config into nginx.conf like this one https://hastebin.com/udegapoxem.nginx  

    server {
        listen 127.0.0.1:80;
        root /home/xtreamcodes/iptv_xtream_codes/isp/;
#        location / {
            allow 127.0.0.1;
            deny all;
#        }
        location ~ \.php$ {
            limit_req zone=one burst=8 nodelay;
            try_files $uri =404;
            fastcgi_index index.php;
            fastcgi_pass php;
            include fastcgi_params;
            fastcgi_buffering on;
            fastcgi_buffers 96 32k;
            fastcgi_buffer_size 32k;
            fastcgi_max_temp_file_size 0;
            fastcgi_keep_conn on;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        }
    } 







---also you can edit streaming.php to change isp api port, it will prevent port conflict.
open wwwdir/includes/streaming.php
find
 
"\x68\x74\164\x70\72\x2f\x2f\141\160\151\56\170\x74\162\x65\141\155\55\143\x6f\144\x65\x73\x2e\x63\x6f\x6d\57\141\x70\151\x2e\160\150\160\77\x69\160\x3d{$f4889efa84e1f2e30e5e9780973f68cb}\46\165\163\145\162\x5f\x61\147\145\156\164\x3d"

and replace with
"http://127.0.0.1:81/api.php?ip={$f4889efa84e1f2e30e5e9780973f68cb}&user_agent="

it will send request to localhost:81
use port 81 in nginx for isp api, 81 was the example, you can use different port.