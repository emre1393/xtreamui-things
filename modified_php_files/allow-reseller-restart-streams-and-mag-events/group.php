<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR ((!hasPermissions("adv", "add_group")) && (!hasPermissions("adv", "edit_group")))) { exit; }

$rAdvPermissions = Array(
	Array("add_rtmp", $_["permission_add_rtmp"], $_["permission_add_rtmp_text"]),
	Array("add_bouquet", $_["permission_add_bouquet"], $_["permission_add_bouquet_text"]),
	Array("add_cat", $_["permission_add_cat"], $_["permission_add_cat_text"]),
	Array("add_e2", $_["permission_add_e2"], $_["permission_add_e2_text"]),
	Array("add_epg", $_["permission_add_epg"], $_["permission_add_epg_text"]),
	Array("add_episode", $_["permission_add_episode"], $_["permission_add_episode_text"]),
	Array("add_group", $_["permission_add_group"], $_["permission_add_group_text"]),
	Array("add_mag", $_["permission_add_mag"], $_["permission_add_mag_text"]),
	Array("add_movie", $_["permission_add_movie"], $_["permission_add_movie_text"]),
	Array("add_packages", $_["permission_add_packages"], $_["permission_add_packages_text"]),
	Array("add_radio", $_["permission_add_radio"], $_["permission_add_radio_text"]),
	Array("add_reguser", $_["permission_add_reguser"], $_["permission_add_reguser_text"]),
	Array("add_server", $_["permission_add_server"], $_["permission_add_server_text"]),
	Array("add_stream", $_["permission_add_stream"], $_["permission_add_stream_text"]),
	Array("tprofile", $_["permission_tprofile"], $_["permission_tprofile_text"]),
	Array("add_series", $_["permission_add_series"], $_["permission_add_series_text"]),
	Array("add_user", $_["permission_add_user"], $_["permission_add_user_text"]),
	Array("block_ips", $_["permission_block_ips"], $_["permission_block_ips_text"]),
    Array("block_isps", $_["permission_block_isps"], $_["permission_block_isps_text"]),
	Array("block_uas", $_["permission_block_uas"], $_["permission_block_uas_text"]),
	Array("create_channel", $_["permission_create_channel"], $_["permission_create_channel_text"]),
	Array("edit_bouquet", $_["permission_edit_bouquet"], $_["permission_edit_bouquet_text"]),
	Array("edit_cat", $_["permission_edit_cat"], $_["permission_edit_cat_text"]),
	Array("channel_order", $_["permission_channel_order"], $_["permission_channel_order_text"]),
	Array("edit_cchannel", $_["permission_edit_cchannel"], $_["permission_edit_cchannel_text"]),
	Array("edit_e2", $_["permission_edit_e2"], $_["permission_edit_e2_text"]),
	Array("epg_edit", $_["permission_epg_edit"], $_["permission_epg_edit_text"]),
	Array("edit_episode", $_["permission_edit_episode"], $_["permission_edit_episode_text"]),
	Array("folder_watch_settings", $_["permission_folder_watch_settings"], $_["permission_folder_watch_settings_text"]),
	Array("settings", $_["permission_settings"], $_["permission_settings_text"]),
	Array("edit_group", $_["permission_edit_group"], $_["permission_edit_group_text"]),
	Array("edit_mag", $_["permission_edit_mag"], $_["permission_edit_mag_text"]),
	Array("edit_movie", $_["permission_edit_movie"], $_["permission_edit_movie_text"]),
	Array("edit_package", $_["permission_edit_package"], $_["permission_edit_package_text"]),
	Array("edit_radio", $_["permission_edit_radio"], $_["permission_edit_radio_text"]),
	Array("edit_reguser", $_["permission_edit_reguser"], $_["permission_edit_reguser_text"]),
	Array("edit_server", $_["permission_edit_server"], $_["permission_edit_server_text"]),
	Array("edit_stream", $_["permission_edit_stream"], $_["permission_edit_stream_text"]),
	Array("edit_series", $_["permission_edit_series"], $_["permission_edit_series_text"]),
	Array("edit_user", $_["permission_edit_user"], $_["permission_edit_user_text"]),
	Array("fingerprint", $_["permission_fingerprint"], $_["permission_fingerprint_text"]),
	Array("import_episodes", $_["permission_import_episodes"], $_["permission_import_episodes_text"]),
	Array("import_movies", $_["permission_import_movies"], $_["permission_import_movies_text"]),
	Array("import_streams", $_["permission_import_streams"], $_["permission_import_streams_text"]),
	Array("database", $_["permission_database"], $_["permission_database_text"]),
	Array("mass_delete", $_["permission_mass_delete"], $_["permission_mass_delete_text"]),
	Array("mass_sedits_vod", $_["permission_mass_sedits_vod"], $_["permission_mass_sedits_vod_text"]),
	Array("mass_sedits", $_["permission_mass_sedits"], $_["permission_mass_sedits_text"]),
	Array("mass_edit_users", $_["permission_mass_edit_users"], $_["permission_mass_edit_users_text"]),
	Array("mass_edit_streams", $_["permission_mass_edit_streams"], $_["permission_mass_edit_streams_text"]),
	Array("mass_edit_radio", $_["permission_mass_edit_radio"], $_["permission_mass_edit_radio_text"]),
	Array("ticket", $_["permission_ticket"], $_["permission_ticket_text"]),
	Array("subreseller", $_["permission_subreseller"], $_["permission_subreseller_text"]),
	Array("stream_tools", $_["permission_stream_tools"], $_["permission_stream_tools_text"]),
	Array("bouquets", $_["permission_bouquets"], $_["permission_bouquets_text"]),
	Array("categories", $_["permission_categories"], $_["permission_categories_text"]),
	Array("client_request_log", $_["permission_client_request_log"], $_["permission_client_request_log_text"]),
	Array("connection_logs", $_["permission_connection_logs"], $_["permission_connection_logs_text"]),
	Array("manage_cchannels", $_["permission_manage_cchannels"], $_["permission_manage_cchannels_text"]),
	Array("credits_log", $_["permission_credits_log"], $_["permission_credits_log_text"]),
	Array("index", $_["permission_index"], $_["permission_index_text"]),
	Array("manage_e2", $_["permission_manage_e2"], $_["permission_manage_e2_text"]),
	Array("epg", $_["permission_epg"], $_["permission_epg_text"]),
	Array("folder_watch", $_["permission_folder_watch"], $_["permission_folder_watch_text"]),
	Array("folder_watch_output", $_["permission_folder_watch_output"], $_["permission_folder_watch_output_text"]),
	Array("mng_groups", $_["permission_mng_groups"], $_["permission_mng_groups_text"]),
	Array("live_connections", $_["permission_live_connections"], $_["permission_live_connections_text"]),
	Array("login_logs", $_["permission_login_logs"], $_["permission_login_logs_text"]),
	Array("manage_mag", $_["permission_manage_mag"], $_["permission_manage_mag_text"]),
	Array("manage_events", $_["permission_manage_events"], $_["permission_manage_events_text"]),
	Array("movies", $_["permission_movies"], $_["permission_movies_text"]),
	Array("mng_packages", $_["permission_mng_packages"], $_["permission_mng_packages_text"]),
	Array("player", $_["permission_player"], $_["permission_player_text"]),
	Array("process_monitor", $_["permission_process_monitor"], $_["permission_process_monitor_text"]),
	Array("radio", $_["permission_radio"], $_["permission_radio_text"]),
	Array("mng_regusers", $_["permission_mng_regusers"], $_["permission_mng_regusers_text"]),
	Array("reg_userlog", $_["permission_reg_userlog"], $_["permission_reg_userlog_text"]),
	Array("rtmp", $_["permission_rtmp"], $_["permission_rtmp_text"]),
	Array("servers", $_["permission_servers"], $_["permission_servers_text"]),
	Array("stream_errors", $_["permission_stream_errors"], $_["permission_stream_errors_text"]),
	Array("streams", $_["permission_streams"], $_["permission_streams_text"]),
	Array("subresellers", $_["permission_subresellers"], $_["permission_subresellers_text"]),
	Array("manage_tickets", $_["permission_manage_tickets"], $_["permission_manage_tickets_text"]),
	Array("tprofiles", $_["permission_tprofiles"], $_["permission_tprofiles_text"]),
	Array("series", $_["permission_series"], $_["permission_series_text"]),
	Array("users", $_["permission_users"], $_["permission_users_text"]),
	Array("episodes", $_["permission_episodes"], $_["permission_episodes_text"]),
	Array("edit_tprofile", $_["permission_edit_tprofile"], $_["permission_edit_tprofile_text"]),
	Array("folder_watch_add", $_["permission_folder_watch_add"], $_["permission_folder_watch_add_text"])
);

