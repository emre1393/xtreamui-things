<?php
//rename this file to api.php and place into /home/xtreamcodes/iptv_xtream_codes/isp folder

include "/home/xtreamcodes/iptv_xtream_codes/admin/functions.php";
require_once("geoip2.phar");
use GeoIp2\Database\Reader;

// This creates the Reader object, which should be reused across
// lookups.


if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
    die;
}
if (basename(__FILE__, '.php') != "api") {
    die;
}


if (!file_exists('GeoIP2-ISP.mmdb')) {
    die;
}

function getASNInfo($asn_number) {
    global $db;
    $rResult = $db->query("SELECT * FROM `asn_database` WHERE `asn` = ".intval($asn_number)." limit 1;");
    return $rResult->fetch_assoc();
}

 
//? validate ip address

if ((isset($_GET["ip"])) && (filter_var($_GET["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE))) {

    //? CHECK IF EXIST MD5 API FOLDER "/home/xtreamcodes/iptv_xtream_codes/isp/data/"
    if (!file_exists("/home/xtreamcodes/iptv_xtream_codes/tmp/maxmind_".md5($_GET["ip"]))) {

        $reader = new Reader('GeoIP2-ISP.mmdb');
        $isp_info = $reader->isp($_GET["ip"]);
        $reader->close();

        $reader = new Reader('/home/xtreamcodes/iptv_xtream_codes/GeoLite2.mmdb');
        $countryinfo = $reader->country($_GET["ip"]);
        $reader->close();

        $asninfo = getASNInfo($isp_info->autonomousSystemNumber);

        if (!is_array($asninfo)) {
            die;
        }
        if (($asninfo["type"] == "isp") OR ($asninfo["type"] == "education"))  {
            $is_server = "0"; //isp is for normal consumers
        } else {
            $is_server ="1";
        }

        //? CREATE JSON STRUCTURE
            $theresponse = array(
                "status" => "1",
                "isp_info" => array(   
                    "description" => $isp_info->isp,
                    "as_number" => "$isp_info->autonomousSystemNumber",               
                    "type" => strtoupper($asninfo["type"]),
                    "ip" => $isp_info->ipAddress,
                    "country_name" => $countryinfo->country->name,
                    "country_code" => $countryinfo->country->isoCode,
                    "is_server" => $is_server
                )
            );
            //? END

            //? ENCODE TO JSON OUTPUT
            $rEnc = json_encode($theresponse);
            
            //? SAVE INFO WITH IP MD5
            file_put_contents("/home/xtreamcodes/iptv_xtream_codes/tmp/maxmind_".md5($_GET["ip"]), $rEnc);

            //? PRINT
            echo $rEnc;
            die;
    } else {

        echo file_get_contents("/home/xtreamcodes/iptv_xtream_codes/tmp/maxmind_".md5($_GET["ip"]));
        die;

    }
} //we don't return response for invalid ip


?>