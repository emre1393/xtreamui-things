<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_stream")) && (!hasPermissions("adv", "edit_stream")))) { exit; }
if ((isset($_GET["import"])) && (!hasPermissions("adv", "import_streams"))) { exit; }

if (isset($_POST["submit_stream"])) {
    set_time_limit(0);
    ini_set('mysql.connect_timeout', 0);
    ini_set('max_execution_time', 0);
    ini_set('default_socket_timeout', 0);
    if (isset($_POST["edit"])) {
		if (!hasPermissions("adv", "edit_stream")) { exit; }
        $rArray = getStream($_POST["edit"]);
        unset($rArray["id"]);
    } else {
		if (!hasPermissions("adv", "add_stream")) { exit; }
        $rArray = Array("type" => 1, "added" => time(), "read_native" => 0, "stream_all" => 0, "redirect_stream" => 1, "direct_source" => 0, "gen_timestamps" => 1, "transcode_attributes" => Array(), "stream_display_name" => "", "stream_source" => Array(), "category_id" => 0, "stream_icon" => "", "notes" => "", "custom_sid" => "", "custom_ffmpeg" => "", "custom_map" => "", "transcode_profile_id" => 0, "enable_transcode" => 0, "auto_restart" => "[]", "allow_record" => 1, "rtmp_output" => 0, "epg_id" => null, "channel_id" => null, "epg_lang" => null, "tv_archive_server_id" => 0, "tv_archive_duration" => 0, "delay_minutes" => 0, "external_push" => Array(), "probesize_ondemand" => 128000);
    }
    if ((isset($_POST["days_to_restart"])) && (preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $_POST["time_to_restart"]))) {
        $rTimeArray = Array("days" => Array(), "at" => $_POST["time_to_restart"]);
        foreach ($_POST["days_to_restart"] as $rID => $rDay) {
            $rTimeArray["days"][] = $rDay;
        }
        $rArray["auto_restart"] = $rTimeArray;
    } else {
        $rArray["auto_restart"] = "";
    }
    $rOnDemandArray = Array();
    if (isset($_POST["on_demand"])) {
        foreach ($_POST["on_demand"] as $rID) {
            $rOnDemandArray[] = $rID;
        }
    }
	if (isset($_POST["custom_map"])) {
		$rArray["custom_map"] = $_POST["custom_map"];
	}
	if (isset($_POST["custom_ffmpeg"])) {
		$rArray["custom_ffmpeg"] = $_POST["custom_ffmpeg"];
	}
	if (isset($_POST["custom_sid"])) {
		$rArray["custom_sid"] = $_POST["custom_sid"];
	}
    if (isset($_POST["gen_timestamps"])) {
        $rArray["gen_timestamps"] = 1;
        unset($_POST["gen_timestamps"]);
    } else {
        $rArray["gen_timestamps"] = 0;
    }
    if (isset($_POST["allow_record"])) {
        $rArray["allow_record"] = 1;
        unset($_POST["allow_record"]);
    } else {
        $rArray["allow_record"] = 0;
    }
    if (isset($_POST["rtmp_output"])) {
        $rArray["rtmp_output"] = 1;
        unset($_POST["rtmp_output"]);
    } else {
        $rArray["rtmp_output"] = 0;
    }
    if (isset($_POST["stream_all"])) {
        $rArray["stream_all"] = 1;
        unset($_POST["stream_all"]);
    } else {
        $rArray["stream_all"] = 0;
    }
    if (isset($_POST["direct_source"])) {
        $rArray["direct_source"] = 1;
        unset($_POST["direct_source"]);
    } else {
        $rArray["direct_source"] = 0;
    }
    if (isset($_POST["read_native"])) {
        $rArray["read_native"] = 1;
        unset($_POST["read_native"]);
    } else {
        $rArray["read_native"] = 0;
    }
    if (isset($_POST["tv_archive_duration"])) {
        $rArray["tv_archive_duration"] = intval($_POST["tv_archive_duration"]);
        unset($_POST["tv_archive_duration"]);
    } else {
        $rArray["tv_archive_duration"] = 0;
    }
    if (isset($_POST["delay_minutes"])) {
        $rArray["delay_minutes"] = intval($_POST["delay_minutes"]);
        unset($_POST["delay_minutes"]);
    } else {
        $rArray["delay_minutes"] = 0;
    }
    if (isset($_POST["probesize_ondemand"])) {
        $rArray["probesize_ondemand"] = intval($_POST["probesize_ondemand"]);
        unset($_POST["probesize_ondemand"]);
    } else {
        $rArray["probesize_ondemand"] = 0;
    }
    if (empty($_POST["epg_lang"])) {
        $rArray["epg_lang"] = "NULL";
        unset($_POST["epg_lang"]);
    }
    if (isset($_POST["epg_id"])) {
        $rArray["epg_id"] = $_POST["epg_id"];
        unset($_POST["epg_id"]);
    }
    if (isset($_POST["channel_id"])) {
        $rArray["channel_id"] = $_POST["channel_id"];
        unset($_POST["channel_id"]);
    }
    if (!$rArray["transcode_profile_id"]) {
        $rArray["transcode_profile_id"] = 0;
    }
    if ($rArray["transcode_profile_id"] > 0) {
        $rArray["enable_transcode"] = 1;
    }
	if (isset($_POST["restart_on_edit"])) {
		$rRestart = true;
		unset($_POST["restart_on_edit"]);
	} else {
		$rRestart = false;
	}
    $rBouquets = $_POST["bouquets"];
    unset($_POST["bouquets"]);
    foreach($_POST as $rKey => $rValue) {
        if (isset($rArray[$rKey])) {
            $rArray[$rKey] = $rValue;
        }
    }
    $rImportStreams = Array();
    if (isset($_FILES["m3u_file"])) {
		if (!hasPermissions("adv", "import_streams")) { exit; }
        $rStreamDatabase = Array();
        $result = $db->query("SELECT `stream_source` FROM `streams` WHERE `type` IN (1,3);");
        if (($result) && ($result->num_rows > 0)) {
            while ($row = $result->fetch_assoc()) {
                foreach (json_decode($row["stream_source"], True) as $rSource) {
                    if (strlen($rSource) > 0) {
                        $rStreamDatabase[] = str_replace(" ", "%20", $rSource);
                    }
                }
            }
        }
        $rFile = '';
        if ((!empty($_FILES['m3u_file']['tmp_name'])) && (strtolower(pathinfo($_FILES['m3u_file']['name'], PATHINFO_EXTENSION)) == "m3u")) {
            $rFile = file_get_contents($_FILES['m3u_file']['tmp_name']);
        }
        preg_match_all('/(?P<tag>#EXTINF:[-1,0])|(?:(?P<prop_key>[-a-z]+)=\"(?P<prop_val>[^"]+)")|(?<name>,[^\r\n]+)|(?<url>http[^\s]+)/', $rFile, $rMatches);
        $rResults = [];
        $rIndex = -1;
        for ($i = 0; $i < count($rMatches[0]); $i++) {
            $rItem = $rMatches[0][$i];
            if (!empty($rMatches['tag'][$i])) {
                ++$rIndex;
            } elseif (!empty($rMatches['prop_key'][$i])) {
                $rResults[$rIndex][$rMatches['prop_key'][$i]] = trim($rMatches['prop_val'][$i]);
            } elseif (!empty($rMatches['name'][$i])) {
                $rResults[$rIndex]['name'] = trim(substr($rItem, 1));
            } elseif (!empty($rMatches['url'][$i])) {
                $rResults[$rIndex]['url'] = str_replace(" ", "%20", trim($rItem));
            }
        }
        foreach ($rResults as $rResult) {
            $rImportArray = Array("stream_source" => Array($rResult["url"]), "stream_icon" => $rResult["tvg-logo"] ?: "", "stream_display_name" => $rResult["name"] ?: "", "epg_id" => null, "epg_lang" => null, "channel_id" => null);
            if ($rResult["tvg-id"]) {
                $rEPG = findEPG($rResult["tvg-id"]);
                if (isset($rEPG)) {
                    $rImportArray["epg_id"] = $rEPG["epg_id"];
                    $rImportArray["channel_id"] = $rEPG["channel_id"];
                    if (!empty($rEPG["epg_lang"])) {
                        $rImportArray["epg_lang"] = $rEPG["epg_lang"];
                    }
                }
            }
			if (!in_array($rResult["url"], $rStreamDatabase)) {
				$rImportStreams[] = $rImportArray;
			}
        }
    } else {
        $rImportArray = Array("stream_source" => Array(), "stream_icon" => $rArray["stream_icon"], "stream_display_name" => $rArray["stream_display_name"], "epg_id" => $rArray["epg_id"], "epg_lang" => $rArray["epg_lang"], "channel_id" => $rArray["channel_id"]);
        if (isset($_POST["stream_source"])) {
            foreach ($_POST["stream_source"] as $rID => $rURL) {
                if (strlen($rURL) > 0) {
                    $rImportArray["stream_source"][] = $rURL;
                }
            }
        }
		if (isset($_POST["edit"])) {
			$rImportStreams[] = $rImportArray;
		} else {
			$rResult = $db->query("SELECT COUNT(`id`) AS `count` FROM `streams` WHERE `stream_display_name` = '".ESC($rImportArray["stream_display_name"])."' AND `type` IN (1,3);");
			if ($rResult->fetch_assoc()["count"] == 0) {
				$rImportStreams[] = $rImportArray;
			} else {
				$_STATUS = 2;
				$rStream = $rArray;
			}
		}
    }
    if (count($rImportStreams) > 0) {
        foreach ($rImportStreams as $rImportStream) {
            $rImportArray = $rArray;
			if ($rAdminSettings["download_images"]) {
				$rImportStream["stream_icon"] = downloadImage($rImportStream["stream_icon"]);
			}
            foreach (array_keys($rImportStream) as $rKey) {
				$rImportArray[$rKey] = $rImportStream[$rKey];
            }
            $rImportArray["order"] = getNextOrder();
            $rCols = "`".ESC(implode('`,`', array_keys($rImportArray)))."`";
            $rValues = null;
            foreach (array_values($rImportArray) as $rValue) {
                isset($rValues) ? $rValues .= ',' : $rValues = '';
                if (is_array($rValue)) {
                    $rValue = json_encode($rValue);
                }
                if (is_null($rValue)) {
                    $rValues .= 'NULL';
                } else {
                    $rValues .= '\''.ESC($rValue).'\'';
                }
            }
            if (isset($_POST["edit"])) {
                $rCols = "`id`,".$rCols;
                $rValues = ESC($_POST["edit"]).",".$rValues;
            }
            $rQuery = "REPLACE INTO `streams`(".$rCols.") VALUES(".$rValues.");";
            if ($db->query($rQuery)) {
                if (isset($_POST["edit"])) {
                    $rInsertID = intval($_POST["edit"]);
                } else {
                    $rInsertID = $db->insert_id;
                }
            }
            if (isset($rInsertID)) {
                $rStreamExists = Array();
                if (isset($_POST["edit"])) {
                    $result = $db->query("SELECT `server_stream_id`, `server_id` FROM `streams_sys` WHERE `stream_id` = ".intval($rInsertID).";");
                    if (($result) && ($result->num_rows > 0)) {
                        while ($row = $result->fetch_assoc()) {
                            $rStreamExists[intval($row["server_id"])] = intval($row["server_stream_id"]);
                        }
                    }
                }
                if (isset($_POST["server_tree_data"])) {
                    $rStreamsAdded = Array();
                    $rServerTree = json_decode($_POST["server_tree_data"], True);
                    foreach ($rServerTree as $rServer) {
                        if ($rServer["parent"] <> "#") {
                            $rServerID = intval($rServer["id"]);
                            $rStreamsAdded[] = $rServerID;
                            if ($rServer["parent"] == "source") {
                                $rParent = "NULL";
                            } else {
                                $rParent = intval($rServer["parent"]);
                            }
                            if (in_array($rServerID, $rOnDemandArray)) {
                                $rOD = 1;
                            } else {
                                $rOD = 0;
                            }
                            
                            if (isset($rStreamExists[$rServerID])) {
                                $db->query("UPDATE `streams_sys` SET `parent_id` = ".$rParent.", `on_demand` = ".$rOD." WHERE `server_stream_id` = ".$rStreamExists[$rServerID].";");
                            } else {
                                $db->query("INSERT INTO `streams_sys`(`stream_id`, `server_id`, `parent_id`, `on_demand`) VALUES(".intval($rInsertID).", ".$rServerID.", ".$rParent.", ".$rOD.");");
                            }
                        }
                    }
                    foreach ($rStreamExists as $rServerID => $rDBID) {
                        if (!in_array($rServerID, $rStreamsAdded)) {
                            $db->query("DELETE FROM `streams_sys` WHERE `server_stream_id` = ".$rDBID.";");
                        }
                    }
                }
                $db->query("DELETE FROM `streams_options` WHERE `stream_id` = ".intval($rInsertID).";");
                if ((isset($_POST["user_agent"])) && (strlen($_POST["user_agent"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rInsertID).", 1, '".ESC($_POST["user_agent"])."');");
                }
                if ((isset($_POST["http_proxy"])) && (strlen($_POST["http_proxy"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rInsertID).", 2, '".ESC($_POST["http_proxy"])."');");
                }
				if ((isset($_POST["cookie"])) && (strlen($_POST["cookie"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rInsertID).", 17, '".ESC($_POST["cookie"])."');");
                }
				if ((isset($_POST["headers"])) && (strlen($_POST["headers"]) > 0)) {
                    $db->query("INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(".intval($rInsertID).", 19, '".ESC($_POST["headers"])."');");
                }
				if ($rRestart) {
					APIRequest(Array("action" => "stream", "sub" => "start", "stream_ids" => Array($rInsertID)));
				}
                foreach ($rBouquets as $rBouquet) {
                    addToBouquet("stream", $rBouquet, $rInsertID);
                }
                if ((!isset($_FILES["m3u_file"])) && (isset($_POST["edit"]))) {
                    foreach (getBouquets() as $rBouquet) {
                        if (!in_array($rBouquet["id"], $rBouquets)) {
                            removeFromBouquet("stream", $rBouquet["id"], $rInsertID);
                        }
                    }
                }
                $_STATUS = 0;
            } else {
                $_STATUS = 1;
				$rStream = $rArray;
            }
        }
        scanBouquets();
        if (isset($_FILES["m3u_file"])) {
            header("Location: ./streams.php");exit;
        } else if (!isset($_GET["id"])) {
            header("Location: ./stream.php?id=".$rInsertID); exit;
        }
    } else {
		if (!isset($_STATUS)) {
			$_STATUS = 3;
            $rStream = $rArray;
		}
    }
}

if (isset($_STATUS)) {
	foreach ($rStream as $rKey => $rValue) {
		if (is_array($rValue)) {
			$rStream[$rKey] = json_encode($rValue);
		}
	}
}

$rEPGSources = getEPGSources();
$rStreamArguments = getStreamArguments();
$rTranscodeProfiles = getTranscodeProfiles();

$rEPGJS = Array(0 => Array());
foreach ($rEPGSources as $rEPG) {
    $rEPGJS[$rEPG["id"]] = json_decode($rEPG["data"], True);
}

$rServerTree = Array();
$rOnDemand = Array();
$rServerTree[] = Array("id" => "source", "parent" => "#", "text" => "<strong>Stream Source</strong>", "icon" => "mdi mdi-youtube-tv", "state" => Array("opened" => true));
if (isset($_GET["id"])) {
    if ((isset($_GET["import"])) OR (!hasPermissions("adv", "edit_stream"))) { exit; }
    $rStream = getStream($_GET["id"]);
    if ((!$rStream) or ($rStream["type"] <> 1)) {
        exit;
    }
    $rStreamOptions = getStreamOptions($_GET["id"]);
    $rStreamSys = getStreamSys($_GET["id"]);
    foreach ($rServers as $rServer) {
        if (isset($rStreamSys[intval($rServer["id"])])) {
            if ($rStreamSys[intval($rServer["id"])]["parent_id"] <> 0) {
                $rParent = intval($rStreamSys[intval($rServer["id"])]["parent_id"]);
            } else {
                $rParent = "source";
            }
        } else {
            $rParent = "#";
        }
        $rServerTree[] = Array("id" => $rServer["id"], "parent" => $rParent, "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
    }
    foreach ($rStreamSys as $rStreamItem) {
        if ($rStreamItem["on_demand"] == 1) {
            $rOnDemand[] = $rStreamItem["server_id"];
        }
    }
} else {
	if (!hasPermissions("adv", "add_stream")) { exit; }
    foreach ($rServers as $rServer) {
        $rServerTree[] = Array("id" => $rServer["id"], "parent" => "#", "text" => $rServer["server_name"], "icon" => "mdi mdi-server-network", "state" => Array("opened" => true));
    }
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="./streams.php<?php if (isset($_GET["category"])) { echo "?category=".$_GET["category"]; } ?>">
                                            <button type="button" class="btn btn-primary waves-effect waves-light btn-sm">
                                                View Streams
                                            </button>
                                        </a>
                                        <?php if (!isset($rStream)) { if (!isset($_GET["import"])) { ?>
                                        <a href="./stream.php?import">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                Import M3U
                                            </button>
                                        </a>
                                        <?php } else { ?>
                                        <a href="./stream.php">
                                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm">
                                                Add Single
                                            </button>
                                        </a>
                                        <?php } } ?>
                                    </li>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rStream["id"])) { echo $rStream["stream_display_name"].' &nbsp;<button type="button" class="btn btn-outline-info waves-effect waves-light btn-xs" onClick="player('.$rStream["id"].');"><i class="mdi mdi-play"></i></button>'; } else if (isset($_GET["import"])) { echo "Import Streams"; } else { echo "Add Stream"; } ?></h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            Stream operation was completed successfully.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            An error occured while inserting into the database, please check the form.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            The stream name is already in use, please select another.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            No new streams were imported from the M3U. Please check the input.
                        </div>
                        <?php }
                        if (isset($rStream["id"])) { ?>
                        <div class="card text-xs-center">
                            <div class="table">
                                <table id="datatable" class="table table-borderless mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th>Source</th>
                                            <th>Clients</th>
                                            <th>Uptime</th>
                                            <th>Actions</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center">Loading stream information...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form<?php if(isset($_GET["import"])) { echo " enctype=\"multipart/form-data\""; } ?> action="./stream.php<?php if (isset($_GET["import"])) { echo "?import"; } else if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="stream_form" data-parsley-validate="">
                                    <?php if (isset($rStream["id"])) { ?>
                                    <input type="hidden" name="edit" value="<?=$rStream["id"]?>" />
                                    <?php } ?>
                                    <input type="hidden" name="server_tree_data" id="server_tree_data" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#stream-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#advanced-options" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Advanced</span>
                                                </a>
                                            </li>
											<?php if (!isset($_GET["import"])) { ?>
                                            <li class="nav-item">
                                                <a href="#stream-map" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-map mr-1"></i>
                                                    <span class="d-none d-sm-inline">Map</span>
                                                </a>
                                            </li>
											<?php } ?>
                                            <li class="nav-item">
                                                <a href="#auto-restart" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-clock-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Auto Restart</span>
                                                </a>
                                            </li>
                                            <?php if (!isset($_GET["import"])) { ?>
                                            <li class="nav-item">
                                                <a href="#epg-options" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-television-guide mr-1"></i>
                                                    <span class="d-none d-sm-inline">EPG</span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <li class="nav-item">
                                                <a href="#load-balancing" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-server-network mr-1"></i>
                                                    <span class="d-none d-sm-inline">Servers</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="stream-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <?php if (!isset($_GET["import"])) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_display_name">Stream Name</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="stream_display_name" name="stream_display_name" value="<?php if (isset($rStream)) { echo htmlspecialchars($rStream["stream_display_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <span class="streams">
                                                            <?php
                                                            if (isset($rStream)) {
                                                                $rStreamSources = json_decode($rStream["stream_source"], True);
                                                                if (!$rStreamSources) {
                                                                    $rStreamSources = Array("");
                                                                }
                                                            } else {
                                                                $rStreamSources = Array("");
                                                            }
                                                            $i = 0;
                                                            foreach ($rStreamSources as $rStreamSource) { $i++
                                                            ?>
                                                            <div class="form-group row mb-4 stream-url">
                                                                <label class="col-md-4 col-form-label" for="stream_source"> Stream URL</label>
                                                                <div class="col-md-8 input-group">
                                                                    <input type="text" id="stream_source" name="stream_source[]" class="form-control" value="<?=htmlspecialchars($rStreamSource)?>">
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-info waves-effect waves-light" onClick="moveUp(this);" type="button"><i class="mdi mdi-chevron-up"></i></button>
                                                                        <button class="btn btn-info waves-effect waves-light" onClick="moveDown(this);" type="button"><i class="mdi mdi-chevron-down"></i></button>
                                                                        <button class="btn btn-primary waves-effect waves-light" onClick="addStream();" type="button"><i class="mdi mdi-plus"></i></button>
                                                                        <button class="btn btn-danger waves-effect waves-light" onClick="removeStream(this);" type="button"><i class="mdi mdi-close"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </span>
                                                        <?php } else { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="m3u_file">M3U</label>
                                                            <div class="col-md-8">
                                                                <input type="file" id="m3u_file" name="m3u_file" />
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="category_id">Category Name</label>
                                                            <div class="col-md-8">
                                                                <select name="category_id" id="category_id" class="form-control" data-toggle="select2">
                                                                    <?php foreach ($rCategories as $rCategory) { ?>
                                                                    <option <?php if (isset($rStream)) { if (intval($rStream["category_id"]) == intval($rCategory["id"])) { echo "selected "; } } else if ((isset($_GET["category"])) && ($_GET["category"] == $rCategory["id"])) { echo "selected "; } ?>value="<?=$rCategory["id"]?>"><?=$rCategory["category_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="bouquets">Add To Bouquets</label>
                                                            <div class="col-md-8">
                                                                <select name="bouquets[]" id="bouquets" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose...">
                                                                    <?php foreach (getBouquets() as $rBouquet) { ?>
                                                                    <option <?php if (isset($rStream)) { if (in_array($rStream["id"], json_decode($rBouquet["bouquet_channels"], True))) { echo "selected "; } } ?>value="<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <?php if (!isset($_GET["import"])) { ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_icon">Stream Logo URL</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="stream_icon" name="stream_icon" value="<?php if (isset($rStream)) { echo htmlspecialchars($rStream["stream_icon"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="notes">Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder=""><?php if (isset($rStream)) { echo htmlspecialchars($rStream["notes"]); } ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="advanced-options">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="gen_timestamps">Generate PTS <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Allow FFmpeg to generate presentation timestamps for you to achieve better synchronization with the stream codecs. In some streams this can cause de-sync." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="gen_timestamps" id="gen_timestamps" type="checkbox" <?php if (isset($rStream)) { if ($rStream["gen_timestamps"] == 1) { echo "checked "; } } else { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="read_native">Native Frames <i data-toggle="tooltip" data-placement="top" title="" data-original-title="You should always read live streams as non-native frames. However if you are streaming static video files, set this to true otherwise the encoding process will fail." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="read_native" id="read_native" type="checkbox" <?php if (isset($rStream)) { if ($rStream["read_native"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="stream_all">Stream All Codecs <i data-toggle="tooltip" data-placement="top" title="" data-original-title="This option will stream all codecs from your stream. Some streams have more than one audio/video/subtitles channels." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="stream_all" id="stream_all" type="checkbox" <?php if (isset($rStream)) { if ($rStream["stream_all"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="allow_record">Allow Recording</label>
                                                            <div class="col-md-2">
                                                                <input name="allow_record" id="allow_record" type="checkbox" <?php if (isset($rStream)) { if ($rStream["allow_record"] == 1) { echo "checked "; } } else { echo "checked "; } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="rtmp_output">Allow RTMP Output <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Enable RTMP output for this channel." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="rtmp_output" id="rtmp_output" type="checkbox" <?php if (isset($rStream)) { if ($rStream["rtmp_output"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="direct_source">Direct Source <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Don't run source through Xtream Codes, just redirect instead." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="direct_source" id="direct_source" type="checkbox" <?php if (isset($rStream)) { if ($rStream["direct_source"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="custom_sid">Custom Channel SID <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Here you can specify the SID of the channel in order to work with the epg on the enigma2 devices. You have to specify the code with the ':' but without the first number, 1 or 4097 . Example: if we have this code:  '1:0:1:13f:157c:13e:820000:0:0:0:2097' then you have to add on this field:  ':0:1:13f:157c:13e:820000:0:0:0:" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="custom_sid" name="custom_sid" value="<?php if (isset($rStream)) { echo htmlspecialchars($rStream["custom_sid"]); } ?>">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="delay_minutes">Minute Delay <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Delay stream by X minutes. Will not work with on demand streams." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="delay_minutes" name="delay_minutes" value="<?php if (isset($rStream)) { echo $rStream["delay_minutes"]; } else { echo "0"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="custom_ffmpeg">Custom FFmpeg Command <i data-toggle="tooltip" data-placement="top" title="" data-original-title="In this field you can write your own custom FFmpeg command. Please note that this command will be placed after the input and before the output. If the command you will specify here is about to do changes in the output video or audio, it may require to transcode the stream. In this case, you have to use and change at least the Video/Audio Codecs using the transcoding attributes below. The custom FFmpeg command will only be used by the server(s) that take the stream from the Source." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="custom_ffmpeg" name="custom_ffmpeg" value="<?php if (isset($rStream)) { echo htmlspecialchars($rStream["custom_ffmpeg"]); } ?>">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="probesize_ondemand">On Demand Probesize <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Adjustable probesize for ondemand streams. Adjust this setting if you experience issues with no audio." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="probesize_ondemand" name="probesize_ondemand" value="<?php if (isset($rStream)) { echo $rStream["probesize_ondemand"]; } else { echo "128000"; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="user_agent">User Agent</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="user_agent" name="user_agent" value="<?php if (isset($rStreamOptions[1])) { echo htmlspecialchars($rStreamOptions[1]["value"]); } else { echo htmlspecialchars($rStreamArguments["user_agent"]["argument_default_value"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="http_proxy">HTTP Proxy <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Format: ip:port" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="http_proxy" name="http_proxy" value="<?php if (isset($rStreamOptions[2])) { echo htmlspecialchars($rStreamOptions[2]["value"]); } else { echo htmlspecialchars($rStreamArguments["proxy"]["argument_default_value"]); } ?>">
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="cookie">Cookie <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Format: key=value;" class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="cookie" name="cookie" value="<?php if (isset($rStreamOptions[17])) { echo htmlspecialchars($rStreamOptions[17]["value"]); } else { echo htmlspecialchars($rStreamArguments["cookie"]["argument_default_value"]); } ?>">
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="headers">Headers <i data-toggle="tooltip" data-placement="top" title="" data-original-title="FFmpeg -headers command." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="headers" name="headers" value="<?php if (isset($rStreamOptions[19])) { echo htmlspecialchars($rStreamOptions[19]["value"]); } else { echo htmlspecialchars($rStreamArguments["headers"]["argument_default_value"]); } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="transcode_profile_id">Transcoding Profile <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Sometimes, in order to make a stream compatible with most devices, it must be transcoded. Please note that the transcode will only be applied to the server(s) that take the stream directly from the source, all other servers attached to the transcoding server will not transcode the stream." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8">
                                                                <select name="transcode_profile_id" id="transcode_profile_id" class="form-control" data-toggle="select2">
                                                                    <option <?php if (isset($rStream)) { if (intval($rStream["transcode_profile_id"]) == 0) { echo "selected "; } } ?>value="0">Transcoding Disabled</option>
                                                                    <?php foreach ($rTranscodeProfiles as $rProfile) { ?>
                                                                    <option <?php if (isset($rStream)) { if (intval($rStream["transcode_profile_id"]) == intval($rProfile["profile_id"])) { echo "selected "; } } ?>value="<?=$rProfile["profile_id"]?>"><?=$rProfile["profile_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
											<?php if (!isset($_GET["import"])) { ?>
                                            <div class="tab-pane" id="stream-map">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-3 col-form-label" for="custom_map">Custom Map</label>
                                                            <div class="col-md-9 input-group">
                                                                <input type="text" class="form-control" id="custom_map" name="custom_map" value="<?php if (isset($rStream)) { echo htmlspecialchars($rStream["custom_map"]); } ?>">
                                                                <div class="input-group-append">
                                                                        <button class="btn btn-primary waves-effect waves-light" id="load_maps" type="button"><i class="mdi mdi-magnify"></i></button>
                                                                    </div>
                                                            </div>
                                                        </div>
                                                        <div class="alert alert-warning bg-warning text-white border-0" role="alert">
                                                            Custom maps are advanced features and you should only modify these if you know what you're doing. Hit the search icon to map the streams. Once mapped, you can select them from the table below.
                                                        </div>
                                                        <table id="datatable-map" class="table table-borderless mb-0">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Type</th>
                                                                    <th>Information</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
											<?php } ?>
                                            <div class="tab-pane" id="auto-restart">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="days_to_restart">Days to Restart</label>
                                                            <div class="col-md-8">
                                                                <?php
                                                                $rAutoRestart = Array("days" => Array(), "at" => "06:00");
                                                                if (isset($rStream)) {
                                                                    if (strlen($rStream["auto_restart"])) {
                                                                        $rAutoRestart = json_decode($rStream["auto_restart"], True);
                                                                        if (!isset($rAutoRestart["days"])) { $rAutoRestart["days"] = Array(); }
                                                                        if (!isset($rAutoRestart["at"])) { $rAutoRestart["at"] = "06:00"; }
                                                                    }
                                                                } ?>
                                                                <select id="days_to_restart" name="days_to_restart[]" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                                                    <?php foreach (Array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday") as $rDay) { ?>
                                                                    <option value="<?=$rDay?>"<?php if (in_array($rDay, $rAutoRestart["days"])) { echo " selected"; } ?>><?=$rDay?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="time_to_restart">Time to Restart</label>
                                                            <div class="col-md-8">
                                                                <div class="input-group clockpicker" data-placement="top" data-align="top" data-autoclose="true">
                                                                    <input id="time_to_restart" name="time_to_restart" type="text" class="form-control" value="<?=$rAutoRestart["at"]?>">
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text"><i class="mdi mdi-clock-outline"></i></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php if (!isset($_GET["import"])) { ?>
                                            <div class="tab-pane" id="epg-options">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="epg_id">EPG Source</label>
                                                            <div class="col-md-8">
                                                                <select name="epg_id" id="epg_id" class="form-control" data-toggle="select2">
                                                                    <option <?php if (isset($rStream)) { if (intval($rStream["epg_id"]) == 0) { echo "selected "; } } ?>value="0">No EPG</option>
                                                                    <?php foreach ($rEPGSources as $rEPG) { ?>
                                                                    <option <?php if (isset($rStream)) { if (intval($rStream["epg_id"]) == $rEPG["id"]) { echo "selected "; } } ?>value="<?=$rEPG["id"]?>"><?=$rEPG["epg_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="channel_id">EPG Channel ID</label>
                                                            <div class="col-md-8">
                                                                <select name="channel_id" id="channel_id" class="form-control" data-toggle="select2">
                                                                <?php if (isset($rStream)) {
                                                                    foreach (json_decode($rEPGSources[intval($rStream["epg_id"])]["data"], True) as $rKey => $rEPGChannel) { ?>
                                                                    <option value="<?=$rKey?>"<?php if ($rStream["channel_id"] == $rKey) { echo " selected"; } ?>><?=$rEPGChannel["display_name"]?></option>
                                                                    <?php }
                                                                } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="epg_lang">EPG Language</label>
                                                            <div class="col-md-8">
                                                                <select name="epg_lang" id="epg_lang" class="form-control" data-toggle="select2">
                                                                <?php if (isset($rStream)) {
                                                                    foreach (json_decode($rEPGSources[intval($rStream["epg_id"])]["data"], True)[$rStream["channel_id"]]["langs"] as $rID => $rLang) { ?>
                                                                    <option value="<?=$rLang?>"<?php if ($rStream["epg_lang"] == $rLang) { echo " selected"; } ?>><?=$rLang?></option>
                                                                    <?php }
                                                                } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php } ?>
                                            <div class="tab-pane" id="load-balancing">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="servers">Server Tree</label>
                                                            <div class="col-md-8">
                                                                <div id="server_tree"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="on_demand">On Demand</label>
                                                            <div class="col-md-8">
                                                                <select id="on_demand" name="on_demand[]" class="form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                                                    <?php foreach($rServers as $rServerItem) { ?>
                                                                        <option value="<?=$rServerItem["id"]?>"<?php if (in_array($rServerItem["id"], $rOnDemand)) { echo " selected"; } ?>><?=$rServerItem["server_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="tv_archive_server_id">Timeshift Server</label>
                                                            <div class="col-md-8">
                                                                <select name="tv_archive_server_id" id="tv_archive_server_id" class="form-control" data-toggle="select2">
                                                                    <option value="0">Timeshift Disabled</option>
                                                                    <?php foreach ($rServers as $rServer) { ?>
                                                                    <option value="<?=$rServer["id"]?>"<?php if ((isset($rStream)) && ($rStream["tv_archive_server_id"] == $rServer["id"])) { echo " selected"; } ?>><?=$rServer["server_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="tv_archive_duration">Timeshift Days</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="tv_archive_duration" name="tv_archive_duration" value="<?php if (isset($rStream)) { echo $rStream["tv_archive_duration"]; } else { echo "0"; } ?>">
                                                                </select>
                                                            </div>
															<label class="col-md-4 col-form-label" for="restart_on_edit"><?php if (isset($rStream["id"])) { ?>Restart on Edit<?php } else { ?>Start Stream Now<?php } ?></label>
															<div class="col-md-2">
																<input name="restart_on_edit" id="restart_on_edit" type="checkbox" data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
															</div>
														</div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_stream" type="submit" class="btn btn-primary" value="<?php if (isset($rStream["id"])) { echo "Edit"; } else { echo "Add"; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>
                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
        <!-- file preview template -->
        <div class="d-none" id="uploadPreviewTemplate">
            <div class="card mt-1 mb-0 shadow-none border">
                <div class="p-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img data-dz-thumbnail class="avatar-sm rounded bg-light" alt="">
                        </div>
                        <div class="col pl-0">
                            <a href="javascript:void(0);" class="text-muted font-weight-bold" data-dz-name></a>
                            <p class="mb-0" data-dz-size></p>
                        </div>
                        <div class="col-auto">
                            <!-- Button -->
                            <a href="" class="btn btn-link btn-lg text-muted" data-dz-remove>
                                <i class="mdi mdi-close-circle"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 copyright text-center"><?=getFooter()?></div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables/dataTables.bootstrap4.js"></script>
        <script src="assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/datatables/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables/buttons.flash.min.js"></script>
        <script src="assets/libs/datatables/buttons.print.min.js"></script>
        <script src="assets/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="assets/libs/datatables/dataTables.select.min.js"></script>
        <script src="assets/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        var rEPG = <?=json_encode($rEPGJS)?>;
        var rSwitches = [];
        
        (function($) {
          $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
              if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
              } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
              }
            });
          };
        }(jQuery));
        
        function moveUp(elem) {
            if ($(elem).parent().parent().parent().prevAll().length > 0) {
                $(elem).parent().parent().parent().insertBefore($('.streams>div').eq($(elem).parent().parent().parent().prevAll().length-1));
            }
        }
        function moveDown(elem) {
            if ($(elem).parent().parent().parent().prevAll().length < $(".streams>div").length) {
                $(elem).parent().parent().parent().insertAfter($('.streams>div').eq($(elem).parent().parent().parent().prevAll().length+1));
            }
        }
        function addStream() {
            $(".stream-url:first").clone().appendTo(".streams");
            $(".stream-url:last label").html("Stream URL");
            $(".stream-url:last input").val("");
        }
        function removeStream(rField) {
            if ($('.stream-url').length > 1) {
                $(rField).parent().parent().parent().remove();
            } else {
                $(rField).parent().parent().find("#stream_source").val("");
            }
        }
        function selectEPGSource() {
            $("#channel_id").empty();
            $("#epg_lang").empty();
            if (rEPG[$("#epg_id").val()]) {
                $.each(rEPG[$("#epg_id").val()], function(key, data) {
                    $("#channel_id").append(new Option(data["display_name"], key, false, false));
                });
                selectEPGID();
            }
        }
        function selectEPGID() {
            $("#epg_lang").empty();
            if (rEPG[$("#epg_id").val()][$("#channel_id").val()]) {
                $.each(rEPG[$("#epg_id").val()][$("#channel_id").val()]["langs"], function(i, data) {
                    $("#epg_lang").append(new Option(data, data, false, false));
                });
            }
        }
        function reloadStream() {
            $("#datatable").DataTable().ajax.reload( null, false );
            setTimeout(reloadStream, 5000);
        }
        function api(rID, rServerID, rType) {
            if (rType == "delete") {
                if (confirm('Are you sure you want to delete this stream?') == false) {
                    return;
                }
            }
            $.getJSON("./api.php?action=stream&sub=" + rType + "&stream_id=" + rID + "&server_id=" + rServerID, function(data) {
                if (data.result == true) {
                    if (rType == "start") {
                        $.toast("Stream successfully started. It will take a minute or so before the stream becomes available.");
                    } else if (rType == "stop") {
                        $.toast("Stream successfully stopped.");
                    } else if (rType == "restart") {
                        $.toast("Stream successfully restarted. It will take a minute or so before the stream becomes available.");
                    } else if (rType == "delete") {
                        $("#stream-" + rID + "-" + rServerID).remove();
                        $.toast("Stream successfully deleted.");
                    }
                    $("#datatable").DataTable().ajax.reload( null, false );
                } else {
                    $.toast("An error occured while processing your request.");
                }
            }).fail(function() {
                $.toast("An error occured while processing your request.");
            });
        }
        function player(rID) {
            $.magnificPopup.open({
                items: {
                    src: "./player.php?type=live&id=" + rID,
                    type: 'iframe'
                }
            });
        }
        function setSwitch(switchElement, checkedBool) {
            if((checkedBool && !switchElement.isChecked()) || (!checkedBool && switchElement.isChecked())) {
                switchElement.setPosition(true);
                switchElement.handleOnchange(true);
            }
        }
        $(document).ready(function() {
            $('select').select2({width: '100%'});
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
              var switchery = new Switchery(html);
              window.rSwitches[$(html).attr("id")] = switchery;
            });
            $("#epg_id").on("select2:select", function(e) { 
                selectEPGSource();
            });
            $("#channel_id").on("select2:select", function(e) { 
                selectEPGID();
            });
            
            $(".clockpicker").clockpicker();
            
            $('#server_tree').jstree({ 'core' : {
                'check_callback': function (op, node, parent, position, more) {
                    switch (op) {
                        case 'move_node':
                            if (node.id == "source") { return false; }
                            return true;
                    }
                },
                'data' : <?=json_encode($rServerTree)?>
            }, "plugins" : [ "dnd" ]
            });
            
            $("#direct_source").change(function() {
                evaluateDirectSource();
            });
            function evaluateDirectSource() {
                $(["read_native", "gen_timestamps", "stream_all", "allow_record", "rtmp_output", "delay_minutes", "custom_ffmpeg", "probesize_ondemand", "user_agent", "http_proxy", "cookie", "headers", "transcode_profile_id", "custom_map", "days_to_restart", "time_to_restart", "epg_id", "epg_lang", "channel_id", "on_demand", "tv_archive_duration", "tv_archive_server_id", "restart_on_edit"]).each(function(rID, rElement) {
                    if ($(rElement)) {
                        if ($("#direct_source").is(":checked")) {
                            if (window.rSwitches[rElement]) {
                                setSwitch(window.rSwitches[rElement], false);
                                window.rSwitches[rElement].disable();
                            } else {
                                $("#" + rElement).prop("disabled", true);
                            }
                        } else {
                            if (window.rSwitches[rElement]) {
                                window.rSwitches[rElement].enable();
                            } else {
                                $("#" + rElement).prop("disabled", false);
                            }
                        }
                    }
                });
            }
            $("#load_maps").click(function() {
                rURL = $("#stream_source:eq(0)").val();
                if (rURL.length > 0) {
                    $.toast("Stream map has started, this can take a while depending on how many streams are present.");
                    $("#datatable-map").DataTable().clear().draw();
                    $.getJSON("./api.php?action=map_stream&stream=" + encodeURIComponent(rURL), function(data) {
                        $(data.streams).each(function(id, array) {
                            if (array.codec_type == "video") {
                                rString = array.codec_name.toUpperCase();
                                if (array.profile) {
                                    rString += " (" + array.profile + ")";
                                }
                                if (array.pix_fmt) {
                                    rString += " - " + array.pix_fmt;
                                }
                                if ((array.width) && (array.height)) {
                                    rString += " - " + array.width + "x" + array.height;
                                }
                                if ((array.avg_frame_rate) && (array.avg_frame_rate.split("/")[0] > 0)) {
                                    rString += " - " + array.avg_frame_rate.split("/")[0] + " fps";
                                }
                                $("#datatable-map").DataTable().row.add([array.index, "Video", rString]);
                            } else if (array.codec_type == "audio") {
                                rString = array.codec_name.toUpperCase();
                                if (array.tags.language) {
                                    rString += " - " + array.tags.language.toUpperCase();
                                }
                                if ((array.sample_rate) && (array.sample_rate > 0)) {
                                    rString += " - " + array.sample_rate + " Hz";
                                }
                                if (array.channel_layout) {
                                    rString += " - " + array.channel_layout;
                                }
                                if (array.sample_fmt) {
                                    rString += " - " + array.sample_fmt;
                                }
                                if ((array.bit_rate) || (array.tags.variant_bitrate)) {
                                    if (array.bit_rate) {
                                        rString += " - " + Math.ceil(array.bit_rate / 1000) + " kb/s";
                                    } else {
                                        rString += " - " + Math.ceil(array.tags.variant_bitrate / 1000) + " vbr";
                                    }
                                }
                                if (array.disposition.visual_impaired) {
                                    rString += " - Visual Impaired";
                                }
                                if (array.disposition.hearing_impaired) {
                                    rString += " - Hearing Impaired";
                                }
                                if (array.disposition.dub) {
                                    rString += " - Dub";
                                }
                                $("#datatable-map").DataTable().row.add([array.index, "Audio", rString]);
                            } else if (array.codec_type == "subtitle") {
                                rString = array.codec_long_name.toUpperCase();
                                if (array.tags.language) {
                                    rString += " - " + array.tags.language.toUpperCase();
                                }
                                $("#datatable-map").DataTable().row.add([array.index, "Subtitle", rString]);
                            } else {
                                rString = array.codec_long_name.toUpperCase();
                                if (array.tags.variant_bitrate) {
                                    rString += " - " + Math.ceil(array.tags.variant_bitrate / 1000) + " vbr";
                                }
                                $("#datatable-map").DataTable().row.add([array.index, "Data", rString]);
                            }
                        });
                        $("#datatable-map").DataTable().draw();
                        if (data.streams.length > 0) {
                            $.toast("Stream map complete. Please select relevant streams from the table.");
                        } else {
                            $.toast("Stream mapping didn't return any information.");
                        }
                    }).fail(function() {
                        $.toast("An error occured while mapping streams.");
                    });
                }
            });
            
            $("#stream_form").submit(function(e){
                <?php if (!isset($_GET["import"])) { ?>
                if ($("#stream_display_name").val().length == 0) {
                    e.preventDefault();
                    $.toast("Enter a stream name.");
                }
                <?php } else { ?>
                if ($("#m3u_file").val().length == 0) {
                    e.preventDefault();
                    $.toast("Please select a M3U file to upload.");
                }
                <?php } ?>
                $("#server_tree_data").val(JSON.stringify($('#server_tree').jstree(true).get_json('#', {flat:true})));
            });
            
            $(window).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
            
            $("#probesize_ondemand").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#delay_minutes").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#tv_archive_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
            <?php if (isset($rStream["id"])) { ?>
            $("#datatable").DataTable({
                ordering: false,
                paging: false,
                searching: false,
                processing: true,
                serverSide: true,
                bInfo: false,
                ajax: {
                    url: "./table_search.php",
                    "data": function(d) {
                        d.id = "streams";
                        d.stream_id = <?=$rStream["id"]?>;
                    }
                },
                columnDefs: [
                    {"className": "dt-center", "targets": [3,4,5,6]},
                    {"visible": false, "targets": [0,1,2,7]}
                ],
            });
            setTimeout(reloadStream, 5000);
            <?php } ?>
            $("#datatable-map").DataTable({
                paging: false,
                searching: false,
                bInfo: false,
                columnDefs: [
                    {"className": "dt-center", "targets": [0,1]},
                ],
                select: {
                    style: 'multi'
                }
            }).on('select', function (e, dt, type, indexes) {
                var i; var rMap = "";
                for (i = 0; i < $("#datatable-map").DataTable().rows('.selected').data().length; i++) {
                    rMap += "-map 0:" + $("#datatable-map").DataTable().rows('.selected').data()[i][0] + " ";
                }
                $("#custom_map").val(rMap.trim());
            });
            evaluateDirectSource();
        });
        </script>
    </body>
</html>