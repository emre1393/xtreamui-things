<?php

//rename this file to api.php and place into /home/xtreamcodes/iptv_xtream_codes/isp folder

if ($_SERVER['REMOTE_ADDR'] !== "127.0.0.1") {
    die;
}
if (basename(__FILE__, '.php') != "api") {
    die;
}


    
function url_result($url) {
    $ch = curl_init();
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36';
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml')); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $theresult = curl_exec($ch);
        return $theresult;
    } 
    
function get_between($content, $start, $end) {
    $r = explode($start, $content);
  if (isset($r[1])) {
    $r = explode($end, $r[1]);
    return $r[0];
  }
  return '';
}
    
  
 
//? NEW API URL

if ((isset($_GET["ip"])) && (filter_var($_GET["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE))) {

    //? CHECK IF EXIST MD5 API FOLDER "/home/xtreamcodes/iptv_xtream_codes/isp/data/"
    if (!file_exists("./data/".md5($_GET["ip"]))) {

        $clientip = $_GET["ip"];
        //? GET INFO ABOUT THE IP WE WANNA TO CONSULT
        $dataports = url_result("https://awebanalysis.com/en/ip-lookup/$clientip/");

        $ip_info = array();

        $startipaddr = "<th><b>IP address</b></th>\n                                <td>";
        $endipaddr = "</td>";
        $ip_info["ipaddr"] = trim(get_between($dataports, $startipaddr, $endipaddr));
        if ($clientip !== $ip_info["ipaddr"]){
            die;
        }

        $startispdesc = "<th><b>ISP</b></th>\n                                <td>";
        $endispdesc = '</td>';
        $ip_info["ispdesc"] = trim(get_between($dataports, $startispdesc, $endispdesc));
    
        $startasn = "<th>AS Number</th>\n                                            <td><a href=\"https://awebanalysis.com/en/ipv4-as-number-directory/";
        $endasn = "/\">AS";
        $ip_info["asnnumber"] = trim(get_between($dataports, $startasn, $endasn));
/*        
        $startcountry = "mt2\"></span>";
        $endcountry = '</td>';
        $ip_info["country"] = trim(get_between($dataports, $startcountry, $endcountry));
       
        $startccode = "<td>CCTLD Code</td>\n                                        <td>";
        $endccode = '</td>';
        $ip_info["countrycode"] = trim(get_between($dataports, $startccode, $endccode));
*/           
        $proxyfound = "Proxy Detected";
        $startprxy = "<b>Proxy Type</b></th>\n                                            <td><b>";
        $endprxy = '</b>'; 
           
    
        if(strpos($dataports, $proxyfound)) {
            $ip_info["isserver"] = "1";
            $thetype = trim(get_between($dataports, $startprxy, $endprxy));
            if($thetype == "Public Proxy"){
                $ip_info["isptype"] = "PUBLIC_SERVER_PROXY";
            } elseif($thetype == "DCH"){
                $ip_info["isptype"] = "BUSINESS_HOSTING";
            } elseif($thetype == "RES"){
                $ip_info["isptype"] = "RESIDENTIAL_PROXY";
            } else{
                $ip_info["isptype"] = $thetype;
            }
        
        } else {
            $ip_info["isserver"] = "0";
            $ip_info["isptype"] = "Consumer";        
        }
        


        //? CREATE JSON STRUCTURE
            $newjson = array(
                "status" => "1",
                "isp_info" => array(   
                    "description" => $ip_info["ispdesc"],
                    "as_number" => $ip_info["asnnumber"],               
                    "type" => $ip_info["isptype"],
                    "ip" => $ip_info["ipaddr"],
//                    "country_code" => $ip_info["countrycode"],
//                    "country_name" => $ip_info["country"],
                    "is_server" => $ip_info["isserver"]
                )
            );
            //? END

            //? ENCODE TO JSON OUTPUT
            $rEnc = json_encode($newjson);
            
            //? SAVE INFO WITH IP MD5
            file_put_contents("./data/".md5($_GET["ip"]), $rEnc);

            //? PRINT
            echo $rEnc;
            die;
    } else {

        echo file_get_contents("./data/".md5($_GET["ip"]));
        die;

    }
} //we don't return response for invalid ip


?>