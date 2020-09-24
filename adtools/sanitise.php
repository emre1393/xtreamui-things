<?php
include "/home/xtreamcodes/iptv_xtream_codes/admin/functions.php";

set_time_limit(0);
ini_set('max_execution_time', 0);

$rTables = Array("admin_settings", "blocked_ips", "blocked_user_agents", "bouquets", "enigma2_devices", "epg", "mag_devices", "member_groups", "packages", "reg_users", "rtmp_ips", "series", "series_episodes", "settings", "stream_categories", "stream_subcategories", "streaming_servers", "streams", "streams_options", "streams_sys", "subreseller_setup", "tickets", "tickets_replies", "transcoding_profiles", "users", "watch_categories", "watch_folders", "watch_settings");
foreach ($rTables as $rTable) {
    $rQueries = 0;
    echo "Scanning table: ".$rTable."...\n";
    $rIDColumn = $db->query("SHOW KEYS FROM `".$rTable."` WHERE `Key_name` = 'PRIMARY';")->fetch_assoc()["Column_name"];
    if ($rIDColumn) {
        $rTableResult = $db->query("SELECT * FROM `".$rTable."`;");
        while ($rTableRow = $rTableResult->fetch_assoc()) {
            $rTableXSS = XSSRow($rTableRow);
            if ($rTableXSS <> $rTableRow) {
                $rChanges = Array();
                foreach ($rTableXSS as $rKey => $rValue) {
                    if ($rTableXSS[$rKey] <> str_replace("&quot;", '"', str_replace("&amp;", "&", $rTableRow[$rKey]))) {
                        if (is_null($rValue)) {
                            if (strtoupper($rTableRow[$rKey]) <> "NULL") {
                                $rChanges[] = "`".$rKey."` = NULL";
                            }
                        } else {
                            $rChanges[] = "`".$rKey."` = '".$db->real_escape_string($rValue)."'";
                        }
                    }
                }
                if (count($rChanges) > 0) {
                    $rQueries ++;
                    $rQuery = "UPDATE `".$rTable."` SET ".join(", ", $rChanges)." WHERE `".$rIDColumn."` = '".$rTableRow[$rIDColumn]."';\n";
                    $db->query($rQuery);
                }
            }
        }
    }
    echo "Updated ".$rQueries." rows.\n\n";
}