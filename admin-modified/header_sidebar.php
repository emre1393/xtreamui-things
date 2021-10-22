<?php if (count(get_included_files()) == 1) { exit; } ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Xtream UI</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="robots" content="noindex,nofollow">
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <link href="assets/libs/jquery-nice-select/nice-select.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/switchery/switchery.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/select2/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/datatables/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/datatables/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/datatables/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/datatables/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/jquery-toast/jquery.toast.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/treeview/style.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/nestable2/jquery.nestable.min.css" rel="stylesheet" />
        <link href="assets/libs/magnific-popup/magnific-popup.css" rel="stylesheet" type="text/css" />
        <link href="assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
		<?php if (!$rAdminSettings["dark_mode"]) { ?>
        <link href="assets/css/app_sidebar.css" rel="stylesheet" type="text/css" />
		<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css" />
		<?php } else { ?>
		<link href="assets/css/app_sidebar.dark.css" rel="stylesheet" type="text/css" />
		<link href="assets/css/bootstrap.dark.css" rel="stylesheet" type="text/css" />
		<?php } ?>
    </head>
    <body class="<?php if (!$rAdminSettings["dark_mode"]) { echo "topbar-dark left-side-menu-light "; } ?><?php if (!$rAdminSettings["expanded_sidebar"]) { echo 'enlarged" data-keep-enlarged="true"'; } else { echo '"' ;} ?>>
        <!-- Begin page -->
        <div id="wrapper">
            <!-- Topbar Start -->
            <div class="navbar-custom">
                <ul class="list-unstyled topnav-menu float-right mb-0">
					<li class="notification-list username">
						<a class="nav-link text-white waves-effect" href="./edit_profile.php" role="button" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?=$_["edit_profile"]?>">
							<?=$rUserInfo["username"]?>
						</a>
					</li>
                    <?php if (($rServerError) && ($rPermissions["is_admin"]) && (hasPermissions("adv", "servers"))) { ?>
                    <li class="notification-list">
                        <a href="./servers.php" class="nav-link right-bar-toggle waves-effect text-warning">
                            <i class="mdi mdi-wifi-strength-off noti-icon"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if ($rPermissions["is_reseller"]) { ?>
                    <li class="notification-list">
                        <a class="nav-link text-white waves-effect" href="#" role="button">
                            <i class="mdi mdi-coins noti-icon"></i>
                            <?php if (floor($rUserInfo["credits"]) == $rUserInfo["credits"]) {
                                echo number_format($rUserInfo["credits"], 0);
                            } else {
                                echo number_format($rUserInfo["credits"], 2);
                            } ?>
                        </a>
                    </li>
                    <?php }
                    if ($rPermissions["is_admin"]) {
					if ((hasPermissions("adv", "settings")) OR (hasPermissions("adv", "database")) OR (hasPermissions("adv", "block_ips")) OR (hasPermissions("adv", "block_isps")) OR (hasPermissions("adv", "block_uas")) OR (hasPermissions("adv", "categories")) OR (hasPermissions("adv", "channel_order")) OR (hasPermissions("adv", "epg")) OR (hasPermissions("adv", "folder_watch")) OR (hasPermissions("adv", "mng_groups")) OR (hasPermissions("adv", "mass_delete")) OR (hasPermissions("adv", "mng_packages")) OR (hasPermissions("adv", "process_monitor")) OR (hasPermissions("adv", "rtmp")) OR (hasPermissions("adv", "subresellers")) OR (hasPermissions("adv", "tprofiles"))) { ?>
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect text-white" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="fe-settings noti-icon"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right profile-dropdown">
							<?php if ((hasPermissions("adv", "settings")) OR (hasPermissions("adv", "database"))) { ?>
							<a href="./settings.php" class="dropdown-item notify-item"><span><?=$_["settings"]?></span></a>
							<?php }
							if (hasPermissions("adv", "block_ips")) { ?>
							<a href="./ips.php" class="dropdown-item notify-item"><span><?=$_["blocked_ips"]?></span></a>
							<?php }
                            if (hasPermissions("adv", "block_isps")) { ?>
							<a href="./isps.php" class="dropdown-item notify-item"><span><?=$_["blocked_isps"]?></span></a>
							<?php }
							if (hasPermissions("adv", "block_uas")) { ?>
							<a href="./useragents.php" class="dropdown-item notify-item"><span><?=$_["blocked_uas"]?></span></a>
							<?php }
							if (hasPermissions("adv", "categories")) { ?>
							<a href="./stream_categories.php" class="dropdown-item notify-item"><span><?=$_["categories"]?></span></a>
							<?php }
							if (hasPermissions("adv", "channel_order")) { ?>
							<a href="./channel_order.php" class="dropdown-item notify-item"><span><?=$_["channel_order"]?></span></a>
							<?php }
							if (hasPermissions("adv", "epg")) { ?>
							<a href="./epgs.php" class="dropdown-item notify-item"><span><?=$_["epgs"]?></span></a>
							<?php }
							if (hasPermissions("adv", "folder_watch")) { ?>
							<a href="./watch.php" class="dropdown-item notify-item"><span><?=$_["folder_watch"]?></span></a>
							<?php }
							if (hasPermissions("adv", "mng_groups")) { ?>
							<a href="./groups.php" class="dropdown-item notify-item"><span><?=$_["groups"]?></span></a>
							<?php }
							if (hasPermissions("adv", "mass_delete")) { ?>
							<a href="./mass_delete.php" class="dropdown-item notify-item"><span><?=$_["mass_delete"]?></span></a>
							<?php }
							if (hasPermissions("adv", "mng_packages")) { ?>
							<a href="./packages.php" class="dropdown-item notify-item"><span><?=$_["packages"]?></span></a>
							<?php }
							if (hasPermissions("adv", "process_monitor")) { ?>
							<a href="./process_monitor.php?server=<?=$_INFO["server_id"]?>" class="dropdown-item notify-item"><span><?=$_["process_monitor"]?></span></a>
							<?php }
							if (hasPermissions("adv", "rtmp")) { ?>
							<a href="./rtmp_ips.php" class="dropdown-item notify-item"><span><?=$_["rtmp_ips"]?></span></a>
							<?php }
							if (hasPermissions("adv", "subresellers")) { ?>
							<a href="./subresellers.php" class="dropdown-item notify-item"><span><?=$_["subresellers"]?></span></a>
							<?php }
							if (hasPermissions("adv", "tprofiles")) { ?>
							<a href="./profiles.php" class="dropdown-item notify-item"><span><?=$_["transcode_profiles"]?></span></a>
							<?php } ?>
						</div>
                    </li>
                    <?php }
					} ?>
                    <li class="notification-list">
                        <a href="./logout.php" class="nav-link right-bar-toggle waves-effect text-white">
                            <i class="fe-power noti-icon"></i>
                        </a>
                    </li>
                </ul>
                <!-- LOGO -->
                <div class="logo-box">
                    <a href="<?php if ($rPermissions["is_admin"]) { ?>dashboard.php<?php } else { ?>reseller.php<?php } ?>" class="logo text-center">
                        <span class="logo-lg">
                            <img src="assets/images/logo.png" alt="" height="26">
                        </span>
                        <span class="logo-sm">
                            <img src="assets/images/logo-sm.png" alt="" height="26">
                        </span>
                    </a>
                </div>

                <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
                    <li>
                        <button class="button-menu-mobile waves-effect text-white">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </li>
                </ul>
            </div>
            <!-- end Topbar -->
            <!-- ========== Left Sidebar Start ========== -->
            <div class="left-side-menu">
                <div class="slimscroll-menu">
                    <!--- Sidemenu -->
                    <div id="sidebar-menu">
                        <ul class="metismenu" id="side-menu">
                            <li>
                                <a href="./<?php if ($rPermissions["is_admin"]) { ?>dashboard.php<?php } else { ?>reseller.php<?php } ?>"><i class="la la-dashboard"></i><span><?=$_["dashboard"]?></span></a>
                            </li>
                            <?php if (($rPermissions["is_reseller"]) && ($rPermissions["reseller_client_connection_logs"])) { ?>
                            <li>
								<a href="#"><i class="la la-exchange"></i><span><?=$_["connections"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <li><a href="./live_connections.php"><?=$_["live_connections"]?></a></li>
                                    <li><a href="./user_activity.php"><?=$_["activity_logs"]?></a></li>
                                </ul>
                            </li>
                            <?php }
                            if ($rPermissions["is_admin"]) {
							if ((hasPermissions("adv", "servers")) OR (hasPermissions("adv", "add_server")) OR (hasPermissions("adv", "live_connections")) OR (hasPermissions("adv", "connection_logs"))) { ?>
                            <li>
                                <a href="#"><i class="la la-server"></i><span><?=$_["servers"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
									<?php if (hasPermissions("adv", "add_server")) { ?>
                                    <li><a href="./server.php"><?=$_["add_existing_lb"]?></a></li>
                                    <li><a href="./install_server.php"><?=$_["install_load_balancer"]?></a></li>
									<?php }
									if (hasPermissions("adv", "servers")) { ?>
                                    <li><a href="./servers.php"><?=$_["manage_servers"]?></a></li>
									<?php }
                                    if ((hasPermissions("adv", "live_connections")) OR (hasPermissions("adv", "connection_logs"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									if (hasPermissions("adv", "live_connections")) { ?>
                                    <li><a href="./live_connections.php"><?=$_["live_connections"]?></a></li>
									<?php }
									if (hasPermissions("adv", "connection_logs")) { ?>
                                    <li><a href="./user_activity.php"><?=$_["activity_logs"]?></a></li>
                                    <li><a href="./user_ips.php">Line IP Usage</a></li>
									<?php } ?>
                                </ul>
                            </li>
                            <?php }
							if ((hasPermissions("adv", "add_user")) OR (hasPermissions("adv", "users")) OR (hasPermissions("adv", "mass_edit_users")) OR (hasPermissions("adv", "mng_regusers")) OR (hasPermissions("adv", "add_reguser")) OR (hasPermissions("adv", "credits_log")) OR (hasPermissions("adv", "client_request_log")) OR (hasPermissions("adv", "reg_userlog"))) { ?>
							<li>
                                <a href="#"> <i class="la la-user"></i><span><?=$_["users"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
									<?php if (hasPermissions("adv", "add_user")) { ?>
                                    <li><a href="./user.php"><?=$_["add_user"]?></a></li>
									<?php }
									if (hasPermissions("adv", "users")) { ?>
                                    <li><a href="./users.php"><?=$_["manage_users"]?></a></li>
									<?php }
									if (hasPermissions("adv", "mass_edit_users")) { ?>
                                    <li><a href="./user_mass.php"><?=$_["mass_edit_users"]?></a></li>
									<?php }
                                    if ((hasPermissions("adv", "add_reguser")) OR (hasPermissions("adv", "mng_regusers"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									if (hasPermissions("adv", "add_reguser")) { ?>
                                    <li><a href="./reg_user.php"><?=$_["add_registered_user"]?></a></li>
									<?php }
									if (hasPermissions("adv", "mng_regusers")) { ?>
                                    <li><a href="./reg_users.php"><?=$_["manage_registered_users"]?></a></li>
									<?php }
                                    if ((hasPermissions("adv", "credits_log")) OR (hasPermissions("adv", "client_request_log")) OR (hasPermissions("adv", "reg_userlog"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									if (hasPermissions("adv", "credits_log")) { ?>
                                    <li><a href="./credit_logs.php"><?=$_["credit_logs"]?></a></li>
									<?php }
									if (hasPermissions("adv", "client_request_log")) { ?>
                                    <li><a href="./client_logs.php"><?=$_["client_logs"]?></a></li>
									<?php }
									if (hasPermissions("adv", "reg_userlog")) { ?>
                                    <li><a href="./reg_user_logs.php"><?=$_["reseller_logs"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							} else { ?>
							<li>
                                <a href="#"> <i class="la la-user"></i><span><?=$_["users"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <?php if ((!$rAdminSettings["disable_trial"]) && ($rPermissions["total_allowed_gen_trials"] > 0) && ($rUserInfo["credits"] >= $rPermissions["minimum_trial_credits"])) { ?>
                                    <li><a href="./user_reseller.php?trial"><?=$_["generate_trial"]?></a></li>
                                    <?php } ?>
                                    <li><a href="./user_reseller.php"><?=$_["add_user"]?></a></li>
                                    <li><a href="./users.php"><?=$_["manage_users"]?></a></li>
                                </ul>
                            </li>
							<?php }
                            if (($rPermissions["is_reseller"]) && ($rPermissions["create_sub_resellers"])) { ?>
                            <li>
                                <a href="#"> <i class="la la-users"></i><span><?=$_["subresellers"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <?php if ($rPermissions["is_admin"]) { ?>
                                    <li><a href="./reg_user.php"><?=$_["add_subreseller"]?></a></li>
                                    <?php } else { ?>
                                    <li><a href="./subreseller.php"><?=$_["add_subreseller"]?></a></li>
                                    <?php } ?>
                                    <li><a href="./reg_users.php"><?=$_["manage_subresellers"]?></a></li>
                                </ul>
                            </li>
                            <?php }
							if ($rPermissions["is_admin"]) {
							if ((hasPermissions("adv", "add_mag")) OR (hasPermissions("adv", "manage_mag")) OR (hasPermissions("adv", "add_e2")) OR (hasPermissions("adv", "manage_e2")) OR (hasPermissions("adv", "manage_events"))) { ?>
                            <li>
                                <a href="#"> <i class="la la-tablet"></i><span><?=$_["devices"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
									<?php if (hasPermissions("adv", "add_mag")) { ?>
                                    <li><a href="./user.php?mag"><?=$_["add_mag"]?></a></li>
                                    <li><a href="./mag.php"><?=$_["link_mag"]?></a></li>
									<?php }
									if (hasPermissions("adv", "manage_mag")) { ?>
                                    <li><a href="./mags.php"><?=$_["manage_mag_devices"]?></a></li>
									<?php }
                                    if ((hasPermissions("adv", "add_e2")) OR (hasPermissions("adv", "manage_e2")) OR (hasPermissions("adv", "manage_events"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									if (hasPermissions("adv", "add_e2")) { ?>
                                    <li><a href="./user.php?e2"><?=$_["add_enigma"]?></a></li>
                                    <li><a href="./enigma.php"><?=$_["link_enigma"]?></a></li>
									<?php }
									if (hasPermissions("adv", "manage_e2")) { ?>
                                    <li><a href="./enigmas.php"><?=$_["manage_enigma_devices"]?></a></li>
									<?php }
									if (hasPermissions("adv", "manage_events")) { ?>
                                    <div class="separator"></div>
                                    <li><a href="./mag_events.php"><?=$_["mag_event_logs"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							} else { ?>
							<li>
                                <a href="#"> <i class="la la-tablet"></i><span><?=$_["devices"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <li><a href="./mags.php"><?=$_["manage_mag_devices"]?></a></li>
                                    <li><a href="./enigmas.php"><?=$_["manage_enigma_devices"]?></a></li>
                                </ul>
                            </li>
                            <?php }
							if ($rPermissions["is_admin"]) {
							if ((hasPermissions("adv", "add_movie")) OR (hasPermissions("adv", "import_movies")) OR (hasPermissions("adv", "movies")) OR (hasPermissions("adv", "series")) OR (hasPermissions("adv", "add_series")) OR (hasPermissions("adv", "mass_sedits_vod")) OR (hasPermissions("adv", "mass_sedits"))) { ?>
                            <li>
                                <a href="#"> <i class="la la-video-camera"></i><span><?=$_["vod"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
									<?php if (hasPermissions("adv", "add_movie")) { ?>
									<li><a href="./movie.php"><?=$_["add_movie"]?></a></li>
									<?php }
									if (hasPermissions("adv", "import_movies")) { ?>
									<li><a href="./movie.php?import"><?=$_["import_movies"]?></a></li>
									<?php }
									if (hasPermissions("adv", "movies")) { ?>
									<li><a href="./movies.php"><?=$_["manage_movies"]?></a></li>
									<?php }
									if ((hasPermissions("adv", "add_series")) OR (hasPermissions("adv", "series")) OR (hasPermissions("adv", "episodes"))) { ?>
									<div class="separator"></div>
									<?php }
									if (hasPermissions("adv", "add_series")) { ?>
									<li><a href="./serie.php"><?=$_["add_series"]?></a></li>
									<?php }
									if (hasPermissions("adv", "series")) { ?>
									<li><a href="./series.php"><?=$_["manage_series"]?></a></li>
									<?php }
									if (hasPermissions("adv", "episodes")) { ?>
									<li><a href="./episodes.php"><?=$_["manage_episodes"]?></a></li>
									<?php }
                                    if ((hasPermissions("adv", "mass_sedits_vod")) OR (hasPermissions("adv", "mass_sedits")) OR (hasPermissions("adv", "mass_edit_radio"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									if (hasPermissions("adv", "mass_sedits_vod")) { ?>
									<li><a href="./movie_mass.php"><?=$_["mass_edit_movies"]?></a></li>
									<?php }
									if (hasPermissions("adv", "mass_sedits")) { ?>
									<li><a href="./series_mass.php"><?=$_["mass_edit_series"]?></a></li>
                                            <li><a href="./episodes_mass.php"><?=$_["mass_edit_episodes"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							if ((hasPermissions("adv", "add_radio")) OR (hasPermissions("adv", "radio")) OR (hasPermissions("adv", "mass_edit_radio"))) { ?>
                            <li>
                                <a href="#"> <i class="mdi mdi-radio-tower"></i><span><?=$_["stations"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
									<?php if (hasPermissions("adv", "add_radio")) { ?>
									<li><a href="./radio.php"><?=$_["add_station"]?></a></li>
									<?php }
									if (hasPermissions("adv", "radio")) { ?>
									<li><a href="./radios.php"><?=$_["manage_stations"]?></a></li>
									<?php } ?>
									<?php if (hasPermissions("adv", "mass_edit_radio")) { ?>
                                    <div class="separator"></div>
									<li><a href="./radio_mass.php"><?=$_["mass_edit_stations"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							if ((hasPermissions("adv", "add_stream")) OR (hasPermissions("adv", "import_streams")) OR (hasPermissions("adv", "create_channel")) OR (hasPermissions("adv", "streams")) OR (hasPermissions("adv", "mass_edit_streams"))  OR (hasPermissions("adv", "stream_tools"))  OR (hasPermissions("adv", "stream_errors"))  OR (hasPermissions("adv", "fingerprint"))) { ?>
                            <li>
                                <a href="#"> <i class="la la-play-circle-o"></i><span><?=$_["streams"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
									<?php if (hasPermissions("adv", "add_stream")) { ?>
                                    <li><a href="./stream.php"><?=$_["add_stream"]?></a></li>
									<?php }
									if (hasPermissions("adv", "create_channel")) { ?>
                                    <li><a href="./created_channel.php"><?=$_["create_channel"]?></a></li>
									<?php }
									if (hasPermissions("adv", "import_streams")) { ?>
                                    <li><a href="./stream.php?import"><?=$_["import_streams"]?></a></li>
									<?php }
									if (hasPermissions("adv", "streams")) { ?>
                                    <li><a href="./streams.php"><?=$_["manage_streams"]?></a></li>
									<?php }
                                    if ((hasPermissions("adv", "mass_edit_streams")) OR (hasPermissions("adv", "stream_errors")) OR (hasPermissions("adv", "stream_tools"))  OR (hasPermissions("adv", "fingerprint"))) { ?>
                                    <div class="separator"></div>
                                    <?php }
									if (hasPermissions("adv", "mass_edit_streams")) { ?>
                                    <li><a href="./stream_mass.php"><?=$_["mass_edit_streams"]?></a></li>
									<?php }
									if (hasPermissions("adv", "stream_errors")) { ?>
                                    <li><a href="./stream_logs.php"><?=$_["stream_logs"]?></a></li>
									<?php }
									if (hasPermissions("adv", "stream_tools")) { ?>
									<li><a href="./stream_tools.php"><?=$_["stream_tools"]?></a></li>
									<?php }
									if (hasPermissions("adv", "fingerprint")) { ?>
                                    <li><a href="./fingerprint.php"><?=$_["fingerprint"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
							<?php }
							if ((hasPermissions("adv", "add_bouquet")) OR (hasPermissions("adv", "bouquets"))) { ?>
                            <li>
                                <a href="#"> <i class="mdi mdi-flower-tulip-outline"></i><span><?=$_["bouquets"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
									<?php if (hasPermissions("adv", "add_bouquet")) { ?>
                                    <li><a href="./bouquet.php"><?=$_["add_bouquet"]?></a></li>
									<?php }
									if (hasPermissions("adv", "bouquets")) { ?>
                                    <li><a href="./bouquets.php"><?=$_["manage_bouquets"]?></a></li>
                                    <?php }
									if (hasPermissions("adv", "edit_bouquet")) { ?>
                                    <li><a href="./bouquet_sort.php"><?=$_["order_bouquets"]?></a></li>
									<?php } ?>
                                </ul>
                            </li>
                            <?php }
							}
                            if (($rPermissions["is_reseller"]) && ($rPermissions["reset_stb_data"])) { ?>
                            <li>
                                <a href="#"> <i class="la la-play-circle-o"></i><span><?=$_["content"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <li><a href="./streams.php"><?=$_["streams"]?></a></li>
                                    <li><a href="./movies.php"><?=$_["movies"]?></a></li>
                                    <li><a href="./series.php"><?=$_["series"]?></a></li>
                                    <li><a href="./episodes.php"><?=$_["episodes"]?></a></li>
                                    <li><a href="./radios.php"><?=$_["stations"]?></a></li>
                                </ul>
                            </li>
                            <?php }
                            if ($rPermissions["is_reseller"]) { ?>
                            <li>
                                <a href="#"> <i class="la la-envelope"></i><span><?=$_["support"]?></span><span class="arrow-right"></span></a>
                                <ul class="nav-second-level" aria-expanded="false">
                                    <li><a href="./ticket.php"><?=$_["create_ticket"]?></a></li>
                                    <li><a href="./tickets.php"><?=$_["manage_tickets"]?></a></li>
                                </ul>
                            </li>
                            <?php }
                            if (($rPermissions["is_admin"]) && (hasPermissions("adv", "manage_tickets"))) { ?>
                            <li>
                                <a href="./tickets.php"> <i class="la la-envelope"></i><span><?=$_["tickets"]?></span></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <!-- End Sidebar -->
                    <div class="clearfix"></div>
                </div>
                <!-- Sidebar -left -->
            </div>