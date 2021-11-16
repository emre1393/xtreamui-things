<?php

//! OLD CODE
//$rURL = "https://isp.xtream-ui.com/api.php?ip=";

// if ((isset($_GET["ip"])) && (filter_var($_GET["ip"], FILTER_VALIDATE_IP))) {
//     if (!file_exists("./data/".md5($_GET["ip"]))) {
//         $rData = json_decode(file_get_contents($rURL.$_GET["ip"]), True);
//         if (strlen($rData["isp_info"]["description"]) > 0) {
//             $rEnc = json_encode($rData);
//             file_put_contents("./data/".md5($_GET["ip"]), $rEnc);
//             echo $rEnc;
//         }
//     } else {
//         echo file_get_contents("./data/".md5($_GET["ip"]));
//     }
// }
//! OLD CODE END



//? NEW API URL
$rURL = "https://db-ip.com/demo/home.php?s=";

if ((isset($_GET["ip"])) && (filter_var($_GET["ip"], FILTER_VALIDATE_IP))) {

    //? CHECK IF EXIST MD5 API FOLDER "/home/xtreamcodes/iptv_xtream_codes/isp/data/"
    if (!file_exists("./data/".md5($_GET["ip"]))) {

        //? GET INFO ABOUT THE IP WE WANNA TO CONSULT
        $rData = json_decode(file_get_contents($rURL.$_GET["ip"]), True);

        //? CHECK LENG STRING
        if (strlen($rData["demoInfo"]["isp"]) > 0) {
            
            //? CREATE JSON STRUCTURE
            $newjson = array(
                "isp_info" => array(   
                    "as_number" => $rData["demoInfo"]["asNumber"],               
                    "description" => $rData["demoInfo"]["isp"],
                    "type" => $rData["demoInfo"]["usageType"],
                    "ip" => $rData["demoInfo"]["ipAddress"],
                    "country_code" => $rData["demoInfo"]["countryCode"],
                    "country_name" => $rData["demoInfo"]["countryName"],
                    "is_server" => $rData["demoInfo"]["usageType"] != "consumer" ? true : false
                    // note: if api is not returning correct usagetype, try another isp api source.
                )
            );
            //? END

            //? ENCODE TO JSON OUTPUT
            $rEnc = json_encode($newjson);
            
            //? SAVE INFO WITH IP MD5
            file_put_contents("./data/".md5($_GET["ip"]), $rEnc);

            //? PRINT
            echo $rEnc;
        }

    } else {

        echo file_get_contents("./data/".md5($_GET["ip"]));

    }

}


// JSON SAMPLE
// {
//     "isp_info": {
//         "description": "Virgin Media Limited",
//         "type": "Custom",
//         "is_server": false
//     }
// }
//

?>