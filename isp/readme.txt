source: https://xtream-ui.com/forum/viewtopic.php?f=7&t=7220  

/home/xtreamcodes/iptv_xtream_codes/isp/api.php  

put it into isp folder  

then add isp api config into nginx.conf like this one https://hastebin.com/udegapoxem.nginx  

    server {
        listen 80;
        root /home/xtreamcodes/iptv_xtream_codes/isp/;
        location / {
            allow 127.0.0.1;
            deny all;
        }
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