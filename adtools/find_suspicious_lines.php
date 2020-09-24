<?php

/*
usage is:
/home/xtreamcodes/iptv_xtream_codes/php/bin/php //home/xtreamcodes/iptv_xtream_codes/adtools/find_suspicious_lines.php 10 60 > suspicious_users.txt
*/
set_time_limit(0);
ini_set('max_execution_time', 0);

if(count($argv) < 3){
	die("Greater than and less than arguements are mandatory!\n");
}

$from = $argv[1];
$to = $argv[2];

define("MAIN_DIR", "/home/xtreamcodes/iptv_xtream_codes/");
define("CONFIG_CRYPT_KEY", "5709650b0d7806074842c6de575025b1");

function xor_parse($data, $key) {
    $i = 0;
    $output = '';
    foreach (str_split($data) as $char) {
	    $output.= chr(ord($char) ^ ord($key[$i++ % strlen($key)]));
    }
    return $output;
}

$_INFO = json_decode(xor_parse(base64_decode(file_get_contents(MAIN_DIR . "config")), CONFIG_CRYPT_KEY), True);
if (!$db = new mysqli($_INFO["host"], $_INFO["db_user"], $_INFO["db_pass"], $_INFO["db_name"], $_INFO["db_port"])) { exit("No MySQL connection!"); } 

$channelsToShow = 50;

function getChannels($userId, $limit){
	global $db;
	global $to;
	global $from;
	
	$streamResult = $db->query("SELECT `stream_id`, `streams`.`stream_display_name`, `date_end` FROM `user_activity`, `streams` WHERE `user_id`=$userId AND `streams`.`id`=`user_activity`.`stream_id` AND (`date_end`-`date_start`) > $from AND (`date_end`-`date_start`) < $to AND `container` <> 'VOD' ORDER BY `date_end` DESC LIMIT $limit;"); 

	$channels = [];

	while($channel = $streamResult->fetch_assoc()){
	    $channels[] = $channel['stream_display_name'];
	}

	return $channels;
}


$result = $db->query("SELECT `user_id`, count(*) AS `count`, `date_end`-`date_start` AS `length`, AVG(`date_end`-`date_start`) as average FROM `user_activity` WHERE (`date_end`-`date_start`) > $from AND (`date_end`-`date_start`) < $to AND `container` <> 'VOD' GROUP BY `user_id` ORDER BY `count` DESC;");


if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
		$channels = getChannels($row["user_id"], $channelsToShow);
		$channelList = "";
		foreach($channels as $channel){
			$channelList .= "[" . $channel .  "]";
		}
		
        echo "user_id = " . $row["user_id"] . " ----> " . $row["count"] . " times found " . "Average time = " . $row["average"] . " " . $channelList . "\n";

    }
} else {
    echo "0 results\n";
}

$db->close();

?>