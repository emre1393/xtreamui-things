this modification redirects users to status videos' link with header location thing, it won't serve those files through main server. also local paths won't work anymore.


replaced code that gives link of status videos,

find this,
readfile(A78bf8d35765BE2408C50712ce7A43aD::$settings[$Cca12fe7fb7e87077953f76f574e3128]);

replace with,
header("\x4c\157\143\x61\x74\151\x6f\x6e\72\x20" . str_replace("\40", "\45\62\x30", A78BF8D35765be2408c50712cE7A43Ad::$settings[$Cca12fe7fb7e87077953f76f574e3128]),TRUE,302);

decoded code is, header("Location: " . str_replace(" ", "%20", ipTV_lib::$settings[$video_path_id],TRUE,302);



also use mpegts videos as status video, you can re-encode an mp4 video to mpegts with this, then upload to any server you want, create a direct download url to serve the file.

/home/xtreamcodes/iptv_xtream_codes/bin/ffmpeg -i /home/examplefolder/input.mp4 \
       -c:v mpeg2video -qscale:v 2 -c:a mp2 -b:a 96k \
       /home/examplefolder/output.ts 



28-08-2021
looks like it does include restreamer for isp check, this one should exclude them. however you are suppose to NOT set is_isplock = 1 for restreamer lines.

run this mysql query to turn off isp lock for them. 
update xtream_iptvpro.users set is_isplock='0' where is_restreamer='1';

--also find,

&& $a8df9f055e91a1e9240230b69af85555["\151\163\x5f\151\x73\160\154\157\x63\x6b"] == 1 &&

--replace with this in streaming.php file,

&& $a8df9f055e91a1e9240230b69af85555["\151\163\x5f\151\x73\160\154\157\x63\x6b"] == 1 && $a8df9f055e91a1e9240230b69af85555["\151\x73\x5f\x72\145\x73\164\162\145\141\x6d\x65\x72"] == 0 &&


----decoded code is && $a8df9f055e91a1e9240230b69af85555["is_isplock"] == 1 && $a8df9f055e91a1e9240230b69af85555["is_restreamer"] == 0 &&
