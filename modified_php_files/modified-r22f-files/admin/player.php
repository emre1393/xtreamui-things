<?php
include "session.php"; include "functions.php";

$protocol=$_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';

// neyslim's fix for http mixed content problem, if you are using https on admin panel, http stream won't work on browser.

if ( $protocol == "https://" ) { $portNumber = "https_broadcast_port" ;}
if ( $protocol == "http://" ) { $portNumber = "http_broadcast_port" ;}


if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "player"))) { exit; }
if (!isset($_GET["id"])) { exit; } ?>
<html>

    <script src="assets/js/vendor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <body>
        <video id="video" width="100%" height="100%" controls></video>
    </body>
    <script>
    $(document).ready(function() {
        var video = document.getElementById('video');
        <?php if ($_GET["type"] == "live") { ?>
        if(Hls.isSupported()) {
            var hls = new Hls();


            hls.loadSource("<?=$protocol.($rServers[$_INFO["server_id"]]["domain_name"] ? $rServers[$_INFO["server_id"]]["domain_name"] : $rServers[$_INFO["server_id"]]["server_ip"])?>:<?=$rServers[$_INFO["server_id"]][$portNumber]?>/live/<?=$rAdminSettings["admin_username"]?>/<?=$rAdminSettings["admin_password"]?>/<?=$_GET["id"]?>.m3u8");
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED,function() {
                video.play();
            });

        }
        <?php } else if ($_GET["type"] == "movie") { ?>
        video.src = "<?=$protocol.($rServers[$_INFO["server_id"]]["domain_name"] ? $rServers[$_INFO["server_id"]]["domain_name"] : $rServers[$_INFO["server_id"]]["server_ip"])?>:<?=$rServers[$_INFO["server_id"]]["portNumber"]?>/movie/<?=$rAdminSettings["admin_username"]?>/<?=$rAdminSettings["admin_password"]?>/<?=$_GET["id"]?>.<?=$_GET["container"]?>";
        <?php } else if ($_GET["type"] == "series") { ?>
        video.src = "<?=$protocol.($rServers[$_INFO["server_id"]]["domain_name"] ? $rServers[$_INFO["server_id"]]["domain_name"] : $rServers[$_INFO["server_id"]]["server_ip"])?>:<?=$rServers[$_INFO["server_id"]]["portNumber"]?>/series/<?=$rAdminSettings["admin_username"]?>/<?=$rAdminSettings["admin_password"]?>/<?=$_GET["id"]?>.<?=$_GET["container"]?>";
        <?php } ?>

    });
    </script>
</html>
