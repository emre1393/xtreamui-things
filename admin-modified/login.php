<?php
include "functions.php";
if (isset($_SESSION['hash'])) { header("Location: ./dashboard.php"); exit; }

$rAdminSettings = getAdminSettings();
if (intval($rAdminSettings["login_flood"]) > 0) {
	$result = $db->query("SELECT COUNT(`id`) AS `count` FROM `login_flood` WHERE `ip` = '".ESC(getIP())."' AND TIME_TO_SEC(TIMEDIFF(NOW(), `dateadded`)) <= 86400;");
	if (($result) && ($result->num_rows == 1)) {
		if (intval($result->fetch_assoc()["count"]) >= intval($rAdminSettings["login_flood"])) {
			$_STATUS = 7;
		}
	}
}

if (!isset($_STATUS)) {
	$rGA = new PHPGangsta_GoogleAuthenticator();
	if ((isset($_POST["username"])) && (isset($_POST["password"]))) {
		if ($rAdminSettings["recaptcha_enable"]) {
			$rResponse = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$rAdminSettings["recaptcha_v2_secret_key"].'&response='.$_POST['g-recaptcha-response']), True);
			if ((!$rResponse["success"]) && (!in_array("invalid-input-secret", $rResponse["error-codes"]))) {
				$_STATUS = 5;
			}
		}
		if (!isset($_STATUS)) {
			$rUserInfo = doLogin($_POST["username"], $_POST["password"]);
			if (isset($rUserInfo)) {
				if ((isset($rAdminSettings["google_2factor"])) && ($rAdminSettings["google_2factor"])) {
					if (strlen($rUserInfo["google_2fa_sec"]) == 0) {
						$rGA = new PHPGangsta_GoogleAuthenticator();
						$rSecret = $rGA->createSecret();
						$rUserInfo["google_2fa_sec"] = $rSecret;
						$db->query("UPDATE `reg_users` SET `google_2fa_sec` = '".ESC($rSecret)."' WHERE `id` = ".intval($rUserInfo["id"]).";");
						$rNew2F = true;
					}
					$rQR = $rGA->getQRCodeGoogleUrl('Xtream UI', $rUserInfo["google_2fa_sec"]);
                    $rAuth = md5($rUserInfo["password"]);
				} else if ((strlen($_POST["password"]) < intval($rAdminSettings["pass_length"])) && (intval($rAdminSettings["pass_length"]) > 0)) {
					$rChangePass = md5($rUserInfo["password"]);
				} else {
					$rPermissions = getPermissions($rUserInfo["member_group_id"]);
					if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && ((!$rPermissions["is_banned"]) && ($rUserInfo["status"] == 1)))) {
						$db->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `ip` = '".ESC(getIP())."' WHERE `id` = ".intval($rUserInfo["id"]).";");
						$_SESSION['hash'] = md5($rUserInfo["username"]);
						$_SESSION['ip'] = getIP();
						if ($rPermissions["is_admin"]) {
							if (strlen($_POST["referrer"]) > 0) {
								header("Location: .".ESC($_POST["referrer"]));
							} else {
								header("Location: ./dashboard.php");
							}
						} else {
							$db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '', '', ".intval(time()).", '[<b>UserPanel</b> -> <u>".$_["logged_in"]."</u>]');");
							if (strlen($_POST["referrer"]) > 0) {
								header("Location: .".ESC($_POST["referrer"]));
							} else {
								header("Location: ./reseller.php");
							}
						}
					} else if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && ($rPermissions["is_banned"]))) {
						$_STATUS = 2;
					} else if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && (!$rUserInfo["status"]))) {
						$_STATUS = 3;
					} else {
						$_STATUS = 4;
					}
				}
			} else {
				if (intval($rAdminSettings["login_flood"]) > 0) {
					$db->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('".ESC($_POST["username"])."', '".ESC(getIP())."');");
				}
				$_STATUS = 0;
			}
		}
	} else if ((isset($_POST["gauth"])) && (isset($_POST["hash"])) && (isset($_POST["auth"])) && (isset($rAdminSettings["google_2factor"])) && ($rAdminSettings["google_2factor"])) {
		$rUserInfo = getRegisteredUserHash($_POST["hash"]);
        $rAuth = $_POST["auth"];
		if (($rUserInfo) && ($rAuth == md5($rUserInfo["password"]))) {
			if ($rGA->verifyCode($rUserInfo["google_2fa_sec"], $_POST["gauth"], 2)) {
				$rPermissions = getPermissions($rUserInfo["member_group_id"]);
				if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && ((!$rPermissions["is_banned"]) && ($rUserInfo["status"] == 1)))) {
					$db->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `ip` = '".ESC(getIP())."' WHERE `id` = ".intval($rUserInfo["id"]).";");
					$_SESSION['hash'] = md5($rUserInfo["username"]);
					$_SESSION['ip'] = getIP();
					if ($rPermissions["is_admin"]) {
						header("Location: ./dashboard.php");
					} else {
						$db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '', '', ".intval(time()).", '[<b>UserPanel</b> -> <u>".$_["logged_in"]."</u>]');");
						header("Location: ./reseller.php");
					}
				} else if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && ($rPermissions["is_banned"]))) {
					$_STATUS = 2;
				} else if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && (!$rUserInfo["status"]))) {
					$_STATUS = 3;
				} else {
					$_STATUS = 4;
				}
			} else {
				$rQR = $rGA->getQRCodeGoogleUrl('Xtream UI', $rUserInfo["google_2fa_sec"]);
				$_STATUS = 1;
			}
		} else {
			if (intval($rAdminSettings["login_flood"]) > 0) {
				$db->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('".ESC($_POST["username"])."', '".ESC(getIP())."');");
			}
			$_STATUS = 0;
		}
	} else if ((isset($_POST["newpass"])) && (isset($_POST["confirm"])) && (isset($_POST["hash"])) && (isset($_POST["change"]))) {
		$rUserInfo = getRegisteredUserHash($_POST["hash"]);
        $rChangePass = $_POST["change"];
		if (($rUserInfo) && ($rChangePass == md5($rUserInfo["password"]))) {
			if (($_POST["newpass"] == $_POST["confirm"]) && (strlen($_POST["newpass"]) >= intval($rAdminSettings["pass_length"]))) {
				$rPermissions = getPermissions($rUserInfo["member_group_id"]);
				if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && ((!$rPermissions["is_banned"]) && ($rUserInfo["status"] == 1)))) {
					$db->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `password` = '".ESC(cryptPassword($_POST["newpass"]))."', `ip` = '".ESC(getIP())."' WHERE `id` = ".intval($rUserInfo["id"]).";");
					$_SESSION['hash'] = md5($rUserInfo["username"]);
					$_SESSION['ip'] = getIP();
					if ($rPermissions["is_admin"]) {
						header("Location: ./dashboard.php");
					} else {
						$db->query("INSERT INTO `reg_userlog`(`owner`, `username`, `password`, `date`, `type`) VALUES(".intval($rUserInfo["id"]).", '', '', ".intval(time()).", '[<b>UserPanel</b> -> <u>".$_["logged_in"]."</u>]');");
						header("Location: ./reseller.php");
					}
				} else if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && ($rPermissions["is_banned"]))) {
					$_STATUS = 2;
				} else if (($rPermissions) && ((($rPermissions["is_admin"]) OR ($rPermissions["is_reseller"])) && (!$rUserInfo["status"]))) {
					$_STATUS = 3;
				} else {
					$_STATUS = 4;
				}
			} else {
				$_STATUS = 6;
			}
		} else {
			if (intval($rAdminSettings["login_flood"]) > 0) {
				$db->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('".ESC($_POST["username"])."', '".ESC(getIP())."');");
			}
			$_STATUS = 0;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Xtream UI - <?=$_["login"]?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <!-- App css -->
		<link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
        <?php if ($rAdminSettings["dark_mode_login"]) { ?>
		<link href="assets/css/bootstrap.dark.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.dark.css" rel="stylesheet" type="text/css" />
        <?php } else { ?>
        <link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.css" rel="stylesheet" type="text/css" />
        <?php } ?>
		<style>
			.g-recaptcha {
				display: inline-block;
			}
		</style>
    </head>
    <body class="authentication-bg authentication-bg-pattern">
        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <?php if (file_exists("./.update")) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?=$_["login_message_1"]?>
                        </div>
                        <?php }
                        if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?=$_["login_message_2"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 1)) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?=$_["login_message_3"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 2)) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?=$_["login_message_4"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 3)) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?=$_["login_message_5"]?>
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS == 4)) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?=$_["login_message_6"]?>
                        </div>
						<?php } else if ((isset($_STATUS)) && ($_STATUS == 5)) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?=$_["login_message_7"]?>
                        </div>
						<?php } else if ((isset($_STATUS)) && ($_STATUS == 6)) { ?>
                        <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<?=str_replace("{num}", $rAdminSettings["pass_length"], $_["login_message_8"])?>
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="text-center w-75 m-auto">
                                    <?php if ($rAdminSettings["dark_mode_login"]) { ?>
									<span><img src="assets/images/logo.png" width="200px" alt=""></span>
                                    <?php } else { ?>
                                    <span><img src="assets/images/logo-back.png" width="200px" alt=""></span>
                                    <?php } ?>
                                    <p class="text-muted mb-4 mt-3"></p>
                                </div>
                                <h5 class="auth-title"><?=$_["admin_reseller_interface"]?></h5>
								<?php if ((!isset($_STATUS)) OR ($_STATUS <> 7)) { ?>
                                <form action="./login.php" method="POST" data-parsley-validate="" id="login_form">
                                    <input type="hidden" name="referrer" value="<?=ESC($_GET["referrer"])?>" />
                                    <?php if ((!isset($rQR)) && (!isset($rChangePass))) { ?>
                                    <div class="form-group mb-3" id="username_group">
                                        <label for="username"><?=$_["username"]?></label>
                                        <input class="form-control" autocomplete="off" type="text" id="username" name="username" required data-parsley-trigger="change" placeholder="<?=$_["enter_your_username"]?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="password"><?=$_["password"]?></label>
                                        <input class="form-control" autocomplete="off" type="password" required data-parsley-trigger="change" id="password" name="password" placeholder="<?=$_["enter_your_password"]?>">
                                    </div>
									<?php if ($rAdminSettings["recaptcha_enable"]) { ?>
									<h5 class="auth-title text-center">
                                        <div class="g-recaptcha" id="verification" data-sitekey="<?=$rAdminSettings["recaptcha_v2_site_key"]?>"></div>
                                    </h5>
									<?php }
                                    } else if (isset($rChangePass)) { ?>
									<input type="hidden" name="hash" value="<?=md5($rUserInfo["username"])?>" />
                                    <input type="hidden" name="change" value="<?=$rChangePass?>" />
									<div class="form-group mb-3 text-center">
                                        <p><?=str_replace("{num}", $rAdminSettings["pass_length"], $_["login_message_9"])?></p>
                                    </div>
									<div class="form-group mb-3">
                                        <label for="newpass"><?=$_["new_password"]?></label>
                                        <input class="form-control" autocomplete="off" type="password" id="newpass" name="newpass" required data-parsley-trigger="change" placeholder="<?=$_["enter_a_new_password"]?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="confirm"><?=$_["confirm_password"]?></label>
                                        <input class="form-control" autocomplete="off" type="password" id="confirm" name="confirm" required data-parsley-trigger="change" placeholder="<?=$_["confirm_your_password"]?>">
                                    </div>
									<?php } else { ?>
                                    <input type="hidden" name="hash" value="<?=md5($rUserInfo["username"])?>" />
                                    <input type="hidden" name="auth" value="<?=$rAuth?>" />
                                    <?php if (isset($rNew2F)) { ?>
                                    <div class="form-group mb-3 text-center">
                                        <p><?=$_["login_message_10"]?></p>
                                        <img src="<?=$rQR?>">
                                    </div>
                                    <?php } ?>
                                    <div class="form-group mb-3">
                                        <label for="gauth"><?=$_["google_authenticator_code"]?></label>
                                        <input class="form-control" autocomplete="off" type="gauth" required="" id="gauth" name="gauth" placeholder="<?=$_["enter_your_auth_code"]?>">
                                    </div>
                                    <?php } ?>
                                    <div class="form-group mb-0 text-center">
                                        <button class="btn btn-danger btn-block" type="submit" id="login_button"><?=$_["login"]?></button>
                                    </div>
                                </form>
								<?php } else { ?>
								<div class="form-group mb-3 text-center text-danger">
									<p><?=$_["login_message_11"]?></p>
								</div>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/app.min.js?rid=<?=getID()?>"></script>
		<?php if ($rAdminSettings["recaptcha_enable"]) { ?>
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<?php } ?>
        <script>
        $(document).ready(function() {
            if (window.location.hash.substring(0,1) == "#") {
                $("#username_group").hide();
                $("#username").val(window.location.hash.substring(1));
                $("#login_form").attr('action', './login.php#' + window.location.hash.substring(1));
                $("#login_button").html("<?=$_["login_as"]?> " + window.location.hash.substring(1).toUpperCase());
            }
        });
        </script>
    </body>
</html>