if (isset($_POST["submit_group"])) {
    if (isset($_POST["edit"])) {
		if (!hasPermissions("adv", "edit_group")) { exit; }
        $rArray = getMemberGroup($_POST["edit"]);
		$rGroup = $rArray;
        unset($rArray["group_id"]);
    } else {
		if (!hasPermissions("adv", "add_group")) { exit; }
        $rArray = Array("group_name" => "", "group_color" => "", "is_banned" => 0, "is_admin" => 0, "is_reseller" => 0, "total_allowed_gen_in" => "day", "total_allowed_gen_trials" => 0, "minimum_trial_credits" => 0, "can_delete" => 1, "delete_users" => 0, "allowed_pages" => "", "reseller_force_server" => "", "create_sub_resellers_price" => 0, "create_sub_resellers" => 0, "alter_packages_ids" => 0, "alter_packages_prices" => 0, "reseller_client_connection_logs" => 0, "reseller_assign_pass" => 0, "allow_change_pass" => 0, "allow_import" => 0, "allow_export" => 0, "reseller_trial_credit_allow" => 0, "edit_mac" => 0, "edit_isplock" => 0, "reset_stb_data" => 0, "reseller_bonus_package_inc" => 0, "allow_download" => 1,  "reseller_controls_streams" => 0); // added this into array ",  "reseller_controls_streams" => 0"
    }
    if (strlen($_POST["group_name"]) == 0) {
        $_STATUS = 1;
    }
    foreach (Array("is_admin", "is_reseller", "is_banned", "delete_users", "create_sub_resellers", "allow_change_pass", "allow_download", "reseller_client_connection_logs", "reset_stb_data", "allow_import", "reseller_controls_streams") as $rSelection) {
        if (isset($_POST[$rSelection])) {
            $rArray[$rSelection] = 1;
            unset($_POST[$rSelection]);
        } else {
            $rArray[$rSelection] = 0;
        }
    }
	if ((!$rArray["can_delete"]) && (isset($_POST["edit"]))) {
		$rArray["is_admin"] = $rGroup["is_admin"];
		$rArray["is_reseller"] = $rGroup["is_reseller"];
	}
	$rArray["allowed_pages"] = array_values(json_decode($_POST["permissions_selected"], True));
    unset($_POST["permissions_selected"]);
    if (!isset($_STATUS)) {
        foreach($_POST as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
        }
        $rCols = "`".ESC(implode('`,`', array_keys($rArray)))."`";
        foreach (array_values($rArray) as $rValue) {
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
            $rCols = "`group_id`,".$rCols;
            $rValues = ESC($_POST["edit"]).",".$rValues;
        }
        $rQuery = "REPLACE INTO `member_groups`(".$rCols.") VALUES(".$rValues.");";
        if ($db->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
            } else {
                $rInsertID = $db->insert_id;
            }
            header("Location: ./group.php?id=".$rInsertID); exit;
        } else {
            $_STATUS = 2;
        }
    }
}

