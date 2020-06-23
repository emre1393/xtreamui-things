<?php
include "./functions.php";

if (!isset($_SESSION['hash'])) { exit; }

if (isset($_GET["action"])) {
    if ($_GET["action"] == "stream") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_stream"))) { echo json_encode(Array("result" => False)); exit; }
        $rStreamID = intval($_GET["stream_id"]);
        $rServerID = intval($_GET["server_id"]);
        $rSub = $_GET["sub"];
        if (in_array($rSub, Array("start", "stop"))) {
            echo APIRequest(Array("action" => "stream", "sub" => $rSub, "stream_ids" => Array($rStreamID), "servers" => Array($rServerID)));exit;
        } else if ($rSub == "restart") {
            echo APIRequest(Array("action" => "stream", "sub" => "start", "stream_ids" => Array($rStreamID), "servers" => Array($rServerID)));exit;
        } else if ($rSub == "delete") {
            $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID)." AND `server_id` = ".intval($rServerID).";");
            $result = $db->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID).";");
            if ($result->fetch_assoc()["count"] == 0) {
                $db->query("DELETE FROM `streams` WHERE `id` = ".intval($rStreamID).";");
            }
            scanBouquets();
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "movie") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_movie"))) { echo json_encode(Array("result" => False)); exit; }
        $rStreamID = intval($_GET["stream_id"]);
        $rServerID = intval($_GET["server_id"]);
        $rSub = $_GET["sub"];
        if (in_array($rSub, Array("start", "stop"))) {
            echo APIRequest(Array("action" => "vod", "sub" => "start", "stream_ids" => Array($rStreamID), "servers" => Array($rServerID)));exit;
        } else if ($rSub == "delete") {
            $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID)." AND `server_id` = ".intval($rServerID).";");
            $result = $db->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID).";");
            if ($result->fetch_assoc()["count"] == 0) {
                $db->query("DELETE FROM `streams` WHERE `id` = ".intval($rStreamID).";");
                deleteMovieFile($rServerID, $rStreamID);
                scanBouquets();
            }
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "episode") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_episode"))) { echo json_encode(Array("result" => False)); exit; }
        $rStreamID = intval($_GET["stream_id"]);
        $rServerID = intval($_GET["server_id"]);
        $rSub = $_GET["sub"];
        if (in_array($rSub, Array("start", "stop"))) {
            echo APIRequest(Array("action" => "vod", "sub" => "start", "stream_ids" => Array($rStreamID), "servers" => Array($rServerID)));exit;
        } else if ($rSub == "delete") {
            $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID)." AND `server_id` = ".intval($rServerID).";");
            $result = $db->query("SELECT COUNT(`server_stream_id`) AS `count` FROM `streams_sys` WHERE `stream_id` = ".intval($rStreamID).";");
            if ($result->fetch_assoc()["count"] == 0) {
                $db->query("DELETE FROM `streams` WHERE `id` = ".intval($rStreamID).";");
                $db->query("DELETE FROM `series_episodes` WHERE `stream_id` = ".intval($rStreamID).";");
                deleteMovieFile($rServerID, $rStreamID);
                scanBouquets();
            }
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "user") {
        $rUserID = intval($_GET["user_id"]);
        // Check if this user falls under the reseller or subresellers.
        if (($rPermissions["is_reseller"]) && (!hasPermissions("user", $rUserID))) {
            echo json_encode(Array("result" => False));exit;
        } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_user"))) {
			echo json_encode(Array("result" => False));exit;
		}
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) OR ($rPermissions["is_admin"])) {
                if ($rPermissions["is_reseller"]) {
                    $rUserDetails = getUser($rUserID);
                    if ($rUserDetails) {
                        if ($rUserDetails["is_mag"]) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rUserDetails["username"])."', '".ESC($rUserDetails["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Delete MAG</u>]');");
                        } else if ($rUserDetails["is_e2"]) {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rUserDetails["username"])."', '".ESC($rUserDetails["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Delete Enigma</u>]');");
                        } else {
                            $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rUserDetails["username"])."', '".ESC($rUserDetails["password"])."', ".intval(time()).", '[<b>UserPanel</b> -> <u>Delete Line</u>]');");
                        }
                    }
                }
                $db->query("DELETE FROM `users` WHERE `id` = ".intval($rUserID).";");
                $db->query("DELETE FROM `user_output` WHERE `user_id` = ".intval($rUserID).";");
                $db->query("DELETE FROM `enigma2_devices` WHERE `user_id` = ".intval($rUserID).";");
                $db->query("DELETE FROM `mag_devices` WHERE `user_id` = ".intval($rUserID).";");
                echo json_encode(Array("result" => True));exit;
            } else {
                echo json_encode(Array("result" => False));exit;
            }
        } else if ($rSub == "enable") {
            $db->query("UPDATE `users` SET `enabled` = 1 WHERE `id` = ".intval($rUserID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "disable") {
            $db->query("UPDATE `users` SET `enabled` = 0 WHERE `id` = ".intval($rUserID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "ban") {
            if (!$rPermissions["is_admin"]) { echo json_encode(Array("result" => False)); exit; }
            $db->query("UPDATE `users` SET `admin_enabled` = 0 WHERE `id` = ".intval($rUserID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "unban") {
            if (!$rPermissions["is_admin"]) { echo json_encode(Array("result" => False)); exit; }
            $db->query("UPDATE `users` SET `admin_enabled` = 1 WHERE `id` = ".intval($rUserID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "kill") {
            $rResult = $db->query("SELECT `pid`, `server_id` FROM `user_activity_now` WHERE `user_id` = ".intval($rUserID).";");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    sexec($rRow["server_id"], "kill -9 ".$rRow["pid"]);
                }
            }
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "user_activity") {
        $rPID = intval($_GET["pid"]);
        // Check if the user running this PID falls under the reseller or subresellers.
        if (($rPermissions["is_reseller"]) && (!hasPermissions("pid", $rPID))) {
            echo json_encode(Array("result" => False));exit;
        } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "connection_logs"))) {
			echo json_encode(Array("result" => False));exit;
		}
        $rSub = $_GET["sub"];
        if ($rSub == "kill") {
            $rResult = $db->query("SELECT `server_id` FROM `user_activity_now` WHERE `pid` = ".intval($rPID)." LIMIT 1;");
            if (($rResult) && ($rResult->num_rows == 1)) {
                sexec($rResult->fetch_assoc()["server_id"], "kill -9 ".$rPID);
                echo json_encode(Array("result" => True));exit;
            }
        }
        echo json_encode(Array("result" => False));exit;
    } else if ($_GET["action"] == "process") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "process_monitor"))) { echo json_encode(Array("result" => False)); exit; }
        sexec(intval($_GET["server"]), "kill -9 ".intval($_GET["pid"]));
        echo json_encode(Array("result" => True));exit;
    } else if ($_GET["action"] == "reg_user") {
        $rUserID = intval($_GET["user_id"]);
        // Check if this registered user falls under the reseller or subresellers.
        if (($rPermissions["is_reseller"]) && (!hasPermissions("reg_user", $rUserID))) {
            echo json_encode(Array("result" => False));exit;
        } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_reguser"))) {
			echo json_encode(Array("result" => False));exit;
		}
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            if ((($rPermissions["is_reseller"]) && ($rPermissions["delete_users"])) OR ($rPermissions["is_admin"])) {
                if ($rPermissions["is_reseller"]) {
                    $rUserDetails = getRegisteredUser($rUserID);
                    if ($rUserDetails) {
                        $db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '".ESC($rUserDetails["username"])."', '', ".intval(time()).", '[<b>UserPanel</b> -> <u>Delete Subreseller</u>]');");
                    }
                    $rPrevOwner = getRegisteredUser($rUserDetails["owner_id"]);
                    $rCredits = $rUserDetails["credits"];
                    $rNewCredits = $rPrevOwner["credits"] + $rCredits;
                    $db->query("UPDATE `reg_users` SET `credits` = ".floatval($rNewCredits)." WHERE `id` = ".intval($rPrevOwner["id"]).";");
                }
                $db->query("DELETE FROM `reg_users` WHERE `id` = ".intval($rUserID).";");
                echo json_encode(Array("result" => True));exit;
            } else {
                echo json_encode(Array("result" => False));exit;
            }
        } else if ($rSub == "reset") {
            $db->query("UPDATE `reg_users` SET `google_2fa_sec` = '' WHERE `id` = ".intval($rUserID).";");
            echo json_encode(Array("result" => True));exit;
		} else if ($rSub == "enable") {
            $db->query("UPDATE `reg_users` SET `status` = 1 WHERE `id` = ".intval($rUserID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "disable") {
            $db->query("UPDATE `reg_users` SET `status` = 0 WHERE `id` = ".intval($rUserID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "ticket") {
        $rTicketID = intval($_GET["ticket_id"]);
        // Check if this ticket falls under the reseller or subresellers.
        if (($rPermissions["is_reseller"]) && (!hasPermissions("ticket", $rTicketID))) {
            echo json_encode(Array("result" => False));exit;
        } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "ticket"))) {
			echo json_encode(Array("result" => False));exit;
		}
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `tickets` WHERE `id` = ".intval($rTicketID).";");
            $db->query("DELETE FROM `tickets_replies` WHERE `ticket_id` = ".intval($rTicketID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "close") {
            $db->query("UPDATE `tickets` SET `status` = 0 WHERE `id` = ".intval($rTicketID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "reopen") {
            $db->query("UPDATE `tickets` SET `status` = 1 WHERE `id` = ".intval($rTicketID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "unread") {
            $db->query("UPDATE `tickets` SET `admin_read` = 0 WHERE `id` = ".intval($rTicketID).";");
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "read") {
            $db->query("UPDATE `tickets` SET `admin_read` = 1 WHERE `id` = ".intval($rTicketID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "mag") {
        $rMagID = intval($_GET["mag_id"]);
        // Check if this device falls under the reseller or subresellers.
        if (($rPermissions["is_reseller"]) && (!hasPermissions("mag", $rMagID))) {
            echo json_encode(Array("result" => False));exit;
        } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_mag"))) {
			echo json_encode(Array("result" => False));exit;
		}
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $rMagDetails = getMag($rMagID);
            if (isset($rMagDetails["user_id"])) {
                $db->query("DELETE FROM `users` WHERE `id` = ".intval($rMagDetails["user_id"]).";");
                $db->query("DELETE FROM `user_output` WHERE `user_id` = ".intval($rMagDetails["user_id"]).";");
            }
            $db->query("DELETE FROM `mag_devices` WHERE `mag_id` = ".intval($rMagID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "mag_event") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "manage_events"))) { echo json_encode(Array("result" => False)); exit; }
        $rMagID = intval($_GET["mag_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `mag_events` WHERE `id` = ".intval($rMagID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "epg") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_epg"))) { echo json_encode(Array("result" => False)); exit; }
        $rEPGID = intval($_GET["epg_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `epg` WHERE `id` = ".intval($rEPGID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "profile") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "tprofiles"))) { echo json_encode(Array("result" => False)); exit; }
        $rProfileID = intval($_GET["profile_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `transcoding_profiles` WHERE `profile_id` = ".intval($rProfileID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "series") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_series"))) { echo json_encode(Array("result" => False)); exit; }
        $rSeriesID = intval($_GET["series_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `series` WHERE `id` = ".intval($rSeriesID).";");
            $rResult = $db->query("SELECT `stream_id` FROM `series_episodes` WHERE `series_id` = ".intval($rSeriesID).";");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    $db->query("DELETE FROM `streams_sys` WHERE `stream_id` = ".intval($rRow["stream_id"]).";");
                    $db->query("DELETE FROM `streams` WHERE `id` = ".intval($rRow["stream_id"]).";");
                    deleteMovieFile($rServerID, $rStreamID);
                }
                $db->query("DELETE FROM `series_episodes` WHERE `series_id` = ".intval($rSeriesID).";");
                scanBouquets();
            }
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "folder") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "folder_watch"))) { echo json_encode(Array("result" => False)); exit; }
        $rFolderID = intval($_GET["folder_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `watch_folders` WHERE `id` = ".intval($rFolderID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "useragent") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "block_uas"))) { echo json_encode(Array("result" => False)); exit; }
        $rUAID = intval($_GET["ua_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `blocked_user_agents` WHERE `id` = ".intval($rUAID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "isp") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "block_isps"))) { echo json_encode(Array("result" => False)); exit; }
        $rISPID = intval($_GET["isp_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `isp_addon` WHERE `id` = ".intval($rISPID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "ip") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "block_ips"))) { echo json_encode(Array("result" => False)); exit; }
        $rIPID = intval($_GET["ip"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $rResult = $db->query("SELECT `ip` FROM `blocked_ips` WHERE `id` = ".intval($rIPID).";");
            if (($rResult) && ($rResult->num_rows > 0)) {
                foreach ($rServers as $rServer) {
                    sexec($rServer["id"], "sudo /sbin/iptables -D INPUT -s ".$rResult->fetch_assoc()["ip"]." -j DROP");
                }
            }
            $db->query("DELETE FROM `blocked_ips` WHERE `id` = ".intval($rIPID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "rtmp_ip") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "add_rtmp"))) { echo json_encode(Array("result" => False)); exit; }
        $rIPID = intval($_GET["ip"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `rtmp_ips` WHERE `id` = ".intval($rIPID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "subreseller_setup") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "subreseller"))) { echo json_encode(Array("result" => False)); exit; }
        $rID = intval($_GET["id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `subreseller_setup` WHERE `id` = ".intval($rID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "watch_output") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "folder_watch_output"))) { echo json_encode(Array("result" => False)); exit; }
        $rID = intval($_GET["result_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `watch_output` WHERE `id` = ".intval($rID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "enigma") {
        $rEnigmaID = intval($_GET["enigma_id"]);
        // Check if this device falls under the reseller or subresellers.
        if (($rPermissions["is_reseller"]) && (!hasPermissions("e2", $rEnigmaID))) {
            echo json_encode(Array("result" => False));exit;
        } else if (($rPermissions["is_admin"]) && (!hasPermissions("adv", "edit_e2"))) {
			echo json_encode(Array("result" => False));exit;
		}
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $rEnigmaDetails = getEnigma($rEnigmaID);
            if (isset($rEnigmaDetails["user_id"])) {
                $db->query("DELETE FROM `users` WHERE `id` = ".intval($rEnigmaDetails["user_id"]).";");
                $db->query("DELETE FROM `user_output` WHERE `user_id` = ".intval($rEnigmaDetails["user_id"]).";");
            }
            $db->query("DELETE FROM `enigma2_devices` WHERE `device_id` = ".intval($rEnigmaID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "server") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_server"))) { echo json_encode(Array("result" => False)); exit; }
        $rServerID = intval($_GET["server_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            if ($rServers[$_GET["server_id"]]["can_delete"] == 1) {
                $db->query("DELETE FROM `streaming_servers` WHERE `id` = ".intval($rServerID).";");
                $db->query("DELETE FROM `streams_sys` WHERE `server_id` = ".intval($rServerID).";");
                echo json_encode(Array("result" => True));exit;
            } else {
                echo json_encode(Array("result" => False));exit;
            }
        } else if ($rSub == "kill") {
            $rResult = $db->query("SELECT `pid`, `server_id` FROM `user_activity_now` WHERE `server_id` = ".intval($rServerID).";");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    sexec($rRow["server_id"], "kill -9 ".$rRow["pid"]);
                }
            }
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "start") {
            $rStreamIDs = Array();
            $rResult = $db->query("SELECT `stream_id` FROM `streams_sys` WHERE `server_id` = ".intval($rServerID)." AND `on_demand` = 0;");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    $rStreamIDs[] = intval($rRow["stream_id"]);
                }
            }
            if (count($rStreamIDs) > 0) {
                $rResult = APIRequest(Array("action" => "stream", "sub" => "start", "stream_ids" => array_values($rStreamIDs), "servers" => Array(intval($rServerID))));
            }
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "stop") {
            $rStreamIDs = Array();
            $rResult = $db->query("SELECT `stream_id` FROM `streams_sys` WHERE `server_id` = ".intval($rServerID)." AND `on_demand` = 0;");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    $rStreamIDs[] = intval($rRow["stream_id"]);
                }
            }
            if (count($rStreamIDs) > 0) {
                $rResult = APIRequest(Array("action" => "stream", "sub" => "stop", "stream_ids" => array_values($rStreamIDs), "servers" => Array(intval($rServerID))));
            }
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "package") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_package"))) { echo json_encode(Array("result" => False)); exit; }
        $rPackageID = intval($_GET["package_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `packages` WHERE `id` = ".intval($rPackageID).";");
            echo json_encode(Array("result" => True));exit;
        } else if (in_array($rSub, Array("is_trial", "is_official", "can_gen_mag", "can_gen_e2", "only_mag", "only_e2"))) {
            $db->query("UPDATE `packages` SET `".ESC($rSub)."` = ".intval($_GET["value"])." WHERE `id` = ".intval($rPackageID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "group") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_group"))) { echo json_encode(Array("result" => False)); exit; }
        $rGroupID = intval($_GET["group_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `member_groups` WHERE `group_id` = ".intval($rGroupID)." AND `can_delete` = 1;");
            echo json_encode(Array("result" => True));exit;
        } else if (in_array($rSub, Array("is_banned", "is_admin", "is_reseller"))) {
            $db->query("UPDATE `member_groups` SET `".ESC($rSub)."` = ".intval($_GET["value"])." WHERE `group_id` = ".intval($rGroupID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "bouquet") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_bouquet"))) { echo json_encode(Array("result" => False)); exit; }
        $rBouquetID = intval($_GET["bouquet_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `bouquets` WHERE `id` = ".intval($rBouquetID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "category") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_cat"))) { echo json_encode(Array("result" => False)); exit; }
        $rCategoryID = intval($_GET["category_id"]);
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $db->query("DELETE FROM `stream_categories` WHERE `id` = ".intval($rCategoryID).";");
            echo json_encode(Array("result" => True));exit;
        } else {
            echo json_encode(Array("result" => False));exit;
        }
    } else if ($_GET["action"] == "get_package") {
        $rReturn = Array();
        $rOverride = json_decode($rUserInfo["override_packages"], True);
        $rResult = $db->query("SELECT `id`, `bouquets`, `official_credits` AS `cost_credits`, `official_duration`, `official_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = ".intval($_GET["package_id"]).";");
        if (($rResult) && ($rResult->num_rows == 1)) {
            $rData = $rResult->fetch_assoc();
            if ((isset($rOverride[$rData["id"]]["official_credits"])) && (strlen($rOverride[$rData["id"]]["official_credits"]) > 0)) {
                $rData["cost_credits"] = $rOverride[$rData["id"]]["official_credits"];
            }
            $rData["exp_date"] = date('Y-m-d', strtotime('+'.intval($rData["official_duration"]).' '.$rData["official_duration_in"]));
            if (isset($_GET["user_id"])) {
                if ($rUser = getUser($_GET["user_id"])) {
                    if(time() < $rUser["exp_date"]) {
                        $rData["exp_date"] = date('Y-m-d', strtotime('+'.intval($rData["official_duration"]).' '.$rData["official_duration_in"], $rUser["exp_date"]));
                    } else {
                        $rData["exp_date"] = date('Y-m-d', strtotime('+'.intval($rData["official_duration"]).' '.$rData["official_duration_in"]));
                    }
                }
            }
            foreach (json_decode($rData["bouquets"], True) as $rBouquet) {
                $rResult = $db->query("SELECT * FROM `bouquets` WHERE `id` = ".intval($rBouquet).";");
                if (($rResult) && ($rResult->num_rows == 1)) {
                    $rRow = $rResult->fetch_assoc();
                    $rReturn[] = Array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], True), "bouquet_series" => json_decode($rRow["bouquet_series"], True));
                }
            }
            echo json_encode(Array("result" => True, "bouquets" => $rReturn, "data" => $rData));
        } else {
            echo json_encode(Array("result" => False));
        }
        exit;
    } else if ($_GET["action"] == "get_package_trial") {
        $rReturn = Array();
        $rResult = $db->query("SELECT `bouquets`, `trial_credits` AS `cost_credits`, `trial_duration`, `trial_duration_in`, `max_connections`, `can_gen_mag`, `can_gen_e2`, `only_mag`, `only_e2` FROM `packages` WHERE `id` = ".intval($_GET["package_id"]).";");
        if (($rResult) && ($rResult->num_rows == 1)) {
            $rData = $rResult->fetch_assoc();
            $rData["exp_date"] = date('Y-m-d', strtotime('+'.intval($rData["trial_duration"]).' '.$rData["trial_duration_in"]));
            foreach (json_decode($rData["bouquets"], True) as $rBouquet) {
                $rResult = $db->query("SELECT * FROM `bouquets` WHERE `id` = ".intval($rBouquet).";");
                if (($rResult) && ($rResult->num_rows == 1)) {
                    $rRow = $rResult->fetch_assoc();
                    $rReturn[] = Array("id" => $rRow["id"], "bouquet_name" => $rRow["bouquet_name"], "bouquet_channels" => json_decode($rRow["bouquet_channels"], True), "bouquet_series" => json_decode($rRow["bouquet_series"], True));
                }
            }
            echo json_encode(Array("result" => True, "bouquets" => $rReturn, "data" => $rData));
        } else {
            echo json_encode(Array("result" => False));
        }
        exit;
    } else if ($_GET["action"] == "streams") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "streams"))) { echo json_encode(Array("result" => False)); exit; }
        $rData = Array();
        $rStreamIDs = json_decode($_GET["stream_ids"], True);
        $rStreams = getStreams(null, false, $rStreamIDs);
        echo json_encode(Array("result" => True, "data" => $rStreams));
        exit;
	} else if ($_GET["action"] == "chart_stats") {
		if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "index"))) { echo json_encode(Array("result" => False)); exit; }
		$rStatistics = Array("users" => Array(), "conns" => Array());
		$rPeriod = intval($rAdminSettings["dashboard_stats_frequency"]) ?: 600;
		$rMax = roundUpToAny(time(), $rPeriod);
		$rMin = $rMax - (60*60*24*7);
		$rResult = $db->query("SELECT `type`, `time`, `count` FROM `dashboard_statistics` WHERE `time` >= ".intval($rMin)." AND `time` <= ".intval($rMax)." AND `type` = 'conns';");
		if (($rResult) && ($rResult->num_rows > 0)) {
			while ($rRow = $rResult->fetch_assoc()) {
				$rStatistics[$rRow["type"]][] = Array(intval($rRow["time"]) * 1000, intval($rRow["count"]));
			}
		}
		echo json_encode(Array("result" => True, "data" => $rStatistics, "dates" => Array("hour" => Array($rMax - (60*60), $rMax), "day" => Array($rMax - (60*60*24), $rMax), "week" => Array(null, null))));
        exit;
    } else if ($_GET["action"] == "stats") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "index"))) { echo json_encode(Array("result" => False)); exit; }
        $return = Array("cpu" => 0, "mem" => 0, "uptime" => "--", "total_running_streams" => 0, "bytes_sent" => 0, "bytes_received" => 0, "offline_streams" => 0, "servers" => Array());
        if (isset($_GET["server_id"])) {
            $rServerID = intval($_GET["server_id"]);
            $rWatchDog = json_decode($rServers[$rServerID]["watchdog_data"], True);
            if (is_array($rWatchDog)) {
                $return["uptime"] = $rWatchDog["uptime"];
                $return["mem"] = intval($rWatchDog["total_mem_used_percent"]);
                $return["cpu"] = intval($rWatchDog["cpu_avg"]);
                $return["bytes_received"] = intval($rWatchDog["bytes_received"]);
                $return["bytes_sent"] = intval($rWatchDog["bytes_sent"]);
            }
            $result = $db->query("SELECT COUNT(*) AS `count` FROM `user_activity_now` WHERE `server_id` = ".$rServerID.";");
            $return["open_connections"] = $result->fetch_assoc()["count"];
            $result = $db->query("SELECT COUNT(*) AS `count` FROM `user_activity_now`;");
            $return["total_connections"] = $result->fetch_assoc()["count"];
            $result = $db->query("SELECT COUNT(`user_id`) AS `count` FROM `user_activity_now` WHERE `server_id` = ".$rServerID." GROUP BY `user_id`;");
            $return["online_users"] = $result->num_rows;
            $result = $db->query("SELECT COUNT(`user_id`) AS `count` FROM `user_activity_now` GROUP BY `user_id`;");
            $return["total_users"] = $result->num_rows;
            $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = ".$rServerID." AND `stream_status` <> 2 AND `type` IN (1,3);");
            $return["total_streams"] = $result->fetch_assoc()["count"];
            $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = ".$rServerID." AND `pid` > 0 AND `type` IN (1,3);");
            $return["total_running_streams"] = $result->fetch_assoc()["count"];
            $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = ".$rServerID." AND ((`streams_sys`.`monitor_pid` IS NOT NULL AND `streams_sys`.`monitor_pid` > 0) AND (`streams_sys`.`pid` IS NULL OR `streams_sys`.`pid` <= 0) AND `streams_sys`.`stream_status` <> 0);");
            $return["offline_streams"] = $result->fetch_assoc()["count"];
            $return["network_guaranteed_speed"] = $rServers[$rServerID]["network_guaranteed_speed"];
        } else {
            $rUptime = 0;
            $result = $db->query("SELECT COUNT(*) AS `count` FROM `user_activity_now`;");
            $rTotalConnections = $result->fetch_assoc()["count"];
            $result = $db->query("SELECT COUNT(*) AS `count` FROM `user_activity_now` GROUP BY `user_id`;");
            $rTotalUsers = $result->fetch_assoc()["count"];
			$result = $db->query("SELECT `user_id` FROM `user_activity_now` GROUP BY `user_id`;");
			$return["online_users"] = $result->num_rows;
			$return["open_connections"] = $rTotalConnections;
            foreach (array_keys($rServers) as $rServerID) {
                $rArray = Array();
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `user_activity_now` WHERE `server_id` = ".$rServerID.";");
                $rArray["open_connections"] = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = ".$rServerID." AND `stream_status` <> 2 AND `type` IN (1,3);");
                $rArray["total_streams"] = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = ".$rServerID." AND `pid` > 0 AND `type` IN (1,3);");
                $rArray["total_running_streams"] = $result->fetch_assoc()["count"];
                $result = $db->query("SELECT COUNT(*) AS `count` FROM `streams_sys` LEFT JOIN `streams` ON `streams`.`id` = `streams_sys`.`stream_id` WHERE `server_id` = ".$rServerID." AND ((`streams_sys`.`monitor_pid` IS NOT NULL AND `streams_sys`.`monitor_pid` > 0) AND (`streams_sys`.`pid` IS NULL OR `streams_sys`.`pid` <= 0) AND `streams_sys`.`stream_status` <> 0);");
                $rArray["offline_streams"] = $result->fetch_assoc()["count"];
                $rArray["network_guaranteed_speed"] = $rServers[$rServerID]["network_guaranteed_speed"];
				$result = $db->query("SELECT `user_id` FROM `user_activity_now` WHERE `server_id` = ".intval($rServerID)." GROUP BY `user_id`;");
				$rArray["online_users"] = $result->num_rows;
                $rWatchDog = json_decode($rServers[$rServerID]["watchdog_data"], True);
                if (is_array($rWatchDog)) {
                    $rArray["uptime"] = $rWatchDog["uptime"];
                    $rArray["mem"] = intval($rWatchDog["total_mem_used_percent"]);
                    $rArray["cpu"] = intval($rWatchDog["cpu_avg"]);
                    $rArray["bytes_received"] = intval($rWatchDog["bytes_received"]);
                    $rArray["bytes_sent"] = intval($rWatchDog["bytes_sent"]);
                }
                $rArray["total_connections"] = $rTotalConnections;
                $rArray["total_users"] = $rTotalUsers;
                $rArray["server_id"] = $rServerID;
                $return["servers"][] = $rArray;
            }
            foreach ($return["servers"] as $rServerArray) {
                $return["total_streams"] += $rServerArray["total_streams"];
                $return["total_running_streams"] += $rServerArray["total_running_streams"];
                $return["offline_streams"] += $rServerArray["offline_streams"];
            }
        }
        echo json_encode($return);exit;
    } else if ($_GET["action"] == "reseller_dashboard") {
        if ($rPermissions["is_admin"]) { echo json_encode(Array("result" => False)); exit; }
        $return = Array("open_connections" => 0, "online_users" => 0, "active_accounts" => 0, "credits" => 0);
        $result = $db->query("SELECT `activity_id` FROM `user_activity_now` AS `a` LEFT JOIN `users` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (".ESC(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))).");");
        $return["open_connections"] = $result->num_rows;
        $result = $db->query("SELECT `activity_id` FROM `user_activity_now` AS `a` LEFT JOIN `users` AS `u` ON `a`.`user_id` = `u`.`id` WHERE `u`.`member_id` IN (".ESC(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))).") GROUP BY `a`.`user_id`;");
        $return["online_users"] = $result->num_rows;
        $result = $db->query("SELECT `id` FROM `users` WHERE `member_id` IN (".ESC(join(",", array_keys(getRegisteredUsers($rUserInfo["id"])))).");");
        $return["active_accounts"] = $result->num_rows;
        $return["credits"] = $rUserInfo["credits"];
        echo json_encode($return);exit;
    } else if ($_GET["action"] == "review_selection") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "edit_cchannel") && (!hasPermissions("adv", "create_channel"))))) { echo json_encode(Array("result" => False)); exit; }
        $return = Array("streams" => Array(), "result" => true);
        if (isset($_POST["data"])) {
            foreach ($_POST["data"] as $rStreamID) {
                $rResult = $db->query("SELECT `id`, `stream_display_name`, `stream_source` FROM `streams` WHERE `id` = ".intval($rStreamID).";");
                if (($rResult) && ($rResult->num_rows == 1)) {
                    $rData = $rResult->fetch_assoc();
                    $return["streams"][] = $rData;
                }
            }
        }
        echo json_encode($return);exit;
    } else if ($_GET["action"] == "review_bouquet") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "edit_bouquet") && (!hasPermissions("adv", "add_bouquet"))))) { echo json_encode(Array("result" => False)); exit; }
        $return = Array("streams" => Array(), "vod" => Array(), "series" => Array(), "radios" => Array(), "result" => true);
        if (isset($_POST["data"]["stream"])) {
            foreach ($_POST["data"]["stream"] as $rStreamID) {
                $rResult = $db->query("SELECT `id`, `stream_display_name`, `type` FROM `streams` WHERE `id` = ".intval($rStreamID).";");
                if (($rResult) && ($rResult->num_rows == 1)) {
                    $rData = $rResult->fetch_assoc();
                    if ($rData["type"] == 2) {
                        $return["vod"][] = $rData;
                    } else if ($rData["type"] == 4) {
                        $return["radios"][] = $rData;
                    } else {
                        $return["streams"][] = $rData;
                    }
                }
            }
        }
        if (isset($_POST["data"]["series"])) {
            foreach ($_POST["data"]["series"] as $rSeriesID) {
                $rResult = $db->query("SELECT `id`, `title` FROM `series` WHERE `id` = ".intval($rSeriesID).";");
                if (($rResult) && ($rResult->num_rows == 1)) {
                    $rData = $rResult->fetch_assoc();
                    $return["series"][] = $rData;
                }
            }
        }
        echo json_encode($return);exit;
    } else if ($_GET["action"] == "userlist") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "edit_e2") && (!hasPermissions("adv", "add_e2")) && (!hasPermissions("adv", "add_mag")) && (!hasPermissions("adv", "edit_mag"))))) { echo json_encode(Array("result" => False)); exit; }
        $return = Array("total_count" => 0, "items" => Array(), "result" => true);
        if (isset($_GET["search"])) {
            if (isset($_GET["page"])) {
                $rPage = intval($_GET["page"]);
            } else {
                $rPage = 1;
            }
            $rResult = $db->query("SELECT COUNT(`id`) AS `id` FROM `users` WHERE `username` LIKE '%".ESC($_GET["search"])."%' AND `is_e2` = 0 AND `is_mag` = 0;");
            $return["total_count"] = $rResult->fetch_assoc()["id"];
            $rResult = $db->query("SELECT `id`, `username` FROM `users` WHERE `username` LIKE '%".ESC($_GET["search"])."%' AND `is_e2` = 0 AND `is_mag` = 0 ORDER BY `username` ASC LIMIT ".(($rPage-1) * 100).", 100;");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    $return["items"][] = Array("id" => $rRow["id"], "text" => $rRow["username"]);
                }
            }
        }
        echo json_encode($return);exit;
    } else if ($_GET["action"] == "streamlist") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "manage_mag"))) { echo json_encode(Array("result" => False)); exit; }
        $return = Array("total_count" => 0, "items" => Array(), "result" => true);
        if (isset($_GET["search"])) {
            if (isset($_GET["page"])) {
                $rPage = intval($_GET["page"]);
            } else {
                $rPage = 1;
            }
            $rResult = $db->query("SELECT COUNT(`id`) AS `id` FROM `streams` WHERE `stream_display_name` LIKE '%".ESC($_GET["search"])."%';");
            $return["total_count"] = $rResult->fetch_assoc()["id"];
            $rResult = $db->query("SELECT `id`, `stream_display_name` FROM `streams` WHERE `stream_display_name` LIKE '%".ESC($_GET["search"])."%' ORDER BY `stream_display_name` ASC LIMIT ".(($rPage-1) * 100).", 100;");
            if (($rResult) && ($rResult->num_rows > 0)) {
                while ($rRow = $rResult->fetch_assoc()) {
                    $return["items"][] = Array("id" => $rRow["id"], "text" => $rRow["stream_display_name"]);
                }
            }
        }
        echo json_encode($return);exit;
    } else if ($_GET["action"] == "force_epg") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "epg"))) { echo json_encode(Array("result" => False)); exit; }
        sexec($_INFO["server_id"], "/home/xtreamcodes/iptv_xtream_codes/php/bin/php /home/xtreamcodes/iptv_xtream_codes/crons/epg.php");
        echo json_encode(Array("result" => True));exit;
    } else if ($_GET["action"] == "tmdb_search") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_series")) && (!hasPermissions("adv", "edit_series")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")))) { echo json_encode(Array("result" => False)); exit; }
        include "tmdb.php";
        if (strlen($rAdminSettings["tmdb_language"]) > 0) {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rAdminSettings["tmdb_language"]);
        } else {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
        }
        $rTerm = $_GET["term"];
        if ($rAdminSettings["release_parser"] == "php") {
            include "tmdb_release.php";
            $rRelease = new Release($rTerm);
            $rTerm = $rRelease->getTitle();
        } else {
            $rRelease = parseRelease($rTerm);
            $rTerm = $rRelease["title"];
        }
        $rJSON = Array();
        if ($_GET["type"] == "movie") {
            $rResults = $rTMDB->searchMovie($rTerm);
            foreach ($rResults as $rResult) {
                $rJSON[] = json_decode($rResult->getJSON(), True);
            }
        } else if ($_GET["type"] == "series") {
            $rResults = $rTMDB->searchTVShow($rTerm);
            foreach ($rResults as $rResult) {
                $rJSON[] = json_decode($rResult->getJSON(), True);
            }
        } else {
            $rJSON = json_decode($rTMDB->getSeason($rTerm, intval($_GET["season"]))->getJSON(), True);
        }
        if (count($rJSON) > 0) {
            echo json_encode(Array("result" => True, "data" => $rJSON)); exit;
        }
        echo json_encode(Array("result" => False));exit;
    } else if ($_GET["action"] == "tmdb") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_series")) && (!hasPermissions("adv", "edit_series")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")))) { echo json_encode(Array("result" => False)); exit; }
        include "tmdb.php";
        if (strlen($rAdminSettings["tmdb_language"]) > 0) {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rAdminSettings["tmdb_language"]);
        } else {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
        }
        $rID = $_GET["id"];
        if ($_GET["type"] == "movie") {
            $rMovie = $rTMDB->getMovie($rID);
            $rResult = json_decode($rMovie->getJSON(), True);
            $rResult["trailer"] = $rMovie->getTrailer();
        } else if ($_GET["type"] == "series") {
            $rSeries = $rTMDB->getTVShow($rID);
            $rResult = json_decode($rSeries->getJSON(), True);
            $rResult["trailer"] = getSeriesTrailer($rID);
        }
        if ($rResult) {
            echo json_encode(Array("result" => True, "data" => $rResult)); exit;
        }
        echo json_encode(Array("result" => False));exit;
    } else if ($_GET["action"] == "listdir") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_episode")) && (!hasPermissions("adv", "edit_episode")) && (!hasPermissions("adv", "add_movie")) && (!hasPermissions("adv", "edit_movie")) && (!hasPermissions("adv", "create_channel")) && (!hasPermissions("adv", "edit_cchannel")) && (!hasPermissions("adv", "folder_watch_add")))) { echo json_encode(Array("result" => False)); exit; }
        if ($_GET["filter"] == "video") {
            $rFilter = Array("mp4", "mkv", "avi", "mpg", "flv");
        } else if ($_GET["filter"] == "subs") {
            $rFilter = Array("srt", "sub", "sbv");
        } else {
            $rFilter = null;
        }
        if ((isset($_GET["server"])) && (isset($_GET["dir"]))) {
            echo json_encode(Array("result" => True, "data" => listDir(intval($_GET["server"]), $_GET["dir"], $rFilter))); exit;
        }
        echo json_encode(Array("result" => False));exit;
    } else if ($_GET["action"] == "fingerprint") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "fingerprint"))) { echo json_encode(Array("result" => False)); exit; }
        $rData = json_decode($_GET["data"], true);
        $rActiveServers = Array();
        foreach ($rServers as $rServer) {
            if (((((time() - $rServer["last_check_ago"]) > 360)) OR ($rServer["status"] == 2)) AND ($rServer["can_delete"] == 1) AND ($rServer["status"] <> 3)) { $rServerError = True; } else { $rServerError = False; }
            if (($rServer["status"] == 1) && (!$rServerError)) {
                $rActiveServers[] = $rServer["id"];
            }
        }
        if (($rData["id"] > 0) && ($rData["font_size"] > 0) && (strlen($rData["font_color"]) > 0) && (strlen($rData["xy_offset"]) > 0) && ((strlen($rData["message"]) > 0) OR ($rData["type"] < 3))) {
            $result = $db->query("SELECT `user_activity_now`.`activity_id`, `user_activity_now`.`user_id`, `user_activity_now`.`server_id`, `users`.`username` FROM `user_activity_now` LEFT JOIN `users` ON `users`.`id` = `user_activity_now`.`user_id` WHERE `user_activity_now`.`container` = 'ts' AND `stream_id` = ".intval($rData["id"]).";");
            if (($result) && ($result->num_rows > 0)) {
                set_time_limit(360);
                ini_set('max_execution_time', 360);
                ini_set('default_socket_timeout', 15);
                while ($row = $result->fetch_assoc()) {
                    if (in_array($row["server_id"], $rActiveServers)) {
                        $rArray = Array("font_size" => $rData["font_size"], "font_color" => $rData["font_color"], "xy_offset" => $rData["xy_offset"], "message" => "", "activity_id" => $row["activity_id"]);
                        if ($rData["type"] == 1) {
                            $rArray["message"] = "#".$row["activity_id"];
                        } else if ($rData["type"] == 2) {
                            $rArray["message"] = $row["username"];
                        } else if ($rData["type"] == 3) {
                            $rArray["message"] = $rData["message"];
                        }
                        $rArray["action"] = "signal_send";
                        $rSuccess = SystemAPIRequest(intval($row["server_id"]), $rArray);
                    }
                }
                echo json_encode(Array("result" => True));exit;
            }
        }
        echo json_encode(Array("result" => False));exit;
    } else if ($_GET["action"] == "restart_services") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "edit_server"))) { echo json_encode(Array("result" => False)); exit; }
        $rServerID = intval($_GET["server_id"]);
        if (isset($rServers[$rServerID])) {
            $rJSON = Array("status" => 0, "port" => intval($_GET["ssh_port"]), "host" => $rServer["server_ip"], "password" => $_GET["password"], "time" => intval(time()), "id" => $rServerID, "type" => "reboot");
            file_put_contents("/home/xtreamcodes/iptv_xtream_codes/adtools/balancer/".$rServerID.".json", json_encode($rJSON));
            echo json_encode(Array("result" => True));exit;
        }
        echo json_encode(Array("result" => False));exit;
    } else if ($_GET["action"] == "map_stream") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_stream")) && (!hasPermissions("adv", "edit_stream")))) { echo json_encode(Array("result" => False)); exit; }
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        ini_set('default_socket_timeout', 300);
        echo shell_exec("/home/xtreamcodes/iptv_xtream_codes/bin/ffprobe -v quiet -probesize 2000000 -print_format json -show_format -show_streams \"".escapeshellcmd($_GET["stream"])."\"");exit;
    } else if ($_GET["action"] == "clear_logs") {
        if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "reg_userlog")) && (!hasPermissions("adv", "client_request_log")) && (!hasPermissions("adv", "connection_logs")) && (!hasPermissions("adv", "stream_errors")) && (!hasPermissions("adv", "credits_log")) && (!hasPermissions("adv", "folder_watch_settings")))) { echo json_encode(Array("result" => False)); exit; }
        if (strlen($_GET["from"]) == 0) {
            $rStartTime = null;
        } else if (!$rStartTime = strtotime($_GET["from"]. " 00:00:00")) {
            echo json_encode(Array("result" => False));exit;
        }
        if (strlen($_GET["to"]) == 0) {
            $rEndTime = null;
        } else if (!$rEndTime = strtotime($_GET["to"]." 23:59:59")) {
            echo json_encode(Array("result" => False));exit;
        }
        if (in_array($_GET["type"], Array("client_logs", "stream_logs", "user_activity", "credits_log", "reg_userlog"))) {
            if ($_GET["type"] == "user_activity") {
                $rColumn = "date_start";
            } else {
                $rColumn = "date";
            }
            if (($rStartTime) && ($rEndTime)) {
                $db->query("DELETE FROM `".ESC($_GET["type"])."` WHERE `".$rColumn."` >= ".intval($rStartTime)." AND `".$rColumn."` <= ".intval($rEndTime).";");
            } else if ($rStartTime) {
                $db->query("DELETE FROM `".ESC($_GET["type"])."` WHERE `".$rColumn."` >= ".intval($rStartTime).";");
            } else if ($rEndTime) {
                $db->query("DELETE FROM `".ESC($_GET["type"])."` WHERE `".$rColumn."` <= ".intval($rEndTime).";");
            } else {
                $db->query("DELETE FROM `".ESC($_GET["type"])."`;");
            }
        } else if ($_GET["type"] == "watch_output") {
            if (($rStartTime) && ($rEndTime)) {
                $db->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= ".intval($rStartTime)." AND UNIX_TIMESTAMP(`dateadded`) <= ".intval($rEndTime).";");
            } else if ($rStartTime) {
                $db->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) >= ".intval($rStartTime).";");
            } else if ($rEndTime) {
                $db->query("DELETE FROM `watch_output` WHERE UNIX_TIMESTAMP(`dateadded`) <= ".intval($rEndTime).";");
            } else {
                $db->query("DELETE FROM `watch_output`;");
            }
        }
        echo json_encode(Array("result" => True));exit;
    } else if ($_GET["action"] == "backup") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "database"))) { echo json_encode(Array("result" => False)); exit; }
        $rSub = $_GET["sub"];
        if ($rSub == "delete") {
            $rBackup = pathinfo($_GET["filename"])["filename"];
            if (file_exists(MAIN_DIR."adtools/backups/".$rBackup.".sql")) {
                unlink(MAIN_DIR."adtools/backups/".$rBackup.".sql");
            }
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "restore") {
            $rBackup = pathinfo($_GET["filename"])["filename"];
            $rFilename = MAIN_DIR."adtools/backups/".$rBackup.".sql";
            $rCommand = "mysql -u ".$_INFO["db_user"]." -p".$_INFO["db_pass"]." -P ".$_INFO["db_port"]." ".$_INFO["db_name"]." < \"".$rFilename."\"";
            $rRet = shell_exec($rCommand);
            echo json_encode(Array("result" => True));exit;
        } else if ($rSub == "backup") {
            $rFilename = MAIN_DIR."adtools/backups/backup_".date("Y-m-d_H:i:s").".sql";
            $rCommand = "mysqldump -u ".$_INFO["db_user"]." -p".$_INFO["db_pass"]." -P ".$_INFO["db_port"]." ".$_INFO["db_name"]." --ignore-table=xtream_iptvpro.user_activity --ignore-table=xtream_iptvpro.stream_logs --ignore-table=xtream_iptvpro.panel_logs --ignore-table=xtream_iptvpro.client_logs --ignore-table=xtream_iptvpro.epg_data > \"".$rFilename."\"";
            $rRet = shell_exec($rCommand);
            if (file_exists($rFilename)) {
                $rBackups = getBackups();
                if ((count($rBackups) > intval($rAdminSettings["backups_to_keep"])) && (intval($rAdminSettings["backups_to_keep"]) > 0)) {
                    $rDelete = array_slice($rBackups, 0, count($rBackups) - intval($rAdminSettings["backups_to_keep"]));
                    foreach ($rDelete as $rItem) {
                        if (file_exists(MAIN_DIR."adtools/backups/".$rItem["filename"])) {
                            unlink(MAIN_DIR."adtools/backups/".$rItem["filename"]);
                        }
                    }
                }
                echo json_encode(Array("result" => True, "data" => Array("filename" => pathinfo($rFilename)["filename"].".sql", "timestamp" => filemtime($rFilename), "date" => date("Y-m-d H:i:s", filemtime($rFilename)))));exit;
            }
            echo json_encode(Array("result" => True));exit;
        }
        echo json_encode(Array("result" => False));exit;
        /* 1st one is original, second one allows reseller mag events and "send_message" with "Reseller Sent: " message prefix.
    } else if ($_GET["action"] == "send_event") {
        if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "manage_events"))) { echo json_encode(Array("result" => False)); exit; }
        $rData = json_decode($_GET["data"], True);
        $rMag = getMag($rData["id"]);
        if ($rMag) {
            if ($rData["type"] == "send_msg") {
                $rData["need_confirm"] = 1;
            } else if ($rData["type"] == "play_channel") {
                $rData["need_confirm"] = 0;
                $rData["reboot_portal"] = 0;
                $rData["message"] = intval($rData["channel"]);
            } else if ($rData["type"] == "reset_stb_lock") {
                resetSTB($rData["id"]);
                echo json_encode(Array("result" => True));exit;
            } else {
                $rData["need_confirm"] = 0;
                $rData["reboot_portal"] = 0;
                $rData["message"] = "";
            }
            if ($db->query("INSERT INTO `mag_events`(`status`, `mag_device_id`, `event`, `need_confirm`, `msg`, `reboot_after_ok`, `send_time`) VALUES (0, ".intval($rData["id"]).", '".ESC($rData["type"])."', ".intval($rData["need_confirm"]).", '".ESC($rData["message"])."', ".intval($rData["reboot_portal"]).", ".intval(time()).");")) {
                echo json_encode(Array("result" => True));exit;
            }
        }
        echo json_encode(Array("result" => False));exit;
    }  
    */
    } else if ($_GET["action"] == "send_event") {
        if (($rPermissions["is_admin"]) && (hasPermissions("adv", "manage_events")) OR (($rPermissions["is_reseller"]) && ($rAdminSettings["reseller_mag_events"]))) {
            $rData = json_decode($_GET["data"], True);
            $rMag = getMag($rData["id"]);
            if ($rMag) {
                if ($rData["type"] == "send_msg") {
                    $rData["need_confirm"] = 1;
                } else if ($rData["type"] == "play_channel") {
                    $rData["need_confirm"] = 0;
                    $rData["reboot_portal"] = 0;
                    $rData["message"] = intval($rData["channel"]);
                } else if ($rData["type"] == "reset_stb_lock") {
                    resetSTB($rData["id"]);
                    echo json_encode(Array("result" => True));exit;
                } else {
                    $rData["need_confirm"] = 0;
                    $rData["reboot_portal"] = 0;
                    $rData["message"] = "";
                }
                if ((!$rPermissions["is_admin"]) && !$rData["message"] == 0) {$rData["reseller_message_prefix"] = "Reseller Sent: ";}
                if ($db->query("INSERT INTO `mag_events`(`status`, `mag_device_id`, `event`, `need_confirm`, `msg`, `reboot_after_ok`, `send_time`) VALUES (0, ".intval($rData["id"]).", '".ESC($rData["type"])."', ".intval($rData["need_confirm"]).", '".ESC($rData["reseller_message_prefix"])."".ESC($rData["message"])."', ".intval($rData["reboot_portal"]).", ".intval(time()).");")) {
                    echo json_encode(Array("result" => True));exit;
                }
            }
            echo json_encode(Array("result" => False));exit;
        }
        else {
            echo json_encode(Array("result" => False)); exit; }
    }
}
echo json_encode(Array("result" => False));
?>