if (isset($_GET["id"])) {
    $rGroup = getMemberGroup($_GET["id"]);
    if ((!$rGroup) OR (!hasPermissions("adv", "edit_group"))) {
        exit;
    }
} else if (!hasPermissions("adv", "add_group")) {
	exit;
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout-ext"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout-ext"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./groups.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> <?=$_["back_to_groups"]?></li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rGroup)) { echo $_["edit_group"]; } else { echo $_["add_group"]; } ?></h4>
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
							<?=$_["group_success"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
							<?=$_["generic_fail"]?>
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./group.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="group_form" data-parsley-validate="">
                                    <?php if (isset($rGroup)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rGroup["group_id"]?>" />
                                    <?php } ?>
									<input type="hidden" name="permissions_selected" id="permissions_selected" value="" />
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#group-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["details"]?></span>
                                                </a>
                                            </li>
											<li class="nav-item">
                                                <a href="#reseller" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-badge-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["reseller_permissions"]?></span>
                                                </a>
                                            </li>
											<?php if ((!isset($rGroup)) OR ($rGroup["can_delete"])) { ?>
											<li class="nav-item">
                                                <a href="#permissions" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-badge-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline"><?=$_["admin_permissions"]?></span>
                                                </a>
                                            </li>
											<?php } ?>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="group-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="group_name"><?=$_["group_name"]?></label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="group_name" name="group_name" value="<?php if (isset($rGroup)) { echo htmlspecialchars($rGroup["group_name"]); } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_admin"><?=$_["is_admin"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="is_admin" id="is_admin" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["is_admin"]) { echo "checked "; } if (!$rGroup["can_delete"]) { echo "disabled "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="is_reseller"><?=$_["is_reseller"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="is_reseller" id="is_reseller" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["is_reseller"]) { echo "checked "; } if (!$rGroup["can_delete"]) { echo "disabled "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_banned"><?=$_["is_banned"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="is_banned" id="is_banned" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["is_banned"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="list-inline-item float-right">
                                                        <input name="submit_group" type="submit" class="btn btn-primary" value="<?php if (isset($rGroup)) { echo $_["edit"]; } else { echo $_["add"]; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
											<div class="tab-pane" id="reseller">
                                                <div class="row">
                                                    <div class="col-12">
														<p class="sub-header">
															<?=$_["permissions_info"]?>
                                                        </p>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="total_allowed_gen_trials"><?=$_["allowed_trials"]?></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="total_allowed_gen_trials" name="total_allowed_gen_trials" value="<?php if (isset($rGroup)) { echo intval($rGroup["total_allowed_gen_trials"]); } else { echo "0"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="total_allowed_gen_in"><?=$_["allowed_trials_in"]?></label>
                                                            <div class="col-md-2">
                                                                <select name="total_allowed_gen_in" id="total_allowed_gen_in" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach (Array("Day", "Month") as $rOption) { ?>
                                                                    <option <?php if (isset($rGroup)) { if ($rGroup["total_allowed_gen_in"] == strtolower($rOption)) { echo "selected "; } } ?>value="<?=strtolower($rOption)?>"><?=$rOption?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="create_sub_resellers"><?=$_["can_create_subresellers"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="create_sub_resellers" id="create_sub_resellers" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["create_sub_resellers"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="create_sub_resellers_price"><?=$_["subreseller_price"]?></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="create_sub_resellers_price" name="create_sub_resellers_price" value="<?php if (isset($rGroup)) { echo htmlspecialchars($rGroup["create_sub_resellers_price"]); } else { echo "0"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="allow_change_pass"><?=$_["can_change_logins"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="allow_change_pass" id="allow_change_pass" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["allow_change_pass"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="allow_download"><?=$_["can_download_playlist"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="allow_download" id="allow_download" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["allow_download"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="reset_stb_data"><?=$_["can_view_vod_streams"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="reset_stb_data" id="reset_stb_data" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["reset_stb_data"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="reseller_client_connection_logs"><?=$_["can_view_live_connections"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="reseller_client_connection_logs" id="reseller_client_connection_logs" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["reseller_client_connection_logs"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
														<div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="delete_users"><?=$_["can_delete_users"]?></label>
                                                            <div class="col-md-2">
                                                                <input name="delete_users" id="delete_users" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["delete_users"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="minimum_trial_credits"><?=$_["minimum_credit_for_trials"]?></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="minimum_trial_credits" name="minimum_trial_credits" value="<?php if (isset($rGroup)) { echo intval($rGroup["minimum_trial_credits"]); } else { echo "0"; } ?>" required data-parsley-trigger="change">
                                                            </div>
                                                        </div>
                                                        <!-- Added a permisson toggle here for "reseller restart button" -->
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="reseller_controls_streams">Can Control Streams</label>
                                                            <div class="col-md-2">
                                                                <input name="reseller_controls_streams" id="reseller_controls_streams" type="checkbox" <?php if (isset($rGroup)) { if ($rGroup["reseller_controls_streams"]) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_group" type="submit" class="btn btn-primary" value="<?php if (isset($rGroup)) { echo $_["edit"]; } else { echo $_["add"]; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
											<div class="tab-pane" id="permissions">
                                                <div class="row">
                                                    <div class="col-12">
														<p class="sub-header">
															<?=$_["advanced_permissions_info"]?>
                                                        </p>
                                                        <div class="form-group row mb-4">
                                                            <table id="datatable-permissions" class="table table-borderless mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th style="display:none;"><?=$_["id"]?></th>
                                                                        <th><?=$_["permission"]?></th>
                                                                        <th><?=$_["description"]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($rAdvPermissions as $rPermission) { ?>
                                                                    <tr<?php if ((isset($rGroup)) & (in_array($rPermission[0], json_decode($rGroup["allowed_pages"], True)))) { echo " class='selected selectedfilter ui-selected'"; } ?>>
                                                                        <td style="display:none;"><?=$rPermission[0]?></td>
                                                                        <td><?=$rPermission[1]?></td>
                                                                        <td><?=$rPermission[2]?></td>
                                                                    </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
													<li class="next list-inline-item">
														<a href="javascript: void(0);" onClick="selectAll()" class="btn btn-info"><?=$_["select_all"]?></a>
														<a href="javascript: void(0);" onClick="selectNone()" class="btn btn-warning"><?=$_["deselect_all"]?></a>
													</li>
                                                    <li class="next list-inline-item float-right">
                                                        <input name="submit_group" type="submit" class="btn btn-primary" value="<?php if (isset($rGroup)) { echo $_["edit"]; } else { echo $_["add"]; } ?>" />
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
        <script src="assets/libs/jquery-ui/jquery-ui.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
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
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
		var rPermissions = [];

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
        
        function selectAll() {
            $("#datatable-permissions tr").each(function() {
                if (!$(this).hasClass('selected')) {
                    $(this).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rPermissions.push(parseInt($(this).find("td:eq(0)").html()));
                    }
                }
            });
        }
        
        function selectNone() {
            $("#datatable-permissions tr").each(function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                    if ($(this).find("td:eq(0)").html()) {
                        window.rPermissions.splice(parseInt($.inArray($(this).find("td:eq(0)").html()), window.rPermissions), 1);
                    }
                }
            });
        }
        
        $(document).ready(function() {
            $('select.select2').select2({width: '100%'})
            $(".js-switch").each(function (index, element) {
                var init = new Switchery(element);
            });
            
            $(document).keypress(function(event){
                if(event.which == 13 && event.target.nodeName != "TEXTAREA") return false;
            });
			
			$("#datatable-permissions").DataTable({
                "rowCallback": function(row, data) {
                    if ($.inArray(data[0], window.rPermissions) !== -1) {
                        $(row).addClass("selected");
                    }
                },
				order: [[ 1, "asc" ]],
                paging: false,
                bInfo: false,
                searching: false
            });
            $("#datatable-permissions").selectable({
                filter: 'tr',
                selected: function (event, ui) {
                    if ($(ui.selected).hasClass('selectedfilter')) {
                        $(ui.selected).removeClass('selectedfilter').removeClass('ui-selected').removeClass("selected");
                        window.rPermissions.splice(parseInt($.inArray($(ui.selected).find("td:eq(0)").html()), window.rPermissions), 1);
                    } else {            
                        $(ui.selected).addClass('selectedfilter').addClass('ui-selected').addClass("selected");
                        window.rPermissions.push(parseInt($(ui.selected).find("td:eq(0)").html()));
                    }
                }
            });
			$("#datatable-permissions_wrapper").css("width","100%");
			$("#datatable-permissions").css("width","100%");
			$("#group_form").submit(function(e){
                var rPermissions = [];
                $("#datatable-permissions tr.selected").each(function() {
                    rPermissions.push($(this).find("td:eq(0)").html());
                });
                $("#permissions_selected").val(JSON.stringify(rPermissions));
            });

            $("#max_connections").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#trial_credits").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#trial_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#official_credits").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("#official_duration").inputFilter(function(value) { return /^\d*$/.test(value); });
			$("#total_allowed_gen_trials").inputFilter(function(value) { return /^\d*$/.test(value); });
			$("#minimum_trial_credits").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
        });
        </script>
    </body>
</html>